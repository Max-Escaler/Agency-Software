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


$engine['chart_archive'] = array(
					   'perm' => 'clinical',
					   'list_fields' => array('chart_archive_date','chart_archive_location_code','last_progress_note_date',
									  'chart_archive_volume','chart_archive_date_end'),
					   'fields'=>array(
								 'destroyed_at' => array('valid'=>array('be_null($x) xor sql_true($rec["was_destroyed"])'=>'{$Y} must be filled in (ONLY) if archive has been destroyed')),								
								  'archive_checked_out_at' => array('valid'=>array('be_null($x) xor !be_null($rec["archive_checked_out_by"])'=>'{$Y} must be filled in (ONLY) if archive is being checked out'))
								 )
					   );
?>