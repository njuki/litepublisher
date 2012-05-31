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

$id = $this->idget();
if ($tml = $polls->get_tml($id)) {
$args->id = $id;
    $tabs = new tuitabs();
foreach ($tml as $name => $value) {
$args->$name = $value;
    $tabs->add($lang->$name, "[editor=$name]");
}
    $args->formtitle = $lang->edittype;
    $result .= $html->adminform($tabs->get(), $args);
}

$result .= $html->h4->alltemplates;
$args->adminurl = $this->adminurl;
$table = '';
$tr = '<tr>
<td><a href="$adminurl=$id&amp;action=edit">$title</a></td>
<td><a href=$adminurl=$id&amp;action=delete">$lang.delete</a></td>
</tr>';

$filelist = tfiler::getfiles(litepublisher::$paths->data . 'polls');
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