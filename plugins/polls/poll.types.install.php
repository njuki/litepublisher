<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpoltypesInstall($self) {
$lang = tlocal::i('polls');
$theme = ttheme::i();
$res = dirname(__file__) .DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
$self->data['result'] = $theme->replacelang(file_get_contents($res . 'microformat.tml'), $lang);
$self->data['itemresult'] = '';
$ini = parse_ini_file($res . 'types.ini',  true);
foreach ($ini as $type => $item) {
$item['type'] = $type;
$item['item'] = $theme->replacelang($item['item'], $lang);
$item['items'] = $theme->replacelang($item['items'], $lang);
$self->add($item);
}
$self->save();
}