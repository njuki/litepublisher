<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplate extends tevents_storage {
  public $path;
  public $url;
  public $context;
  public $itemplate;
  public $view;
  public $ltoptions;
  public $hover;
  //public $footer;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    //prevent recursion
    litepublisher::$classes->instances[__class__] = $this;
    parent::create();
    $this->basename = 'template' ;
    $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onbody', 'ontitle', 'ongetmenu');
    $this->path = litepublisher::$paths->themes . 'default' . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$site->files . '/themes/default';
    $this->itemplate = false;
    $this->ltoptions = array(
    'url' =>    litepublisher::$site->url,
    'files' =>litepublisher::$site->files,
    'idurl' => litepublisher::$urlmap->itemrequested['id'],
    'jqueryui_version' => litepublisher::$site->jqueryui_version,
    );
    $this->hover = true;
    $this->data['heads'] = '';
    $this->data['js'] = '<script type="text/javascript" src="%s"></script>';
  $this->data['jsready'] = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
    $this->data['jsload'] = '<script type="text/javascript">$.load_script(%s);</script>';
    $this->data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->data['tags'] = array();
  }
  
  public function __get($name) {
    if (method_exists($this, $get = 'get' . $name)) return $this->$get();
    if (array_key_exists($name, $this->data)) return $this->data[$name];
    if (preg_match('/^sidebar(\d)$/', $name, $m)) {
      $widgets = twidgets::i();
      return $widgets->getsidebarindex($this->context, $this->view, (int) $m[1]);
    }
    
    if (array_key_exists($name, $this->data['tags'])) {
      $tags = ttemplatetags::i();
      return $tags->__get($name);
    }
    if (isset($this->context) && isset($this->context->$name)) return $this->context->$name;
    return parent::__get($name);
  }
  
  protected function get_view($context) {
    return $this->itemplate ? tview::getview($context) : tview::i();
  }
  
  public function request($context) {
    $this->context = $context;
    ttheme::$vars['context'] = $context;
    ttheme::$vars['template'] = $this;
    $this->itemplate = $context instanceof itemplate;
    $this->view = $this->get_view($context);
    //$this->itemplate ? tview::getview($context) : tview::i();
    $theme = $this->view->theme;
    $this->ltoptions['themename'] = $theme->name;
    litepublisher::$classes->instances[get_class($theme)] = $theme;
    $this->path = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$site->files . '/themes/' . $theme->name;
    $this->hover = $this->view->hovermenu && ($theme->templates['menu.hover'] == 'true');
    
    $result = $this->httpheader();
    $result  .= $theme->gethtml($context);
    unset(ttheme::$vars['context'], ttheme::$vars['template']);
    return $result;
  }
  
  protected function  httpheader() {
    $ctx = $this->context;
    if (method_exists($ctx, 'httpheader')) {
      $result= $ctx->httpheader();
      if (!empty($result)) return $result;
    }
    
    if (isset($ctx->idperm) && ($idperm = $ctx->idperm)) {
      $perm =tperm::i($idperm);
      if ($result = $perm->getheader($ctx)) {
        return $result . turlmap::htmlheader($ctx->cache);
      }
    }
    
    return turlmap::htmlheader($ctx->cache);
  }
  
  //html tags
  public function getsidebar() {
    $widgets = twidgets::i();
    return $widgets->getsidebar($this->context, $this->view);
  }
  
  public function gettitle() {
    $title = $this->itemplate ? $this->context->gettitle() : '';
    if ($this->callevent('ontitle', array(&$title))) return $title;
    return $this->parsetitle($this->view->theme->title, $title);
  }
  
  public function parsetitle($tml, $title) {
    $args = targs::i();
    $args->title = $title;
    $result = $this->view->theme->parsearg($tml, $args);
    //$result = trim($result, sprintf(' |.:%c%c', 187, 150));
    $result = trim($result, " |.:\n\r\t");
    if ($result == '') return litepublisher::$site->name;
    return $result;
  }
  
  public function geticon() {
    $result = '';
    if (isset($this->context) && isset($this->context->icon)) {
      $icon = $this->context->icon;
      if ($icon > 0) {
        $files = tfiles::i();
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
    if ($r = $this->ongetmenu()) return $r;
    //$current = $this->context instanceof tmenu ? $this->context->id : 0;
    $view = $this->view;
    $menuclass = $view->menuclass;
    $filename = litepublisher::$paths->cache . $view->theme->name . sprintf('.%s.%s.php',
    $menuclass, litepublisher::$options->group ? litepublisher::$options->group : 'nobody');
    
    //if (file_exists($filename)) return file_get_contents($filename);
    //use memcache
    if ($result = tfilestorage::getfile($filename)) return $result;
    
    $menus = getinstance($menuclass);
    $result = $menus->getmenu($this->hover, 0);
    //file_put_contents($filename, $result);
    tfilestorage::setfile($filename, $result);
    return $result;
  }
  
  public function getcssfile() {
    return sprintf('%s/files/css/%s.css', litepublisher::$site->files, $this->view->theme->name);
  }
  
  private function getltoptions() {
    return sprintf('<script type="text/javascript">var ltoptions = %s;</script>', json_encode($this->ltoptions));
  }
  
  public function getjavascript($filename) {
    return sprintf($this->js, litepublisher::$site->files . $filename);
  }
  
  public function getready($s) {
    return sprintf($this->jsready, $s);
  }
  
  public function getloadjavascript($s) {
    return sprintf($this->jsload, $s);
  }
  
  public function addtohead($s) {
    $s = trim($s);
    if (false === strpos($this->heads, $s)) {
      $this->heads = trim($this->heads) . "\n" . $s;
      $this->save();
    }
  }
  
  public function deletefromhead($s) {
    $s = trim($s);
    $i = strpos($this->heads, $s);
    if (false !== $i) {
      $this->heads = substr_replace($this->heads, '', $i, strlen($s));
      $this->heads = trim(str_replace("\n\n", "\n", $this->heads));
      $this->save();
    }
  }
  
  public function gethead() {
    $result = $this->heads;
    if ($this->itemplate) $result .= $this->context->gethead();
    $result = $this->getltoptions() . $result;
    $result = $this->view->theme->parse($result);
    $this->callevent('onhead', array(&$result));
    return $result;
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
  
  public function getpage() {
    $page = litepublisher::$urlmap->page;
    if ($page <= 1) return '';
    return sprintf(tlocal::get('default', 'pagetitle'), $page);
  }
  
  public function trimwords($s, array $words) {
    if ($s == '') return '';
    foreach ($words as $word) {
      if (strbegin($s, $word)) $s = substr($s, strlen($word));
      if (strend($s, $word)) $s = substr($s, 0, strlen($s) - strlen*($word));
    }
    return $s;
  }
  
}//class