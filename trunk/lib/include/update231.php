<?php

function Update231() {
$cust = &TCustomWidget::Instance();
foreach ($cust->items as $id => $item) {
$cust->items[$id]['templ'] = true;
}
$cust->Save();
}

?>