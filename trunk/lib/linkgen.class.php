<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlinkgenerator extends tevents {
  public $source;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'linkgenerator';
    $this->data= array_merge($this->data, array(
    'post' => '/[title].htm',
    'menu' => '/[title].htm',
    'tag' => '/tag/[title].htm',
    'category' => '/category/[title].htm',
    'archive' => '/[year]/[month].htm',
    'file' => '/[medium]/[filename]/',
    ));
    $this->data['urlencode'] = false;
  }
  
  public function createlink($source, $schema, $uniq) {
    $this->source= $source;
    $result = $this->data[$schema];
    if(preg_match_all('/\[(\w+)\]/', $result, $match, PREG_SET_ORDER)) {
      foreach ($match as $item) {
        $tag = $item[1];
        if (method_exists($this, $tag)) {
          $text = $this->$tag();
        } elseif( method_exists($source, $tag)) {
          $text = $source->$tag();
        } else {
          $text = $source->$tag;
        }
        $text = $this->encode($text);
        $text = str_replace('.', '-', $text);
        $result= str_replace("[$tag]", $text, $result);
      }
    }
    
    $result= $this->clean($result);
    if ($uniq) $result = $this->MakeUnique($result);
    return $result;
  }
  
  public function createurl($title, $schema, $uniq) {
    $title = $this->encode($title);
    $result = $this->data[$schema];
    $result = str_replace('[title]', $title, $result);
    if(preg_match_all('/\[(\w+)\]/', $result, $match, PREG_SET_ORDER)) {
      foreach ($match as $item) {
        $tag = $item[1];
        if (method_exists($this, $tag)) {
          $result= str_replace("[$tag]", $this->$tag(), $result);
        }
      }
    }
    
    $result= $this->clean($result);
    if ($uniq) $result = $this->MakeUnique($result);
    return $result;
  }
  
  public function encode($s) {
    if ($this->urlencode) return rawurlencode($s);
    $s = trim($s, "\n\r\t \x0B\0,.;?!/\\<>():;-\"'");
    if (litepublisher::$options->language == 'ru') $s = $this->ru2lat($s);
    return strtolower($s);
  }
  
  public function ru2lat($s) {
    static $ru2lat_iso;
    if (!isset($ru2lat_iso)) {
      require_once(litepublisher::$paths->libinclude . 'ru2lat-iso.php');
    }
    return strtr($s, $ru2lat_iso);
  }
  
  public function clean($url) {
    $url = strip_tags($url);
    $url = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $url);
    $url = str_replace('%', '', $url);
    $url = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $url);
    $url = preg_replace('/&.+?;/', '', $url); // kill entities
    $url = preg_replace('/[^%a-z0-9\.\/ _-]/', '', $url);
    $url = preg_replace('/\s+/', '-', $url);
    $url = preg_replace('|-+|', '-', $url);
    $url = trim($url, '-.');
    $url = str_replace('..', '-', $url);
    $url = '/' . ltrim($url, '/');
    return $url;
  }
  
  public function filterfilename($filename) {
    $filename = trim($filename, "\n\r\t \x0B\0,.;?!/\\<>():;-\"'");
    $result = basename($filename);
    $result= $this->encode($result);
    $result= $this->clean($result);
    return trim($result, '/');
  }
  
  public function AddSlashes($url) {
    if (empty($url) || ($url == '/')) return '/';
    return '/' . trim($url, '/') . '/';
  }
  
  public function getdate() {
    if (isset($this->source->date)) {
      return $this->source->date;
    } else {
      return time();
    }
  }
  
  public function year() {
    return date('Y', $this->getdate());
  }
  
  public function month() {
    return date('m', $this->getdate());
  }
  
  public function day() {
    return date('d', $this->getdate());
  }
  
  public function monthname() {
    return tlocal::date($this->getdate(), '%F');
  }
  
  public function MakeUnique($url) {
    $urlmap = turlmap::instance();
    if(!$urlmap->urlexists($url)) return $url;
    $l = strlen($url);
    if (substr($url, $l-1, 1) == '/') {
      $url = substr($url, 0, $l - 1);
      $sufix = '/';
    } else {
      $sufix = '';
    }
    
  if (preg_match('/(\.[a-z]{2,4})$/', $url, $match)) {
      $sufix = $match[1]. $sufix;
      $url = substr($url, 0, strlen($url) - strlen($match[1]));
    }
    for ($i = 2; $i < 1000; $i++) {
      $Result = "$url-$i$sufix";
      if (!$urlmap->urlexists($Result)) return $Result;
    }
    
    return "/some-wrong". time();
  }
  
  // $obj is tpost or tmenu
  public function addurl($obj, $schema) {
    if (!isset($obj->url)) return $this->error("The properties url and title not found");
    if ($obj->url == '' )  return $this->createlink($obj, $schema, true);
    $url = trim(strip_tags($obj->url), "\n\r\t \x0B\0,.;?!/\\<>():;-\"'");
    if ($url == '') return $this->createlink($obj, $schema, true);
    $result= '/' . $this->encode($url);
    if (strend($obj->url, '/')) $result .= '/';
    $result= $this->clean($result);
    $result = $this->MakeUnique($result);
    return $result;
  }
  
  public function editurl($obj, $schema) {
    if (!isset($obj->url) || !isset($obj->idurl) || !isset($obj->url)) return $this->error("The properties url and title not found");
    $urlmap = turlmap::instance();
    $oldurl = $urlmap->getidurl($obj->idurl);
    if ($oldurl == $obj->url)return;
    if ($obj->url == '') {
      $obj->url = $this->createlink($obj, $schema, false);
      if ($oldurl == $obj->url)return;
    }
    
    $url = trim(strip_tags($obj->url), "\n\r\t \x0B\0,.;?!/\\<>():;-\"'");
    if ($url == '') {
      $obj->url = $this->createlink($obj, $schema, false);
      if ($oldurl == $obj->url)return;
    }
    
    $url = '/' . $url;
    if (strend($obj->url, '/')) $url .= '/';
    if ($oldurl == $url){
      $obj->url = $oldurl;
      return;
    }
    
    
    $url = $this->encode($url);
    $url = $this->clean($url);
    
    if ($oldurl == $url){
      $obj->url = $oldurl;
      return;
    }
    
    //check unique url
    if ($urlitem = $urlmap->findurl($url)) {
      $url = $this->MakeUnique($url);
    }
    
    $obj->url = $url;
    $urlmap->setidurl($obj->idurl, $obj->url);
    $urlmap->addredir($oldurl, $obj->url);
  }
  
}//class

?>