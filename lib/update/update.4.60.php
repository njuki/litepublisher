<?php

function update460() {
$contact = tcontactform::instance();
$contact->data['extra'] = array();
$contact->save();
}