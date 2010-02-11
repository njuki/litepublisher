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
    if ($plugin = $this->getplugin())  {
      $template = ttemplate::instance();
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
      $template = ttemplate::instance();
      $result .= $html->formheader();
      $list =    tfiler::getdir(litepublisher::$paths->themes);
      sort($list);
      $args->editurl = litepublisher::$options->url . $this->url . 'edit/' . litepublisher::$options->q . 'theme';
      foreach ($list as $name) {
        $about = $this->getabout($name);
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
    }
    
    return str_replace("'", '"', $result);
  }
  
  public function processform() {
    if  (isset($_POST['reparse'])) {
      $parser = tthemeparser::instance();
      $parser->reparse();
      return;
    }
    
    
    switch ($this->name) {
      case 'themes':
      if (!empty($_GET['plugin']) && ($plugin = $this->getplugin())) return $plugin->processform();
      
      if (empty($_POST['selection']))   return '';
      $template = ttemplate::instance();
      try {
        $template->theme = $_POST['selection'];
      } catch (Exception $e) {
        $template->theme = 'default';
        return 'Caught exception: '.  $e->getMessage() . "<br>\ntrace error\n<pre>\n" .  $e->getTraceAsString() . "\n</pre>\n";
      }
      return $this->html->h2->success;
      
      case 'edit':
      if (!empty($_GET['file']) && !empty($_GET['theme'])) {
        //проверка на безопасность, чтобы не указывали в запросе файлы не в теме
        if (strpbrk ($_GET['file'] . $_GET['theme'], '/\<>')) return '';
        if (!file_put_contents(litepublisher::$paths->themes . $_GET['theme'] . DIRECTORY_SEPARATOR . $_GET['file'], $_POST['content'])) {
          return  $this->html->h2->errorsave;
        }
        $urlmap = turlmap::instance();
        $urlmap->clearcache();
      }
      break;
    }
    return '';
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