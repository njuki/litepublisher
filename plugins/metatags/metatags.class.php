<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmetatags extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function themeparsed(ttheme $theme) {
$theme->templates['index'] = strtr($theme->templates['index'], array(
'$template.keywords' => '$metatags.keywords',
'$template.description' => '$metatags.description',
));
}

public function getlist() {
$context = ttemplate::i()->context;
if (isset($context) && isset($context->idposts)) {
$list = $context->idposts;
if (count($list) > 0) {
tposts::i()->loaditems($list);
return array_slice($list, 0, 3);
}
}
return false;
}

public function getkeywords() {
if ($list = $this->getlist()) {
$result = '';
foreach ($list as $id) {
$post = tpost::i($id);
$result .= $post->keywords . ', ';
}
return trim($result, ', ');
}
return ttemplate::i()->getkeywords();
}

public function getdescription() {
if ($list = $this->getlist()) {
$result = '';
foreach ($list as $id) {
$post = tpost::i($id);
$result .= $post->title . ' ';
}
return $result;
}
return ttemplate::i()->getdescription();
}

}//class