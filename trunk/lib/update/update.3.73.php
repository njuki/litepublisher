<?php
function update373() {
unset(litepublisher::$classes->items['tnode']);
litepublisher::$classes->items['toauth']  = array('oauth.class.php', '');
litepublisher::$classes->save();
}
?>