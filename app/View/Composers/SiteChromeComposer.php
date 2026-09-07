<?php

namespace App\View\Composers;

use App\Models\NavigationMenuItem;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use Illuminate\View\View;

/**
 * Shares the site-wide chrome (settings, nav, footer, social links) with the
 * main layout - the Laravel equivalent of ISiteContentService.GetChromeAsync().
 */
class SiteChromeComposer
{
    public function compose(View $view): void
    {
        $navItems = NavigationMenuItem::query()->active()->orderBy('DisplayOrder')->get();

        $view->with([
            'settings' => SiteSetting::current(),
            'mainNav' => $navItems->where('Location', NavigationMenuItem::LOCATION_MAIN_NAV)->values(),
            'footerExplore' => $navItems->where('Location', NavigationMenuItem::LOCATION_FOOTER_EXPLORE)->values(),
            'footerMembership' => $navItems->where('Location', NavigationMenuItem::LOCATION_FOOTER_MEMBERSHIP)->values(),
            'socialLinks' => SocialLink::query()->active()->orderBy('DisplayOrder')->get(),
        ]);
    }
}
