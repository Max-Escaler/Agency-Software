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


$engine['export_kc_transaction'] = array('singular' => 'King County Export Transaction Record',
						     'perm' => 'clinical',
						     'perm_add' => 'clinical_admin',
						     'allow_edit' => false,
						     'list_fields' => array('object_type','object_id','event_date','export_kc_id','added_by','added_at'),
						     'fields' => array('object_type' => array('value' => 'get_singular($x)'),
									     'object_id' => array('value' => 'elink($rec["object_type"],$x,$x)',
													  'is_html' => true),
									     'export_kc_id' => array('value' => 'be_null($x) ? "Export pending" : elink("export_kc",$x,"View Export Record")',
													     'is_html' => true)
									     )
						     );

?>