<?php

function Update255() {
$s = '<pre>Обновление не закончено. Теперь вы обязаны сделать следующее:
1. Обязательно загрузить в корневую папку сайта файл <a href="http://litepublisher.googlecode.com/svn/trunk/update255.php">update255.php</a>
2. Набрать адрес скрипта в браузере (site.ru/update255.php)
3. Обновить в корневой папке файл <a href="http://litepublisher.googlecode.com/svn/trunk/index.php">index.php</a>
4. Только после этого вы сможете продолжить обновление сайта.
</pre>';
eval( '?>'. TTemplate::SimpleHtml($s));
exit();
}
?>