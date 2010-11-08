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

public static function getviewform() {
    $html = THtmlResource ::instance();
    $html->section = 'views';
    $lang = tlocal::instance('views');
$args = targs::instance();
$args->items = self::getcombo(self::getparam('idview', 1));
return $html->comboform($args);
}

public static function getcombo($idview$id) {
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
$id = self::getparam('idview', 1);
$view = tview::instance($id);
$form = new tautoform($view, 'views', 'editform');
$form->add($form->id('hidden'), $form->name, $form->ajax);
if ($id != 1) $form->addprop($form->customsitebar);
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
$form->props[$i]['items'] = explode$customadmin[$name]['values'];
}
}
}

$this->_editform = $form;
return $form;
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
$result .= $this->editform->getcontent();
break;

case 'delete':
$idview = self::getparam('idview', 1);
          if($this->confirmed) {
$views->delete($idview);
} else {
    $result .= $html->confirmdelete($idview, self::getlink('/admin/views/', 'idview'), $lang->confirmdelete);
}
break;
}

$result .= $html->buildtable($views->items, array(
array('left', $lang->name,'$name'),
array('left', $lang->themename, sprintf('<a href="%s">$themename</a>', self::getlink('/admin/views/themes/', 'idview=$id'))),
array('center', $lang->widgets, sprintf('<a href="%s">%s</a>', self::getlink('/admin/views/widgets/', 'idview=$id'), $lang->widgets)),
array('center', $lang->edit, sprintf('<a href="%s">%s</a>', self::getlink('/admin/views/', 'action=edit&idview=$id'), $lang->edit)),
array('center', $lang->delete, sprintf('<a href="%s">%s</a>', self::getlink('/admin/views/', 'action=delete&idview=$id'), $lang->delete))
));
      break;
      
      case 'spec':
$items = '';
$content = '';
foreach (array('thomepage', 'tarchives', 'tnotfound404', 'tsitemap') as $classname) {
$obj = getinstance($classname);
ttheme::$vars['specobj'] = $obj;

$items .= $html->spectab($args);
$content .=$html->specform($args);
}

$args->items = $items;
$args->content = $content;
      $result .= $html->adminform($html->spectabs, $args);
      break;
      
      case 'headers':
      $args->hovermenu = $template->stdjavascripts['hovermenu'];
      $args->comments = $template->stdjavascripts['comments'];
      $args->moderate = $template->stdjavascripts['moderate'];
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
        
        $result = $this->html->h2->success;
        break;
        
        case 'spec':
        extract($_POST, EXTR_SKIP);
        if (isset($hometheme)) {
          $home = thomepage::instance();
          $home->theme = $hometheme;
          $home->save();
        }
        
        if (isset($archtheme)) {
          $arch = tarchives::instance();
          $arch->theme = $archtheme;
          $arch->save();
        }
        
        if (isset($theme404)) {
          $notfound = tnotfound404::instance();
          $notfound->theme = $theme404;
          $notfound->save();
        }
        
        if (isset($sitemaptheme)) {
          $sitemap = tsitemap::instance();
          $sitemap->theme = $sitemaptheme;
          $sitemap->save();
        }
        
        if (isset($admintheme)) {
          $template = ttemplate::instance();
          $template->admintheme = $admintheme;
          $template->save();
        }
        $result = $this->html->h2->themeschanged;
        break;
        
        case 'headers':
        extract($_POST, EXTR_SKIP);
        $template = ttemplate::instance();
        $template->stdjavascripts['hovermenu'] = $hovermenu;
        $template->stdjavascripts['comments'] = $comments;
        $template->stdjavascripts['moderate'] = $moderate;
        $template->save();
        break;
      }
    }
    
    ttheme::clearcache();
    return $result;
  }
  
}//class
?>