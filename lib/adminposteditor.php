<?php

class TPostEditor extends TAdminPage {
  public $postid;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'posteditor';
  }
  
  public function Getcategories() {
    global $Options;
    $post = &TPost::Instance($this->postid);
    $categories = &TCategories::Instance();
    if (count($post->categories) == 0) $post->categories = array($categories->defaultid);
    $result = "<p>\n<a href=\"$Options->url/admin/categories\">" . TLocal::$data['default']['categories'] . "</a>:\n";
    
    foreach ($categories->items as $id => $item) {
      $checked = in_array($id, $post->categories) ? "checked='checked'" : '';
      $result .= "<input type='checkbox' name='category-$id' id='category-$id' $checked />
  <label for='category-$id'><a href='$Options->url{$item['url']}'>{$item['name']}</a></label>\n";
    }
    $result .= "</p>\n";
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function Getcontent() {
    global $Options;
    $result = '';
    $html = THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = TLocal::Instance();
    
    $this->postid = isset($_GET['postid']) ? (int) $_GET['postid'] : (isset($_POST['postid']) ? (int) $_POST['postid'] : 0);
    $post = TPost::Instance($this->postid);
    if ($post->id != 0) {
  $result .= $html->formhead("<a href='$Options->url$post->url'>$post->title</a>", "$Options->url/admin/posteditor/{$Options->q}postid=$post->id", "$Options->url/admin/posteditor/full/{$Options->q}postid=$post->id");
    }
    $raw = $this->ContentToForm($post->rawcontent);
    $commentsenabled = $post->commentsenabled ? 'checked' : '';
    $pingenabled = $post->pingenabled ? 'checked' : '';
    $published = $post->status != 'draft' ? 'selected' : '';
    $draft = $post->status == 'draft' ? 'selected' : '';
    if ($this->arg == null) {
      eval('$result .= "' . $html->form . '\n";');
    } else {
      $date = $post->date != 0 ?date('d-m-Y', $post->date) : '';
      $time  = $post->date != 0 ?date('H:i', $post->date) : '';
      $content = $this->ContentToForm($post->filtered);
      $excerpt = $this->ContentToForm($post->excerpt);
      $rss = $this->ContentToForm($post->rss);
      eval('$result .= "' . $html->fullform . '\n";');
    }
    return $result;
  }
  
  public function ProcessForm() {
    global $Options;
    $html = THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = TLocal::Instance();
    
    $cats = array();
    $cat = 'category-';
    foreach ($_POST as $key => $value) {
      if ($cat == substr($key, 0, strlen($cat))) {
        $id = (int) substr($key, strlen($cat));
        $cats[] = $id;
      }
    }
    
    extract($_POST);
    if (empty($title)){
      eval('$result = "'. $html->emptytitle . '\n";');
      return $result;
    }
    
    $post = TPost::Instance($postid);
    $post->title = $title;
    $post->categories = $cats;
    $post->tagnames = $tags;
    $post->commentsenabled = isset($commentsenabled);
    $post->pingenabled = isset($pingenabled);
    $post->status = $status == 'draft' ? 'draft' : 'published';
    
    if ($this->arg == null) {
      $post->content = $raw;
    } else {
      $post->url = $url;
      $post->description = $description;
      $post->rawcontent = $raw;
      $post->filtered = $content;
      $post->excerpt = $excerpt;
      $post->rss = $rss;
$post->moretitle = $moretitle;
      if (($date != '')  && @sscanf($date, '%d-%d-%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
        $post->date = mktime($h,$min,0, $m, $d, $y);
      }
    }
    
    $posts = TPosts::Instance();
    if ($postid == 0) {
      $posts->Add($post);
    } else {
      $posts->Edit($post);
    }
    
    eval('$s = "'. $html->success  . '\n";');
    return sprintf($s,"<a href=\"$Options->url$post->url\">$post->title</a>");
  }
  
}//class
?>