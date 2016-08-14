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
    $config_json = json_decode(file_get_contents($request->config_file), true);
    dump($config_json);
  }
}
