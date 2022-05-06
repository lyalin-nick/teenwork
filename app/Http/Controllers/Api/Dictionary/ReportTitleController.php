<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\ReportTitle;
use Illuminate\Http\JsonResponse;

class ReportTitleController extends BaseController
{
    /**
     * Получение заголовков для жалобы на пользователя
     *
     * @return JsonResponse
     */
    public function userTitles(): JsonResponse
    {
        $data = ReportTitle::getReportTitles(ReportTitle::USER_TITLES);

        return $this->sendResponse($data, 'Report Titles');
    }

    /**
     * Получение заголовков для жалобы на задачу
     *
     * @return JsonResponse
     */
    public function taskTitles(): JsonResponse
    {
        $data = ReportTitle::getReportTitles(ReportTitle::TASK_TITLES);

        return $this->sendResponse($data, 'Report Titles');
    }
}
