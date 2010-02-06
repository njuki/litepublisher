<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function THomepageInvertInstall($self) {
 $urlmap = turlmap::instance();
if (dbversion) {
$item = $urlmap->db->finditem("url = '/'");
$urlmap->setvalue($item['id'], 'class', get_class($self));
} else {
 $urlmap->items['/']['class'] = get_class($self);
$urlmap->save();
}
}

function THomepageInvertUninstall($self) {
$parent = get_parent_class($self);
 $urlmap = turlmap::instance();
if (dbversion) {
$item = $urlmap->db->finditem("url = '/'");
$urlmap->setvalue($item['id'], 'class', $parent);
} else {
 $urlmap->items['/']['class'] = $parent;
$urlmap->save();
}
}

?>