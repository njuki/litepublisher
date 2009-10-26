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
  protected $tags;
  protected $aboutFiles;
  
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
    $this->data['sitebars'] = array(
'count' => 2,
'items' => array(0 => array(), 1 => array(), 2 => array()))
);
    $this->addmap('tags', array());
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "Get$name")) {
      return $this->$get();
    }
    
    if (key_exists($name, $this->tags)) {
      return $this->GetTag($name);
    }
    
    if ($this->contextHasProp($name)) {
      return $this->context->$name;
    }
    
    return parent::__get($name);
  }
  
  protected function contextHasProp($name) {
    return isset($this->context) && (isset($this->context->$name) || (method_exists($this->context, 'PropExists') && $this->context->PropExists($name)));
  }
  
  public function load() {
    global $options, $paths;
    parent::load();
    if (!$this->ThemeExists($this->themename))  $this->themename = 'default';
    $this->path = $paths['themes'] . $this->themename . DIRECTORY_SEPARATOR ;
    $this->url = $options->files . '/themes/'. $this->themename;
    
    if (count($this->theme) == 0) {
      $this->theme = parse_ini_file($this->path . 'theme.ini', true);
      $this->Save();
    }
  }
  
  public function ThemeExists($name) {
    global $paths;
    return ($name != '') && @file_exists($paths['themes']. $name . DIRECTORY_SEPARATOR   . 'index.tml');
  }
  
  protected function Setthemename($name) {
    global $paths, $options;
    if (($this->themename <> $name) && $this->ThemeExists($name)) {
      $this->Lock();
      //echo "uninstall prev theme plugin if exists\n";
      if ($about = $this->GetAbout($this->themename)) {
        if (!empty($about['pluginclassname'])) {
          //echo "uninstall theme plugin<br>\n";
          $plugins = &TPlugins::instance();
          $plugins->Delete($this->themename);
          //echo "plugin successfuly deleted<br>\n";
        }
      }
      $this->data['themename'] = $name;
      $this->path = $paths['themes'] . $name . DIRECTORY_SEPARATOR  ;
      $this->url = $options->url  . '/themes/'. $this->themename;
      //echo "load info about new theme\n";
      $about = $this->GetAbout($name);
      $this->sitebarcount = $about['sitebars'];
      // install theme plugin if exists
      if (!empty($about['pluginclassname'])) {
        $plugins = &TPlugins::instance();
        $plugins->AddExt($name, $about['pluginclassname'], $about['pluginfilename']);
      }
      
      $this->theme = parse_ini_file($this->path . 'theme.ini', true);
      $this->Unlock();
      $this->ThemeChanged();
      $urlmap = &TUrlmap::instance();
      $urlmap->ClearCache();
    }
  }
  
  public function getsitebar() {
$sitebars = tsitebars::instance();
    $result = '';
    if (($sitebars->current == 0) && $this->submenuinwidget) $result .= $this->Getsubmenuwidget();
    $result .= $sitebars->getcurrent();
    return $result;
  }
  
  protected function gettag($name) {
    if (!isset($this->tags[$name]))  return '';
    $result ='';
    $function = $this->tags[$name]['function'];
    $classname = $this->tags[$name]['class'];
    if (empty($classname)) {
      if (function_exists($function)) {
        $result = $function($name);
      } else {
        unset($this->tags[$name]);
        $this->Save();
      }
    } else {
      
      if (!@class_exists($classname))   __autoload($classname);
      if (!@class_exists($classname)) {
        unset($this->tags[$name]);
        $this->Save();
      } else {
        $obj = &GetInstance($classname);
        $result = $obj->$function($name);
      }
    }
    
    return $result;
  }
  
  public function AddTag($tag, $classname, $function) {
    $this->tags[$tag] = array(
    'class' => $classname,
    'function' => $function
    );
    $this->Save();
  }
  
  public function DeleteTag($name) {
    if (isset($this->tags[$name])) {
      unset($this->tags[$name]);
      $this->Save();
    }
  }
  
  
  public function DeleteTagClass($classname) {
    $this->Lock();
    foreach ($this->tags as$tag => $item) {
      if ($item['class'] == $classname) unset($this->tags[$tag]);
    }
    $this->Unlock();
  }
  
  public function request(&$context) {
    global $options;
    $this->context = &$context;
    $this->contextsupported = is_a($context, 'ITemplate');
    $GLOBALS['context'] = &$context;
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
  
  public function GetAbout($themename) {
    global $options, $paths;
    if (!isset($this->aboutFiles)) $this->aboutFiles = array();
    if (!isset($this->aboutFiles[$themename])) {
      $this->aboutFiles[$themename] = @parse_ini_file($paths['themes'] . $themename . DIRECTORY_SEPARATOR    . 'about.ini', false);
      $langfile = $paths['themes'] . $themename . DIRECTORY_SEPARATOR    . $options->language . '.ini';
      if (@file_exists($langfile) && ($ini = @parse_ini_file($langfile, true))) {
        $this->aboutFiles[$themename] = $ini['about'] + $this->aboutFiles[$themename];
      }
    }
    return $this->aboutFiles[$themename];
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