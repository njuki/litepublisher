<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpoltypes extends titems {

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = false;
    parent::create();
$this->basename = 'polls' . DIRECTORY_SEPARATOR . 'types';
  }

public function set(array $item) {
if (!isset($item['result'])) $item['result'] = $this->result;
if (!isset($item['itemresult'])) $item['result'] = $this->itemresult;

$this->items[$item['type']] = $item;
$this->save();
}

public function getmicroformat($idpoll) {
$item = tpolls::i()->getitem($idpoll);
    if ($item['rate'] ==  0) return '';

$args = new targs();
      $args->votes = $item['total'];
      $args->rate =1 + $poll['rate'] / 10;
      $args->worst = 1;
      $args->best = count($items);
return ttheme::i()->parsearg($this->tml_microformat, $args);
}
    
public function build($type, $title, array $items) {
if (!isset($this->items[$type])) $this->error(sprintf('The "%s" type not exists', $type));
if (count($items) == 0) $this->error('Empty poll items');

    $theme = ttheme::i();
    $args = new targs();
    $args->id = '$id';
$args->type = $type;
    $args->title = $title;
    $tmlitem = $this->items[$type]['item'];
    $itemresult = $this->items[$type]['itemresult'];
$pollitems = '';
$resultitems = '';
    foreach ($items as $index => $itemtext) {
      $args->checked = 0 == $index;
      $args->index = $index;
      $args->indexplus = $index + 1;
      $args->text = $itemtext;
      $pollitems .= $theme->parsearg($tmlitem, $args);
      $resultitems .= $theme->parsearg($itemresult, $args);
    }

$args->items = $pollitems;
$args->resultitems = $resultitems;
$args->id = '$id';
$args->type = $type;
    $args->title = $title;

return array(
'type' => $type,
'title' => $title,
'items' => $items,
'tml' => $theme->parsearg($this->items[$type]['items'], $args),
'result' => $theme->parsearg($this->items[$type]['result'], $args)
));
}  

}//class