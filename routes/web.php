<?php

use App\Http\Controllers\Admin\ActivitiesController as AdminActivitiesController;
use App\Http\Controllers\Admin\ActivityCategoriesController as AdminActivityCategoriesController;
use App\Http\Controllers\Admin\ApplicationsController as AdminApplicationsController;
use App\Http\Controllers\Admin\ArtistsController as AdminArtistsController;
use App\Http\Controllers\Admin\ArtMediumsController as AdminArtMediumsController;
use App\Http\Controllers\Admin\ArtworksController as AdminArtworksController;
use App\Http\Controllers\Admin\BannersController as AdminBannersController;
use App\Http\Controllers\Admin\CategoriesController as AdminCategoriesController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\NavigationController as AdminNavigationController;
use App\Http\Controllers\Admin\PagesController as AdminPagesController;
use App\Http\Controllers\Admin\RolesController as AdminRolesController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TestimonialsController as AdminTestimonialsController;
use App\Http\Controllers\Admin\UsersController as AdminUsersController;
use App\Http\Controllers\Admin\WhyJoinController as AdminWhyJoinController;
use App\Http\Controllers\Artist\ArtworksController as ArtistArtworksController;
use App\Http\Controllers\Artist\DashboardController as ArtistDashboardController;
use App\Http\Controllers\Artist\ProfileController as ArtistProfileController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\ArtworksController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GroupActivitiesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\PagesController;
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

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

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

    Route::get('/activity-categories', [AdminActivityCategoriesController::class, 'index'])->name('activity-categories.index');
    Route::post('/activity-categories', [AdminActivityCategoriesController::class, 'store'])->name('activity-categories.store');
    Route::post('/activity-categories/update', [AdminActivityCategoriesController::class, 'update'])->name('activity-categories.update');
    Route::post('/activity-categories/{id}/toggle', [AdminActivityCategoriesController::class, 'toggleActive'])->name('activity-categories.toggle');
    Route::post('/activity-categories/{id}/delete', [AdminActivityCategoriesController::class, 'destroy'])->name('activity-categories.destroy');

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

    // ---------------- Website content ----------------
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/delete-logo', [AdminSettingsController::class, 'deleteLogo'])->name('settings.delete-logo');
    Route::get('/settings/social-links', [AdminSettingsController::class, 'socialLinks'])->name('settings.social-links');
    Route::post('/settings/social-links', [AdminSettingsController::class, 'createSocialLink'])->name('settings.social-links.store');
    Route::post('/settings/social-links/update', [AdminSettingsController::class, 'updateSocialLink'])->name('settings.social-links.update');
    Route::post('/settings/social-links/{id}/delete', [AdminSettingsController::class, 'destroySocialLink'])->name('settings.social-links.destroy');

    Route::get('/banners', [AdminBannersController::class, 'index'])->name('banners.index');
    Route::get('/banners/create', [AdminBannersController::class, 'create'])->name('banners.create');
    Route::post('/banners', [AdminBannersController::class, 'store'])->name('banners.store');
    Route::get('/banners/{id}/edit', [AdminBannersController::class, 'edit'])->name('banners.edit');
    Route::post('/banners/{id}', [AdminBannersController::class, 'update'])->name('banners.update');
    Route::post('/banners/{id}/toggle', [AdminBannersController::class, 'toggleEnabled'])->name('banners.toggle');
    Route::post('/banners/{id}/delete', [AdminBannersController::class, 'destroy'])->name('banners.destroy');

    Route::get('/testimonials', [AdminTestimonialsController::class, 'index'])->name('testimonials.index');
    Route::get('/testimonials/create', [AdminTestimonialsController::class, 'create'])->name('testimonials.create');
    Route::post('/testimonials', [AdminTestimonialsController::class, 'store'])->name('testimonials.store');
    Route::get('/testimonials/{id}/edit', [AdminTestimonialsController::class, 'edit'])->name('testimonials.edit');
    Route::post('/testimonials/{id}', [AdminTestimonialsController::class, 'update'])->name('testimonials.update');
    Route::post('/testimonials/{id}/toggle', [AdminTestimonialsController::class, 'togglePublished'])->name('testimonials.toggle');
    Route::post('/testimonials/{id}/delete', [AdminTestimonialsController::class, 'destroy'])->name('testimonials.destroy');

    Route::get('/why-join', [AdminWhyJoinController::class, 'index'])->name('why-join.index');
    Route::post('/why-join', [AdminWhyJoinController::class, 'update'])->name('why-join.update');
    Route::get('/why-join/benefits', [AdminWhyJoinController::class, 'benefits'])->name('why-join.benefits');
    Route::get('/why-join/benefits/create', [AdminWhyJoinController::class, 'createBenefit'])->name('why-join.benefits.create');
    Route::post('/why-join/benefits', [AdminWhyJoinController::class, 'storeBenefit'])->name('why-join.benefits.store');
    Route::get('/why-join/benefits/{id}/edit', [AdminWhyJoinController::class, 'editBenefit'])->name('why-join.benefits.edit');
    Route::post('/why-join/benefits/{id}', [AdminWhyJoinController::class, 'updateBenefit'])->name('why-join.benefits.update');
    Route::post('/why-join/benefits/{id}/delete', [AdminWhyJoinController::class, 'destroyBenefit'])->name('why-join.benefits.destroy');
    Route::post('/why-join/benefits/{id}/move', [AdminWhyJoinController::class, 'moveBenefit'])->name('why-join.benefits.move');
    Route::get('/why-join/photos', [AdminWhyJoinController::class, 'photos'])->name('why-join.photos');
    Route::get('/why-join/photos/create', [AdminWhyJoinController::class, 'createPhoto'])->name('why-join.photos.create');
    Route::post('/why-join/photos', [AdminWhyJoinController::class, 'storePhoto'])->name('why-join.photos.store');
    Route::get('/why-join/photos/{id}/edit', [AdminWhyJoinController::class, 'editPhoto'])->name('why-join.photos.edit');
    Route::post('/why-join/photos/{id}', [AdminWhyJoinController::class, 'updatePhoto'])->name('why-join.photos.update');
    Route::post('/why-join/photos/{id}/set-featured', [AdminWhyJoinController::class, 'setFeaturedPhoto'])->name('why-join.photos.set-featured');
    Route::post('/why-join/photos/{id}/toggle', [AdminWhyJoinController::class, 'toggleActivePhoto'])->name('why-join.photos.toggle');
    Route::post('/why-join/photos/{id}/move', [AdminWhyJoinController::class, 'movePhoto'])->name('why-join.photos.move');
    Route::post('/why-join/photos/{id}/delete', [AdminWhyJoinController::class, 'destroyPhoto'])->name('why-join.photos.destroy');

    Route::get('/navigation', [AdminNavigationController::class, 'index'])->name('navigation.index');
    Route::post('/navigation', [AdminNavigationController::class, 'store'])->name('navigation.store');
    Route::post('/navigation/update', [AdminNavigationController::class, 'update'])->name('navigation.update');
    Route::post('/navigation/{id}/toggle', [AdminNavigationController::class, 'toggleActive'])->name('navigation.toggle');
    Route::post('/navigation/{id}/delete', [AdminNavigationController::class, 'destroy'])->name('navigation.destroy');

    Route::get('/faq', [AdminFaqController::class, 'index'])->name('faq.index');
    Route::post('/faq', [AdminFaqController::class, 'store'])->name('faq.store');
    Route::post('/faq/update', [AdminFaqController::class, 'update'])->name('faq.update');
    Route::post('/faq/{id}/toggle', [AdminFaqController::class, 'togglePublished'])->name('faq.toggle');
    Route::post('/faq/{id}/delete', [AdminFaqController::class, 'destroy'])->name('faq.destroy');

    Route::get('/pages', [AdminPagesController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [AdminPagesController::class, 'create'])->name('pages.create');
    Route::post('/pages', [AdminPagesController::class, 'store'])->name('pages.store');
    Route::get('/pages/{id}/edit', [AdminPagesController::class, 'edit'])->name('pages.edit');
    Route::post('/pages/{id}', [AdminPagesController::class, 'update'])->name('pages.update');
    Route::post('/pages/{id}/toggle', [AdminPagesController::class, 'togglePublished'])->name('pages.toggle');
    Route::post('/pages/{id}/delete', [AdminPagesController::class, 'destroy'])->name('pages.destroy');

    // ---------------- Users & Admin Roles/Permissions (Super Admin only) ----------------
    Route::middleware('superadmin')->group(function () {
        Route::get('/users/transfer-super-admin', [AdminUsersController::class, 'transferSuperAdminForm'])->name('users.transfer-super-admin');
        Route::post('/users/transfer-super-admin', [AdminUsersController::class, 'transferSuperAdmin'])->name('users.transfer-super-admin.store');
        Route::get('/users', [AdminUsersController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUsersController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUsersController::class, 'store'])->name('users.store');

        // AspNetUser keys on a GUID, not the auto-increment int the app-wide
        // {id} pattern (AppServiceProvider::boot) expects - override it here.
        Route::where(['id' => '[0-9a-fA-F-]{36}'])->group(function () {
            Route::get('/users/{id}/edit', [AdminUsersController::class, 'edit'])->name('users.edit');
            Route::post('/users/{id}', [AdminUsersController::class, 'update'])->name('users.update');
            Route::post('/users/{id}/toggle-active', [AdminUsersController::class, 'toggleActive'])->name('users.toggle-active');
            Route::post('/users/{id}/delete', [AdminUsersController::class, 'destroy'])->name('users.destroy');
            Route::get('/users/{id}/reset-password', [AdminUsersController::class, 'resetPasswordForm'])->name('users.reset-password');
            Route::post('/users/{id}/reset-password', [AdminUsersController::class, 'resetPassword'])->name('users.reset-password.store');
        });

        Route::get('/roles', [AdminRolesController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [AdminRolesController::class, 'create'])->name('roles.create');
        Route::post('/roles', [AdminRolesController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}/edit', [AdminRolesController::class, 'edit'])->name('roles.edit');
        Route::post('/roles/{id}', [AdminRolesController::class, 'update'])->name('roles.update');
        Route::post('/roles/{id}/delete', [AdminRolesController::class, 'destroy'])->name('roles.destroy');

        // Force delete (Super Admin only): permanently removes a membership
        // application and its artworks/notes/status history, regardless of
        // status. Leaves any already-converted artist/artwork records intact.
        Route::post('/applications/{id}/delete', [AdminApplicationsController::class, 'destroy'])->name('applications.destroy');
    });
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

// ---------------- Custom pages (must stay last: catch-all-ish slug) ----------------
Route::get('/pages/{slug}', [PagesController::class, 'show'])->name('pages.show');
