<?php

function update400() {
if (!isset(litepublisher::$site)) {
create_storage();
die('Please update your /index.php from <a href="http://litepublisher.googlecode.com/svn/trunk/utils/index3to4.php">http://litepublisher.googlecode.com/svn/trunk/utils/index3to4.php</a>');
} else {
update_step2();
}
}

function addstorage($storage, $obj) {
$storage->data[$obj->getbasename()] = $obj->data;
}

function load_data($name) {
$result = new tdata();
$result->basename = $name;
$result->load();
return $result;
}

function create_storage() {
$storage = new tdata();
$storage->basename = 'storage';
add_new_kernel_classes();
addstorage($storage, litepublisher::$classes);

$options = litepublisher::$options;
foreach ($options->data['storage'] as $name => $datastorage) {
if ($name == 'posts') $name ='posts'  . DIRECTORY_SEPARATOR  . 'index';
$storage->data[$name] = $datastorage;
}

addstorage($storage, $options);
unset($storage->data['options']['storage']);


create_site($storage->data);

$widgets = load_data('widgets');
unset($widgets->data['sitebars']);
foreach ($widgets->data['classes'] as $class => &$items) {
unset($item);
foreach ($items as &$item) {
$item['sidebar'] = $item['sitebar'];
unset($item['sitebar']);
}
}
$storage->data['widgets'] = $widgets->data;

$storage->data['template'] = get_template_data();
$storage->save();
}

function create_site(&$data) {
$options = &$data['options'];
$e = new tevents();
$site = &$e->data;

$site['url'] = $options['url'];
unset($options['url']);

$site['files'] = $options['files'];
unset($options['files']);

$site['q'] = $options['q'];
unset($options['q']);

  $site['subdir'] =$options['subdir'];
unset($options['subdir']);

  $site['fixedurl'] =$options['fixedurl'];
unset($options['fixedurl']);

$site['name'] = $options['name'];
unset($options['name']);

$site['keywords'] = $options['keywords'];
unset($options['keywords']);

$site['description'] = $options['description'];
unset($options['description']);

  $site['home'] = '/';
$data['site'] = $site;
}


function get_template_data() {
$template = load_data('template');
$data = &$template->data;
unset($data['stdjavascripts']);
unset($data['javascripts']);
unset($data['events']['onadminhover']);
unset($data['adminjavascripts']);
unset($data['adminheads']);
    unset($data['theme']);
    unset($data['admintheme']);

$data['heads'] =
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

return $data;
}

//step2
function update_posts() {
if (dbversion) {
$table = 'posts';
$man = tdbmanager ::instance();
$man->alter($table, 'drop tmlfile');
$man->alter($table, 'drop theme');
$man->alter($table, "add `idview` int unsigned NOT NULL default '1'");
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
}
}

