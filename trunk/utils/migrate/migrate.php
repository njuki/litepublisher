<?php
set_time_limit(120);
define('litepublisher_mode', 'xmlrpc');
include('index.php');

function cleartags($tags) {
$tags->lock();
$tags->loadall();
foreach ($tags->items as $id => $item) {
$tags->delete($id);
}
$tags->unlock();
}

function clearposts() {
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
}

function clearmenu() {


$menus = tmenus::instance();
$menus->lock();
foreach ($menus->items as $id => $item) {
$menus->delete($id);
}
$menus->unlock();
}

class tmigratedata extends tdata {
public static $dir;

public function loadfile($name) {
$this->data = array();
$filename = self::$dir . $name . '.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }

}

}//class

function migrateposts() {
global $data, $man;
$data->loadfile('posts' . DIRECTORY_SEPARATOR . 'index');
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
$trusts = $db->res2assoc($db->query("SELECT author as 'author', count(author) as 'count' FROM  $db->comments 
where status = 'approved' GROUP BY  author"));

$db->table = 'comusers';
foreach ($trusts as $r) {
        $db->setvalue($r['author'], 'trust', $r['count']);
}
unset($trust);
}
}

function migratepost($id) {
global $data;
$data->loadfile('posts' . DIRECTORY_SEPARATOR  . $id . DIRECTORY_SEPARATOR . 'index');
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
if (!$data->loadfile('posts' . DIRECTORY_SEPARATOR  . $idpost . DIRECTORY_SEPARATOR . 'comments')) return;
if (!isset($data->data['items'])) {
var_dump($idpost, $data->data);
exit();
}
if (!isset($users)) {
$users = new tmigratedata();
$users->loadfile('commentusers');
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
$data->loadfile($tags->basename);
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

function migratemenus() {
$data = new tmigratedata();
$data->loadfile('menus' . DIRECTORY_SEPARATOR . 'index');
$menus = tmenus::instance();
$menus->lock();
$menus->autoid = $data->lastid;

foreach ($data->data['items'] as $id => $item) {
$menu = migratemenu($id, $item['class']);
    $menus->items[$id] = array(
    'id' => $id,
    'class' => get_class($menu)
    );
    //move props
    foreach (tmenu::$ownerprops as $prop) {
      $menus->items[$id][$prop] = $menu->$prop;
      if (array_key_exists($prop, $menu->data)) unset($menu->data[$prop]);
    }
$menu->id = $id;
$menu->idurl = addurl($menu->url, $menu, $id);
$menu->save();
}
$menus->sort();
$menus->unlock();
}

function migratemenu($id, $class) {
global $data;
$data->loadfile('menus' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR  . 'index');
$classes = array(
'TMenuItem' => 'tmenu',
'TContactForm' => 'tcontactform'
);
$class = $classes[$class];
$menu = new $class();
foreach ($data->data as $name => $value) {
if (isset($menu->data[$name])) $menu->data[$name] = $value;
}
$menu->data['id'] = 0;
return $menu;
}  

function addurl($url, $obj, $id) {
return litepublisher::$urlmap->add($url, get_class($obj), $id, 'normal');
}

function migrateoptions() {
global $data;
$data->loadfile('options');
$options = litepublisher::$options;
$options->name = $data->name;
$options->description = $data->description;
$options->keywords = $data->keywords;
$options->email = $data->email;
    $options->timezone = $data->timezone;
$options->cache = $data->CacheEnabled;
$options->expiredcache = $data->CacheExpired;
$options->perpage = $data->postsperpage;
  $options->DefaultCommentStatus = $data->DefaultCommentStatus;
  $options->commentsdisabled = $data->commentsdisabled;
  $options->commentsenabled = $data->commentsenabled;
  $options->pingenabled = $data->pingenabled;
  $options->commentpages = $data->commentpages;
  $options->commentsperpage = $data->commentsperpage;
  $options->echoexception = $data->echoexception;
    }

tmigratedata::$dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'data2' . DIRECTORY_SEPARATOR ;
$data = new tmigratedata();

if (dbversion) {
$man = tdbmanager::instance();
    $tables = $man->gettables();
    foreach ($tables as $table) {
$man->exec("OPTIMIZE TABLE $table");
    }
}

litepublisher::$urlmap->lock();
/*
clearposts();
cleartags(tcategories::instance());
cleartags(ttags::instance());
clearmenu();
migrateoptions();
migrateposts();
migratetags(tcategories::instance());
migratetags(ttags::instance());
migratemenus();
//migratewidgets();
*/
migrateoptions();
clearmenu();
migratemenus();
litepublisher::$urlmap->unlock();
litepublisher::$options->savemodified();
//echo  $man->performance();
echo "\nmigrated\n";
?>