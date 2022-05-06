<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\PortfolioImage;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Section;

/**
 * Class PortfolioImages
 *
 * @property \App\Models\PortfolioImage $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class PortfolioImages extends Section implements Initializable
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
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-camera');
    }

    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {
        $columns = [
            AdminColumn::image('preview', 'Photo')->setSearchable(false),
            AdminColumn::text('profile.FullName', 'User')->setSearchable(false),
        ];

        $display = AdminDisplay::table()
            ->setColumns($columns)

            ->setHtmlAttribute('class', 'table-primary table-hover th-center');

        if (isset($payload['profile_id'])) {
            $display->setApply(function ($query) use ($payload) {
                $query->where('profile_id', '=', $payload['profile_id']);
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
                AdminFormElement::image('photo', 'Image')
                    ->setUploadPath(function (\Illuminate\Http\UploadedFile $file) {
                        return "storage/tmp";
                    })
                    ->setSaveCallback(function ($file, $path, $filename, $settings) use ($id) {
                        $portfolio_image = PortfolioImage::where('id', '=', $id)->first();
                        if ($portfolio_image) {
                            $portfolio_image->updateImage($file);
                            return ['path' => $portfolio_image->getFullPath(), 'value' => $portfolio_image->getLink()];
                        }
                        return ['path' => $path, 'value' => $path];
                    })
            ])
        ]);

        $form->getButtons()->setButtons([]);

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
