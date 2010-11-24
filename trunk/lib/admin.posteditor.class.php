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
    <script type="text/javascript" src="%1$s/js/litepublisher/filebrowser.js"></script>
    <script type="text/javascript" src="%1$s/files/admin%2$s.js"></script>
    ', litepublisher::$site->files, litepublisher::$options->language);
    $ajax = tajaxposteditor ::instance();
    return $ajax->dogethead($result);
  }
  
  private function getsubcategories($parent, array $postitems) {
    $result = '';
    $categories = tcategories::instance();
    $html = $this->gethtml('editor');
    $args = targs::instance();
    foreach ($categories->items  as $id => $item) {
      if ($parent != $item['parent']) continue;
      $args->add($item);
      $args->checked = in_array($item['id'], $postitems);
      $args->subitems = $this->getsubcategories($id, $postitems);
      $result .= $html->category($args);
    }
    
    if ($result != '') $result = sprintf($html->categories(), $result);
    if ($parent == 0) $result = $html->categorieshead($args) . $result;
    return $result;
  }
  
  protected function getcategories(tpost $post) {
    $categories = tcategories::instance();
    $categories->loadall();
    $postitems = $post->categories;
    if (count($postitems) == 0) $postitems = array($categories->defaultid);
    $result = $this->getsubcategories(0, $postitems);
    return str_replace("'", '"', $result);
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
  
  public function getexternal() {
    $this->basename = 'editor';
    $this->idpost = 0;
    return $this->getcontent();
  }
  
  public function getcontent() {
    $html = $this->html;
    $post = tpost::instance($this->idpost);
    ttheme::$vars['post'] = $post;
    $args = targs::instance();
    $args->id = $post->id;
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    $args->title = $post->title;
    $args->categories = $this->getcategories($post);
    $ajaxeditor = tajaxposteditor ::instance();
    $args->editor = $ajaxeditor->geteditor('raw', $post->rawcontent);
    
    $result = $post->id == 0 ? '' : $html->h2->formhead . $post->bookmark;
    $result .= $html->form($args);
    return $html->fixquote($result);
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
/*
    echo "<pre>\n";
    var_dump($_POST);
    echo "</pre>\n";
    return;
*/
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
      $post->keywords = $keywords;
      $post->description = $description;
    }
    
    $post->content = $raw;
    if (isset($excerpt)) $post->excerpt = $excerpt;
    if (isset($rss)) $post->rss = $rss;
    if (isset($more)) $post->moretitle = $more;
    if (isset($filtered)) $post->filtered = $content;
    if (isset($upd)) {
      $update = sprintf($this->lang->updateformat, tlocal::date(time()), $upd);
      $post->content = $post->rawcontent . "\n\n" . $update;
    }
    
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