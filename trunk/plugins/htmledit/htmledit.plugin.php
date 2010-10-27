<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class themleditplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function install() {
    $url = litepublisher::$site->files . '/plugins/' . tplugins::getname(__file__);
    $about = tplugins::getabout(tplugins::getname(__file__));
    $template = ttemplate::instance();
    $template->adminheads['htmleditplugin'] = '<link rel="stylesheet" href="' . $url . '/ed.css" type="text/css" />';
    $template->adminjavascripts['htmleditplugin'] = '<script type="text/javascript">
    var URLpromt = "' . $about['urlpromt'] . '";
    var IMGpromt = "' . $about['imgpromt'] . '";
    </script>
    <script type="text/javascript" src="'. $url . '/ed.js"></script>';
    $template->save();
  }
  
  public function uninstall() {
    $template = ttemplate::instance();
    unset($template->adminheads['htmleditplugin']);
    unset($template->adminjavascripts['htmleditplugin']);
    $template->save();
  }
  
}//class
?>