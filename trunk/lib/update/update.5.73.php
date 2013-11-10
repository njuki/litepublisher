<?php

function update573() {
$js = tjsmerger::i();
$js->lock();
$section = 'default';
  $js->deletefile($section, '/js/litepublisher/prettyphoto.dialog.min.js');
   $js->add($section, '/js/litepublisher/dialog.min.js');
    $js->add($section, '/js/litepublisher/dialog.pretty.min.js');
      $js->add($section, '/js/litepublisher/dialog.bootstrap.min.js');
            $js->add($section, '/js/litepublisher/widgets.bootstrap.min.js');
$js->unlock();

$home = thomepage::i();
$home->data['showposts'] = !$home->data['hideposts'];
unset($home->data['hideposts']);
        $home->data['showpagenator'] = true;
                $home->data['showmidle'] = false;
                $home->data['midlecat'] = 0;
$home->save();


//delete tree editor
$m = tadminmenus::i();
$m->deleteurl('/admin/views/edittheme/');

tlocalmerger::i()->delete('theme');

unset(litepublisher::$classes->items['tadminthemetree']);
litepublisher::$classes->classes['home'] = 'thomepage';
litepublisher::$classes->save();
}