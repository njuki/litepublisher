<?php
function update393() {
litepublisher::$classes->add('tforbidden', 'notfound.class.php');
if (dbversion) {
    $comusers = tcomusers::instance();
$comusers->db->update("name = 'Admin'", "name = ''");
}
}
?>