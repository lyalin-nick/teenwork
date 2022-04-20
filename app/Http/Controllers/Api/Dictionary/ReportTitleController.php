<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\ReportTitle;

class ReportTitleController extends BaseController
{
    public function userTitles()
    {
        $data = ReportTitle::getReportTitles(ReportTitle::USER_TITLES);

        return $this->sendResponse($data, 'Report Titles');
    }

    public function taskTitles()
    {
        $data = ReportTitle::getReportTitles(ReportTitle::TASK_TITLES);

        return $this->sendResponse($data, 'Report Titles');
    }
}
