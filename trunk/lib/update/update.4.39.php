<?php

function update439() {
if (!litepublisher::$classes->exists('tpolls'])) return;
$p = tpolls::instance();
foreach ($p->templateitems as $name => $tml) {
$p->templateitems[$name] = str_replace(
'onclick="pollclient.clickvote($id, $index);"', 
'rel="poll"',
str_replace("'", '"', $tml));
}
$p->save();

include_once(litepublisher::$paths->plugins . 'polls/polls.class.install.php');
$template = ttemplate::instance();
$template->addtohead(getpollhead());
$template->save();

$posts = tposts::instance();
$posts->addrevision();
tstorage::savemodified();
litepublisher::$urlmap->clearcache();
}