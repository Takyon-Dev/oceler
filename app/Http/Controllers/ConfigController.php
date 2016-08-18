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

    $config_json = json_decode(file_get_contents($request->config_file), true);

    foreach ($config_json as $config) {

      switch($config['type']){
        case 'network':
          \oceler\Network::addNetwork($config);
          break;

        case 'factoid':
          \oceler\Factoidset::addFactoidset($config);
          break;

        case 'names':
          \oceler\Nameset::addNameset($config);
          break;
      }
    }


  return redirect('\admin\config-files');

  }


}
