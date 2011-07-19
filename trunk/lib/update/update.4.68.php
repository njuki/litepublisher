<?php

function update468() {
if (litepublisher::$classes->exists('texternallinks')) {
$url = '/externallink.htm';
if (dbversion) {
$item = litepublisher::$urlmap->db->finditem('url = ' . dbquote($url));
litepublisher::$urlmap->db->setvalue($item['id'], 'type', 'get');
} else {
litepublisher::$urlmap->items[$url]['type'] = 'get';
litepublisher::$urlmap->save();
}
}

if (litepublisher::$classes->exists('tdownloaditemcounter')) {
$url = '/downloaditem.htm';
if (dbversion) {
$item = litepublisher::$urlmap->db->finditem('url = ' . dbquote($url));
litepublisher::$urlmap->db->setvalue($item['id'], 'type', 'get');
} else {
litepublisher::$urlmap->items[$url]['type'] = 'get';
litepublisher::$urlmap->save();
}
}

if (dbversion) {
$man = tdbmanager::instance();
$man->alter('posts', "add   `keywords` text NOT NULL after description");
$man->alter('categories', "add `customorder` int(10) unsigned NOT NULL default '0' after idurl");
$man->alter('tags', "add `customorder` int(10) unsigned NOT NULL default '0' after idurl");
//$man->alter('pingbacks', "modify `title` text NOT NULL");
} else {
add_customorder(tcategories::instance());
add_customorder(ttags::instance());
}

if (litepublisher::$classes->exists('tbackup2dropbox')) {
$dropbox = tbackup2dropbox::instance();
    $dropbox->data['onlychanged'] = false;
    $dropbox->data['posts'] = 0;
    $dropbox->data['comments'] = 0;
$dropbox->save();

}
}


function add_customorder($tags) {
foreach ($tags->items as $id => $item) {
$tags->items[$id]['customorder'] = 0;
}
$tags->save();
}