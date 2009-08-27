<?php
echo "<pre>\n";

function PHPComment(&$s) {
  $s = str_replace('*/', '**//*/', $s);
  return "<?php /* $s */ ?>";
}

function PHPUncomment(&$s) {
  $s = substr($s, 9, strlen($s) - 9 - 6);
  return str_replace('**//*/', '*/', $s);
}

function SafeSaveFile($BaseName, &$Content) {
  $TmpFileName = $BaseName.'.tmp.php';
  if(!file_put_contents($TmpFileName, $Content))  return false;
  @chmod($TmpFileName , 0666);
  $FileName = $BaseName.'.php';
  if (@file_exists($FileName)) {
    $BakFileName = $BaseName . '.bak.php';
    @unlink($BakFileName);
    rename($FileName, $BakFileName);
  }
  return rename($TmpFileName, $FileName);
}

  if (version_compare(PHP_VERSION, '5.2', '<')) {
   echo 'Lite Publisher requires PHP 5.2 or later. You are using PHP ' . PHP_VERSION ;
   exit;
  }

//begin config
$domain = strtolower(trim($_SERVER['HTTP_HOST']));
if (substr($domain, 0, 4) == 'www.') $domain = substr($domain, 4);
$domain = trim($domain, '.:/\,;');
$paths = array('home' => dirname(__file__). DIRECTORY_SEPARATOR);
$paths['lib'] = $paths['home'] .'lib'. DIRECTORY_SEPARATOR;
$paths['libinclude'] = $paths['lib'] . 'include'. DIRECTORY_SEPARATOR;
$paths['languages'] = $paths['lib'] . 'languages'. DIRECTORY_SEPARATOR;
$paths['plugins'] =  $paths['home'] . 'plugins' . DIRECTORY_SEPARATOR;
$paths['themes'] = $paths['home'] . 'themes'. DIRECTORY_SEPARATOR;
$paths['data'] = $paths['home'] . 'data'. DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR;
$paths['cache'] = $paths['home'] . 'cache'. DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR;
$paths['files'] = $paths['home'] . 'files' . DIRECTORY_SEPARATOR;
$paths['backup'] = $paths['home'] . 'backup' . DIRECTORY_SEPARATOR;

define('secret', 'сорок тыс€ч обезъ€н в жопу сунули банан');
$microtime = microtime();
require_once($paths['lib'] . 'dataclass.php');
require_once($paths['lib'] . 'eventclass.php');
require_once($paths['lib'] . 'itemsclass.php');
require_once('lib/updater.php');
require_once('lib/optionsclass.php');
require_once('lib/localclass.php');
require_once('lib/remoteadminclass.php');
$Options = new TOptions();
        $updater = new TUpdater();
echo $updater->DownloadLatest();

//$classes = TClasses::Instance();

    $ini = parse_ini_file($paths['libinclude'] . 'classes.ini', true);
$s = file_get_contents($paths['data'].'classes.php');
@unlink($paths['data'].'classes.php');
@rename($paths['data'].'classes.php', $paths['data'].'classes.bak.php');
      $s = PHPUncomment($s);

class TTempClasses extends TItems {
  public $classes;
  public $instances;

  protected function CreateData() {
    parent::CreateData();
$this->basename = 'tempclasses';
    $this->AddDataMap('classes', array());
    $this->instances = array();
  }

}//class

$classes = new TTempClasses ();
$classes->Lock();
$classes->basename = 'classes';
$classes->items = unserialize($s);
$classes->classes = $ini['classes'];
//$classes->Add('TManifest', 'manifest.php');
$classes->Unlock();

?>