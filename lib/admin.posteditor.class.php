<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
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
    $result .= sprintf('
<script type="text/javascript" src="%1$s/js/litepublisher/rpc.min.js"></script>
<script type="text/javascript" src="%1$s/js/litepublisher/filebrowser.js"></script>
    <script type="text/javascript" src="%1$s/files/admin%2$s.js"></script>
    ', litepublisher::$site->files, litepublisher::$options->language);
    //<script type="text/javascript" src="%1$s/js/litepublisher/swfuploader.js"></script>
return $result;
  }
  
  protected function getcategories(tpost $post) {
    $result = '';
    $categories = tcategories::instance();
    if (count($post->categories) == 0) $post->categories = array($categories->defaultid);
    $html = $this->html;
    $args = targs::instance();
    $categories->loadall();
    foreach ($categories->items  as $id => $item) {
      $args->add($item);
      $args->checked = in_array($item['id'], $post->categories);
      $result .= $html->category($args);
    }
    $result = sprintf($html->categories(), $result);
    $result = str_replace("'", '"', $result);
    return $result;
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
    if ((litepublisher::$options->group == 'author') && (litepublisher::$options->user != $post->author)) return 403;
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::$data[$this->name]['editor'];
    }
  }
  
  private function getmode() {
    $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'midle';
    if (!preg_match('/short|midle|full|update/', $mode)) $mode = 'midle';
    return $mode;
  }
  
  public function shorteditor() {
    $_REQUEST['mode'] = 'short';
    $this->basename = 'editor';
    $this->idpost = 0;
    return $this->getcontent();
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $post = tpost::instance($this->idpost);
    ttheme::$vars['post'] = $post;
    $args = targs::instance();
$args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    if ($post->id != 0) $result .= $html->h2->formhead . $post->bookmark;
    $args->categories = $this->getcategories($post);
    $args->raw = $post->rawcontent;

      $args->content = $post->filtered;
      $args->excerpt = $post->data['excerpt'];
      $args->rss = $post->rss;
    
    $result .= $html->short($args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  protected function getcats() {
    $result = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'category-')) {
        $result[] = (int) $value;
      }
    }
    return $result;
  }
  
  public function processform() {
echo "<pre>\n";
var_dump($_POST);
echo "</pre>\n";
return;
    extract($_POST);

    $this->basename = 'editor';
    $html = $this->html;
    $post = tpost::instance((int)$id);
      if (empty($title)) return $html->h2->emptytitle;
      $post->title = $title;
      $post->categories = $this->getcats();
      if (isset($tags)) $post->tagnames = $tags;
      if (isset($icon)) $post->icon = (int) $icon;
      if (isset($idview)) $post->idview = $idview;
    if (isset($files))  $post->files = explode(',', $files);
    
      $post->content = $raw;

if (isset($date) && ($date != '')  && @sscanf($date, '%d.%d.%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
        $post->posted = mktime($h,$min,0, $m, $d, $y);
      }
      
      $post->content = $raw;

if (isset($status)) {
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->commentsenabled = isset($commentsenabled);
      $post->pingenabled = isset($pingenabled);
}

      $post->title2 = $title2;
      $post->url = $url;
      $post->description = $description;
      $post->rawcontent = $raw;
      $post->filtered = $content;
      $post->excerpt = $excerpt;
      $post->rss = $rss;
      $post->moretitle = $moretitle;
      $update = sprintf($this->lang->updateformat, tlocal::date(time()), $update);
      $post->content = $post->rawcontent . "\n\n" . $update;

    $posts = tposts::instance();
    if ($id == 0) {
      $_POST['id'] = $posts->add($post);
    } else {
      $posts->edit($post);
    }
    
    return sprintf($html->p->success,"<a href=\"$post->link\" title=\"$post->title\">$post->title</a>");
  }
  
}//class
?>