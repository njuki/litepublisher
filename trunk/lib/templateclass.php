<?php

class TTemplate extends TEventClass {
  public $theme;
public $tml;
  public $path;
  public $url;
  public $context;
  public $contextsupported;
  //public $footer;
  //public $submenuinwidget;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'template' ;
$this->tml = 'index';
    $this->contextsupported = false;
    $this->addevents('BeforeContent', 'AfterContent', 'Onhead', 'OnAdminHead', 'Onbody', 'ThemeChanged');
    $this->data['theme'] = 'default';
    $this->data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->data['submenuinwidget'] = true;
    $this->data['sitebars'] = array(0 => array(), 1 => array(), 2 => array()));
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (array_key_exists($name, $this->data['tags'])) {
$tags = ttemplatetags::instance();
return $tags->__get($name);
}
    if ($this->contextHasProp($name)) return $this->context->$name;
    return parent::__get($name);
  }
  
  private function contextHasProp($name) {
    return isset($this->context) && (isset($this->context->$name) || (method_exists($this->context, 'PropExists') && $this->context->PropExists($name)));
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
$sitebars = tsitebars::instance();
    $result = '';
    if (($sitebars->current == 0) && $this->submenuinwidget) $result .= $this->Getsubmenuwidget();
    $result .= $sitebars->getcurrent();
    return $result;
  }
  
  public function request($context) {
    global $options;
    $this->context = $context;
    $this->contextsupported = is_a($context, 'ITemplate');
    $GLOBALS['context'] = $context;
    $header = $this->ServerHeader();
    if ($this->contextHasProp('subtheme')) {
      $tml = $this->context->subtheme;
      if (empty($tml)) $tml =  'index.tml';
    } else {
      $tml =  'index.tml';
    }
    
    $s = $this->ParseFile($tml);
    $s = $header .$s;
    if (method_exists($this->context, 'AfterTemplated')) {
      $this->context->AfterTemplated($s);
    }
    return $s;
  }
  
  protected function  ServerHeader() {
    global $options;
    if (method_exists($this->context, 'ServerHeader')) {
      $s= $this->context->ServerHeader();
      if (!empty($s)) return $s;
    }
    $nocache = $this->context->CacheEnabled ? '' : "
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');";
    
    return "<?php $nocache
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $options->url/rpc.xml');
    ?>";
  }
  
  public function ParseFile($FileName) {
    global $options, $Urlmap, $Template, $context, $user, $post, $item, $tabindex, $lang;
    $Template = &$this;
    if (!isset($this->fFiles[$FileName])) {
      $this->fFiles[$FileName] = @file_get_contents($this->path . $FileName);
    }
    $Result = $this->fFiles[$FileName];
    $Result = str_replace('"', '\"', $Result);
    $lang = &TLocal::instance();
    try {
      eval("\$Result = \"$Result\";");
    } catch (Exception $e) {
      $options->HandleException($e);
    }
    
    return $Result;
  }
  
  //html tags
  public function Gettitle() {
    global $options;
    $result = '';
    if ($this->contextsupported) {
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
  
  public function Getkeywords() {
    global $options;
    $result = $this->contextHasProp('keywords') ? $this->context->keywords : '';
    if ($result == '')  return $options->keywords;
    return $result;
  }
  
  public function Getdescription() {
    global $options;
    $result = $this->contextHasProp('description') ? $this->context->description : '';
    if ($result =='') return $options->description;
    return $result;
  }
  
  public function Getmenu() {
    global $paths;
    $filename = $paths['cache'] . 'menu.php';
    if (@file_exists($filename)) {
      return file_get_contents($filename);
    }
    
    $result = $this->GetMenuItems();
    file_put_contents($filename, $result);
    @chmod($filename, 0666);
    return $result;
  }
  
  private function GetMenuItems() {
    $jsmenu = !$this->submenuinwidget && isset($this->theme['menu']['id']);
    $Menu = TMenu::instance();
    $items = $Menu->GetMenuList();
    if (count($items) == 0) return '';
    $menuitem = $this->theme['menu']['item'];
    $result = '';
    foreach ($items as $item) {
      $subitems = '';
      if ($jsmenu &&(count($item['subitems']) > 0)) {
        foreach ($item['subitems'] as $subitem) {
          $subitems .= sprintf($menuitem , $subitem['url'], $subitem['title'], '') . "\n";
        }
        $subitems = sprintf($this->theme['menu']['subitems'], $subitems) . "\n";
      }
      
      $result .= sprintf($menuitem , $item['url'], $item['title'], $subitems) . "\n";
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function Getsubmenuwidget() {
    if (!method_exists($this->context, 'Getsubmenu'))  return '';
    
    $items = $this->context->Getsubmenu();
    if (count($items) == 0) return '';
    $menuitem = $this->theme['menu']['item'];
    $content = '';
    foreach ($items as $item) {
      $content .= sprintf($menuitem , $item['url'], $item['title'], '') . "\n";
    }
    $content = str_replace("'", '"', $content);
    
    $result = $this->GetBeforeWidget ('submenu');
    $result .= $content;
    $result .= $this->GetAfterWidget();
    return $result;
  }
  
  public function Setsubmenuinwidget($value) {
    if ($value != $this->submenuinwidget) {
      $this->data['submenuinwidget'] = $value;
      $this->Save();
      $urlmap = &TUrlmap::instance();
      $urlmap->ClearCache();
    }
  }
  
  public function Getarchives() {
    global $paths;
    $filename = $paths['cache'] . 'archives.php';
    if (@file_exists($filename)) return file_get_contents($filename);
    $arch = &TArchives::instance();
    $result = $arch->GetHeadLinks();
    @file_put_contents($filename, $result);
    return $result;
  }
  
  public function Gethead() {
    global $paths;
    $result = '';
    if ($this->contextsupported) $result .= $this->context->gethead();
    if (!$this->submenuinwidget && isset($this->theme['menu']['id'])) {
      $java = file_get_contents($paths['libinclude'] . 'javasubmenu.txt');
      $result .= sprintf($java, $this->theme['menu']['id'], $this->theme['menu']['tag']);
    }
    $result .= $this->Onhead();
    $Urlmap = TUrlmap::instance();
    if ($Urlmap->IsAdminPanel) $result .= $this->OnAdminHead();
    return $result;
  }
  
  public function Getbody() {
    return $this->Onbody();
  }
  
  public function Getcontent() {
    $result = $this->BeforeContent();
    if (empty($result)) $result = '';
    if (method_exists($this->context, 'GetTemplateContent')) {
      $result .= $this->context->GetTemplateContent();
    } elseif ($this->contextHasProp('content')) {
      $result .= $this->context->content;
    }
    
    $result .= $this->AfterContent();
    return $result;
  }
  
  protected function Setfooter($s) {
    if ($s != $this->data['footer']) {
      $this->data['footer'] = $s;
      $this->Save();
    }
  }
  
}//class

?>