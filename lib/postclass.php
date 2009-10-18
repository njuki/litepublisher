<?php

class tpost extends TItem implements  ITemplate {
  private $fComments;
  private $dateformater;
  
  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
  }
  
  public static function instance($id = 0) {
    global $classes;
    $class = !empty($classes->classes['post']) ? $classes->classes['post'] : __class__;
    return parent::instance($class, $id);
  }
  
  protected function create() {
    global $options;
$this->table = 'posts';
    $this->data= array(
    'id' => 0,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0, //reserved, not used
    'date' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => '',
    'description' => '',
    'moretitle' => '',
    'categories' => array(0),
    'tags' => array(),
    'status' => 'published',
    'commentsenabled' => $options->commentsenabled,
    'pingenabled' => $options->pingenabled,
    'rssenabled' => true,
    'password' => '',
    'template' => '',
    'theme' => '',
    'pages' => array()
    );
  }
  
  public function getcomments() {
    if (!isset($this->fComments) ) {
      $this->fComments = TComments::instance($this->id);
    }
    return $this->fComments;
  }
  
  public function getprev() {
if (dbversion) {
if ($id = $this->db->findid("status = 'published' and created < '$this->created' order by created desc");
return self::instance($id);
}
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
if ($id = $this->db->findid("status = 'published' and created > '$this->created' order by created asc");
return self::instance($id);
}
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
  
  public function getrsslink() {
    global $options;
    return "$options->url/comments/$this->id/";
  }
  
  public function Getpubdate() {
    return date('r', $this->date);
  }
  
  public function Setpubdate($date) {
    $this->data['date'] = strtotime($date);
  }
  
  //template
  public function Getcategorieslinks($divider = ', ') {
    $categories = tcategories::instance();
    $items= array();
    foreach ($this->data['categories'] as  $id) {
      $items[] = $categories->getlink($id);
    }
    return implode($divider, $items);
  }
  
  public function Gettagslinks($divider = ', ') {
    $tags= ttags::instance();
    $items= array();
    foreach ($this->data['tags'] as $id) {
      $items[] = $tags->getlink($id);
    }
    return implode($divider, $items);
  }
  
  public function getlocaldate() {
    return TLocal::date($this->date);
  }
  
  public function getdateformat() {
    if (!isset($this->dateformater)) $this->dateformater = new tdate($this->date);
    $this->dateformater->date = $this->date;
    return $this->dateformater;
  }
  
  public function getmorelink() {
    global $options;
    if ($this->moretitle == '') return '';
    return  "<a href=\"$options->url$this->url#more-$this->id\" class=\"more-link\">$this->moretitle</a>";
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
    if (count($this->categories ) == 0) $this->dat['categories '][] = $categories->defaultid;
  }
  
  //ITemplate
  public function request($id) {
    parent::request($id);
    if ($this->status != 'published') return 404;
  }
  
  public function gettitle() {
    return $this->data['title'];
  }
  
  public function gethead() {
    $result = '';
    if ($prev = $this->prev) $result .= "<link rel=\"prev\" title=\"$prev->title\" href=\"$prev->link\" />\n";
    if ($next = $this->next) $result .= "<link rel=\"next\" title=\"$next->title\" href=\"$next->link\" />\n";
    if ($this->commentsenabled && ($this->commentscount > 0))  {
      $lang = tlocal::instance('comment');
      $result .= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"$lang->onpost $this->title\" href=\"$this->rsslink\" />\n";
    }
    return $result;
  }
  
  public function getkeywords() {
    return $this->Gettagnames();
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function GetTemplateContent() {
    $template = ttemplate::instance();
    $GLOBALS['post'] = $this;
    $tml = 'post.tml';
    if ($this->theme <> '') {
      if (@file_exists($template->path . $this->theme)) $tml = $this->theme;
    }
    return $template->ParseFile($tml);
  }
  
  public function getcontent() {
    $template = ttemplatePost::instance();
    $result = $template->BeforePostContent($this->id);
    $urlmap = turlmap::instance();
    if (($urlmap->pagenumber == 1) && !(isset($this->data['pages']) && (count($this->data['pages']) > 0))) {
      $result .= $this->filtered;
    } elseif ($s = $this->getpage($urlmap->pagenumber - 1)) {
      $result .= $s;
    } elseif ($urlmap->pagenumber <= $this->commentpages) {
      //$result .= '';
    } else {
      $lang = tlocal::instance();
      $result .= $lang->notfound;
    }
    $result .= $template->AfterPostContent($this->id);
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
      $this->data['rawcontent'] = $this->rawdb->idvalue($this->id, 'rawcontent');
    }
    return $this->data['rawcontent'];
  }
  
  public function setrawcontent($s) {
    $this->data['rawcontent'] = $s;
    if (dbversion && ($this->id > 0)) {
      $this->rawdb->idupdate($this->id, 'rawcontent = '. $this->db->quote($s));
    }
  }

protected function getrawdb() {
global $db;
$db->table = 'postsraw';
return $db;
}
  
  public function getpage($i) {
    if (dbversion && ($this->id > 0)) {
     if ($r = $this->getdb('pages')->getassoc("(post = $this->id) and (page = $i) limit 1")) {
        return $r['content'];
      }
    } elseif ( isset($This->data['pages'][$i]))  {
      return $this->data['pages'][$i];
    }
    return false;
  }
  
  public function addpage($s) {
    $this->data['pages'][] = $s;
    if (dbversion && ($this->id != 0)) {
      global $db;
      $db->table = 'pages';
      $count = $db->getcount("post = $this->id");
      $db->InsertAssoc(array(
      'post' => $this->id,
      'page' => $count,
      'content' => $s
      ));
    }
  }
  
  public function deletepages() {
    $this->data['pages'] = array();
    if (dbversion) {
      $this->getdb('pages')->delete("post = $this->id");
    }
  }
  
  public function gethaspages() {
    return (isset($this->data['pages']) && (count($this->data['pages']) > 0)) || ($this->commentpages > 1);
  }
  
  public function getpagescount() {
    return max($this->commentpages,  isset($this->data['pages']) ? count($this->data['pages']) : 0);
  }
  
  public function getcommentpages() {
    global $options;
    if (!$options->commentpages) return 1;
    return ceil($this->comments->count / $options->commentsperpage);
  }
  
  public function getcommentscount() {
    if (dbversion) {
      return $this->data['commentscount'];
    } else {
      return $this->comments->count;
    }
  }
  
  //db
  public function LoadFromDB() {
global $db;
    if ($res = $db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
where $db->posts.id = $this->id and  $db->urlmap.id  = $db->posts.idurl limit 1")) {
      $res->fetch(PDO::FETCH_INTO , TPostTransform::instance($this));
return true;
    }
return false;
  }
  
 protected function SaveToDB() {
TPostTransform ::instance($this)->save();
}

}//class

?>