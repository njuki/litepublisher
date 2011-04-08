<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
//items.posts.class.php
class titemsposts extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'itemsposts';
    $this->table = 'itemsposts';
  }
  
  public function add($idpost, $iditem) {
    if (dbversion) {
      $this->db->add(array(
      'post' => $idpost,
      'item' => $iditem
      ));
      $this->added();
    } else {
      if (!isset($this->items[$idpost]))  $this->items[$idpost] = array();
      if (!in_array($iditem, $this->items[$idpost])) {
        $this->items[$idpost][] =$iditem;
        $this->save();
        $this->added();
        return true;
      }
      return false;
    }
  }
  
  public function exists($idpost, $iditem) {
    if ($this->dbversion) {
      return $this->db->exists("post = $idpost and item = $iditem");
    } else {
      return isset($this->items[$idpost]) && is_int(array_search($iditem, $this->items[$idpost]));
    }
  }
  
  public function remove($idpost, $iditem) {
    if (dbversion) {
      return $this->db->delete("post = $idpost and item = $iditem");
    } elseif (isset($this->items[$idpost])) {
      $i = array_search($iditem, $this->items[$idpost]);
      if (is_int($i))  {
        array_splice($this->items[$idpost], $i, 1);
        $this->save();
        $this->deleted();
        return true;
      }
      return false;
    }
  }
  
  public function delete($idpost) {
    return $this->deletepost($idpost);
  }
  
  public function deletepost($idpost) {
    if (dbversion) {
      $result = litepublisher::$db->res2id(litepublisher::$db->query("select item from $this->thistable where post = $idpost"));
      $this->db->delete("post = $idpost");
      return $result;
    } else {
      if (isset($this->items[$idpost])) {
        $result = $this->items[$idpost];
        unset($this->items[$idpost]);
        $this->save();
        return $result;
      } else {
        return array();
      }
    }
  }
  
  public function deleteitem($iditem) {
    if (dbversion) {
      $this->db->delete("item = $iditem");
    } else {
      foreach ($this->items as $idpost => $item) {
        $i = array_search($iditem, $item);
        if (is_int($i))  array_splice($this->items[$idpost], $i, 1);
      }
      $this->save();
    }
    $this->deleted();
  }
  
  public function setitems($idpost, array $items) {
    $items = array_unique($items);
    // delete zero item
    if (false !== ($i = array_search(0, $items))) array_splice($items, $i, 1);
    if (dbversion) {
      $db = $this->db;
      $old = $this->getitems($idpost);
      $add = array_diff($items, $old);
      $delete = array_diff($old, $items);
      
      if (count($delete) > 0) {
        $db->delete("post = $idpost and item in (" . implode(', ', $delete) . ')');
      }
      
      if (count($add) > 0) {
        $vals = array();
        foreach ($add as $iditem) {
          $vals[]= "($idpost, $iditem)";
        }
        $db->exec("INSERT INTO $this->thistable (post, item) values " . implode(',', $vals) );
      }
      
      return array_merge($old, $add);
    } else {
      if (!isset($this->items[$idpost])) {
        $this->items[$idpost] = $items;
        $this->save();
        return $items;
      } else {
        $result = array_merge($this->items[$idpost], array_diff($items, $this->items[$idpost]));
        $this->items[$idpost] = $items;
        $this->save();
        return $result;
      }
    }
  }
  
  public function getitems($idpost) {
    if (dbversion) {
      return litepublisher::$db->res2id(litepublisher::$db->query("select item from $this->thistable where post = $idpost"));
    } elseif (isset($this->items[$idpost])) {
      return $this->items[$idpost];
    } else {
      return false;
    }
  }
  
  public function getposts($iditem) {
    if (dbversion) {
      return litepublisher::$db->res2id(litepublisher::$db->query("select post from $this->thistable where item = $iditem"));
    } else {
      $result = array();
      foreach ($this->items as $id => $item) {
        if (in_array($iditem, $item)) $result[] = $id;
      }
      return $result;
    }
  }
  
  public function getpostscount($ititem) {
    $items = $this->getposts($ititem);
    $posts = tposts::instance();
    $items = $posts->stripdrafts($items);
    return count($items);
  }
  
  public function updateposts(array $list, $propname) {
    if (dbversion) {
      $db = $this->db;
      foreach ($list as $idpost) {
        $items = $this->getitems($idpost);
        $db->table = 'posts';
        $db->setvalue($idpost, $propname, implode(', ', $items));
      }
    } else {
      foreach ($list as $idpost) {
        $items = $this->items[$idpost];
        $post = tpost::instance($idpost);
        if ($items != $post->$propname) {
          $post->$propname = $items;
          $post->Save();
        }
      }
    }
  }
  
}//class

class titemspostsowner extends titemsposts {
  private $owner;
  public function __construct($owner) {
    if (!isset($owner)) return;
    parent::__construct();
    $this->owner = $owner;
    if ($owner->dbversion) {
      $this->table = $owner->table . 'items';
    } else {
      $this->items = &$owner->data['itemsposts'];
    }
    
    $this->dbversion = $owner->dbversion;
  }
  
public function load() { }
public function save() { $this->owner->save(); }
public function lock() { $this->owner->lock(); }
public function unlock() { $this->owner->unlock(); }
  
}//class

//post.class.php
class tpost extends titem implements  itemplate {
  public $childdata;
  public $childtable;
  private $aprev;
  private $anext;
  private $ameta;
  private $_theme;
  
  public static function instance($id = 0) {
    $id = (int) $id;
    if (dbversion && ($id > 0)) {
      if (isset(self::$instances['post'][$id]))     return self::$instances['post'][$id];
      if ($result = self::loadpost($id)) {
        self::$instances['post'][$id] = $result;
        return $result;
      }
      return null;
    }
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getinstancename() {
    return 'post';
  }
  
  public static function getchildtable() {
    return '';
  }
  
  public static function newpost($class) {
    if (empty($class)) $class = __class__;
    return new $class();
  }
  
  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
  }
  
  protected function create() {
    $this->table = 'posts';
    //last binding, like cache
    $this->childtable = call_user_func_array(array(get_class($this), 'getchildtable'), array());
    $this->data['childdata'] = &$this->childdata;
    $this->data= array(
    'id' => 0,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0,
    'revision' => 0,
    'icon' => 0,
    'class' => __class__,
    'posted' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'title2' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => dbversion ? false : '',
    'description' => '',
    'moretitle' => '',
    'categories' => array(),
    'tags' => array(),
    'files' => array(),
    'status' => 'published',
    'commentsenabled' => litepublisher::$options->commentsenabled,
    'pingenabled' => litepublisher::$options->pingenabled,
    'password' => '',
    'idview' => 1,
    'commentscount' => 0,
    'pingbackscount' => 0,
    'pagescount' => 0,
    'pages' => array()
    );
    
    $posts = tposts::instance();
    foreach ($posts->itemcoclasses as $class) {
      $coinstance = litepublisher::$classes->newinstance($class);
      $coinstance->post = $this;
      $this->coinstances[]  = $coinstance;
    }
  }
  
  public function getdbversion() {
    return dbversion;
  }
  
  public function __get($name) {
    if ($this->childtable) {
      if ($name == 'id') return $this->data['id'];
      if (method_exists($this, $get = 'get' . $name))   return $this->$get();
      if (array_key_exists($name, $this->childdata)) return $this->childdata[$name];
    }
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if ($this->childtable) {
      if ($name == 'id') return $this->setid($value);
      if (method_exists($this, $set = 'set'. $name)) return $this->$set($value);
      if (array_key_exists($name, $this->childdata)) {
        $this->childdata[$name] = $value;
        return true;
      }
    }
    return parent::__set($name, $value);
  }
  
  public function __isset($name) {
    return parent::__isset($name) || ($this->childtable && array_key_exists($name, $this->childdata) );
  }
  
  //db
  public function afterdb() {
    //$this->childdata['reproduced'] = $this->childdata['reproduced'] == '1';
  }
  
