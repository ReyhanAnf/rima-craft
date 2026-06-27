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
        $data = $request->except(['_token', '_method', 'looping_video', 'hero_image', 'gallery_1_image', 'gallery_2_image']);

        $filesToUpload = [
            'looping_video' => 'looping_video_url',
            'hero_image' => 'hero_image_url',
            'gallery_1_image' => 'gallery_1_url',
            'gallery_2_image' => 'gallery_2_url'
        ];

        foreach ($filesToUpload as $input => $key) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $path = $file->store('landing', 'public');
                $data[$key] = $path;
            }
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
