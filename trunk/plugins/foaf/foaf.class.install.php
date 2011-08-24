<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfoafInstall($self) {
  //turlmap::unsub($self);
  $dir = dirname(__file__) .DIRECTORY_SEPARATOR  . 'resource' . DIRECTORY_SEPARATOR;
    tlocal::loadsection('', 'foaf', $dir);
  $lang = tlocal::instance('foaf');
  
  if ($self->dbversion) {
    $manager = tdbmanager ::instance();
    $manager->createtable($self->table, file_get_contents($dir .'foaf.sql'));
  }
  
  $actions = TXMLRPCAction ::instance();
  $actions->lock();
  $actions->add('invatefriend', get_class($self), 'Invate');
  $actions->add('rejectfriend', get_class($self), 'Reject');
  $actions->add('acceptfriend', get_class($self), 'Accept');
  $actions->unlock();
  
  $urlmap = litepublisher::$urlmap;
  $urlmap->lock();
  $urlmap->add('/foaf.xml', get_class($self), null);
  
  $name = tplugins::getname(__file__);
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->add('tadminfoaf', 'admin.foaf.class.php', $name);
  $classes->add('tfoafutil', 'foaf.util.class.php', $name);
  $classes->add('tprofile', 'profile.class.php', $name);
  $classes->add('tfriendswidget', 'widget.friends.class.php', $name);
  $classes->unlock();
  
  $admin = tadminmenus::instance();
  $admin->lock();
  $id = $admin->createitem(0, 'foaf', 'admin', 'tadminfoaf');
  {
    $admin->createitem($id, 'profile', 'admin', 'tadminfoaf');
    $admin->createitem($id, 'profiletemplate', 'admin', 'tadminfoaf');
  }
  $admin->unlock();
  $urlmap->unlock();
  
  $template = ttemplate::instance();
  $template->addtohead('	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />');
  $about = tplugins::getabout($name);
  $meta = tmetawidget::instance();
  $meta->lock();
  $meta->add('foaf', '/foaf.xml', $about['name']);
  $meta->add('profile', '/profile.htm', $lang->profile);
  $meta->unlock();
  ttheme::clearcache();
}

function tfoafUninstall($self) {
  $actions = TXMLRPCAction ::instance();
  $actions->deleteclass(get_class($self));
  
  $urlmap = litepublisher::$urlmap;
  $urlmap->lock();
  turlmap::unsub($self);
  
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->delete('tfoafutil');
  $classes->delete('tprofile');
  $classes->delete('tfriendswidget');
  $classes->delete('tadminfoaf');
  $classes->unlock();
  
  $admin = tadminmenus::instance();
  $admin->lock();
  $admin->deleteurl('/admin/foaf/profiletemplate/');
  $admin->deleteurl('/admin/foaf/profile/');
  $admin->deleteurl('/admin/foaf/');
  $admin->unlock();
  
  $urlmap->unlock();
  
  if ($self->dbversion) {
    $manager = tdbmanager ::instance();
    $manager->deletetable($self->table);
  }
  
  $template = ttemplate::instance();
  $template->deletefromhead('	<link rel="meta" type="application/rdf+xml" title="FOAF" href="$site.url/foaf.xml" />');
  
  $meta = tmetawidget::instance();
  $meta->lock();
  $meta->delete('foaf');
  $meta->delete('profile');
  $meta->unlock();
  
  ttheme::clearcache();
}

?>