  public function beforedb() {
    //if ($this->childdata['closed'] == '') $this->childdata['closed'] = sqldate();
  }
  
  public function load() {
    $result = dbversion? $this->LoadFromDB() : parent::load();
    if ($result) {
      foreach ($this->coinstances as $coinstance) $coinstance->load();
    }
    return $result;
  }
  
  protected function LoadFromDB() {
    if ($a = self::getassoc($this->id)) {
      $this->setassoc($a);
      return true;
    }
    return false;
  }
  
  public static function loadpost($id) {
    if ($a = self::getassoc($id)) {
      $self = self::newpost($a['class']);
      $self->setassoc($a);
      return $self;
    }
    return false;
  }
  
  public static function getassoc($id) {
    $db = litepublisher::$db;
    return $db->selectassoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $id and  $db->urlmap.id  = $db->posts.idurl limit 1");
  }
  
  public function setassoc(array $a) {
    $trans = tposttransform::instance($this);
    $trans->setassoc($a);
    if ($this->childtable) {
      if ($a = $this->getdb($this->childtable)->getitem($this->id)) {
        $this->childdata = $a;
        $this->afterdb();
      }
    }
  }
  
  public function save() {
    parent::save();
    foreach ($this->coinstances as $coinstance) $coinstance->save();
  }
  
  protected function SaveToDB() {
    tposttransform ::instance($this)->save();
    if ($this->childtable) {
      $this->beforedb();
      $this->childdata['id'] = $this->id;
      $this->getdb($this->childtable)->updateassoc($this->childdata);
    }
  }
  
  public function addtodb() {
    $id = tposttransform ::add($this);
    $this->setid($id);
    if ($this->childtable) {
      $this->beforedb();
      $this->childdata['id'] = $id;
      $this->getdb($this->childtable)->insert_a($this->childdata);
    }
    return $id;
  }
  
  public function free() {
    foreach ($this->coinstances as $coinstance) $coinstance->free();
    unset($this->aprev, $this->anext, $this->ameta, $this->_theme);
    parent::free();
  }
  
  public function getcomments() {
    return tcomments::instance($this->id);
  }
  
  public function getpingbacks() {
    return tpingbacks::instance($this->id);
  }
  
