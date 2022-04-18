<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\ReportTitle;

class ReportTitleController extends BaseController
{
    public function index()
    {
        $data = ReportTitle::getReportTitles();

        return $this->sendResponse($data, 'Report Titles');
    }
}
