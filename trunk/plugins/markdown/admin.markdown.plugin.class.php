<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmarkdownplugin {

  public static function instance() {
    return getinstance(__class__);
  }

  public function getcontent() {  
$plugin = tmarkdownplugin::instance();

    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $html = THtmlResource::instance();

      $args->formtitle = $about['name'];
      $args->data['$lang.nocontinue'] = $about['nocontinue'];
      $args->data['$lang.deletep'] = $about['deletep'];
$args->nocontinue = $plugin->nocontinue;
      $args->deletep = $plugin->deletep;
return $html->adminform('[checkbox:nocontinue] [checkbox:deletep]', $args);
}

  public function processform() {
$plugin = tmarkdownplugin::instance();
$plugin->nocontinue = isset($_POST['nocontinue']);
$plugin->deletep = isset($_POST['deletep']);
$plugin->save();
}

}//class
?>