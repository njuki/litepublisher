<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpost extends titem implements  itemplate {
  private $aprev;
  private $anext;
  private $ameta;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getinstancename() {
    return 'post';
  }
  
  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
  }
  
  protected function create() {
    $this->table = 'posts';
    $this->data= array(
    'id' => 0,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0,
    'revision' => 0,
    'icon' => 0,
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
  
  //db
  public function load() {
    $result = dbversion? $this->LoadFromDB() : parent::load();
    if ($result) {
      foreach ($this->coinstances as $coinstance) $coinstance->load();
    }
    return $result;
  }
  
  protected function LoadFromDB() {
    $db = litepublisher::$db;
    if ($a = $db->selectassoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $this->id and  $db->urlmap.id  = $db->posts.idurl limit 1")) {
      $trans = tposttransform::instance($this);
      $trans->setassoc($a);
      return true;
    }
    return false;
  }
  
  public function save() {
    parent::save();
    foreach ($this->coinstances as $coinstance) $coinstance->save();
  }
  
  protected function SaveToDB() {
    tposttransform ::instance($this)->save();
  }
  
  public function addtodb() {
    $this->setid(tposttransform ::add($this));
    return $this->id;
  }
  
  public function free() {
    foreach ($this->coinstances as $coinstance) $coinstance->free();
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
  
  public function getbookmark() {
    ttheme::$vars['post'] = $this;
    $theme = ttheme::instance();
    return $theme->parse('<a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.iconlink$post.title</a>');
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
    $theme = ttheme::instance();
    $tml = $excerpt ? $theme->content->excerpts : $theme->content->post;
$tml = $names == 'tags' ? $tml->taglinks : $tml->catlinks;
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
      $list[] = $theme->parsearg($tml->item,  $args);
    }
    
    return str_replace('$items', implode($tml->divider, $list), $theme->parse($tml));
  }
  
  public function getdate() {
    $theme = ttheme::instance();
    return tlocal::date($this->posted, $theme->content->post->date);
  }
  
  public function getexcerptdate() {
    $theme = ttheme::instance();
    return tlocal::date($this->posted, $theme->content->excerpts->excerpt->date);
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
    $theme = ttheme::instance();
    ttheme::$vars['post'] = $this;
    return $theme->parse($theme->content->excerpts->excerpt->morelink);
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
    if (count($this->categories) == 0)  return array();
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
    ttheme::$vars['post'] = $this;
    $theme = ttheme::instance();
    return $theme->parse($theme->content->post);
  }
  
  public function getrsslink() {
    if ($this->commentsenabled && ($this->commentscount > 0)) {
      $theme = ttheme::instance();
      ttheme::$vars['post'] = $this;
      return $theme->parse($theme->content->post->rsslink);
    }
    return '';
  }
  
  public function getprevnext() {
    $prev = '';
    $next = '';
    $theme = ttheme::instance();
    $tml = $theme->content->post->prevnext;
    if ($prevpost = $this->prev) {
      ttheme::$vars['prevpost'] = $prevpost;
      $prev = $theme->parse($tml->prev);
    }
    
    if ($nextpost = $this->next) {
      ttheme::$vars['nextpost'] = $nextpost;
      $next = $theme->parse($tml->next);
    }
    
    if (($prev == '') && ($next == '')) return '';
    return str_replace(
    array('$prev', '$next'),
    array($prev, $next),
    $theme->parse($tml));
  }
  
  public function getcommentslink() {
    if (($this->commentscount == 0) && !$this->commentsenabled) return '';
    $tc = ttemplatecomments::instance();
    return $tc->getcommentslink($this);
  }
  
  public function  gettemplatecomments() {
    if (($this->commentscount == 0) && !$this->commentsenabled && ($this->pingbackscount ==0)) return '';
    if ($this->haspages && ($this->commentpages < $urlmap->page)) return $this->getcommentslink();
    $tc = ttemplatecomments::instance();
    return $tc->getcomments($this->id);
  }
  
  public function getexcerptcontent() {
    $result = $this->data['excerpt'];
    $posts = tposts::instance();
    $posts->beforeexcerpt($this, $result);
    if ($this->revision < $posts->revision) $this->revision = $posts->revision;
    $result = $this->replacemore($result, true);
    if (litepublisher::$options->parsepost) {
      $theme = ttheme::instance();
      $result = $theme->parse($result);
    }
    $posts->afterexcerpt($this, $result);
    return $result;
  }
  
  public function replacemore($content, $excerpt) {
    $theme = ttheme::instance();
    ttheme::$vars['post'] = $this;
    $more = $theme->parse($excerpt ?
    $theme->content->excerpts->excerpt->morelink :
    $theme->content->post->more);
    $tag = '<!--more-->';
    if ($i =strpos($content, $tag)) {
      return str_replace($tag, $more, $content);
    } else {
      return $excerpt ? $content  : $more . $content;
    }
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
      $theme = ttheme::instance();
      $result .= $theme->getpages($this->url, $page, $this->countpages);
    }
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $posts = tposts::instance();
    $posts->beforecontent($this, $result);
    //$posts->addrevision();
    if ($this->revision < $posts->revision) $this->revision = $posts->revision;
    $result .= $this->getcontentpage(litepublisher::$urlmap->page);
    if (litepublisher::$options->parsepost) {
      $theme = ttheme::instance();
      $result = $theme->parse($result);
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
      $this->data['revision'] = $posts->revision;
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
      $profile = tprofile::instance();
      return $profile->nick;
    } else {
      $users = tusers::instance();
      $account = $users->getitem($id);
      if (!$link || ($account['url'] == '')) return $account['name'];
      return sprintf('<a href="%s/users.htm%sid=%s">%s</a>',litepublisher::$site->url, litepublisher::$site->q, $id, $account['name']);
    }
  }

}//class

?>