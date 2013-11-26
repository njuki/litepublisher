<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

clas admincatbread implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
$plugin = catbread::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $html= tadminhtml::i();
    $args = new targs();

      $args->sort = tadminhtml::array2combo(tlocal::admin()->ini['sortnametags'], $widget->items[$id]['sortname']);
      $args->idwidget = $id;
      $args->data['$lang.invertorder'] = $about['invertorder'];
      $args->formtitle = $widget->gettitle($id);
      return $html->adminform('
      [combo=sort]
      [checkbox=showsubitems]
      [checkbox=showcount]
      [text=maxcount]
      [hidden=idwidget]',
      $args);
    }
    $tags = array();
    foreach ($widget->items as $id => $item) {
      $tags[] = $item['idtag'];
    }
    $args->formtitle = $about['formtitle'];
    return $html->adminform(tposteditor::getcategories($tags), $args);
  }
  
  public function processform()  {
$plugin = catbread::i();
      $item = $widget->items[$id];
      extract($_POST, EXTR_SKIP);
      $item['maxcount'] = (int) $maxcount;
      $item['showcount'] = isset($showcount);
      $item['showsubitems'] = isset($showsubitems);
      $item['sortname'] = $sort;
      $widget->items[$id] = $item;
      $plugin->save();
      return '';
    }
    
  }
  
}//class