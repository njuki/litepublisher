<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocfilter extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }

  protected function create() {
$this->dbversion = dbversion;
    parent::create();
$this->table = 'codedoc';
}

public function getwords($words) {
$words = trim($words);
if ($words == '') return '';
$links = array();
foreach (explode(',', $words) as $word) {
$word= trim($word);
if ($word == '') continue;
$links[] = $wiki->getwordlink($word);
}
return implode(', ', $links);
}  

  public function convert(tpost $post, $s) {
$result = '';
    $this->checklang();
$ini = tini2array::parse($s);
$doc = $ini['document'];
switch ($doc['type']) {
case 'class':
$result = $this->convertclass($post, $ini);
break;

case 'interface':
$result .= $this->getinterface($post, $ini);
bbreak;

case 'manual':
$result .= $this->getmanual($post, $ini);
break;
}

    if ((!empty($doc['example'])) {
$example = highlight_string($doc['example'], true);
      $result .= sprintf('$html->example, $example);
    }

$post->filtered = $result;
$post->title = 'class ' . $doc['name'];
$post->excerpt = '';


  }

private function convertclass(tpost $post, array &$ini) {
$doc = $ini['document'];
$args = targs::instance();
$args->dependent = $this->getclasses($doc['dependent']);

$theme = ttheme::instance();
return $$theme->parsearg($html->class, $args);
}

  public function getdoccontent() {
    $lang = tlocal::instance('doc');
    $args = targs::instance();
    foreach (array('type', 'state', 'prio') as $prop) {
      $value = $this->$prop;
      $args->$prop = $lang->$value;
    }
    $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
    if ($this->assignto <= 1) {
      $profile = tprofile::instance();
      $args->assignto = $profile->nick;
    } else {
      $users = tusers::instance();
      $account = $users->getaccount($this->assignto);
      $args->assignto = $account['name'];
    }
    
    ttheme::$vars['doc'] = $this;
    $theme = ttheme::instance();
    $tml = file_get_contents($this->resource . 'doc.tml');
    $result = $theme->parsearg($tml, $args);
    if ($this->poll > 1) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll);
    }
    return $result;
  }
  
  protected function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public function checklang() {
    if (!isset(tlocal::$data['doc'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . litepublisher::$options->language . '.js');
    }
  }
  
  
}//class
?>