  public function getprev() {
    if (!is_null($this->aprev)) return $this->aprev;
    $this->aprev = false;
    if (dbversion) {
      if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
        $this->aprev = self::instance($id);
      }
      return false;
    } else {
      $posts = tposts::instance();
      $keys = array_keys($posts->archives);
      $i = array_search($this->id, $keys);
      if ($i < count($keys) -1) $this->aprev = self::instance($keys[$i + 1]);
    }
    return $this->aprev;
  }
  
  public function getnext() {
    if (!is_null($this->anext)) return $this->anext;
    $this->anext = false;
    if (dbversion) {
      if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
        $this->anext = self::instance($id);
      }
    } else {
      $posts = tposts::instance();
      $keys = array_keys($posts->archives);
      $i = array_search($this->id, $keys);
      if ($i > 0 ) $this->anext = self::instance($keys[$i - 1]);
      
    }
    return $this->anext;
  }
  
  public function getmeta() {
    if (!isset($this->ameta)) {
      $this->ameta = tmetapost::instance($this->id);
    }
    return $this->ameta;
  }
  
  public function Getlink() {
    return litepublisher::$site->url . $this->url;
  }
  
  public function Setlink($link) {
    if ($a = @parse_url($link)) {
      if (empty($a['query'])) {
        $this->url = $a['path'];
      } else {
        $this->url = $a['path'] . '?' . $a['query'];
      }
    }
  }
  
  public function gettheme() {
    ttheme::$vars['post'] = $this;
    if (isset($this->_theme)) return $this->_theme;
    $this->_theme = tview::getview($this)->theme;
    return $this->_theme;
  }
  
  public function getbookmark() {
    return $this->theme->parse('<a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.iconlink$post.title</a>');
  }
  
  public function getrsscomments() {
    return litepublisher::$site->url . "/comments/$this->id.xml";
  }
  
  public function Getpubdate() {
    return date('r', $this->posted);
  }
  
  public function Setpubdate($date) {
    $this->data['posted'] = strtotime($date);
  }
  
  public function getsqldate() {
    return sqldate($this->posted);
  }
  
  //template
  public function getexcerptcatlinks() {
    return $this->getcommontagslinks('categories', true);
  }
  
  public function getexcerpttaglinks() {
    return $this->getcommontagslinks('tags', true);
  }
  
  public function getcatlinks() {
    return $this->getcommontagslinks('categories', false);
  }
  
  public function Gettaglinks() {
    return $this->getcommontagslinks('tags', false);
  }
  
  private function getcommontagslinks($names, $excerpt) {
    if (count($this->$names) == 0) return '';
    $theme = $this->theme;
    $tmlpath= $excerpt ? 'content.excerpts.excerpt' : 'content.post';
    $tmlpath .= $names == 'tags' ? '.taglinks' : '.catlinks';
    $tmlitem = $theme->templates[$tmlpath . '.item'];
    $tags= litepublisher::$classes->$names;
    $tags->loaditems($this->$names);
    $args = targs::instance();
    $list = array();
    foreach ($this->$names as $id) {
      $item = $tags->getitem($id);
      $args->add($item);
      if (($item['icon'] == 0) || litepublisher::$options->icondisabled) {
        $args->icon = '';
      } else {
        $files = tfiles::instance();
        if ($files->itemexists($item['icon'])) {
          $args->icon = $files->geticon($item['icon']);
        } else {
          $args->icon = '';
        }
      }
      $list[] = $theme->parsearg($tmlitem,  $args);
    }
    
    return str_replace('$items', implode($theme->templates[$tmlpath . '.divider'], $list), $theme->parse($theme->templates[$tmlpath]));
  }
  
  public function getdate() {
    return tlocal::date($this->posted, $this->theme->templates['content.post.date']);
  }
  
  public function getexcerptdate() {
    return tlocal::date($this->posted, $this->theme->templates['content.excerpts.excerpt.date']);
  }
  
  public function getday() {
    return date($this->posted, 'D');
  }
  
  public function getmonth() {
    return tlocal::date($this->posted, 'M');
  }
  
  public function getyear() {
    return date($this->posted, 'Y');
  }
  
  public function getmorelink() {
    if ($this->moretitle == '') return '';
    return $this->theme->parse($this->theme->content->excerpts->excerpt->morelink);
  }
  
  public function gettagnames() {
    if (count($this->tags) == 0) return '';
    $tags = ttags::instance();
    return implode(', ', $tags->getnames($this->tags));
  }
  
  public function settagnames($names) {
    $tags = ttags::instance();
    $this->tags=  $tags->createnames($names);
  }
  
  public function getcatnames() {
    if (count($this->categories) == 0)  return '';
    $categories = tcategories::instance();
    return implode(', ', $categories->getnames($this->categories));
  }
  
  public function setcatnames($names) {
    $categories = tcategories::instance();
    $this->categories = $categories->createnames($names);
    if (count($this->categories ) == 0) {
      $defaultid = $categories->defaultid;
      if ($defaultid > 0) $this->data['categories '][] =  $dfaultid;
    }
  }
  
  public function getcategory() {
    if (count($this->categories) == 0) return '';
    $cats = tcategories::instance();
    return $cats->getname($this->categories[0]);
  }
  
  //ITemplate
  public function request($id) {
    parent::request((int) $id);
    if ($this->status != 'published') return 404;
  }
  
  public function gettitle() {
    if ($this->data['title2'] != '') return $this->data['title2'];
    return $this->data['title'];
  }
  
  public function gethead() {
    $result = '';
    $options = litepublisher::$options;
    $template = ttemplate::instance();
    $template->ltoptions[] = 'idpost: ' . $this->id;
    
    if ($prev = $this->prev) $result .= "<link rel=\"prev\" title=\"$prev->title\" href=\"$prev->link\" />\n";
    if ($next = $this->next) $result .= "<link rel=\"next\" title=\"$next->title\" href=\"$next->link\" />\n";
    
    if ($this->commentsenabled && ($this->commentscount > 0) ) {
      $lang = tlocal::instance('comment');
      $result .= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"$lang->onpost $this->title\" href=\"$this->rsscomments\" />\n";
    }
    
    return $result;
  }
  
  public function getkeywords() {
    return $this->Gettagnames();
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      if ($this->dbversion) {
        $this->db->setvalue($this->id, 'idview', $id);
      } else {
        $this->save();
      }
    }
  }
  
  public function geticonurl() {
    if ($this->icon == 0) return '';
    $files = tfiles::instance();
    if ($files->itemexists($this->icon)) return $files->geturl($this->icon);
    $this->icon = 0;
    $this->save();
    return '';
  }
  
  public function geticonlink() {
    if (($this->icon == 0) || litepublisher::$options->icondisabled) return '';
    $files = tfiles::instance();
    if ($files->itemexists($this->icon)) return $files->geticon($this->icon);
    $this->icon = 0;
    $this->save();
    return '';
  }
  
  public function getfilelist() {
    if (count($this->files) == 0) return '';
    $files = tfiles::instance();
    return $files->getfilelist($this->files, false);
  }
  
  public function getexcerptfilelist() {
    if (count($this->files) == 0) return '';
    $files = tfiles::instance();
    return $files->getfilelist($this->files, true);
  }
  
  public function getcont() {
    return $this->theme->content->post();
  }
  
  public function getrsslink() {
    if ($this->commentsenabled && ($this->commentscount > 0)) {
      return $this->theme->content->post->rsslink();
    }
    return '';
  }
  
  public function onrssitem($item) {
  }
  
  public function getprevnext() {
    $prev = '';
    $next = '';
    $theme = $this->theme;
    if ($prevpost = $this->prev) {
      ttheme::$vars['prevpost'] = $prevpost;
      $prev = $theme->parse($theme->templates['content.post.prevnext.prev']);
    }
    
    if ($nextpost = $this->next) {
      ttheme::$vars['nextpost'] = $nextpost;
      $next = $theme->parse($theme->templates['content.post.prevnext.next']);
    }
    
    if (($prev == '') && ($next == '')) return '';
    $result = strtr(    $theme->parse($theme->templates['content.post.prevnext']), array(
    '$prev' => $prev,
    '$next' => $next
    ));
    unset(ttheme::$vars['prevpost'],ttheme::$vars['nextpost']);
    return $result;
  }
  
  public function getcommentslink() {
    if (($this->commentscount == 0) && !$this->commentsenabled) return '';
    return sprintf('<a href="%s%s#comments">%s</a>', litepublisher::$site->url, $this->getlastcommenturl(), $this->getcmtcount());
  }
  
  public function getcmtcount() {
    $l = &tlocal::$data['comment'];
    switch($this->commentscount) {
      case 0: return $l[0];
      case 1: return $l[1];
      default: return sprintf($l[2], $this->commentscount);
    }
  }
  
  public function  gettemplatecomments() {
    if (($this->commentscount == 0) && !$this->commentsenabled && ($this->pingbackscount ==0)) return '';
    if ($this->haspages && ($this->commentpages < $urlmap->page)) return $this->getcommentslink();
    $tc = ttemplatecomments::instance();
    return $tc->getcomments($this->id);
  }
  
  public function get_excerpt() {
    return $this->data['excerpt'];
  }
  
  public function getexcerptcontent() {
    $posts = tposts::instance();
    if ($this->revision < $posts->revision) $this->setrevision($posts->revision);
    $result = $this->get_excerpt();
    $posts->beforeexcerpt($this, $result);
    $result = $this->replacemore($result, true);
    if (litepublisher::$options->parsepost) {
      $result = $this->theme->parse($result);
    }
    $posts->afterexcerpt($this, $result);
    return $result;
  }
  
  public function replacemore($content, $excerpt) {
    $theme = $this->theme;
    $more = $theme->parse($excerpt ?
    $theme->templates['content.excerpts.excerpt.morelink'] :
    $theme->templates['content.post.more']);
    $tag = '<!--more-->';
    if ($i =strpos($content, $tag)) {
      return str_replace($tag, $more, $content);
    } else {
      return $excerpt ? $content  : $more . $content;
    }
  }
  
  protected function getteaser() {
    $content = $this->filtered;
    $tag = '<!--more-->';
    if ($i =strpos($content, $tag)) {
      $content = substr($content, $i + strlen($tag));
      if (!strbegin($content, '<p>')) $content = '<p>' . $content;
      return $content;
    }
    return '';
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($page == 1) {
      $result .= $this->filtered;
      $result = $this->replacemore($result, false);
    } elseif ($s = $this->getpage($page - 1)) {
      $result .= $s;
    } elseif ($page <= $this->commentpages) {
      //$result .= '';
    } else {
      $lang = tlocal::instance();
      $result .= $lang->notfound;
    }
    
    if ($this->haspages) {
      $result .= $this->theme->getpages($this->url, $page, $this->countpages);
    }
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $posts = tposts::instance();
    $posts->beforecontent($this, $result);
    //$posts->addrevision();
    if ($this->revision < $posts->revision) $this->setrevision($posts->revision);
    $result .= $this->getcontentpage(litepublisher::$urlmap->page);
    if (litepublisher::$options->parsepost) {
      $result = $this->theme->parse($result);
    }
    $posts->aftercontent($this, $result);
    return $result;
  }
  
  public function setcontent($s) {
    if (!is_string($s)) $this->error('Error! Post content must be string');
    if ($s != $this->rawcontent) {
      $this->rawcontent = $s;
      $filter = tcontentfilter::instance();
      $filter->filterpost($this,$s);
    }
  }
  
  public function setrevision($value) {
    if ($value != $this->data['revision']) {
      $this->updatefiltered();
      $posts = tposts::instance();
      $this->data['revision'] = (int) $posts->revision;
      if ($this->id > 0) $this->save();
    }
  }
  
  public function updatefiltered() {
    $filter = tcontentfilter::instance();
    $filter->filterpost($this,$this->rawcontent);
  }
  
  public function getrawcontent() {
    if (dbversion && ($this->id > 0) && ($this->data['rawcontent'] === false)) {
      $this->data['rawcontent'] = $this->rawdb->getvalue($this->id, 'rawcontent');
    }
    return $this->data['rawcontent'];
  }
  
  protected function getrawdb() {
    litepublisher::$db->table = 'rawposts';
    return litepublisher::$db;
  }
  
  public function getpage($i) {
    if ($i == 0) return $this->filtered;
    if (dbversion && ($this->id > 0)) {
      if ($r = $this->getdb('pages')->getassoc("(id = $this->id) and (page = $i) limit 1")) {
        return $r['content'];
      }
    } elseif ( isset($This->data['pages'][$i]))  {
      return $this->data['pages'][$i];
    }
    return false;
  }
  
  public function addpage($s) {
    $this->data['pages'][] = $s;
  }
  
  public function deletepages() {
    $this->data['pages'] = array();
  }
  
  public function gethaspages() {
    return ($this->pagescount > 1) || ($this->commentpages > 1);
  }
  
  public function getpagescount() {
    if (dbversion && ($this->id > 0)) return $this->data['pagescount'];
    return isset($this->data['pages']) ? count($this->data['pages']) : 1;
  }
  
  public function getcountpages() {
    return max($this->pagescount, $this->commentpages);
  }
  
  public function getcommentpages() {
    if (!litepublisher::$options->commentpages || ($this->commentscount <= litepublisher::$options->commentsperpage)) return 1;
    return ceil($this->commentscount / litepublisher::$options->commentsperpage);
  }
  
  public function getlastcommenturl() {
    $c = $this->commentpages;
    return $c > 1 ? rtrim($this->url, '/') . "/page/$c/" : $this->url;
  }
  
  public function setcommentsenabled($value) {
    if ($value != $this->commentsenabled) {
      if (!dbversion) $this->data['commentscount'] =  $this->comments->count;
      $this->data['commentsenabled'] = $value;
    }
  }
  
  public function getcommentscount() {
    if (!$this->commentsenabled || dbversion)  return $this->data['commentscount'];
    return $this->comments->approvedcount;
  }
  
  public function clearcache() {
    litepublisher::$urlmap->setexpired($this->idurl);
  }
  
  public function getschemalink() {
    return 'post';
  }
  
  //author
  protected function getauthorname() {
    return $this->getusername($this->author, false);
  }
  
  protected function getauthorlink() {
    return $this->getusername($this->author, true);
  }
  
  protected function getusername($id, $link) {
    if ($id == 0) return '';
    if ($id == 1) {
      if (litepublisher::$classes->exists('tprofile')) {
        $profile = tprofile::instance();
        return $profile->nick;
      } else {
        return 'admin';
      }
    } else {
      $users = tusers::instance();
      try {
        $account = $users->getitem($id);
      } catch (Exception $e) {
        return '';
      }
      if (!$link || ($account['url'] == '')) return $account['name'];
      return sprintf('<a href="%s/users.htm%sid=%s">%s</a>',litepublisher::$site->url, litepublisher::$site->q, $id, $account['name']);
    }
  }
  
}//class

