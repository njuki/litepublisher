<?php
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
?>