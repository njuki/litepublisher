<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tgoogleanalitic extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['user'] = '';
    $this->data['se'] = '';
  }
  
  public function getcontent() {
    $tml = '[text:user]
    [editor:se]';
    $html = THtmlResource::instance();
    $args = targs::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.user'] = $about['user'];
    $args->data['$lang.se'] = $about['se'];
    $args->user = $this->user;
    $args->se = $this->se;
    return $html->adminform($tml, $args);
  }
  
  public function processform() {
    $this->user = $_POST['user'];
    $this->se = $_POST['se'];
    $this->save();
    $filename = litepublisher::$paths->files . 'googleanalitic.js';
    $template = ttemplate::instance();
    if ($this->user == '') {
      $template->deletefromhead($template->getjavascript('/files/googleanalitic.js'));
      @unlink($filename);
    } else {
      $s = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'googleanalitic.js');
      $s = sprintf($s, $this->user, $this->se);
      file_put_contents($filename, $s);
      @chmod($filename, 0666);
      $template->addtohead($template->getjavascript('/files/googleanalitic.js'));
    }
    litepublisher::$urlmap->clearcache();
  }
  
  public function install() {
    $this->se = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . litepublisher::$options->language . 'se.js');
    $this->save();
  }
  
  public function uninstall() {
    ttemplate::instance()->deletejavascript('googleanalitic');
  }
  
}//class
?>