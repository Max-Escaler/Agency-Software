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


$engine['chronic_homeless_status_asked'] = 
    array( 
	  "singular"=>'Self-reported Chronic Homeless status',
	  'fields'=>array(
			  'chronic_homeless_status_code'=> 
			  array(
				'comment'=>'Enter what client reports. Don\'t use other information'),
			  'chronic_homeless_status_asked_id'=> 
			  array(
				'label'=>'Self-reported Chronic Homeless Status ID'),
			  'comments'=>  
			  array(		
				'valid' => array('(($rec["chronic_homeless_status_code"] != "UNKNOWN" && $rec["chronic_homeless_status_code"] != "NOT_ASKED") || (!be_null($x)))' => '{$Y} must be filled in if client not asked or unknown'),
				'comment'=>'Required if status is Client Not Asked or Unknown'
						)
			  )
	   );
?>

