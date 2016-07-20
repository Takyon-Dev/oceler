<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;

class AdminController extends Controller
{
  public function showDashboard()
  {
    return ('[ADMIN DASHBOARD]');
  }
}
