<?php
function update334() {
if (!dbversion) return;
$data = &litepublisher::$options->data;

$data['posts'] = $data['tposts'];
unset($data['tposts']);
unset($data['turlmap']);
unset($data['tcomments']);
unset($data['tcomusers']);
unset($data['tpingbacks']);
unset($data['tfileitems']);
$data['foaf'] = $data['tfoaf'];
unset($data['tfoaf']);
unset($data['tusers']);

litepublisher::$options->save();
}
?>