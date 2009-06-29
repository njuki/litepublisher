<?php

function Update233() {
global $paths;
$plugins = &TPlugins::Instance();
if (isset($plugins->items['KeyWords'])) {
$plugins->items['keywords'] = $plugins->items['KeyWords'];
unset($plugins->items['KeyWords']);
$plugins->Save();
TClasses::$items['TKeywordsPlugin'][1] = 'keywords';
TClasses::Save();
}

$old = $paths['plugins'] . 'KeyWords';
$new = $paths['plugins'] . 'keywords';
$tmp = $paths['plugins'] . 'tmp';

if (@is_dir($old)) {
rename($old, $tmp);
if (@is_dir($new))  {
TFiler::DeleteFiles($temp . DIRECTORY_SEPARATOR  , true, true);
} else {
rename($tmp, $new);
}
}

$posts = &TPosts::Instance();
foreach ($posts->items as $id => $item) {
if ($item['status'] == 'published') unset($posts->items[$id]['status']);
}
$posts->Save();
}

?>