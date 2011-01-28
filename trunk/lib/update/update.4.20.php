<?php

function update420() {
$groups = tusergroups::instance();
  $groups->add('subeditor', '/admin/posts/');
$groups->save();
}
