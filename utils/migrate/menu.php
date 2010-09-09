function migratemenus() {
$data = new tmigratedata();
$data->load('menus' . DIRECTORY_SEPARATOR . 'index');
$menus = tmenus::instance();
$menus->lock();
$menus->autoid = $data->lastid;
foreach ($data->data['items'] as $id => $item) {
$menu = migratemenu($id, $item['class']);
    $menus->items[$id] = array(
    'id' => $id,
    'class' => get_class($menu)
    );
    //move props
    foreach (tmenu::$ownerprops as $prop) {
      $menus->items[$id][$prop] = $menu->$prop;
      if (array_key_exists($prop, $menu->data)) unset($menu->data[$prop]);
    }
$menu->save();
}
$menus->sort();
$menus->unlock();
}

function migratemenu($id, $class) {
global $data;
$data->load('menus' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR  . 'index');
$menu = tmenu::instance();
foreach ($data->data as $name => $value) {
if (isset($menu->data[$name])) $menu->data[$name] = $value;
}
return $menu;
}