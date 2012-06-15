<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsonfiles extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function auth($idpost) {
    if (!litepublisher::$options->user) return false;
    if (litepublisher::$options->ingroup('editor')) return true;
if ($idpost == 0) return true;
      if ($idauthor = $this->getdb('posts')->getvalue($idpost, 'author')) {
return litepublisher::$options->user == (int) $idauthor;
}
return false;
  }
  
  public function forbidden() {
    $this->error('Forbidden', 403);
  }
  
  public function files_getpost(array $args) {
    $idpost = (int) $args['idpost'];
    if (!$this->auth($idpost)) return $this->forbidden();
$result = array();
$where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;
$files = tfiles::i();
$result['count'] = $files->db->getcount(" parent = 0 $where");
$result['files'] = array();

if ($idpost) {
$list = $files->itemsposts->getitems($idpost);
if (count($list)) {
$items = implode(',', $list);
$result['files'] = $files->db->res2items($files->db->query("id in ($items) and parent in ($items)"));
}
}

return $result;
}

  public function files_getpage(array $args) {
    if (!litepublisher::$options->hasgroup('author')) return $this->forbidden();
$page = (int) $args['page'];
$perpage = 20;
$from = $page * $perpage;
$where = litepublisher::$options->ingroup('editor') ? '' : ' where author = ' . litepublisher::$options->user;
$files = tfiles::i();
return array(
'files' => $files->db->res2items($files->db->query("select * from $files->thistable $where order by id limit $from, $perpage"))
);
}

  public function files_setprops(array $args) {
    if (!litepublisher::$options->hasgroup('author')) return $this->forbidden();
$id = (int) $args['idfile'];
$files = tfiles::i();
if (!$files->itemexists($id)) return $this->forbidden();
$item= $files->getitem($id);
$item['title'] = $args['title'];
$item['description'] = $args['description'];
$item['keywords'] = $args['keywords'];
$item = $files->escape($item);
$files->db->updateassoc($item);
return array(
'item' => $item
);
}

}//class