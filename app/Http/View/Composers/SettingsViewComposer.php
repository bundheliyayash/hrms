<?php

namespace App\Http\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class SettingsViewComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $settings = Cache::remember('app_settings', 60 * 60, function () {
            return Setting::all()->pluck('value', 'key');
        });

        $companyName = $settings->get('company_name', 'Cleansheen');
        $appName = $settings->get('app_name', 'HRMS');
        // If logo is stored as a path in settings, use that, otherwise default
        $logo = $settings->get('app_logo'); 

        $view->with('globalCompanyName', $companyName);
        $view->with('globalAppName', $appName);
        $view->with('globalLogo', $logo);
    }
}
