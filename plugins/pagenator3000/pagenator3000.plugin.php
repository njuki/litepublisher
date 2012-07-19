<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpagenator3000 extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function themeparsed(ttheme $theme) {
    if (strpos($theme->templates['content.navi'], 'paginator3000.min.js')) return;

    $script = '<script type="text/javascript"><!--
        $(document).ready(function() {
          var tag = $("#paginator, .navigation");
          tag.addClass("paginator");
          tag.paginator({
            pagesTotal : $count,
            pagesSpan : $perpage,
            pageCurrent : $page - 1,
            baseUrl : function(page) {
              window.location= ++page == 1 ? "$link" :
              "$pageurl" + page + "/";
            }
});
});
       </script>';
    
    $theme->templates['content.navi'] = $head . $theme->templates['content.navi'];
  }
  
}//class