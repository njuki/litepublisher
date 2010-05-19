<?php
function update352() {
if (!isset(litepublisher::$classes->items['tticket'])) return;
  $adminmenus = tadminmenus::instance();  
  $adminmenus->lock();
  $parent = $adminmenus->class2id('tadmintickets');
  $idmenu = $adminmenus->createitem($parent, 'opened', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['ticket']['opened'];

  $idmenu = $adminmenus->createitem($parent, 'fixed', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['ticket']['fixed'];
  $adminmenus->unlock();


  }
?>