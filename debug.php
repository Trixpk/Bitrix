<?php

ob_start();
echo "<pre>".PHP_EOL;
var_dump($arFields);
file_put_contents($_SERVER['DOCUMENT_ROOT'].'/dbg.txt', ob_get_clean(), FILE_APPEND);
