<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolls extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public function setargs(targs $args, $status, $id_perm) {
$lang = tlocal::admin('polls');
$polls = tpolls::i();
$args->status = tadminhtml::array2combo(array(
'opened' => $lang->opened,
'closed' => $lang->closed
), $status);

$polls->loadall_tml();
$tml_items = array();
foreach ($polls->tml_items as $id => $tml) {
$tml_items[$id] = $tml['title'];
}

$args->id_tml = tadminhtml::array2combo($tml_items, $id_tml);
}

  public function getcontent() {
$result = '';
$polls = tpolls::i();
    $html = tadminhtml::i();
$lang = tlocal::admin('polls');
    $args = new targs();

$adminurl = $this->adminurl;

if ($action = $this->action) {
$id = $this->idget();
switch ($action) {
case 'delete':
      if ($this->confirmed) {
$polls->delete($id);
      $result .= $html->h4->deleted;
} else {
$result .= $html->confirmdelete($id, $adminurl, $lang->confirmdelete);
}
break;

case 'edit':
if (!$polls->itemexists($id)) {
$result .= $this->notfound();
} else {
$item = $polls->getitem($id);
$this->setargs($args, $item['status'], $item['id_tml']);
$args->id = $id;
    $args->formtitle = $lang->editpoll;
    $result .= $html->adminform(
'[combo=status]
[combo=id_tml]
', $args);
}
break;

case 'add':
$this->setargs($args, 'opened', tpollsman::i()->pullpost);

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

$perpage = 20;
$count = $polls->db->getcount();
      $from = $this->getfrom($perpage, $count);
$items = $polls->select('', " order by id desc limit $from, $perpage");
//$votes = $polls->db->res2items($polls->getdb($polls->votes)->select(sprintf('id in (%s)', implode(',', $items))));
foreach ($items as $id) {
$item = $polls->getitem($id);
$args->id = $id;
$args->add($item);
$tml = $polls->get_tml($item['id_tml']);
$args->title = $tml['title'];
$table .= $html->parsearg($tr, $args);
}

$head = "<tr>
<th>$lang->edit</th>
<th>$lang->delete</th>
</tr>";

$result .= $html->gettable($head, $table);

    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));

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
$type = $_POST['type'];
$title = tcontentfilter::escape($_POST['title']);
$items = strtoarray(str_replace(array("\r\n", "\r"), "\n", trim($_POST['newitems'])));
$items = array_unique($items);
array_delete_value($items, '');
if (count($items) == 0) return $this->html->empty;
$id = $polls->add_tml($type, $title, $items);
return litepublisher::$urlmap->redir($this->adminurl . '=' . $id . '&action=edit');
break;

}
}
}

}//class