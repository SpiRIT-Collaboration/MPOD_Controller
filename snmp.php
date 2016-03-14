<?php
//--------------------------------------
//       Author: Genie Jhang
//       e-mail: geniejhang@majimak.com
//         Date: 2013. 07. 17
// Last Updated: 2016. 03. 14
//
//      Version: 2.1hv
//--------------------------------------

include 'setting.php';

$ip = readSetting('IPAddress');

snmp_set_quick_print(TRUE);
snmp_read_mib('./WIENER-CRATE-MIB.txt');

// Getting Part
if (isset($_GET['get'])) {
  $get = $_GET['get'];

  if ($get == 'powerStatus') {
    $status = snmpget($ip, 'public', 'sysMainSwitch.0', 5000, 0);

    if ($status == 'off')
      echo 'Off';
    else
      echo 'On';
  }

  if ($get == 'channels') {
    $dataNameArray = Array('outputVoltage',
                           'outputCurrent',
                           'outputVoltageRiseRate',
                           'outputMeasurementCurrent',
                           'outputSupervisionMaxCurrent',
                           'outputMeasurementSenseVoltage',
                           'outputMeasurementTerminalVoltage',
                           'outputSupervisionMaxTerminalVoltage',
                           'outputSwitch');

    $numData = count($dataNameArray);

    $nameArray = readSetting('ActiveChannels');
    $numChannels = count($nameArray);

    $returnValue = array();
    $returnValue["numChannels"] = $numChannels;
    $returnValue["channels"] = array();

    for ($i = 0; $i < $numChannels; $i++) {
      $channel = array();
      $channel["name"] = strtolower($nameArray[$i]);
      $channel["data"] = array();

      for ($j = 0; $j < $numData; $j++) {
        $data = snmpget($ip, 'public', $dataNameArray[$j].'.'.strtolower($nameArray[$i]), 2000, 0);

        if ($j < $numData - 1) {
          $valueNunit = explode(' ', $data);
          if ($valueNunit[1] === 'A') {
            $valueNunit[0] = doubleval($valueNunit[0])*1000.;
            $valueNunit[1] = 'mA';
          }

          $channel['data'][$dataNameArray[$j]] = doubleval($valueNunit[0]);
          $channel['data'][$dataNameArray[$j].'Unit'] = $valueNunit[1];
        } else {
          $channel['data'][$dataNameArray[$j]] = $data;
        }
      }
      array_push($returnValue['channels'], $channel);
    }
    echo json_encode($returnValue);
  }

/* testing part
   $data = Array('3.6511 V', '1.3411 A', '101.1111 V/s', '0.1111 A', '1.1111 V', '0.1111 V' ,'Off');
   echo '{';
   echo '"numChannels":14,';
   echo '"channels":[';
   for ($i = 0; $i < 14; $i++) {
   echo '{';
   echo '"name":'.'"U'.($i < 7 ? $i : '10'.($i-7)).'",';
   echo '"data":{';
   for ($j = 0; $j < $numData; $j++) {
   if ($j < $numData - 1) {
   $valueNunit = explode(' ', $data[$j]);

   echo '"'.$dataNameArray[$j].'":'.$valueNunit[0].',';
   echo '"'.$dataNameArray[$j].'Unit":"'.$valueNunit[1].'",';
   } else {
   echo '"'.$dataNameArray[$j].'":"'.$data[$j].'"';
   }
   }
   echo '}}';
   if ($i + 1 != 14)
   echo ',';
   }
   echo ']';
   echo '}';
 */


  if (substr($get, 0, 1) == 'u') {
    $ch = $get;

    // Measurement Part
    $data = explode(' ', snmpget($ip, 'public', 'outputMeasurementSenseVoltage.'.$ch, 2000, 0));
    $MeasSV = $data[0];
    $MeasSVUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputMeasurementTerminalVoltage.'.$ch, 2000, 0));
    $MeasTV = $data[0];
    $MeasTVUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputMeasurementCurrent.'.$ch, 2000, 0));
    $MeasI = $data[0];
    $MeasIUnit = $data[1];
    $MeasHT = snmpget($ip, 'public', 'outputMeasurementTemperature.'.$ch, 2000, 0);
    $MeasPL = $MeasI*$MeasTV;
    $MeasPM = $MeasI*$MeasSV;

    // Nominal Part
    $data = explode(' ', snmpget($ip, 'public', 'outputVoltage.'.$ch, 2000, 0));
    $NomSV = $data[0];
    $NomSVUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputCurrent.'.$ch, 2000, 0));
    $NomCL = $data[0];
    $NomCLUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputVoltageRiseRate.'.$ch, 2000, 0));
    $NomRU = $data[0];
    $NomRUUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputVoltageFallRate.'.$ch, 2000, 0));
    $NomRD = $data[0];
    $NomRDUnit = $data[1];

    // Supervision Part
    $SupBehavior = snmpget($ip, 'public', 'outputSupervisionBehavior.'.$ch, 2000, 0);

    $data = explode(' ', snmpget($ip, 'public', 'outputSupervisionMinSenseVoltage.'.$ch, 2000, 0));
    $SupMinSV = 0; //$data[0];
    $SupMinSVUnit = $data[1];
    $SupMinSVFail = ($SupBehavior & 0x3);
    $data = explode(' ', snmpget($ip, 'public', 'outputSupervisionMaxSenseVoltage.'.$ch, 2000, 0));
    $SupMaxSV = 0; //$data[0];
    $SupMaxSVUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputConfigMaxSenseVoltage.'.$ch, 2000, 0));
    $SupMaxSVMax = $data[0];
    $SupMaxSVMaxUnit = $data[1];
    $SupMaxSVFail = (($SupBehavior & 0xc) >> 2);
    $data = explode(' ', snmpget($ip, 'public', 'outputSupervisionMaxTerminalVoltage.'.$ch, 2000, 0));
    $SupMaxTV = $data[0];
    $SupMaxTVUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputConfigMaxTerminalVoltage.'.$ch, 2000, 0));
    $SupMaxTVMax = $data[0];
    $SupMaxTVMaxUnit = $data[1];
    $SupMaxTVFail = (($SupBehavior & 0x30) >> 4);
    $data = explode(' ', snmpget($ip, 'public', 'outputSupervisionMaxCurrent.'.$ch, 2000, 0));
    $SupMaxI = $data[0];
    $SupMaxIUnit = $data[1];
    $data = explode(' ', snmpget($ip, 'public', 'outputConfigMaxCurrent.'.$ch, 2000, 0));
    $SupMaxIMax = $data[0];
    $SupMaxIMaxUnit = $data[1];
    $SupMaxIFail = (($SupBehavior & 0xc0) >> 6);

    /*
       $data = explode(' ', snmpget($ip, 'public', 'outputSupervisionMaxTemperature.'.$ch, 2000, 0));
       $SupMaxT = 0; //$data[0];
       $SupMaxTUnit = "none";
       $data = explode(' ', snmpget($ip, 'public', 'outputConfigMaxTemperature.'.$ch, 2000, 0));
       $SupMaxTMax = 0; //$data[0];
       $SupMaxTMaxUnit = "none"; //$data[1];
       $SupMaxTFail = (($SubBehavior & 0x300) >> 8);
       $data = explode(' ', snmpget($ip, 'public', 'outputSupervisionMaxPower.'.$ch, 2000, 0));
       $SupMaxP = $data[0];
       $SupMaxPUnit = $data[1];
       $data = explode(' ', snmpget($ip, 'public', 'outputConfigMaxPower.'.$ch, 2000, 0));
       $SupMaxPMax = 0; //$data[0];
       $SupMaxPMaxUnit = "none";//$data[1];
       $SupMaxPFail = (($SubBehavior & 0xc00) >> 10);
    */
    /* Test Code
       $MeasSV = 3.1;
       $MeasSVUnit = 'V';
       $MeasTV = 3.1;
       $MeasTVUnit = 'V';
       $MeasI = 1.0;
       $MeasIUnit = 'A';
       $MeasHT = 20.0;
       $MeasHTUnit = 'C';
       $MeasPL = $MeasI*$MeasTV;
       $MeasPM = $MeasI*$MeasSV;

       $NomSV = 3.2;
       $NomSVUnit = 'V';
       $NomCL = 3.3;
       $NomCLUnit = 'A';
       $NomRU = 21.0;
       $NomRUUnit = 'V/s';
       $NomRD = 22.0;
       $NomRDUnit = 'V/s';
       $NomNRSO = 1;
       $NomR = 1;

       $SupMinSV = 8.0;
       $SupMinSVUnit = 'V';
       $SupMinSVFail = 0;
       $SupMaxSV = 8.1;
       $SupMaxSVUnit = 'V';
       $SupMaxSVMax = 8.3;
       $SupMaxSVMaxUnit = 'V';
       $SupMaxSVFail = 1;
       $SupMaxTV = 8.4;
       $SupMaxTVUnit = 'V';
       $SupMaxTVMax = 8.5;
       $SupMaxTVMaxUnit = 'V';
       $SupMaxTVFail = 2;
       $SupMaxI = 3.4;
       $SupMaxIUnit = 'A';
       $SupMaxIMax = 3.5;
       $SupMaxIMaxUnit = 'A';
       $SupMaxIFail = 3;
       $SupMaxT = 100.0;
       $SupMaxTUnit = 'C';
       $SupMaxTMax = 200.0;
       $SupMaxTMaxUnit = 'C';
       $SupMaxTFail = 0;
       $SupMaxP = 210.0;
       $SupMaxPUnit = 'W';
       $SupMaxPMax = 211.0;
       $SupMaxPMaxUnit = 'W';
       $SupMaxPFail = 1;
     */

    echo '{';
    echo '"name":"'.$ch.'",';
    echo '"data":{';
    echo '"outputMeasurementSenseVoltage":'.$MeasSV.',';
    echo '"outputMeasurementSenseVoltageUnit":"'.$MeasSVUnit.'",';
    echo '"outputMeasurementTerminalVoltage":'.$MeasTV.',';
    echo '"outputMeasurementTerminalVoltageUnit":"'.$MeasTVUnit.'",';
    if ($MeasIUnit === 'A') {
      $MeasI *= 1000.;
      $MeasIUnit = 'mA';
    }
    echo '"outputMeasurementCurrent":'.$MeasI.',';
    echo '"outputMeasurementCurrentUnit":"'.$MeasIUnit.'",';
    echo '"outputMeasurementTemperature":'.$MeasHT.',';
    echo '"outputMeasurementPowerLoad":'.$MeasPL.',';
    echo '"outputMeasurementPowerModule":'.$MeasPM.',';
    echo '"outputVoltage":'.$NomSV.',';
    echo '"outputVoltageUnit":"'.$NomSVUnit.'",';
    if ($NomCLUnit === 'A') {
      $NomCL *= 1000.;
      $NomCLUnit = 'mA';
    }
    echo '"outputCurrentLimit":'.$NomCL.',';
    echo '"outputCurrentLimitUnit":"'.$NomCLUnit.'",';
    echo '"outputVoltageRiseRate":'.$NomRU.',';
    echo '"outputVoltageRiseRateUnit":"'.$NomRUUnit.'",';
    echo '"outputVoltageFallRate":'.$NomRD.',';
    echo '"outputVoltageFallRateUnit":"'.$NomRDUnit.'",';
    echo '"outputSupervisionMinSenseVoltage":'.$SupMinSV.',';
    echo '"outputSupervisionMinSenseVoltageUnit":"'.$SupMinSVUnit.'",';
    echo '"outputFailureMinSenseVoltage":'.$SupMinSVFail.',';
    echo '"outputSupervisionMaxSenseVoltage":'.$SupMaxSV.',';
    echo '"outputSupervisionMaxSenseVoltageUnit":"'.$SupMaxSVUnit.'",';
    echo '"outputConfigMaxSenseVoltage":'.$SupMaxSVMax.',';
    echo '"outputConfigMaxSenseVoltageUnit":"'.$SupMaxSVMaxUnit.'",';
    echo '"outputFailureMaxSenseVoltage":'.$SupMaxSVFail.',';
    echo '"outputSupervisionMaxTerminalVoltage":'.$SupMaxTV.',';
    echo '"outputSupervisionMaxTerminalVoltageUnit":"'.$SupMaxTVUnit.'",';
    echo '"outputConfigMaxTerminalVoltage":'.$SupMaxTVMax.',';
    echo '"outputConfigMaxTerminalVoltageUnit":"'.$SupMaxTVMaxUnit.'",';
    echo '"outputFailureMaxTerminalVoltage":'.$SupMaxTVFail.',';
    if ($SupMaxIUnit === 'A') {
        $SupMaxI *= 1000.;
        $SupMaxIUnit = 'mA';
    }
    echo '"outputSupervisionMaxCurrent":'.$SupMaxI.',';
    echo '"outputSupervisionMaxCurrentUnit":"'.$SupMaxIUnit.'",';
    if ($SupMaxIMaxUnit === 'A') {
        $SupMaxIMax *= 1000.;
        $SupMaxIMaxUnit = 'mA';
    }
    echo '"outputConfigMaxCurrent":'.$SupMaxIMax.',';
    echo '"outputConfigMaxCurrentUnit":"'.$SupMaxIMaxUnit.'",';
    echo '"outputFailureMaxCurrent":'.$SupMaxIFail;
    /*
       echo '"outputFailureMaxCurrent":'.$SupMaxIFail.',';
       echo '"outputSupervisionMaxTemperature":'.$SupMaxT.',';
       echo '"outputSupervisionMaxTemperatureUnit":"'.$SupMaxTUnit.'",';
       echo '"outputConfigMaxTemperature":'.$SupMaxTMax.',';
       echo '"outputConfigMaxTemperatureUnit":"'.$SupMaxTMaxUnit.'",';
       echo '"outputFailureMaxTemperature":'.$SupMaxTFail.',';
       echo '"outputSupervisionMaxPower":'.$SupMaxP.',';
       echo '"outputSupervisionMaxPowerUnit":"'.$SupMaxPUnit.'",';
       echo '"outputConfigMaxPower":'.$SupMaxPMax.',';
       echo '"outputConfigMaxPowerUnit":"'.$SupMaxPMaxUnit.'",';
       echo '"outputFailureMaxPower":'.$SupMaxPFail;
    */
    echo '}}';
  }
}

