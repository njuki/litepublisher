<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tprofile extends tevents {
  
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
    
    $result = "<foaf:nick>$this->nick</foaf:nick>
    <foaf:name>$this->nick</foaf:name>
    <foaf:dateOfBirth>$this->dateOfBirth</foaf:dateOfBirth>
    <foaf:gender>$this->gender</foaf:gender>
    <foaf:img rdf:resource=\"$this->img\" />
    <foaf:icqChatID>$this->icqChatID</foaf:icqChatID>
    <foaf:aimChatID>$this->aimChatID</foaf:aimChatID>
    <foaf:jabberID>$this->jabberID</foaf:jabberID>
    <foaf:msnChatID>$this->msnChatID</foaf:msnChatID>
    <foaf:yahooChatID>$this->yahooChatID</foaf:yahooChatID>
    <foaf:homepage>$options->url/</foaf:homepage>
    <foaf:mbox>$this->mbox</foaf:mbox>
    <foaf:weblog
    dc:title=\"$options->name\"
    rdf:resource=\"$options->url/\"/>
    
    <foaf:page>
    <foaf:Document rdf:about=\"$options->url$this->url\">
    <dc:title>$options->name Profile</dc:title>
    <dc:description>Full profile, including information such as interests and bio.</dc:description>
    </foaf:Document>
    </foaf:page>
    
    <lj:journaltitle>litepublisher::$options->name</lj:journaltitle>
    <lj:journalsubtitle>litepublisher::$options->description</lj:journalsubtitle>
    
    <ya:blogActivity>
    <ya:Posts>
    <ya:feed
    dc:type=\"application/rss+xml\"
    rdf:resource=\"litepublisher::$options->url/rss/\"/>
    <ya:posted>$postscount</ya:posted>
    </ya:Posts>
    </ya:blogActivity>
    
    <ya:blogActivity>
    <ya:Comments>
    <ya:feed
    dc:type=\"application/rss+xml\"
    rdf:resource=\"litepublisher::$options->url/comments/\"/>
    <ya:posted>$postscount</ya:posted>
    <ya:received>$manager->count</ya:received>
    </ya:Comments>
    </ya:blogActivity>\n";
    
    if ($this->bio != '') $result .= "<ya:bio>$this->bio</ya:bio>\n";
    
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
      $result .= "    <foaf:interest dc:title=\"$name\" rdf:resource=\"$this->interesturl". urlencode($name) . "\" />\n";
    }
    return $result;
    
  }
  
  public function GetFoafOpenid() {
    return '<foaf:openid rdf:resource="'. litepublisher::$options->url . '/" />';
  }
  
  public function GetFoafCountry() {
    $result = '';
    if ($this->country != '') $result .= "<ya:country dc:title=\"$this->country\"
    rdf:resource=\"$this->geourl" . urlencode($this->country) . "\"/>\n";
    
    if ($this->region != '') $result .="<ya:region dc:title=\"$this->region\"
    rdf:resource=\"$this->geourl". urlencode($this->region) . "\"/>\n";
    
    if ($this->city != '') $result .= "<ya:city dc:title=\"$this->city\"
    rdf:resource=\"$this->geourl". urlencode("$this->country, $this->city") . "\"/>\n";
    
    return $result;
  }
  
  public function gettitle() {
    return TLocal::$data['profile']['myprofile'];
  }
  
  public function GetTemplateContent() {
    $lang = &tlocal::$data['profile'];
  $result = "<h2>{$lang['myprofile']}</h2>\n";
    $result .= $this->GetStat();
    
  $result .= "<h2>{$lang['myself']}</h2>\n";
    $result .= $this->GetMyself();
    
  $result .= "<h2>{$lang['contact']}</h2>\n";
    $result .= $this->GetContacts();
    
  if ($this->bio != '') $result .= "<h2>{$lang['bio']}</h2>\n
    <p>$this->bio</p>\n";
    
  $result .= "<h2>{$lang['interests']}</h2>\n";
    $result .= $this->GetMyInterests();
    
    $result .= "<h2>" . TLocal::$data['default']['myfriends'] . "</h2>\n";
    $result .= $this->GetFriendsList();
    return $result;
  }
  
  private function GetStat() {
    $posts = tposts::instance();
    $manager = tcommentmanager::instance();
    return sprintf(tlocal::$data['profile']['statistic'], count($posts->archives), $CommentManager->count) . "\n";
  }
  
  private function GetMyself() {
    $lang = tlocal::$data['profile'];
    $result = array();
    if ($this->img != '') $result[] = "<img src=\"$this->img\" />";
  if ($this->nick != '') $result[] = "{$lang['nick']} $this->nick";
    if (($this->dateOfBirth != '')  && @sscanf($this->dateOfBirth , '%d-%d-%d', $y, $m, $d)) {
      $date = mktime(0,0,0, $m, $d, $y);
      $ldate = TLocal::date($date);
      $result[] = sprintf($lang['birthday'], $ldate);
    }
    
    $result[] = $this->gender == 'female' ? $lang['female'] : $lang['male'];
    
    if (!$this->country != '') $result[] = $this->country;
    if (!$this->region != '') $result[] = $this->region;
    if (!$this->city != '') $result[] = $this->city;
    return "<p>\n" . implode(", ", $result) . "</p>\n";
  }
  
  private function GetContacts() {
    $contacts = array(
    'icqChatID' => 'ICQ',
    'aimChatID' => 'AIM',
    'jabberID' => 'Jabber',
    'msnChatID' => 'MSN',
    'yahooChatID' => 'Yahoo',
    'mbox' => 'E-Mail'
    );
    $lang = TLocal::$data['profile'];
    $result = "<table>
    <thead>
    <tr>
  <th align=\"left\">{$lang['contactname']}</th>
  <th align=\"left\">{$lang['value']}</th>
    </tr>
    </thead>
    <tbody>\n";
    
    foreach ($contacts as $contact => $name) {
      if ($this->data[$contact] == '') continue;
      $result .= "<tr>
      <td align=\"left\">$name</td>
    <td align=\"left\">{$this->data[$contact]}</td>
      </tr>\n";
    }
    
    $result .= "</tbody >
    </table>";
    return $result;
  }
  
  private function GetMyInterests() {
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
  
  private function GetFriendsList() {
    $result = "<p>\n";
    $foaf = &TFoaf::instance();
    foreach ($foaf->items As $id => $friend) {
    $url = $foaf->redir ?"litepublisher::$options->url$foaf->redirlink{litepublisher::$options->q}friend=$id" : $friend['blog'];
    $result .= "<a href=\"$url\" rel=\"friend\">{$friend['nick']}</a>,\n";
    }
    $result .= "</p>\n";
    return $result;
  }
  
}//class

?>