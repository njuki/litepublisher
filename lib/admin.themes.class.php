<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminthemes extends tadminmenu {
  private $plugin;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $args = targs::instance();
    $template = ttemplate::instance();
    if ($plugin = $this->getplugin())  {
      $args->themename = $Template->theme;
      $args->url = litepublisher::$options->url . $this->url . litepublisher::$options->q ."plugin=$template->theme";
      $result .= $html->pluginlink($args);
    }
    
    switch ($this->name) {
      case 'themes':
      if ($plugin && !empty($_GET['plugin'])) {
        $result .= $plugin->getcontent();
        return $result;
      }
      $result .= $html->formheader();
      $list =    tfiler::getdir(litepublisher::$paths->themes);
      sort($list);
      $args->editurl = litepublisher::$options->url . $this->url . 'edit/' . litepublisher::$options->q . 'theme';
      foreach ($list as $name) {
        $about = $this->getabout($name);
        $about['name'] = $name;
        $args->add($about);
        $args->checked = $name == $template->theme;
        $result .= $html->radioitem($args);
      }
      $result .= $html->formfooter();
      break;
      
      case 'edit':
      $themename = !empty($_GET['theme']) ? $_GET['theme'] : $template->theme;
      if (strpbrk($themename, '/\<>')) return $this->notfound;
      $result = sprintf($html->h2->filelist, $themename);
      $list = tfiler::getfiles(litepublisher::$paths->themes . $themename . DIRECTORY_SEPARATOR  );
      sort($list);
      $editurl = litepublisher::$options->url . $this->url . litepublisher::$options->q . "theme=$themename&file";
      $fileitem = $html->fileitem . "\n";
      $filelist = '';
      foreach ($list as $file) {
        $filelist .= sprintf($fileitem, $editurl, $file);
      }
      $result .= sprintf($html->filelist, $filelist);
      
      if (!empty($_GET['file'])) {
        $file = $_GET['file'];
        if (strpbrk ($file, '/\<>')) return $this->notfound;
        $filename = litepublisher::$paths->themes .$themename . DIRECTORY_SEPARATOR  . $file;
        if (!@file_exists($filename)) return $this->notfound;
        $args->content = file_get_contents($filename);
        $result .= sprintf($html->h2->filename, $_GET['file']);
        $result .= $html->editform($args);
      }
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
      $result = implode("<br />\n", $parser->warnings);
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
        $parser = tthemeparser::instance();
        if (isset($parser->warnings)) $result .=implode("<br />\n", $parser->warnings);
        break;
        
        case 'edit':
        if (!empty($_GET['file']) && !empty($_GET['theme'])) {
          //проверка на безопасность, чтобы не указывали в запросе файлы не в теме
          if (strpbrk ($_GET['file'] . $_GET['theme'], '/\<>')) return '';
          if (!file_put_contents(litepublisher::$paths->themes . $_GET['theme'] . DIRECTORY_SEPARATOR . $_GET['file'], $_POST['content'])) {
            return  $this->html->h2->errorsave;
          }
        }
        break;
        
        case 'options':
        extract($_POST);
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
      }
    }
    
    litepublisher::$urlmap->clearcache();
    return $result;
  }
  
  private function getabout($name) {
    $parser = tthemeparser::instance();
    return $parser->getabout($name);
  }
  
  private function  getplugin() {
    if (!isset($this->plugin)) {
      $template =  ttemplate::instance();
      $about = $this->getabout($template->theme);
      if (empty($about['adminclassname']))  return false;
      $class = $about['adminclassname'];
      if (!class_exists($class))  require_once($template->path . $about['adminfilename']);
      $this->plugin = getinstance($class);
    }
    return $this->plugin;
  }
  
}//class
?>