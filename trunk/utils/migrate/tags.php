function migratetags(tcommontags $tags) {
global $data, $man;
$data->load($tags->basename);
if (dbversion) {
$man->setautoincrement($tags->table, $data->lastid)
} else {
$tags->autoid = $data->lastid;
}

foreach ($data->data['items] as $id => $item) {
$idurl = addurl($item['url'], $tags, $id);
if (dbversion) {
$db->insert_a(array(
'id' => $id,
'idurl' => $idurl,
'title' => $item['name'],
'itemscount' => count($item['items'])
));
} else {
$tags->items[$id] =
    'id' => $id,
    'parent' => 0,
    'idurl' =>         $idurl,
    'url' =>$item['url'],
    'title' => $item['name'],
    'icon' => 0,
    'itemscount' => count($item['items'])
    );
}
}
if (!dbversion) $tags->save();
}