//posts.class.php
class tposts extends titems {
  public $itemcoclasses;
  public $archives;
  public $rawtable;
  public $childtable;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public static function unsub($obj) {
    $self = self::instance();
    $self->unsubscribeclassname(get_class($obj));
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'posts';
    $this->childtable = '';
    $this->rawtable = 'rawposts';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt', 'onselect');
    $this->data['archivescount'] = 0;
    $this->data['revision'] = 0;
    $this->data['syncmeta'] = false;
    if (!dbversion) $this->addmap('archives' , array());
    $this->addmap('itemcoclasses', array());
  }
  
  public function getitem($id) {
    if (dbversion) {
      if ($result = tpost::instance($id)) return $result;
      $this->error("Item $id not found in class ". get_class($this));
    } else {
      if (isset($this->items[$id])) return tpost::instance($id);
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function loaditems(array $items) {
    if (!dbversion || count($items) == 0) return;
    //exclude already loaded items
    if (isset(titem::$instances['post'])) {
      $items = array_diff($items, array_keys(titem::$instances['post']));
    }
    if (count($items) == 0) return;
    $list = implode(',', $items);
    return $this->select("$this->thistable.id in ($list)", '');
  }
  
  public function setassoc(array $items) {
    if (count($items) == 0) return array();
    $result = array();
    $t = new tposttransform();
    $fileitems = array();
    foreach ($items as $a) {
      $t->post = tpost::newpost($a['class']);
      $t->setassoc($a);
      $result[] = $t->post->id;
      $f = $t->post->files;
      if (count($f) > 0) $fileitems = array_merge($fileitems, array_diff($f, $fileitems));
    }
    unset($t);
    if ($this->syncmeta)  tmetapost::loaditems($result);
    if (count($fileitems) > 0) {
      $files = tfiles::instance();
      $files->preload($fileitems);
    }
    
    $this->onselect($result);
    return $result;
  }
  
  public function select($where, $limit) {
    $db = litepublisher::$db;
    if ($this->childtable) {
      $childtable = $db->prefix . $this->childtable;
      return $this->setassoc($db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url, $childtable.*
      from $db->posts, $db->urlmap, $childtable
      where $where and  $db->posts.id = $childtable.id and $db->urlmap.id  = $db->posts.idurl $limit")));
    }
    
    $items = $db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $where and  $db->urlmap.id  = $db->posts.idurl $limit"));
    
    if (count($items) == 0) return array();
    $subclasses = array();
    foreach ($items as &$item) {
      if (empty($item['class'])) $item['class'] = 'tpost';
      if ($item['class'] != 'tpost') {
        $subclasses[$item['class']][] = $item['id'];
      }
    }
    unset($item);
    
    foreach ($subclasses as $class => $list) {
      $childtable =  $db->prefix .
      call_user_func_array(array($class, 'getchildtable'), array());
      $list = implode(',', $list);
      $subitems = $db->res2items($db->query("select $childtable.*
      from $childtable where id in ($list)"));
      foreach ($subitems as $id => $subitem) {
        $items[$id] = array_merge($items[$id], $subitem);
      }
    }
    
    return $this->setassoc($items);
  }
  
  public function getcount() {
    if (dbversion) {
      return $this->db->getcount("status<> 'deleted'");
    } else {
      return count($this->items);
    }
  }
  
  public function getchildscount($where) {
    if ($this->childtable == '') return 0;
    $db = litepublisher::$db;
    $childtable = $db->prefix . $this->childtable;
    if ($res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $childtable
    where $db->posts.status <> 'deleted' and $childtable.id = $db->posts.id $where")) {
      if ($r = $db->fetchassoc($res)) return $r['count'];
    }
    return 0;
  }
  
  private function beforechange($post) {
    $post->title = tcontentfilter::escape($post->title);
    $post->modified = time();
    $post->data['revision'] = $this->revision;
    $this->data['class'] = get_class($this);
    if (($post->status == 'published') && ($post->posted > time())) {
      $post->status = 'future';
    } elseif (($post->status == 'future') && ($post->posted <= time())) {
      $post->status = 'published';
    }
  }
  
  public function add(tpost $post) {    if ($post->posted == 0) $post->posted = time();
    $this->beforechange($post);
    if (($post->icon == 0) && !litepublisher::$options->icondisabled) {
      $icons = ticons::instance();
      $post->icon = $icons->getid('post');
    }
    
    if ($post->idview == 1) {
      $views = tviews::instance();
      if (isset($views->defaults['post'])) $post->data['idview'] = $views->defaults['post'];
    }
    
    $post->pagescount = count($post->pages);
    $linkgen = tlinkgenerator::instance();
    $post->url = $linkgen->addurl($post, $post->schemalink);
    $urlmap = turlmap::instance();
    if (dbversion) {
      $id = $post->addtodb();
      $post->idurl = $urlmap->add($post->url, get_class($post), (int) $post->id);
      $post->db->setvalue($post->id, 'idurl', $post->idurl);
    } else {
      $post->id = ++$this->autoid;
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      if (!is_dir($dir)) mkdir($dir, 0777);
      chmod($dir, 0777);
      $post->idurl = $urlmap->Add($post->url, get_class($post), $post->id);
    }
    $this->lock();
    $this->updated($post);
    $this->cointerface('add', $post);
    if (!$this->dbversion) $post->save();
    $this->unlock();
    $this->added($post->id);
    $this->changed();
    $urlmap->clearcache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $this->beforechange($post);
    $linkgen = tlinkgenerator::instance();
    $linkgen->editurl($post, $post->schemalink);
    
    $this->lock();
    $this->updated($post);
    $this->cointerface('edit', $post);
    $post->save();
    $this->unlock();
    $this->edited($post->id);
    $this->changed();
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $urlmap = turlmap::instance();
    if ($this->dbversion) {
      $idurl = $this->db->getvalue($id, 'idurl');
      $this->db->setvalue($id, 'status', 'deleted');
      if ($this->childtable) {
        $db = $this->getdb($this->childtable);
        $db->delete("id = $id");
      }
    } else {
      if ($post = tpost::instance($id)) {
        $idurl = $post->idurl;
        $post->free();
      }
      titem::deletedir(litepublisher::$paths->data . 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
      unset($this->items[$id]);
      $urlmap->deleteitem($idurl);
    }
    
    $this->lock();
    $this->PublishFuture();
    $this->UpdateArchives();
    $this->cointerface('delete', $id);
    $this->unlock();
    $this->deleted($id);
    $this->changed();
    $urlmap->clearcache();
    return true;
  }
  
  
  public function updated(tpost $post) {
    if (!$this->dbversion) {
      $this->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $this->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $this->items[$post->id]['author'] = $post->author;
    }
    $this->PublishFuture();
    $this->UpdateArchives();
    $cron = tcron::instance();
    $cron->add('single', get_class($this), 'dosinglecron', $post->id);
  }
  
  public function UpdateArchives() {
    if ($this->dbversion) {
      $this->archivescount = $this->db->getcount("status = 'published' and posted <= '" . sqldate() . "'");
    } else {
      $this->archives = array();
      foreach ($this->items as $id => $item) {
        if ((!isset($item['status']) || ($item['status'] == 'published')) &&(time() >= $item['posted'])) {
          $this->archives[$id] = $item['posted'];
        }
      }
      arsort($this->archives,  SORT_NUMERIC);
      $this->archivescount = count($this->archives);
    }
  }
  
  public function dosinglecron($id) {
    $this->PublishFuture();
    ttheme::$vars['post'] = tpost::instance($id);
    $this->singlecron($id);
  }
  
  public function hourcron() {
    $this->PublishFuture();
  }
  
  private function publish($id) {
    $post = tpost::instance($id);
    $post->status = 'published';
    $this->edit($post);
  }
  
  public function PublishFuture() {
    if ($this->dbversion) {
      if ($list = $this->db->idselect(sprintf("status = 'future' and posted <= '%s' order by posted asc", sqldate()))) {
        foreach( $list as $id) $this->publish($id);
      }
    } else {
      foreach ($this->items as $id => $item) {
        if (isset($item['status']) && ($item['status'] == 'future') && ($item['posted'] <= time())) $this->publish($id);
      }
    }
  }
  
  public function getrecent($count) {
    if (dbversion) {
      return $this->db->idselect("status = 'published'order by posted desc limit $count");
    }  else {
      return array_slice(array_keys($this->archives), 0, $count);
    }
  }
  
  public function GetPublishedRange($page, $perpage) {
    $count = $this->archivescount;
    $from = ($page - 1) * $perpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      return $this->select("status = 'published'", " order by posted desc limit $from, $perpage");
    } else {
      $to = min($from + $perpage , $count);
      return array_slice(array_keys($this->archives), $from, $to - $from);
    }
  }
  
  public function stripdrafts(array $items) {
    if (count($items) == 0) return array();
    if (dbversion) {
      $list = implode(', ', $items);
      return $this->db->idselect("status = 'published' and id in ($list)");
    } else {
      return array_intersect($items, array_keys($this->archives));
    }
  }
  
  public function sortbyposted(array $items) {
    if (count($items) <= 1) return $items;
    if (dbversion) {
      $list = implode(', ', $items);
      return $this->db->idselect("status = 'published' and id in ($list) order by posted desc");
    }
    /* надо выбрать опубликованные посты из items, потом отсортировать */
    $result = array_intersect_key ($this->archives, array_combine($items, $items));
    arsort($result,  SORT_NUMERIC);
    return array_keys($result);
  }
  
  //coclasses
  private function cointerface($method, $arg) {
    foreach ($this->coinstances as $coinstance) {
      if ($coinstance instanceof  ipost) $coinstance->$method($arg);
    }
  }
  
  public function addrevision() {
    $this->data['revision']++;
    $this->save();
    litepublisher::$urlmap->clearcache();
  }
  
  //fix call reference
  public function beforecontent($post, &$result) {
    $this->callevent('beforecontent', array($post, &$result));
  }
  
  public function aftercontent($post, &$result) {
    $this->callevent('aftercontent', array($post, &$result));
  }
  
  public function beforeexcerpt($post, &$result) {
    $this->callevent('beforeexcerpt', array($post, &$result));
  }
  
  public function afterexcerpt($post, &$result) {
    $this->callevent('afterexcerpt', array($post, &$result));
  }
  
}//class


class tpostswidget extends twidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.posts';
    $this->template = 'posts';
    $this->adminclass = 'tadminmaxcount';
    $this->data['maxcount'] = 10;
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['recentposts'];
  }
  
  public function getcontent($id, $sidebar) {
    $posts = tposts::instance();
    $list = $posts->getrecent($this->maxcount);
    $theme = ttheme::instance();
    return $theme->getpostswidgetcontent($list, $sidebar, '');
  }
  
}//class

//post.transform.class.php
class tposttransform  {
  public $post;
  public static $arrayprops= array('categories', 'tags', 'files');
  public static $intprops= array('id', 'idurl', 'parent', 'author', 'revision', 'icon', 'commentscount', 'pingbackscount', 'pagescount', 'idview');
  public static $boolprops= array('commentsenabled', 'pingenabled');
  public static $props = array('id', 'idurl', 'parent', 'author', 'revision', 'class',
  //'created', 'modified',
  'posted',
  'title', 'title2', 'filtered', 'excerpt', 'rss', 'description', 'moretitle',
  'categories', 'tags', 'files',
  'password', 'idview', 'icon',
  'status', 'commentsenabled', 'pingenabled',
  'commentscount', 'pingbackscount', 'pagescount',
  );
  
  public static function instance(tpost $post) {
    $self = getinstance(__class__);
    $self->post = $post;
    return $self;
  }
  
  public static function add(tpost $post) {
    $self = self::instance($post);
    $values = array();
    foreach (self::$props as $name) {
      $values[$name] = $self->__get($name);
    }
    $db = litepublisher::$db;
    $db->table = 'posts';
    $id = $db->add($values);
    $post->rawdb->insert_a(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));
    
    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert_a(array('post' => $id, 'page' => $i,         'content' => $content));
    }
    
    return $id;
  }
  
  public function save() {
    $db = litepublisher::$db;
    $db->table = 'posts';
    $post = $this->post;
    $list = array();
    foreach (self::$props  As $name) {
      if ($name == 'id') continue;
      $list[] = "$name = " . $db->quote($this->__get($name));
    }
    
    $db->idupdate($post->id, implode(', ', $list));
    
    $raw = array(
    'id' => $post->id,
    'modified' => sqldate()
    );
    if (false !== $post->data['rawcontent']) $raw['rawcontent'] = $post->data['rawcontent'];
    $post->rawdb->updateassoc($raw);
    $db->table = 'pages';
    $db->iddelete($this->post->id);
    foreach ($post->data['pages'] as $i => $content) {
      $db->updateassoc(array('post' => $post->id, 'page' => $i, 'content' => $content));
    }
  }
  
  public function setassoc(array $a) {
    foreach ($a as $name => $value) {
      $this->__set($name, $value);
    }
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (in_array($name, self::$arrayprops))  return implode(',', $this->post->$name);
    if (in_array($name, self::$boolprops))  return $this->post->$name ? 1 : 0;
    return $this->post->$name;
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) return $this->$set($value);
    if (in_array($name, self::$arrayprops)) {
      $this->post->data[$name] = tdatabase::str2array($value);
    } elseif (in_array($name, self::$intprops)) {
      $this->post->$name = (int) $value;
    } elseif (in_array($name, self::$boolprops)) {
      $this->post->data[$name] = $value == '1';
    } else {
      $this->post->$name = $value;
    }
  }
  
