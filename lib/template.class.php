<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplate extends tevents_storage {
  public $path;
  public $url;
  public $context;
  public $itemplate;
public $view;
  public $heads;
  public  $adminheads;
  public $javascripts;
  public $adminjavascripts;
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
    $this->url = litepublisher::$site->files . '/themes/default';
    $this->itemplate = false;
    $this->hover = true;
    $this->javaoptions = array(0 =>
    sprintf("url: '%1\$s',\npingback: '%1\$s/rpc.xml',\nfiles: '%2\$s',\nidurl: '%3\$s'",
    litepublisher::$site->url, litepublisher::$site->files, litepublisher::$urlmap->itemrequested['id']));
    $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onadminhead', 'onbody', 'themechanged', 'onadminhover', 'ondemand');
    $this->data['theme'] = 'default';
    $this->data['admintheme'] = '';
    $this->data['hovermenu'] = true;
    $this->data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->data['tags'] = array();
    $this->addmap('heads', array());
    $this->addmap('adminheads', array());
    $this->addmap('javascripts', array());
    $this->addmap('adminjavascripts', array());
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (array_key_exists($name, $this->data)) return $this->data[$name];
    if (array_key_exists($name, $this->data['tags'])) {
      $tags = ttemplatetags::instance();
      return $tags->__get($name);
    }
    if (isset($this->context) && isset($this->context->$name)) return $this->context->$name;
    return parent::__get($name);
  }
  
  protected function settheme($name) {
    if (($this->theme != $name) && ttheme::exists($name)) {
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
  
  public function request($context) {
    $this->context = $context;
    ttheme::$vars['template'] = $this;
    $this->itemplate = $context instanceof itemplate;
$this->view = $this->itemplate ? tview::getview($context) : tview::instance();
    $theme = $this->view->theme;
litepublisher::$classes->instances[get_class($theme)] = $theme;
    $this->path = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$site->files . '/themes/' . $theme->name;
    $this->hover = $this->hovermenu && $theme->menu->hover;
    $result = $this->httpheader();
    $result  .= $theme->gethtml($context);
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
    return $widgets->getsitebar($this->context, $this->view);
  }
  
  public function gettitle() {
    $title = $this->itemplate ? $this->context->gettitle() : '';
    if (empty($title)) return litepublisher::$site->name;
    
    $args = targs::instance();
    $args->title = $title;
    $theme = ttheme::instance();
    return $theme->parsearg($theme->title, $args);
  }
  
  public function geticon() {
    $result = '';
    if (isset($this->context) && isset($this->context->icon)) {
      $icon = $this->context->icon;
      if ($icon > 0) {
        $files = tfiles::instance();
        if ($files->itemexists($icon)) $result = $files->geturl($icon);
      }
    }
    if ($result == '')  return litepublisher::$site->files . '/favicon.ico';
    return $result;
  }
  
  public function getkeywords() {
    $result = $this->itemplate ? $this->context->getkeywords() : '';
    if ($result == '')  return litepublisher::$site->keywords;
    return $result;
  }
  
  public function getdescription() {
    $result = $this->itemplate ? $this->context->getdescription() : '';
    if ($result =='') return litepublisher::$site->description;
    return $result;
  }
  
  public function getmenu() {
    $current = $this->context instanceof tmenu ? $this->context->id : 0;
    if (litepublisher::$urlmap->adminpanel) {
      $this->onadminhover();
      $adminmenus = tadminmenus::instance();
      return $adminmenus->getmenu($this->hover, $current);
    }
    
    if ($current == 0) {
      $filename = litepublisher::$paths->cache . $this->view->theme->name . '.menu.php';
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
    return sprintf('<script type="text/javascript" src="%s"></script>', litepublisher::$site->files . $filename) . "\n";
  }
  
  public function gethead() {
    $result = implode("\n", $this->heads);
    $result .= implode("\n", $this->javascripts);
    if ($this->itemplate) $result .= $this->context->gethead();
    if (litepublisher::$urlmap->adminpanel) {
      $result .= implode("\n", $this->adminheads);
      $result .= implode("\n", $this->adminjavascripts);
      $this->callevent('onadminhead', array(&$result));
    }
    $result = $this->getjavaoptions() . $result;
    $result = $this->view->theme->parse($result);
    $this->callevent('onhead', array(&$result));
    return $result;
  }
  
  public function getmeta() {
    $result =
    '<link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />
    <link rel="pingback" href="$site.url/rpc.xml" />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />
    <link rel="shortcut icon" type="image/x-icon" href="$template.icon" />
    <meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->
    <meta name="keywords" content="$template.keywords" />
    <meta name="description" content="$template.description" />
    <link rel="sitemap" href="$site.url/sitemap.htm" />
		<link type="text/css" href="$site.files/js/jquery/jquery-ui-1.8.6.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="$site.files/js/jquery/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="$site.files/js/jquery/jquery-ui-1.8.6.custom.min.js"></script>

    <script type="text/javascript" src="$site.files/js/litepublisher/widgets.js"></script>
    <script type="text/javascript" src="$site.files/js/litepublisher/players.js"></script>
    <script type="text/javascript" src="$site.files/js/jsibox/jsibox_basic.js"></script>
';

/*
    <script type="text/javascript" src="$site.files/js/litepublisher/rpc.min.js"></script>
    <script type="text/javascript" src="$site.files/js/litepublisher/client.min.js"></script>
    <script type="text/javascript" src="$site.files/js/jsibox/jsibox_basic.js"></script>';
*/
    
    return $this->view->theme->parse($result);
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