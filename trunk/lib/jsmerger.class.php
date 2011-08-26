<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsmerger extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'jsmerger';
    $this->data['revision'] = 0;
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    $this->data['revision']++;
    parent::save();
    $this->assemble();
  }

public function normfilename($filename) {
    $filename = trim($filename);
    if (strbegin($filename,litepublisher::$paths->home)) $filename = substr($filename, strlen(litepublisher::$paths->home));
    if (empty($filename)) return false;
$filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
    $filename = '/' . ltrim($filename, '/');
return $filename;
}
  
  public function add($section, $filename) {
if (!($filename = $this->normfilename($filename))) return false;
    if (!isset($this->items[$section])) {
      $this->items[$section] = array(
      'files' => array($filename),
      'texts' => array()
      );
    } else {
      if (in_array($filename, $this->items[$section]['files'])) return false;
      $this->items[$section]['files'][] = $filename;
    }
    $this->save();
    return count($this->items[$section]['files']) - 1;
  }
  
  public function deletefile($section, $filename) {
    if (!isset($this->items[$section])) return false;
if (!($filename = $this->normfilename($filename))) return false;
    if (false === ($i = array_search($filename, $this->items[$section]['files']))) return false;
    array_delete($this->items[$section]['files'], $i);
    $this->save();
  }
  
  public function setfromstring($section, $s) {
    $this->lock();
    if (isset($this->items[$section])) {
      $this->items[$section]['files'] = array();
    } else {
      $this->items[$section] = array(
      'files' => array(),
      'texts' => array()
      );
    }
    
    $a = explode("\n", trim($s));
    foreach ($a as $filename) {
      $this->add($section, trim($filename));
    }
    $this->unlock();
  }
  
  public function addtext($section, $key, $s) {
    $s = trim($s);
    if (empty($s)) return false;
    if (!isset($this->items[$section])) {
      $this->items[$section] = array(
      'files' => array(),
      'texts' => array($key => $s)
      );
    } else {
      if (in_array($s, $this->items[$section]['texts'])) return false;
      $this->items[$section]['texts'][$key] = $s;
    }
    $this->save();
    return count($this->items[$section]['texts']) - 1;
  }
  
  public function deletetext($section, $key) {
    if (!isset($this->items[$section]['texts'][$key])) return;
    unset($this->items[$section]['texts'][$key]);
    $this->save();
    return true;
  }
  
  public function getfilename($section) {
    return sprintf('/files/js/%s.%s.js', $section, $this->revision);
  }
  
  public function assemble() {
    $home = rtrim(litepublisher::$paths->home, DIRECTORY_SEPARATOR);
    $theme = ttheme::instance();
    $template = ttemplate::instance();
    foreach ($this->items as $section => $items) {
      $s = '';
      foreach ($items['files'] as $filename) {
        $filename = $theme->parse($filename);
        $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
        if (false === ($file = file_get_contents($home . $filename))) $this->error(sprintf('Error read %s file', $filename));
        $s .= $file;
$s .= "\n"; //prevent coomments
      }
      $s .= implode("\n", $items['texts']);
      $jsfile =  $this->getfilename($section);
      $realfile= $home . str_replace('/',DIRECTORY_SEPARATOR, $jsfile);
      file_put_contents($realfile, $s);
      @chmod($realfile, 0666);
      $template->data['jsmerger_' . $section] = $jsfile;
    }
    $template->save();
    litepublisher::$urlmap->clearcache();
    foreach (array_keys($this->items) as $section) {
      $old = $home . str_replace('/',DIRECTORY_SEPARATOR, sprintf('/files/js/%s.%s.js', $section, $this->revision - 1));
      if (file_exists($old)) @unlink($old);
    }
  }
  
  public function onupdated() {
    tlocal::loadlang('admin');
    $this->lock();
  $js = "var lang;\nif (lang == undefined) lang = {};\n";
    $widgetlang = array(
    'expand' => tlocal::$data['default']['expand'],
    'colapse' => tlocal::$data['default']['colapse']
    );
    
    $this->addtext('default', 'widgetlang', $js . sprintf('lang.widgetlang= %s;',  json_encode($widgetlang)));
    $this->addtext('comments', 'lang', $js . sprintf('lang.comment = %s;',  json_encode(tlocal::$data['comment'])));
    $this->addtext('moderate', 'lang', $js . sprintf('lang.comments = %s;',  json_encode(tlocal::$data['comments'])));
    
    $this->unlock();
  }
  
}//class