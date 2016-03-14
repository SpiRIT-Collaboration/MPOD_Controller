<?php
//--------------------------------------
//       Author: Genie Jhang
//       e-mail: geniejhang@majimak.com
//         Date: 2016. 03. 14
// Last Updated: 2016. 03. 14
//
//      Version: 2.1
//--------------------------------------

include 'setting.php';

$ip = readSetting('IPAddress');
$activeModules = readSetting('ActiveModules');
$activeChannels = array();

$filename = 'settings.json';
$settingsText = file_get_contents($filename);
$settings = json_decode($settingsText, true);

snmp_set_quick_print(TRUE);
snmp_read_mib('./WIENER-CRATE-MIB.txt');

$channelList = snmpwalk($ip, 'public', 'outputName');
$pluggedIndexList = snmpwalk($ip, 'public', 'moduleIndex');
$pluggedList = snmpwalk($ip, 'public', 'moduleDescription');
$pluggedList = str_replace('"', '', $pluggedList);
$pluggedList = str_replace(' ', '', $pluggedList);
foreach ($pluggedList as &$entry) {
  $entry = explode(',', $entry);
}

$activeModuleIndex = 0;
$pluggedIndexListIndex = 0;
while (1) {
  if ($activeModules[$activeModuleIndex] === $pluggedIndexList[$pluggedIndexListIndex]) {
    for ($iCh = 0; $iCh < $pluggedList[$pluggedIndexListIndex][2]; $iCh++)
      array_push($activeChannels, array_shift($channelList));

    if ($activeModuleIndex + 1 != count($activeModules))
      $activeModuleIndex++;
    else
      break;

    if ($pluggedIndexListIndex + 1 != count($pluggedIndexList))
      $pluggedIndexListIndex++;
    else
      break;
  } else {
    for ($iCh = 0; $iCh < $pluggedList[$pluggedIndexListIndex][2]; $iCh++)
      array_shift($channelList);

    if ($pluggedIndexListIndex + 1 != count($pluggedIndexList)) {
      $pluggedIndexListIndex++;
    } else {
      break;
    }
  }
}

$settings['ActiveChannels'] = $activeChannels;
$settingsFile = fopen($filename, 'w+') or die("Unable to open file!");
fwrite($settingsFile, json_encode($settings));
fclose($settingsFile);
?>
