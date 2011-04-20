<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trss extends tevents {
  public $domrss;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'shop-base' . DIRECTORY_SEPARATOR . 'feed.google';
    $this->addevents('beforepost', 'afterpost', 'onpostitem');
  }
  
  public function request($arg) {
    $result = '<?php turlmap::sendxml(); ?>';
    $this->domrss = new tdomrss;
    $this->domrss->CreateRoot(litepublisher::$site->url. '/rss.xml', litepublisher::$site->name);
$rootnode = $this->domrss->rss;
tnode::attr($rootnode, 'xmlns:g', 'http://base.google.com/ns/1.0');
tnode::attr($rootnode, 'xmlns:c', 'http://base.google.com/cns/1.0');
/*
tnode::attr($rootnode, 'xmlns:app', 'http://www.w3.org/2007/app');
tnode::attr($rootnode, 'xmlns:gd', 'http://schemas.google.com/g/2005');
tnode::attr($rootnode, 'xmlns:sc', 'http://schemas.google.com/structuredcontent/2009');
tnode::attr($rootnode, 'xmlns:scp', 'http://schemas.google.com/structuredcontent/2009/products');
*/
      $this->addproducts();
  
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  public function addproducts() {
    $product = tproducts::instance();
    $this->getrssposts($posts->getrecent(litepublisher::$options->perpage));
  }
  
  public function getrssposts(array $list) {
    foreach ($list as $id ) {
      $this->addproduct(tpost::instance($id));
    }
  }
  
  public function addproduct(tproduct $product) {
$shop = tshop::instance();
    $item = $this->domrss->AddItem();
//Required
    tnode::addvalue($item, 'g:id', $product->id);
    tnode::addvalue($item, 'title', $product->title);
    tnode::addvalue($item, 'link', $product->link);
    tnode::addvalue($item, 'g:price', sprintf('%f %s', $product->price, $shop->currency);));
     tnode::addvalue($item, 'description', strip_tags($product->filtered));
    tnode::addvalue($item, 'g:condition', $product->condition);

//Recommended
//Global Trade Item Numbers (GTINs) for your products. These identifiers include UPC (in North America), EAN (in Europe), 
//A unique numerical identifier for commercial products that's usually associated with a barcode printed on retail merchandise.
if ($product->gtin != '' ) tnode::addvalue($item, 'g:gtin', $product->gtin);

// The manufacturer product number (the number which uniquely identifies the product to it's manufacturer). It is required to provide identifiers such as 'gtin'
if ($product->mpn != '' ) tnode::addvalue($item, 'g:mpn', $product->mpn);
if ($product->brand != '' ) tnode::addvalue($item, 'g:brand', $product->brand);
// use attribute only for items whose brand and manufacturer are different, e.g. when one brand is produced by multiple manufacturers.
if ($product->manufacturer != '' ) tnode::addvalue($item, 'g:manufacturer', $product->manufacturer);
if ($image = $product->image_link ) tnode::addvalue($item, 'g:image_link', $image);
if ($product->quantity > 0) tnode::addvalue($item, 'g:quantity', $product->quantity);
tnode::addvalue($item, 'g:availability', $product->availability);
tnode::addvalue($item, 'g:online_only', $product->online_only);
if ($product->expiration_date > time()) tnode::addvalue($item, 'g:expiration_date', date('Y-m-d', $product->expiration_date));
if ($product->weight > 0) tnode::addvalue($item, 'g:shipping_weight', sprintf('%d %s', $product->weight, $product->unit_weight));
if ($product->rate > 0.0) tnode::addvalue($item, 'g:product_review_average', $product->rate);
if ($product->votes > 0) tnode::addvalue($item, 'g:product_review_count', $product->votes);
tnode::addvalue($item, 'g:featured_product', $product->featured);
if ($product->year_produced > 0) tnode::addvalue($item, 'g:year', $product->year_produced);

if (count($product->types) > 0) {
    $producttypes = tproducttypes::instance();
    $names = $producttypes ->getnames($product->types);
    foreach ($names as $name) {
      if (empty($name)) continue;
      tnode::addvalue($item, 'g:product_type', $name);
    }
}
    
   }

}//class