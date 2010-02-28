<?php

class TLightbox extends TPlugin {

 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
$this->Data['speed'] = 7;
$this->Data['border'] = 10;
$this->Data['opacity'] = 0;
$this->Data['animate'] = true;
$this->Data['posts'] = false;
$this->Data['comments'] = false;
$this->Data['excerpt'] = false;
 }

public function Setposts($value) {
$this->SetValue('posts', $value);
}

public function Setcomments($value) {
$this->SetValue('comments', $value);
}

public function Setexcerpt($value) {
$this->SetValue('excerpt', $value);
}

private function SetValue($name, $value) {
if ($value != $this->Data[$name]) {
$this->Data[$name] = $value;
$events = array(
'posts' => 'OnPost',
'comments' => 'OnComment',
'excerpt' => 'OnExcerpt'
);

$event = $events[$name];
$filter = &TContentFilter::Instance();
if ($value) {
$filter->{$event} = $this->converter;
} else {
$filter->UnsubscribeEvent($event, get_class($this));
}

$this->Save();
}
}

public function Onhead() {
global $Options;
$url = $Options->url . '/plugins/lightbox';
$animate = $this->animate ? 'true' : 'false';
$result = "<link rel=\"stylesheet\" href=\"$url/css/lightbox.css\" type=\"text/css\" media=\"screen\" />\n";
		$result .= "\t<script type=\"text/javascript\">\n\t<!--\n\t\tvar fileLoadingImage = '$url/images/loading.gif';\n\t\tvar fileBottomNavCloseImage = '$url/images/closelabel.gif';\n\t\tvar resizeSpeed = {$this->speed};\n\t\tvar borderSize = {$this->border};\n\t\tvar animate = {$animate};\n\t\tvar overlayOpacity = {$this->opacity};\n\t//-->\n\t</script>\n";
		$result .= "\t<script type=\"text/javascript\" src=\"{$url}/js/prototype.js\"></script>\n";
		$result .= "\t<script type=\"text/javascript\" src=\"{$url}/js/effects.js\"></script>\n";
		$result .= "\t<script type=\"text/javascript\" src=\"{$url}/js/lightbox.js\"></script>\n";

return $result;
}

public function converter($s) {
		if(strpos($s, '##NOLIGHTBOX##') !== false) return $s;
			// actually i tested that those support single and double quotes.
			// yes, i really suck at regex, but it works. feel free to submit patches :)
			$link_reg = "/<a\s*.*?href\s*=\s*['\"]([^\"'>]*).*?>(.*?)<\/a>/i";
			$title_reg = '#title\s*=\s*[\'|"]*([^("|\')\s>]*)#';
			$rel_reg = '#rel\s*=\s*[\'|"]*([^("|\')\s>]*)#';
			$image_reg = '#^(.*?\.(jpg|jpeg|png|gif)$)#is';

			if(preg_match_all($link_reg, $s, $links)) {
				foreach($links[1] as $num => $link) {
// check all URLs
					if(preg_match($image_reg, $link)) {
// if the URL leads to an image
						$link_html = $links[0][$num];

						// remove nofollows, to avoid false positives in image links (comments, actually)
						$link_html = str_replace('rel="nofollow"', '', $link_html);
						$link_html = str_replace("rel='nofollow'", '', $link_html);

						// no rel-tag yet?
						if(!preg_match($rel_reg, $link_html)) {
							// any title-tag ?
							if(preg_match($title_reg, $link_html, $title)) {
								// series-title tag maybe ?
								if(preg_match('/{(.*)}/', $title[1], $series)) {
									$new_link_html = str_replace('<a ', '<a rel="lightbox['.$series[1].']" ', $link_html);
									$new_link_html = str_replace($series[0], '', $new_link_html);
								}else  {
// single lightbox
									$new_link_html = str_replace('<a ', '<a rel="lightbox" ', $link_html);
								}
							}else {
// single lightbox
								$new_link_html = str_replace('<a ', '<a rel="lightbox" ', $link_html);
							}

							// replace old <a href..> with new one
							$s = str_replace($link_html, $new_link_html, $s);
						}
					}
				} // loop
			}
		return $s;
	}

}//class
?>