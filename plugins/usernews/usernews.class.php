<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusernews extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }

public function create() {
parent::create();
$this->data['_changeposts'] = false;
$this->data['_canupload'] = true;
$this->data['_candeletefile'] = true;
}

public function getnorights() {
$about = tplugins::getabout(tplugins::getname(__file__));
return sprintf('<h4>%s</h4>', $about['norights']);
}

public function changeposts($action) {
if (!$this->_changeposts) return $this->norights;
}

public function canupload() {
if (!$this->_canupload) return $this->norights;
}

public function candeletefile() {
if (!$this->_candeletefile) return $this->norights;
}

public function getposteditor($post, $args) {
$args->sourceurl = isset($post->meta->sourceurl) ? $post->meta->sourceurl : '';
$form = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'editor.htm');
$about = tplugins::getabout(tplugins::getname(__file__));
$args->data['$lang.sourceurl'] = $about['sourceurl'];
    $html = tadminhtml::instance();
    $result = $post->id == 0 ? '' : $html->h2->formhead . $post->bookmark;
    $result .= $html->parsearg($form, $args);
    unset(ttheme::$vars['post']);
    return $html->fixquote($result);
}  

public function editpost(tpost $post) {
echo "fu";
$post->meta->sourceurl = $_POST['sourceurl'];
var_dump($post->meta->sourceurl);
return;
if ($post->id == 0) {

}

$posts = tposts::instance();
$posts->edit($post);
return "ok";
}
}//class
