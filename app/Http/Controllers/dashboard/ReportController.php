<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\User;
use App\Models\Survey;
use App\Models\Order;
use App\Models\Bank;
use App\Models\Car;
class ReportController extends Controller
{
  public function index()
  {
        $sellercount=Seller::where('type','seller')->count();
        $salecount=Seller::where('type','sale')->count();
        $leadercount=Seller::where('type','leader')->count();
        $managercount=Seller::where('type','manager')->count();
        $usercount=User::count();
        $surveycount=Survey::count();
        $ordercount=Order::count();
        $bankcount=Bank::count();
        $carcount=Car::count();
        
    return view('content.reports.reports',compact('sellercount','salecount','leadercount','usercount','managercount','surveycount','ordercount','bankcount','carcount'));
  }
}
