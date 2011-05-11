<?php
/* export all data from Wordpress to Lite Publisher
Export categories, tags, posts, comments
*/
set_time_limit(300);
@ini_set('memory_limit', '48M'); 
define('litepublisher_mode', 'import');
require('index.php');
if (@file_exists('wp-load.php')) {
require('wp-load.php');
} else {
 require('wp-config.php');
}
echo "<pre>\n";

function ExportOptions() {
$options = litepublisher::$options;
$options->lock();
litepublisher::$site->name = get_option('blogname');
litepublisher::$site->description = get_option('blogdescription');
$options->email = get_option('admin_email');
$options->unlock();

 $robots = trobotstxt ::instance();
 $robots->AddDisallow('/feed/');

$redir = tredirector::instance();
$redir->items['/feed/'] = '/rss.xml';
$redir->items['/feed'] = '/rss.xml';
$redir->save();
}

function ExportPages() {
		global $wpdb;
$menus = tmenus::instance();
$menus->lock();
  litepublisher::$urlmap->lock();

$list = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'page'");
foreach ($list as  $item) {
  $menuitem = new tmenu();
$menuitem->id = (int) $item->ID;
//$menuitem->date =strtotime(mysql2date('Ymd\TH:i:s', $item->post_date));
  $menuitem->title = $item->post_title;
  $menuitem->content = $item->post_content;
$menuitem->status = 'published';
$menuitem->order = (int) $item->menu_order;
$menuitem->parent = (int) $item->post_parent;
$menuitem->password = $item->post_password;
  
  //if ($menuitem->date == 0) $menuitem->date = time();
$url =get_permalink($item->ID);
$UrlArray = parse_url($url);
$url = $UrlArray['path'];
if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
$menuitem->url = $url;
    $item->idurl = $urlmap->Add($item->url, get_class($item), $item->id);
    $menuitem->idurl = litepublisher::$urlmap->Add($menuitem->url, get_class($menuitem), $menuitem->id);
    $menus->items[$menuitem->id] = array(
    'id' => $menuitem->id,
    'class' => get_class($menuitem)
    );
$menus->autoid = max($menus->autoid, $menuitem->id);
    //move props
    foreach (tmenu::$ownerprops as $prop) {
      $menus->items[$menuitem->id][$prop] = $menuitem->$prop;
      if (array_key_exists($prop, $menuitem->data)) unset($menuitem->data[$prop]);
    }

  $menuitem->save();

echo "menu $menuitem->id\n";	
flush();
}
litepublisher::$urlmap->unlock();
$menus->sort();
$menus->unlock();
}

 function AddTag($tags, $id, $parent, $title, $url) {
$id = (int) $id;
$parent = (int) $parent;
if (isset($tags->items[$id])) return;
  $UrlArray = parse_url($url);
  $url = $UrlArray['path'];
  if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
        $idurl =         litepublisher::$urlmap->add($url, get_class($tags),  $id);

    if ($tags->dbversion)  {
//$tags->autoid = max($tags->autoid, $id);
    $tags->db->exec(sprintf('ALTER TABLE %s AUTO_INCREMENT = %d',$tags->thistable,$tags->autoid));
$tags->db->insert_a(array(
      'parent' => $parent,
'idurl' => $idurl,
      'title' => $title,
      'idview' => 1
      ));
    } else {
$tags->autoid = max($tags->autoid, $id);
}
    
    $tags->items[$id] = array(
    'id' => $id,
    'parent' => $parent,
    'idurl' =>         $idurl,
    'url' =>$url,
    'title' => $title,
    'icon' => 0,
    'idview' => 1,
    'itemscount' => 0
    );
}
    
function ExportCategories() {
$categories = tcategories::instance();
$categories->lock();
		if ( $cats = get_categories('get=all') ) {
			foreach ( $cats as $cat ) {
AddTag($categories, $cat->term_id, $cat->parent, $cat->name, get_category_link($cat->term_id));
}
}
$categories->unlock();
}

