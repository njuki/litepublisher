<?php

function update573() {
$l = tlocalmerger::i();
$l->lock();
$l->delete('theme');
    $l->add('mail', "lib/languages/" . litepublisher::$options->language . "/mail.ini");
    if (litepublisher::$classes->exists('tticket')) {
        $l->add('mail', "plugins/tickets/resource/" . litepublisher::$options->language . ".mail.ini");
        }
        
            if (litepublisher::$classes->exists('tfoaf')) {
        $l->add('mail', "plugins/foaf/resource/" . litepublisher::$options->language . ".mail.ini");
        }

    $l->unlock();

  litepublisher::$site->jquery_version = '1.10.2';
  litepublisher::$site->save();
  
$js = tjsmerger::i();
$js->lock();
$section = 'default';
  $js->deletefile($section, '/js/litepublisher/prettyphoto.dialog.min.js');
   $js->add($section, '/js/litepublisher/dialog.min.js');
    $js->add($section, '/js/litepublisher/dialog.pretty.min.js');
      $js->add($section, '/js/litepublisher/dialog.bootstrap.min.js');
            $js->add($section, '/js/litepublisher/widgets.bootstrap.min.js');
$js->unlock();

$css = tcssmerger::i();
$css->lock();
  $css->add('default', '/js/litepublisher/css/form.inline.min.css');
$css->unlock();

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

unset(litepublisher::$classes->items['tadminthemetree']);
unset(litepublisher::$classes->items['tmailtemplate']);
litepublisher::$classes->classes['home'] = 'thomepage';
litepublisher::$classes->save();
}