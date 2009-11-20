<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class TMetaWidget extends TEventClass {
  
  public function GetBaseName() {
    return 'metafooter';
  }
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function GetWidgetContent($id) {
    global $Options;
    $Template = TTemplate::Instance();
    $lang = &TLocal::$data['default'];
    
    $result = '';
    $class = isset($Template->theme['class']['rss']) ? $Template->theme['class']['rss'] : '';
    $class = empty($class) ? '' : "class=\"$class\"";
  $result .=   "<li $class><a href=\"$Options->url/rss/\" $class>{$lang['rss']}</a></li>
  <li $class><a href=\"$Options->url/comments/\" $class>{$lang['rsscomments']}</a></li>
  <li><a href=\"$Options->url/foaf.xml\">{$lang['foaf']}</a></li>
  <li><a href=\"$Options->url/profile/\">{$lang['profile']}</a></li>
  <li><a rel=\"sitemap\" href=\"$Options->url/sitemap/\">{$lang['sitemap']}</a></li>\n";
    
    return $result;
  }
  
}

?>