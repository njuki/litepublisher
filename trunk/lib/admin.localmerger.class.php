<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlocalmerger extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function  gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getcontent() {
    $merger = tlocalmerger::instance();
    $tabs = new tuitabs();
    $html = $this->html;
    $lang = tlocal::instance('views');
    $args = targs::instance();
    $args->formtitle= $lang->mergertitle;
    foreach ($merger->items as $section => $items) {
      $tab = new tuitabs();
      $tab->add($lang->files, $html->getinput('editor',
      $section . '_files', tadminhtml::specchars(implode("\n", $items['files'])), $lang->files));
      $tabtext = new tuitabs();
      foreach ($items['texts'] as $key => $text) {
        $tabtext->add($key, $html->getinput('editor',
        $section . '_text_' . $key, tadminhtml::specchars($text), $key));
      }
      $tab->add($lang->text, $tabtext->get());
      $tabs->add($section, $tab->get());
    }

      $tabs->add('HTML', $html->getinput('editor',
      'adminhtml_files', tadminhtml::specchars(implode("\n", $merger->html)), $lang->files));

        return  $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $merger = tlocalmerger::instance();
    $merger->lock();
    //$merger->items = array();
    //$merger->install();
    foreach (array_keys($merger->items) as $name) {
      $keys = array_keys($merger->items[$name]['texts']);
      $merger->setfiles($name, $_POST[$name . '_files']);
      foreach ($keys as $key) {
        $merger->addtext($name, $key, $_POST[$name . '_text_' . $key]);
      }
    }

$merger->html = explode("\n", trim($_POST['adminhtml_files']));
    $merger->unlock();
  }
  
}//class