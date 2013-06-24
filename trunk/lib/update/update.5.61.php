<?php

function update561() {
if (!isset(litepublisher::$urlmap->data['redirdom'])) {
litepublisher::$urlmap->data['redirdom'] = false;
litepublisher::$urlmap->save();
}

}