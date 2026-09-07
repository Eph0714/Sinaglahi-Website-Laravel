<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityPhoto;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\MembershipApplication;
use Illuminate\View\View;

/** Mirrors Areas/Admin/Controllers/DashboardController in the .NET app. */
class DashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth();

        return view('admin.dashboard.index', [
            'totalArtists' => Artist::query()->count(),
            'pendingRegistrations' => Artist::query()->where('AccountStatus', Artist::STATUS_PENDING)->count(),
            'pendingJoinApplications' => MembershipApplication::query()
                ->whereIn('Status', [MembershipApplication::STATUS_PENDING_REVIEW, MembershipApplication::STATUS_UNDER_REVIEW])
                ->count(),
            'verifiedArtists' => Artist::query()->where('IsVerified', true)->count(),
            'activeArtists' => Artist::query()->where('AccountStatus', Artist::STATUS_ACTIVE)->count(),
            'inactiveArtists' => Artist::query()->where('AccountStatus', Artist::STATUS_INACTIVE)->count(),
            'totalArtworks' => Artwork::query()->count(),
            'pendingArtworkSubmissions' => Artwork::query()->where('Status', Artwork::STATUS_PENDING_REVIEW)->count(),
            'publishedArtworks' => Artwork::query()->where('Status', Artwork::STATUS_PUBLISHED)->count(),
            'newApplicationsThisMonth' => MembershipApplication::query()
                ->where('SubmittedAt', '>=', $monthStart)
                ->where('Status', '!=', MembershipApplication::STATUS_DRAFT)
                ->count(),
            'totalActivities' => Activity::query()->count(),
            'publishedActivities' => Activity::query()->where('IsPublished', true)->count(),
            'draftActivities' => Activity::query()->where('IsPublished', false)->count(),
            'featuredActivities' => Activity::query()->where('IsFeatured', true)->count(),
            'totalActivityPhotos' => ActivityPhoto::query()->count(),
            'activitiesThisYear' => Activity::query()->whereYear('ActivityDate', now()->year)->count(),
        ]);
    }
}
