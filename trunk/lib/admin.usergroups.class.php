<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmingroups extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public static function getgroups(array $idgroups) {
$result = '';
$groups = tgroups::i();
$tml = '<li><input type="checkbox" name="idgroup-$id" id="checkbox-idgroup-$id" $value="$id" $checked />
<label for="checkbox-idgroup-$id"><strong>$title</strong></label></li>';
$theme = ttheme::i();
$args = new targs();
foreach ($groups->items as $id => $item) {
$args->add($item);
$args->id = $id;
$args->checked = in_array($id, $idgroups);
      $result .= strtr ($s, $args->data);
    }
    
return sprintf('<ul>%s</ul>', $result);
}
  
  public function getcontent() {
    $result = '';
    $groups = tusergroups::i();
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();
$adminurl = $this->adminurl;
$id = $this->idget();

switch ($this->action) {
case 'add':
$args->name = '';
$args->title = '';
$args->home = '';
$args->action = 'add';
$args->formtitle = $lang->editgroup;
$result .= $html->adminform('[text=name] [text=title] [text=home] [hidden=action]', $args);
break;

case 'edit':
$args->add($groups->items[$id]);
$args->id = $id;
$args->action = 'edit';
$args->formtitle = $lang->editgroup;
$result .= $html->adminform('[text=name] [text=title] [text=home] [hidden=id] [hidden=action]', $args);
break;

case 'delete':
if ($groups->itemexists($id)) {
      if  (!$this->confirmed) {
        $args->adminurl = $adminurl;
        $args->id = $id;
        $args->action = 'delete';
        $args->confirm = sprintf($lang->deletegroup, $groups->items[$id]['title']);
        $result .= $html->confirmform($args);
      } else {
        $groups->delete($id);
      }
}
break;
}

$result .= $html->h4->grouptable;
$result .= $html->buildtable($groups->items, array(
    array('left', $lang->name, '<a href="' . $adminurl . '=$id&action=edit" title="$title">$title</a>'),
    array('left', $lang->users, '<a href="' . litepublisher::$site->url . '/admin/users/' . litepublisher::$site->q . 'idgroup=$id">' . $lang->users . '</a>'),
    array('left', $lang->delete, '<a href="' . $adminurl . '=$id&action=delete">' $lang->delete . '</a>')
));
return $result;
}
    
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $groups = tusergroups::i();
switch ($this->action) {
case 'add':
$id = $groups->add($name, $title, $home);
$_POST['id'] = $id;
$_GET['id'] = $id;
$_GET['action'] = 'edit';
break;

case 'edit':
if ($groups->itemexists($id)) {
$groups->items[$id] = array(
'name' => $name,
'title' => $title,
'home' => $home
);
$groups->save();
}
break;
}
}

}//class