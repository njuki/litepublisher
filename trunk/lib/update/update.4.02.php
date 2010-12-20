<?php

function update402() {
$filter = tcontentfilter::instance();
$filter->data['usefilter'] = true;
$filter->save();

  litepublisher::$urlmap->lock();
    litepublisher::$urlmap->unsubscribeclassname('TXMLRPCFiles');
        litepublisher::$urlmap->deleteclass('TXMLRPCFiles');

if (!litepublisher::$urlmap->findurl('/getwidget.htm')) {
  litepublisher::$urlmap->addget('/getwidget.htm', 'twidgets');

  $robot = trobotstxt::instance();
  $robot->AddDisallow('/getwidget.htm');
}  
  litepublisher::$urlmap->unlock();
}