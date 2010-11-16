<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminviews extends tadminmenu {
private $_editform;

  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public static function getviewform($url) {
    $html = tadminhtml ::instance();
    $html->section = 'views';
    $lang = tlocal::instance('views');
$args = targs::instance();
$args->url = litepublisher::$site->url . $url;
$args->items = self::getcombo(tadminhtml::getparam('idview', 1));
return $html->comboform($args);
}

public static function getcomboview($idview, $name = 'idview') {
    $lang = tlocal::instance('views');
$theme = ttheme::instance();
return strtr($theme->content->admin->combo, array(
'$lang.$name' => $lang->view,
'$name' => $name,
'$value' => self::getcombo($idview)
));
}

public static function getcombo($idview) {
$result = '';
$views = tviews::instance();
foreach ($views->items as $id => $item) {
      $result .= sprintf('<option value="%d" %s>%s</option>', $id,
 $idview == $id ? 'selected="selected"' : '', $item['name']);
}
return $result;
}

public function geteditform() {
if (isset($this->_editform)) return $this->_editform;
$id = tadminhtml::getparam('idview', 1);
$view = tview::instance($id);
$form = new tautoform($view, 'views', 'editform');
$form->add($form->id('hidden'), $form->name);
if ($id > 1) {
$form->add($form->customsidebar, $form->disableajax);
}
if (count($view->custom) > 0) {
$custom = new tarray2prop ();
$custom->array = &$view->data['custom'];
$form->obj = $custom;
$customadmin = $view->theme->templates['customadmin'];
foreach ($custom->array as $name => $value) {
$i = $form->addprop(array(
'obj' => $custom,
'propname' => $name,
'type' => $customadmin[$name]['type'],
'title' => $customadmin[$name]['title']
));
if ($customadmin[$name]['type'] == 'combo') {
$form->props[$i]['items'] = $customadmin[$name]['values'];
}
}
}

$this->_editform = $form;
return $form;
}

public function getspecclasses() {
return array('thomepage', 'tarchives', 'tnotfound404', 'tsitemap');
}

    public function getcontent() {
    $result = '';
$views = tviews::instance();
    $html = $this->html;
$lang = tlocal::instance('views');
    $args = targs::instance();
    switch ($this->name) {
      case 'views':

switch ($this->action) {
case 'edit':
$result .= $this->editform->getform();
break;

case 'delete':
$idview = tadminhtml::getparam('idview', 1);
          if($this->confirmed) {
$views->delete($idview);
} else {
    $result .= $html->confirmdelete($idview, tadminhtml::getadminlink('/admin/views/', 'idview'), $lang->confirmdelete);
}
break;
}
$result .= $html->buildtable($views->items, array(
array('left', $lang->name,'$name'),
array('left', $lang->themename, sprintf('<a href="%s">$themename</a>', tadminhtml::getadminlink('/admin/views/themes/', 'idview=$id'))),
array('center', $lang->widgets, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/views/widgets/', 'idview=$id'), $lang->widgets)),
array('center', $lang->edit, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/views/', 'action=edit&idview=$id'), $lang->edit)),
array('center', $lang->delete, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/views/', 'action=delete&idview=$id'), $lang->delete))
));
      break;
      
      case 'spec':
$items = '';
$content = '';
foreach (self::getspecclasses() as $classname) {
$obj = getinstance($classname);
$args->classname = $classname;
$name = substr($classname, 1);
$args->title = $lang->{$name};
$inputs = self::getcomboview($obj->idview, "idview-$classname");
$inputs .= $html->getedit("keywords-$classname", $obj->keywords, $lang->keywords);
$inputs .= $html->getedit("description-$classname", $obj->description, $lang->description);
$args->inputs = $inputs;
$items .= $html->spectab($args);
$content .=$html->specform($args);
}

$args->items = $items;
$args->content = $content;
$args->formtitle = $lang->defaults;
      $result .= $html->adminform($html->spectabs, $args);
      break;

case 'defaults':
$items = '';
$theme = ttheme::instance();
$tml = $theme->content->admin->combo;
foreach ($views->defaults as $name => $id) {
$args->name = $name;
$args->value = self::getcombo($id);
$args->data['$lang.$name'] = $lang->$name;
$items .= $theme->parsearg($tml, $args);
}
$args->items = $items;
$args->formtitle = $lang->defaultsform;
$result .= $theme->parsearg($theme->content->admin->form, $args);
break;
      
      case 'headers':
$template = ttemplate::instance();
      $args->heads = $template->heads;
      $result = $html->jsform($args);
      break;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
      switch ($this->name) {
        case 'views':
if ($this->action == 'edit') return $this->editform->processform();
        break;
        
        case 'spec':
foreach (self::getspecclasses() as $classname) {
$obj = getinstance($classname);
$obj->lock();
$obj->setidview($_POST["idview-$classname"]);
$obj->keywords = $_POST["keywords-$classname"];
$obj->description = $_POST["description->$classname"];
$obj->unlock();
}        
        break;

case 'defaults':
$views = tviews::instance();
foreach ($views->defaults as $name => $id) {
$views->defaults[$name] = (int) $_POST[$name];
}
$views->save();
break;
        
        case 'headers':
    extract($_POST, EXTR_SKIP);
        $template = ttemplate::instance();
        $template->headers = $headers;
        $template->stdjavascripts['comments'] = $comments;
        $template->stdjavascripts['moderate'] = $moderate;
        $template->save();
        break;
    }
    
    ttheme::clearcache();
  }
  
}//class

?>