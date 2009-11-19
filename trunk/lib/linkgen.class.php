<?php

class tlinkgenerator extends tevents {
  public $DataObject;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'linkgenerator';
  }
  
  public function install() {
    $this->data['post'] = '/[title]/';
    $this->data['tag'] = '/tag/[name]/';
    $this->data['category'] = '/category/[name]/';
    $this->data['archive'] ='/[year]/[month]/';
    $this->data['file'] = '/[medium]/[filename]/';
    $this->Save();
  }
  
  public function createlink($Obj, $schema, $uniq = true) {
    $this->DataObject= $Obj;
    $result = $this->data[$schema];
    if(preg_match_all('/\[(\w+)\]/', $result, $match, PREG_SET_ORDER)) {
foreach ($match as $item) {
      $tag = $item[1];
      if (method_exists($this, $tag)) {
        $text = $this->$tag();
      } elseif( method_exists($Obj, $tag)) {
        $text = $Obj->$tag();
      } else {
        $text = $Obj->$tag;
      }
      $result= str_replace("[$tag]", $text, $result);
    }
}
    $result= $this->AfterCreate($result);
    $result= $this->validate($result);
    if ($uniq) $result = $this->MakeUnique($result);
    return $result;
  }

  public function createurl($title, $schema) {
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

    $result= $this->AfterCreate($result);
    $result= $this->validate($result);
$result = $this->MakeUnique($result);
    return $result;
  }

  public function AfterCreate($url) {
    global $options;
    if ($options->language == 'ru') $url = $this->ru2lat($url);
    return strtolower($url);
  }
  
  public function ru2lat($s) {
    static $ru2lat_iso;
    if (!isset($ru2lat_iso)) {
      global  $paths;
      require_once($paths['libinclude'] . 'ru2lat-iso.php');
    }
    return strtr($s, $ru2lat_iso);
  }
  
  public function Validate($url) {
    $url = strip_tags($url);
    $url = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $url);
    $url = str_replace('%', '', $url);
    $url = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $url);
    $url = preg_replace('/&.+?;/', '', $url); // kill entities
    $url = preg_replace('/[^%a-z0-9\.\/ _-]/', '', $url);
    $url = preg_replace('/\s+/', '-', $url);
    $url = preg_replace('|-+|', '-', $url);
    $url = trim($url, '-');
    $url = trim($url, '. ');
    $url = str_replace('..', '-', $url);
    return $url;
  }
  
  public function filterfilename($filename) {
    $filename = trim($filename);
    $filename = trim($filename, '/');
    $result = basename($filename);
    $result= $this->AfterCreate($result);
    $result= $this->Validate($result);
    return $result;
  }
  
  public function AddSlashes($url) {
    if (empty($url) || ($url == '/')) return '/';
    return '/' . trim($url, '/') . '/';
  }
  
  public function GetDate() {
    if ($this->DataObject->PropExists('date')) {
      return $this->DataObject->date;
    } else {
      return time();
    }
  }
  
  public function year() {
    return date('Y', $this->GetDate());
  }
  
  public function month() {
    return date('m', $this->GetDate());
  }
  
  public function monthname() {
    return TLocal::date($this->GetDate(), 'F');
  }
  
  public function MakeUnique($url) {
    $urlmap = turlmap::instance();
    if(!$urlmap->ItemExists($url)) return $url;
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
      if (!$urlmap->ItemExists($Result)) return $Result;
    }
    
    return "/some-wrong". time();
  }
  
}

?>