// Setting Part
if (isset($_GET['set'])) {
  $set = $_GET['set'];
  $type = $_GET['type'];
  $value = $_GET['value'];

  $explodedSet = explode('.', $set);
  $parameter = $explodedSet[0];
  $channel = $explodedSet[1];
  if ($parameter == 'outputNoRampAtSwitchOff') {
    $set = 'outputUserConfig.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0x3e);
    $value = ($oldvalue | $value);
  } else if ($parameter == 'outputRegulationMode') {
    $set = 'outputUserConfig.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0x39);
    $value = ($oldvalue | ($value << 1));
  } else if ($parameter == 'internalSenseUse') {
    $set = 'outputUserConfig.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0x37);
    $value = ($oldvalue | ($value << 3));
  } else if ($parameter == 'outputFailureMinSenseVoltage') {
    $set = 'outputSupervisionBehavior.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0xfffc);
    $value = ($oldvalue | $value);
    echo $channel." ".$parameter." ".$value;
  } else if ($parameter == 'outputFailureMaxSenseVoltage') {
    $set = 'outputSupervisionBehavior.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0xfff3);
    $value = ($oldvalue | ($value << 2));
    echo $channel." ".$parameter." ".$value;
  } else if ($parameter == 'outputFailureMaxTerminalVoltage') {
    $set = 'outputSupervisionBehavior.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0xffcf);
    $value = ($oldvalue | ($value << 4));
    echo $channel." ".$parameter." ".$value;
  } else if ($parameter == 'outputFailureMaxCurrent') {
    $set = 'outputSupervisionBehavior.'.$channel;
    $oldvalue = snmpget($ip, 'public', $set, 5000, 0);
    $oldvalue = ($oldvalue & 0xff3f);
    $value = ($oldvalue | ($value << 6));
    echo $channel." ".$parameter." ".$value;
  }

  if ($set == 'powerSwitch') {
    $value = ($value == 'On' ? 1 : 0);
    snmpset($ip, 'private', 'sysMainSwitch.0', 'i', $value, 5000, 0);
    echo "snmpset(".$ip.", 'private', 'sysMainSwitch.0', 'i', ".$value.", 5000, 0);";
  } else {
    snmpset($ip, 'guru', $set, $type, $value, 5000, 0);
  }
}
?>
