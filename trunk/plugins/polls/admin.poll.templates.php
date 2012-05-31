<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolltemplatesextends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    return parent::gethead() . tuitabs::gethead();
  }

  public function getcontent() {
$result = '';
    $polls = tpolls::i();
$lang = tlocal::admin('polls');
    $html = tadminhtml::i();
    $args = new targs();
$dir = litepublisher::$paths->data . 'polls';

if ($action = $this->action) {
$id = $this->idget();
switch ($action) {
case 'delete':
      if ($this->confirmed) {
@unlink($dir .DIRECTORY_SEPARATOR . "$id.php");
@unlink($dir .DIRECTORY_SEPARATOR . "$id.bak.php");
unset($polls->tml_items[$id]);
      $result .= $html->h4->deleted;
} else {
$result .= $html->confirmdelete($id, $this->adminurl, $lang->confirmdelete);
}
break;

case 'edit':
$tml = $polls->get_tml($id);
break;

case 'add':
$tml = array(
);
break;
}

if (isset($tml) && ($tml !== false)) {
$args->add($tml);
$args->id = $id;
$args->items = implode("\n", $tml['items']);
    $tabs = new tuitabs();
    $tabs->add($lang->items, "[editor=items]");
    $tabs->add($lang->tml, "[editor=tml]");
    $tabs->add($lang->result, "[editor=result]");

    $args->formtitle = $lang->edittemplate;
    $result .= $html->adminform('[text=title]' .
$tabs->get(), $args);
}
}

$result .= $html->h4->alltemplates;
$args->adminurl = $this->adminurl;
$table = '';
$tr = '<tr>
<td><a href="$adminurl=$id&amp;action=edit">$title</a></td>
<td><a href=$adminurl=$id&amp;action=delete">$lang.delete</a></td>
</tr>';

$filelist = tfiler::getfiles($dir);
      foreach($filelist as $filename) {
if (preg_match('/^(\d*+)\.php$/', $filename, $m)) {
$id = (int) $m[1];
$tml = $polls->get_tml($id);
$args->id = $id;
$args->title = $tml['title'];
$table .= $html->parsearg($tr, $args);
}
}

$head = "<tr>
<th>$lang->edit</th>
<th>$lang->delete</th>
</tr>";

$result .= $html->gettable($head, $table);
return $result;    
  }
  
  public function processform() {
    $polls = tpolls::i();
 $type = isset($_GET['type'] ? $_GET['type'] : '';
if (isset($polls->items[$type])) {
foreach ($polls->items[$type] as $name => $value) {
if (isset($_POST[$name])) $polls->items[$type][$name] = $_POST[$name];
}
$polls->save();
}

    return '';
  }
 
}//class