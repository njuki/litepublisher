<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocfilter extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'codedoc';
  }

public function getheaders(array &$a) {
$result = array();
while (count($a) > 0) && preg_match('/^\s*(\w*+)\s*[=:]\s*(\w*+)', $a[0], $m)) {
$result[$m[1]] = $m[2];
  array_splice($a, 0, 1);
}
return $result;
}

public function getbody(array &$a) {
$result = '';
while (count($a) > 0) && !preg_match('/^\s*(\w*+)\s*[=:]\s*(\w*+)', $a[0], $m)) {
$result .= array_shift($a) . "\n";
}
return trim($result);
}

public function skip(array &$a) {
while ((count($a) > 0) && (trim($a[0]) == '') ) array_splice($a, 0, 1);
}
  
  public function convert(tpost $post, $s, $type) {
    $lang = tlocal::i('codedoc');
    $s = str_replace('->', '-&gt;', $s);
$s = str_replace(array("\r\n", "\r"), "\n", $s);
$lines = explode("\n", $s);
$headers = $this->getheaders($lines);

    $result = array(
    'parent' => 0,
    'class' => $doc['name']
    );
    
    if ($post->id == 0) {
      $post->title = $doc['name'];
      $linkgen = tlinkgenerator::i();
      $post->url = $linkgen->addurl($post, 'codedoc');
    }
    
    switch ($type) {
      case 'class':
      $result['parent'] = $this->filterclass($post, $lines);
      break;
      
      case 'interface':
      $this->getinterface($post, $ini);
      break;
      
      case 'manual':
      $result['class'] = '';
      $this->getmanual($post, $ini);
      break;
    }
    
    $post->rss = $post->excerpt;
    $post->description = tcontentfilter::getpostdescription($post->excerpt);
    $post->moretitle = sprintf($lang->moretitle, $post->title);
    /*
    /$cat = tcategories::i();
    $idcat = $cat->add($lang->documentation);
    if (($idcat != 0) && !in_array($idcat , $post->categories)) $post->categories[] = $idcat;
    */
    return $result;
  }

  private function getdescription(tpost $post, $s) {
    $wiki = twikiwords::i();
    $wiki->createwords($post, $s);
    $s = $this->classtowiki($s);
    $wiki->replacewords($s);
    $s = str_replace('->', '-&gt;', $s);
    $filter = tcontentfilter::i();
    return $filter->filter($s);
  }
  
  private function classtowiki($s) {
    if (preg_match_all('/\[\[(\w*?)::(.*?)\]\]/', $s, $m, PREG_SET_ORDER)) {
      $wiki = twikiwords::i();
      foreach ($m as $item) {
        $class = trim($item[1]);
        $word = trim($item[2]);
        $link = $word;
        if ($idpost = $this->IndexOf('class', $class)) {
          $post = tpost::i();
          if ($id =$wiki->add($word, 0)) {
            $link = sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $word, $id);
          } else {
            $link = sprintf('<a href="%1$s#more-%3$d" title="%2$s">%2$s</a>', $post->link, $word, $post->id);
          }
        }
        $s = str_replace($item[0], $link, $s);
      }
    }
    return $s;
  }
  
  private function filterclass(tpost $post, array &$a) {
    $wiki = twikiwords::i();
    $headers = '';
    $content = '';
    $lang = tlocal::i('codedoc');
    $args = targs::i();

$headers = $this->getheaders($a);
$body = $this->getbody($a);
$aboutclass = $this->getaboutclass($headers, $body);
    $post->title = sprintf($lang->classtitle, $class);
$post->meta->class = $class;
    $post->excerpt = $body;
if (isset($headers['parent'])) $post->meta->parentclass = $headers['parent'];

$parts = array(
'method' => array(),
'property' =>  array(),
'event' => array()
);

$types = array_keys($parts);

//parse and collect parts
while (count($a) >0) {
$headers = $this->getheaders($a);
$body = $this->getbody($a);
foreach ($types as $type) {
if (isset($headers[$type])) {
$parts[$type][$headers[$type]] = array(
'headers' => $headers,
'body' => $body
);
break;
}
}
}

//sort by name
foreach ($types as $type) {
ksort($parts[$type]);
}

//generate content
foreach ($parts as $type => $items) {
foreach ($items as $name => $item) {
$args->add($item['headers']);
$args->body = $item['body'];
$result .= $html->$type($args);
}
}

return $result;
}
/*

    $a = array(
    'method' => 'methods',
    'property' => 'properties',
    'event' => 'events');
    
    foreach ($a as $name => $names) {
      if ($items = $this->convertitems($post, $ini, $name, $names)) {
        $headers .= sprintf(' <a href="#%1$s">%2$s</a>', $names, $lang->$names);
        $content .= $items;
      }
    }
    
    if (!empty($doc['example'])) {
      $headers .= sprintf(' <a href="#example">%s</a>', $lang->example);
      $content .= sprintf('<h2><a name="example"></a>%s</h2>', $lang->example);
      $content .= highlight_string($doc['example'], true);
    }
    
    $args->headers = $headers;
    $args->content = $content;
    $tml = file_get_contents(self::getresource() . 'class.tml');
    $theme = ttheme::i();
    $post->filtered = $theme->parsearg($tml, $args);
    return $idparent;
  }

public function getaboutclass(tpost $post, array $headers, $body) {
$class = $headers['classname'];
$lang = tlocal::i('codedoc');
$args = new targs();
    $args->class = $class;
$args->parent = isset($headers['parent']) ? sprintf('[[%s]]', $headers['parent']) : $lang->noparent;
    $args->childs = $this->getchilds($class);
    $args->source = sprintf('<a href="%1$s/source/%2$s" title="%2$s">%2$s</a>', litepublisher::$site->url, $doc['source']);
    $args->interfaces = $this->getclasses($headers, 'interface');
    $args->dependent = $this->getclasses($headers, 'dependent');
    $args->body = $body;
 return $this->html->aboutclass($args);
}
  
  private function convertitems(tpost $post, array &$ini, $name, $names) {
    if (!isset($ini[$name])) return '';
    $lang = tlocal::i('codedoc');
    $headers = $lang->$names . ': ';
    $wiki = twikiwords::i();
    $items = &$ini[$name];
    if (isset($items[0])) {
      foreach ($items as $i => $item) $list[$i] = $item['name'];
      asort($list);
      
      foreach ($list as $i => $itemname) {
        $item = $items[$i];
        $headers .= sprintf('<a href="#wikiword-%1$d" title="%2$s">%2$s</a> ', $wiki->add($itemname, $post->id), $itemname);
        $content .= $this->convertitem($post, $item, $name);
      }
    } else {
      $headers .= sprintf('<a href="#wikiword-%1$d" title="%2$s">%2$s</a> ', $wiki->add($items['name'], $post->id), $items['name']);
      $content = $this->convertitem($post, $items, $name);
    }
    
    if ($content == '') return '';
    $args = targs::i();
    $args->names = $names;
    $args->headers = $headers;
    $args->items = $content;
    return $this->html->items($args);
  }
  
  private function convertitem(tpost $post, array $item, $name) {
    $wiki = twikiwords::i();
    $args = targs::i();
    $args->add($item);
    if (!empty($item['type']) && preg_match_all('/\[\[(.*?)\]\]/i', $item['type'], $m)) {
      if ($id = $wiki->add($m[1], 0)) $args->type = $wiki->getlink($id);
    }
    $args->description = $this->getdescription($post, $item['description']);
    $args->idwiki = $wiki->add($item['name'], $post->id);
    $lang =tlocal::i('codedoc');
    if ($lang->__isset($item['access']))  $args->access = $lang->__get($item['access']);
    return $this->html->item($args);
  }
  
  public function getchilds($idpost) {
    IF ($idpost == 0) return '__childs__';
$db = litepublisher::$db;
$db->table = 'postsmeta';
    $items = $db->idselect("name = 'parentclass' and value = '$idpost'");
    if (count($items) == 0) return '';
    $names = $db->res2items($db->select(sprintf('name = \'class\' and id in(%s)', implode(',', $items)));
    $links = array();
    $posts = tposts::i();
    $posts->loaditems($items);
    foreach ($items as $id) {
      $post = tpost::i($id);
      $links[] = sprintf('<a href="%1$s#more-%3$d" title="%2$s">%2$s</a>', $post->link, $names[$id]['class'], $id);
    }
    return implode(', ', $links);
  }
  
  private function getclasses(array $doc, $name) {
    if (empty($doc[$name])) return '';
return preg_replace('/\w*+/', '[[$0]]', $doc[$name);

    $links = array();
    foreach (explode(',', $doc[$name]) as $class) {
      $class = trim($class);
      if ($class == '') continue;;
      $links[] = "[[$class]]";
    }
    return implode(', ', $links);
  }
  
  private function getclasslink($class) {
    //var_dump($this->db->res2assoc($this->db->query("select * from $this->thistable")));
    if ($idpost = $this->db->findid('class = ' .dbquote($class))) {
      $post = tpost::i($idpost);
      $wiki = twikiwords::i();
      if ($id = $wiki->IndexOf('word', $class)) {
        return sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $class, $id);
      } else {
        return sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $post->link, $class);
      }
    }
    return $class;
  }
  
  private function getinterface(tpost $post, array &$ini) {
    $doc = $ini['document'];
    $wiki = twikiwords::i();
    $lang = tlocal::i('codedoc');
    $args = targs::i();
    $class = $doc['name'];
    $post->title = sprintf($lang->interfacetitle, $class);
    $id = $wiki->add($class, $post->id);
    $args->class = sprintf('<a name="wikiword-%d"></a><strong>%s</strong>', $id, $class);
    $args->source = sprintf('<a href="%1$s/source/%2$s" title="%2$s">%2$s</a>', litepublisher::$site->url, $doc['source']);
    $content = $this->getdescription($post, $doc['description']);
    $post->excerpt = $content;
    $content .= $this->convertitems($post, $ini, 'method', 'methods');
    if (!empty($doc['example'])) {
      $headers .= sprintf(' <a href="#example">%s</a>', $lang->example);
      $content .= sprintf('<h2><a name="example"></a>%s</h2>', $lang->example);
      $content .= highlight_string($doc['example'], true);
    }
    
    $args->headers = $headers;
    $args->content = $content;
    $post->filtered = $this->html->interface($args);
  }
  
  private function getmanual(tpost $post, array &$ini) {
    $doc = $ini['document'];
    $wiki = twikiwords::i();
    $lang = tlocal::i('codedoc');
    $post->title = $doc['name'];
    
    $content = $this->getdescription($post, $doc['description']);
    $post->excerpt = $content;
    $post->excerpt = tcontentfilter::GetExcerpt($s, 250);
    
    if (!empty($doc['example'])) {
      $content = sprintf('<h2><a href="#example">%2$s</a></h2>%1$s<h2><a name="example"></a>%2$s</h2>',
      $content, $lang->example);
      $content .= highlight_string($doc['example'], true);
    }
    
    $post->filtered = $content;
  }
  
  public static function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public function gethtml($name = '') {
    if ($name == '') $name = 'codedoc';
    $result = tadminhtml ::i();
    $result->section = $name;
    $lang = tlocal::i($name);
    return $result;
  }
  
}//class