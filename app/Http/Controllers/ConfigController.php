<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
    $errors = [];

    foreach ($config_json as $config) {

      // Save the file to the storage/config directory
      File::move($request->config_file, storage_path().'/config-files/'.$config['name'].'.json', $request->config_file);

      switch($config['type']){
        case 'network':
          try{
            \oceler\Network::addNetworkFromConfig($config);
          }
          catch(\Exception $e){
            $errors[] = $e->getMessage();
          }
          break;

        case 'factoid':
          try{
            \oceler\Factoidset::addFactoidsetFromConfig($config);
          }
          catch(\Exception $e){
            $errors[] = $e->getMessage();
          }
          break;

        case 'names':
          try{
            \oceler\Nameset::addNamesetFromConfig($config);
          }
          catch(\Exception $e){
            $errors[] = $e->getMessage();
          }
          break;

        case 'trial':
          try{
            \oceler\Trial::addTrialFromConfig($config);
          }
          catch(\Exception $e){
            $errors[] = $e;
          }
          break;
      }
    }

  return back()->withErrors($errors);

  }

  public function deleteConfig($type, $id)
  {

    if($type == 'factoidset'){

      $config = \oceler\Factoidset::find($id);
      $config->delete();

    }

    if($type == 'network'){

      $config = \oceler\Network::find($id);
      $config->delete();

    }

    if($type == 'nameset'){

      $config = \oceler\Nameset::find($id);
      $config->delete();

    }

    return redirect('\admin\config-files');
  }

  public function viewConfig($name)
  {
    $config = storage_path().'/config-files/'.$name.'.json';

    $fh = fopen($config, 'r');
    $display = nl2br(fread($fh, 25000));

    return \Response::make($display, 200);

  }


}
