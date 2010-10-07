<?php
function update394() {
$parser = tmediaparser::instance();
if (!isset($parser->data['enablepreview'])) {
$parser->data['enablepreview'] = true;
$parser->save();
}
}
?>