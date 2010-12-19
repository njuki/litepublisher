<?php

function update403() {
$filter = tcontentfilter::instance();
$filter->data['usefilter'] = true;
$filter->save();

    litepublisher::$urlmap->unsubscribeclassname('TXMLRPCFiles');
        litepublisher::$urlmap->deleteclass('TXMLRPCFiles');
}