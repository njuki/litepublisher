<?php
function update400() {
$classes = litepublisher::$classes;
$classes->lock();
unset($classes->items['imenu']);
$classes->add('tthemeparserver3', 'theme.parser.ver3.class.php');
$classes->add('twordpressthemeparser', 'theme.parser.wordpress.class.php');
$classes->add('tsite', 'site.class.php'();
$classes->add('tview', 'views.class.php');
$classes->add('tviews',  'views.class.php');

$classes->unlock();

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
    'view' => 1,

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
$menu->data['view'] = 1;
unset($menu->data['tmlfile']);
unset($menu->data['theme']);
$menu->content = $menu->data['content'];
$menu->save();
}
$menus->unlock();

//fix for something
$admin = tadminmenus::instance();
$admin->data['idhome'] = 0;
$admin->data['home'] = false;
$admin->save();

//contact form
  $html = THtmlResource::instance();
if (!isset($html->ini['installation'])) $html->loadini(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.ini');
  $html->section = 'contactform';
    tlocal::loadinstall();
  $lang = tlocal::instance('contactform');

$contact = tcontactform();
$contact->data['subject'] = $lang->subject;
$contact->data['errmesg'] =$html->errmesg();
$contact->data['success'] = $html->success();
$contact->save();

tstorage::savemodified();
}
?>
