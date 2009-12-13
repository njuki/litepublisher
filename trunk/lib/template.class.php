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
  //public $footer;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
global $paths;
    parent::create();
    $this->basename = 'template' ;
$this->tml = 'index';
    $this->itemplate = false;
    $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onadminhead', 'onbody', 'themechanged',
 'onsitebar', 'onwidget', 'onwidgetcontent');
    $this->data['theme'] = 'default';
    $this->path = $paths['themes'] . 'default' . DIRECTORY_SEPARATOR ;
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
    global $options, $paths;
    parent::afterload();
    if (!$this->themeexists($this->theme))  $this->theme = 'default';
    $this->path = $paths['themes'] . $this->theme  . DIRECTORY_SEPARATOR ;
    $this->url = $options->files . '/themes/'. $this->theme;
  }
  
  public function themeexists($name) {
    global $paths;
    return ($name != '') && @file_exists($paths['themes']. $name . DIRECTORY_SEPARATOR   . 'index.tml');
  }
  
  protected function settheme($name) {
    if (($this->theme <> $name) && $this->themeexists($name)) {
$parser = tthemeparser::instance();
$parser->changetheme($this->theme, $name);
$this->themechanged();
}
}

  public function getsitebar() {
$result = '';
if (is_a($this->context, 'itemplate2')) {
$result .= $this->context->getsitebar();
} else {
$widgets = twidgets::instance();
    $result .= $widgets->getcontent();
}
    return $result;
  }
  
  public function request($context) {
    global $options;
    $GLOBALS['context'] = $context;
    $this->context = $context;
    $this->itemplate = is_a($context, 'itemplate');
$itemplate2 = is_a($context, 'itemplate2');
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
    $result  .= $theme->parse($theme->theme);

if ($itemplate2) $context->afterrequest($result);
    return $result;
  }
  
  protected function  httpheader() {
    global $options;
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
    @header('X-Pingback: $options->url/rpc.xml');
    ?>";
  }

public function getisadmin() {
    $urlmap = turlmap::instance();
return $urlmap->admin;
}
  
  //html tags
  public function gettitle() {
    global $options;
    $result = '';
    if ($this->itemplate) {
      $result = $this->context->gettitle();
    } elseif ($this->contextHasProp('title')) {
      $result = $this->context->title;
    }
    if (empty($result)) {
      $result = $options->name;
    } else {
      $result = "$result | $options->name";
    }
    return $result;
  }
  
public function geticon() {
global $options;
$result = '';
if ($this->contextHasProp('icon')) {
$icon = $this->context->icon;
if ($icon > 0) {
$icons = ticons::instance();
$result = $icons->geturl($icon);
}
}
    if ($result == '')  return "$options->files/favicon.ico";
    return $result;
}

  public function getkeywords() {
    global $options;
    $result = $this->contextHasProp('keywords') ? $this->context->keywords : '';
    if ($result == '')  return $options->keywords;
    return $result;
  }
  
  public function getdescription() {
    global $options;
    $result = $this->contextHasProp('description') ? $this->context->description : '';
    if ($result =='') return $options->description;
    return $result;
  }
  
  public function getmenu() {
    global $paths;
$theme = ttheme::instance();
    $hovermenu = $this->hovermenu && isset($theme->menu['id']);
if ($this->isadmin) {
$adminmenus = tadminmenus::instance();
return $adminmenus->getmenu($hovermenu);
}

    $filename = $paths['cache'] . "$this->tml.menu.php";
    if (@file_exists($filename)) return file_get_contents($filename);

$menus = tmenus::instance();
    $result = $menus->getmenu($hovermenu);
    file_put_contents($filename, $result);
    @chmod($filename, 0666);
    return $result;
  }
  
public function gethovermenu() {
return isset($this->javascripts['hovermenu']);
}  

  public function sethovermenu($value) {
    if ($value != $this->hovermenu) {
if ($value) {
$this->addjavascript('hovermenu', file_get_contents($paths['libinclude'] . 'hovermenu.js'));
} else {
$this->deletejavascript('hovermenu');
}

      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    }
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

  public function gethead() {
    $result = '';
    if ($this->itemplate) $result .= $this->context->gethead();
foreach ($this->javascripts as $name => $script) {
if ($name == 'hovermenu') {
$theme = ttheme::instance();
if (isset($theme->menu['id'])) $result .= sprintf($script, $theme->menu['id'], $theme->menu['tag']);
    }
$result .=$script;
$result .= "\n";
}

$this->onhead(&$result);
    if ($this->isadmin) $this->onadminhead(&$result);
    return $result;
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
  
}//class

?>