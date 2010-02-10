<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplate extends tevents {
  public $tml;
  public $path;
  public $url;
  public $context;
  public $itemplate;
  public $javascripts;
  public $javaoptions;
  public $cursitebar;
  //public $footer;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'template' ;
    $this->tml = 'index';
    $this->itemplate = false;
    $this->javaoptions = array(0 => 
sprintf("url: '%1\$s',\npingback: '%1\$s/rpc.xml',\nfiles: '%2\$s'",
litepublisher::$options->url, litepublisher::$options->files));
    $this->cursitebar = 0;
    $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onadminhead', 'onbody', 'themechanged',
    'onsitebar', 'onadminsitebar', 'onadminpanelsitebar', 'onwidget', 'onwidgetcontent');
    $this->data['theme'] = 'default';
    $this->data['hovermenu'] = false;
    $this->path = litepublisher::$paths['themes'] . 'default' . DIRECTORY_SEPARATOR ;
    $this->data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->data['hovermenu'] = false;
    $this->data['sitebars'] = null;
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
    return isset($this->context) && (isset($this->context->$name) || (method_exists($this->context, 'propexists') && $this->context->propexists($name)));
  }
  
  public function afterload() {
    parent::afterload();
    if (!$this->themeexists($this->theme))  $this->theme = 'default';
    $this->path = litepublisher::$paths['themes'] . $this->theme  . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$options->files . '/themes/'. $this->theme;
  }
  
  public function themeexists($name) {
    return ($name != '') && @file_exists(litepublisher::$paths['themes']. $name . DIRECTORY_SEPARATOR   . 'index.tml');
  }
  
  protected function settheme($name) {
    if (($this->theme <> $name) && $this->themeexists($name)) {
      $parser = tthemeparser::instance();
      $parser->changetheme($this->theme, $name);
      $this->themechanged();
    }
  }
  
  public function getsitebar() {
    if ($this->context instanceof itemplate2) {
      $result = $this->context->getsitebar();
    } else {
      $widgets = twidgets::instance();
      $result = $widgets->getcontent();
    }
    
    $this->dositebarclass(&$result, get_class($this->context));
    
    $this->onsitebar(&$result, $this->cursitebar);
    if (litepublisher::$options->admincookie) $this->onadminsitebar(&$result, $this->cursitebar);
    if (litepublisher::$urlmap->adminpanel) $this->onadminpanelsitebar(&$result, $this->cursitebar);
    $this->cursitebar++;
    return $result;
  }
  
  public function request($context) {
    $this->context = $context;
    $this->itemplate = $context instanceof itemplate;
    $itemplate2 = $context instanceof itemplate2;
    if ($itemplate2) {
      $tml = $context->template;
      ttheme::$name = $context->theme;
      if (ttheme::$name == '') {
        if ($tml != '') $this->tml = $tml;
      } else {
        if ($tml == '') $tml = 'index';
        ttheme::$name .= '.' . $tml;
      }
    }
    
    $result = $this->httpheader();
    $theme = ttheme::instance();
$theme->vars['context'] = $context;
    $result  .= $theme->parse($theme->theme);
    
    if ($itemplate2) $context->afterrequest($result);
    return $result;
  }
  
  protected function  httpheader() {
    if (method_exists($this->context, 'httpheader')) {
      $result= $this->context->httpheader();
      if (!empty($result)) return $result;
    }
    $nocache = $this->context->cache ? '' : "
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');";
    
    return "<?php $nocache
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: " . litepublisher::$options->url . "/rpc.xml');
    ?>";
  }
  
  //html tags
  public function gettitle() {
    $result = '';
    if ($this->itemplate) {
      $result = $this->context->gettitle();
    } elseif ($this->contextHasProp('title')) {
      $result = $this->context->title;
    }
    if (empty($result)) {
      $result = litepublisher::$options->name;
    } else {
      $result = "$result | " . litepublisher::$options->name;
    }
    return $result;
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
    $hovermenu = $this->hovermenu && isset($theme->menu['id']);
    if (litepublisher::$urlmap->adminpanel) {
      $adminmenus = tadminmenus::instance();
      return $adminmenus->getmenu($hovermenu);
    }
    
    $filename = litepublisher::$paths['cache'] . "$this->tml.menu.php";
    if (@file_exists($filename)) return file_get_contents($filename);
    
    $menus = tmenus::instance();
    $result = $menus->getmenu($hovermenu);
    file_put_contents($filename, $result);
    @chmod($filename, 0666);
    return $result;
  }
  
  public function sethovermenu($value) {
    if ($value == $this->hovermenu)  return;
    $this->data['hovermenu'] = $vlue;
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
      if (isset($theme->menu['id'])) {
        $this->javaoptions[] = sprintf("idmenu: '%s'", $theme->menu['id']);
        $this->javaoptions[] = sprintf("tagmenu: '%s'", $theme->menu['tag']);
        $result .=  "<script type=\"text/javascript\" src=\"litepublisher::$options->files/js/litepublisher/hovermenu.js\"></script>\n";
      }
    }
    
    foreach ($this->javascripts as $name => $script)  $result .=$script . "\n";
    
    if ($this->itemplate) $result .= $this->context->gethead();
    
    if (litepublisher::$urlmap->adminpanel) $this->onadminhead(&$result);
    $result = $this->getjavaoptions() . $result;
    $this->onhead(&$result);
    return trim($result);
  }
  
  public function getbody() {
    $result = '';
    $this->onbody(&$result);
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $this->beforecontent(&$result);
    if ($this->itemplate || method_exists($this->context, 'GetTemplateContent')) {
      $result .= $this->context->GetTemplateContent();
    } elseif ($this->contextHasProp('content')) {
      $result .= $this->context->content;
    }
    
    $this->aftercontent(&$result);
    return $result;
  }
  
  protected function setfooter($s) {
    if ($s != $this->data['footer']) {
      $this->data['footer'] = $s;
      $this->Save();
    }
  }
  
  private function dositebarclass(&$content, $class) {
    if (isset($this->events["sitebar_$class"])) {
      $this->callevent("sitebar_$class", array(&$content, $this->cursitebar));
    }
  }
  
  public function addsitebarclass($class, $handler) {
    if (!class_exists($class)) return $this->error("Class $class not found", 404);
    $this->lock();
    $this->doeventsubscribe("sitebar_$class", $handler);
    $this->optimizeevents();
    $this->unlock();
  }
  
  public function  deletesitebarclass($sitebarclass, $instance) {
    $this->lock();
    $this->eventunsubscribe("sitebar_$sitebarclass", get_class($instance));
    $this->optimizeevents();
    $this->unlock();
  }
  
  private function optimizeevents() {
    foreach ($this->events as $name => $list) {
      if (count($list) == 0) {
        unset($this->events[$name]);
      } elseif (strbegin($name, 'sitebar_')) {
        $class = substr($name, strlen('sitebar_'));
        if (!class_exists($class)) unset($this->events[$name]);
      }
    }
  }
  
}//class

?>