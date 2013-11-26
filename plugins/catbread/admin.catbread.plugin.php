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
    $lang = tplugins::getnamelang('catbread');
    $html= tadminhtml::i();
    $args = new targs();
$args->add($plugin->tml);
$args->showchilds = $plugin->showchilds;
$args->showsame = $plugin->showsame;

      $args->sort = tadminhtml::array2combo(tlocal::admin()->ini['sortnametags'], $widget->items[$id]['sortname']);
      $args->idwidget = $id;
      $args->data['$lang.invertorder'] = $about['invertorder'];
      $args->formtitle = $lang->formtitle;
      return $html->adminform('
[text=items]
[text=item]
[text=active]

[checkbox=showchilds]
[text=childitems]
[text=childitem]
      [combo=sort]
      [checkbox=showsubitems]
      [checkbox=showcount]
      [text=maxcount]

[checkbox=showsame]
', $args);
  }
  
  public function processform()  {
      extract($_POST, EXTR_SKIP);
$plugin = catbread::i();
$plugin->showchilds = isset($showchilds);
$plugin->showsame = isset($showsame);
foreach ($plugin->tml as $k => $v) {
$plugin->tml[$k] = trim($_POST[$k]);
}

      $item = $widget->items[$id];

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