<?php
//---------------------------------
// Author: Genie Jhang
// e-mail: geniejhang@majimak.com
//   Date: 2014. 11. 6
//---------------------------------

$filename = 'settings.json';
$settingsText = file_get_contents($filename);
$settings = json_decode($settingsText, true);

if (isset($_GET['read'])) {
  $readItem = $_GET['read'];

  $exist = array_key_exists($readItem, $settings);
  if ($exist)
    echo $settings[$readItem];
}

if (isset($_GET['write'])) {
  $writeItem = $_GET['write'];
  $value = $_GET['value'];

  $exist = array_key_exists($writeItem, $settings);
  if ($exist)
    $settings[$writeItem] = $value;
  
  $settingsFile = fopen('settings.json', 'w+') or die("Unable to open file!");
  fwrite($settingsFile, json_encode($settings));
  fclose($settingsFile);
}

// For use as exec
if ($argv[1] != '')
  echo $settings[$argv[1]];
?>