  private function getposted() {
    return sqldate($this->post->posted);
  }
  
  private function setposted($value) {
    $this->post->posted = strtotime($value);
  }
  
  private function setrevision($value) {
    $this->post->data['revision'] = $value;
  }
  
}//class

//post.meta.class.php
class tmetapost extends titem {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, (int) $id);
  }
  
  public static function getinstancename() {
    return 'postmeta';
  }
  
  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'meta';
  }
  
  protected function create() {
    $this->table = 'postsmeta';
  }
  
  public function getdbversion() {
    return dbversion;
  }
  
  public function __set($name, $value) {
    if ($name == 'id') return $this->setid($value);
    $exists = isset($this->data[$name]);
    if ($exists && ($this->data[$name] == $value)) return true;
    $this->data[$name] = $value;
    if (dbversion) {
      $name = dbquote($name);
      $value = dbquote($value);
      if ($exists) {
        $this->db->update("value = $value", "id = $this->id and name = $name");
      } else {
        $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
      }
    } else {
      $this->save();
    }
  }
  
  //db
  public function load() {
    if ($this->dbversion)  {
      $this->LoadFromDB();
    } else {
      parent::load();
    }
    return true;
  }
  
  protected function LoadFromDB() {
    $res = $this->db->select("id = $this->id");
    while ($row = litepublisher::$db->fetchassoc($res)) {
      $this->data[$row['name']] = $row['value'];
    }
    return true;
  }
  
  protected function SaveToDB() {
    $db = $this->db;
    $db->delete("id = $this->id");
    foreach ($this->data as $name => $value) {
      if ($name == 'id') continue;
      $name = dbquote($name);
      $value = dbquote($value);
      $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
    }
  }
  
  public static function loaditems(array $items) {
    if (!dbversion || count($items) == 0) return;
    //exclude already loaded items
    if (isset(self::$instances['postmeta'])) {
      $items = array_diff($items, array_keys(self::$instances['postmeta']));
    } else {
      self::$instances['postmeta'] = array();
    }
    if (count($items) == 0) return;
    $instances = &self::$instances['postmeta'];
    $db = litepublisher::$db;
    $db->table = 'postsmeta';
    $res = $db->select(sprintf('id in (%s)', implode(',', $items)));
    while ($row = $db->fetchassoc($res)) {
      $id = (int) $row['id'];
      if (!isset($instances[$id])) {
        $instances[$id] = new self();
        $instances[$id]->data['id'] = $id;
      }
      $instances[$id]->data[$row['name']] = $row['value'];
    }
    return true;
    
  }
  
}//class

