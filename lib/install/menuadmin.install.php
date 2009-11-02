<?php

function tadminmenuInstall($self) {
$self->lock();
//posts
$posts = $self->add(0, 'posts', 'author', 'tadminposts'); 
$self->add($posts, 'posteditor', 'author', 'tposteditor'); 


$self->unlock();
}

function  TMenuUninstall(&$self) {
  //rmdir(. 'menus');
}

?>