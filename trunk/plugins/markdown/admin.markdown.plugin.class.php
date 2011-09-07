<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
    $html = tadminhtml::instance();

      $args->formtitle = $about['name'];
      $args->data['$lang.deletep'] = $about['deletep'];
      $args->deletep = $plugin->deletep;
return $html->adminform('[checkbox=deletep]', $args);
}

  public function processform() {
$plugin = tmarkdownplugin::instance();
$plugin->deletep = isset($_POST['deletep']);
$plugin->save();
}

}//class
