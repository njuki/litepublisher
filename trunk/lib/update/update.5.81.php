<?php
function update581() {
  litepublisher::$site->jqueryui_version = '1.10.4';

$js = tjsmerger::i();
$js->lock();
  $js->deletefile('admin', '/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
$a = &$js->items['admin']['files'];
array_insert($a, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.core.min.js', 0);
array_insert($a, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.widget.min.js', 1);
array_insert($a, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.mouse.min.js', 2);
array_insert($a, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.position.min.js', 3);
array_insert($a, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.tabs.min.js', 4);

  $section = 'adminviews';
  $js->replacefile($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.draggable.min.js', '/js/jquery/ui-$site.jqueryui_version/jquery.ui.draggable.min.js');
  $js->replacefile($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.droppable.min.js', '/js/jquery/ui-$site.jqueryui_version/jquery.ui.droppable.min.js');
  $js->replacefile($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.resizable.min.js', '/js/jquery/ui-$site.jqueryui_version/jquery.ui.resizable.min.js');
  $js->replacefile($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.selectable.min.js', '/js/jquery/ui-$site.jqueryui_version/jquery.ui.selectable.min.js');
  $js->replacefile($section, '/js/jquery/ui-$site.jqueryui_version/interact/jquery.ui.sortable.min.js', '/js/jquery/ui-$site.jqueryui_version/jquery.ui.sortable.min.js');

  $js->unlock();
}