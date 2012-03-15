<?php

function update522() {
unset(litepublisher::$classes->items['toauth']);
litepublisher::$classes->save();
}