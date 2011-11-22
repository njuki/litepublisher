<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocfilter extends titems {
private $fix;
  
  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'codedoc';
    $this->fix = array();
}

  public function filter(tpost $post, $s, $type) {
tlocal::usefile('codedoc');
    $lang = tlocal::i('codedoc');

//prepare content
    $s = str_replace('->', '-&gt;', $s);
$s = str_replace(array("\r\n", "\r"), "\n", $s);
$s = trim($s);
$s = $this->replace_props($s);

$lines = explode("\n", $s);
    switch ($type) {
      case 'classname':
return $this->filterclass($post, $lines);
      break;
      
      case 'interface':
      $this->getinterface($post, $ini);
      break;
    }


return;    
    $post->rss = $post->excerpt;
    $post->description = tcontentfilter::getpostdescription($post->excerpt);
    $post->moretitle = sprintf($lang->moretitle, $post->title);

    $cat = tcategories::i();
    $idcat = $cat->add($lang->$type);
   if (($idcat != 0) && !in_array($idcat , $post->categories)) $post->categories[] = $idcat;
  }

public function fixpost(tpost $post) {
if (count($this->fix) == 0) return;
foreach ($this->fix as $i => $item) {
if ($post == $item['post']) {
unset($item['post']);
$item['id'] = $post->id;
$this->db->insertrow($this->db->assoctorow($item));
unset($this->fix[$i]);
}
}
}

public function html($key, targs $args) {
$theme = ttheme::instance();
return $theme->parsearg(tlocal::get('htmlcodedoc', $key), $args);
}
  
public function getheaders(array &$a) {
$result = array();
while ((count($a) > 0) && preg_match('/^\s*(\w*+)\s*[=:]\s*(.*+)/', $a[0], $m)) {
$result[$m[1]] = trim($m[2]);
  array_splice($a, 0, 1);
}
return $result;
}

public function getbody(array &$a) {
$result = '';
while ((count($a) > 0) && !preg_match('/^\s*(\w*+)\s*[=:]\s*(.*+)/', $a[0], $m)) {
$result .= array_shift($a) . "\n";
}
return trim($result);
}

public function skip(array &$a) {
while ((count($a) > 0) && (trim($a[0]) == '') ) array_splice($a, 0, 1);
}
  

  public function replace_props($s) {
    if (preg_match_all('/\[\[(\w*?)::(.*?)\]\]/', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $class = $item[1];
        $prop = $item[2];
        if ($idpost = $this->find_class($class)) {
          $post = tpost::i($idpost);
            $link = sprintf('<a href="%1$s#itemdoc-%2$s" title="%2$s">%2$s</a>', $post->link, $prop);
          } else {
            $link = $prop;
        }

        $s = str_replace($item[0], $link, $s);
      }
    }
    return $s;
  }

public function find_class($class) {
//check cache array
if (isset($this->classes[$class])) return $this->classes[$class];
litepublisher::$db->table = 'postsmeta';
$result = litepublisher::$db->findid("name = 'class' and value = " . dbquote($class));
$this->classes[$class] = $result;
return $result;
}
  
  public function filterclass(tpost $post, array &$a) {
    $lang = tlocal::i('codedoc');
    $args = new targs();

$headers = $this->getheaders($a);
$body = $this->getbody($a);
dumpvar($headers);
dumpstr($body);
$result = $this->getaboutclass($headers, $body);
//dumpstr($result);
$class =$headers['classname'];
$parentclass = isset($headers['parent']) ? $headers['parent'] : '';
$docitem = array(
'id' => $post->id,
'class' => $class,
'parentclass' => $parentclass,
'methods' => '',
'properties' => '',
'events' => ''
);

/*
    $post->excerpt = tcontentfilter::i()->filter($body);
$post->rss = $post->excerpt;
    if ($post->id == 0) {
    $post->title = sprintf($lang->classtitle, $class);
      $linkgen = tlinkgenerator::i();
      $post->url = $linkgen->addurl($post, 'codedoc');
}
*/
$parts = array(
'method' => array(),
'prop' =>  array(),
'event' => array()
);

$types = array_keys($parts);

//parse content and collect parts
while (count($a) >0) {
$headers = $this->getheaders($a);
$body = $this->getbody($a);
if (isset($headers['property'])) {
$headers['prop'] = $headers['property'];
unset($headers['property']);
}
foreach ($types as $type) {
if (isset($headers[$type])) {
$name = $headers[$type];
$parts[$type][$name] = array(
'headers' => $headers,
'body' => $body
);
break;
}
}
}

//sort by name
$maxcount = 0;
foreach ($types as $type) {
if (count($parts[$type]) > 0) {
ksort($parts[$type]);
$docitem[$type . 's'] = implode(',', array_keys($parts[$type]));
$maxcount = max($maxcount, count($parts[$type]));
} else {
unset($parts[$type]);
}
}

if ($post->id > 0) {
$this->db->insert($docitem);
} else {
$docitem['post'] = $post;
$this->fix[] = $docitem;
$post->onid = $this->fixpost;
}

//generate content
$tablehead = '';
$rows = array_fill(0, $maxcount, '');
foreach ($parts as $type => $items) {
$i = 0;
$args->toctype = $type;
$args->tocname = $lang->{$type . 's'};
$args->itemname = $lang->$type;
$tablehead .= $this->html('tablehead', $args);
$result .= $this->html('items', $args);
foreach ($items as $name => $item) {
$args->add($item['headers']);
$args->name = $name;
$args->body = $item['body'];
$access = isset($item['headers']['access']) ? $item['headers']['access'] : 'public';
$args->access = isset($lang->$access) ? $lang->$access : $access;
$rows[$i++] .= $this->html('itemtoc', $args);
$result .= $this->html('item',  $args);
}
while ($i < $maxcount) $rows[$i++] .= '<td></td>';
}

$args->tablehead = $tablehead;
$args->itemtoc = implode('</tr><tr>', $rows);
$toc = $this->html('toc', $args);

dumpstr($toc . $result);
return $toc . $result;
}

public function getaboutclass(array $headers, $body) {
$class = $headers['classname'];
$lang = tlocal::i('codedoc');
$args = new targs();
    $args->class = $class;
$args->parent = isset($headers['parent']) ? sprintf('[[%s]]', $headers['parent']) : $lang->noparent;
    $args->childs = $this->getchilds($class);
    $args->source = sprintf('<a href="%1$s/source/%2$s" title="%2$s">%2$s</a>', litepublisher::$site->url, $headers['source']);
    $args->interfaces = $this->getclasses($headers, 'interface');
    $args->dependent = $this->getclasses($headers, 'dependent');
    $args->body = $body;
 return $this->html('class', $args);
}
  
  public function getchilds($parent) {
    IF ($parent == '') return '';
    $items = $this->db->res2items($this->db->query(
sprintf('select id, class from %s where parentclass = %s order by class', $this->thistable, dbquote($parent)) ));
    if (count($items) == 0) return '';

    $links = array();
tposts::i()->loaditems(array_keys($items));
    foreach ($items as $id => $item) {
      $post = tpost::i($id);
      $links[] = sprintf('<a href="%1$s#more-%3$d" title="%2$s">%2$s</a>', $post->link, $item['class']);
    }
    return implode(', ', $links);
  }
  
  private function getclasses(array $doc, $name) {
    if (empty($doc[$name])) return '';
return preg_replace('/\w\w*+/', '[[$0]]', $doc[$name]);
}

}//class