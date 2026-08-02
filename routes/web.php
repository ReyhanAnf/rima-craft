<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/customer.php';
require __DIR__.'/admin.php';

// Direct route to preview or render error pages (400, 401, 403, 404, 500, 503)
Route::get('/error/{status}', function ($status) {
    $code = (int) $status;
    abort_if(! in_array($code, [400, 401, 403, 404, 500, 503]), 404);

    return Inertia::render('Error', [
        'status' => $code,
    ]);
})->name('error.show');

// Fallback route for unmapped URLs (404)
Route::fallback(function () {
    return Inertia::render('Error', ['status' => 404])
        ->toResponse(request())
        ->setStatusCode(404);
});
