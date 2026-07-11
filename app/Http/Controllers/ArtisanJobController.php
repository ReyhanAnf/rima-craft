<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ArtisanJob;
use App\Models\ArtisanJobAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ArtisanJobController extends Controller
{
    public function index(): InertiaResponse
    {
        $jobs = ArtisanJob::with(['assignments.artisan'])
            ->orderByRaw("case status when 'open' then 0 when 'in_progress' then 1 when 'done' then 2 else 3 end")
            ->orderByDesc('created_at')
            ->paginate(12);

        $artisans = User::whereHas('roles', fn ($query) => $query->where('name', 'pengrajin'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('ArtisanJobs/Index', [
            'jobs' => $jobs,
            'artisans' => $artisans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $assignments = $data['assignments'] ?? [];
        unset($data['assignments']);

        $job = ArtisanJob::create([
            ...$data,
            'created_by' => $request->user()?->id,
        ]);

        $this->syncAssignments($job, $assignments);

        return redirect()->route('artisan-jobs.index')
            ->with('success', 'Pekerjaan pengrajin berhasil dibuat.');
    }

    public function update(Request $request, ArtisanJob $artisanJob): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $assignments = $data['assignments'] ?? [];
        unset($data['assignments']);

        $artisanJob->update($data);
        $this->syncAssignments($artisanJob, $assignments);

        return redirect()->route('artisan-jobs.index')
            ->with('success', 'Pekerjaan pengrajin berhasil diperbarui.');
    }

    public function destroy(ArtisanJob $artisanJob): RedirectResponse
    {
        $artisanJob->delete();

        return redirect()->route('artisan-jobs.index')
            ->with('success', 'Pekerjaan pengrajin berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'estimated_wage' => ['nullable', 'numeric', 'min:0'],
            'work_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'done', 'cancelled'])],
            'assignments' => ['nullable', 'array'],
            'assignments.*.artisan_id' => ['required_with:assignments', 'exists:users,id'],
            'assignments.*.assigned_wage' => ['nullable', 'numeric', 'min:0'],
            'assignments.*.status' => ['required_with:assignments', Rule::in(['interested', 'assigned', 'done', 'cancelled'])],
            'assignments.*.notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $assignments
     */
    private function syncAssignments(ArtisanJob $job, array $assignments): void
    {
        $keptArtisanIds = [];

        foreach ($assignments as $assignment) {
            if (empty($assignment['artisan_id'])) {
                continue;
            }

            $keptArtisanIds[] = (int) $assignment['artisan_id'];

            ArtisanJobAssignment::updateOrCreate(
                [
                    'artisan_job_id' => $job->id,
                    'artisan_id' => $assignment['artisan_id'],
                ],
                [
                    'assigned_wage' => $assignment['assigned_wage'] ?? null,
                    'status' => $assignment['status'] ?? 'assigned',
                    'notes' => $assignment['notes'] ?? null,
                ]
            );
        }

        if ($keptArtisanIds !== []) {
            $job->assignments()->whereNotIn('artisan_id', $keptArtisanIds)->delete();
        } else {
            $job->assignments()->delete();
        }
    }
}
