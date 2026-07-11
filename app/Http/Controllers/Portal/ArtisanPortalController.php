<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ArtisanJob;
use App\Models\ArtisanJobAssignment;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionArtisanWage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ArtisanPortalController extends Controller
{
    public function dashboard(): InertiaResponse
    {
        $user = auth()->user();

        $wages = ProductionArtisanWage::with(['production.results.product'])
            ->where('artisan_id', $user->id)
            ->latest()
            ->paginate(10);

        $assignments = ArtisanJobAssignment::with('job')
            ->where('artisan_id', $user->id)
            ->latest()
            ->get();

        $followedJobIds = $assignments->pluck('artisan_job_id')->all();
        $openJobs = ArtisanJob::where('status', 'open')
            ->whereNotIn('id', $followedJobIds)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Portal/Artisan/Dashboard', [
            'wages' => $wages,
            'assignments' => $assignments,
            'openJobs' => $openJobs,
            'summary' => [
                'total_wages' => (float) ProductionArtisanWage::where('artisan_id', $user->id)->sum('amount'),
                'assigned_job_wages' => (float) ArtisanJobAssignment::where('artisan_id', $user->id)->sum('assigned_wage'),
                'total_products_stock' => (float) Product::sum('current_stock'),
                'total_materials_stock' => (float) Material::sum('current_stock'),
            ],
        ]);
    }

    public function join(ArtisanJob $artisanJob): RedirectResponse
    {
        if ($artisanJob->status !== 'open') {
            return back()->with('error', 'Pekerjaan ini sudah tidak dibuka.');
        }

        ArtisanJobAssignment::firstOrCreate([
            'artisan_job_id' => $artisanJob->id,
            'artisan_id' => auth()->id(),
        ], [
            'status' => 'interested',
        ]);

        return back()->with('success', 'Pekerjaan berhasil diikuti.');
    }
}
