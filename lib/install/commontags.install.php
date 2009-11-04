<?php

function TCommonTagsInstall(TCommonTags $self) {
  if ('TCommonTags' == get_class($self)) return;
  $posts= tposts::instance();
  $posts->lock();
  $posts->added = $self->postedited;
  $posts->edited = $self->postedited;
  $Posts->deleted = $self->postdeleted;
  $posts->unlock();
  
  $urlmap = turlmap::instance();
  $urlmap->add("/$self->PermalinkIndex/", get_class($self), 0);
if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'tags.sql'));
    $manager->CreateTable($self->itemstable, file_get_contents($dir .'tagsitems.sql'));
    $manager->CreateTable($self->table, file_get_contents($dir .'tagscontent.sql'));
} else {
}

}
}

function TCommonTagsUninstall(&$self) {
tposts::unsub($self);
    tulmap::unsub($self);
  
$widgets = twidgets::instance();
$widgets->deleteclass(get_class($self));
}

?>