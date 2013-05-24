<?php

function update560() {
litepublisher::$classes->add('adminitems', 'admin.items.class.php');
litepublisher::$classes->save();
}