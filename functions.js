//---------------------------------
// Author: Genie Jhang
// e-mail: geniejhang@majimak.com
//   Date: 2013. 07. 17
//---------------------------------

{
  var mapping;
  var UAOrder = new Array(48);

  // Initialization Part ---------------------------------------------------
  function init() {
    readSetting("IPAddress", printIPAddress);

    mapping = (function() {
                  var json = null;
                  $.ajax({
                    'async': false,
                    'global': false,
                    'url': "mapping.json",
                    'dataType': "json",
                    'success': function (data) {
                     json = data;
                  }
                });
                return json;
              })();

    var uOrder = 0;
    for (var ua in mapping) {
      var order = parseInt(ua.substr(1));
      order = parseInt(order/100)*8 + parseInt(order)%100;
      UAOrder[order] = uOrder++;
    }

    printPowerStatus("Off");
    getData("powerStatus", printPowerStatus);
    setInterval(function() {getData("powerStatus", printPowerStatus);}, 5001);
  }

  var timerForChannels;
  var timerForGC;
  var powerStatusForChannels = "Off";
  function initChannels() {
    getData("channels", buildChannelList);
    readSetting("selectedGroup", printGroupController);
    timerForChannels = setInterval(function() {getData("channels", buildChannelList);}, 5000);
    timerForGC = setInterval(function() {readSetting("SelectedGroup", printGroupController);}, 5000);
  }
  // -----------------------------------------------------------------------

  // Change IP Part --------------------------------------------------------
  function printIPAddress(ipAddress) {
    document.getElementById('ipAddress').value = ipAddress;
  } 

  function changeIP() {
    var newIP = document.getElementById('ipAddress').value; 
    writeSetting("IPAddress", newIP);
  } 
  // -----------------------------------------------------------------------

  // Power Switch Part -----------------------------------------------------
  function printPowerStatus(status) {
    var title = "Crate Main Power: ";
    var textColor = (status == "On" ? "#00ff00" : "#ff0000");
    var textStatus = "<font color='" + textColor + "'><b>" + status + "</b></font>";
    var buttonStatus = (status == "Off" ? "On" : "Off");
    var button = " <input type='button' value='Turn " + buttonStatus + "' onclick='setPowerSwitch(\"" + buttonStatus + "\");'>";

    var powerStatus = document.getElementById("cratePower");
    powerStatus.innerHTML = title + textStatus + button;

    if (status != powerStatusForChannels) {
      powerStatusForChannels = status;
      if (powerStatusForChannels == "On")
        initChannels()
      else {
        clearTimeout(timerForChannels);
        clearTimeout(timerForGC);
        document.getElementById('channelList').innerHTML = "Turn On the Crate to See the Channel List";
        document.getElementById('channelController').display = "none";
        document.getElementById('groupController').display = "none";
      }
    }
  }

  function setPowerSwitch(status) {
    setData("powerSwitch", "i", status);
    getData("powerStatus", printPowerStatus);
  }
  // -----------------------------------------------------------------------

  // Channel Part ----------------------------------------------------------
  function buildChannelList(rawdata) {
    var dataNameArray = new Array('outputVoltage',
                                  'outputCurrent',
                                  'outputVoltageRiseRate',
                                  'outputMeasurementCurrent',
                                  'outputMeasurementSenseVoltage',
                                  'outputMeasurementTerminalVoltage',
                                  'outputSupervisionMaxTerminalVoltage',
                                  'outputGroup',
                                  'outputSwitch');

    var data = JSON.parse(rawdata);
    var numChannels = data.numChannels;
    var channelsData = data.channels;

    //create map here.

    var channelList = "";
    for (var i = 0; i < numChannels; i++) {
      var isOn = (channelsData[UAOrder[i]].data.outputSwitch == "on");
      var currentMin = document.getElementById('currentMin').value;
      var currentMax = document.getElementById('currentMax').value;

      channelList += "<tr align='center'" + (i%2 == 0 ? "" : " bgcolor='#ccffff'") + "><td>";
      channelList += "<input type='checkbox' name='" + channelsData[UAOrder[i]].name + "' >";
      channelList += "</td><td>";
      channelList += "<channel onclick='getData(\"" + channelsData[UAOrder[i]].name + "\", initializeChannelController);'>" + mapping[capitalize(channelsData[UAOrder[i]].name)] + " (" + capitalize(channelsData[UAOrder[i]].name) + ")</channel>";
      channelList += "</td><td>";
      channelList += numberFormat(channelsData[UAOrder[i]].data.outputVoltage);
      channelList += " ";
      channelList += channelsData[UAOrder[i]].data.outputVoltageUnit;
      /*
         channelList += "</td><td>";
         channelList += channelsData[UAOrder[i]].data.outputVoltageRiseRate
         channelList += " ";
         channelList += channelsData[UAOrder[i]].data.outputVoltageRiseRateUnit;
       */
      channelList += "</td><td>";
      channelList += numberFormat(channelsData[UAOrder[i]].data.outputMeasurementSenseVoltage);
      channelList += " ";
      channelList += channelsData[UAOrder[i]].data.outputMeasurementSenseVoltageUnit;

      var style = "color:black";
      var current = numberFormat(channelsData[UAOrder[i]].data.outputMeasurementCurrent);
      if (isOn) {
        if (current < currentMin)
          style = "color:black";
        else if (current > currentMin && current < currentMax)
          style = "color:green";
        else
          style = "color:red; font-weight:bold";
      }

      channelList += "</td><td style='" + style + "'>";
      channelList += current;
      channelList += " ";
      channelList += channelsData[UAOrder[i]].data.outputMeasurementCurrentUnit;
      channelList += "</td><td>";
      channelList += numberFormat(channelsData[UAOrder[i]].data.outputMeasurementTerminalVoltage);
      channelList += " ";
      channelList += channelsData[UAOrder[i]].data.outputMeasurementTerminalVoltageUnit;
      channelList += "</td><td>";
      channelList += numberFormat(channelsData[UAOrder[i]].data.outputSupervisionMaxTerminalVoltage);
      channelList += " ";
      channelList += channelsData[UAOrder[i]].data.outputSupervisionMaxTerminalVoltageUnit;
      channelList += "</td><td><select onchange='setData(\"outputGroup." + channelsData[UAOrder[i]].name + "\", \"i\", value);'>";
      for (var j = 1; j < 64; j++) {
        if (channelsData[UAOrder[i]].data.outputGroup == j)
          channelList += "<option value='" + j + "' selected>" + j + "</option>";
        else
          channelList += "<option value='" + j + "'>" + j + "</option>";
      }
      channelList += "</select></td><td>";
      channelList += "<input type='button' class='btn btn-" + (isOn ? "success" : "danger") + " btn-xs' value='" + capitalize(channelsData[UAOrder[i]].data.outputSwitch) + "' onclick='setData(\"outputSwitch." + channelsData[UAOrder[i]].name + "\", \"i\", " + (isOn ? "0" : "1") + ");'>";
      /*            if (channelsData[UAOrder[i]].data.outputSwitch == "on") {
                    channelList += "<input type='button' value='On' onclick='setData(\"outputSwitch." + channelsData[UAOrder[i]].name + "\", \"i\", \"1\");'>";
                    channelList += "<input type='button' value='Off' disabled>";
                    } else {
                    channelList += "<input type='button' value='On' disabled>";
                    channelList += "<input type='button' value='Off' onclick='setData(\"outputSwitch." + channelsData[UAOrder[i]].name + "\", \"i\", \"0\");'>";
                    }
       */
      channelList += "</td></tr>";
    }

    printChannelList(channelList);
  }

  function printChannelList(channelList) {
    //        var header = "<table cellspacing='0' cellpadding='4px'><tr align='center' bgcolor='#ccffff'><td width='50px'>Name</td><td width='80px'>Voltage</td><td width='80px'>Current</td><td width='120px'>V Rise Rate</td><td width='90px'>Measured<br>Sense V</td><td width='90px'>Measured<br>Current</td><td width='90px'>Measured<br>Terminal V</td><td width='60px'>Switch</td></tr>";
    var header = "<table cellspacing='0' cellpadding='4px'><tr align='center' bgcolor='#ccffff'><td width='20px'><input type='checkbox'></td><td width='120px'>Name</td><td width='80px'>Voltage</td><td width='90px'>Measured<br>Sense V</td><td width='90px'>Measured<br>Current</td><td width='90px'>Measured<br>Terminal V</td><td width='90px'>Maximum<br>Terminal V</td><td width='60px'>Group</td><td width='60px'>Switch</td></tr>";
    var footer = "</table>";

    var channelListTable = document.getElementById("channelList");
    channelListTable.innerHTML = header + channelList + footer;
  }
  // -----------------------------------------------------------------------

  // Group Controller Part -------------------------------------------------
  function printGroupController(selectedGroup) {
    document.getElementById("groupController").style.display = "";

    var groupList = "<select id='selectedGroup' onchange='writeSetting(\"SelectedGroup\", value);'>";
    for (var iGroup = 0; iGroup < 64; iGroup++) {
      if (iGroup != 0)
        groupList += "<option value='" + iGroup + "'" + (selectedGroup == iGroup ? "selected" : "") + ">" + iGroup + "</option>";
      else
        groupList += "<option value='" + iGroup + "'" + (selectedGroup == iGroup ? "selected" : "") + ">All</option>";
    }
    groupList += "</select>";
    document.getElementById("GC_groupList").innerHTML = groupList;
  }

  function groupOn() {
    var selectedGroup = document.getElementById("selectedGroup").value;

    setData("groupsSwitch." + selectedGroup, "i", 1);
  }

  function groupOff() {
    var selectedGroup = document.getElementById("selectedGroup").value;

    setData("groupsSwitch." + selectedGroup, "i", 0);
  }

  function groupSet(setting) {
    if (setting == "resetEmergencyOff") {
      var button = confirm("Leave \"Emergency Off\" state.\nYou must \"Clear Events\" to use the group!");
    } else if (setting == "setEmergencyOff") {
      var button = confirm("Set \"Emergency Off\" state.\nThis will switch off the group without ramping!");
    } else if (setting == "enableKill") {
      var button = confirm("Enable kill of the group?");
    } else if (setting == "disableKill") {
      var button = confirm("Disable kill of the group?");
    } else if (setting == "clearEvents") {
      var button = confirm("Clear failure state of the group?");
    }
  }
  // -----------------------------------------------------------------------

  // Channel Controller Part------------------------------------------------
  var timerForChannelController;

  function setViewChannelController(channel, hidden) {
    clearTimeout(timerForChannelController);

    var title = document.getElementById("title");
    title.innerHTML = "<b>" + mapping[capitalize(channel)] + "(" + capitalize(channel) + ")</b> Output Configuration";

    var channelController = document.getElementById("channelController");
    channelController.style.position = "fixed";
    channelController.style.left = "805px";
    channelController.style.top = "100px";
    channelController.style.zIndex = 10;
    channelController.style.display = hidden;

    if (hidden == "")
      timerForChannelController = setInterval(function() {getData(channel, refreshChannelController);}, 5000);
  }

  function initializeChannelController(rawdata) {
    var data = JSON.parse(rawdata);
    var channelName = data.name;
    var channelData = data.data;

    document.getElementById("ch").value = channelName;

    document.getElementById("MeasSV").innerHTML = numberFormat(channelData.outputMeasurementSenseVoltage);
    document.getElementById("MeasTV").innerHTML = numberFormat(channelData.outputMeasurementTerminalVoltage);
    document.getElementById("MeasI").innerHTML = numberFormat(channelData.outputMeasurementCurrent);
    document.getElementById("MeasHT").innerHTML = numberFormat(channelData.outputMeasurementTemperature);
    document.getElementById("MeasPL").innerHTML = numberFormat(channelData.outputMeasurementPowerLoad);
    document.getElementById("MeasPM").innerHTML = numberFormat(channelData.outputMeasurementPowerModule);

    document.getElementById("NomSV").value = numberFormat(channelData.outputVoltage);
    document.getElementById("NomSVMax").innerHTML = numberFormat(channelData.outputSupervisionMaxSenseVoltage);
    document.getElementById("NomCL").value = numberFormat(channelData.outputCurrentLimit);
    document.getElementById("NomCLMax").innerHTML = numberFormat(channelData.outputSupervisionMaxCurrent);
    document.getElementById("NomRU").value = numberFormat(channelData.outputVoltageRiseRate);
    document.getElementById("NomRD").value = numberFormat(channelData.outputVoltageFallRate);
    document.getElementById("NomNRSO").checked = channelData.outputNoRampAtSwitchOff;
    document.getElementsByName("NomR")[channelData.outputRegulationMode].checked = true;
    document.getElementById("IntSen").checked = channelData.internalSenseUse;

    document.getElementById("SupMinSV").value = numberFormat(channelData.outputSupervisionMinSenseVoltage);
    document.getElementById("SupMinSVFail").value = channelData.outputFailureMinSenseVoltage;
    document.getElementById("SupMaxSV").value = numberFormat(channelData.outputSupervisionMaxSenseVoltage);
    document.getElementById("SupMaxSVMax").innerHTML = numberFormat(channelData.outputConfigMaxSenseVoltage);
    document.getElementById("SupMaxSVFail").value = channelData.outputFailureMaxSenseVoltage;
    document.getElementById("SupMaxTV").value = numberFormat(channelData.outputSupervisionMaxTerminalVoltage);
    document.getElementById("SupMaxTVMax").innerHTML = numberFormat(channelData.outputConfigMaxTerminalVoltage);
    document.getElementById("SupMaxTVFail").value = channelData.outputFailureMaxTerminalVoltage;
    document.getElementById("SupMaxI").value = numberFormat(channelData.outputSupervisionMaxCurrent);
    document.getElementById("SupMaxIMax").innerHTML = numberFormat(channelData.outputConfigMaxCurrent);
    document.getElementById("SupMaxIFail").value = channelData.outputFailureMaxCurrent;
    //        document.getElementById("SupMaxP").value = numberFormat(channelData.outputSupervisionMaxTemperature);
    //        document.getElementById("SupMaxPMax").innerHTML = numberFormat(channelData.outputConfigMaxTemperature);
    //        document.getElementById("SupMaxPFail").value = channelData.outputFailureMaxTemperature;
    //        document.getElementById("SupMaxT").value = numberFormat(channelData.outputSupervisionMaxPower);
    //        document.getElementById("SupMaxTMax").innerHTML = numberFormat(channelData.outputConfigMaxPower);
    //        document.getElementById("SupMaxTFail").value = channelData.outputFailureMaxPower;

    setViewChannelController(channelName, "");
  }

  function refreshChannelController(rawdata) {
    var data = JSON.parse(rawdata);
    var channelName = data.name;
    var channelData = data.data;

    document.getElementById("MeasSV").innerHTML = numberFormat(channelData.outputMeasurementSenseVoltage);
    document.getElementById("MeasTV").innerHTML = numberFormat(channelData.outputMeasurementTerminalVoltage);
    document.getElementById("MeasI").innerHTML = numberFormat(channelData.outputMeasurementCurrent);
    document.getElementById("MeasHT").innerHTML = numberFormat(channelData.outputMeasurementTemperature);
    document.getElementById("MeasPL").innerHTML = numberFormat(channelData.outputMeasurementPowerLoad);
    document.getElementById("MeasPM").innerHTML = numberFormat(channelData.outputMeasurementPowerModule);

    document.getElementById("NomSVMax").innerHTML = numberFormat(channelData.outputSupervisionMaxSenseVoltage);
    document.getElementById("NomCLMax").innerHTML = numberFormat(channelData.outputSupervisionMaxCurrent);
  }

  function updateChannel() {
    var channelName = document.getElementById("ch").value;

    setData("outputVoltage." + channelName, "F", document.getElementById("NomSV").value);
    setData("outputCurrentLimit." + channelName, "F", document.getElementById("NomCL").value);
    setData("outputVoltageRiseRate." + channelName, "F", document.getElementById("NomRU").value);
    setData("outputVoltageFallRate." + channelName, "F", document.getElementById("NomRD").value);
    setData("outputNoRampAtSwitchOff." + channelName, "i", (document.getElementById("NomNRSO").checked == true ? 1 : 0)); // modified

    for (var i = 0; i < 3; i++)
      if (document.getElementsByName("NomR")[i].checked == true)
        setData("outputRegulationMode." + channelName, "i", i);

    setData("internalSenseUse." + channelName, "i", (document.getElementById("IntSen").checked == true ? 1 : 0));
    setData("outputSupervisionMinSenseVoltage." + channelName, "F", document.getElementById("SupMinSV").value);
    setData("outputFailureMinSenseVoltage." + channelName, "i", document.getElementById("SupMinSVFail").value);
    setData("outputSupervisionMaxSenseVoltage." + channelName, "F", document.getElementById("SupMaxSV").value);
    setData("outputFailureMaxSenseVoltage." + channelName, "i", document.getElementById("SupMaxSVFail").value);
    setData("outputSupervisionMaxTerminalVoltage." + channelName, "F", document.getElementById("SupMaxTV").value);
    setData("outputFailureMaxTerminalVoltage." + channelName, "i", document.getElementById("SupMaxTVFail").value);
    setData("outputSupervisionMaxCurrent." + channelName, "F", document.getElementById("SupMaxI").value);
    setData("outputFailureMaxCurrent." + channelName, "i", document.getElementById("SupMaxIFail").value);
    //        setData("outputSupervisionMaxTemperature." + channelName, "F", document.getElementById("SupMaxP").value);
    //        setData("outputFailureMaxTemperature." + channelName, "i", document.getElementById("SupMaxPFail").value);
    //        setData("outputSupervisionMaxPower." + channelName, "F", document.getElementById("SupMaxT").value);
    //        setData("outputFailureMaxPower." + channelName, "F", document.getElementById("SupMaxTFail").value);

    setViewChannelController(channelName, "");
  }
  // -----------------------------------------------------------------------

  // Getter and Setter for data reading ------------------------------------
  function getData(dataName, runFn) {
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
      // readyState: 4 - Request complete
      // status: 200 - Request successful
      if(request.readyState == 4 && request.status == 200) {
        var data = request.responseText;
        runFn(data);
      }
    }
    var url = "./snmp.php?get=" + dataName;
    request.open("GET", url);
    request.send();
    delete request;
  }

  function setData(dataName, type, value) {
    var request = new XMLHttpRequest();
    var url = "./snmp.php?set=" + dataName + "&type=" + type + "&value=" + value;
    request.open("POST", url);
    request.send();
  }
  // -----------------------------------------------------------------------

  // Getter and Setter for setting reading ------------------------------------
  function readSetting(settingName, runFn) {
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
      // readyState: 4 - Request complete
      // status: 200 - Request successful
      if(request.readyState == 4 && request.status == 200) {
        var data = request.responseText;
        runFn(data);
      }
    }
    var url = "./setting.php?read=" + settingName;
    request.open("GET", url);
    request.send();
    delete request;
  }

  function writeSetting(settingName, value) {
    var request = new XMLHttpRequest();
    var url = "./setting.php?write=" + settingName + "&value=" + value;
    request.open("POST", url);
    request.send();
  }
  // -----------------------------------------------------------------------

  // Utility ---------------------------------------------------------------
  function capitalize(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
  }

  function numberFormat(value) {
    return value.toFixed(4);
  }
  // -----------------------------------------------------------------------

  window.onload = init;
}
