<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminviews extends tadminmenu {
  private $_editform;
  private $_adminoptionsform;
  
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
    return strtr($theme->templates['content.admin.combo'], array(
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
  

  public function gethead() {
    $result = parent::gethead();
    switch ($this->name) {
      case 'views':
$template = ttemplate::instance();
    $template->ltoptions[] = sprintf('allviews: [%s]', implode(',', array_keys(tviews::instance()->items)));
    $result .= '<link rel="stylesheet" type="text/css" href="$site.files/js/jquery/ui-1.8.10/redmond/jquery-ui-1.8.10.custom.css" />';

  //$result .= $template->getloadjavascript('"$site.files/js/litepublisher/admin.views.js", function() {init_views();}' );
$result .= '<script type="text/javascript" src="$site.files/js/litepublisher/admin.views.js"></script>
<script type="text/javascript" >
init_views();
</script>';
      break;

/*
      case 'spec':
      //$result .= $template->
      break;
*/
    }
    return $result;
  }

private function get_view_sidebars($idview) {
$view = tview::instance($idview);
    $widgets = twidgets::instance();
    $html = $this->html;
    $lang = tlocal::instance('views');
    $args = targs::instance();
$args->idview = $idview;
    $args->adminurl = tadminhtml::getadminlink('/admin/views/widgets/', 'idwidget');
$view_sidebars = '';
$widgetoptions = '';
     $count = count($view->sidebars);
    $sidebarnames = range(1, $count);
    $parser = tthemeparser::instance();
    $about = $parser->getabout($view->theme->name);
    foreach ($sidebarnames as $key => $value) {
      if (isset($about["sidebar$key"])) $sidebarnames[$key] = $about["sidebar$key"];
    }

    foreach ($view->sidebars as $index => $sidebar) {
$args->index = $index;
$widgetlist = '';
$idwidgets = array();
      foreach ($sidebar as $j => $_item) {
        $id = $_item['id'];
$idwidgets[] = $id;
        $widget = $widgets->getitem($id);
        $args->id = $id;
        $args->ajax = $_item['ajax'];
        $args->inline = $_item['ajax'] === 'inline';
        $args->disabled = ($widget['cache'] == 'cache') || ($widget['cache'] == 'nocache') ? '' : 'disabled';
        $args->add($widget);
$widgetlist .= $html->widgetitem($args);
        $widgetoptions .= $html->widgetoption($args);
}
$args->sidebarname = $sidebarnames[$index];
$args->items = $widgetlist;
$args->idwidgets = implode(',', $idwidgets);
$view_sidebars .= $html->view_sidebar($args);
}

$args->view_sidebars = $view_sidebars;
$args->widgetoptions = $widgetoptions;
$args->id = $idview;
return $html->view_sidebars($args);
}

private function get_view_theme($idview) {
return '';
}

    public function getcontent() {
    $result = '';
    $views = tviews::instance();
    $html = $this->html;
    $lang = tlocal::instance('views');
    $args = targs::instance();
    switch ($this->name) {
      case 'views':
/*
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
        
        default:
        $args = targs::instance();
        $args->formtitle = $html->togglelink();
        $args->action = 'add';
        $result .= $html->adminform('[text=name] [hidden=action]', $args);
        $result .= $html->addscript;
        break;
      }
      
      $result .= $html->buildtable($views->items, array(
      array('left', $lang->name,sprintf('<a href="%s">$name</a>', tadminhtml::getadminlink('/admin/views/', 'action=edit&idview=$id'))),
      array('left', $lang->themename, sprintf('<a href="%s">$themename</a>', tadminhtml::getadminlink('/admin/views/themes/', 'idview=$id'))),
      array('center', $lang->widgets, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/views/widgets/', 'idview=$id'), $lang->widgets)),
      array('center', $lang->delete, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/views/', 'action=delete&idview=$id'), $lang->delete))
      ));
*/

$items = '';
$content = '';
foreach ($views->items as $id => $itemview) {
$args->add($itemview);
$items .= $html->itemview($args);
$args->view_sidebars = $this->get_view_sidebars($id);
$args->view_theme = $this->get_view_theme($id);
$content .= $html->viewtab($args);
}
$args->items = $items;
$args->content = $content;
$result = $html->allviews($args);
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
        if (isset($obj->data['keywords'])) $inputs .= $html->getedit("keywords-$classname", $obj->keywords, $lang->keywords);
        if (isset($obj->data['description'])) $inputs .= $html->getedit("description-$classname", $obj->description, $lang->description);
        $args->inputs = $inputs;
        $items .= $html->spectab($args);
        $content .=$html->specform($args);
      }
      
      $args->items = $items;
      $args->content = $content;
      $args->formtitle = $lang->defaults;
      $result .= $html->adminform($html->spectabs, $args);
      break;
      
      case 'group':
      $args->formname = 'posts';
      $args->formtitle = $lang->viewposts;
      $args->items = self::getcomboview($views->defaults['post'], 'postview');
      $result .= $html->groupform($args);
      
      $args->formname = 'menus';
      $args->formtitle = $lang->viewmenus;
      $args->items = self::getcomboview($views->defaults['menu'], 'menuview');
      $result .= $html->groupform($args);
      
      $args->formname = 'themes';
      $args->formtitle = $lang->themeviews;
      $view = tview::instance();
      $list =    tfiler::getdir(litepublisher::$paths->themes);
      sort($list);
      $themes = array_combine($list, $list);
      $args->items = $html->getcombo('themeview', tadminhtml::array2combo($themes, $view->themename), $lang->themename);
      $result .= $html->groupform($args);
      break;
      
      case 'defaults':
      $items = '';
      $theme = ttheme::instance();
      $tml = $theme->templates['content.admin.combo'];
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
      $args->formtitle = $lang->headstitle;
      $result = $html->adminform('[editor=heads]', $args);
      break;
      
      case 'admin':
      return $this->adminoptionsform->getform();;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
    switch ($this->name) {
      case 'views':
      switch ($this->action) {
        case 'add':
        $name = trim($_POST['name']);
        if ($name != '') {
          $views = tviews::instance();
          $id = $views->add($name);
          return;
        }
        break;
        
        case 'edit':
        return $this->editform->processform();
      }
      break;
      
      case 'spec':
      foreach (self::getspecclasses() as $classname) {
        $obj = getinstance($classname);
        $obj->lock();
        $obj->setidview($_POST["idview-$classname"]);
        if (isset($obj->data['keywords'])) $obj->keywords = $_POST["keywords-$classname"];
        if (isset($obj->data['description '])) $obj->description = $_POST["description-$classname"];
        $obj->unlock();
      }
      break;
      
      case 'group':
      //find action
      foreach ($_POST as $name => $value) {
        if (strbegin($name, 'action_')) {
          $action = substr($name, strlen('action_'));
          break;
        }
      }
      
      switch ($action) {
        case 'posts':
        $posts = tposts::instance();
        $idview = (int) $_POST['postview'];
        if (dbversion) {
          $posts->db->update("idview = '$idview'", 'id > 0');
        } else {
          foreach ($posts->items as $id => $item) {
            $post = tpost::instance($id);
            $post->idview = $idview;
            $post->save();
            $post->free();
          }
        }
        break;
        
        case 'menus':
        $idview = (int) $_POST['menuview'];
        $menus = tmenus::instance();
        foreach ($menus->items as $id => $item) {
          $menu = tmenu::instance($id);
          $menu->idview = $idview;
          $menu->save();
        }
        break;
        
        case 'themes':
        $themename = $_POST['themeview'];
        $views = tviews::instance();
        $views->lock();
        foreach ($views->items as $id => $item) {
          $view = tview::instance($id);
          $view->themename = $themename;
          $view->save();
        }
        $views->unlock();
        break;
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
      $template = ttemplate::instance();
      $template->heads = $_POST['heads'];
      $template->save();
      break;
      
      case 'admin':
      return $this->adminoptionsform->processform();
    }
    
    ttheme::clearcache();
  }
  
  public function getadminoptionsform() {
    if (isset($this->_adminoptionsform)) return $this->_adminoptionsform;
    $form = new tautoform(tajaxposteditor ::instance(), 'views', 'adminoptions');
    $form->add($form->ajaxvisual, $form->visual);
    $form->obj = tadminmenus::instance();
    $form->add($form->heads('editor'));
    $this->_adminoptionsform = $form;
    return $form;
  }
  
}//class

?>