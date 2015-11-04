<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use View;
use Auth;
use oceler\SolutionCategory;
use oceler\User;

class PlayerController extends Controller
{
    //
    public function getShow() 
    {
    	$id = Auth::id();

    	$solution_categories = SolutionCategory::all();
    	$users = User::all();

    	return View::make('layouts.player.main')->with('id', $id)->with('users', $users)->with('solution_categories', $solution_categories);
    }

    public function __construct()
    {
    	$this->middleware('auth');

    }
}
