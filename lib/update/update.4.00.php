<?php

function altertheme($table) {
$man = tdbmanager ::instance();
$man->alter($table, 'drop tmlfile');
$man->alter($table, 'drop theme');
$man->alter($table, "add `idview` int unsigned NOT NULL default '1'");
}

function updatetags($tags) {
foreach ($tags->items as $id => $itemtag) {
$item = $tags->content->getitem($id);
if (isset($item['tmlfile'])) {
unset($item['tmlfile']);
unset($item['theme']);
$item['idview'] = 1;
$tags->contents->setitem($id, $item);
}
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
$admin->unlock();
}

function update400() {
$classes = litepublisher::$classes;
$classes->lock();
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
unset($classes->interfaces['itemplate2']);
$classes->interfaces['iwidgets'] = 'interfaces.php';
$classes->unlock();

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
$template->data
$template->heads .= implode("\n", $template->data['javascripts']);
unset($template->data['javascripts']);
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
  $rpc->lock();
  $rpc->delete('litepublisher.files.gettags');
  $rpc->delete('litepublisher.files.getbrowser');
  $rpc->delete('litepublisher.files.getpage');
$rpc->unlock();
}
?>
