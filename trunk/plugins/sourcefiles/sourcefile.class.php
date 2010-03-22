<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsourcefile extends tpost {
  
  public static function instance($id = 0) {
    return parent::iteminstance('post', __class__, $id);
  }
  
  public function getext() {
    $parts = pathinfo($this->title);
    $result = strtolower($parts['extension']);
    if ($result == 'tml') $result = 'html';
    return $result;
  }
  
  public function  gethead() {
    $result = parent::gethead();
    $dir = litepublisher::$options->files . '/plugins/codesource/js';
    $ext = $this->ext;
    switch ($ext) {
      case 'txt':
      case 'ini':
      $ext = 'Plain';
      break;
      
      case 'js':
      $ext = 'JScript';
      break;
      
      case 'html':
      $ext = 'Xml';
      break;
      
      default:
      $ext[0] = strtoupper($ext[0]);
      break;
    }
    
    $result .= "<script type=\"text/javascript\" src=\"$dir/shCore.js\"></script>
    <script type=\"text/javascript\" src=\"$dir/shBrush$ext.js\"></script>
    <script type=\"text/javascript\">
    SyntaxHighlighter.config.clipboardSwf = '$dir/clipboard.swf';
    SyntaxHighlighter.all();
    </script>\n";
    return $result;
  }
  
  protected function getcontentpage($page) {
    $result = parent::getcontentpage($page);
    if (($page == 1){
      $filename = litepublisher::$paths->home . str_replace('/', DIRECTORY_SEPARATOR, $this->title);
      if (file_exists($filename)) {
        $source = file_get_contents($filename);
        $source = str_replace(array('"', "'", '$'), array('&quot;', '&#39;', '&#36;'), htmlspecialchars($source));
        $result .= "\n<pre class=\"brush: $this->ext;\">\n$source\n</pre>\n";
      }
      return $result;
    }
    
  }//class
  ?>