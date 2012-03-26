<?php

function update524tickets() {
$types = array('bug','feature','support','task');
$cats = tcategories::i();
//delete menus
$menus = tmenus::i();
$menus->lock();
$menus->deletetree($menus->url2id('/tickets/');

$menus->unlock();

$man = tdbmanager::i();
$db = litepublisher::$db;
$table = 'tickets';
$ticketstable = $db->prefix . 'tickets';
foreach ($types as $type) {
     $idcat = $cats->db->add(array(
      'idurl' => 0,
      'customorder' => $order,
      'parent' => $parent,
      'title' => $title,
      'idview' => $idview,
      'idperm' => 0,
      'icon' => 0,
      'itemscount' => 0
      ));

      $idurl =         litepublisher::$urlmap->add($url, get_class($cats),  $idcat);
      $cats->db->setvalue($idcat, 'idurl', $idurl);

$db->table = 'tickets';
$idposts = $db->idselect("type = '$type'");

$db->table = 'posts';
$db->update("categories = '$idcat'", "$poststable.id in (" . implode(',', $idposts) . ")");


}

$man->alter($table, "drop index type"); 
$man->alter($table, "drop type"); 

  litepublisher::$classes->delete('tticketsmenu');
}