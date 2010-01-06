<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpost extends titem implements  itemplate {
  
  public static function instance($id = 0) {
    return parent::instance(__class__, $id);
  }
  
  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
  }
  
  protected function create() {
    global $options;
    $this->table = 'posts';
    $this->data= array(
    'id' => 0,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0,
    'icon' => 0,
    'posted' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'title2' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => '',
    'description' => '',
    'moretitle' => '',
    'categories' => array(),
    'tags' => array(),
    'files' => array(),
    'status' => 'published',
    'commentsenabled' => $options->commentsenabled,
    'pingenabled' => $options->pingenabled,
    'password' => '',
    'template' => '',
    'theme' => '',
    'commentscount' => 0,
    'pingbackscount' => 0,
    'pagescount' => 0,
    'pages' => array()
    );
    
    $posts = tposts::instance();
    foreach ($posts->itemcoclasses as $class) {
      $coinstance = getinstance($class);
      $coinstance->post = $this;
      $this->instances[]  = $coinstance;
    }
  }
  
  public function getdbversion() {
    return dbversion;
  }
  
  protected function SaveToDB() {
    TPostTransform ::instance($this)->save();
  }
  
  public function getcomments() {
    return tcomments::instance($this->id);
  }
  
  public function getpingbacks() {
    return tpingbacks::instance($this->id);
  }
  
  public function getprev() {
    if (dbversion) {
      if ($id = $this->db->findid(sprintf("status = 'published' and posted < '%s' order by posted desc", sqldate($this->posted)))) {
        return self::instance($id);
      }
      return null;
    } else {
      $posts = tposts::instance();
      $keys = array_keys($posts->archives);
      $i = array_search($this->id, $keys);
      if ($i < count($keys) -1) return self::instance($keys[$i + 1]);
    }
    return null;
  }
  
  public function getnext() {
    if (dbversion) {
      if ($id = $this->db->findid(sprintf("status = 'published' and posted > '%s' order by posted desc", sqldate($this->posted)))) {
        return self::instance($id);
      }
      return null;
    } else {
      $posts = tposts::instance();
      $keys = array_keys($posts->archives);
      $i = array_search($this->id, $keys);
      if ($i > 0 ) return self::instance($keys[$i - 1]);
    }
    return null;
  }
  
  public function Getlink() {
    global $options;
    return $options->url . $this->url;
  }
  
  public function Setlink($link) {
    global $options;
    if ($UrlArray = parse_url($link)) {
      $url = $UrlArray['path'];
      if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
      $this->url = $url;
    }
  }
  
  public function getrsscomments() {
    global $options;
    return "$options->url/comments/$this->id.xml";
  }
  
  public function Getpubdate() {
    return date('r', $this->posted);
  }
  
  public function Setpubdate($date) {
    $this->data['posted'] = strtotime($date);
  }
  
  //template
  public function getexcerptcategories() {
    return $this->getcommontagslinks('categories', 'category', true);
  }
  
  public function getexcerpttags() {
    return $this->getcommontagslinks('tags', 'tag', true);
  }
  
  public function getcategorieslinks() {
    return $this->getcommontagslinks('categories', 'category', false);
  }
  
  public function Gettagslinks() {
    return $this->getcommontagslinks('tags', 'tag', false);
  }
  
  private function getcommontagslinks($names, $name, $excerpt) {
    global $classes;
    $theme = ttheme::instance();
    $tml = $excerpt ? $theme->content->excerpts->$names : $theme->content->post->$names;
    $tags= $classes->$names;
    $tags->loaditems($this->$names);
    $args = targs::instance();
    $list = array();
    foreach ($this->$names as $id) {
      $item = $tags->getitem($id);
      $args->add($item);
      if ($item['icon'] == 0) {
        $args->icon = '';
      } else {
        $files = tfiles::instance();
        $args->icon = $files->geticon($item['icon']);
      }
      $list[] = $theme->parsearg($tml->item,  $args);
    }
    $result = implode($tml->divider, $list);
    return sprintf($theme->parse($tml), $result);
  }
  
  public function getdate() {
    $theme = ttheme::instance();
    return tlocal::date($this->posted, $theme->content->post->dateformat);
  }
  
  public function getdateformat() {
    if (isset($this->dateformater)){
      $this->dateformater->date = $this->posted;
    } else {
      $this->dateformater = new tdateformater($this->posted);
    }
    return $this->dateformater;
  }
  
  public function getmorelink() {
    global $post;
    if ($this->moretitle == '') return '';
    $post = $this;
    $theme = ttheme::instance();
    return $theme->parse($theme->content->excerpts->excerpt->more);
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
    return $categories->getnames($this->categories);
  }
  
  public function setcatnames($names) {
    $categories = tcategories::instance();
    $this->categories = $categories->createnames($names);
    if (count($this->categories ) == 0) {
      $defaultid = $categories->defaultid;
      if ($defaultid > 0) $this->data['categories '][] =  $dfaultid;
    }
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
    if ($prev = $this->prev) $result .= "<link rel=\"prev\" title=\"$prev->title\" href=\"$prev->link\" />\n";
    if ($next = $this->next) $result .= "<link rel=\"next\" title=\"$next->title\" href=\"$next->link\" />\n";
    if ($this->commentsenabled && ($this->commentscount > 0))  {
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
  
  public function geticonurl() {
    if ($this->icon == 0) return '';
    $files = tfiles::instance();
    return $files->geturl($this->icon);
  }
  
  public function geticonlink() {
    if ($this->icon == 0) return '';
    $files = tfiles::instance();
    return $files->geticon($this->icon);
  }
  
  public function getpreviews() {
    if (count($this->files) == 0) return '';
    $files = tfiles::instance();
    return $files->getpreviews($this->files);
  }
  
  public function getfilelist() {
    if (count($this->files) == 0) return '';
    $files = tfiles::instance();
    return $files->getlist($this->files, false);
  }
  
  public function GetTemplateContent() {
    global $post;
    $post = $this;
    $theme = ttheme::instance();
    return $theme->parse($theme->content->post);
  }
  
  public function getsubscriberss() {
    global $post;
    if ($this->commentsenabled && ($this->commentscount > 0)) {
      $post = $this;
      $theme = ttheme::instance();
      return $theme->parse($theme->content->post->rss);
    }
    return '';
  }
  
  public function getprevnext() {
    global $prevpost, $nextpost;
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->content->post->prevnext;
    if ($prevpost = $this->prev) {
      $result .= $theme->parse($tml->prev);
    }
    
    if ($nextpost = $this->next) {
      $result .= $theme->parse($tml->next);
    }
    
    if ($result != '') $result = sprintf($theme->parse($tml), $result);
    return $result;
  }
  
  public function getcommentslink() {
    $tc = ttemplatecomments::instance();
    return $tc->getcommentslink($this);
  }
  
  public function  gettemplatecomments() {
    if (($this->commentscount == 0) && !$this->commentsenabled && ($this->pingbackscount ==0)) return '';
    if ($this->haspages && ($this->commentpages < $urlmap->page)) return $this->getcommentslink();
    $tc = ttemplatecomments::instance();
    return $tc->getcomments($this->id);
  }
  
  private function replacemore($content) {
    global $post;
    $post = $this;
    $theme = ttheme::instance();
    $more = $theme->parse($theme->content->post->more);
    $tag = '<!--more-->';
    if ($i =strpos($content, $tag)) {
      return str_replace($tag, $more, $content);
    } else {
      return $more . $content;
    }
  }
  
  public function getcontent() {
    $result = '';
    $posts = tposts::instance();
    $posts->beforecontent($this->id, &$result);
    $urlmap = turlmap::instance();
    if ($urlmap->page == 1) {
      $result .= $this->filtered;
      $result = $this->replacemore($result);
    } elseif ($s = $this->getpage($urlmap->page - 1)) {
      $result .= $s;
    } elseif ($urlmap->page <= $this->commentpages) {
      //$result .= '';
    } else {
      $lang = tlocal::instance();
      $result .= $lang->notfound;
    }
    
    if ($this->haspages) {
      $theme = theme::instance();
      $result .= $theme->getpages($this->url, $urlmap->page, $this->countpages);
    }
    
    $posts->aftercontent($this->id, &$result);
    return $result;
  }
  
  public function setcontent($s) {
    if ($s <> $this->rawcontent) {
      $this->rawcontent = $s;
      $filter = TContentFilter::instance();
      $filter->SetPostContent($this,$s);
    }
  }
  
  public function getrawcontent() {
    if (dbversion && ($this->id > 0) && empty($this->data['rawcontent'])) {
      $this->data['rawcontent'] = $this->rawdb->getvalue($this->id, 'rawcontent');
    }
    return $this->data['rawcontent'];
  }
  
  protected function getrawdb() {
    global $db;
    $db->table = 'rawposts';
    return $db;
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
    global $options;
    if (!$options->commentpages) return 1;
    return ceil($this->commentscount / $options->commentsperpage);
  }
  
  public function getlastcommenturl() {
    $c = $this->commentpages;
    return $c > 1 ? rtrim($this->url, '/') . "/page/$c/" : $this->url;
  }
  
  public function setcommentsenabled($value) {
    if ($value != $this->commentsenabled) {
      if (!dbversion) $this->data['commentscount'] =  $this->comments->GetCountApproved;
      $this->data['commentsenabled'] = $value;
    }
  }
  
  public function getcommentscount() {
    if (!$this->commentsenabled || dbversion)  return $this->data['commentscount'];
    return $this->comments->approvedcount;
  }
  
  //db
  public function load() {
    if (dbversion)  return $this->LoadFromDB();
    return parent::load();
  }
  
  public function LoadFromDB() {
    global $db;
    if ($res = $db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $this->id and  $db->urlmap.id  = $db->posts.idurl limit 1")) {
      $res->setFetchMode (PDO::FETCH_INTO , tposttransform::instance($this));
      $res->fetch();
      return true;
    }
    return false;
  }
  
  public function clearcache() {
    $urlmap = turlmap::instance();
    $urlmap->setexpired($this->idurl);
  }
  
}//class

?>