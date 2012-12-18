<?php

function update546() {
litepublisher::$site->jquery_version = '1.8.3';
  litepublisher::$site->jqueryui_version = '1.9.2';
  litepublisher::$site->save();

$js = tjsmerger::i();
$js->lock();
  $section = 'adminviews';
  $js->deletefile($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.mouse.min.js');
  $js->deletefile($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.draggable.min.js');
  $js->deletefile($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.droppable.min.js');
  $js->deletefile($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.selectable.min.js');
  $js->deletefile($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.sortable.min.js');
  $js->deletefile($section, '/js/litepublisher/admin.views.min.js');
  
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.draggable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.droppable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.resizable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.selectable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.sortable.min.js');
  $js->add($section, '/js/litepublisher/admin.views.min.js');
  
$js->unlock();

$admin = tadminmenus::i();
$admin->heads = str_replace(
'$site.files/js/jquery/ui-$site.jqueryui_version/redmond/jquery-ui-$site.jqueryui_version.custom.css',
'$site.files/js/jquery/ui-$site.jqueryui_version/redmond/jquery-ui-$site.jqueryui_version.custom.min.css',
$admin->heads);
$admin->save();
}