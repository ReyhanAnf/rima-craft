<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SettingController extends Controller
{
    public function index(): InertiaResponse
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $isDevAdmin = auth()->user()->hasRole('dev-admin');

        $data = $request->except([
            '_token', '_method', 
            'looping_video', 'hero_image', 'gallery_1_image', 'gallery_2_image',
            'logo', 'sponsor_logos',
        ]);

        $devKeys = [
            'business_subtitle',
            'sponsors_json',
        ];

        if (!$isDevAdmin) {
            foreach ($devKeys as $key) {
                unset($data[$key]);
            }
        }

        $filesToUpload = [
            'looping_video' => 'looping_video_url',
            'hero_image'    => 'hero_image_url',
            'gallery_1_image' => 'gallery_1_url',
            'gallery_2_image' => 'gallery_2_url',
            'logo'          => 'logo_url',
        ];

        foreach ($filesToUpload as $input => $key) {
            if ($request->hasFile($input)) {
                $path = $request->file($input)->store('landing', 'public');
                $data[$key] = $path;
            }
        }

        // Handle dynamic sponsors JSON + per-sponsor logo uploads
        if ($isDevAdmin && $request->has('sponsors_json')) {
            $sponsors = json_decode($request->input('sponsors_json'), true) ?? [];
            
            // Process uploaded sponsor logos
            if ($request->hasFile('sponsor_logos')) {
                foreach ($request->file('sponsor_logos') as $idx => $file) {
                    $path = $file->store('sponsors', 'public');
                    if (isset($sponsors[$idx])) {
                        $sponsors[$idx]['logo_url'] = $path;
                    }
                }
            }
            
            $data['sponsors_json'] = json_encode($sponsors);
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
