<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminthemes extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public static function isfilename($filename) {
return preg_match('/^\w[\w\.\-_]*+$/', $filename);
}

public static function file_exists($themename, $filename) {
return self::theme_exists($themename) && self::isfilename($filename) && 
file_exists(litepublisher::$paths->themes .$themename . DIRECTORY_SEPARATOR  . $filename);
}

public static function theme_exists($name) {
return preg_match('/^\w[\w\.\-_]*+$/', $themename) &&
is_dir(litepublisher::$paths->themes .$themename);
}

  public function getcontent() {
    $result = tadminviews::getviewform();
$idview = self::idget('idview', 1);
$view = tview::instance(1);
    $html = $this->html;
    $args = targs::instance();
$theme = $view->theme;

    switch ($this->name) {
      case 'themes':
      $result .= $html->formheader();
      $list =    tfiler::getdir(litepublisher::$paths->themes);
      sort($list);
      $args->editurl = self::getadminlink('/admin/views/themes/edit/', 'theme');
      $parser = tthemeparser::instance();
      foreach ($list as $name) {
        if ($about = $parser->getabout($name)) {
          $about['name'] = $name;
          $args->add($about);
          $args->checked = $name == $theme->name;
          $result .= $html->radioitem($args);
        }
      }
      $result .= $html->formfooter();
      break;
      
      case 'edit':
      $themename = self::getparam('theme', $theme->name);
if (!self::theme_exists($themename)) return $this->notfound;
      $result = sprintf($html->h2->filelist, $themename);
      $list = tfiler::getfiles(litepublisher::$paths->themes . $themename . DIRECTORY_SEPARATOR  );
      sort($list);
      $editurl = self::getadminlink('/admin/views/themes/edit/', sprintf('theme=%s&file', $themename));
      $fileitem = $html->fileitem;
      $filelist = '';
      foreach ($list as $file) {
        $filelist .= sprintf($fileitem, $editurl, $file);
      }
      $result .= sprintf($html->filelist, $filelist);
      
      if (!empty($_GET['file'])) {
        $file = $_GET['file'];
if (!self::file_exists($file)) return $this->notfound;
        $filename = litepublisher::$paths->themes .$themename . DIRECTORY_SEPARATOR  . $file;
        $args->content = file_get_contents($filename);
        $result .= sprintf($html->h2->filename, $_GET['file']);
        $result .= $html->editform($args);
      }
      break;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
$idview = self::getparam('idview', 1);
$view = tview::instance($idview);

    if  (isset($_POST['reparse'])) {
      $parser = tthemeparser::instance();
      try {
        $parser->reparse($view->theme->name);
      } catch (Exception $e) {
        $result = $e->getMessage();
      }
    } else {
      switch ($this->name) {
        case 'themes':
        if (empty($_POST['selection']))   return '';
        try {
          $view->themename = $_POST['selection'];
        $result = $this->html->h2->success;
        } catch (Exception $e) {
          $view->themename = 'default';
          $result = $e->getMessage();
        }
        break;
        
        case 'edit':
        if (empty($_GET['file']) || empty($_GET['theme'])) return '';
if (!self::file_exists($_GET['theme'], $_GET['file'])) return '';
          if (!file_put_contents(litepublisher::$paths->themes . $_GET['theme'] . DIRECTORY_SEPARATOR . $_GET['file'], $_POST['content'])) {
            $result = $this->html->h2->errorsave;
          }
        break;
    }
    
    ttheme::clearcache();
    return $result;
  }
  
}//class
?>