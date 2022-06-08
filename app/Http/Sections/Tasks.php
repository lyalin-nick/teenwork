<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Category;
use App\Models\Task;
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
 * Class Tasks
 *
 * @property \App\Models\Task $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class Tasks extends Section implements Initializable
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
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-tasks');
    }

    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {
        $columns = [
            AdminColumn::text('id', '#')->setHtmlAttribute('class', 'text-center')->setWidth(100),
            AdminColumn::link('name', 'Name')
                ->setSearchCallback(function ($column, $query, $search) {
                    return $query->orWhere('name', 'like', '%' . $search . '%');
                })
                ->setOrderable(function ($query, $direction) {
                    $query->orderBy('created_at', $direction);
                }),
//            AdminColumn::text('user.profile.first_name', 'User First Name'),
            AdminColumn::text('user.profile.last_name', 'User Last Name', 'user.profile.first_name'),
            AdminColumn::text('price', 'Price', 'payment_type'),
            AdminColumn::text('status', 'Status'),
            AdminColumn::text('start_date', 'Start Date/Time', 'start_time')->setSearchable(false),
        ];


        $display = AdminDisplay::datatablesAsync()
            ->setName('tasks_table')
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover th-center');

        $display->setColumnFilters([

            AdminColumnFilter::text()->setColumnName('id')
                ->setPlaceholder('Number')->setOperator('contains'),
            AdminColumnFilter::text()->setColumnName('name')
                ->setPlaceholder('Task Name')->setOperator('contains'),
//            AdminColumnFilter::text()->setColumnName('user.profile.last_name')
//                ->setPlaceholder('Last Name')->setOperator('contains'),
            AdminColumnFilter::range()->setFrom(
                AdminColumnFilter::text()->setColumnName('user.profile.last_name')
                ->setPlaceholder('Last Name')->setOperator('contains'),
            )->setTo(
                AdminColumnFilter::text()->setColumnName('user.profile.first_name')
                    ->setPlaceholder('First Name')->setOperator('contains'),
            ),
            AdminColumnFilter::text()->setPlaceholder('Price')->setOperator('contains'),
            AdminColumnFilter::select(Task::getStatusLabels())->setPlaceholder('Status')->multiple(),
            AdminColumnFilter::range()->setFrom(
                AdminColumnFilter::date()->setPlaceholder('Start date from')->setFormat('Y-m-d H:i:s')
            )->setTo(
                AdminColumnFilter::date()->setPlaceholder('Start Date to')->setFormat('Y-m-d H:i:s')
            ),
        ]);
        $display->getColumnFilters()->setPlacement('card.heading');

        if (isset($payload['user_id'])) {
            $display->setApply(function ($query) use ($payload) {
                $query->where('user_id', '=', $payload['user_id']);
            });
        }

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
        $form = AdminForm::card()->addBody([
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('user.profile.FullName', 'User name')->setReadonly(true),
                AdminFormElement::select('category_id', 'Category')
                    ->setModelForOptions(Category::class, 'name')
                    ->setLoadOptionsQueryPreparer(function ($element, $query) {
                        return $query->where('category_id', '!=', 0);
                    })->required(),
                AdminFormElement::text('name', 'Name')->required(),
                AdminFormElement::textarea('description', 'Description'),
                AdminFormElement::textarea('result', 'Result'),
                AdminFormElement::date('start_date', 'Start Date'),
                AdminFormElement::time('start_time', 'Start Time'),
                AdminFormElement::number('amount_of_workers', 'Amount of Performer')->setMin(1)->setMax(50),
                AdminFormElement::number('minimum_age', 'Age')->setMin(12)->setMax(40),
                AdminFormElement::number('price', 'Price')->setMin(1),
                AdminFormElement::text('payment_type', 'Payment Type'),
                AdminFormElement::checkbox('safe_deal', 'Safe deal'),
                AdminFormElement::checkbox('hot_work', 'Hot work'),
                AdminFormElement::checkbox('account_verified', 'Account verified'),
                AdminFormElement::select('status', 'Status', Task::getStatusLabels()),
                AdminFormElement::text('views_number', 'Views')->setReadonly(true),
                AdminFormElement::datetime('created_at')
                    ->setVisible(true)
                    ->setReadonly(false),
            ])
        ]);

        $form->getButtons()->setButtons([
            'save' => new Save(),
            'save_and_close' => new SaveAndClose(),
            'cancel' => (new Cancel()),
        ]);

        return $form;
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
