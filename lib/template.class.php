<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplate extends tevents {
  public $path;
  public $url;
  public $context;
  public $itemplate;
  public $javascripts;
  public $javaoptions;
  //public $footer;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
//prevent recursion
litepublisher::$classes->instances[__class__] = $this;
    parent::create();
    $this->basename = 'template' ;
    $this->path = litepublisher::$paths->themes . 'default' . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$options->files . '/themes/default';
    $this->itemplate = false;
    $this->javaoptions = array(0 =>
    sprintf("url: '%1\$s',\npingback: '%1\$s/rpc.xml',\nfiles: '%2\$s',\nidurl: '%3\$s'",
    litepublisher::$options->url, litepublisher::$options->files, litepublisher::$urlmap->itemrequested['id']));
    $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onadminhead', 'onbody', 'themechanged', 'onadminhover', 'ondemand');
    $this->data['theme'] = 'default';
    $this->data['admintheme'] = '';
    $this->data['hovermenu'] = true;
    $this->data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->data['tags'] = array();
    $this->addmap('javascripts', array());
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (array_key_exists($name, $this->data)) return $this->data[$name];
    if (array_key_exists($name, $this->data['tags'])) {
      $tags = ttemplatetags::instance();
      return $tags->__get($name);
    }
    if ($this->contextHasProp($name)) return $this->context->$name;
    return parent::__get($name);
  }
  
  private function contextHasProp($name) {
    return isset($this->context) && isset($this->context->$name);
  }
  
  public function afterload() {
    parent::afterload();
    if (!$this->theme_exists($this->theme))  $this->theme = 'default';
    $this->path = litepublisher::$paths->themes . $this->theme  . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$options->files . '/themes/'. $this->theme;
  }
  
  public function theme_exists($name) {
    return ($name != '') && file_exists(litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR   );
  }
  
  protected function settheme($name) {
    if (($this->theme != $name) && $this->theme_exists($name)) {
      try {
        $this->lock();
        $parser = tthemeparser::instance();
        $parser->changetheme($this->theme, $name);
        $this->unlock();
        $this->themechanged();
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
    }
  }
  
  private function loadtheme($name, $tmlfile) {
    if (!$this->theme_exists($name)) {
        if ($name == $this->theme) {
$name = 'default';
} else {
$name = $this->theme;
    if (!$this->theme_exists($name)) $name = 'default';
    }
}

    /*
    if (!@file_exists($path . "$tmlfile.tml")) {
      if (($tmlfile != 'index') && @file_exists($this->path . "index.tml")) {
        $tmlfile = 'index';
      } else {
        //not exists "/theme/$name/index.tml"
        $tmlfile = 'index';
        if ($name != $this->theme) {
          $name = $this->theme;
          $this->path = litepublisher::$paths->themes . $this->theme . DIRECTORY_SEPARATOR;
        }
        
        if (!@file_exists($this->path . 'index.tml')) {
          $this->theme = 'default';
          $name = 'default';
        }
      }
    }
    */
    $tmlfile = 'index';
    $this->path = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$options->files . "/themes/$name";
    return ttheme::getinstance($name, $tmlfile);
  }
  
  public function getcontexttheme($context) {
    $themename = $this->theme;
    if (litepublisher::$urlmap->adminpanel)       $themename = $this->admintheme;
    if (isset($context->theme) && ($context->theme != '')) $themename = $context->theme;
    $tmlfile = 'index';
    if (isset($context->tmlfile) && ($context->tmlfile != '')) $ttmlfile = $context->tmlfile;
    $theme = $this->loadtheme($themename, $tmlfile);
    if (($theme->type != 'litepublisher') && litepublisher::$urlmap->adminpanel) {
      $theme = $this->loadtheme('default', $tmlfile);
    }
    
    litepublisher::$classes->instances[get_class($theme)] = $theme;
    return $theme;
  }
  
  public function request($context) {
    $this->context = $context;
    $this->itemplate = $context instanceof itemplate;
    ttheme::$vars['template'] = $this;
    $theme = $this->getcontexttheme($context);
    $result = $this->httpheader();
    $result  .= $theme->gethtml($context);
    if ($context instanceof itemplate2) $context->afterrequest($result);
    return $result;
  }
  
  protected function  httpheader() {
    if (method_exists($this->context, 'httpheader')) {
      $result= $this->context->httpheader();
      if (!empty($result)) return $result;
    }
return turlmap::httpheader($this->context->cache);
}

  //html tags
  public function getsitebar() {
    $widgets = twidgets::instance();
    return $widgets->getsitebar($this->context);
  }
  
  public function gettitle() {
    $title = '';
    if ($this->itemplate) {
      $title = $this->context->gettitle();
    } elseif ($this->contextHasProp('title')) {
      $title = $this->context->title;
    }
    
    if (empty($title)) return litepublisher::$options->name;
    
    $args = targs::instance();
    $args->title = $title;
    $theme = ttheme::instance();
    return $theme->parsearg($theme->title, $args);
  }
  
  public function geticon() {
    $result = '';
    if ($this->contextHasProp('icon')) {
      $icon = $this->context->icon;
      if ($icon > 0) {
        $files = tfiles::instance();
        $result = $files->geturl($icon);
      }
    }
    if ($result == '')  return litepublisher::$options->files . '/favicon.ico';
    return $result;
  }
  
  public function getkeywords() {
    $result = $this->contextHasProp('keywords') ? $this->context->keywords : '';
    if ($result == '')  return litepublisher::$options->keywords;
    return $result;
  }
  
  public function getdescription() {
    $result = $this->contextHasProp('description') ? $this->context->description : '';
    if ($result =='') return litepublisher::$options->description;
    return $result;
  }
  
  public function getmenu() {
    $theme = ttheme::instance();
    $hovermenu = $this->hovermenu && $theme->menu->hover;
    $current = $this->context instanceof tmenu ? $this->context->id : 0;
    if (($current == 0) && ($this->context instanceof thomepage)) $current = $this->context->idmenu;
    if (litepublisher::$urlmap->adminpanel) {
      $this->callevent('onadminhover', array(&$hovermenu));
      $adminmenus = tadminmenus::instance();
      return $adminmenus->getmenu($hovermenu, $current);
    }
    
    if ($current == 0) {
      $filename = litepublisher::$paths->cache . "$theme->name.$theme->tmlfile.menu.php";
      if (@file_exists($filename)) return file_get_contents($filename);
    }
    
    $menus = tmenus::instance();
    $result = $menus->getmenu($hovermenu, $current);
    if ($current == 0) {
      file_put_contents($filename, $result);
      @chmod($filename, 0666);
    }
    return $result;
  }
  
  public function sethovermenu($value) {
    if ($value == $this->hovermenu)  return;
    $this->data['hovermenu'] = $value;
    $this->save();
    
    litepublisher::$urlmap->clearcache();
  }
  
  public function addjavascript($name, $script) {
    if (!isset($this->javascripts[$name])) {
      $this->javascripts[$name] = $script;
      $this->save();
    }
  }
  
  public function editjavascript($name, $script) {
    $this->javascripts[$name] = $script;
    $this->save();
  }
  
  public function deletejavascript($name) {
    if (isset($this->javascripts[$name])) {
      unset($this->javascripts[$name]);
      $this->save();
    }
  }
  
  private function getjavaoptions() {
    $result = "<script type=\"text/javascript\">\nvar ltoptions = {\n";
      $result .= implode(",\n", $this->javaoptions);
    $result .= "\n};\n</script>\n";
    return $result;
  }
  
  public function gethead() {
    $result = '';
    if ($this->hovermenu) {
      $theme = ttheme::instance();
      if ($theme->menu->hover) {
        $this->javaoptions[] = sprintf("idmenu: '%s'", $theme->menu->id);
        $this->javaoptions[] = sprintf("tagmenu: '%s'", $theme->menu->tag);
        $result .=  '<script type="text/javascript" src="' . litepublisher::$options->files . '/js/litepublisher/hovermenu.min.js"></script>' . "\n";
      }
    }
    
    foreach ($this->javascripts as $name => $script)  $result .=$script . "\n";
    
    if ($this->itemplate) $result .= $this->context->gethead();
    
    if (litepublisher::$urlmap->adminpanel) $this->callevent('onadminhead', array(&$result));
    $result = $this->getjavaoptions() . $result;
    $this->callevent('onhead', array(&$result));
    return trim($result);
  }
  
  public function getbody() {
    $result = '';
    $this->callevent('onbody', array(&$result));
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $this->callevent('beforecontent', array(&$result));
    if ($this->itemplate || method_exists($this->context, 'getcont')) {
      $result .= $this->context->getcont();
    } elseif ($this->contextHasProp('content')) {
      $result .= $this->context->content;
    }
    
    $this->callevent('aftercontent', array(&$result));
    return $result;
  }
  
  protected function setfooter($s) {
    if ($s != $this->data['footer']) {
      $this->data['footer'] = $s;
      $this->Save();
    }
  }
  
}//class

?>