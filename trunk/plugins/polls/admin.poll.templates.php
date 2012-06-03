<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolltemplates extends tadminmenu {
  
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
$adminurl = $this->adminurl;

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
$result .= $html->confirmdelete($id, $adminurl, $lang->confirmdelete);
}
break;

case 'edit':
if ($tml = $polls->get_tml($id)) {
$args->add($tml);
$args->id = $id;
//$args->items = implode("\n", $tml['items']);
    $tabs = new tuitabs();
    //$tabs->add($lang->pollitems, "[editor=items]");
    $tabs->add($lang->tml, "[editor=tml]");
    $tabs->add($lang->result, "[editor=result]");

    $args->formtitle = $lang->edittemplate;
    $result .= $html->adminform('[text=title]' .
$tabs->get(), $args);
}
break;

case 'add':
$types = array_keys(tpolltypes::i()->items);
$args->type = tadminhtml::array2combo(array_combine($types, $types), $types[0]);

$args->title= '';
$args->newitems = '';
    $args->formtitle = $lang->newtemplate;
    $result .= $html->adminform(
'[text=title]
[combo=type]
[editor=newitems]',
$args);
break;
}
}

$result .= $html->h3("<a href='$adminurl=0&amp;action=add'>$lang->addtemplate</a>");
$result .= $html->h4->alltemplates;
$args->adminurl = $adminurl;
$table = '';
$tr = '<tr>
<td><a href="$adminurl=$id&amp;action=edit">$title</a></td>
<td><a href=$adminurl=$id&amp;action=delete">$lang.delete</a></td>
</tr>';
$polls->loadall_tml();
foreach ($polls->tml_items as $id => $tml) {
$args->id = $id;
$args->title = $tml['title'];
$table .= $html->parsearg($tr, $args);
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
if ($action = $this->action) {
switch ($action) {
case 'edit':
$id = $this->idget();
if ($tml = $polls->get_tml($id)) {
$tml['tml'] = $_POST['tml'];
$tml['result'] = $_POST['result'];
$polls->set_tml($id, $tml);
}
break;

case 'add':
if ($id = $this->addtml()) {
return litepublisher::$urlmap->redir($this->adminurl . '=' . $id . '&action=edit');
} else {
return $this->html->empty;
}
break;
}
}
}

public function addtml() {
$type = $_POST['type'];
$title = tcontentfilter::escape($_POST['title']);
$items = strtoarray(str_replace(array("\r\n", "\r"), "\n", trim($_POST['newitems'])));
$items = array_unique($items);
array_delete_value($items, '');
if (count($items) == 0) return false;
return tpolls::i()->add_tml($type, $title, $items);
}

}//class