<?php
/*

<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2017 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency-software.org/
For more information about DESC, see http://www.desc.org/.

AGENCY is free software: you can redistribute it and/or modify
it under the terms of version 3 of the GNU General Public License
as published by the Free Software Foundation.

AGENCY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with AGENCY.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

/*
This code was copied from a freely-available source on the
internet, and was written by Eric Mueller.  Eric has kindly
given DESC rights to this code.

It is licensed in the same manner and terms as any other
code or file in AGENCY.

(detail is in DESC bug 29294)
*/

    class zipfile {
    var $datasec     = array();
    var $rootdir     = array();
    var $offset     = 0;

    function addfile($data,$name) {
         $name          = str_replace('\\', '/', $name);
         $ctime = getdate();
         $ctime = preg_replace("/(..){1}(..){1}(..){1}(..){1}/","\\x\\4\\x\\3\\x\\2\\x\\1",dechex(($ctime['year']-1980<<25)|($ctime['mon']<<21)|($ctime['mday']<<16)|($ctime['hours']<<11)|($ctime['minutes']<<5)|($ctime['seconds']>>1)));
         eval('$ctime = "'.$ctime.'";');

         $crc          = crc32($data);
         $nlength     = strlen($data);
         $zdata          = gzcompress($data);
         $zdata          = substr(substr($zdata,0,strlen($zdata) - 4),2);
         $clength     = strlen($zdata);

         $this->datasec[] = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00$ctime" . pack('V',$crc) . pack('V',$clength) . pack('V',$nlength) . pack('v',strlen($name)) . pack('v',0) . $name . $zdata . pack('V',$crc) . pack('V',$clength) . pack('V',$nlength);
         $this->rootdir[] = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00$ctime" . pack('V',$crc) . pack('V',$clength) . pack('V',$nlength) . pack('v',strlen($name)) . pack('v',0) . pack('v',0) . pack('v',0) . pack('v',0) . pack('V',32) . pack('V',$this->offset) . $name;
         $this->offset = strlen(implode('',$this->datasec));
    }

    function data() {
         $data          = implode('',$this->datasec);
         $ctrldir     = implode('',$this->rootdir);
         return $data . $ctrldir . "\x50\x4b\x05\x06\x00\x00\x00\x00" . pack('v',sizeof($this->rootdir)) . pack('v',sizeof($this->rootdir)) . pack('V',strlen($ctrldir)) . pack('V',strlen($data)) . "\x00\x00";
    }
}
?>
