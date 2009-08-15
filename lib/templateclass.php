<?php

class TTemplate extends TEventClass {
  private static $fInstance;
  public $theme;
  public $path;
  public $url;
  public $DataObject;
  //public $footer;
  //public $sitebarcount;
  //public $submenuinwidget;
  
  protected $sitebars;
  public $widgets;
  public $curwidget;
  //protected $idwidget;
  protected $SitebarIndex;
  protected $tags;
  protected $fFiles;
  protected $aboutFiles;
  
  public static function &Instance() {
    global   $Options;
    if (isset(self::$fInstance))  return self::$fInstance;
    $TemplateClass = isset($Options->themeclass) ? $Options->themeclass: __class__;
    self::$fInstance = &GetInstance($TemplateClass);
    $GLOBALS['Template'] = &self::$fInstance;
    return self::$fInstance;
  }
  
  protected function CreateData() {
    global $Urlmap;
    parent::CreateData();
    $this->basename = 'template' . ($Urlmap->Ispda ? '.pda' : '');
    $this->AddEvents('WidgetAdded', 'WidgetDeleted', 'AfterWidget', 'OnWidgetContent', 'BeforeContent', 'AfterContent', 'Onhead', 'ThemeChanged');
    $this->Data['themename'] = 'default';
    $this->Data['sitebarcount'] = 2;
    $this->Data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->Data['idwidget'] = 0;
    $this->Data['submenuinwidget'] = true;
    $this->AddDataMap('sitebars', array(0 => array(), 1 => array(), 2 => array()));
    $this->AddDataMap('widgets', array());
    $this->AddDataMap('tags', array());
    $this->AddDataMap('theme', array());
    $this->fFiles = array();
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "Get$name")) {
      return $this->$get();
    }
    
    if (key_exists($name, $this->tags)) {
      return $this->GetTag($name);
    }
    
    if ($this->DataObjectHasProp($name)) {
      return $this->DataObject->$name;
    }
    
    return parent::__get($name);
  }
  
  protected function DataObjectHasProp($name) {
    return isset($this->DataObject) && (isset($this->DataObject->$name) || (method_exists($this->DataObject, 'PropExists') && $this->DataObject->PropExists($name)));
  }
  
  public function Load() {
    global $Options, $paths;
    parent::Load();
    if (!$this->ThemeExists($this->themename))  $this->themename = 'default';
    $this->path = $paths['themes'] . $this->themename . DIRECTORY_SEPARATOR ;
    $this->url = $Options->url . '/themes/'. $this->themename;
    
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
    global $paths, $Options;
    if (($this->themename <> $name) && $this->ThemeExists($name)) {
      $this->Lock();
      //echo "uninstall prev theme plugin if exists\n";
      if ($about = $this->GetAbout($this->themename)) {
        if (!empty($about['pluginclassname'])) {
          //echo "uninstall theme plugin<br>\n";
          $plugins = &TPlugins::Instance();
          $plugins->Delete($this->themename);
          //echo "plugin successfuly deleted<br>\n";
        }
      }
      $this->Data['themename'] = $name;
      $this->path = $paths['themes'] . $name . DIRECTORY_SEPARATOR  ;
      $this->url = $Options->url  . '/themes/'. $this->themename;
      //echo "load info about new theme\n";
      $about = $this->GetAbout($name);
      $this->sitebarcount = $about['sitebars'];
      // install theme plugin if exists
      if (!empty($about['pluginclassname'])) {
        $plugins = &TPlugins::Instance();
        $plugins->AddExt($name, $about['pluginclassname'], $about['pluginfilename']);
      }
      
      $this->theme = parse_ini_file($this->path . 'theme.ini', true);
      $this->Unlock();
      $this->ThemeChanged();
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
    }
  }
  
  public function AddWidget($class, $echotype, $order = -1, $index = 0) {
    if ($index >= $this->sitebarcount) return $this->Error("sitebar index $index cant more than sitebars count in template");
    if (!in_array($echotype, array('echo', 'include', 'nocache'))) $echotype = 'echo';
    $this->widgets[++$this->Data['idwidget']] = array(
    'class' => $class,
    'index' => $index,
    'echotype' => $echotype
    );
    
    if (($order < 0) || ($order > count($this->sitebars[$index]))) $order = count($this->sitebars[$index]);
    array_splice($this->sitebars[$index], $order, 0, $this->idwidget);
    
    $this->Save();
    $this->WidgetAdded($this->idwidget);
    return $this->idwidget;
  }
  
  public function ClassHasWidget($class) {
    foreach ($this->widgets as $id => $item) {
      if ($item['class'] == $class) {
        return true;
      }
    }
    return false;
  }
  
  public function DeleteWidget($ClassName) {
    $this->Lock();
    foreach ($this->widgets as $id => $item) {
      if ($item['class'] == $ClassName) {
        $this->DeleteIdWidget($id);
      }
    }
    $this->Unlock();
  }
  
  public function DeleteIdWidget($id) {
    global $paths;
    if (isset($this->widgets[$id])) {
      for ($i = count($this->sitebars) -1; $i >= 0; $i--) {
        $j = array_search($id, $this->sitebars[$i]);
        if (is_int($j)) array_splice($this->sitebars[$i], $j, 1);
      }
      
      @unlink($paths['cache']. "widget$id.php");
      unset($this->widgets[$id]);
      $this->Save();
      $this->WidgetDeleted($id);
    }
  }
  
  public function FindWidget($ClassName) {
    foreach ($this->widgets as $id => $item) {
      if ($item['class'] == $ClassName) return $id;
    }
    return false;
  }
  
  public static function  WidgetExpired(&$widget) {
    $self = &self::Instance();
    $self->SetWidgetExpired(get_class($widget));
  }
  
  public function SetWidgetExpired($ClassName) {
    global $paths;
    foreach ($this->widgets as $id => $item) {
      if ($item['class'] == $ClassName) {
        @unlink($paths['cache']. "widget$id.php");
      }
    }
  }
  
  public function WidgetsExpire() {
    global $paths;
    foreach ($this->widgets as $id => $item) {
      @unlink($paths['cache']. "widget$id.php");
    }
  }
  
  public function GetWidgetContent($id) {
    global $paths;
    $this->curwidget = $id;
    $FileName = $paths['cache']. "widget$id.php";
    switch ( $echotype = $this->widgets[$id]['echotype']) {
      case 'echo':
      if (@file_exists($FileName)) {
        $result = file_get_contents($FileName);
      } else {
        $widget = &GetInstance($this->widgets[$id]['class']);
        $result =   $widget->GetWidgetContent($id);
        file_put_contents($FileName, $result);
        @chmod($FileName, 0666);
      }
      break;
      
      case 'include':
      if (!@file_exists($FileName)) {
        $widget = &GetInstance($this->widgets[$id]['class']);
        $result =   $widget->GetWidgetContent($id);
        file_put_contents($FileName, $result);
        @chmod($FileName, 0666);
      }
      $result = "\n<?php @include(\$GLOBALS['paths']['cache']. 'widget$id.php'); ?>\n";
      break;
      
      case 'nocache':
      $widget = &GetInstance($this->widgets[$id]['class']);
      $result =   $widget->GetWidgetContent($id);
      break;
    }
    
    $s = $this->OnWidgetContent($result);
    if ($s != '') $result = $s;
    return $result;
  }
  
  public function GetBeforeWidget($name, $title = '') {
    $i = $this->SitebarIndex + 1;
    $result = '';
    if (isset($this->theme["sitebar$i"][$name])) {
      $result = $this->theme["sitebar$i"][$name];
    } elseif (isset($this->theme["sitebar$i"]['before'])) {
      $result = $this->theme["sitebar$i"]['before'];
    } elseif (isset($this->theme['widget'])) {
      $theme = &$this->theme['widget'];
      if (isset($theme[$name])) {
        $result = $theme[$name];
      } elseif (isset($theme[$name . $i])) {
        $result = $theme[$name . $i];
      } elseif (isset($theme["before$i"])) {
        $result = $theme["before$i"];
      } elseif (isset($theme['before'])) {
        $result = $theme['before'];
      }
    }
    
    if (empty($title) && isset(TLocal::$data['default'][$name])) {
      $title=  TLocal::$data['default'][$name];
    }
    
    //eval("\$result =\"$result\n\";");
    $result = sprintf($result, $title);
    return str_replace("'", '"', $result);
  }
  
  public function GetAfterWidget() {
    $result = $this->AfterWidget($this->curwidget);
    $i = $this->SitebarIndex + 1;
    if (isset($this->theme["sitebar$i"]['after'])) {
      $result .= $this->theme["sitebar$i"]['after'];
    } elseif (isset($this->theme['widget']["after$i"])) {
      $result .= $this->theme['widget']["after$i"];
    } elseif (isset($this->theme['widget']['after'])) {
      $result .= $this->theme['widget']['after'];
    }
    
    return str_replace("'", '"', $result);
  }
  
  public function Getsitebar() {
    $result = '';
    if ($this->submenuinwidget) $result .= $this->Getsubmenuwidget();
    $result .= $this->GetSitebarIndex(0);
    if ($this->sitebarcount == 1) {
      $result .= $this->GetSitebarIndex(1);
      $result .= $this->GetSitebarIndex(2);
    }
    return $result;
  }
  
  public function Getsitebar2() {
    $result = $this->GetSitebarIndex(1);
    if ($this->sitebarcount == 2) {
      $result .= $this->GetSitebarIndex(2);
    }
    return $result;
  }
  
  public function Getsitebar3() {
    return $this->GetSitebarIndex(2);
  }
  
  protected function GetSitebarIndex($index) {
    $this->SitebarIndex = $index;
    $result = '';
    foreach ($this->sitebars[$index] as $id) {
      $result .= $this->GetWidgetContent($id);
    }
    return $result;
  }
  
  public function MoveWidget($id, $index) {
    if (!isset($this->widgets[$id])) return false;
    $oldindex = $this->widgets[$id]['index'];
    if ($index != $oldindex) {
      $i = array_search($id, $this->sitebars[$oldindex]);
      array_splice($this->sitebars[$oldindex],  $i, 1);
      $this->sitebars[$index][] = $id;
      $this->widgets[$id]['index'] = $index;
      $this->Save();
    }
  }
  
  public function MoveWidgetOrder($id, $order) {
    if (!isset($this->widgets[$id])) return false;
    $index = $this->widgets[$id]['index'];
    if (($order < 0) || ($order > count($this->sitebars[$index]))) $order = count($this->sitebars[$index]);
    $oldorder = array_search($id, $this->sitebars[$index]);
    if ($oldorder != $order) {
      array_splice($this->sitebars[$index], $oldorder, 1);
      array_splice($this->sitebars[$index], $order, 0, $id);
      $this->Save();
    }
  }
  
  protected function GetTag($name) {
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
  
  public function&Request(&$DataObject) {
    $this->DataObject = &$DataObject;
    $GLOBALS['DataObject'] = &$DataObject;
    $header = $this->ServerHeader();
    $tml = $this->DataObjectHasProp('template') ? $this->DataObject->template : 'index.tml';
    $s = $this->ParseFile($tml);
    $s = $header .$s;
    if (method_exists($this->DataObject, 'AfterTemplated')) {
      $this->DataObject->AfterTemplated($s);
    }
    return $s;
  }
  
  protected function  ServerHeader() {
    global $Options;
    if (method_exists($this->DataObject, 'ServerHeader')) {
      $s= $this->DataObject->ServerHeader();
      if (!empty($s)) return $s;
    }
    $nocache = $this->DataObject->CacheEnabled ? '' : "
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');";
    
    return "<?php $nocache
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $Options->pingurl');
    ?>";
  }
  
  public function ParseFile($FileName) {
    global $Options, $Urlmap, $Template, $DataObject, $user, $post, $item, $tabindex, $lang;
    if (!isset($this->fFiles[$FileName])) {
      $this->fFiles[$FileName] = @file_get_contents($this->path . $FileName);
    }
    $Result = $this->fFiles[$FileName];
    $Result = str_replace('"', '\"', $Result);
    $lang = &TLocal::Instance();
    eval("\$Result = \"$Result\";");
    return $Result;
  }
  
  public function GetAbout($themename) {
    global $Options, $paths;
    if (!isset($this->aboutFiles)) $this->aboutFiles = array();
    if (!isset($this->aboutFiles[$themename])) {
      $this->aboutFiles[$themename] = @parse_ini_file($paths['themes'] . $themename . DIRECTORY_SEPARATOR    . 'about.ini', false);
      $langfile = $paths['themes'] . $themename . DIRECTORY_SEPARATOR    . $Options->language . '.ini';
      if (@file_exists($langfile) && ($ini = @parse_ini_file($langfile, true))) {
        $this->aboutFiles[$themename] = $ini['about'] + $this->aboutFiles[$themename];
      }
    }
    return $this->aboutFiles[$themename];
  }
  
  //html tags
  public function Gettitle() {
    global $Options;
    $result = '';
    if ($this->DataObjectHasProp('title')) {
      $result = $this->DataObject->title;
    }
    if (empty($result)) {
      $result = $Options->name;
    } else {
      $result = "$result | $Options->name";
    }
    return $result;
  }
  
  public function Getkeywords() {
    global $Options;
    $result = $this->DataObjectHasProp('keywords') ? $this->DataObject->keywords : '';
    if ($result == '')  return $Options->keywords;
    return $result;
  }
  
  public function Getdescription() {
    global $Options;
    $result = $this->DataObjectHasProp('description') ? $this->DataObject->description : '';
    if ($result =='') return $Options->description;
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
    $Menu = TMenu::Instance();
    $items = $Menu->GetMenuList();
    if (count($items) == 0) return '';
    $menuitem = $this->theme['menu']['item'];
    $result = '';
    foreach ($items as $item) {
      $subitems = '';
      if ($jsmenu &&(count($item['subitems']) > 0)) {
        foreach ($item['subitems'] as $subitem) {
          $subitems .= sprintf($menuitem , $subitem['url'], $subitem['title']) . "\n";
        }
        $subitems = sprintf($this->theme['menu']['subitems'], $$subitems) . "\n";
      }
      
      $result .= sprintf($menuitem , $item['url'], $item['title'], $subitems) . "\n";
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function Getsubmenuwidget() {
    if (!method_exists($this->DataObject, 'Getsubmenu'))  return '';
    
    $links = &$this->DataObject->Getsubmenu();
    if (count($links) == 0) return '';
    $item = $this->theme['menu']['item'];
    $content = '';
    foreach ($links as $link) {
      eval('$content .= "'. $item . '\n";');
    }
    $content = str_replace("'", '"', $content);
    
    $result = $this->GetBeforeWidget ('submenu');
    $result .= $content;
    $result .= $this->GetAfterWidget();
    return $result;
  }
  
  public function Setsubmenuinwidget($value) {
    if ($value != $this->submenuinwidget) {
      $this->Data['submenuinwidget'] = $value;
      $this->Save();
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
    }
  }
  
  public function Getarchives() {
    global $paths;
    $filename = $paths['cache'] . 'archives.php';
    if (@file_exists($filename)) return file_get_contents($filename);
    $arch = &TArchives::Instance();
    $result = $arch->GetHeadLinks();
    @file_put_contents($filename, $result);
    return $result;
  }
  
  public function Gethead() {
    $result = '';
    if (!$this->submenuinwidget && isset($this->theme['menu']['id'])) {
      global $paths;
      $java = file_get_contents($paths['libinclude'] . 'javasubmenu.txt');
      $id = $this->theme['menu']['id'];
      $tag = $this->theme['menu']['tag'];
      eval('$java = "'. str_replace('"', '\"', $java) . '\n";');
      $result .= $java;
    }
    $result .= $this->Onhead();
    return $result;
  }
  
  public function Getcontent() {
    $result = $this->BeforeContent();
    if (empty($result)) $result = '';
    if (method_exists($this->DataObject, 'GetTemplateContent')) {
      $result .= $this->DataObject->GetTemplateContent();
    } elseif ($this->DataObjectHasProp('content')) {
      $result .= $this->DataObject->content;
    }
    
    $result .= $this->AfterContent();
    return $result;
  }
  
  protected function Setfooter($s) {
    if ($s != $this->Data['footer']) {
      $this->Data['footer'] = $s;
      $this->Save();
    }
  }
  
  public static function SimpleContent($content) {
    $DataObj  = &new TSimpleContent();
    $DataObj->text = $content;
    $self = &self::Instance();
    return $self->Request($DataObj);
  }
  
  public static function SimpleHtml($content) {
    $DataObj  = &new TSimpleContent();
    $DataObj->html = $content;
    $self = &self::Instance();
    return $self->Request($DataObj);
  }
  
}//class

class TSimpleContent {
  public $text;
  public $html;
  
  public function  ServerHeader() {
    return "<?php
    @Header( 'Content-Type: text/html; charset=utf-8' );
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    ?>";
  }
  
  function GetTemplateContent() {
    if (!empty($this->text)) return "<h2>$this->text</h2>";
    return $this->html;
  }
  
}

?>