<?php

function update451() {
$home = thomepage::instance();
    $home ->data['invertorder'] = false;
    $home ->data['includecats'] = array();
    $home ->data['excludecats'] = array();;
$home->save();
}
