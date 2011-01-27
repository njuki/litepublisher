<?php

function update419() {
tsidebars::fix();
$widgets = twidgets::instance();
$views = tviews::instance();
$widgets->deleted = $views->widgetdeleted;
}
