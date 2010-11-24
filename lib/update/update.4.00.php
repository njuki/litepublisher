<?php

function altertheme($table) {
$man = tdbmanager ::instance();
$man->alter($table, 'drop tmlfile');
$man->alter($table, 'drop theme');
$man->alter($table, "add `idview` int unsigned NOT NULL default '1'");
}

function updatetags($tags) {
if (dbversion) {
$man = tdbmanager ::instance();
$man->alter($tags->table, "add `idview` int unsigned NOT NULL default '1'");
$man->alter($tags->contents->table, 'drop tmlfile');
$man->alter($tags->contents->table, 'drop theme');
} else {
$tags->lock();
foreach ($tags->items as $id => $itemtag) {
$tags->items[$id]['idview'] = 1;
$item = $tags->content->getitem($id);
if (isset($item['tmlfile'])) {
unset($item['tmlfile']);
unset($item['theme']);
$item['idview'] = 1;
$tags->contents->setitem($id, $item);
}
}
$tags->unlock();
}
}

function singleupdate($obj) {
if (isset($obj->data['theme'])) {
unset($obj->data['theme']);
unset($obj->data['tmlfile']);
$obj->data['idview'] = 1;
$obj->data['keywords'] = '';
$obj->data['description'] = '';
$obj->save();
}
}

function updateadminmenu() {
$admin = tadminmenus::instance();
$admin->lock();
$admin->data['idhome'] = 0;
$admin->data['home'] = false;
$idwidgets = $admin->url2id('/admin/widgets/');
$id = $admin->url2id('/admin/themes/');
$childs = $admin->getchilds($id);
foreach ($childs as $child) {
$admin->delete($child);
}
$admin->delete($id);

$id = $admin->url2id('/admin/widgets/');
$childs = $admin->getchilds($id);
foreach ($childs as $child) {
$admin->delete($child);
}
$admin->delete($id);

$admin->deleteurl('/admin/posts/editor/' . litepublisher::$site->q . 'mode=short');

  $views = $admin->createitem(0, 'views', 'admin', 'tadminviews');
  {
    $admin->createitem($views, 'themes', 'admin', 'tadminthemes');
    $admin->createitem($views, 'edittheme', 'admin', 'tadminthemefiles');
    $admin->createitem($views, 'widgets', 'admin', 'tadminwidgets');
    $admin->createitem($views, 'addcustom', 'admin', 'tadminwidgets');
    $admin->createitem($views, 'defaults', 'admin', 'tadminviews');
    $admin->createitem($views, 'spec', 'admin', 'tadminviews');
    $admin->createitem($views, 'headers', 'admin', 'tadminviews');
    $admin->createitem($views, 'admin', 'admin', 'tadminviews');
  }

$admin->data['heads'] = '<link type="text/css" href="$site.files/js/jquery/jquery-ui-1.8.6.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="$site.files/js/jquery/jquery-ui-1.8.6.custom.min.js"></script>
  <script type="text/javascript">
  $(document).ready(function() {
    $("input[rel=\'checkall\']").click(function() {
      $(this).closest("form").find("input:checkbox").attr("checked", true);
      $(this).attr("checked", false);
    });
    
    $("input[rel=\'invertcheck\']").click(function() {
      $(this).closest("form").find("input:checkbox").each(function() {
        $(this).attr("checked", ! $(this).attr("checked"));
      });
      $(this).attr("checked", false);
    });
    
  });
  </script>';

$admin->unlock();
}