function  ExportPosts() {
		global $wpdb, $from;

  $urlmap = turlmap::instance();
  $urlmap->lock();

$posts = tposts::instance();
$posts->lock();
if (dbversion) {
$autoid = $wpdb->get_var("SELECT max(ID) as max FROM $wpdb->posts ", 'max');
    $posts->db->exec(sprintf('ALTER TABLE %s AUTO_INCREMENT = %d',$posts->thistable,$autoid ));
}

$categories = tcategories::instance();
$categories->loadall();
$categories->lock();
$tags = ttags::instance();
$tags->loadall();
$tags->lock();
$CommentManager = tcommentmanager::instance();
$CommentManager->lock();

if ($from == 0) {
ExportCategories();
ExportPages();
}

$cron = tcron::instance();
$cron->disableadd = true;
//$list = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'post'");
$list = $wpdb->get_results("SELECT ID FROM $wpdb->posts 
WHERE post_type = 'post'
and ID > $from
limit 500
");
foreach ($list as $idresult) {
$itemres= $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $idresult->ID");
$item = &$itemres[0];

  $post = new tpost();
$post->id = (int) $item->ID;
$post->posted =strtotime(mysql2date('Ymd\TH:i:s', $item->post_date));
  $post->title = $item->post_title;
  $post->categories = wp_get_post_categories($item->ID);

$taglist = array();
$wptags = wp_get_post_tags( $item->ID);
foreach ($wptags as 	$wptag) {
AddTag($tags, (int) $wptag->term_id, $wptag->name, get_tag_link($wptag->term_id ));
$taglist[] = (int) $wptag->term_id ;
}

  $post->tags = $taglist;
$UrlArray = parse_url(get_permalink($item->ID));
$url = $UrlArray['path'];
if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
$post->url = $url;
$post->idurl = litepublisher::$urlmap->add($post->url, get_class($post), $post->id);
  $post->content = $item->post_content;
$post->commentsenabled =  'open' == $item->comment_status;
$post->pingenabled = 'open' == $item->ping_status;
$post->password = $item->post_password;
$post->status = $item->post_status == 'publish' ? 'published' : 'draft';
savepost($post);
  $categories->itemsposts->setitems($post->id, $post->categories);
  $tags->itemsposts->setitems($post->id, $post->tags);
ExportComments($post);
$post->free();
}
$cron->unlock();
//$CommentManager->SubscribtionEnabled = true;
//$CommentManager->NotifyModerator = true;
$CommentManager->unlock();
$tags->unlock();
$categories->unlock();

$posts->UpdateArchives();
$posts->addrevision();
$posts->unlock();
  $urlmap->clearcache();
$arch = tarchives::instance();
$arch->postschanged();
  $urlmap->unlock();

if (count($list) < 500) return false;
return $item->ID;
}

function savepost($post) {
    if ($post->posted == 0) $post->posted = time();
$post->modified = time();
 
$posts =tposts::instance();
  $posts->autoid = max($posts->autoid, $post->id);
if (dbversion) {
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
    
} else {
      $dir =litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      if (!is_dir($dir)) mkdir($dir, 0777);
      chmod($dir, 0777);
$post->save();

      $posts->items[$post->id] = array(
      'posted' => $post->posted
      );
      if   ($post->status != 'published') $posts->items[$post->id]['status'] = $post->status;
      if   ($post->author > 1) $posts->items[$post->id]['author'] = $post->author;
}

echo "$post->id\n";	
flush();
 }

function ExportComments(tpost $post) {
  global $wpdb;
  $users = dbversion ? tcomusers ::instance() : tcomusers ::instance($$post->id);
  $CommentManager = tcommentmanager::instance();
  $comments = $post->comments;
  $filter = tcontentfilter::instance();
  $items = $wpdb->get_results("SELECT  * FROM $wpdb->comments 
  WHERE comment_post_ID   = $post->id");
foreach ($items as $item) {
if ($item->comment_type != '') continue;
  $userid = (int) $users->Add($item->comment_author, 
$item->comment_author_email,
$item->comment_author_url,
$item->comment_author_IP 
);

$date =strtotime(mysql2date('Ymd\TH:i:s', $item->comment_date));
$status = $item->comment_approved ==  '1' ? 'approved' : 'holld';
$id = (int) $item->comment_ID;

    $filtered = $filter->filtercomment($item->comment_content);
if (dbversion) {
$comments->db->insert_a(   $a = array(
'id' => $id,
    'post' => $post->id,
    'parent' => 0,
    'author' => $userid,
    'posted' => sqldate($date),
    'content' =>$filtered,
    'status' => $status
    ));

    $comments->getdb($comments->rawtable)->add(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'ip' => $ip,
    'rawcontent' => $item->comment_content,
    'hash' => md5($item->comment_content)
    ));
} else {
    $a = array(
    'author' => $userid,
    'posted' => $date,
    'content' => $filtered,
    );
$comments->autoid = max($comments->autoid, $id);
    if ($status == 'approved') {
      $comments->items[$id] = $a;
    } else {
      $comments->hold->items[++$id] =  $a;
      $comments->hold->save();
    }
    $comments->raw->add($id, $item->comment_content, $item->comment_author_IP );    

    //if ($status == 'approved') $commentmanager->addrecent($id, $idpost);
}
}
$comments->save();
}

$from = isset($_REQUEST['from']) ? $_REQUEST['from'] : 0;
if ($from == 0) ExportOptions();
if ($from = ExportPosts()) {
echo "</pre>
<form name='form' action='' type='get'>
<input type=hidden name='from' value='$from' />
 <p><input type='submit' name='Submit' value='Continue'/></p>
</form>
<br>";
} else {
echo "import finished<br>\n";
}

echo round(memory_get_usage()/1024/1024, 2), 'MB <br>'; 
?>