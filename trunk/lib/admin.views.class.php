<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminviews extends tadminmenu {

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
$id = self::getparam('idview', 1);
$view = tview::instance($id);
$form = new tautoform($view, 'views');
$form->name = tautoform::text;
$form->ajax = tautoform::checkbox;
$form->customsitebar = tautoform::checkbox;
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
      
      case 'options':
      $home = thomepage::instance();
      $args->hometheme = $home->theme;
      $arch = tarchives::instance();
      $args->archtheme = $arch->theme;
      $notfound = tnotfound404::instance();
      $args->theme404 = $notfound->theme;
      $sitemap = tsitemap::instance();
      $args->sitemaptheme = $sitemap->theme;
      $args->admintheme = $template->admintheme;
      $result = $html->optionsform($args);
      break;
      
      case 'javascripts':
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
    if  (isset($_POST['reparse'])) {
      $parser = tthemeparser::instance();
      try {
        $parser->reparse();
      } catch (Exception $e) {
        return $e->getMessage();
      }
    } else {
      switch ($this->name) {
        case 'themes':
        if (!empty($_GET['plugin']) && ($plugin = $this->getplugin())) return $plugin->processform();
        
        if (empty($_POST['selection']))   return '';
        $template = ttemplate::instance();
        try {
          $template->theme = $_POST['selection'];
        } catch (Exception $e) {
          $template->theme = 'default';
          return $e->getMessage();
        }
        $result = $this->html->h2->success;
        break;
        
        case 'edit':
        if (!empty($_GET['file']) && !empty($_GET['theme'])) {
          //security check
          if (strpbrk ($_GET['file'] . $_GET['theme'], '/\<>')) return '';
          if (!file_put_contents(litepublisher::$paths->themes . $_GET['theme'] . DIRECTORY_SEPARATOR . $_GET['file'], $_POST['content'])) {
            ttheme::clearcache();
            return  $this->html->h2->errorsave;
          }
        }
        break;
        
        case 'options':
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
        
        case 'javascripts':
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
  
  private function  getplugin() {
    if (!isset($this->plugin)) {
      $template =  ttemplate::instance();
      $parser = tthemeparser::instance();
      if (!($about = $parser->getabout($template->theme))) return false;
      if (empty($about['adminclassname']))  return false;
      $class = $about['adminclassname'];
      if (!class_exists($class))  require_once($template->path . $about['adminfilename']);
      $this->plugin = getinstance($class);
    }
    return $this->plugin;
  }
  
}//class
?>