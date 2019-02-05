#!/usr/bin/php -q
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



$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$UID = $GLOBALS['sys_user'];
$date = dateof('now','SQL');
landlord($date);

// During the period of the 20th --> 5th,
// two months are run.
// 5th Changed to 10th per bug 15628

if (day_of($date)<=10) {
	landlord(last_month($date));
} elseif (day_of($date)>=20) {
	landlord(next_month($date));
}

page_close($silent = true);
?>
