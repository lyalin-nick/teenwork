<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\ReportTitle;
use App\Models\TaskReport;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\SaveAndCreate;
use SleepingOwl\Admin\Navigation\Badge;
use SleepingOwl\Admin\Section;

/**
 * Class TaskReports
 *
 * @property \App\Models\TaskReport $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class TaskReports extends Section implements Initializable
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
        $new_report_count = TaskReport::where('is_new', 1)->count();

        $this->addToNavigation()->setPriority(100)
            ->setIcon('fa fa-flag')
            ->setBadge(new Badge($new_report_count));
    }

    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {
        $columns = [
            AdminColumn::text('id', '#')->setWidth('50px')->setHtmlAttribute('class', 'text-center'),
            AdminColumn::text('reportTitle.name', 'Title'),
//            AdminColumn::custom('Task', function ($model) {
//                return '<a href="' . route('admin.model.edit', ['adminModel' => 'tasks', 'adminModelId' => $model->task->id]) . '">' . $model->task->name . '</a>';
//            }),
            AdminColumn::relatedLink('task.name', 'Task'),
            AdminColumn::relatedLink('reporter.phone', 'Reporter', 'reporter.profile.first_name'),
//            AdminColumn::text('reporter.profile.FullName', 'Reporter'),
            AdminColumnEditable::checkbox('is_new', 'Is new')->setOrderable('is_new'),
            AdminColumn::text('created_at', 'Created')
                ->setSearchable(false)
            ,
        ];

        $display = AdminDisplay::table()
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover th-center');

        $display->setColumnFilters([
            null,
            \AdminColumnFilter::select()
                ->setModelForOptions(\App\Models\ReportTitle::class, 'name')
                ->setLoadOptionsQueryPreparer(function ($element, $query) {
                    return $query->where('flag', ReportTitle::TASK_TITLES);
                })
                //->setDisplay('name')
                ->setColumnName('title_id')
                ->setPlaceholder('All names')
            ,
            null,
            null,
            null,
            null,
        ]);
        $display->getColumnFilters()->setPlacement('card.heading');

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
                AdminFormElement::text('reportTitle.name', 'Report title')->setReadonly(true),
                AdminFormElement::text('task.name', 'Task name')->setReadonly(true),
                AdminFormElement::text('reporter.profile.FullName', 'User name')->setReadonly(true),
                AdminFormElement::textarea('text', 'Report text')->setReadonly(true),
                AdminFormElement::datetime('created_at')->setVisible(true)->setReadonly(false),
            ]),
        ]);

        $form->getButtons()->setButtons([
            'save' => new Save(),
            'save_and_close' => new SaveAndClose(),
            'save_and_create' => new SaveAndCreate(),
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
