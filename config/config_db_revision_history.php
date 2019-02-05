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

$engine['db_revision_history'] = array(
			       'perm'=>'super_user',
					'add_link_show'=>false,
					'list_order'=>array('applied_at'=>true),
			       'allow_edit' => false,
			       'allow_delete' => false,
			       'title' => 'ucwords($action) . "ing User Options for " . staff_link($rec["staff_id"])',
					'list_fields'=>array('db_revision_code','git_tag','applied_at')
			       );
?>