//tags.common.class.php
class tcommontags extends titems implements  itemplate {
  public $contents;
  public $itemsposts;
  public $PermalinkIndex;
  public $PostPropname;
  public $id;
  private $newtitle;
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->data['lite'] = false;
    $this->data['includechilds'] = false;
    $this->data['includeparents'] = false;
    $this->PermalinkIndex = 'category';
    $this->PostPropname = 'categories';
    $this->contents = new ttagcontent($this);
    if (!$this->dbversion)  $this->data['itemsposts'] = array();
    $this->itemsposts = new titemspostsowner ($this);
  }
  
  protected function getpost($id) {
    return tpost::instance($id);
  }
  
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where .= ' and ';
    $db = litepublisher::$db;
    $table = $this->thistable;
    $res = $db->query("select $table.*, $db->urlmap.url from $table, $db->urlmap
    where $where $table.idurl = $db->urlmap.id $limit");
    return $this->res2items($res);
  }
  
  public function load() {
    if (parent::load() && !$this->dbversion) {
      $this->itemsposts->items = &$this->data['itemsposts'];
    }
  }
  
  public function getsortedcontent($parent, $tml, $subtml, $sortname, $count, $showcount) {
    $sorted = $this->getsorted($parent, $sortname, $count);
    if (count($sorted) == 0) return '';
    $result = '';
    $iconenabled = ! litepublisher::$options->icondisabled;
    $theme = ttheme::instance();
    $args = targs::instance();
    $args->rel = $this->PermalinkIndex;
    $args->parent = $parent;
    foreach($sorted as $id) {
      $item = $this->getitem($id);
      $args->add($item);
      $args->icon = $iconenabled ? $this->geticonlink($id) : '';
      $subitems = '';
      if ($showcount) $subitems = sprintf(' (%d)', $item['itemscount']);
      if ($subtml != '') $subitems .= $this->getsortedcontent($id, $tml, $subtml, $sortname, $count, $showcount);
      $args->subitems = $subitems;
      $result .= $theme->parsearg($tml,$args);
    }
    if ($parent == 0) return $result;
    $args->parent = $parent;
    $args->item = $result;
    return $theme->parsearg($subtml, $args);
  }
  
  public function geticonlink($id) {
    $item = $this->getitem($id);
    if ($item['icon'] == 0)  return '';
    $files = tfiles::instance();
    if ($files->itemexists($item['icon'])) return $files->geticon($item['icon'], $item['title']);
    $this->setvalue($id, 'icon', 0);
    if (!$this->dbversion) $this->save();
    return '';
  }
  
  public function geticon() {
    $item = $this->getitem($this->id);
    return $item['icon'];
  }
  
  public function geturl($id) {
    $item = $this->getitem($id);
    return $item['url'];
  }
  
  public function postedited($idpost) {
    $post = $this->getpost((int) $idpost);
    $this->lock();
  $changed = $this->itemsposts->setitems($idpost, $post->{$this->PostPropname});
    $this->updatecount($changed);
    $this->unlock();
  }
  
  public function postdeleted($idpost) {
    $this->lock();
    $changed = $this->itemsposts->deletepost($idpost);
    $this->updatecount($changed);
    $this->unlock();
  }
  
  private function updatecount(array $items) {
    if (count($items) == 0) return;
    if ($this->dbversion) {
      $db = litepublisher::$db;
      // вначале один запрос к таблице постов, чтобы получить массив новых значений
      //следующие запросы обновляют значение в таблице тегов
      $items = implode(',', $items);
      $thistable = $this->thistable;
      $itemstable = $this->itemsposts->thistable;
      $poststable = $db->posts;
      $list = $db->res2assoc($db->query("select $itemstable.item as id, count($itemstable.item)as itemscount from $itemstable, $poststable
      where $itemstable.item in ($items)  and $itemstable.post = $poststable.id and $poststable.status = 'published'
      group by $itemstable.item"));
      
      $db->table = $this->table;
      foreach ($list as $item) {
        $db->setvalue($item['id'], 'itemscount', $item['itemscount']);
      }
    } else {
      $this->lock();
      foreach ($items as $id) {
        $this->items[$id]['itemscount'] = $this->itemsposts->getpostscount($id);
      }
      $this->unlock();
    }
  }
  
  public function add($parent, $title) {
    if (empty($title)) return false;
    if ($id  = $this->IndexOf('title', $title)) return $id;
    $parent = (int) $parent;
    if (($parent != 0) && !$this->itemexists($parent)) $parent = 0;
    
    $urlmap =turlmap::instance();
    $linkgen = tlinkgenerator::instance();
    $url = $linkgen->createurl($title, $this->PermalinkIndex, true);
    
    $views = tviews::instance();
    $idview = isset($views->defaults[$this->PermalinkIndex]) ? $views->defaults[$this->PermalinkIndex] : 1;
    
    if ($this->dbversion)  {
      $id = $this->db->add(array(
      'parent' => $parent,
      'title' => $title,
      'idview' => $idview
      ));
      $idurl =         $urlmap->add($url, get_class($this),  $id);
      $this->db->setvalue($id, 'idurl', $idurl);
    } else {
      $id = ++$this->autoid;
      $idurl =         $urlmap->add($url, get_class($this),  $id);
    }
    
    $this->lock();
    $this->items[$id] = array(
    'id' => $id,
    'parent' => $parent,
    'idurl' =>         $idurl,
    'url' =>$url,
    'title' => $title,
    'icon' => 0,
    'idview' => $idview,
    'itemscount' => 0
    );
    $this->unlock();
    
    $this->added($this->autoid);
    $urlmap->clearcache();
    return $id;
  }
  
  public function edit($id, $title, $url) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['url'] == $url)) return;
    $item['title'] = $title;
    if ($this->dbversion) {
      $this->db->updateassoc(array(
      'id' => $id,
      'title' => $title
      ));
    }
    
    $urlmap = turlmap::instance();
    $linkgen = tlinkgenerator::instance();
    $url = trim($url);
    // try rebuild url
    if ($url == '') {
      $url = $linkgen->createurl($title, $this->PermalinkIndex, false);
    }
    
    if ($item['url'] != $url) {
      if (($urlitem = $urlmap->finditem($url)) && ($urlitem['id'] != $item['idurl'])) {
        $url = $linkgen->MakeUnique($url);
      }
      $urlmap->setidurl($item['idurl'], $url);
      $urlmap->addredir($item['url'], $url);
      $item['url'] = $url;
    }
    
    $this->items[$id] = $item;
    $this->save();
    $urlmap->clearcache();
  }
  
  public function delete($id) {
    $item = $this->getitem($id);
    $urlmap = turlmap::instance();
    $urlmap->deleteitem($item['idurl']);
    
    $this->lock();
    $this->contents->delete($id);
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    parent::delete($id);
    $this->unlock();
    $this->itemsposts->updateposts($list, $this->PostPropname);
    $urlmap->clearcache();
  }
  
  public function createnames($list) {
    if (is_string($list)) $list = explode(',', trim($list));
    $result = array();
    $this->lock();
    foreach ($list as $title) {
      $title = tcontentfilter::escape($title);
      if ($title == '') continue;
      $result[] = $this->add(0, $title);
    }
    $this->unlock();
    return $result;
  }
  
  public function getnames(array $list) {
    $this->loaditems($list);
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $result[] = $this->items[$id]['title'];
    }
    return $result;
  }
  
  public function getlinks(array $list) {
    if (count($list) == 0) return array();
    $this->loaditems($list);
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $result[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', litepublisher::$site->url . $item['url'], $item['title']);
    }
    return $result;
  }
  
  public function getsorted($parent, $sortname, $count) {
    $count = (int) $count;
    if ($sortname == 'count') $sortname = 'itemscount';
    if (!in_array($sortname, array('title', 'itemscount', 'id'))) $sortname = 'title';
    
    if ($this->dbversion) {
      $limit  = $sortname == 'itemscount' ? "order by $this->thistable.itemscount desc" :"order by $this->thistable.$sortname asc";
      if ($count > 0) $limit .= " limit $count";
      return $this->select("$this->thistable.parent = $parent", $limit);
    }
    
    $list = array();
    foreach($this->items as $id => $item) {
      if ($parent != $item['parent']) continue;
      $list[$id] = $item[$sortname];
    }
    if (($sortname == 'itemscount')) {
      arsort($list);
    } else {
      asort($list);
    }
    
    if (($count > 0) && ($count < count($list))) {
      $list = array_slice($list, 0, $count, true);
    }
    
    return array_keys($list);
  }
  
  //Itemplate
  public function request($id) {
    $this->id = (int) $id;
    try {
      $item = $this->getitem((int) $id);
    } catch (Exception $e) {
      return 404;
    }
    
    $url = $item['url'];
    if(litepublisher::$urlmap->page != 1) $url = rtrim($url, '/') . '/page/'. litepublisher::$urlmap->page . '/';
    if (litepublisher::$urlmap->url != $url) litepublisher::$urlmap->redir301($url);
  }
  
  public function AfterTemplated(&$s) {
    $redir = "<?php
  \$url = '{$this->items[$this->id]['url']}';
    if(litepublisher::\$urlmap->page != 1) \$url = rtrim(\$url, '/') . \"/page/\$urlmap->page/\";
    if (litepublisher::\$urlmap->url != \$url) litepublisher::\$urlmap->redir301(\$url);
    ?>";
    $s = $redir.$s;
  }
  
  public function getname($id) {
    $item = $this->getitem($id);
    return $item['title'];
  }
  
  public function gettitle() {
    $item = $this->getitem($this->id);
    return $item['title'];
  }
  
  public function gethead() {
    return sprintf('<link rel="alternate" type="application/rss+xml" title="%s" href="$site.url/rss/%s/%d.xml" />',
    $this->gettitle(), $this->PostPropname, $this->id);
  }
  
  public function getkeywords() {
    $result = $this->contents->getvalue($this->id, 'keywords');
    if ($result == '') $result = $this->title;
    return $result;
  }
  
  public function getdescription() {
    $result = $this->contents->getvalue($this->id, 'description');
    if ($result == '') $result = $this->title;
    return $result;
  }
  
  public function getidview() {
    $item = $this->getitem($this->id);
    return $item['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->setvalue($this->id, 'idview', $id);
    }
  }
  
  public function getcont() {
    $result = '';
    $theme = ttheme::instance();
    if ($this->id == 0) {
      $items = $this->getsortedcontent(0, '<li><a href="$link" title="$title">$icon$title</a>$count</li>',
      '<ul>$item</ul>',
      'count', 0, 0, false);
      $result .= sprintf('<ul>%s</ul>', $items);
      return $result;
    }
    
    $result .= $this->contents->getcontent($this->id);
    if ($result != '') $result = $theme->simple($result);
    
    $perpage = $this->lite ? 1000 : litepublisher::$options->perpage;
    $posts = litepublisher::$classes->posts;
    if ($this->dbversion) {
      if ($this->includeparents || $this->includechilds) {
        $this->loadall();
        $all = array($this->id);
        if ($this->includeparents) $all = array_merge($all, $this->getparents($this->id));
        if ($this->includechilds) $all = array_merge($all, $this->getchilds($this->id));
        $tags = sprintf('in (%s)', implode(',', $all));
      } else {
        $tags = " = $this->id";
      }
      
      $from = (litepublisher::$urlmap->page - 1) * $perpage;
      $itemstable  = $this->itemsposts->thistable;
      $poststable = $posts->thistable;
      $items = $posts->select("$poststable.status = 'published' and $poststable.id in
      (select DISTINCT post from $itemstable  where $itemstable .item $tags)",
      "order by $poststable.posted desc limit $from, $perpage");
      
      $result .= $theme->getposts($items, $this->lite);
    } else {
      $items = $this->itemsposts->getposts($this->id);
      if ($this->dbversion && ($this->includeparents || $this->includechilds)) $this->loadall();
      if ($this->includeparents) {
        $parents = $this->getparents($this->id);
        foreach ($parents as $id) {
          $items = array_merge($items, array_diff($this->itemsposts->getposts($id), $items));
        }
      }
      
      if ($this->includechilds) {
        $childs = $this->getchilds($this->id);
        foreach ($childs as $id) {
          $items = array_merge($items, array_diff($this->itemsposts->getposts($id), $items));
        }
      }
      
      $items = $posts->stripdrafts($items);
      $items = $posts->sortbyposted($items);
      $list = array_slice($items, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
      $result .= $theme->getposts($list, $this->lite);
    }
    
    $item = $this->getitem($this->id);
    $result .=$theme->getpages($item['url'], litepublisher::$urlmap->page, ceil($item['itemscount'] / $perpage));
    return $result;
  }
  
  public function getparents($id) {$result = array();
    while ($id = (int) $this->items[$id]['parent']) $result[] = $id;
    return $result;
  }
  
  public function getchilds($parent) {
    $result = array();
    foreach ($this->items as $id => $item) {
      if ($parent == $item['parent']) {
        $result[] =$id;
        $result = array_merge($result, $this->getchilds($id));
      }
    }
    return $result;
  }
  
}//class

