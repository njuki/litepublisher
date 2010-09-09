<?php
set_time_limit(120);
define('litepublisher_mode', 'xmlrpc');
include('index.php');
$man = tdbmanager::instance();

function cleartags($tags) {
$tags->lock();
$tags->loadall();
foreach ($tags->items as $id => $item) {
$tags->delete($id);
}
$tags->unlock();
}

$posts = tposts::instance();
$posts->lock();
if (dbversion) {
$items = $posts->select(litepublisher::$db->prefix . 'posts.id > 0', '');
foreach ($items as $id) {
$posts->delete($id);
}
$posts->deletedeleted();
} else {
foreach ($posts->items as $id => $item) {
$posts->delete($id);
}
}
$posts->unlock();
cleartags(tcategories::instance());
cleartags(ttags::instance());

class tmigratedata extends tdata {
public static $dir;

public function load($name) {
$this->data = array();
$filename = self::$dir . $name . '.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }

}

}//class

function migrateposts() {
global $data, $man;
$data->load('posts' . DIRECTORY_SEPARATOR . 'index');
$posts = tposts::instance();
if (dbversion) {
$man->setautoincrement('posts', $data->lastid);
} else {
$posts->autoid = $data->lastid;
}
$items = $data->data['items'];
foreach ($items as $id => $item) {
$post = migratepost($id);
savepost($post);
migratecomments($id);
if (!dbversion) {
      $posts->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $posts->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $posts->items[$post->id]['author'] = $post->author;
}
}
$posts->UpdateArchives();
$posts->save();

$arch = tarchives::instance();
$arch->postschanged();

//update trust values
if (dbversion) {
      $db = litepublisher::$db;
      $res = $db->query("SELECT author as 'author', count(author) as 'count' FROM  $db->comments 
where status = 'approved' GROUP BY  author");
$db->table = 'comusers';
      while ($r = $db->fetchassoc($res)) {
        $db->setvalue($r['author'], 'trust', $r['count']);
}
}
}

function migratepost($id) {
global $data;
$data->load('posts' . DIRECTORY_SEPARATOR  . $id . DIRECTORY_SEPARATOR . 'index');
$post = tpost::instance();
foreach ($data->data as $name =>  $value) {
if (isset($post->data[$name])) $post->data[$name] = $value;
}

    $post->posted = $data->date;
$post->idurl = addurl($post->url, $post, $post->id);
return $post;
}

function savepost(tpost $post) {
if (!dbversion) {
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      if (!is_dir($dir)) mkdir($dir, 0777);
      chmod($dir, 0777);
$post->save();
} else {
insertpost($post);
}
}

function insertpost(tpost $post) {
    $self = tposttransform::instance($post);
    $values = array('id' => $post->id);
    foreach (tposttransform::$props as $name) {
      $values[$name] = $self->__get($name);
    }
    $db = litepublisher::$db;
    $db->table = 'posts';
$db->insert_a($values);
    $post->rawdb->insert_a(array(
    'id' => $post->id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));
    
    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert_a(array('post' => $id, 'page' => $i,         'content' => $content));
    }
    
 }

function migratecomments($idpost) {
global $data, $users;
if (!$data->load('posts' . DIRECTORY_SEPARATOR  . $idpost . DIRECTORY_SEPARATOR . 'comments')) return;
if (!isset($data->data['items'])) {
var_dump($idpost, $data->data);
exit();
}
if (!isset($users)) {
$users = new tmigratedata();
$users->load('commentusers');
}

$comments = tcomments::instance($idpost);
$comments->lock();
$comusers = tcomusers::instance($idpost);
$comusers->lock();
foreach ($data->data['items'] as $id => $item) {
if ($item['type'] == '') {
$user = $users->data['items'][$item['uid']];
$author = $comusers->add($user['name'], $user['email'], $user['url'], '');
$cid = $comments->add($author, $item['rawcontent'], $item['status'], '');
if (dbversion) {
$comments->db->setvalue($cid, 'posted', sqldate($item['date']));
$comusers->db->setvalue($author, 'cookie', $user['cookie']);
} else {
$comments->items[$cid]['posted'] = $item['date'];
$comusers->items[$author]['cookie'] = $user['cookie'];
}
} else {
addpingback($idpost, $item);
}
}
$comusers->unlock();
$comments->unlock();

if (dbversion) {      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
}

}

function addpingback($idpost, $item) {

}

function migratetags(tcommontags $tags) {
global $data, $man;
$data->load($tags->basename);
if (dbversion) {
$man->setautoincrement($tags->table, $data->lastid);
} else {
$tags->autoid = $data->lastid;
}

foreach ($data->data['items'] as $id => $item) {
$idurl = addurl($item['url'], $tags, $id);
if (dbversion) {
$tags->db->insert_a(array(
'id' => $id,
'idurl' => $idurl,
'title' => $item['name'],
'itemscount' => count($item['items'])
));
} else {
$tags->items[$id]  = array(
    'id' => $id,
    'parent' => 0,
    'idurl' =>         $idurl,
    'url' =>$item['url'],
    'title' => $item['name'],
    'icon' => 0,
    'itemscount' => count($item['items'])
    );
}
}
if (!dbversion) $tags->save();
}
  
function addurl($url, $obj, $id) {
return litepublisher::$urlmap->add($url, get_class($obj), $id, 'normal');
}

tmigratedata::$dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'data2' . DIRECTORY_SEPARATOR ;
$data = new tmigratedata();

//$man->optimize();
    $tables = $man->gettables();
    foreach ($tables as $table) {
$man->exec("OPTIMIZE TABLE $table");
    }

migrateposts();
migratetags(tcategories::instance());
migratetags(ttags::instance());
migratemenus();
//migratewidgets();

//$man = tdbmanager::instance();
echo  $man->performance();

?>