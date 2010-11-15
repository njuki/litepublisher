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
    $s = $this->classtowiki($s);
    $wiki->replacewords($s);
    $s = str_replace('->', '-&gt;', $s);
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
            $link = sprintf('<a href="%1$s#more-%3$d" title="%2$s">%2$s</a>', $post->link, $word, $post->id);
          }
        }
        $s = str_replace($item[0], $link, $s);
      }
    }
    return $s;
  }
  
  public function convert(tpost $post, $s) {
    $this->checkadminlang();
    $lang = tlocal::instance('codedoc');
    $ini = tini2array::parse($s);
    $doc = $ini['document'];
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
    /$cat = tcategories::instance();
    $idcat = $cat->add($lang->documentation);
    if (($idcat != 0) && !in_array($idcat , $post->categories)) $post->categories[] = $idcat;
    */
    return $result;
  }
  
  private function filterclass(tpost $post, array &$ini) {
    $doc = $ini['document'];
    $wiki = twikiwords::instance();
    $headers = '';
    $content = '';
    $lang = tlocal::instance('codedoc');
    $args = targs::instance();
    $class = $doc['name'];
    $post->title = sprintf($lang->classtitle, $class);
    $id = $wiki->add($class, $post->id);
    $args->class = sprintf('<a name="wikiword-%d"></a><strong>%s</strong>', $id, $class);
    
    $idparent = 0;
    $parent = isset($doc['parent']) ? trim($doc['parent']) : '';
    if ($parent == '') {
      $args->parent = '';
    } else {
      $args->parent = $this->getclasslink($parent);
      if ($idparent = $this->db->findid('class = ' .dbquote($parent))) {
        if ($post->id > 0) $this->db->setvalue($post->id, 'parent', $idparent);
      } else {
        $idparent = 0;
      }
    }
    
    $args->childs = $this->getchilds($post->id);
    $args->source = sprintf('<a href="%1$s/source/%2$s" title="%2$s">%2$s</a>', litepublisher::$site->url, $doc['source']);
    $args->interfaces = $this->getclasses($doc, 'interface');
    $args->dependent = $this->getclasses($doc, 'dependent');
    $description = $this->getdescription($post, $doc['description']);
    $post->excerpt = $description;
    $args->description = $description;
    
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
    $theme = ttheme::instance();
    $post->filtered = $theme->parsearg($tml, $args);
    return $idparent;
  }
  
  private function convertitems(tpost $post, array &$ini, $name, $names) {
    if (!isset($ini[$name])) return '';
    $lang = tlocal::instance('codedoc');
    $headers = $lang->$names . ': ';
    $wiki = twikiwords::instance();
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
    $args = targs::instance();
    $args->names = $names;
    $args->headers = $headers;
    $args->items = $content;
    return $this->html->items($args);
  }
  
  private function convertitem(tpost $post, array $item, $name) {
    $wiki = twikiwords::instance();
    $args = targs::instance();
    $args->add($item);
    if (!empty($item['type']) && preg_match_all('/\[\[(.*?)\]\]/i', $item['type'], $m)) {
      if ($id = $wiki->add($m[1], 0)) $args->type = $wiki->getlink($id);
    }
    $args->description = $this->getdescription($post, $item['description']);
    $args->idwiki = $wiki->add($item['name'], $post->id);
    if (isset(tlocal::$data['codedoc'][$item['access']]))  $args->access = tlocal::$data['codedoc'][$item['access']];
    return $this->html->item($args);
  }
  
  public function getchilds($idpost) {
    IF ($idpost == 0) return '__childs__';
    $items = $this->select('parent = ' . $idpost, '');
    if (count($items) == 0) return '';
    $links = array();
    $posts = tposts::instance();
    $posts->loaditems($items);
    foreach ($items as $id) {
      $item = $this->getitem($id);
      $post = tpost::instance($id);
      $links[] = sprintf('<a href="%1$s#more-%3$d" title="%2$s">%2$s</a>', $post->link, $item['class'], $id);
    }
    return implode(', ', $links);
  }
  
  private function getclasses(array $doc, $name) {
    if (empty($doc[$name])) return '';
    $links = array();
    foreach (explode(',', $doc[$name]) as $class) {
      $class = trim($class);
      if ($class == '') continue;;
      $links[] = $this->getclasslink($class);
    }
    return implode(', ', $links);
  }
  
  private function getclasslink($class) {
    //var_dump($this->db->res2assoc($this->db->query("select * from $this->thistable")));
    if ($idpost = $this->db->findid('class = ' .dbquote($class))) {
      $post = tpost::instance($idpost);
      $wiki = twikiwords::instance();
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
    $wiki = twikiwords::instance();
    $lang = tlocal::instance('codedoc');
    $args = targs::instance();
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
    $wiki = twikiwords::instance();
    $lang = tlocal::instance('codedoc');
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
    $this->checkadminlang();
    $result = tadminhtml ::instance();
    if (!isset($result->ini['codedoc'])) {
      $result->loadini(self::getresource() . 'html.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'adminhtml.php', $result->ini);
    }
    $result->section = $name;
    $lang = tlocal::instance($name);
    return $result;
  }
  
  public function checkadminlang() {
    tlocal::loadlang('admin');
    if (!isset(tlocal::$data['codedoc'])) {
      tlocal::loadini(self::getresource() . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'admin' . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . 'admin' . litepublisher::$options->language . '.js');
    }
  }
  
}//class
?>