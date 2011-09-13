<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcssmerger extends tjsmerger {
  
  public static function instance() {
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
echo $url;
return sprintf(' url(%s)', litepublisher::$site->files . $url);
}
  
public function readfile($filename) {
if ($result = parent::readfile($filename)) {
chdir(dirname($filename));
$result = preg_replace_callback('/\surl\s*\(\s*[\'"]?(.*?)[\'"]?\s*\)/i',
array($this, 'replaceurl'), $result);
return $result;
}
}

  public function getfilename($section, $revision) {
    return sprintf('/files/css/%s.%s.css', $section, $revision);
  }


