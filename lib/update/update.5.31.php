<?php

function update531() {
if (litepublisher::$classes->exists('tpolls')) {
$man = tpollsman::i();
$polls= tpolls::i();
$polls->loadall_tml();
foreach ($polls->tml_items as $id_tml => $tml) {
if ($tml['type'] == 'star') {
$man->data['fivestars'] = $id_tml;
$man->save();
break;
}
}
}
}