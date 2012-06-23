<?php

function update533() {
$lang = tlocal::admin('editor');
$js = tjsmerger::i();
$js->lock();
  $section = 'admin';
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
  $js->add($section, '/js/litepublisher/admin.min.js');

  $section = 'adminviews';
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.mouse.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.draggable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.droppable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.selectable.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.sortable.min.js');
  $js->add($section, '/js/litepublisher/admin.views.min.js');

$js->unlock();
}