<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposteditor extends tadminmenu {
  public $idpost;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    
    $template = ttemplate::instance();
    $template->ltoptions[] = 'idpost: ' . $this->idget();
    $template->ltoptions[] = sprintf('lang: "%s"', litepublisher::$options->language );
    //$result .= $template->getready('$.initposteditor();');
    $result .= $template->getready('initposteditor();');
    $ajax = tajaxposteditor ::instance();
    return $ajax->dogethead($result);
  }
  
  private static function getsubcategories($parent, array $postitems) {
    $result = '';
    $categories = tcategories::instance();
    $html = tadminhtml::getinstance('editor');
    $args = targs::instance();
    foreach ($categories->items  as $id => $item) {
      if ($parent != $item['parent']) continue;
      $args->add($item);
      $args->checked = in_array($item['id'], $postitems);
      $args->subcount = '';
      $args->subitems = self::getsubcategories($id, $postitems);
      $result .= $html->category($args);
    }
    
    if ($result != '') $result = sprintf($html->categories(), $result);
    if ($parent == 0) $result = $html->categorieshead($args) . $result;
    return $result;
  }
  
  public static function getcategories(array $items) {
    $categories = tcategories::instance();
    $categories->loadall();
    $result = self::getsubcategories(0, $items);
    return str_replace("'", '"', $result);
  }
  
  protected function getpostcategories(tpost $post) {
    $postitems = $post->categories;
    $categories = tcategories::instance();
    if (count($postitems) == 0) $postitems = array($categories->defaultid);
    return self::getcategories($postitems);
  }
  
  public function request($id) {
    if ($s = parent::request($id)) return $s;
    $this->basename = 'editor';
    $this->idpost = $this->idget();
    if ($this->idpost > 0) {
      $posts = tposts::instance();
      if (!$posts->itemexists($this->idpost)) return 404;
    }
    $post = tpost::instance($this->idpost);
    $groupname = litepublisher::$options->group;
    if ($groupname != 'admin') {
      $groups = tusergroups::instance();
      if (!$groups->hasright($groupname, 'editor') and  $groups->hasright($groupname, 'author')) {
        if (($post->id != 0) && (litepublisher::$options->user != $post->author)) return 403;
      }
    }
    
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::$data[$this->name]['editor'];
    }
  }
  
  public function getexternal() {
    $this->basename = 'editor';
    $this->idpost = 0;
    return $this->getcontent();
  }
  
  public function getpostargs(tpost $post, targs $args) {
    $args->id = $post->id;
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    $args->title = $post->title;
    $args->categories = $this->getpostcategories($post);
    $ajaxeditor = tajaxposteditor ::instance();
    $args->editor = $ajaxeditor->getraweditor($post->rawcontent);
  }
  
  public function getcontent() {
    $html = $this->html;
    $post = tpost::instance($this->idpost);
    ttheme::$vars['post'] = $post;
    $args = targs::instance();
    $this->getpostargs($post, $args);
    $result = $post->id == 0 ? '' : $html->h2->formhead . $post->bookmark;
    $result .= $html->form($args);
    unset(ttheme::$vars['post']);
    return $html->fixquote($result);
  }
  
  public static function processcategories() {
    return tadminhtml::check2array('category-');
  }
  
  protected function set_post(tpost $post) {
    extract($_POST, EXTR_SKIP);
    $post->title = $title;
    $post->categories = self::processcategories();
    if (isset($tags)) $post->tagnames = $tags;
    if (isset($icon)) $post->icon = (int) $icon;
    if (isset($idview)) $post->idview = $idview;
    if (isset($files))  {
      $files = trim($files);
      $post->files = $files == '' ? array() : explode(',', $files);
    }
    if (isset($date) && ($date != '')  && @sscanf($date, '%d.%d.%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
      $post->posted = mktime($h,$min,0, $m, $d, $y);
    }
    
    if (isset($status)) {
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->commentsenabled = isset($commentsenabled);
      $post->pingenabled = isset($pingenabled);
    }
    
    if (isset($url)) {
      $post->url = $url;
      $post->title2 = $title2;
      //$post->keywords = $keywords;
      $post->description = $description;
    }
    
    $post->content = $raw;
    if (isset($excerpt)) $post->excerpt = $excerpt;
    if (isset($rss)) $post->rss = $rss;
    if (isset($more)) $post->moretitle = $more;
    if (isset($filtered)) $post->filtered = $filtered;
    if (isset($upd)) {
      $update = sprintf($this->lang->updateformat, tlocal::date(time()), $upd);
      $post->content = $post->rawcontent . "\n\n" . $update;
    }
    
  }
  
  public function processform() {
    /*
    echo "<pre>\n";
    var_dump($_POST);
    echo "</pre>\n";
    return;
    */
    
    $this->basename = 'editor';
    $html = $this->html;
    if (empty($_POST['title'])) return $html->h2->emptytitle;
    $id = (int)$_POST['id'];
    $post = tpost::instance($id);
    
    $this->set_post($post);
    
    $posts = tposts::instance();
    if ($id == 0) {
      $_POST['id'] = $posts->add($post);
    } else {
      $posts->edit($post);
    }
    return sprintf($html->p->success,$post->bookmark);
  }
  
}//class
?>