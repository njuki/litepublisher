<?php

function update530() {
if (litepublisher::$classes->exists('tpolls')) {
    tcssmerger::i()->addstyle(dirname(__file__) . '/stars.min.css');
  litepublisher::$urlmap->delete('/ajaxpollserver.htm');
$self = tpolls::i();
$self->templates['star'] = '<h4>$title</h4>
<ul id="pollform_$id" class="rating star_0">$items  </ul>';

$self->save();

  $json = tjsonserver::i();
  $json->addevent('polls_sendvote', get_class($self), 'polls_sendvote');

$man = tdbmanager::i();
$man->deletetable('pollusers');

}
}