class ttagcontent extends tdata {
  private $owner;
  private $items;
  
  public function __construct(TCommonTags $owner) {
    parent::__construct();
    $this->owner = $owner;
    $this->items = array();
  }
  
  private function getfilename($id) {
    return litepublisher::$paths->data . $this->owner->basename . DIRECTORY_SEPARATOR . $id;
  }
  
  public function getitem($id) {
    if (isset($this->items[$id]))  return $this->items[$id];
    $item = array(
    'description' => '',
    'keywords' => '',
    'content' => '',
    'rawcontent' => ''
    );
    
    if ($this->owner->dbversion) {
      if ($r = $this->db->getitem($id)) $item = $r;
    } else {
      tfilestorage::loadvar($this->getfilename($id), $item);
    }
    $this->items[$id] = $item;
    return $item;
  }
  
  public function setitem($id, $item) {
    if (isset($this->items[$id]) && ($this->items[$id] == $item)) return;
    $this->items[$id] = $item;
    if ($this->owner->dbversion) {
      $item['id'] = $id;
      $this->db->insert($item);
    } else {
      tfilestorage::savevar($this->getfilename($id), $item);
    }
  }
  
  public function edit($id, $content, $description, $keywords) {
    $item = $this->getitem($id);
    $filter = tcontentfilter::instance();
    $item =array(
    'content' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => $description,
    'keywords' => $keywords
    );
    $this->setitem($id, $item);
  }
  
  public function delete($id) {
    if ($this->owner->dbversion) {
      $this->db->iddelete($id);
    } else {
      @unlink($this->getfilename($id));
    }
  }
  
