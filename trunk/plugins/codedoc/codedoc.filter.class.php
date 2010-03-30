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

private function getdescription(tpost $post, $s) {
$wiki = twikiwords::instance();
$wiki->createwords($post, $s);
$this->
$this->classname2wiki($post, $s);
$wiki->replacewords($s);
return $s;
}

private function classname2wiki($s) {
    if (preg_match_all('/\[\[(\w*?)::(.*?)\]\]/', $s, $m, PREG_SET_ORDER)) {
$wiki = twikiwords::instance();
      foreach ($m as $item) {
        $class = $item[1];
        $word = $item[2];
$idpost = $this->IndexOf('class', $class);
        if ($id =$wiki->add($word, 0)) {
}
$s = str_replace($item[0], $link, $s);
      }
    }

return $s;
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
$result .= $this->convertitems($post, $ini, 'method');
return $html->class($args);
}

private function convertitems(tpost $post, array &$ini, $name) {
$result = '';
if (isset($ini[$name])) {
$items = &$ini[$name];
if (isset($items[0])) {
foreach ($items as $item) {
$result .= $this->convertitem($post, $item, $name);
}
} else {
$result .= $this->convertitem($post, $items, $name);
}
}
return $result;
}

private function convertitem(tpost $post, array $item, $name) {
$result = '';
$args = targs::instance();
$args->add($item);
$args->description = $this->getdescription($post, $item]['description']);
$html = $this->html;
return $html->$name($args);
}

  protected function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }

  public function gethtml($name = '') {
    if ($name == '') $name = 'doc';
    $this->checkadminlang();
    $result = THtmlResource ::instance();
    if (!isset($result->ini['doc'])) {
      $result->loadini($this->resource . 'html.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'adminhtml.php', $result->ini);
    }
    $result->section = $name;
    $lang = tlocal::instance($name);
    return $result;
  }

  public function checkadminlang() {
    if (!isset(tlocal::$data['doc'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.admin.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'admin' . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . 'admin' . litepublisher::$options->language . '.js');
    }
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