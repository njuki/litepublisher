<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpagenator3000 extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function gethead() {
    $url = litepublisher::$site->files . '/plugins/' . tplugins::getname(__file__);
    $about = tplugins::getabout(tplugins::getname(__file__));
    $result = '<link rel="stylesheet" href="' . $url . '/ed.css" type="text/css" />';
    $result .= '<script type="text/javascript">';
    $result .= '    var URLpromt = "' . $about['urlpromt'] . '";';
    $result .= ' var IMGpromt = "' . $about['imgpromt'] . '";';
    $result .= '</script>';
    $result .= '<script type="text/javascript" src="'. $url . '/ed.js"></script>';
    return $result;
  }

public function themeparsed(ttheme $theme) {
$url = litepublisher::$site->files . '/plugins/' . basename(dirname(__file__)) . '/';
$about = tplugins::getabouttplugins::getname(__file__));(
$head = '		<script type="text/javascript">
  $(document).ready(function() {
    $('<link rel="stylesheet" type="text/css" href="' . $url . 'paginator3000.css" />').appendTo("head");
        $.getScript("' . $url . 'paginator3000.js", function() {
    $('#paginator').paginator({
        pagesTotal : %%count%%,
        pagesSpan : %%perpage%%,
        pageCurrent : %%page%%,
        baseUrl : function(num) {
if (nul == 1) return '%%url%%';
            var str = window.location.search;
            if (str) {
                if(str.indexOf('/page/') + 1) {
                    var loc = str.replace('/page/[0-9]+/', '/page/' + num);
                } else {
                    var loc = str + '/page/' + num + '/';
                }
            } else {
                var loc = '/page/' + num + '/';
            }
            window.location.search = loc;
        },

        returnOrder : false,
        lang : {
            next : "Следующая",
            last : "Последняя",
            prior : "Предыдущая",
            first : "Первая",
            arrowRight : String.fromCharCode(8594),
            arrowLeft : String.fromCharCode(8592)
        }
});
});
});
</script>';

$theme->templates['content.navi'] = $head . $theme->templates'content.navi'];
$theme->save();
}
  
  public function install() {
  $parser = tthemeparser::instance();
  $parser->parsed = $this->themeparsed;
  ttheme::clearcache();
  }
  
  public function uninstall() {
  $parser = tthemeparser::instance();
  $parser->unsubscribeclass($this);
  }
  
}//class
?>