  public function getvalue($id, $name) {
    $item = $this->getitem($id);
    return $item[$name];
  }
  
  public function setvalue($id, $name, $value) {
    $item = $this->getitem($id);
    $item[$name] = $value;
    $this->setitem($id, $item);
  }
  
  public function getcontent($id) {
    return $this->getvalue($id, 'content');
  }
  
  public function setcontent($id, $content) {
    $item = $this->getitem($id);
    $filter = tcontentfilter::instance();
    $item['rawcontent'] = $content;
    $item['content'] = $filter->filter($content);
    $item['description'] = tcontentfilter::getexcerpt($content, 80);
    $this->setitem($id, $item);
  }
  
  public function getdescription($id) {
    return $this->getvalue($id, 'description');
  }
  
  public function getkeywords($id) {
    return $this->getvalue($id, 'keywords');
  }
  
}//class

class tcommontagswidget extends twidget {
  
  protected function create() {
    parent::create();
    $this->adminclass = 'tadmintagswidget';
    $this->data['sortname'] = 'count';
    $this->data['showcount'] = true;
    $this->data['showsubitems'] = true;
    $this->data['maxcount'] =0;
  }
  
  public function getowner() {
    return false;
  }
  
  public function getcontent($id, $sidebar) {
    $theme = ttheme::instance();
    $items = $this->owner->getsortedcontent(0,
    $theme->getwidgetitem($this->template, $sidebar),
    $this->showsubitems ? $theme->getwidgettml($sidebar, $this->template, 'subitems') : '',
    $this->sortname, $this->maxcount, $this->showcount);
    return str_replace('$parent', 0,
    $theme->getwidgetcontent($items, $this->template, $sidebar));
  }
  
}//class

class tcategories extends tcommontags {
  //public  $defaultid;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'categories';
    $this->contents->table = 'catscontent';
    $this->itemsposts->table = $this->table . 'items';
    $this->basename = 'categories' ;
    $this->data['defaultid'] = 0;
  }
  
  public function setdefaultid($id) {
    if (($id != $this->defaultid) && $this->itemexists($id)) {
      $thisdata['defaultid'] = $id;
      $this->save();
    }
  }
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      tcategorieswidget::instance()->expire();
    }
  }
  
}//class

class tcategorieswidget extends tcommontagswidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.categories';
    $this->template = 'categories';
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['categories'];
  }
  
  public function getowner() {
    return tcategories::instance();
  }
  
}//class

class ttags extends tcommontags {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'tags';
    $this->basename = 'tags';
    $this->PermalinkIndex = 'tag';
    $this->PostPropname = 'tags';
    $this->contents->table = 'tagscontent';
    $this->itemsposts->table = $this->table . 'items';
  }
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      ttagswidget::instance()->expire();
    }
  }
  
}//class

class ttagswidget extends tcommontagswidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.tags';
    $this->template = 'tags';
    $this->sortname = 'title';
    $this->showcount = false;
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['tags'];
  }
  
  public function getowner() {
    return ttags::instance();
  }
  
}//class

//files.class.php
class tfiles extends titems {
  public $itemsposts;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'files';
    $this->table = 'files';
    $this->addevents('changed', 'edited', 'ongetfilelist');
    $this->itemsposts = tfileitems ::instance();
  }
  
  public function preload(array $items) {
    if (!dbversion) return false;
    $items = array_diff($items, array_keys($this->items));
    if (count($items) > 0) {
      $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))',
      implode(',', $items)), '');
    }
  }
  
  public function geturl($id) {
    $item = $this->getitem($id);
    return litepublisher::$site->files . '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    $item = $this->getitem($id);
    $icon = '';
    if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
      $icon = $this->geticon($item['icon']);
    }
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', litepublisher::$site->files. $item['filename'], $item['title'], $icon . $item['description']);
  }
  
  public function geticon($id) {
    return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
  }
  
  public function additem(array $item) {
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item['author'] = litepublisher::$options->user;
    $item['posted'] = sqldate();
    $item['md5'] = md5_file($realfile);
    $item['size'] = filesize($realfile);
    return $this->insert($item);
  }
  
  public function insert(array $item) {
    if (dbversion) {
      $id = $this->db->add($item);
    } else {
      $id = ++$this->autoid;
    }
    $this->items[$id] = $item;
    if (!$this->dbversion) $this->save();
    $this->changed();
    $this->added($id);
    return $id;
  }
  
  public function edit($id, $title, $description, $keywords) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) return false;
    
    $item['title'] = $title;
    $item['description'] = $description;
    $item['keywords'] = $keywords;
    $this->items[$id] = $item;
    if ($this->dbversion) {
      $this->db->updateassoc($item);
    } else {
      $this->save();
    }
    $this->changed();
    $this->edited($id);
    return true;
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    $this->itemsposts->updateposts($list, 'files');
    $item = $this->getitem($id);
    @unlink(litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    $this->lock();
    parent::delete($id);
    if ($item['preview'] > 0) $this->delete($item['preview']);
    $this->unlock();
    $this->changed();
    return true;
  }
  
  public function setcontent($id, $content) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    if (file_put_contents($realfile, $content)) {
      $item['md5'] = md5_file($realfile);
      $item['size'] = filesize($realfile);
      $this->items[$id] = $item;
      if ($this->dbversion) {
        $item['id'] = $id;
        $this->db->updateassoc($item);
      } else {
        $this->save();
      }
    }
  }
  
  public function exists($filename) {
    return $this->IndexOf('filename', $filename);
  }
  
  public function getfilelist(array $list, $excerpt) {
    if ($result = $this->ongetfilelist($list, $excerpt)) return $result;
    $theme = ttheme::instance();
    return $this->getlist($list, $excerpt ?
    $theme->gettag('content.excerpts.excerpt.filelist') :
    $theme->gettag('content.post.filelist'));
  }
  
  public function getlist(array $list,  $templates) {
    if (count($list) == 0) return '';
    $result = '';
    if ($this->dbversion) $this->preload($list);
    
    //sort by media type
    $items = array();
    $types = array(
    'file' => $templates->file,
    'files' => $templates->files,
    'preview' => $templates->preview
    );
    
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $type = $item['media'];
      if (isset($types[$type])) {
        $items[$type][] = $id;
      } elseif (isset($templates->$type)) {
        $items[$type][] = $id;
        $types[$type] = $templates->$type;
        $type .= 's';
        $types[$type] = $templates->$type;
      } else {
        $items['file'][] = $id;
      }
    }
    $theme = ttheme::instance();
    $args = targs::instance();
    $url = litepublisher::$site->files . '/files/';
    $preview = new tarray2prop();
    ttheme::$vars['preview'] = $preview;
    foreach ($items as $type => $subitems) {
      $sublist = '';
      foreach ($subitems as $id) {
        $item = $this->items[$id];
        $args->preview  = '';
        $args->add($item);
        $args->link = $url . $item['filename'];
        $args->id = $id;
        if ($item['preview'] > 0) {
          $preview->array = $this->getitem($item['preview']);
          if ($preview->media === 'image') {
            $preview->id = $item['preview'];
            $preview->link = $url . $preview->filename;
            $args->preview = $theme->parsearg($types['preview'], $args);
          }
        } elseif($type == 'image') {
          $preview->array = $item;
          $preview->id = $id;
          $preview->link = $url . $preview->filename;
          $args->preview = $theme->parsearg($types['preview'], $args);
        }
        
        $sublist .= $theme->parsearg($types[$type], $args);
      }
      $sublist = str_replace('$' . $type, $sublist, $types[$type . 's']);
      $result .= $sublist;
    }
    
    unset(ttheme::$vars['preview'], $preview);
    return str_replace('$files', $result, $theme->parse((string) $templates));
  }
  
  public function postedited($idpost) {
    $post = tpost::instance($idpost);
    $this->itemsposts->setitems($idpost, $post->files);
  }
  
}//class

class tfileitems extends titemsposts {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'fileitems';
    $this->table = 'filesitemsposts';
  }
  
}

?>