<?php

function update525() {
$home = thomepage::i();
$home->data['parsetags'] = false;
$home->save();

update525tags(tcategories::i());
update525tags(ttags::i());

tplugins::i()->delete('litecategories');
}

function update525tags($tags) {
if (dbversion) {
$man = tdbmanager::i();
$man->alter($tags->table, "add `includeparents` boolean default " . ($tags->includeparents ? 'true' : 'false'));
$man->alter($tags->table, "add `includechilds` boolean default " . ($tags->includechilds ? 'true' : 'false'));
$man->alter($tags->table, "add `invertorder` boolean default false");
$man->alter($tags->table, "add `lite` boolean default " . ($tags->lite ? 'true' : 'false'));
$man->alter($tags->table, "add `liteperpage` int unsigned NOT NULL default '1000'");
} else {
foreach ($tags->items as $id => &$item) {
$item['includechilds'] = $tags->includechilds;
$item['includeparents'] = $tags->includeparents;
$item['lite'] = $tags->lite;
$item['liteperpage'] = 1000;
$item['invertorder'] = false;
}
$tags->save();
}
}