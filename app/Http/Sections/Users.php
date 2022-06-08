<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\PortfolioImage;
use App\Models\PortfolioLink;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Section;

/**
 * Class Users
 *
 * @property \App\Models\AdminUser $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class Users extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = false;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $alias;

    /**
     * Initialize class.
     */
    public function initialize()
    {
//        $page = \AdminNavigation::getPages()->findById('userdata');
//        $page->addPage(
//            $this->makePage(300)->setIcon('fa fa-users')
//        );
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-users');
    }

    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {
        $columns = [
            AdminColumn::text('id', '#')
                ->setHtmlAttribute('class', 'text-center'),
            AdminColumn::image('profile.profileImage.Preview', 'Avatar'),
            AdminColumn::link('profile.first_name', 'First Name'),
            AdminColumn::link('profile.last_name', 'Last Name'),
            AdminColumn::text('phone', 'Phone')
                ->setSearchCallback(function ($column, $query, $search) {
                    return $query->orWhere('phone', 'like', '%' . $search . '%');
                }),
            AdminColumn::text('role', 'Role')
                ->setSearchable(false),
            AdminColumn::text('status', 'Status')
                ->setSearchable(false),

            AdminColumn::text('created_at', 'Created')
                ->setSearchable(false),
        ];

        $display = AdminDisplay::datatablesAsync()
            ->setColumns($columns)
            ->with('profile')
            ->setHtmlAttribute('class', 'table-primary table-hover');

        $display->setColumnFilters([
            null,
            null,
            AdminColumnFilter::text()->setColumnName('profile.first_name')
                ->setPlaceholder('First Name')->setOperator('contains'),
            AdminColumnFilter::text()->setColumnName('profile.last_name')
                ->setPlaceholder('Last Name')->setOperator('contains'),
            AdminColumnFilter::text()->setPlaceholder('Phone')->setOperator('contains'),
            AdminColumnFilter::select([User::ROLE_PERFORMER => User::ROLE_PERFORMER, User::ROLE_EMPLOYER => User::ROLE_EMPLOYER])->setPlaceholder('Role'),
            AdminColumnFilter::select([User::STATUS_ACTIVE => User::STATUS_ACTIVE, User::STATUS_BANNED => User::STATUS_BANNED])->setPlaceholder('Status'),
            null,
        ])->setPlacement('table.header');

        return $display;
    }

    /**
     * @param int|null $id
     * @param array $payload
     *
     * @return FormInterface
     */
    public function onEdit($id = null, $payload = [])
    {
        $userForm = AdminForm::card()->addBody([
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('phone', 'Phone')->setReadonly(true),
                AdminFormElement::text('email', 'Email')->addValidationRule('email')->unique(),
                AdminFormElement::select('role', 'Role',
                    [
                        User::ROLE_EMPLOYER => User::ROLE_EMPLOYER,
                        User::ROLE_PERFORMER => User::ROLE_PERFORMER
                    ]
                )->required(),
                AdminFormElement::select('status', 'Status',
                    [
                        User::STATUS_ACTIVE => User::STATUS_ACTIVE,
                        User::STATUS_BANNED => User::STATUS_BANNED
                    ]
                )->required(),
            ])
        ]);

        $userForm->getButtons()->setButtons([
            'save' => new Save(),
            'save_and_close' => new SaveAndClose(),
            'cancel' => (new Cancel()),
        ]);

        $tabs = AdminDisplay::tabbed();

        $tabs->appendTab($userForm, 'User');
        if (!is_null($id)) {

            $user = User::where('id', '=', $id)->first();
            $profile = $user->profile;

            $profileForm = AdminForm::card()->addBody([
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::text('profile.first_name', 'First Name'),
                    AdminFormElement::text('profile.last_name', 'Last Name'),
                    AdminFormElement::date('profile.date_of_birth', 'Date of birth'),
                    AdminFormElement::textarea('profile.about', 'About'),
                    AdminFormElement::select('profile.status', 'Status', Profile::getStatuses()),
                    AdminFormElement::text('profile.address', 'Address'),

                    AdminFormElement::number('profile.number_performer_tasks', 'Number performer tasks')->setMin(0),
                    AdminFormElement::number('profile.number_employer_tasks', 'Number employer tasks')->setMin(0),
                    AdminFormElement::text('profile.rating', 'Rating'),
                ])]);

            $profileForm->getButtons()->setButtons([
                'save' => new Save(),
                'save_and_close' => new SaveAndClose(),
                'cancel' => (new Cancel()),
            ]);


            $profilePhotoForm = AdminForm::card()->addBody([
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::image('profile.profileImage.Photo', 'Image')
                        ->setUploadPath(function (\Illuminate\Http\UploadedFile $file) {
                            return "storage/tmp";
                        })
                        ->setSaveCallback(function ($file, $path, $filename, $settings) use ($profile) {
                            if ($profile->uploadProfileImage($file)) {
                                $profile->refresh();
                                $image = $profile->profileImage;
                                return ['path' => $image->getFullPath(), 'value' => $image->getLink()];
                            }
                            return ['path' => $path, 'value' => $path];
                        })
                ])
            ]);

            $profilePhotoForm->getButtons()->setButtons([
                'save' => new Save(),
                'save_and_close' => new SaveAndClose(),
                'cancel' => (new Cancel()),
            ]);

            //$tasks = AdminSection::getModel(Task::class)->fireDisplay(['user_id' => $id]);

            $portfolio_photos = AdminSection::getModel(PortfolioImage::class)->fireDisplay(['profile_id' => $profile->id]);
            $portfolio_links = AdminSection::getModel(PortfolioLink::class)->fireDisplay(['profile_id' => $profile->id]);

            $tabs->appendTab($profileForm, 'Profile');

            $tabs->appendTab($profilePhotoForm, 'Profile Photo');

            $tabs->appendTab($portfolio_photos, 'Portfolio Images');
            $tabs->appendTab($portfolio_links, 'Portfolio Links');
        }
        return $tabs;
    }

    /**
     * @return FormInterface
     */
    public function onCreate($payload = [])
    {
        return $this->onEdit(null, $payload);
    }

    /**
     * @return bool
     */
    public function isDeletable(Model $model)
    {
        return true;
    }

    /**
     * @return void
     */
    public function onRestore($id)
    {
        // remove if unused
    }
}
