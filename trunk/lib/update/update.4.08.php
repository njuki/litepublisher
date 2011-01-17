<?php

function update408() {
litepublisher::$classes->items['tnode'] = array('domrss.class.php', '');
litepublisher::$classes->save();
}
