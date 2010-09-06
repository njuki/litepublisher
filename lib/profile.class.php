<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tprofile extends tevents implements itemplate {
  
  public static function instance($id = 0) {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'profile';
    $this->data = $this->data + array(
    'url' => '/profile.htm',
    'template' => '',
    'tmlfile' => '',
    'theme' => '',
    'nick' => 'admin',
    'dateOfBirth' => date('Y-m-d'),
    'gender' => 'male',
    'img' => '',
    
    'icqChatID' => '',
    'aimChatID' => '',
    'jabberID' => '',
    'msnChatID' => '',
    'yahooChatID' => '',
    'mbox' => '',
    
    'country' => litepublisher::$options->language,
    'region' => '',
    'city' => '',
    'geourl' => 'http://beta-maps.yandex.ru/?text=',
    'bio' => '',
    'interests' => '',
    'interesturl' => '  http://www.livejournal.com/interests.bml?int='
    );
  }
  
  public function getfoaf() {
    $options = litepublisher::$options;
    $posts = tposts::instance();
    $postscount = $posts->archivescount;
    $manager = litepublisher::$classes->commentmanager;
    
    $result = '<foaf:nick>' . tfoaf::escape($this->nick) . '</foaf:nick>' .
    '<foaf:name>' . tfoaf::escape($this->nick) . '</foaf:name>' .
    '<foaf:dateOfBirth>' . tfoaf::escape($this->dateOfBirth) . '</foaf:dateOfBirth>' .
    "<foaf:gender>$this->gender</foaf:gender>" .
    '<foaf:img rdf:resource="' . tfoaf::escape($this->img) . '" />' .
    '<foaf:icqChatID>' . tfoaf::escape($this->icqChatID) . '</foaf:icqChatID>' .
    '<foaf:aimChatID>' . tfoaf::escape($this->aimChatID) . '</foaf:aimChatID>' .
    '<foaf:jabberID>' . tfoaf::escape($this->jabberID) . '</foaf:jabberID>' .
    '<foaf:msnChatID>' . tfoaf::escape($this->msnChatID) . '</foaf:msnChatID>' .
    '<foaf:yahooChatID>' . tfoaf::escape($this->yahooChatID) . '</foaf:yahooChatID>' .
    '<foaf:homepage>' . tfoaf::escape($options->url) . '/</foaf:homepage>' .
    '<foaf:mbox>' . tfoaf::escape($this->mbox) . '</foaf:mbox>' .
    '<foaf:weblog ' .
    'dc:title="'. tfoaf::escape($options->name) . '" ' .
    'rdf:resource="' . tfoaf::escape($options->url) . '/" />' .
    
    '<foaf:page>' .
    '<foaf:Document rdf:about="' . tfoaf::escape($options->url . $this->url) . '">' .
    '<dc:title>' . tfoaf::escape($options->name) . ' Profile</dc:title>' .
    '<dc:description>Full profile, including information such as interests and bio.</dc:description>' .
    '</foaf:Document>' .
    '</foaf:page>' .
    
    '<lj:journaltitle>' . tfoaf::escape($options->name) . '</lj:journaltitle>' .
    '<lj:journalsubtitle>' . tfoaf::escape($options->description) . '</lj:journalsubtitle>' .
    
    '<ya:blogActivity>' .
    '<ya:Posts>' .
    '<ya:feed ' .
    'dc:type="application/rss+xml" ' .
    'rdf:resource="' . tfoaf::escape($options->url) . '/rss.xml" />' .
    "<ya:posted>$postscount</ya:posted>" .
    '</ya:Posts>' .
    '</ya:blogActivity>' .
    
    '<ya:blogActivity>' .
    '<ya:Comments>' .
    '<ya:feed ' .
    'dc:type="application/rss+xml" '.
    'rdf:resource="' . tfoaf::escape($options->url) . '/comments.xml"/>' .
    "<ya:posted>$postscount</ya:posted>" .
    "<ya:received>$manager->count</ya:received>" .
    '</ya:Comments>' .
    '</ya:blogActivity>';
    
    if ($this->bio != '') $result .= '<ya:bio>'. tfoaf::escape($this->bio) . '</ya:bio>';
    
    $result .= $this->GetFoafOpenid();
    $result .= $this->GetFoafCountry();
    $result .= $this->GetFoafInterests();
    return $result;
  }
  
  public function GetFoafInterests() {
    $result = '';
    $list = explode(',', $this->interests);
    foreach ($list as $name) {
      $name = trim($name);
      if (empty($name)) continue;
      $result .= '<foaf:interest dc:title="' . tfoaf::escape($name) . '" rdf:resource="' . tfoaf::escape($this->interesturl) . urlencode($name) . '" />';
    }
    return $result;
  }
  
  public function GetFoafOpenid() {
    return '<foaf:openid rdf:resource="'. tfoaf::escape(litepublisher::$options->url) . '/" />';
  }
  
  public function GetFoafCountry() {
    $result = '';
    if ($this->country != '') $result .= '<ya:country dc:title="' . tfoaf::escape($this->country) . '" '.
    'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode($this->country) . '"/>';
    
    if ($this->region != '') $result .='<ya:region dc:title="' . tfoaf::escape($this->region) . '" '.
    'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode($this->region) . '"/>';
    
    if ($this->city != '') $result .= '<ya:city dc:title="' . tfoaf::escape($this->city) . '" ' .
    'rdf:resource="' . tfoaf::escape($this->geourl) . urlencode("$this->country, $this->city") . '" />';
    
    return $result;
  }
  
public function request($arg) { }
  
  public function gettitle() {
    return tlocal::$data['default']['profile'];
  }
public function gethead() { }
  
  public function getkeywords() {
    return $this->interests;
  }
  
  public function getdescription() {
    return tcontentfilter::getexcerpt($this->bio, 128);
  }
  
  public function getcont() {
    tlocal::loadlang('admin');
    $lang = tlocal::instance('profile');
    ttheme::$vars['profile'] = $this;
    $theme = ttheme::instance();
    return $theme->parse($this->template);
  }
  
  protected function getstat() {
    $posts = tposts::instance();
    $manager = tcommentmanager::instance();
    $lang = tlocal::instance('profile');
    return sprintf($lang->statistic, $posts->archivescount, $manager->count);
  }
  
  protected function getmyself() {
    $lang = tlocal::instance('profile');
    $result = array();
    if ($this->img != '') $result[] = "<img src=\"$this->img\" />";
    if ($this->nick != '') $result[] = "$lang->nick $this->nick";
    if (($this->dateOfBirth != '')  && @sscanf($this->dateOfBirth , '%d-%d-%d', $y, $m, $d)) {
      $date = mktime(0,0,0, $m, $d, $y);
      $ldate = TLocal::date($date);
      $result[] = sprintf($lang->birthday, $ldate);
    }
    
    $result[] = $this->gender == 'female' ? $lang->female : $lang->male;
    
    if (!$this->country != '') $result[] = $this->country;
    if (!$this->region != '') $result[] = $this->region;
    if (!$this->city != '') $result[] = $this->city;
    return "<p>\n" . implode(", ", $result) . "</p>\n";
  }
  
  protected function getcontacts() {
    $contacts = array(
    'icqChatID' => 'ICQ',
    'aimChatID' => 'AIM',
    'jabberID' => 'Jabber',
    'msnChatID' => 'MSN',
    'yahooChatID' => 'Yahoo',
    'mbox' => 'E-Mail'
    );
    $lang = tlocal::instance('profile');
    $result = "<table>
    <thead>
    <tr>
    <th align=\"left\">$lang->contactname</th>
    <th align=\"left\">$lang->value</th>
    </tr>
    </thead>
    <tbody>\n";
    
    foreach ($contacts as $contact => $name) {
      $value = $this->data[$contact];
      if ($value == '') continue;
      $result .= "<tr>
      <td align=\"left\">$name</td>
      <td align=\"left\">$value</td>
      </tr>\n";
    }
    
    $result .= "</tbody >
    </table>";
    return $result;
  }
  
  protected function getmyinterests() {
    $result = "<p>\n";
    $list = explode(',', $this->interests);
    foreach ($list as $name) {
      $name = trim($name);
      if (empty($name)) continue;
      $result .= "<a href=\"$this->interesturl". urlencode($name). "\">$name</a>,\n";
    }
    $result .= "</p>\n";
    return $result;
  }
  
  protected function getfriendslist() {
    $result = "<p>\n";
    $foaf = tfoaf::instance();
    $foaf->loadall();
    foreach ($foaf->items As $id => $item) {
    $url = $foaf->redir ?"litepublisher::$options->url$foaf->redirlink{litepublisher::$options->q}friend=$id" : $item['url'];
    $result .= "<a href=\"$url\" rel=\"friend\">{$item['nick']}</a>,\n";
    }
    $result .= "</p>\n";
    return $result;
  }
  
}//class

?>