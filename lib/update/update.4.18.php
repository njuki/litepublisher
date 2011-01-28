<?php

function update418() {
tsidebars::fix();
$widgets = twidgets::instance();
$views = tviews::instance();
$widgets->deleted = $views->widgetdeleted;

ttheme::clearcache();
}
