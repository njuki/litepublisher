<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyml extends tevents {
  public $dom;
public $shop;
public $offers;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'yml';
    $this->addevents('beforepost', 'afterpost', 'onpostitem');
    $this->data['template'] = '';
  }
  
  public function request($arg) {
    $result = '<?php turlmap::sendxml(); ?>';
    $this->dom = $this->createdom();
$this->addprofile();
      $this->addoffers();
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }

public function createdom() {
$impl = new DOMImplementation();
$dom = $impl->createDocument('', 'yml_catalog', 
$impl->createDocumentType('yml_catalog', '', 'shops.dtd'));
    $dom->encoding = 'utf-8';
tnode::attr($dom->lastChild, 'date', date('Y-m-d h:i'));
$this->shop = tnode::add($dom->lastChild, 'shop');
return  $dom;
}

public function addprofile() {
$shop = tshop::instance();
        tnode::addvalue($this->shop, 'name', $shop->title);
        tnode::addvalue($this->shop, 'company', $shop->company);
        tnode::addvalue($this->shop, 'url', litepublisher::$site->url . '/');
$currency  = tnode::add(tnode::add($this->shop, 'currencies'), 'currency');
tnode::attr($currency,  'rate', '1');
tnode::attr($currency, 'id', $shop->currency);
//'RUR'

$cats = tcategories::instance();
$cats->loadall();
$domcats = tnode::add($this->shop, 'categories');
tnode::attr(tnode::addvalue($domcats, 'category', $cats->items[$shop->rootcategory]['title']),
'id', $shop->rootcategory);
$this->addcats($domcats, $shop->rootcategory);
}

public function addcats($domcats, $parent) {
$cats = tcategories::instance();
    foreach ($cats->items as $id => $item) {
      if ($parent == $item['parent']) {
$node = tnode::addvalue($domcats, 'category', $item['title']);
tnode::attr($node, 'id', $id);
tnode::attr($node, 'parentId', $parent);
$this->addcats($domcats, $id);
}
}
}

    public function addoffers() {
$this->offers = tnode::add($this->shop, 'offers');
    $products = tproducts::instance();
    foreach ($list as $id ) {
      $this->addoffer(tproduct::instance($id));
    }
  }
  
  public function addoffer(tproduct $product) {
$shop = tshop::instance();
    $offer = tnode::add($this->offers, 'offer');
tnode::attr($offer, 'id', $product->id);
tnode::attr($offer, 'available', $product->availability== 'in stock' ? 'true' : 'false');
tnode::attr($offer, 'type', $this->gettype($product));

    tnode::addvalue($offer, 'name', $product->title);
    tnode::addvalue($offer, 'url', $product->link);
if ($image = $product->image_link ) tnode::addvalue($offer, 'picture', $image);
    tnode::addvalue($offer, 'price', $product->price);
    tnode::addvalue($offer, 'currencyId', $shop->currency);
    tnode::addvalue($offer, 'categoryId', $product->categories[0]);
        tnode::addvalue($offer, 'description', strip_tags($product->filtered));
if ($product->brand != '' ) tnode::addvalue($offer, 'vendor', $product->brand);
if ($product->mpn != '' ) tnode::addvalue($offer, 'vendorCode', $product->mpn);
if ($product->gtin != '' ) tnode::addvalue($offer, 'barcode', $product->gtin);
if ($product->mpn != '' ) tnode::addvalue($offer, 'model', $product->mpn);
tnode::addvalue($offer, 'downloadable', $product->online_only == 'y' ? 'yes' : 'no');

/*
tnode::addvalue($offer, 'delivery', 'false');

sales_notes
country_of_origin
local_delivery_cost
typePrefix
manufacturer_warranty
*/

}

}//class