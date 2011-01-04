<?php

function update411() {
litepublisher::$classes->items['tcoevents'] = array('events.class.php', '');
litepublisher::$classes->save();

if (isset(litepublisher::$classes->items['thomepageInvert'])) {
//uninst old
    $parent = get_parent_class('thomepageInvert');
    $urlmap = turlmap::instance();
    if (dbversion) {
      $item = $urlmap->db->finditem("url = '/'");
      $urlmap->setvalue($item['id'], 'class', $parent);
    } else {
      $urlmap->items['/']['class'] = $parent;
      $urlmap->save();
}

$plugin = thomepageInvert::instnace();
$plugin->install();
}

}