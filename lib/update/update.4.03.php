<?php

function update403() {
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

if (isset(litepublisher::$classes->items['tpolls'])) {
litepublisher::$urlmap->addget('/ajaxpollserver.htm', 'tpolls');
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->deleteclass('tpolls');
}

  litepublisher::$urlmap->unlock();

$template = ttemplate::instance();
  $template->heads =
  '<link rel="alternate" type="application/rss+xml" title="$site.name RSS Feed" href="$site.url/rss.xml" />
  <link rel="pingback" href="$site.url/rpc.xml" />
  <link rel="EditURI" type="application/rsd+xml" title="RSD" href="$site.url/rsd.xml" />
  <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="$site.url/wlwmanifest.xml" />
  <link rel="shortcut icon" type="image/x-icon" href="$template.icon" />
  <meta name="generator" content="Lite Publisher $site.version" /> <!-- leave this for stats -->
  <meta name="keywords" content="$template.keywords" />
  <meta name="description" content="$template.description" />
  <link rel="sitemap" href="$site.url/sitemap.htm" />
  <script type="text/javascript" src="$site.files/js/litepublisher/litepublisher.min.js"></script>
  <link type="text/css" href="$site.files/js/prettyphoto/css/prettyPhoto.css" rel="stylesheet" />
  <script type="text/javascript">
  $(document).ready(function() {
    $("a[rel^=\'prettyPhoto\']").prettyPhoto();
  });
  </script>';
$template->save();
}