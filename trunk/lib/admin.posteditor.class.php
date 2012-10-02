<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposteditor extends tadminmenu {
  public $idpost;
  protected $isauthor;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    
    $template = ttemplate::i();
    $template->ltoptions['idpost'] = $this->idget();
    $template->ltoptions['lang'] = litepublisher::$options->language;
    
    $result .= '<link type="text/css" href="$site.files/js/litepublisher/css/fileman.css" rel="stylesheet" />';
    $result .= $template->getjavascript($template->jsmerger_posteditor);
    /*
    $result .= $template->getjavascript('/js/litepublisher/swfuploader.js');
    $result .= $template->getjavascript('/js/litepublisher/POSTEDITOR.js');
    $result .= $template->getjavascript('/js/litepublisher/fileman.js');
    $result .= $template->getjavascript('/js/litepublisher/fileman.templates.js');
    */
    
    if ($this->isauthor &&($h = tauthor_rights::i()->gethead()))  $result .= $h;
    return $result;
  }
  
  private static function getsubcategories($parent, array $postitems) {
    $result = '';
    $categories = tcategories::i();
    $html = tadminhtml::getinstance('editor');
    $args = targs::i();
    foreach ($categories->items  as $id => $item) {
      if ($parent != $item['parent']) continue;
      $args->add($item);
      $args->checked = in_array($item['id'], $postitems);
      $args->subcount = '';
      $args->subitems = self::getsubcategories($id, $postitems);
      $result .= $html->category($args);
    }
    
    if ($result == '') return '';
    return sprintf($html->categories(), $result);
  }
  
  public static function getcategories(array $items) {
    $categories = tcategories::i();
    $categories->loadall();
    $html = tadminhtml::i();
    $html->push_section('editor');
    $result = $html->categorieshead();
    $result .= self::getsubcategories(0, $items);
    $html->pop_section();
    return str_replace("'", '"', $result);
  }
  
  public static function getcombocategories(array $items, $idselected) {
    $result = '';
    $categories = tcategories::i();
    $categories->loadall();
    if (count($items) == 0) $items = array_keys($categories->items);
    foreach ($items as $id) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', tadminhtml::specchars($categories->getvalue($id, 'title')));
    }
    return $result;
  }
  
  protected function getpostcategories(tpost $post) {
    $postitems = $post->categories;
    $categories = tcategories::i();
    if (count($postitems) == 0) $postitems = array($categories->defaultid);
    return self::getcategories($postitems);
  }
  
  public function canrequest() {
    $this->isauthor = false;
    $this->basename = 'editor';
    $this->idpost = $this->idget();
    if ($this->idpost > 0) {
      $posts = tposts::i();
      if (!$posts->itemexists($this->idpost)) return 404;
    }
    $post = tpost::i($this->idpost);
    if (!litepublisher::$options->hasgroup('editor')) {
      if (litepublisher::$options->hasgroup('author')) {
        $this->isauthor = true;
        if (($post->id != 0) && (litepublisher::$options->user != $post->author)) return 403;
      }
    }
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      if (isset(tlocal::admin()->ini[$this->name]['editor'])) return tlocal::get($this->name, 'editor');
      return tlocal::get('editor', 'editor');
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
    $args->title = tcontentfilter::unescape($post->title);
    $args->categories = $this->getpostcategories($post);
    $args->date = $post->posted != 0 ?date('d.m.Y', $post->posted) : '';
    $args->time  = $post->posted != 0 ?date('H:i', $post->posted) : '';
    $args->url = $post->url;
    $args->title2 = $post->title2;
    $args->keywords = $post->keywords;
    $args->description = $post->description;
    $args->head = $post->rawhead;
    
    $args->raw = $post->rawcontent;
    $args->filtered = $post->filtered;
    $args->excerpt = $post->excerpt;
    $args->rss = $post->rss;
    $args->more = $post->moretitle;
    $args->upd = '';
  }
  
  public function getcontent() {
    $html = $this->html;
    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    $args = targs::i();
    $this->getpostargs($post, $args);
    
    $result = $post->id == 0 ? '' : $html->h4($this->lang->formhead . ' ' . $post->bookmark);
    if ($this->isauthor &&($r = tauthor_rights::i()->getposteditor($post, $args)))  return $r;
    
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
    if (($post->id == 0) && (litepublisher::$options->user >1)) $post->author = litepublisher::$options->user;
    if (isset($tags)) $post->tagnames = $tags;
    if (isset($icon)) $post->icon = (int) $icon;
    if (isset($idview)) $post->idview = $idview;
    if (isset($files))  {
      $files = trim($files);
var_dump($post->files );
dumpvar($files);
      $post->files = tdatabase::str2array($files);
var_dump($post->files );
    }
    if (isset($date) && ($date != '')  && @sscanf($date, '%d.%d.%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
      $post->posted = mktime($h,$min,0, $m, $d, $y);
    }
    
    if (isset($status)) {
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->comstatus = $comstatus;
      $post->pingenabled = isset($pingenabled);
      $post->idperm = (int) $idperm;
      if ($password != '') $post->password = $password;
    }
    
    if (isset($url)) {
      $post->url = $url;
      $post->title2 = $title2;
      $post->keywords = $keywords;
      $post->description = $description;
      $post->rawhead = $head;
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
    //dumpvar($_POST);
    $this->basename = 'editor';
    $html = $this->html;
    if (empty($_POST['title'])) return $html->h2->emptytitle;
    $id = (int)$_POST['id'];
    $post = tpost::i($id);
    
    if ($this->isauthor &&($r = tauthor_rights::i()->editpost($post)))  {
      $this->idpost = $post->id;
      return $r;
    }
    
    $this->set_post($post);
    $posts = tposts::i();
    if ($id == 0) {
      $this->idpost = $posts->add($post);
      $_POST['id'] = $this->idpost;
    } else {
      $posts->edit($post);
    }
    $_GET['id'] = $this->idpost;
    return sprintf($html->p->success,$post->bookmark);
  }
  
}//class
?>