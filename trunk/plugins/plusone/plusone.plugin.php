<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tplusoneplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
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
    if (strpos($theme->templates['content.post'], 'g-plusone')) return;
$lang = litepublisher::$options->language == 'en' ? '' : sprintf('{lang: \'%s\'}', litepublisher::$options->language);
    $theme->templates['content.post'] = str_replace('$post.content', '$post.content' . 
'<script type="text/javascript" src="https://apis.google.com/js/plusone.js">'. $lang . '</script>' .
'<div class="g-plusone"></div>',
 $theme->templates['content.post']);
}

}//class
