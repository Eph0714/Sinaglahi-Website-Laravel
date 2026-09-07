<?php

use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\ArtworksController;
use App\Http\Controllers\GroupActivitiesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('home.about');
Route::get('/contact', [HomeController::class, 'contact'])->name('home.contact');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('home.privacy');

Route::get('/artists', [ArtistsController::class, 'index'])->name('artists.index');
Route::get('/artists/{slug}', [ArtistsController::class, 'show'])->name('artists.show');

Route::get('/artworks', [ArtworksController::class, 'index'])->name('artworks.index');
Route::get('/artworks/{slug}', [ArtworksController::class, 'show'])->name('artworks.show');

Route::get('/group-activities', [GroupActivitiesController::class, 'index'])->name('group-activities.index');
Route::get('/group-activities/{slug}', [GroupActivitiesController::class, 'show'])->name('group-activities.show');

// Phase 2 (auth, the Join wizard, and the Admin/Artist areas) is not ported
// yet - these stay as simple placeholders so the layout's nav/footer links
// don't 404 while the public catalog pages above are the focus of Phase 1.
Route::get('/join', [PlaceholderController::class, 'comingSoon'])->name('join.index');
Route::get('/join/check-status', [PlaceholderController::class, 'comingSoon'])->name('join.checkStatus');
Route::get('/account/login', [PlaceholderController::class, 'comingSoon'])->name('account.login');
Route::get('/account/register', [PlaceholderController::class, 'comingSoon'])->name('account.register');
