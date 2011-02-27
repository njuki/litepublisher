<?php
/* export all data from Wordpress to Lite Publisher
Export categories, tags, posts, comments
*/
echo "<pre>\n";
set_time_limit(300);
@ini_set('memory_limit', '48M'); 
define('litepublisher_mode', 'import');
require('index.php');
if (@file_exists('wp-load.php')) {
require('wp-load.php');
} else {
 require('wp-config.php');
}

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
if (isset($tags->items[$id])) return;
  $UrlArray = parse_url($url);
  $url = $UrlArray['path'];
  if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
  
  $tags->items[$id] = array(
  'id' => $id,
  'count' => 0,
  'name' => $name,
  'url' =>$url,
  //'description ' => '',
  //'keywords' => '',
  'items' => array()
  );
  
  $urlmap =&TUrlmap::instance();
$dir = "/$tags->PermalinkIndex/";
if (substr($url, 0, strlen($dir)) == $dir) {
$subdir = substr($url, strlen($dir));
$subdir = trim($subdir, '/');
$urlmap->AddSubNode($tags->PermalinkIndex, $subdir, get_class($tags), $id);
} else {
  $urlmap->Add($url, get_class($tags), $id);
}

}  

function ExportCategories() {
$categories = &TCategories::instance();
$categories->lock();
		if ( $cats = get_categories('get=all') ) {
			foreach ( $cats as $cat ) {
AddTag($categories, $cat->term_id, $cat->name, get_category_link($cat->term_id));
$categories->lastid = max($categories->lastid, $cat->term_id);
}
}
$categories->unlock();
}

function  ExportPosts() {
		global $wpdb, $from;

  $urlmap = &TUrlmap::instance();
  $urlmap->lock();

$posts = &TPosts::instance();
$posts->lock();
$categories = &TCategories::instance();
$categories->lock();
$tags = &TTags::instance();
$tags->lock();
$CommentManager = &TCommentManager::instance();
$CommentManager->lock();

$users = &TCommentUsers::instance();
$users->lock();

if ($from == 0) {
ExportCategories();
ExportPages();
}

$cron = &TCron::instance();

//$list = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'post'");
$list = $wpdb->get_results("SELECT ID FROM $wpdb->posts 
WHERE post_type = 'post'
and ID > $from
limit 500
");
foreach ($list as $idresult) {
$itemres= $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $idresult->ID");
$item = &$itemres[0];

  $post = &new TPost();
$post->id = (int) $item->ID;
$post->date =strtotime(mysql2date('Ymd\TH:i:s', $item->post_date));

  $post->title = $item->post_title;
  $post->categories = wp_get_post_categories($item->ID);

$taglist = array();
$wptags = wp_get_post_tags( $item->ID);
foreach ($wptags as 	$wptag) {
AddTag($tags, (int) $wptag->term_id, $wptag->name, get_tag_link($wptag->term_id ));
$tags->lastid = max($tags->lastid, $wptag->term_id);
$taglist[] = $wptag->term_id ;
}

  $post->tags = $taglist;
$post->url =get_permalink($item->ID);
  $post->content = $item->post_content;
$post->commentsenabled =  'open' == $item->comment_status;
$post->pingenabled = 'open' == $item->ping_status;
$post->password = $item->post_password;
$post->status = $item->post_status == 'publish' ? 'published' : 'draft';
//
ExportPost($post);
$categories->PostEdit($post->id);
$tags->PostEdit($post->id);
ExportComments($post);
unset(TPost::$AllItems['TPost']);
}
$cron->unlock();
$users->unlock();
//$CommentManager->SubscribtionEnabled = true;
//$CommentManager->NotifyModerator = true;
$CommentManager->unlock();
$tags->unlock();
$categories->unlock();
$posts->unlock();
  $urlmap->ClearCache();
$arch = &TArchives::instance();
$arch->PostsChanged();
  $urlmap->unlock();

if (count($list) < 500) return false;
return $item->ID;
}

function ExportPost(&$post) {
  global $paths;
$posts =&TPosts::instance();
  $posts->lastid = max($posts->lastid, $post->id);
  @mkdir($paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $post->id, 0777);
  @chmod($paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $post->id, 0777);

    if ($post->date == 0) $post->date = time();
$post->modified = time();
$UrlArray = parse_url($post->url);
$url = $UrlArray['path'];
if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
$post->url = $url;
  
  $posts->Updated($post);
$cron = &TCron::instance();
$cron->Remove($cron->lastid);
  $post->save();
$urlmap = &TUrlmap::instance();
  $urlmap->Add($post->url, get_class($post), $post->id);
echo "$post->id\n";	
flush();
 }

function ExportComments(&$post) {
  global $wpdb;
  $users = &TCommentUsers::instance();
  $CommentManager = &TCommentManager::instance();
  $comments = &$post->comments;
  $ContentFilter = &TContentFilter::instance();
  $items = $wpdb->get_results("SELECT  * FROM $wpdb->comments 
  WHERE comment_post_ID   = $post->id");
foreach ($items as $item) {
if ($item->comment_type != '') continue;
  $userid = $users->Add($item->comment_author, 
$item->comment_author_email,
$item->comment_author_url);

$date =strtotime(mysql2date('Ymd\TH:i:s', $item->comment_date));
$status = $item->comment_approved ==  '1' ? 'approved' : 'holld';
$id = (int) $item->comment_ID;
  $comments->items[$id] = array(
  'id' => $id,
  'uid' => $userid ,
  'date' => $date,
  'status' => $status,
  'type' => $item->comment_type,
  'content' => $ContentFilter ->GetCommentContent($item->comment_content),
  'rawcontent' =>  $item->comment_content,
  'ip' => $item->comment_author_IP 
  );

  $CommentManager->items[$id] = array(
  'uid' => (int) $userid,
  'pid' => (int) $post->id,
  'date' => $date
);
if ($status != 'approved')   $CommentManager->items[$id]['status'] = $status;
//if ($item->comment_type != '')   $CommentManager->items[$id]['type'] = $item->comment_type ;

$CommentManager->lastid = max($CommentManager->lastid, $item->comment_ID);
}
$comments->save();
//unset(TComments::$Instances[$post->id]);
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