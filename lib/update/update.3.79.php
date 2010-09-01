<?php
function update379() {
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$idthemes = $admin->url2id('/admin/themes/');
if ($idjava = $admin->url2id('/admin/themes/javascripts/')) {
$admin->delete($idjava);
}
$admin->createitem($idthemes, 'javascripts', 'admin', 'tadminthemes');

}
?>
