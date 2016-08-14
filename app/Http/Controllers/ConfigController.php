<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;

class ConfigController extends Controller
{
  public function uploadConfig(Request $request)
  {

    $this->validate($request, [
        'config_file' => 'required|max:1024',
    ]);
    $file = file_get_contents($request->config_file);
    echo $file;
    $file = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $file);
    $config_json = json_decode($file, true);
    dump($config_json);
    echo json_last_error();
  }
}
