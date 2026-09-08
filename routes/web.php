<?php

use App\Http\Controllers\Admin\ActivitiesController as AdminActivitiesController;
use App\Http\Controllers\Admin\ApplicationsController as AdminApplicationsController;
use App\Http\Controllers\Admin\ArtistsController as AdminArtistsController;
use App\Http\Controllers\Admin\ArtMediumsController as AdminArtMediumsController;
use App\Http\Controllers\Admin\ArtworksController as AdminArtworksController;
use App\Http\Controllers\Admin\CategoriesController as AdminCategoriesController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Artist\ArtworksController as ArtistArtworksController;
use App\Http\Controllers\Artist\DashboardController as ArtistDashboardController;
use App\Http\Controllers\Artist\ProfileController as ArtistProfileController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\ArtworksController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupActivitiesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinController;
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

    Route::get('/categories', [AdminCategoriesController::class, 'index'])->name('categories.index');
    Route::post('/categories/{id}/toggle', [AdminCategoriesController::class, 'toggleActive'])->name('categories.toggle');

    Route::get('/art-mediums', [AdminArtMediumsController::class, 'index'])->name('art-mediums.index');
    Route::post('/art-mediums', [AdminArtMediumsController::class, 'store'])->name('art-mediums.store');
    Route::post('/art-mediums/update', [AdminArtMediumsController::class, 'update'])->name('art-mediums.update');
    Route::post('/art-mediums/{id}/toggle', [AdminArtMediumsController::class, 'toggleActive'])->name('art-mediums.toggle');
    Route::post('/art-mediums/{id}/move', [AdminArtMediumsController::class, 'move'])->name('art-mediums.move');
    Route::post('/art-mediums/{id}/delete', [AdminArtMediumsController::class, 'destroy'])->name('art-mediums.destroy');

    Route::get('/artists', [AdminArtistsController::class, 'index'])->name('artists.index');
    Route::get('/artists/create', [AdminArtistsController::class, 'create'])->name('artists.create');
    Route::post('/artists', [AdminArtistsController::class, 'store'])->name('artists.store');
    Route::get('/artists/{id}', [AdminArtistsController::class, 'show'])->name('artists.show');
    Route::get('/artists/{id}/edit', [AdminArtistsController::class, 'edit'])->name('artists.edit');
    Route::post('/artists/{id}', [AdminArtistsController::class, 'update'])->name('artists.update');
    Route::post('/artists/{id}/approve', [AdminArtistsController::class, 'approve'])->name('artists.approve');
    Route::post('/artists/{id}/reject', [AdminArtistsController::class, 'reject'])->name('artists.reject');
    Route::post('/artists/{id}/activate', [AdminArtistsController::class, 'activate'])->name('artists.activate');
    Route::post('/artists/{id}/deactivate', [AdminArtistsController::class, 'deactivate'])->name('artists.deactivate');
    Route::post('/artists/{id}/toggle-featured', [AdminArtistsController::class, 'toggleFeatured'])->name('artists.toggle-featured');
    Route::post('/artists/{id}/delete', [AdminArtistsController::class, 'destroy'])->name('artists.destroy');

    Route::get('/artworks', [AdminArtworksController::class, 'index'])->name('artworks.index');
    Route::get('/artworks/create', [AdminArtworksController::class, 'create'])->name('artworks.create');
    Route::post('/artworks', [AdminArtworksController::class, 'store'])->name('artworks.store');
    Route::get('/artworks/{id}', [AdminArtworksController::class, 'show'])->name('artworks.show');
    Route::get('/artworks/{id}/edit', [AdminArtworksController::class, 'edit'])->name('artworks.edit');
    Route::post('/artworks/{id}', [AdminArtworksController::class, 'update'])->name('artworks.update');
    Route::post('/artworks/{id}/approve', [AdminArtworksController::class, 'approve'])->name('artworks.approve');
    Route::post('/artworks/{id}/reject', [AdminArtworksController::class, 'reject'])->name('artworks.reject');
    Route::post('/artworks/{id}/publish', [AdminArtworksController::class, 'publish'])->name('artworks.publish');
    Route::post('/artworks/{id}/unpublish', [AdminArtworksController::class, 'unpublish'])->name('artworks.unpublish');
    Route::post('/artworks/{id}/toggle-featured', [AdminArtworksController::class, 'toggleFeatured'])->name('artworks.toggle-featured');
    Route::post('/artworks/{id}/delete', [AdminArtworksController::class, 'destroy'])->name('artworks.destroy');

    // Activity photo AJAX endpoints (activity-photos.js) - flat paths, id/photoId
    // read from the request body, matching the pre-built JS's fetch/XHR calls.
    Route::post('/activities/UploadPhotos', [AdminActivitiesController::class, 'uploadPhotos'])->name('activities.upload-photos');
    Route::post('/activities/SetCoverPhoto', [AdminActivitiesController::class, 'setCoverPhoto'])->name('activities.set-cover-photo');
    Route::post('/activities/UpdateCaption', [AdminActivitiesController::class, 'updateCaption'])->name('activities.update-caption');
    Route::post('/activities/ReplacePhoto', [AdminActivitiesController::class, 'replacePhoto'])->name('activities.replace-photo');
    Route::post('/activities/ReorderPhotos', [AdminActivitiesController::class, 'reorderPhotos'])->name('activities.reorder-photos');
    Route::post('/activities/DeletePhoto', [AdminActivitiesController::class, 'deletePhoto'])->name('activities.delete-photo');
    Route::post('/activities/BulkDeletePhotos', [AdminActivitiesController::class, 'bulkDeletePhotos'])->name('activities.bulk-delete-photos');

    Route::get('/activities', [AdminActivitiesController::class, 'index'])->name('activities.index');
    Route::get('/activities/create', [AdminActivitiesController::class, 'create'])->name('activities.create');
    Route::post('/activities', [AdminActivitiesController::class, 'store'])->name('activities.store');
    Route::get('/activities/{id}/edit', [AdminActivitiesController::class, 'edit'])->name('activities.edit');
    Route::post('/activities/{id}', [AdminActivitiesController::class, 'update'])->name('activities.update');
    Route::post('/activities/{id}/publish', [AdminActivitiesController::class, 'publish'])->name('activities.publish');
    Route::post('/activities/{id}/unpublish', [AdminActivitiesController::class, 'unpublish'])->name('activities.unpublish');
    Route::post('/activities/{id}/toggle-featured', [AdminActivitiesController::class, 'toggleFeatured'])->name('activities.toggle-featured');
    Route::post('/activities/{id}/delete', [AdminActivitiesController::class, 'destroy'])->name('activities.destroy');
    Route::get('/activities/{id}/manage-photos', [AdminActivitiesController::class, 'managePhotos'])->name('activities.manage-photos');
    Route::post('/activities/{id}/hide-photo/{photoId}', [AdminActivitiesController::class, 'hidePhoto'])->name('activities.hide-photo');
    Route::post('/activities/{id}/unhide-photo/{photoId}', [AdminActivitiesController::class, 'unhidePhoto'])->name('activities.unhide-photo');

    Route::get('/applications', [AdminApplicationsController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}', [AdminApplicationsController::class, 'show'])->name('applications.show');
    Route::get('/applications/{id}/profile-photo', [AdminApplicationsController::class, 'profilePhoto'])->name('applications.profile-photo');
    Route::get('/applications/artwork-image/{id}', [AdminApplicationsController::class, 'artworkImage'])->name('applications.artwork-image');
    Route::post('/applications/{id}/mark-under-review', [AdminApplicationsController::class, 'markUnderReview'])->name('applications.mark-under-review');
    Route::post('/applications/{id}/request-info', [AdminApplicationsController::class, 'requestInfo'])->name('applications.request-info');
    Route::post('/applications/{id}/approve', [AdminApplicationsController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{id}/reject', [AdminApplicationsController::class, 'reject'])->name('applications.reject');
    Route::post('/applications/{id}/archive', [AdminApplicationsController::class, 'archive'])->name('applications.archive');
    Route::post('/applications/{id}/add-note', [AdminApplicationsController::class, 'addNote'])->name('applications.add-note');
    Route::post('/applications/{id}/convert-to-artist', [AdminApplicationsController::class, 'convertToArtist'])->name('applications.convert-to-artist');
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

// ---------------- Join application wizard ----------------
Route::get('/join', [JoinController::class, 'index'])->name('join.index');
Route::post('/join/start', [JoinController::class, 'start'])->name('join.start');

Route::get('/join/personal-info', [JoinController::class, 'personalInfo'])->name('join.personal-info');
Route::post('/join/personal-info', [JoinController::class, 'personalInfoStore'])->name('join.personal-info.store');
Route::get('/join/personal-info-photo', [JoinController::class, 'personalInfoPhoto'])->name('join.personal-info-photo');

Route::get('/join/art-background', [JoinController::class, 'artBackground'])->name('join.art-background');
Route::post('/join/art-background', [JoinController::class, 'artBackgroundStore'])->name('join.art-background.store');

Route::get('/join/artworks', [JoinController::class, 'artworks'])->name('join.artworks');
Route::post('/join/artworks', [JoinController::class, 'addArtwork'])->name('join.add-artwork');
Route::post('/join/artworks/{id}/remove', [JoinController::class, 'removeArtwork'])->name('join.remove-artwork');
Route::get('/join/artworks/{id}/image', [JoinController::class, 'artworkImage'])->name('join.artwork-image');

Route::get('/join/consent', [JoinController::class, 'consent'])->name('join.consent');
Route::post('/join/submit', [JoinController::class, 'submit'])->name('join.submit');
Route::get('/join/confirmation', [JoinController::class, 'confirmation'])->name('join.confirmation');

Route::get('/join/check-status', [JoinController::class, 'checkStatus'])->name('join.checkStatus');
Route::post('/join/check-status', [JoinController::class, 'checkStatusSubmit'])->name('join.checkStatus.submit');
Route::post('/join/edit-application', [JoinController::class, 'editApplication'])->name('join.edit-application');
