<?php

function update525() {
update525tags(tcategories::i());
update525tags(ttags::i());
}

function update525tags($tags) {
if (dbversion) {
$man = tdbmanager::i();
$man->alter($tags->table, "add `includeparents` boolean default " . ($tags->includeparents ? 'true' : 'false'));
$man->alter($tags->table, "add `includechilds` boolean default " . ($tags->includechilds ? 'true' : 'false'));
$man->alter($tags->lite, "add `lite` boolean default " . ($tags->lite ? 'true' : 'false'));
$man->alter($tags->table, "add `liteperpage` int unsigned NOT NULL default '1000'");
} else {
foreach ($tags->items as $id => &$item) {
$item['includechilds'] = $tags->includechilds;
$item['includeparents'] = $tags->includeparents;
$item['lite'] = $tags->lite;
$item['liteperpage'] = 1000;
}
$tags->save();
}
}
