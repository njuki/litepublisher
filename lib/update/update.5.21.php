<?php

function update521() {
litepublisher::$classes->interfaces['iadmin'] = 'interfaces.php';
litepublisher::$classes->save();
}