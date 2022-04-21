<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Language;
use App\Models\ReportTitle;
use App\Models\User;
use SleepingOwl\Admin\Providers\AdminSectionsServiceProvider as ServiceProvider;

class AdminSectionsServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $sections = [
        Category::class => 'App\Http\Sections\Categories',
        Language::class => 'App\Http\Sections\Languages',
        Faq::class => 'App\Http\Sections\Faqs',
        ReportTitle::class => 'App\Http\Sections\ReportTitles',
        AdminUser::class => 'App\Http\Sections\AdminUsers',
        //User::class => 'App\Http\Sections\Users',
    ];

    /**
     * Register sections.
     *
     * @param \SleepingOwl\Admin\Admin $admin
     * @return void
     */
    public function boot(\SleepingOwl\Admin\Admin $admin)
    {
    	//

        parent::boot($admin);
    }
}
