<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlazybuttons extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function themeparsed(ttheme $theme) {
    if (strpos($theme->templates['content.post'], 'lazybuttons')) return;
    $theme->templates['content.post'] = str_replace('$post.content', '$post.content' .
    '<div class="lazybuttons"></div>',
    $theme->templates['content.post']);
  }
  
}//class
