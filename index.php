<?php
if (isset($_GET['order']))
    $order = $_GET['order'];
else
    $order = 0;
?>

<!DOCTYPE html>

<!--
 Author: Genie Jhang
 e-mail: geniejhang@majimak.com
   Date: 2013. 07. 17
-->

<html>
    <head>
        <meta charset='utf-8' />
        <title>MPOD HV&LV Power Supply System Controller</title>
        
        <link rel='stylesheet' href='./style.css'>
        <script src='./functions.js'></script>

        <script src="jquery-2.1.4.min.js"></script>
    </head>
    <body>
        <h1>MPOD HV&LV Power Supply System Controller</h1>
        <div class='plain'>Crate IP Address:</div>
        <div class='plain'><input type='text' class='ipAddress' id='ipAddress' value='0.0.0.0' /></div>
        <div class='plain'><input type='button' value='Change' onclick='changeIP()' /></div>
        <div class='clear'></div>
        <div class='frameSpace'>&nbsp;</div>
        <div class='clear'></div>
        <div id='cratePower'>Loading Crate Power Status</div>
        <p>It takes a few seconds for the settings to be applied.<br>Don't change properties too fast.</p>
        <p><b>Make sure if the settings are properly changed before you turn on!</b></p>
        <hr>
        <input type='hidden' id='order' value='<?php echo $order; ?>'>
        <div id='channelList'>Turn On the Crate to See the Channel List</div>

        <!-- Range Selector -->
        <div id='rangeSelector' class='groupController' style='position:absolute; left:805px; top:90px; width:220px; height:80px;'>
            <div class='title'>Safe Current Range</div>
            <div class='clear'></div>
            <div style='width:85px;float:left;text-align:right;'>Minimum:</div><div style='float:left;'><input type='text' id='currentMin' size='10' value='1.2' /> A</div>
            <div class='clear'></div>
            <div style='width:85px;float:left;text-align:right;'>Maximum:</div><div style='float:left;'><input type='text' id='currentMax' size='10' value='1.5' /> A</div>
        </div>

        <!-- Group Controller -->
        <div id='groupController' class='groupController'>
            <div class='title'>Group Controller</div>
            <div class='clear'></div>
            <div style='float:left;'>Selected group:</div>
            <div id='GC_groupList' class='groupSwitchDiv'>GC_groupList</div>
            <input type='button' class='groupSwitch' value='On' onclick='groupOn()'>
            <input type='button' class='groupSwitch' value='Off' onclick='groupOff()'>
            <input type='button' class='groupButton' value='Change group setting' onclick=''>
            <p></p>
            <input type='button' class='groupButton' value='Reset emergency off' onclick='groupSet("resetEmergencyOff");'>
            <input type='button' class='groupButton' value='Set emergency off' onclick='groupSet("setEmergencyOff");'>
            <input type='button' class='groupButton' value='Enable kill' onclick='groupSet("enableKill");'>
            <input type='button' class='groupButton' value='Disable kill' onclick='groupSet("disableKill");'>
            <input type='button' class='groupButton' value='Clear events' onclick='groupSet("clearEvents");'>
        </div>

        <!-- Channel Controller -->
        <div id='channelController' style='display:none;'>
            <div id='title' class='title'>CHANNELNAME Output Configuration</div>
            <input type='hidden' id='ch' value=''>
            <!-- Measurement Frame -->
            <div id='measurement'>
                <div class='frameTitle'>Measurement</div>
                <div class='measLeftFrame'>
                    <div class='measLeftLabel'>Sense Voltage [V]</div>
                    <div class='value' id='MeasSV'>0.000</div>
                    <div class='clear'></div>
                    <div class='measLeftLabel'>Terminal Voltage [V]</div>
                    <div class='value' id='MeasTV'>0.000</div>
                    <div class='clear'></div>
                    <div class='measLeftLabel'>Current [A]</div>
                    <div class='value' id='MeasI'>0.000</div>
                </div>
                <div class='frameSpace'>&nbsp;</div>
                <div class='measRightFrame'>
                    <div class='measRightLabel'>Power of the Load [W]</div>
                    <div class='value' id='MeasPL'>0.000</div>
                    <div class='clear'></div>
                    <div class='measRightLabel'>Power of the Module [W]</div>
                    <div class='value' id='MeasPM'>0.000</div>
                    <div class='clear'></div>
                    <div class='measRightLabel'>Hotspot Temerature [&#8451;]</div>
                    <div class='value' id='MeasHT'>0.000</div>
                </div>
            </div>
            <div class='frameBreak'></div>
            <!-- Nominal Values Frame -->
            <div id='nominal'>
                <div class='frameTitle'>Nominal Values</div>
                <div class='nomLeftFrame'>
                    <div class='nomLeftLabel'>&nbsp;</div>
                    <div class='input'>&nbsp;</div>
                    <div class='info'>max.</div>
                    <div class='clear'></div>
                    <div class='nomLeftLabel'>Sense Voltage [V]</div>
                    <div class='input'><input type='text' class='input' id='NomSV' value='0.000'></div>
                    <div class='info' id='NomSVMax'>0.000</div>
                    <div class='clear'></div>
                    <div class='nomLeftLabel'>Current Limit [A]</div>
                    <div class='input'><input type='text' class='input' id='NomCL' value='0.000'></div>
                    <div class='info' id='NomCLMax'>0.000</div>
                </div>
                <div class='frameSpace2'>&nbsp;</div>
                <div class='nomRightFrame'>
                    <div class='nomRightLabel'>&nbsp;</div>
                    <div class='input'>&nbsp;</div>
                    <div class='clear'></div>
                    <div class='nomRightLabel'>Ramp Up [V/s]</div>
                    <div class='input'><input type='text' class='input' id='NomRU' value='100'></div>
                    <div class='clear'></div>
                    <div class='nomRightLabel'>Ramp Down [V/s]</div>
                    <div class='input'><input type='text' class='input' id='NomRD' value='100'></div>
                </div>
                <div class='clear'></div>
                <div class='nomBottomFrame'>
                    <div class='nomBottomLabel'>No Ramp at Switch Off</div>
                    <div class='input'><input type='checkbox' id='NomNRSO'></div>
                    <div class='clear'></div>
                    <div class='nomBottomLabel'>Fast Regulation (Cable length &#60; 1m)</div>
                    <div class='input'><input type='radio' name='NomR' value='0'></div>
                    <div class='clear'></div>
                    <div class='nomBottomLabel'>Moderate Regulation (Cable length &#62; 1m)</div>
                    <div class='input'><input type='radio' name='NomR' value='1'></div>
                    <div class='clear'></div>
                    <div class='nomBottomLabel'>Slow Regulation (Cable length &#62; 50m)</div>
                    <div class='input'><input type='radio' name='NomR' value='2'></div>
                    <div class='clear'></div>
                    <div class='nomBottomLabel'>Internal Sensing</div>
                    <div class='input'><input type='checkbox' id='IntSen'></div>
                    <!-- not used
                    <div class='nomBottomLabel'>reserved</div>
                    <div class='input'><input type='checkbox' id='reserved'></div>
                    -->
                </div>
            </div>
            <div class='frameBreak'></div>
            <!-- Supervision Frame -->
            <div id='supervision'>
                <div class='frameTitle'>Supervision</div>
                <div class='supLabel'>&nbsp;</div>
                <div class='input'>&nbsp;</div>
                <div class='info'>max.</div>
                <div class='frameSpace2'>&nbsp;</div>
                <div class='info2'>on failure:</div>
                <div class='clear'></div>
                <div class='supLabel'>min. Sense Voltage [V]</div>
                <div class='input'><input type='text' class='input' id='SupMinSV' value='0.000'></div>
                <div class='info'>&nbsp;</div>
                <div class='frameSpace2'>&nbsp;</div>
                <div class='value2'>
                    <select class='value2' id='SupMinSVFail'>
                        <option value='0'>ignore the failure</option>
                        <option value='1'>switch off this channel</option>
                        <option value='2'>switch off all channels with the same group number</option>
                        <option value='3'>switch off the complete crate</option>
                    </select>
                </div>
                <div class='clear'></div>
                <div class='supLabel'>max. Sense Voltage [V]</div>
                <div class='input'><input type='text' class='input' id='SupMaxSV' value='0.000'></div>
                <div class='info' id='SupMaxSVMax'>0.000</div>
                <div class='frameSpace2'>&nbsp;</div>
                <div class='value2'>
                    <select class='value2' id='SupMaxSVFail'>
                        <option value='0'>ignore the failure</option>
                        <option value='1'>switch off this channel</option>
                        <option value='2'>switch off all channels with the same group number</option>
                        <option value='3'>switch off the complete crate</option>
                    </select>
                </div>
                <div class='clear'></div>
                <div class='supLabel'>max. Terminal Voltage [V]</div>
                <div class='input'><input type='text' class='input' id='SupMaxTV' value='0.000'></div>
                <div class='info' id='SupMaxTVMax'>0.000</div>
                <div class='frameSpace2'>&nbsp;</div>
                <div class='value2'>
                    <select class='value2' id='SupMaxTVFail'>
                        <option value='1'>switch off this channel</option>
                        <option value='2'>switch off all channels with the same group number</option>
                        <option value='3'>switch off the complete crate</option>
                    </select>
                </div>
                <div class='clear'></div>
                <div class='supLabel'>max. Current [A]</div>
                <div class='input'><input type='text' class='input' id='SupMaxI' value='0.000'></div>
                <div class='info' id='SupMaxIMax'>0.000</div>
                <div class='frameSpace2'>&nbsp;</div>
                <div class='value2'>
                    <select class='value2' id='SupMaxIFail'>
                        <option value='0'>ignore the failure</option>
                        <option value='1'>switch off this channel</option>
                        <option value='2'>switch off all channels with the same group number</option>
                        <option value='3'>switch off the complete crate</option>
                    </select>
                </div>
            </div>
            <div class='frameBreak'></div>
            <div class='buttonsFrame'>
                <input type='button' class='button' onclick='updateChannel()' value='Apply'>&nbsp;
                <input type='button' class='button' onclick='setViewChannelController("", "none");' value='Close'>
            </div>
        </div>
    </body>
</html>
