<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocplugin extends tplugin {
private $fix;
  
  public static function instance() {
    return getinstance(__file__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'codedoc';
$this->fix = array();
}

public function beforefilter($post, $content) {
$content = trim($content);
if (!strbegin($content, '[document]')) return;
$filter = tcodedocfilter::instance();
$result = $filter->convert($post, $content);
if ($post->id == 0) {
$this->fix[] = $result;
} else {
$result['id'] = $post->id;
$this->db->updateassoc($result);
}
return true;
}

public function postadded($id) {
if (count($this->fix) == 0) return;
foreach ($this->fix as $i => $item) {
if ($id == $item['post']->id) {
$this->db->add(array(
'id' => $id,
'parent' => $item['parent'],
'class' => $item['class']
));

$filter = tcodedocfilter::instance();
$filtered = str_replace('__childs__', $filter->getchilds($post->id), $post->filtered);
$post->db->setvalue($post->id, 'filtered', $filtered);
unset($this->fix[$i]);

$posts = tposts::instance();
$posts->addrevision();

return;
}
}
}

public function postdeleted($id) {
$this->db->iddelete($id);
}

}//class
?>