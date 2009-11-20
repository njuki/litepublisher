<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function TMetaWidgetUninstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->DeleteWidget(get_class($self));
}

?>