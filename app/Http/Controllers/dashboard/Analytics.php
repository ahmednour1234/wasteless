<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Bundle;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Order;
use App\Models\Pdf;
use App\Models\Project;
use App\Models\Role;
use App\Models\Survey;
use App\Models\SurveyDetails;
use App\Models\User;
use Carbon\Carbon;


class Analytics extends Controller
{
  public function index()
  {
    $storesCount = Company::count();
    $admincount = User::count();
    $Bundels = Bundle::count();
    $Branches = Branch::count();
    $usercount = User::count();
    $companynotactives=Company::where('active',0)->paginate(10);
    $ordercount = Project::whereDate('created_at', Carbon::today())->count();
    $admincount = User::whereDate('created_at', Carbon::today())->count();
    $surveycount =  Pdf::whereDate('created_at', Carbon::today())->count();


    return view('content.dashboard.dashboards-analytics', compact('storesCount', 'admincount', 'Bundels', 'Branches', 'usercount', 'ordercount', 'surveycount','companynotactives'));
  }
}
