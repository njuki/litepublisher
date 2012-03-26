<?php

function update524tickets() {
$types = array('bug','feature','support','task');
$tickets = ttickets::i();
$tickets->data['cats'] = array();
$cats = tcategories::i();
$man = tdbmanager::i();
$db = litepublisher::$db;
$table = 'tickets';
$ticketstable = $db->prefix . 'tickets';

$menus = tmenus::i();
$fakes = array();
$menus->lock();

$menuitem = tticketsmenu::i($menus->url2id("/tickets/"));
    $parentfake = new tfakemenu();
    $parentfake->title = $menuitem->title;
    $parentfake->url = $menuitem->url;
$parentfake->order = $menuitem->order;
$parentfake->status = $menuitem->status;

$itemcat = array(
      'idurl' => 0,
      'customorder' => $menuitem->order,
      'parent' => 0,
      'title' => $menuitem->title,
      'idview' => $menuitem->idview,
      'idperm' => 0,
      'icon' => 0,
      'itemscount' => 0,
      );

     $parentcat = $cats->db->add($itemcat);
$cats->contents->edit($parentcat, $menuitem->rawcontent, '', '', '');

foreach ($types as $type) {
$db->table = 'tickets';
$idposts = $db->idselect("type = '$type'");

$menuitem = tticketsmenu::i($menus->url2id("/$type/"));
    $fake = new tfakemenu();
    $fake->title = $menuitem->title;
    $fake->url = $menuitem->url;
$fake->order = $menuitem->order;
$fake->status = $menuitem->status;
$fakes[$type] = $fake;

$itemcat = array(
      'idurl' => 0,
      'customorder' => $menuitem->order,
      'parent' => $parentcat,
      'title' => $menuitem->title,
      'idview' => $menuitem->idview,
      'idperm' => 0,
      'icon' => 0,
      'itemscount' => count($idposts)
      );

     $idcat = (int) $cats->db->add($itemcat);
$menus->delete($menuitem->id);

$idurl = litepublisher::$urlmap->add("/$type/", get_class($cats), $idcat);
$cats->db->setvalue($idcat, 'idurl', $idurl);
$tickets->data['cats'][] = $idcat;

if (count($idposts) > 0) {
$db->table = 'posts';
$db->update("categories = '$idcat'", "id in (" . implode(',', $idposts) . ")");

$db = $cats->itemsposts->db;
foreach ($idposts as $idpost) {
        $db->insertrow("(post, item) values  ($idpost, $idcat)");
}
}
}

$menus->deletetree($menus->url2id("/tickets/"));
$idurl = litepublisher::$urlmap->add("/tickets/", get_class($cats), $parentcat);
$cats->db->setvalue($parentcat, 'idurl', $idurl);

$menus->addfakemenu($parentfake);
foreach ($types as $type) {
$fake = $fakes[$type];
$fake->parent = $parentfake->id;
    $menus->addfakemenu($fake);
}
$menus->unlock();

$man->alter($table, "drop index type"); 
$man->alter($table, "drop type"); 

$tickets->save();
litepublisher::$options->savemodified();
  litepublisher::$classes->delete('tticketsmenu');
}