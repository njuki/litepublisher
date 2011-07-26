<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminjsmerger extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public function  gethead() {
return parent::gethead() . tuitabs::gethead();
}
  
  public function getcontent() {
    $jsmerger = tjsmerger::instance();
$tabs = new tuitabs();
    $html = $this->html;
    $lang = $this->lang;
    $args = targs::instance();
        $args->formtitle= $lang->jsmergertitle;
foreach ($jsmerger->items as $section => $items) {
$name = $section . '_files';
    $args->$name = implode("\n", $items['files']);
$tab = new tuitabs();
$tab->add($lang->files, "[editor=$name]");
$tabtext = new tuitabs();
foreach ($items['texts'] as $key => $text) {
$name = $section . '_text_' . $key;
$args->$name  = $text;
$tabtext->add($key, "[editor=$name]");
}
$tab->add($lang->text, $tabtext->get());
$tabs->add($section, $tab->get());
}

return  $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $jsmerger = tjsmerger::instance();
$jsmerger->lock();
foreach ($array_keys($jsmerger->items) as $section) {
$keys = array_keys($jsmerger->items[$section]['texts']);
$jsmerger->setfromstring($_POST[$section . _files']);
foreach ($keys as $key) {
$jsmerger->addtext($section, $key, $_POST[$section . '_text_' . $key"]);
}
}
$jsmerger->unlock();
  }
  
}//class