<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlinkdescription extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
$this->data['description'] = '';

  }
  
  public function install() {
    $parser = tthemeparser::instance();
    $parser->parsed = $this->themeparsed;
    ttheme::clearcache();
  }
  
  public function uninstall() {
    $parser = tthemeparser::instance();
    $parser->unsubscribeclass($this);
    ttheme::clearcache();
  }

  public function themeparsed(ttheme $theme) {
$s = $this->description;
if ($s && !strpos($theme->templates['index'], $s)) {
$theme->templates['index'] = str_replace('$site.description', $s, $theme->templates['index']);
}
}

}//class