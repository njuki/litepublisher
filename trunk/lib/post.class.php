<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpost extends titem implements  itemplate {
  public $childdata;
  public $childtable;
  private $aprev;
  private $anext;
  private $_meta;
  private $_theme;
  private $_onid;
  
  public static function i($id = 0) {
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
  
  public static function selectitems(array $items) {
    return array();
  }
  
  public static function select_child_items($table, array $items) {
    if (($table == '') || (count($items) == 0)) return array();
    $db = litepublisher::$db;
    $childtable =  $db->prefix . $table;
    $list = implode(',', $items);
    return $db->res2items($db->query("select $childtable.*
    from $childtable where id in ($list)"));
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
    'keywords' => '',
    'description' => '',
    'head' => '',
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
    
    $posts = tposts::i();
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
    $trans = tposttransform::i($this);
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
    tposttransform ::i($this)->save();
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
  
  public function onid() {
    if (isset($this->_onid) && count($this->_onid) > 0) {
      foreach ($this->_onid as  $call) {
        try {
          call_user_func ($call, $this);
        } catch (Exception $e) {
          litepublisher::$options->handexception($e);
        }
      }
      unset($this->_onid);
    }
    
    if (isset($this->_meta)) {
      $this->_meta->id = $this->id;
      $this->_meta->save();
    }
  }
  
  public function setonid($call) {
    if (!is_callable($call)) $this->error('Event onid not callable');
    if (isset($this->_onid)) {
      $this->_onid[] = $call;
    } else {
      $this->_onid = array($call);
    }
  }
  
  public function free() {
    foreach ($this->coinstances as $coinstance) $coinstance->free();
    unset($this->aprev, $this->anext, $this->_meta, $this->_theme, $this->_onid);
    parent::free();
  }
  
  public function getcomments() {
    return tcomments::i($this->id);
  }
  
  public function getpingbacks() {
    return tpingbacks::i($this->id);
  }
  
  public function getprev() {
    if (!is_null($this->aprev)) return $this->aprev;
    $this->aprev = false;
    if (dbversion) {
      if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
        $this->aprev = self::i($id);
      }
    } else {
      $posts = tposts::i();
      $keys = array_keys($posts->archives);
      $i = array_search($this->id, $keys);
      if ($i < count($keys) -1) $this->aprev = self::i($keys[$i + 1]);
    }
    return $this->aprev;
  }
  
  public function getnext() {
    if (!is_null($this->anext)) return $this->anext;
    $this->anext = false;
    if (dbversion) {
      if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
        $this->anext = self::i($id);
      }
    } else {
      $posts = tposts::i();
      $keys = array_keys($posts->archives);
      $i = array_search($this->id, $keys);
      if ($i > 0 ) $this->anext = self::i($keys[$i - 1]);
      
    }
    return $this->anext;
  }
  
  public function getmeta() {
    if (!isset($this->_meta)) $this->_meta = tmetapost::i($this->id);
    return $this->_meta;
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
  
  public function parsetml($path) {
    $theme = $this->theme;
    return $theme->parse($theme->templates[$path]);
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
    $args = targs::i();
    $list = array();
    foreach ($this->$names as $id) {
      $item = $tags->getitem($id);
      $args->add($item);
      if (($item['icon'] == 0) || litepublisher::$options->icondisabled) {
        $args->icon = '';
      } else {
        $files = tfiles::i();
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
    return $this->parsetml('content.excerpts.excerpt.morelink');
  }
  
  public function gettagnames() {
    if (count($this->tags) == 0) return '';
    $tags = ttags::i();
    return implode(', ', $tags->getnames($this->tags));
  }
  
  public function settagnames($names) {
    $tags = ttags::i();
    $this->tags=  $tags->createnames($names);
  }
  
  public function getcatnames() {
    if (count($this->categories) == 0)  return '';
    $categories = tcategories::i();
    return implode(', ', $categories->getnames($this->categories));
  }
  
  public function setcatnames($names) {
    $categories = tcategories::i();
    $this->categories = $categories->createnames($names);
    if (count($this->categories ) == 0) {
      $defaultid = $categories->defaultid;
      if ($defaultid > 0) $this->data['categories '][] =  $dfaultid;
    }
  }
  
  public function getcategory() {
    if (count($this->categories) == 0) return '';
    $cats = tcategories::i();
    return $cats->getname($this->categories[0]);
  }
  
  //ITemplate
  public function request($id) {
    parent::request((int) $id);
    if ($this->status != 'published') {
    $groupname = litepublisher::$options->group;
    if (($groupname == 'admin') || ($groupname == 'editor')) return;
        if ($this->author == litepublisher::$options->user) return;
return 404;
}
  }
  
  public function gettitle() {
    //if ($this->data['title2'] != '') return $this->data['title2'];
    return $this->data['title'];
  }
  
  public function gethead() {
    // backward compatably with file version
    $result = isset($this->data['head']) ? $this->data['head'] : '';
    ttemplate::i()->ltoptions['idpost'] = $this->id;
    $theme = $this->theme;
    $result .= $theme->templates['head.post'];
    if ($prev = $this->prev) {
      ttheme::$vars['prev'] = $prev;
      $result .= $theme->templates['head.post.prev'];
    }
    
    if ($next = $this->next) {
      ttheme::$vars['next'] = $next;
      $result .= $theme->templates['head.post.next'];
    }
    
    if ($this->commentsenabled && ($this->commentscount > 0) ) {
      $lang = tlocal::i('comment');
      $result .= $theme->templates['head.post.rss'];
    }
    
    return $theme->parse($result);
  }
  
  public function getkeywords() {
    return empty($this->data['keywords']) ? $this->Gettagnames() : $this->data['keywords'];
  }
  //fix for file version. For db must be deleted
  public function setkeywords($s) {
    $this->data['keywords'] = $s;
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
    $files = tfiles::i();
    if ($files->itemexists($this->icon)) return $files->geturl($this->icon);
    $this->icon = 0;
    $this->save();
    return '';
  }
  
  public function geticonlink() {
    if (($this->icon == 0) || litepublisher::$options->icondisabled) return '';
    $files = tfiles::i();
    if ($files->itemexists($this->icon)) return $files->geticon($this->icon);
    $this->icon = 0;
    $this->save();
    return '';
  }
  
  public function setfiles(array $list) {
    $this->data['files'] = array_unique($list);
  }
  
  public function getfilelist() {
    if ((count($this->files) == 0) || ((litepublisher::$urlmap->page > 1) &&   litepublisher::$options->hidefilesonpage)) return '';
    $files = tfiles::i();
    return $files->getfilelist($this->files, false);
  }
  
  public function getexcerptfilelist() {
    if (count($this->files) == 0) return '';
    $files = tfiles::i();
    return $files->getfilelist($this->files, true);
  }
  
  public function getcont() {
    return $this->parsetml('content.post');
  }
  
  public function getrsslink() {
    if ($this->commentsenabled && ($this->commentscount > 0)) {
      return $this->parsetml('content.post.rsslink');
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
    $l = tlocal::i()->ini['comment'];
    switch($this->commentscount) {
      case 0: return $l[0];
      case 1: return $l[1];
      default: return sprintf($l[2], $this->commentscount);
    }
  }
  
  public function  gettemplatecomments() {
    $result = '';
    $page = litepublisher::$urlmap->page;
    $countpages = $this->countpages;
    if ($countpages > 1) $result .= $this->theme->getpages($this->url, $page, $countpages);
    
    if (($this->commentscount > 0) || $this->commentsenabled || ($this->pingbackscount > 0)) {
      if (($countpages > 1) && ($this->commentpages < $page)) {
        $result .= $this->getcommentslink();
      } else {
        $result .= ttemplatecomments::i()->getcomments($this->id);
      }
    }
    
    return $result;
  }
  
  public function get_excerpt() {
    return $this->data['excerpt'];
  }
  
  public function getexcerptcontent() {
    $posts = tposts::i();
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
    $more = $this->parsetml($excerpt ?
    'content.excerpts.excerpt.morelink' :
    'content.post.more');
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
    } else {
      $lang = tlocal::i();
      $result .= $lang->notfound;
    }
    
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $posts = tposts::i();
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
      $filter = tcontentfilter::i();
      $filter->filterpost($this,$s);
    }
  }
  
  public function setrevision($value) {
    if ($value != $this->data['revision']) {
      $this->updatefiltered();
      $posts = tposts::i();
      $this->data['revision'] = (int) $posts->revision;
      if ($this->id > 0) $this->save();
    }
  }
  
  public function updatefiltered() {
    $filter = tcontentfilter::i();
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
    if ($id <= 1) {
      if ($link) {
        return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', litepublisher::$site->url, litepublisher::$site->author);
      } else {
        return litepublisher::$site->author;
      }
    } else {
      $pages = tuserpages::i();
      try {
        $item = $pages->getitem($id);
      } catch (Exception $e) {
        return '';
      }
      if (!$link || ($item['website'] == '')) return $item['name'];
      return sprintf('<a href="%s/users.htm%sid=%s">%s</a>',litepublisher::$site->url, litepublisher::$site->q, $id, $item['name']);
    }
  }
  
  public function getauthorpage() {
    $id = $this->author;
    if ($id <= 1) {
      return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', litepublisher::$site->url, litepublisher::$site->author);
    } else {
      $pages = tuserpages::i();
      if (!$pages->itemexists($id)) return '';
      if ($item['url'] == '') return '';
      return sprintf('<a href="%s%s" title="%3$s" rel="author"><%3$s</a>', litepublisher::$site->url, $item['url'], $item['name']);
    }
  }
  
}//class