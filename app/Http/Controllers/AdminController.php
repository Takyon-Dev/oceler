<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use View;
use Auth;
use DB;
use Response;
use Session;

class AdminController extends Controller
{
  public function showDashboard()
  {
    return View::make('layouts.admin.dashboard');
  }


  public function showConfigFiles()
  {
    return View::make('layouts.admin.config-files');
  }

}
