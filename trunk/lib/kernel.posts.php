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
    $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt');
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
    foreach ($items as $a) {
      $t->post = tpost::newpost($a['class']);
      $t->setassoc($a);
      $result[] = $t->post->id;
    }
    unset($t);
    if ($this->syncmeta)  tmetapost::loaditems($result);
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

?>