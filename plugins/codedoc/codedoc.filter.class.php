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
$this->classtowiki($post, $s);
$wiki->replacewords($s);
$filter = tcontentfilter::instance();
return $filter->filter($s);
}

private function classtowiki($s) {
    if (preg_match_all('/\[\[(\w*?)::(.*?)\]\]/', $s, $m, PREG_SET_ORDER)) {
$wiki = twikiwords::instance();
      foreach ($m as $item) {
        $class = trim($item[1]);
        $word = trim($item[2]);
$link = $word;
if ($idpost = $this->IndexOf('class', $class)) {
$post = tpost::instance();
        if ($id =$wiki->add($word, 0)) {
$link = sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $word, $id);
} else {
}
$s = str_replace($item[0], $link, $s);
      }
    }
return $s;
}

  public function convert(tpost $post, $s) {
    $this->checklang();
$ini = tini2array::parse($s);
$doc = &$ini['document'];
$result = array(
'parent' => 0,
'class' => $doc['name']
);

if ($post->id == 0) {
$post->title = $doc['name'];
    $linkgen = tlinkgenerator::instance();
    $post->url = $linkgen->addurl($post, 'codedoc');
}

switch ($doc['type']) {
case 'class':
$result['parent'] = $this->filterclass($post, $ini);
break;

case 'interface':
$this->getinterface($post, $ini);
bbreak;

case 'manual':
$this->getmanual($post, $ini);
break;
}

$post->rss = $post->excerpt;
return $result;
  }

private function filterclass(tpost $post, array &$ini) {
$doc = $ini['document'];
$wiki = twikiwords::instance();
$args = targs::instance();
$class = $doc['name'];
$id = $wiki->add($class, $post->id);
$args->class = sprintf('<a name="wikiword-%d"></a><strong>%s</strong>', $id, $class);

$idparent = 0;
$parent = isset($doc['parent']) ? trim($doc['parent']) : '';
if ($parent == '') {
$args->parent = '';
} else {
$args->parent = $this->getclasslink($parent);
if ($idparent = $this->db->findid('class = ' .dbquote($parent)) {
if ($post->id > 0) $this->db->setvalue($post->id, 'parent', $idparent);
} else {
$idparent = 0;
}
}

$args->childs = $this->getchilds($post->id);
$args->source = sprintf('<a href="%1$s/source/%2$s title="%2$s">%2$s</a>', $doc['source']);
$args->interfaces = $this->getclasses($doc, 'interface');
$args->dependent = $this->getclasses($doc, 'dependent');

$description = $this->getdescription($doc['description']);
$post->excerpt = $description;
$args->description = $description;

$args->methods = $this->convertitems($post, $ini, 'method');
$args->properties = $this->convertitems($post, $ini, 'property');
$args->events = $this->convertitems($post, $ini, 'event');

    if ((!empty($doc['example'])) {
$args->example = highlight_string($doc['example'], true);
    } else {
$args->example = '';
}

$tml = file_get_contents($this->resource . 'class.tml');
$theme = ttheme::instance();
$post->filtered = $theme->parsearg($tml, $args);
$post->title = $html->classtitle($doc['name']);
return $idparent;
}

private function convertitems(tpost $post, array &$ini, $name) {
if (!isset($ini[$name])) return '';
$result = '';
$items = &$ini[$name];
if (isset($items[0])) {
foreach ($items as $item) {
$result .= $this->convertitem($post, $item, $name);
}
} else {
$result .= $this->convertitem($post, $items, $name);
}
}

if ($result == '') return '';
return sprintf($this->html->items, $result);
}

private function convertitem(tpost $post, array $item, $name) {
$wiki = twikiwords::instance();
$args = targs::instance();
$args->add($item);
$args->description = $this->getdescription($post, $item]['description']);
$args->idwiki = $wiki->add($item['name'], $post->id);
if (isset(tlocal::$data['codedoc'][$item['access']]))  $args->access = tlocal::$data['codedoc'][$item['access']];
return $this->html->item($args);
}

public function getchilds($idpost) {
IF ($idpost == 0) return '__childs__';
$items = $this->db->select('parent = ' . $idparent, '');
if (count($items) == 0) return '';
$links = array();
$posts = tposts::instance();
$posts->loaditems($items);
foreach ($items as $id) {
$item = $this->getitem($id);
$post = tpost::instance($id);
$links[] = sprintf('<a href="%1$s#more-%3$d" title="%2$s">%2$s</a>', $post->link, item['class'], $id);
}
return implode(', ', $links);
}

private function getclasses(array $doc, $name) {
if (empty($doc[$name])) return '';
$links = array();
foreach (explode(',', $doc[$name]) as $class) {
$class = trim($class);
if ($class == '') continue;;
$links = $this->getclasslink($class);
}
return implode(', ', $links);
}

private function getclasslink($class) {
if ($idpost = $this->db->findid('class = ' .dbquote($class)) {
$post = tpost::instance($idpost);
if ($id = $wiki->IndexOf('word', $class)) {
return sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $class, $id);
} else {
return sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $post->link, $class);
}
}
return $class;
}

  protected function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }

  public function gethtml($name = '') {
    if ($name == '') $name = 'codedoc';
    $this->checkadminlang();
    $result = THtmlResource ::instance();
    if (!isset($result->ini['codedoc'])) {
      $result->loadini($this->resource . 'html.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'adminhtml.php', $result->ini);
    }
    $result->section = $name;
    $lang = tlocal::instance($name);
    return $result;
  }

  public function checkadminlang() {
    tlocal::loadlang('admin');
    if (!isset(tlocal::$data['codedoc'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'admin' . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . 'admin' . litepublisher::$options->language . '.js');
    }
  }

  }//class
?>