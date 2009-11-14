<?php

class treplyquoteplugin extends TPlugin {
 
 public static function instance() {
  return getinstance(__class__);
 }

private function getscript() {
$result = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'replyquotecomments.js');
$theme = ttheme::instance();
$parser = tthemeparser::instance();
$id = $parser->getidtag('area', $theme->comments['form']);

return $sprintf(result, $id);
}

 
 public function themechanged() {
$template = ttemplate::instance();
$template->editjavascript('replyquotecomments', $this->getjavascript());
}

public function install() {
$template = ttemplate();
$template->lock();
$template->themechanged = $this->themechanged;
$template->addjavascript('replyquotecomments', $this->getjavascript());
$template->unlock();
}

public function uninstall() {
$template = ttemplate();
$template->lock();
$template->unsubscribeclass($this);
$template->deletejavascript('replyquotecomments');
$template->unlock();

}

}//class
?>