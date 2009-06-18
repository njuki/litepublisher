<?php

function TMetaWidgetUninstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->DeleteWidget(get_class($self));
}

?>