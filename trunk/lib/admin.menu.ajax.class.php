<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxmenueditor extends tajaxposteditor  {
  
  public static function instance() {
    return getinstance(__class__);
  }

public function install() {
litepublisher::$urlmap->addget('/admin/ajaxmenueditor.htm', get_class($this));
}
  
  public function request($arg) {
if ($err = self::auth()) return $err;
return $this->getcontent();
}

public function getcontent() {
    $id = tadminhtml::idparam();
      $menus = tmenus::instance();
      if (($id != 0) && !$menus->itemexists($id)) return self::error403();
    $menu = tposttmenu:instance($id);
    if ((litepublisher::$options->group == 'author') && (litepublisher::$options->user != $menu->author)) return self::error403();
    if (($id > 0) && !$tags->itemexists($id)) return self::error403();

$theme = tview::instance(tviews::instance()->defaults['admin'])->theme;
   $html = tadminhtml ::instance();
    $html->section = 'menu';
$lang = tlocal::instance('menu');

switch ($_GET['get']) {
case 'view':
$result = tadminviews::getcomboview($menu->idview);
if ($icons = tadminicons::getradio($menu->icon)) {
$result .= $html->h2->icons;
$result .= $icons;
}
break;

case 'seo':
$args = targs::instance();
$args->url = $menu->url;
$args->keywords = $menu->keywords;
$args->description = $menu->description;
$result = $html->parsearg('[text=url] [text=description] [text=keywords]', $args);
break;

case 'text':
$result = $this->geteditor('raw', $menu->rawcontent);
break;

default:
$result = var_export($_GET, true);
}
return turlmap::htmlheader(false) . $result;
}

}//class
?>