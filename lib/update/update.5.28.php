<?php

function update528() {
if (isset(tstorage::$data) {
unset(tstorage::$data['comusers']);
unset(tstorage::$data['postclasses']);

if (!litepublisher::$classes->exists('tfoaf')) unset(tstorage::$data['foaf']);
if (!litepublisher::$classes->exists('texternallinks')) unset(tstorage::$data['externallinks']);
if (isset(tstorage::$data['posts\index']) && !isset(tstorage::$data['posts/index'])) {
tstorage::$data['posts/index'] = tstorage::$data['posts\index'];
unset(tstorage::$data['posts\index']);
}

litepublisher::$options->save();
}

tadmingroups ::i()->update_groupnames();

}