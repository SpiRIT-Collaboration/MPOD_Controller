WIENER MPOD LV Controller
============================

http://github.com/SpiRIT-Collaboration/MPOD_Controller.git

This software is written for the SpiRIT-TPC experiment at RIKEN.

You can freely copy and redistribute this with any modificaiton.
Please leave **a note** about the modification you made and **your name** and **e-mail address** before distribution.

Please make sure that the system you are going to use is compatible to this software. Original author does not guarantee proper performances on the other system than that at RIKEN.

Important
---------
This software is WIENER LV module controller. There is iseg HV module controller in the other branch.

How to Use
----------
1. Type `snmpwalk -v 2c -m ./WIENER-CRATE-MIB.txt -c public IPADDRESS moduleDescription` in command line.
2. Check which module you want to use. (e.g. ma0 and ma1)
3. Change `ActiveModules` array into the list of modules you want to use in `settings.json` file. (e.g. ["ma0","ma1"])
4. Change IP address in `settings.json` file.
5. Type `php configure.php` to run configure.php file.
6. Modify `mapping.json` file according to the mapping for the experiment.

Author list
-----------
Original Author: Genie Jhang (geniejhang@majimak.com)

Note
----
2016/03/15 by the original author<br>
Release of two separated versions 2.1lv and 2.1hv.

2015/07/01 by the original author<br>
Initial public release with the version 2.0

Remark
------
WIENER-CRATE-MIB.txt file is offered by WIENER. (http://www.wiener-d.com)
