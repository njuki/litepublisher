<?php
class TMetaWidget extends TEventClass {
 
 public function GetBaseName() {
  return 'metafooter';
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function GetWidgetContent($id) {
  global $Options, $Template;
  $lang = &TLocal::$data['default'];
  
  $result = $Template->GetBeforeWidget('meta');
  $class = isset($Template->theme['class']['rss']) ? $Template->theme['class']['rss'] : '';
  $class = empty($class) ? '' : "class=\"$class\"";
 $result .=   "<li $class><a href=\"$Options->rss\" $class>{$lang['rss']}</a></li>
 <li $class><a href=\"$Options->rsscomments\" $class>{$lang['rsscomments']}</a></li>
 <li><a href=\"$Options->foaf\">{$lang['foaf']}</a></li>
 <li><a href=\"$Options->url/profile/\">{$lang['profile']}</a></li>
 <li><a rel=\"sitemap\" href=\"$Options->url/sitemap/\">{$lang['sitemap']}</a></li>\n";
  
  $result .= $Template->GetAfterWidget();
  return $result;
 }
 
}

?>