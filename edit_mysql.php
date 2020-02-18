<?php
 
// Written by Mark Jackson @ MJDIGITAL
 
// http://www.mjdigital.co.uk/blog
$search = 'старый.ru'; // Ищем...
 
$replace = 'новый.ru'; // Меняем на...
 
$hostname = "localhost"; // Настройки базы данных
 
$database = "host1234";
 
$username = "host1234";
 
$password = "password";
//дальше ничего не трогаем
 
$queryType = 'replace';
 
$showErrors = true;
 
if($showErrors) {
 
error_reporting(E_ALL);
 
ini_set('error_reporting', E_ALL);
 
ini_set('display_errors',1);
 
}
 
$MJCONN = mysql_pconnect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR);
 
mysql_select_db($database,$MJCONN);
 
$table_sql = 'SHOW TABLES';
 
$table_q = mysql_query($table_sql,$MJCONN) or die("Cannot Query DB: ".mysql_error());
 
$tables_r = mysql_fetch_assoc($table_q);
 
$tables = array();
 
do{
 
$tables[] = $tables_r['Tables_in_'.strtolower($database)];
 
}while($tables_r = mysql_fetch_assoc($table_q));
 
$use_sql = array();
 
$rowHeading = ($queryType=='replace') ?
 
'Replacing \''.$search.'\' with \''.$replace.'\' in \''.$database."'\n\nSTATUS | ROWS AFFECTED | TABLE/FIELD (+ERROR)\n"
 
: 'Searching for \''.$search.'\' in \''.$database."'\n\nSTATUS | ROWS CONTAINING | TABLE/FIELD (+ERROR)\n";
 
$output = $rowHeading;
 
$summary = '';
 
foreach($tables as $table) {
 
$field_sql = 'SHOW FIELDS FROM '.$table;
 
$field_q = mysql_query($field_sql,$MJCONN);
 
$field_r = mysql_fetch_assoc($field_q);
 
do {
 
$field = $field_r['Field'];
 
$type = $field_r['Type'];
switch(true) {
 
case stristr(strtolower($type),'char'): $typeOK = true; break;
 
case stristr(strtolower($type),'text'): $typeOK = true; break;
 
case stristr(strtolower($type),'blob'): $typeOK = true; break;
 
case stristr(strtolower($field_r['Key']),'pri'): $typeOK = false; break;
 
default: $typeOK = false; break;
 
}
 
if($typeOK) {
 
$handle = $table.'_'.$field;
 
if($queryType=='replace') {
 
$sql[$handle]['sql'] = 'UPDATE '.$table.' SET '.$field.' = REPLACE('.$field.',\''.$search.'\',\''.$replace.'\')';
 
} else {
 
$sql[$handle]['sql'] = 'SELECT * FROM '.$table.' WHERE '.$field.' REGEXP(\''.$search.'\')';
 
}
 
$error = false;
 
$query = @mysql_query($sql[$handle]['sql'],$MJCONN) or $error = mysql_error();
 
$row_count = @mysql_affected_rows() or $row_count = 0;
 
$sql[$handle]['result'] = $query;
 
$sql[$handle]['affected'] = $row_count;
 
$sql[$handle]['error'] = $error;
 
$output .= ($query) ? 'OK ' : '-- ';
 
$output .= ($row_count>0) ? '<strong>'.$row_count.'</strong> ' : '<span style="color:#CCC">'.$row_count.'</span> ';
 
$fieldName = '`'.$table.'`.`'.$field.'`';
 
$output .= $fieldName;
 
$erTab = str_repeat(' ', (60-strlen($fieldName)) );
 
$output .= ($error) ? $erTab.'(ERROR: '.$error.')' : '';
 
$output .= "\n";
 
}
 
}while($field_r = mysql_fetch_assoc($field_q));
 
}
 
echo '<pre>';
 
echo $output."\n";
 
echo '<pre>';
 
?>
