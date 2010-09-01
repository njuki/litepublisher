<?php
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
unset($urlmap->items['/admin/themes/javascripts/');
$urlmap->save();
}
}
$admin->createitem($idthemes, 'javascripts', 'admin', 'tadminthemes');

}
?>
