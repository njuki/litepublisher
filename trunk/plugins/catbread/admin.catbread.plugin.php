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
$args->showhome = $plugin->showhome;
$args->showchilds = $plugin->showchilds;
$args->showsame = $plugin->showsame;

      $args->sort = tadminhtml::array2combo(tlocal::admin()->ini['sortnametags'], $widget->items[$id]['sortname']);
      $args->idwidget = $id;
      $args->data['$lang.invertorder'] = $about['invertorder'];
      $args->formtitle = $lang->formtitle;
      return $html->adminform('
[checkbox=showhome]

[text=item]
[text=active]
[text=child]
[editor=items]

[checkbox=showchilds]
[text=childitems]
[text=childitem]
[text=childsubitems]
      [combo=sort]

[checkbox=showsame]
[text=sameitems]
[text=sameitem]
', $args);
  }
  
  public function processform()  {
      extract($_POST, EXTR_SKIP);
$plugin = catbread::i();
$plugin->showhome = isset($showchilds);
$plugin->showchilds = isset($showchilds);
$plugin->showsame = isset($showsame);
foreach ($plugin->tml as $k => $v) {
$plugin->tml[$k] = trim($_POST[$k]);
}

      $plugin->save();
      return '';
    }
    
  }
  
}//class