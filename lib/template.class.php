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
  public $adminjavascripts;
  public $stdjavascripts;
  public $javaoptions;
  public $hover;
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
    $this->hover = true;
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
    $this->addmap('adminjavascripts', array());
    $this->addmap('stdjavascripts', array(
    'hovermenu' => '/js/litepublisher/hovermenu.min.js',
    'comments' => '/js/litepublisher/comments.min.js',
    'moderate' => '/js/litepublisher/moderate.min.js'
    ));
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
    $this->hover = $this->hovermenu && $theme->menu->hover;
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
    return turlmap::htmlheader($this->context->cache);
  }
  
  //html tags
  public function getsitebar() {
    $widgets = twidgets::instance();
    return $widgets->getsitebar($this->context);
  }
  
  public function gettitle() {
    $title = $this->itemplate ? $this->context->gettitle() : '';
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
    $result = $this->itemplate ? $this->context->getkeywords() : '';
    if ($result == '')  return litepublisher::$options->keywords;
    return $result;
  }
  
  public function getdescription() {
    $result = $this->itemplate ? $this->context->getdescription() : '';
    if ($result =='') return litepublisher::$options->description;
    return $result;
  }
  
  public function getmenu() {
    $current = $this->context instanceof tmenu ? $this->context->id : 0;
    if (litepublisher::$urlmap->adminpanel) {
      $this->onadminhover();
      $adminmenus = tadminmenus::instance();
      return $adminmenus->getmenu($this->hover, $current);
    }
    
    if (($current == 0) && ($this->context instanceof thomepage)) {
      $menus = tmenus::instance();
      if ($idmenu = $menus->url2id('/')) return $menus->getmenu($this->hover, $idmenu);
    }
    
    if ($current == 0) {
      $theme = ttheme::instance();
      $filename = litepublisher::$paths->cache . "$theme->name.$theme->tmlfile.menu.php";
      if (file_exists($filename)) return file_get_contents($filename);
    }
    
    $menus = tmenus::instance();
    $result = $menus->getmenu($this->hover, $current);
    
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
  
  public function getjavascript($filename) {
    return sprintf('<script type="text/javascript" src="%s"></script>', litepublisher::$options->files . $filename) . "\n";
  }
  
  public function gethead() {
    $result = $this->gethovermenuhead();
    if ($this->itemplate) $result .= $this->context->gethead();
    if (litepublisher::$urlmap->adminpanel) {
      $result .= implode("\n", $this->adminjavascripts);
      $this->callevent('onadminhead', array(&$result));
    } else {
      $result .= implode("\n", $this->javascripts);
    }
    $result = $this->getjavaoptions() . $result;
    $this->callevent('onhead', array(&$result));
    return trim($result);
  }
  
  public function gethovermenuhead() {
    if ($this->hover) {
      if ($script = $this->stdjavascripts['hovermenu']) {
        $theme = ttheme::instance();
        $this->javaoptions[] = sprintf("idmenu: '%s'", $theme->menu->id);
        $this->javaoptions[] = sprintf("tagmenu: '%s'", $theme->menu->tag);
        return $this->getjavascript($script);
      }
    }
    return '';
  }
  
  public function getbody() {
    $result = '';
    $this->callevent('onbody', array(&$result));
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $this->callevent('beforecontent', array(&$result));
    $result .= $this->itemplate ? $this->context->getcont() : '';
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