function update400() {
$classes = litepublisher::$classes;
$classes->lock();
$plugins = tplugins::instance();
$plugins->lock();
$plugins->delete('adminhistory');
$plugins->delete('adminhover');
$plugins->delete('adminlinks');
$plugins->unlock();
$classes->delete('TXMLRPCFiles');
unset($classes->items['imenu']);
unset($classes->items['tadminhomewidgets');
$classes->items['tsidebars'][0] = 'admin.widgets.class.php';
$classes->items['tadminhtml'] = $classes->items['tadminhtml'];
unset($classes->items['tadminhtml']);
$classes->add('titems_storage', 'items.class.php');
$classes->add('tthemeparserver3', 'theme.parser.ver3.class.php');
$classes->add('twordpressthemeparser', 'theme.parser.wordpress.class.php');
$classes->add('tsite', 'site.class.php'();
$classes->add('tview', 'views.class.php');
$classes->add('tviews',  'views.class.php');
$classes->add('tadminviews', 'admin.views.class.php');
$classes->add('tevents_storage', 'events.class.php');
$classes->add('tevents_itemplate', 'views.class.php');
$classes->add('titems_itemplate', 'views.class.php');
$classes->add('tadminthemefiles', 'admin.themefiles.class.php');
$classes->add('tautoform' 'htmlresource.class.php');
$classes->add('tajaxposteditor', 'admin.posteditor.ajax.class.php');
$classes->add('tajaxmenueditor', 'admin.menu.ajax.class.php');
$classes->add('tajaxtageditor',  'admin.tags.ajax.class.php');
$classes->items['tchildpost'] = array('posts.child.class.php', '');
$classes->add('tchildposts',  'posts.child.class.php');
unset($classes->interfaces['itemplate2']);
$classes->interfaces['iwidgets'] = 'interfaces.php';
$classes->unlock();

$urlmap = litepublisher::$urlmap;
$urlmap->lock();
$urlmap->add('/rss/categories/', 'trss', 'categories', 'tree');
$urlmap->add('/rss/tags/', 'trss', 'tags', 'tree');
  $urlmap->unlock();

$data = new tdata();
$data->basename = 'widgets';
tfilestorage::load($data);
$view = tview::instance();
$view->sidebars = $data->data['sidebars'];
unset($data->data['sidebars'];
$widgets = twidgets::instance();
$widgets->data = $data->data;
$widgets->save();

$home = thomepage();
$old = $home->data;
    $home->data= array(
    'id' => 0,
    'author' => 0, //not supported
    'content' => '',
    'rawcontent' => '',
    'keywords' => '',
    'description' => '',
    'password' => '',
    'idview' => 1,

    //owner props
    'title' => tlocal::$data['default']['home'],
    'url' => '/',
    'idurl' => 0,
    'parent' => 0,
    'order' => 0,
    'status' => 'published'
    );

$home->content = $old['text'];

    $home->data['image'] = $old['image'];
    $home->data['hideposts'] = $old['hideposts'];

litepublisher::$urlmap->delete('/');
$menus = tmenus::instance();
$menus->lock();
$menus->data['idhome'] = 0;
$menus->data['home'] = false;

$home->install();

foreach ($menus->items as $id => $item) {
$menu = tmenu::instance($id);
$menu->data['idview'] = 1;
unset($menu->data['tmlfile']);
unset($menu->data['theme']);
$menu->content = $menu->data['content'];
$menu->save();
}
$menus->unlock();

updateadminmenu();

//contact form
  $html = tadminhtml::instance();
if (!isset($html->ini['installation'])) $html->loadini(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.ini');
  $html->section = 'contactform';
    tlocal::loadinstall();
  $lang = tlocal::instance('contactform');

$contact = tcontactform();
$contact->data['subject'] = $lang->subject;
$contact->data['errmesg'] =$html->errmesg();
$contact->data['success'] = $html->success();
$contact->data['idview'] = 1;
$contact->save();

if (dbversion) {
altertheme('posts');
altertheme('tagscontent');
altertheme('catscontent');
} else {
$posts = tposts::instance();
foreach ($posts->items as $id => $item) {
$post = tpost::instance($id);
unset($post->data['tmlfile']);
unset($post->data['theme']);
$post->data['idview'] = 1;
$post->save();
$post->free();
}

updatetags(ttags::instance());
updatetags(tcategories::instance());
}

singleupdate(tarchives::instance());
singleupdate(tforbidden::instance());
singleupdate(tnotfound404::instance());
singleupdate(tsitemap::instance());
singleupdate(tsimplecontent::instance());
if (isset($classes->items['tprofile'])) singleupdate(tprofile::instance());

$static = tstaticpages::instance();
if (count($static->items)) {
foreach ($static->items as $id => $item) {
$static->items[$id]['idview'] = 1;
}
$static->save();
}

$data = new tdata();
$data->basename = 'template';
tfilestorage::load($data);
unset($data->data['stdjavascripts']);
$template = ttemplate::instance();
$template->lock();
$template->heads .= implode("\n", $data->data['javascripts']);
unset($template->data['javascripts']);
unset($template->data['events']['onadminhover']);
$template->unlock();
tview::instance(1)->themename = $template->data['theme'];
unset($template->data['adminjavascripts']);
unset($template->data['adminheads']);
    unset($template->data['theme']);
    unset($template->data['admintheme']);

$template->heads =
    '<link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />
    <link rel="pingback" href="$site.url/rpc.xml" />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />
    <link rel="shortcut icon" type="image/x-icon" href="$template.icon" />
    <meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->
    <meta name="keywords" content="$template.keywords" />
    <meta name="description" content="$template.description" />
    <link rel="sitemap" href="$site.url/sitemap.htm" />
    <script type="text/javascript" src="$site.files/js/litepublisher/litepublisher.min.js"></script>';

tstorage::savemodified();

  $rpc = TXMLRPC::instance();
$rpc->deleteclass('TXMLRPCFiles');
turlmap::unsub('TXMLRPCFiles');

$l = litepublisher::$paths->languages;
@unlink($l . 'adminru.ini');
@unlink($l . 'adminen.ini');
}
?>
