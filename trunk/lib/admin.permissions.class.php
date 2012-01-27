<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminperms extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getpermform($url) {
    $html = tadminhtml ::i();
    $html->section = 'perms';
    $lang = tlocal::i('perms');
    $args = targs::i();
    $args->url = litepublisher::$site->url . $url;
    $args->items = self::getcombo(tadminhtml::getparam('idperm', 1));
    return $html->comboform($args);
  }
  
  public static function getcomboperm($idperm, $name = 'idperm') {
    $lang = tlocal::i('perms');
    $theme = ttheme::i();
    return strtr($theme->templates['content.admin.combo'], array(
    '$lang.$name' => $lang->perm,
    '$name' => $name,
    '$value' => self::getcombo($idperm)
    ));
  }
  
  public static function getcombo($idperm) {
      $result = sprintf('<option value="0" %s>%s</option>', $idperm == 0 ? 'selected="selected"' : '', tlocal::get('perms', 'nolimits'));
    $perms = tperms::i();
    foreach ($perms->items as $id => $item) {
      $result .= sprintf('<option value="%d" %s>%s</option>', $id,
      $idperm == $id ? 'selected="selected"' : '', $item['name']);
    }
    return $result;
  }
  
    public function getcontent() {
    $result = '';
    $perms = tperms::i();
    $html = $this->html;
    $lang = tlocal::i('perms');
    $args = targs::i();
if (!($action = $this->action)) $action = 'perms';
    switch ($action) {
      case 'perms':
$args->editurl = $this->link . litepublisher::$site->q . 'action=edit&id';
$items = '';
foreach ($perms->items as $id => $item) {
if ($id == 1) continue;
$args->add($item);
$items .= $html->item($args);
}

$result = strtr(ttheme::i()->templates['content.admin.form'], array(
'$formtitle' => $this->lang->formtitle,
'$items' => $html->gettable($html->tablehead, $items),
'$lang.update' => $this->lang->delete
));

$items = '';
foreach ($perms->classes as $class => $name) {
$args->class = $class;
$args->name = $name;
$items .= $html->newitem($args);
}

$args->items = $items;
$result .= $html->newitems($args);
return $result;

case 'edit':
$id = $this->idget;
if (!$perms->itemexists($id)) return $this->notfound();
$perm = tperm::i($id);
return $perm->admin->getcont();

case 'add':
$class = tadminhtml::getparam('class', '');
if (!isset($perms->classes[$class])) return $this->notfound();
$perm = new $class();
return $perm->admin->getcont();
}

}

  public function processform() {
$perms = tperms::i();
if (!($action = $this->action)) $action = 'perms';
    switch ($action) {
      case 'perms':
$perms->lock();
foreach ($_POST as $name => $val) {
if (strbegin($name, 'checkbox-')) {
$perms->delete((int) $val);
}
}
$perms->unlock();
return;

case 'edit':
$id = $this->idget;
if (!$perms->itemexists($id)) return $this->notfound();
$perm = tperm::i($id);
return $perm->admin->processform();
}
}

}//class

class tadminperm {
public $perm;

public function getcont() {
$html = tadminhtml::i();
$lang = tlocal::i('perms');
$args = new targs();
$args->add($this->perm->data);
$args->formtitle = $lang->editperm;
$form = 'text=name] [hidden=id]';
$form .= $this->getform($args);
return $html->adminform($tml, $args);
}

public function getform(targs $args) {
return '';
}

public function processform() {
$name = trim($_POST['name']);
if ($name != '') $this->perm->name = $name;
$this->perm->save();
}

class tadminpermpassword extends tadminperm {

public function getform(targs $args) {
$args->password = '';
return '[password=password]';
}

public function processform() {
$this->perm->password = $_POST['password'];
parent::processfform();
}

}//class

class tadminpermgroups extends tadminperm {

public function getform(targs $args) {
$g = $this->perm->groups;
$groups = tusergroups::i();
$html = tadminhtml::i();
$result = '';
foreach ($groups->items as $id => $item) {
$name = $item['name'];
$checked = in_array($name, $g) ? 'checked="checked"' : '';
$checked .= sprintf(' value="%s" ', $name);
$result .= $html->getinput('checkbox', "permgroup-$id",  $checked, $name);
}
return $result;
}

public function processform() {
$g = array('admin');
foreach ($_POST as $name => $val) {
if (strbegin($name, 'permgroup-')) {
$g[] = $val;
}
}

$this->data['groups'] = array_unique($g);
parent::processfform();
}

}//class