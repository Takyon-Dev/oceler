<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use View;

class PlayerController extends Controller
{
    //
    public function getShow($id) 
    {
    	return View::make('layouts.player.main')->with('id', $id);
    }
}
