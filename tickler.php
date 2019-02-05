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

$quiet='Y';
include 'includes.php';

$title = 'Proposal Action';

$control=array('object'=>'proposal',
		     'action'=>'list',
		     'list'=>array(
				   'fields'=>array('donor_id',
							 'next_action_date',
							 'proposal_next_action_code'),
				   'order'=>array('next_action_date'=>false))
);

$var = 'tickler_proposal_control';
$CONTROL=array_merge($control,orr($_SESSION[$var],array()),orr($_REQUEST[$var],array()));
$CONTROL['page']=$_SERVER['PHP_SELF'];
$CONTROL['anchor']='permission';


$out = head($title);
$out .= call_engine($CONTROL,$var,true,$no_messages,$tot,$perm);


out(agency_top_header());
out($out);
?>
