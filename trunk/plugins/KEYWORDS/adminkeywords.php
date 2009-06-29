<?php

class TAdminKeywords {

public function Getcontent() {
global $Options, $paths;
$result = '';
$dir = $paths['data'] . 'keywords' . DIRECTORY_SEPARATOR  ;
$selfdir = dirname(__file__) . DIRECTORY_SEPARATOR ;
$admin = &TAdminPlugins::Instance();
$html = &THtmlResource::Instance();
$html->LoadIni($selfdir . 'templates.ini');
$html->section = 'keywords';
TLocal::LoadIni($selfdir . 'about.ini');
TLocal::LoadIni($selfdir . "$Options->language.ini");
$lang = &TLocal::Instance();
$lang->section = 'keywords';

if (isset($_GET['filename'])) {
$filename = $_GET['filename'];
if (@file_exists($dir . $filename)) {
$content =file_get_contents($dir . $filename);
$content =$admin->ContentToForm($content);
eval('$result .= "'. $html->editform . '\n";');
return $result;
} else {
return $admin->notfound();
}
}

$page = isset($_GET['page'])  ? (int) $_GET['page'] : 1;
$from = 100 * ($page - 1);
   $filelist = TFiler::GetFileList($dir);
sort($filelist);
$count = ceil(count($filelist)/ 100);
eval('$s = "'. $html->pages . '\n";');
$pages = sprintf($s, $page, $count, count($filelist));
$pages .= $this->GetPages($page, $count);

    $filelist = array_slice($filelist, $from, 100, true);
$items = '';
$item = $html->item;
   foreach ($filelist as $filename) {
if (!preg_match('/^\d+?-\d+?\.php$/', $filename)) continue;
$content = file_get_contents($dir . $filename);
eval('$items .= "'. $item . '\n";');
   }
eval('$result .= "'. $html->form . '\n";');
return $admin->FixCheckall($result);
}

private function GetPages($page, $count) {
global $Options;
$result = "<p><a href='$Options->url/admin/plugins/keywords/'>1</a>\n";
for ($i = 2; $i < $count; $i++) {
$result .= "<a href='$Options->url/admin/plugins/keywords/{$Options->q}page=$i'>$i</a>|\n";
}
$result .= "</p>\n";
return $result;
}

public function ProcessForm() {
global $Options, $paths;
$dir = $paths['data'] . 'keywords' . DIRECTORY_SEPARATOR  ;
if (isset($_GET['filename'])) {
//edit file
file_put_contents($dir . $filename, $_POST['content']);
} else {
foreach ($_POST as $filename => $value) {
$filename = str_replace('_php', '.php', $filename);
if (preg_match('/^\d+?-\d+?\.php$/', $filename)) unlink($dir . $filename);
}
}
}

}//class

?>