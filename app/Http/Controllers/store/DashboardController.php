<?php

namespace App\Http\Controllers\store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $page_heading = "Store Dashboard";
        return view('store_manager.dashboard', compact('page_heading'));
    }
}
