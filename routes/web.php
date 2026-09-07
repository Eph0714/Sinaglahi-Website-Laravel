<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Artist\ArtworksController as ArtistArtworksController;
use App\Http\Controllers\Artist\DashboardController as ArtistDashboardController;
use App\Http\Controllers\Artist\ProfileController as ArtistProfileController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\ArtworksController;
use App\Http\Controllers\AuthController;
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

// ---------------- Auth ----------------
Route::get('/account/login', [AuthController::class, 'showLogin'])->name('account.login');
Route::post('/account/login', [AuthController::class, 'login'])->name('account.login.submit');
Route::post('/account/logout', [AuthController::class, 'logout'])->name('account.logout');
Route::get('/account/register', [PlaceholderController::class, 'comingSoon'])->name('account.register');

// ---------------- Admin area ----------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});

// ---------------- Artist area ----------------
Route::middleware(['auth', 'artist'])->prefix('artist')->name('artist.')->group(function () {
    Route::get('/', [ArtistDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ArtistProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ArtistProfileController::class, 'update'])->name('profile.update');

    Route::get('/artworks', [ArtistArtworksController::class, 'index'])->name('artworks.index');
    Route::get('/artworks/create', [ArtistArtworksController::class, 'create'])->name('artworks.create');
    Route::post('/artworks', [ArtistArtworksController::class, 'store'])->name('artworks.store');
    Route::get('/artworks/{id}/edit', [ArtistArtworksController::class, 'edit'])->name('artworks.edit');
    Route::post('/artworks/{id}', [ArtistArtworksController::class, 'update'])->name('artworks.update');
    Route::post('/artworks/{id}/withdraw', [ArtistArtworksController::class, 'withdraw'])->name('artworks.withdraw');
    Route::post('/artworks/{id}/submit', [ArtistArtworksController::class, 'submit'])->name('artworks.submit');
    Route::post('/artworks/{id}/delete', [ArtistArtworksController::class, 'destroy'])->name('artworks.destroy');
});

// Phase 2 continues: the Join wizard is not ported yet.
Route::get('/join', [PlaceholderController::class, 'comingSoon'])->name('join.index');
Route::get('/join/check-status', [PlaceholderController::class, 'comingSoon'])->name('join.checkStatus');
