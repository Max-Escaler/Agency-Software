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

$root_install_dir='/home/gate';

//SET SCANNER LOCATION HERE
//$entry_location_code = 'CONN505';
//$entry_location_code = 'SHEL517';

//location-specific checks
switch ($entry_location_code) {
 case 'CONN505': //connections
	 $bar_locations     = array('connections');
	 $priority_function = 'has_priority_connections';
	 $check_safe_harbors = false;
	 $check_chronic_homeless = true;
	 $indicate_housed_function = 'indicate_client_note';
	 break;
 case 'SHEL517': //shelter
	 $bar_locations     = array('shelter_main');
	 $priority_function = 'has_priority';
	 $check_safe_harbors = false;
	 $check_chronic_homeless = true;
	 $indicate_housed_function = 'indicate_barred';
	 break;
 default:
	 die('Unknown scanner location code: '.$entry_location_code);
} 


$MODE='TEXT';
include $off.'/command_line_includes.php';

include 'card.php';

$entry_table='entry';
$entry_select_sql="SELECT * FROM $entry_table";

$cg_sound_player = 'artsplay';

$SOUNDS_DIR=$root_install_dir.'/sounds';
$BARRED_WAV=$SOUNDS_DIR.'/die.wav';
$CLIENT_NOTE_WAV=$SOUNDS_DIR.'/teleport.wav';
$NOT_PRIORITY_WAV=$SOUNDS_DIR.'/splat.wav';
$OBSOLETE_CARD_WAV=$SOUNDS_DIR.'/bonus.wav';
$DISASTER=$SOUNDS_DIR.'/error.wav';
$PROVISIONAL_WAV=$SOUNDS_DIR.'/crash.wav';
$SUCCESS_WAV=$SOUNDS_DIR.'/gong10.wav';

set_time_limit( 300000 );

?>
