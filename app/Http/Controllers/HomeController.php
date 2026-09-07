<?php

namespace App\Http\Controllers;

use App\Models\AboutContent;
use App\Models\Activity;
use App\Models\Category;
use App\Models\SiteBanner;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\WhyJoinBenefit;
use App\Models\WhyJoinPhoto;
use App\Models\WhyJoinSection;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Mirrors HomeController.Index in the .NET app - Section 8 Homepage Content Priority. */
    public function index(): View
    {
        $homepageActivities = $this->homepageActivities(6);

        return view('home.index', [
            'homepageActivities' => $homepageActivities,
            'banners' => SiteBanner::query()->enabled()->orderBy('DisplayOrder')->get(),
            'testimonials' => Testimonial::query()->published()->orderBy('DisplayOrder')->take(3)->get(),
            'about' => AboutContent::current(),
            'whyJoin' => WhyJoinSection::query()->first() ?? new WhyJoinSection,
            'whyJoinBenefits' => WhyJoinBenefit::query()->active()->orderBy('DisplayOrder')->get(),
            'whyJoinPhotos' => WhyJoinPhoto::query()->active()->orderBy('DisplayOrder')->get(),
            'categories' => Category::query()->active()->orderBy('DisplayOrder')->get(),
        ]);
    }

    public function about(): View
    {
        $about = AboutContent::current();
        $coreValues = $about?->coreValues()->active()->get() ?? collect();

        return view('pages.about', ['about' => $about, 'coreValues' => $coreValues]);
    }

    public function contact(): View
    {
        return view('pages.contact', ['settings' => SiteSetting::current()]);
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    /**
     * Homepage Featured activities first (in FeaturedOrder), topped up with
     * the latest published activities - mirrors
     * GroupActivitiesService.GetHomepageActivitiesAsync in the .NET app.
     */
    private function homepageActivities(int $max): Collection
    {
        $featured = Activity::query()->published()->featured()
            ->orderBy('FeaturedOrder')
            ->orderByDesc('ActivityDate')
            ->withCount(['visiblePhotos as PhotoCount'])
            ->take($max)
            ->get();

        if ($featured->count() < $max) {
            $usedIds = $featured->pluck('Id');
            $fill = Activity::query()->published()
                ->whereNotIn('Id', $usedIds)
                ->orderByDesc('ActivityDate')
                ->withCount(['visiblePhotos as PhotoCount'])
                ->take($max - $featured->count())
                ->get();

            return $featured->concat($fill);
        }

        return $featured;
    }
}
