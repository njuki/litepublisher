<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tsameposts extends tplugin {

 public static function instance() {
  return getinstance(__class__);
 }
 
 protected function create) {
  parent::create();
$this->data['tml'] = '';
if (dbversion) {
$this->table = 'sameposts';
} else {
$this->data['revision'] = 1;
}
 }
 
 public function postchanged() {
if (dbversion) {
$this->db->exec("truncate $this->thistable");
} else {
  $this->revision += 1;
  $this->save();
}
 }
 
 private function findsame($idpost) {
$posts = tposts::instance();
  $post = tpost::instance($idpost);
  $list = $post->categories;
  $cats = tcategories::instance();
$cats->loadall();
  $same = array();
  foreach ($list as $id) {
if (!isset($cats->items[$id])) continue;
$itemsposts = $cats->itemsposts->getposts($id);
$posts->stripdrafts($itemsposts);
   foreach ($itemsposts as $i) {
    if ($i == $idpost) continue;
    if (isset($same[$i])) {
     $same[$i]++;
    } else {
     $same[$i] = 1;
    }
   }
  }
  
  arsort($same);
  $result = array_keys($same);
  $result = array_slice($result, 0, 10);
return $result;
 }

private function getsame($id) {
global $paths;
if (dbversion) {
if ($items = $this->db->getvalue($id, 'items')) {
return explode(',', $items);
} else {
$result = $this->findsame($id);
$this->db->setvalue($id, 'items', implode(',', $result);
return $result;
}
} else {
$filename = $paths['data'] . 'posts' . DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR . 'same.php');
$data = null;
if (tfiler::unserialize($filename, $data)) {
if ($data['revision'] == $this->revision) return $data['items'];
}

$result= $this->findsame($id);
$data = array(
'revision' => $this->revision,
'items' => $result
);
tfiler::serialize($filename, $data);
return $result;
}
} 

 public function onsitebar(&$content, $index) {
global $template;
if ($index > 0) return;
$post = $template->context;
$list = $this->getsame($post->id);
if (count($list) == 0) return;
$theme = ttheme::instance();
$tml = $this->tml != '' ? $this->tml : $theme->getwidgetitem('posts', $index);
$links = $theme->getpostswidgetcontent($list, $tml);
$widget = $theme->getwidget(TLocal::$data['default']['sameposts']], $links, 'widget', $index);
$content = $widget . $content;
 }
 
}//class
?>