<?php

function update502() {
$s = DIRECTORY_SEPARATOR . '/';
$files = tfiles::i();
if (dbversion) {
$items = $files->select('LOCATE(' . $s = dbquote($s) .', filename) > 0', '');
foreach ($items as $id) {
$filename = $files->items[$id]['filename'];
$filename = str_replace(DIRECTORY_SEPARATOR.  '/', '/', $filename);
//echo "$filename<br>";
$files->setvalue($id, 'filename', $filename);
}
} else {
foreach ($files->items as $id => $item) {
if (strpos($item['filename'], DIRECTORY_SEPARATOR.  '/')) {
$files->items[$id]['filename'] = str_replace(DIRECTORY_SEPARATOR.  '/', '/', $item['filename']);
}
}
$filess->save();
}

}