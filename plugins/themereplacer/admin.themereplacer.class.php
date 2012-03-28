<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminitemsreplacer implements iadmin{
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
$result = '';
    $plugin = titemsreplacer ::i();
    $html = tadminhtml::i();
    $args = targs::i();
$lang = tplugins::getlangabout(__file__);
    $args->formtitle = $lang->formtitle;

if (isset($_GET['action']) && ('add' == $_GET['action'])) {
$args->name = '';
$result .= $html->adminform('[text=name]', $args);
}

if (!empty($_GET['id'])) {
$id = (int) $_GET['id'];
if (isset($plugin->items[$id])) {
    $tabs = new tuitabs();
$args->id = $id;
    
    $tabs->add($lang->new, $html->getinput('text',
    'addtag', '', $lang->addtag) .
    $html->getinput('editor',
    'replace-add', '', $about['replace']) );

    $i = 0;
    foreach ($plugin->items[$id] as $tag => $replace) {
$i++;
      $tabs->add($tag,
      $html->getinput('editor',
      "replace-$i", tadminhtml::specchars($replace), $lang->replace) );
    }
    
    $result .= $html->adminform($tabs->get(), $args);
}
}

$result .= '<ul>';
$adminurl = tadminhtml::getadminlink('/admin/plugins/', 'plugin=' . basename(dirname(__file__)));
$views = tviews::i();
foreach (array_keys($plugin->items) as $id) {
$name= $views->items[$id]['name'];
$result .= "<li><a href='$adminurl&id=$id'>$name</a></li>";
}
$result .= '</ul>';

return $result;
  }
  
  public function processform() {
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $plugin = titemsreplacer ::i();
    $plugin->lock();

    $i = 0;
    foreach ($plugin->items[$id] as $tag => $replace) {
$i++;
$k = "replace-$i";
if (!isset($_POST[$k])) continue;
$v = trim($_POST[$k]);
if ($v) {
$plugin->items[$id][$tag] = $v;
} else {
unset($plugin->items[$id][$tag]);
}
}

    $plugin->unlock();
    ttheme::clearcache();
    return '';
  }
  
}//class