function updatetags($tags) {
$tags->lock();
$tags->data['includeparents'] = false;
$tags->data['includechilds'] = false;

if (dbversion) {
$man = tdbmanager ::instance();
$man->alter($tags->table, "add `idview` int unsigned NOT NULL default '1'");
$man->alter($tags->contents->table, 'drop tmlfile');
$man->alter($tags->contents->table, 'drop theme');
} else {
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
}
$tags->unlock();
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

function update_home_menu() {
$urlitem = litepublisher::$urlmap->findurl('/');
$home = getinstance($urlitem['class']);
$oldhome = load_data('homepage');
$old = $oldhome->data;
//echo implode("\n" , array_keys($old));

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

$home->lock();
$home->install();
$home->unlock();
foreach ($menus->items as $id => $item) {
$menu = tmenu::instance($id);
$menu->data['idview'] = 1;
unset($menu->data['tmlfile']);
unset($menu->data['theme']);
$menu->content = $menu->data['content'];
$menu->save();
}
$menus->unlock();
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
$admin->items[$views]['order'] = 4;
  {
    $admin->createitem($views, 'themes', 'admin', 'tadminthemes');
    $admin->createitem($views, 'edittheme', 'admin', 'tadminthemetree');
    $admin->createitem($views, 'themefiles', 'admin', 'tadminthemefiles');
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

function update_contactform() {
  $html = tadminhtml::instance();
if (!isset($html->ini['contactform'])) $html->loadini(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.ini');
  $html->section = 'contactform';
    tlocal::loadinstall();
  $lang = tlocal::instance('contactform');
$theme = ttheme::getinstance('default');

$contact = tcontactform::instance();
$contact->data['subject'] = $lang->subject;
$contact->data['errmesg'] =$html->errmesg();
$contact->data['success'] = $html->success();
$contact->data['idview'] = 1;
$contact->save();
}

function update_static() {
$static = tstaticpages::instance();
if (count($static->items)) {
foreach ($static->items as $id => $item) {
$static->items[$id]['idview'] = 1;
}
$static->save();
}
}

function add_new_kernel_classes() {
$classes = litepublisher::$classes;
$classes->lock();
$classes->items['tevents_storage'] = array('events.class.php', '');
$classes->items['tevents_itemplate'] = array('views.class.php', '');
$classes->items['titems_storage'] = array('items.class.php', '');
$classes->items['titems_itemplate'] = array('views.class.php', '');
$classes->items['tsite'] = array('site.class.php', '');
$classes->items['tview']  = array('views.class.php', '');
$classes->items['tviews']  = array('views.class.php', '');
$classes->unlock();
}

function add_classes() {
$classes = litepublisher::$classes;
$classes->lock();
$classes->delete('TXMLRPCFiles');
unset($classes->items['imenu']);
unset($classes->items['tadminhomewidgets']);
unset($classes->items['tsitebars']);
$classes->items['tsidebars'] = array('admin.widgets.class.php', '');
$classes->items['tadminhtml'] = $classes->items['THtmlResource'];
unset($classes->items['THtmlResource']);
//$classes->add('tviews',  'views.class.php');
$classes->add('tthemeparserver3', 'theme.parser.ver3.class.php');
$classes->add('twordpressthemeparser', 'theme.parser.wordpress.class.php');
$classes->add('tadminviews', 'admin.views.class.php');
$classes->add('tadminthemefiles', 'admin.themefiles.class.php');
$classes->add('tadminthemetree', 'admin.themetree.class.php');
$classes->items['tautoform'] = array('htmlresource.class.php', '');
$classes->add('tajaxposteditor', 'admin.posteditor.ajax.class.php');
$classes->add('tajaxmenueditor', 'admin.menu.ajax.class.php');
$classes->add('tajaxtageditor',  'admin.tags.ajax.class.php');
$classes->items['tchildpost'] = array('posts.child.class.php', '');
$classes->add('tchildposts',  'posts.child.class.php');
$classes->add('tremotefiler', 'remote.abstract.filer.class.php');
$classes->add('tftpfiler', 'remote.ftp.class.php');
$classes->add('tftpsocketfiler', 'remote.ftpsocket.class.php');
$classes->add('tlocalfiler', 'remote.local.filer.class.php');
$classes->add('tssh2filer', 'remote.ssh2.class.php');

unset($classes->interfaces['itemplate2']);
$classes->interfaces['iwidgets'] = 'interfaces.php';
$classes->unlock();
}

function create_views() {
$views = tviews::instance();
$views->install();
$widgets = load_data('widgets');
$view = tview::instance(1);
$view->sidebars = $widgets->data['sitebars'];
$template = load_data('template');
$view->themename = $template->data['theme'];
$views->save();
}

function update_step2() {
tlocal::clearcache();
$classes = litepublisher::$classes;
$classes->lock();

$plugins = tplugins::instance();
$plugins->lock();
$plugins->delete('adminhistory');
$plugins->delete('adminhover');
$plugins->delete('adminlinks');
$plugins->unlock();
add_classes();
$classes->unlock();
create_views();
litepublisher::$options->crontime = time();

$urlmap = litepublisher::$urlmap;
$urlmap->lock();
$urlmap->add('/rss/categories/', 'trss', 'categories', 'tree');
$urlmap->add('/rss/tags/', 'trss', 'tags', 'tree');
  $urlmap->unlock();
update_home_menu();
updateadminmenu();
update_contactform();
update_posts();
updatetags(ttags::instance());
updatetags(tcategories::instance());

singleupdate(tarchives::instance());
singleupdate(tforbidden::instance());
singleupdate(tnotfound404::instance());
singleupdate(tsitemap::instance());
singleupdate(tsimplecontent::instance());
if (isset($classes->items['tprofile'])) singleupdate(tprofile::instance());
update_static();
tstorage::savemodified();

  $rpc = TXMLRPC::instance();
$rpc->deleteclass('TXMLRPCFiles');
turlmap::unsub('TXMLRPCFiles');

$backuper = tbackuper::instance();
if (!isset($backuper->data['ftpfolder'])) {
$backuper->data['fftpfolder'] = '';
$backuper->save();
}

return;
$l = litepublisher::$paths->languages;
@unlink($l . 'adminru.ini');
@unlink($l . 'adminen.ini');
}
