<?php

function update400() {
if (!isset(litepublisher::$site)) {
    $v = litepublisher::$options->version + 0.01;
    while ( $v<= 3.99) {
$ver = (string) $v;
      if (strlen($ver) == 3) $ver .= '0';
$func = 'function' . str_replace('.', '', $ver);
if (function_exists($func)) $func();
      $v = $v + 0.01;
    }

    litepublisher::$options->version = '3.98';
create_storage();
tlocal::clearcache();
create_storage_folder();
echo '<pre>
Для продолжения обновления вам следует заменить файл index.php в корне сайта на новый файл 4 версии. Взять его можно из последнего релиза либо из репозитория по адресу: 
<a href="http://litepublisher.googlecode.com/svn/trunk/index.php">http://litepublisher.googlecode.com/svn/trunk/index.php</a>
</pre>';
exit();
} else {
update_step2();
}
}

function create_storage_folder() {
$dir = litepublisher::$paths->home;
if (!@is_dir($dir)) @mkdir($dir, 0777);
@chmod($dir, 0777);
if (!@is_dir($dir)) {
echo 'не удалось создать папку <strong>storage</storage> в корне сайта. Дальнейшее обновление невозможно, пожалуйста, создайте папку storage в корне сайта и присвойте ей права 0777<br>';
}
$dir .= DIRECTORY_SEPARATOR  ;
if (!file_put_contents($dir . 'index.htm', ' '))  {
echo 'не удалось записать файл storage/index.htm - пожалуйста, проверьте права на запись в папке storage<br>';
} else {
@chmod($dir . 'index.htm', 0666);
}

if (!file_put_contents($dir . '.htaccess', 'Deny from all'))  {
echo 'не удалось записать файл storage/.htaccess - пожалуйста, проверьте права на запись в папке storage<br>';
} else {
@chmod($dir . '.htaccess', 0666);
}

foreach (array('backup', 'cache', 'data') as $name) {
$old = rtrim(litepublisher::$_paths[$name], DIRECTORY_SEPARATOR  );
$newdir = $dir . $name;
if (!@is_dir($newdir)) {
if (!@rename($old, $newdir)) {
echo "Не удалось переименовать<br>$old<br>в<br>$newdir<br>Пожалуйста, вручную переместити эти папки<br>";
}
}
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
$posts = tposts::instance();
if (dbversion) {
$table = 'posts';
$man = tdbmanager ::instance();
$man->alter($table, 'drop tmlfile');
$man->alter($table, 'drop theme');
$man->alter($table, "add `idview` int unsigned NOT NULL default '1'");
} else {
foreach ($posts->items as $id => $item) {
$post = tpost::instance($id);
unset($post->data['tmlfile']);
unset($post->data['theme']);
$post->data['idview'] = 1;
$post->save();
$post->free();
}
}
$posts->addrevision();
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

foreach ($menus->items as $id => $item) {
$menu = tmenu::instance($id);
$menu->data['idview'] = 1;
unset($menu->data['tmlfile']);
unset($menu->data['theme']);
$menu->content = $menu->data['content'];
$menu->save();
}
$home->lock();
$home->install();
$home->unlock();
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
$admin->items[$views]['order'] = $admin->url2id('/admin/files/') + 1;
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
tlocal::loadlang('');
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

//updates 3.xx
if (!function_exists('function334')) {
function update334() {
if (!dbversion) return;
$data = &litepublisher::$options->data;

$data['posts'] = $data['tposts'];
unset($data['tposts']);
unset($data['turlmap']);
unset($data['tcomments']);
unset($data['tcomusers']);
unset($data['tpingbacks']);
unset($data['tfileitems']);
$data['foaf'] = $data['tfoaf'];
unset($data['tfoaf']);
unset($data['tusers']);

litepublisher::$options->save();
}
}

if (!function_exists('function335')) {
function update335() {
$menus = tmenus::instance();
foreach ($menus->items as $id => $item) {
$menus->items[$id]['class'] = 'tmenu';
}
$menus->save();
}
}

if (!function_exists('function338')) {
function update338() {
litepublisher::$classes->add('tini2array', 'ini2array.class.php');

$posts = tposts::instance();
$posts->data['revision'] = 0;
$posts->save();

if (dbversion) {
$man = tdbmanager ::instance();
$man->alter($posts->table, 'add revision int unsigned NOT NULL default 0');
} else {
foreach ($posts->items as $id => $item) {
$post = tpost::instance($id);
$post->data['revision'] = 0;
$post->save();
$post->free();
}
}

}
}

if (!function_exists('function342')) {
function update342() {
$sub = tadminsubscribers::instance();
$sub->install();
}
}

if (!function_exists('function348')) {
function update348() {
$files = tfiles::instance();
  $posts= tposts::instance();
  $posts->lock();
  $posts->added = $files->postedited;
  $posts->edited = $files->postedited;
  $posts->deleted = $files->itemsposts->deletepost;
  $posts->unlock();
}
}

if (!function_exists('function349')) {
function update349() {
      $parser = tthemeparser::instance();
        $parser->reparse();
}
}

if (!function_exists('function352')) {
function update352() {
if (!isset(litepublisher::$classes->items['tticket'])) return;
  $adminmenus = tadminmenus::instance();  
  $adminmenus->lock();
  $parent = $adminmenus->class2id('tadmintickets');
  $idmenu = $adminmenus->createitem($parent, 'opened', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['ticket']['opened'];

  $idmenu = $adminmenus->createitem($parent, 'fixed', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['ticket']['fixed'];
  $adminmenus->unlock();


  }
}

if (!function_exists('function354')) {
function update354() {
tfiler::delete(litepublisher::$paths->data . 'themes' . DIRECTORY_SEPARATOR, false, false);
    litepublisher::$urlmap->clearcache();

litepublisher::$options->filtercommentstatus = true;
  }
}

if (!function_exists('function356')) {
function update356() {
ttheme::clearcache();

litepublisher::$classes->add('wordpress', 'wordpress.functions.php');

if (isset(litepublisher::$classes->items['tsourcefiles'])) {
litepublisher::$db->table = 'sourcefiles';
litepublisher::$db->update("content = ''", "filename like '%.php'");
}
  }
}

if (!function_exists('function357')) {
function update357() {
$template = ttemplate::instance();
$data = new titems();
      $data->data = $template->data['sitebars'];
unset($template->data['sitebars']);
$eventnames = array('beforecontent', 'aftercontent', 'onhead', 'onadminhead', 'onbody', 'themechanged', 'onadminhover', 'ondemand');
foreach ($template->events as $name => $event) {
if (!in_array($name,$eventnames)) unset($template->data['events'][$name]);
}

$s = 'echo round(($usec1 + $sec1) - ($usec2 + $sec2), 2), \'Sec \';';
if ($i = strpos($template->footer, $s)) {
$template->footer = substr_replace($template->footer, 'echo round(microtime(true) - litepublisher::$microtime, 2), \'Sec \';', $i, strlen($s));
$s = 'list($usec1, $sec1) = explode(\' \', microtime());';
if ($i = strpos($template->footer, $s))  $template->footer = substr_replace($template->footer, '', $i, strlen($s));
$s = 'list($usec2, $sec2) = explode(\' \', litepublisher::$microtime);';
if ($i = strpos($template->footer, $s)) $template->footer = substr_replace($template->footer, '', $i, strlen($s));
}

$template->save();

$widgets = twidgets::instance();
$widgets->lock();
$std = tstdwidgets::instance();

$classes = litepublisher::$classes;
$classes->lock();

$plugins = tplugins::instance();
$plugins->lock();
$plugins->delete('adminlinks');
$plugins->delete('oldestposts');
$plugins->delete('keywords');
$plugins->delete('sameposts');
$plugins->delete('sape');
$plugins->save();

$classes->add('twidget', 'widgets.class.php');
$classes->add('torderwidget', 'widgets.class.php');
$classes->add('tclasswidget', 'widgets.class.php');
$classes->add('twidgetscache', 'widgets.class.php');
$classes->add('tsitebars', 'widgets.class.php');
$classes->add('tcommentswidget', 'widget.comments.class.php');
$classes->add('tmetawidget', 'widget.meta.class.php');
$classes->add('tcommontagswidget', 'tags.common.class.php');
$classes->add('tcategorieswidget', 'tags.categories.class.php');
$classes->add('ttagswidget', 'tags.cloud.class.php');
$classes->add('tarchiveswidget', 'archives.class.php');
$classes->add('tpostswidget', 'posts.class.php');

//admin
$classes->add('tadminwidget', 'admin.widget.class.php');
$classes->add('tadminmaxcount', 'admin.widget.class.php');
$classes->add('tadminshowcount', 'admin.widget.class.php');
$classes->add('tadminorderwidget', 'admin.widget.class.php');
$classes->add('tadminfriendswidget', 'admin.widget.class.php');
$classes->add('tadmintagswidget', 'admin.widget.class.php');
$classes->add('tadmincustomwidget', 'admin.widget.class.php');
$classes->add('tadminlinkswidget', 'admin.widget.class.php');
$classes->add('tadminmetawidget', 'admin.widget.class.php');
$classes->add('tadminhomewidgets', 'admin.widget.class.php');
//change filename
$classes->items['tlinkswidget'][0] = 'widget.links.class.php';
$classes->items['tcustomwidget'][0] = 'widget.custom.class.php';
$classes->items['tcommentswidget'][0] = 'widget.comments.class.php';

$custom = tcustomwidget::instance();
$customitems = array();
$items = array();
foreach ($custom->items as $idold => $item) {
$item['template'] = $item['templ'] ? 'widget' : '';
unset($item['templ']);
$id = $widgets->addext($custom, $item['title'], $item['template']);
$items[$id] = $item;
$customitems[$idold] = $id;
}
$custom->items = $items;
$custom->save();

$widget = tlinkswidget::instance();
foreach ($widget->items as $id => $item) {
$item['anchor'] = $item['text'];
unset($item['text']);
$widget->items[$id] = $item;
}
$widget->save();

$meta = tmetawidget::instance();
$meta->data['meta'] = $std->data['meta'];
$meta->save();

$widget = tcategorieswidget::instance();
$widget->setparams($widget->owner->sortname, $widget->owner->maxcount, $widget->owner->showcount);
unset($widget->owner->data['sortname']);
unset($widget->owner->data['maxcount']);
unset($widget->owner->data['showcount']);
$widget->owner->save();

$widget = ttagswidget::instance();
$widget->setparams($widget->owner->sortname, $widget->owner->maxcount, $widget->owner->showcount);
unset($widget->owner->data['sortname']);
unset($widget->owner->data['maxcount']);
unset($widget->owner->data['showcount']);
$widget->owner->save();

$widget = tarchiveswidget::instance();
$arch= tarchives::instance();
$widget->showcount = $arch->showcount;
unset($arch->data['showcount']);
$arch->save();

$widget = tpostswidget::instance();
$posts = tposts::instance();
$widget->maxcount = $posts->recentcount;
unset($posts->data['recentcount']);
$posts->save();

$widget = tcommentswidget::instance();
    $manager = tcommentmanager::instance();
if ($widget->maxcount != $manager->recentcount) {
$widget->maxcount = $manager->recentcount;
$widget->save();
}
unset($manager->data['recentcount']);
$manager->save();

$foaf = tfoaf::instance();
litepublisher::$urlmap->lock();
litepublisher::$urlmap->delete('/foaflink.htm');
$classes->add('tfriendswidget', 'widget.friends.class.php');
litepublisher::$urlmap->unlock();

    unset($foaf->data['maxcount']);
    unset($foaf->data['redir']);
    unset($foaf->data['redirlink']);
$foaf->save();

$sitebars = tsitebars::instance();
foreach ($data->data['items'] as $i => $sitebar) {
$j = 0;
foreach ($sitebar as $idold => $item) {
$class = $item['class'];
if ($class == 'tstdwidgets') {
$class = $std->getname($idold);
}
switch ($class) {
case 'tcommentswidget':
case 'comments':
$widget = tcommentswidget::instance();
$id = $widgets->add($widget);
$ajax = $std->items['comments']['ajax'];
break;

case 'tcustomwidget':
$id = $customitems[$idold];
$ajax = false;
break;

case 'tlinkswidget':
case 'links':
$widget = tlinkswidget::instance();
$id = $widgets->add($widget);
$ajax = $std->items['links']['ajax'];
break;

case 'meta':
$id = $widgets->add($meta);
$ajax = $std->items['meta']['ajax'];
break;

case 'tcategories':
case 'categories':
$id = $widgets->add(tcategorieswidget::instance());
$ajax = $std->items['categories']['ajax'];
break;

case 'ttags':
case 'tags':
$id = $widgets->add(ttagswidget::instance());
$ajax = $std->items['tags']['ajax'];
break;

case 'tarchives':
case 'archives':
$id = $widgets->add(tarchiveswidget ::instance());
$ajax = $std->items['archives']['ajax'];
break;

case 'tposts':
case 'posts':
$id = $widgets->add(tpostswidget ::instance());
$ajax = $std->items['posts']['ajax'];
break;

case 'friends':
case 'tfoaf':
$id = $widgets->add(tfriendswidget::instance());
$ajax = $std->items['friends']['ajax'];
break;
}

$sitebars->insert($id, $ajax, $i, $j++);
}
}

  $xmlrpc = TXMLRPC::instance();
$xmlrpc->lock();
  $xmlrpc->deleteclass('tstdwidgets');
  $xmlrpc->add('litepublisher.getwidget', 'xmlrpcgetwidget', get_class($widgets));
$xmlrpc->unlock();

$classes->delete('tstdwidgets');
$classes->add('tmenuwidget', 'widget.menu.class.php');
$classes->unlock();
$widgets->unlock();

$admin = tadminmenus::instance();
$admin->lock();
$idwidgets = url2id($admin, '/admin/widgets/');
$admin->deleteurl('/admin/widgets/std/');
$admin->deleteurl('/admin/widgets/stdoptions/');
$admin->deleteurl('/admin/widgets/links/');
$admin->deleteurl('/admin/widgets/custom/');
$admin->deleteurl('/admin/widgets/meta/');
$admin->deleteurl('/admin/widgets/homepagewidgets/');

   //$admin->createitem($idwidgets, 'classes', 'admin', 'tadminwidgets');
   $admin->createitem($idwidgets, 'home', 'admin', 'tadminwidgets');
    $admin->createitem($idwidgets, 'addcustom', 'admin', 'tadminwidgets');
$admin->unlock();

ttheme::clearcache();

//delete files
$lib = litepublisher::$paths->lib;
$install = $lib . 'install' . DIRECTORY_SEPARATOR;

//@unlink($lib . 'widgets.standarts.class.php');
@unlink($install . 'widgets.standarts.class.install.php');

@unlink($lib . 'widgets.links.class.php');
@unlink($install . 'widgets.links.class.install.php');

@unlink($lib . 'widgets.custom.class.php');
@unlink($install . 'widget.custom.class.install.php');

@unlink($lib . 'widgets.comments.class.php');

litepublisher::$options->version = '3.57';
litepublisher::$urlmap->redir301('/admin/service/' . litepublisher::$options->q . 'update=1');
}

if (!function_exists('array_insert')) {
function array_delete_value(array &$a, $value) {
  $i = array_search($value, $a);
  if ($i !== false)         array_splice($a, $i, 1);
}

function array_insert(array &$a, $item, $index) {
  array_splice($a, $index, 0, array($item));
}

}

  function url2id(tmenus $menus, $url) {
    foreach ($menus->items as $id => $item) {
      if ($url == $item['url']) return $id;
    }
    return false;
  }
}

if (!function_exists('function358')) {
function update358() {
litepublisher::$options->dateformat = tthemeparser::strftimetodate(litepublisher::$options->dateformat);
ttheme::clearcache();
  }
}

if (!function_exists('function359')) {
function update359() {
$notfound = tnotfound404::instance();
if (!isset($notfound->data['notify'])) {
$notfound->data['notify'] = true;
$notfound->save();
}

if (dbversion) {
litepublisher::$classes->add('trssholdcomments', 'rss.holdcomments.class.db.php');
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$idcomments = $admin->url2id('/admin/comments/');
$admin->createitem($idcomments, 'holdrss', 'moderator', 'tadminmoderator');
}

$redir = tredirector::instance();
$redir->lock();
$redir->add('/rss/', '/rss.xml');
$redir->add('/contact.php', '/kontakty.htm');
$redir->add('/feed/', '/rss.xml');
$redir->unlock();
  }
}

if (!function_exists('function360')) {
function update360() {
if (dbversion) {
$admin = tadminmenus::instance();
if ($id = $admin->url2id('/admin/comments/holdrss/')) {
tlocal::loadlang('admin');
$admin->items[$id]['title'] = tlocal::$data['names']['holdrss'];
$admin->save();
ttheme::clearcache();
}
}

$redir = tredirector::instance();
$redir->lock();
$redir->add('/rss', '/rss.xml');
$redir->unlock();
  }
}

if (!function_exists('function362')) {
function update362() {
$home = thomepage::instance();
if (!isset($home->data['idmenu'])) {
      $menus = tmenus::instance();
      $home->data['id'] =$menus->class2id(get_class($home));
$home->save();
}

if (dbversion) {
$admin = tadminmenus::instance();
if ($id = $admin->url2id('/admin/comments/holdrss/')) {
tlocal::loadlang('admin');
$admin->items[$id]['title'] = tlocal::$data['names']['holdrss'];
$admin->save();
ttheme::clearcache();
}
}

$redir = tredirector::instance();
$redir->lock();
$redir->add('/rss', '/rss.xml');
$redir->unlock();
  }
}

if (!function_exists('function363')) {
function update363() {
$redir = tredirector::instance();
$redir->lock();
$redir->add('/$post.link', '/');
$redir->unlock();

$widget = ttagswidget::instance();
$widgets = twidgets::instance();
if (!$widgets->find($widget)) {
$widgets->add($widget);
}
litepublisher::$urlmap->lock();
    $cache = twidgetscache::instance();
if (litepublisher::$urlmap->eventexists('CacheExpired')) {
litepublisher::$urlmap->CacheExpired= $cache->onclearcache;
$events = &litepublisher::$urlmap->data['events'];
if (isset($events['CacheExpired'])) {
$events['onclearcache'] = $events['CacheExpired'];
unset($events['CacheExpired']);
}
} else {
litepublisher::$urlmap->onclearcache = $cache->onclearcache;
}
litepublisher::$urlmap->unlock();

$widget = tcommentswidget::instance();
  litepublisher::$classes->commentmanager->changed = $widget->changed;
ttheme::clearcache();
  }
}

if (!function_exists('function364')) {
function update364() {
      //litepublisher::$options->setcookie(litepublisher::$options->cookie);
    litepublisher::$options->data['cookie'] = md5((string) litepublisher::$options->cookie . litepublisher::$secret);
    litepublisher::$options->save();

$users = tusers::instance();
if ($users->count > 0) {
if (dbversion) {
$users->loadall();
$db = $users->db;
foreach ($users->items as $id => $item) {
$db->setvalue($id, 'cookie',  md5($item['cookie'] . litepublisher::$secret));
}
} else {
foreach ($users->items as $id => $item) {
$users->items[$id]['cookie'] = md5($item['cookie'], litepublisher::$secret);
}
$users->save();
}
}

}
}

if (!function_exists('function365')) {
function update365() {
tcustomwidget::instance()->install();
}
}

if (!function_exists('function366')) {
function update366() {
litepublisher::$options->checkduplicate = true;
litepublisher::$options->defaultsubscribe = true;
}
}

if (!function_exists('function368')) {
function update368() {
if (isset(litepublisher::$classes->items['Tdomrss'])) unset(litepublisher::$classes->items['Tdomrss']);
litepublisher::$classes->items['tdomrss'] = array('domrss.class.php', '');
litepublisher::$classes->items['tnode'] = array('domrss.class.php', '');
litepublisher::$classes->items['tclasses'] = array('classes.class.php', '');
litepublisher::$classes->save();
@unlink(litepublisher::$paths->lib . 'classes.php');

$template = ttemplate::instance();
$template->data['adminjavascripts'] = array();
    $template->data['stdjavascripts'] = array(
'hovermenu' => '/js/litepublisher/hovermenu.min.js',
'comments' => '/js/litepublisher/comments.min.js',
'moderate' => '/js/litepublisher/moderate.min.js'
);

$template->save();

tlocal::loadlang('admin');
$admin = tadminmenus::instance();
//$admin->deleteurl('/admin/themes/javascripts/');
$idthemes = $admin->url2id('/admin/themes/');
$admin->createitem($idthemes, 'javascripts', 'admin', 'tadminthemes');

    tfiler::deletemask(litepublisher::$paths->languages . '*.php');

    $dir =litepublisher::$paths->data . 'languages';
mkdir($dir, 0777);
chmod($dir, 0777);
}
}

if (!function_exists('function371')) {
function update371() {
$redir = tredirector::instance();
$redir->lock();
  $redir->add('/wp-rss.php', '/rss.xml');
  $redir->add('/wp-rss2.php', '/rss.xml');
$redir->add('/wp-login.php', '/admin/login/');

$redir->unlock();
}
}

if (!function_exists('function373')) {
function update373() {
unset(litepublisher::$classes->items['tnode']);
litepublisher::$classes->items['toauth']  = array('oauth.class.php', '');
litepublisher::$classes->save();

$menus = tmenus::instance();
if ($idmenu = $menus->url2id('/')) {
$menus->lock();
$menus->remove($idmenu);
while ($idmenu = $menus->url2id('/')) {
$menus->remove($idmenu);
}
$menus->unlock();
}

$home = thomepage::instance();
if (!litepublisher::$urlmap->findurl('/')) {
  $home->idurl = litepublisher::$urlmap->add('/', get_class($home), null);
}
unset($home->data['idmenu']);
$home->data['image'] = '';
  $home->save();

}
}

if (!function_exists('function375')) {
function update375() {
$widgets = twidgets::instance();
foreach ($widgets->classes as $name => $items) {
foreach ($items as $i => $item) {
$id = $item['id'];
if (!isset($widgets->items[$id])) array_delete($this->classes[$name], $i);
}
if (count($widgets->classes[$name]) == 0) unset($widgets->classes[$name]);
}
$widgets->save();

$template = ttemplate::instance();
$template->data['heads'] = array();
$template->data['adminheads'] = array();
$template->save();
}
}

if (!function_exists('function376')) {
function update376() {
$classes = litepublisher::$classes;
$classes->lock();
$classes->items['topenid'][1] = 'openid-provider';
$classes->unlock();

    $about = tplugins::getabout('openid-provider');
    extract($about, EXTR_SKIP);
$plugins = tplugins::instance();
$plugins->items['openid-provider'] =  array(
    'id' => ++$plugins->autoid,
    'class' => $classname,
    'file' => $filename,
    'adminclass' => $adminclassname,
    'adminfile' => $adminfilename
    );
$plugins->save();

$admin = tadminmenus::instance();
$admin->deleteurl('/admin/options/openid/');

@unlink(litepublisher::$paths->libinclude . 'bigmath.php');
@unlink(litepublisher::$paths->lib . 'openid.class.php');
}
}

if (!function_exists('function377')) {
function update377() {
$admin = tadminmenus::instance();
$admin->remove($admin->url2id('/admin/'));

if (dbversion) {
$man = tdbmanager ::instance();
$man->alter('comusers', "alter ip set default ''");
$man->alter('rawcomments', "add `ip` varchar(15) NOT NULL default ''");
$man->alter('comments', 'drop ip');
}
}
}

if (!function_exists('function379')) {
function update379() {
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$idthemes = $admin->url2id('/admin/themes/');
if ($idjava = $admin->url2id('/admin/themes/javascripts/')) {
$admin->delete($idjava);
$urlmap = turlmap::instance();
if (dbversion) {
$urlmap->db->delete("url = '/admin/themes/javascripts/'");
} else {
unset($urlmap->items['/admin/themes/javascripts/']);
$urlmap->save();
}
}
$admin->createitem($idthemes, 'javascripts', 'admin', 'tadminthemes');

}
}

if (!function_exists('function380')) {
function update380() {
if (dbversion) {
$man = tdbmanager ::instance();
$man->alter('files', "alter `samplingrate` set default 0");
$man->alter('files', "modify `title` text  NOT NULL");
$man->alter('files', "modify `description` text  NOT NULL");
$man->alter('files', "modify `keywords` text  NOT NULL");

$man->alter('users', "modify `password` varchar(32) NOT NULL");
$man->alter('users', "alter `ip` set default ''");
}
}
}

if (!function_exists('function381')) {
function update381() {
tlocal::loadlang('admin');
if (!function_exists('update380')) tupdater::instance()->run(3.80);
$classes = litepublisher::$classes;
$classes->lock();
$classes->items['tfoaf'][1] = 'foaf';
$classes->items['tfoafutil'][1] = 'foaf';
$classes->items['tprofile'][1] = 'foaf';
$classes->items['tfriendswidget'][1] = 'foaf';
$classes->items['tadminfoaf'][1] = 'foaf';
$classes->items['tadminfriendswidget'][1] = 'foaf';
$classes->unlock();

    $about = tplugins::getabout('foaf');
    extract($about, EXTR_SKIP);
$plugins = tplugins::instance();
$plugins->items['foaf'] =  array(
    'id' => ++$plugins->autoid,
    'class' => $classname,
    'file' => $filename,
    'adminclass' => $adminclassname,
    'adminfile' => $adminfilename
    );
$plugins->save();

$template = ttemplate::instance();
$template->heads['foaf'] = '	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$options.url/foaf.xml" />';
$template->save();

        $dir = litepublisher::$paths->plugins . 'foaf' . DIRECTORY_SEPARATOR  . 'resource' . DIRECTORY_SEPARATOR;
if (!isset(tlocal::$data['foaf'])) {
    if (file_exists($dir . litepublisher::$options->language . '.ini')) {
      tlocal::loadini($dir . litepublisher::$options->language . '.ini');
    } else {
      tlocal::loadini($dir . 'en.ini');
    }
    }
$lang = tlocal::instance('foaf');

$meta = tmetawidget::instance();
$meta->lock();
$meta->add('rss', '/rss.xml', tlocal::$data['default']['rss']);
$meta->add('comments', '/comments.xml', tlocal::$data['default']['rsscomments']);
$meta->add('media', '/rss/multimedia.xml', tlocal::$data['default']['rssmedia']);
$meta->add('sitemap', '/sitemap.htm', tlocal::$data['default']['sitemap']);
$meta->add('foaf', '/foaf.xml', $about['name']);
$meta->add('profile', '/profile.htm', $lang->profile);
unset($meta->data['meta']);
$meta->unlock();

ttheme::clearcache();
$lib = litepublisher::$paths->lib;
@unlink($lib . 'foaf.class.php');
@unlink($lib . 'foaf.util.class.php');
@unlink($lib . 'profile.class.php');
@unlink($lib . 'widget.friends.class.php');
@unlink($lib . 'admin.foaf.class.php');

$dir = $lib . 'install' . DIRECTORY_SEPARATOR;
@unlink($dir . 'foaf.sql');
@unlink($dir . 'foaf.class.install.php');
@unlink($dir . 'foaf.util.install.php');
@unlink($dir . 'profile.class.install.php');
@unlink($dir . 'widget.friends.class.install.php');

}
}

if (!function_exists('function382')) {
function update382() {
$posts = tposts::instance();
$posts->addrevision();
ttheme::clearcache();

litepublisher::$options->ob_cache = false;
litepublisher::$options->compress = false;
}
}

if (!function_exists('function384')) {
function update384() {
if (dbversion) {
$data = &litepublisher::$options->data;
$data['storage'] = array();
$storage = &$data['storage'];
foreach (array('posts', 'comusers', 'foaf', 'postclasses', 'externallinks', 'codedoc', 'wikiwords',
'filesitemsposts', 'pingbacks', 'users', 'urlmap', 'cron', 'comments', 'tags', 'categories') as $name) {
if (isset($data[$name])) {
$storage[$name] = $data[$name];
unset($data[$name]);
}
}
litepublisher::$options->save();
litepublisher::$options->savemodified();
}

if (class_exists('ttickets')) {
$tickets = ttickets::instance();
  $groups = tusergroups  ::instance();
$groups->onhasright = $ttickets->hasright;
}
}
}

if (!function_exists('function386')) {
function update386() {
tredirector::instance()->install();

}
}

if (!function_exists('function387')) {
function update387() {
$redir = tredirector::instance();
$redir->add('/rss/', '/rss.xml');$redir->save();
$redir->add('/profile/', '/profile.htm');
$redir->add('/profile.htm', '/');
$redir->add('/foaf/', '/foaf.xml');
$redir->save();
}
}

if (!function_exists('function388')) {
function update388() {
$redir = tredirector::instance();
$redir->lock();
$redir->add('/themes/default/print.css', '/themes/default/css/style.css');
$redir->add('/themes/default/style.css', '/themes/default/css/style.css');
$redir->unlock();
}
}

if (!function_exists('function389')) {
function update389() {
$redir = tredirector::instance();
$redir->lock();
$redir->add('/profile/', '/profile.htm');
$redir->add('/sitemap/', '/sitemap.htm');
$redir->unlock();
}
}

if (!function_exists('function391')) {
function update391() {
if (!dbversion) return;
$backup = array (
  'urlmap' => 
  array (
    'events' => 
    array (
      'onclearcache' => 
      array (
        0 => 
        array (
          'class' => 'twidgetscache',
          'func' => 'onclearcache',
        ),
      ),
    ),
    'coclasses' => 
    array (
    ),
  ),
  'posts' => 
  array (
    'events' => 
    array (
      'changed' => 
      array (
        0 => 
        array (
          'class' => 'tarchives',
          'func' => 'postschanged',
        ),
      ),
      'deleted' => 
      array (
        0 => 
        array (
          'class' => 'tcommentmanager',
          'func' => 'postdeleted',
        ),
        1 => 
        array (
          'class' => 'tsubscribers',
          'func' => 'deletepost',
        ),
        2 => 
        array (
          'class' => 'tpingbacks',
          'func' => 'postdeleted',
        ),
        3 => 
        array (
          'class' => 'tcategories',
          'func' => 'postdeleted',
        ),
        4 => 
        array (
          'class' => 'ttags',
          'func' => 'postdeleted',
        ),
        5 => 
        array (
          'class' => 'tfileitems',
          'func' => 'deletepost',
        ),
      ),
      'added' => 
      array (
        0 => 
        array (
          'class' => 'tcategories',
          'func' => 'postedited',
        ),
        1 => 
        array (
          'class' => 'ttags',
          'func' => 'postedited',
        ),
        2 => 
        array (
          'class' => 'tfiles',
          'func' => 'postedited',
        ),
      ),
      'edited' => 
      array (
        0 => 
        array (
          'class' => 'tcategories',
          'func' => 'postedited',
        ),
        1 => 
        array (
          'class' => 'ttags',
          'func' => 'postedited',
        ),
        2 => 
        array (
          'class' => 'tfiles',
          'func' => 'postedited',
        ),
      ),
      'singlecron' => 
      array (
        0 => 
        array (
          'class' => 'tpinger',
          'func' => 'pingpost',
        ),
      ),
    ),
    'coclasses' => 
    array (
    ),
    'archivescount' => '1',
    'revision' => 0,
    'itemcoclasses' => 
    array (
    ),
  ),
  'comments' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  'comusers' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  'pingbacks' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  '' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
    'lite' => false,
  ),
  'categories' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
    'lite' => false,
    'defaultid' => 0,
  ),
  'tags' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
    'lite' => false,
  ),
  'filesitemsposts' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
  'files' => 
  array (
    'events' => 
    array (
      'deleted' => 
      array (
        0 => 
        array (
          'class' => 'ticons',
          'func' => 'filedeleted',
        ),
        1 => 
        array (
          'class' => 'tdownloadcounter',
          'func' => 'delete',
        ),
      ),
      'changed' => 
      array (
        0 => 
        array (
          'class' => 'trssMultimedia',
          'func' => 'fileschanged',
        ),
      ),
    ),
    'coclasses' => 
    array (
    ),
  ),
  'users' => 
  array (
    'events' => 
    array (
    ),
    'coclasses' => 
    array (
    ),
  ),
);

$storage = &litepublisher::$options->data['storage'];
foreach ($backup as $name => $data) {
if (isset($storage[$name])) {
if (count($storage[$name]['events']) == 0) $storage[$name]['events'] = $data['events'];
} else {
$storage[$name] = $data;
}
}

  $posts = tposts::instance();
$posts->lock();

if (isset(litepublisher::$classes->items['ttickets'])) {
$tickets = ttickets::instance();
  $posts->deleted = $tickets->postdeleted;
$c = tpostclasses::instance();
  $posts->added = $c->postadded;
  $posts->deleted = $c->postdeleted;

sync_tickets($tickets);
}

if (isset(litepublisher::$classes->items['twikiwords'])) {
$wiki = twikiwords::instance();
  $posts->added = $wiki->postadded;
  $posts->deleted = $wiki->postdeleted;
}

if (isset(litepublisher::$classes->items['tsameposts'])) {
$same = tsameposts::instance();
  $posts->changed = $same->postschanged;
}

if (isset(litepublisher::$classes->items['tpostcontentplugin'])) {
$c = tpostcontentplugin::instance();
  $posts->beforecontent = $c->beforecontent;
  $posts->aftercontent = $c->aftercontent;
}

if (isset(litepublisher::$classes->items['tcodedocplugin'])) {
$doc = tcodedocplugin::instance();
  $posts->deleted = $doc->postdeleted;
  $posts->added = $doc->postadded;
}

if (isset(litepublisher::$classes->items['tlivejournal'])) {
$lj = tlivejournal::instance();
$lj->install();
};

$posts->unlock();
litepublisher::$options->save();
litepublisher::$options->savemodified();

}

function sync_tickets($tickets) {
    $db = $tickets->getdb($tickets->ticketstable);
    $items = $db->idselect("id not in (select id from $db->postclasses)");
$c = tpostclasses::instance();
    $idclass = $c->addclass('tticket');
foreach ($items as $id) {
    $c->add($id, $idclass);
}
}
}

if (!function_exists('function393')) {
function update393() {
litepublisher::$classes->add('tforbidden', 'notfound.class.php');
if (dbversion) {
    $comusers = tcomusers::instance();
$comusers->db->update("name = 'Admin'", "name = ''");
}
}
}

if (!function_exists('function394')) {
function update394() {
$parser = tmediaparser::instance();
if (!isset($parser->data['enablepreview'])) {
$parser->data['enablepreview'] = true;
$parser->save();
}
}
}

if (!function_exists('function397')) {
function update397() {
$filter = tcontentfilter::instance();
if (!isset($filter->data['autolinks'])) {
$filter->data['autolinks'] = true;
$filter->data['commentautolinks'] = true;
$filter->save();
}

if (isset(litepublisher::$classes->items['texternallinks'])) {
$plugin = texternallinks::instance();
  $filter = tcontentfilter::instance();
  $filter->onaftercomment = $plugin->filter;
}
}
}

if (!function_exists('function398')) {
function update398() {
if (isset(litepublisher::$classes->items['tmarkdownplugin'])) {
$plugin = tmarkdownplugin::instance();
  $filter = tcontentfilter::instance();
  $filter->oncomment= $plugin->filter;
}
}
}

