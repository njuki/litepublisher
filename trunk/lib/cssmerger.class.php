<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcssmerger extends tfilemerger {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'cssmerger';
  }
  
  public function replaceurl($m) {
    $url = $m[1];
    $url = realpath($url);
    $url = substr($url, strlen(litepublisher::$paths->home));
    $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
    return sprintf(' url(%s/%s)', litepublisher::$site->files, ltrim($url, '/'));
  }
  
  public function readfile($filename) {
    if ($result = parent::readfile($filename)) {
      chdir(dirname($filename));
      $result = preg_replace_callback('/\s*url\s*\(\s*[\'"]?(.*?)[\'"]?\s*\)/i',
      array($this, 'replaceurl'), $result);
      
      //delete comments
      $result = preg_replace('/\/\*.*?\*\//ims', '', $result);
      return $result;
    }
  }
  
  public function getfilename($section, $revision) {
    return sprintf('/files/js/%s.%s.css', $section, $revision);
  }
  
  public function addstyle($filename) {
    if (!($filename = $this->normfilename($filename))) return false;
    $template = ttemplate::i();
    if (strpos($template->heads, $this->basename . '_default')) {
      $this->add('default', $filename);
    } else {
      $template->addtohead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
    }
  }
  
  public function deletestyle($filename) {
    if (!($filename = $this->normfilename($filename))) return false;
    $template = ttemplate::i();
    if (strpos($template->heads, $this->basename . '_default')) {
      $this->deletefile('default', $filename);
    } else {
      $template->deletefromhead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
    }
  }
  
}//class