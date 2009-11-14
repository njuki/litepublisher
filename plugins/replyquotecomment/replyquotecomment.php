<?php

class treplyquotecomment extends TPlugin {

 public static function instance() {
  return getinstance(__class__);
 }

private function getbuttons() {
return '			<div class="replyquotebuttons">
<input type="button" value="$lang->reply" onclick="replycomment('$comment->id','$comment->name');" />
<input type="button" value="$lang->quote" onclick="quotecomment('$comment->id','$comment->name');" />
</div>';
}

private function getscript() {
$result = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'replyquotecomment.js');
$theme = ttheme::instance();
$parser = tthemeparser::instance();
$lang = tlocal::instance();
if ($idarea = $parser->getidtag('area', $theme->comments['form'])) {
//вставить в шаблон кнопки
$p = '<p id="commentcontent->$comment->id">';
$comment = &$theme->comments['comment'];
if ($i = strpos($comment, $p)) {
$comment = str_replace($p, $this->getbuttons() . $p, $comments);
$theme->save();
return sprintf($result, $idarea, $lang->says);
}
}
return false;
}

  public function themechanged() {
$template = ttemplate::instance();
if ($s = $this->getscript()) {
$template->editjavascript(__class__, $s);
} else {
$template->deletejavascript(__class__);
}
}

public function install() {
$template = ttemplate();
$template->lock();
$template->themechanged = $this->themechanged;
if ($s = $this->getscript()) {
$template->addjavascript(__clas__, $s);
}
$template->unlock();
}

public function uninstall() {
$template = ttemplate();
$template->lock();
$template->unsubscribeclass($this);
$template->deletejavascript(__class__);
$template->unlock();

$theme = ttheme::instance();
$p = '<p id="commentcontent->$comment->id">';
$comment = &$theme->comments['comment'];
if ($i = strpos($comment, $this->getbuttons())) {
$comment = str_replace($this->getbuttons() . $p, $p, $comments);
$theme->save();

}

}//class
?>