<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Language;
use App\Models\PortfolioImage;
use App\Models\PortfolioLink;
use App\Models\ReportTitle;
use App\Models\Task;
use App\Models\TaskReport;
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
        Task::class => 'App\Http\Sections\Tasks',
        PortfolioImage::class => 'App\Http\Sections\PortfolioImages',
        PortfolioLink::class => 'App\Http\Sections\PortfolioLinks',
        TaskReport::class => 'App\Http\Sections\TaskReports',
        User::class => 'App\Http\Sections\Users',
        AdminUser::class => 'App\Http\Sections\AdminUsers',
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
