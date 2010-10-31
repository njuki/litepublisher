<?php
function update400() {
$classes = litepublisher::$classes;
$classes->lock();
unset($classes->items['imenu']);
$classes->add('tthemeparserver3', 'theme.parser.ver3.class.php');
$classes->add('twordpressthemeparser', 'theme.parser.wordpress.class.php');
$classes->add('tsite', 'site.class.php'();
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
    'tmlfile' => '',
    'theme' => '',
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
$menus->unlock();

//fix for something
$admin = tadminmenus::instance();
$admin->data['idhome'] = 0;
$admin->data['home'] = false;
$admin->save();

}
?>
