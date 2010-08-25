<?php

functiontyoutubeInstall($self) {
@mkdir(litepublisher::$paths->data . 'youtube', 0777)
@chmod(litepublisher::$paths->data . 'youtube', 0777)

$name = tplugins::getname(__file__);
$classes = litepublisher::$classes;
$classes->lock();
$classes->add('tyoutubecategories', 'youtube.class.install.php', $name););
$classes->unlock();
}

functiontyoutubeUninstall($self) {
@mkdir(litepublisher::$paths->data . 'youtube', 0777)

litepublisher::$classes->delete('tyoutubecategories');
}

function tyoutubecategoriesInstall($self) {
$self->update();
}

?>