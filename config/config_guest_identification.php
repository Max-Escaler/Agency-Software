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

$engine['guest_identification'] = array(
	'list_fields'=>array('guest_id','identification_type_code','identification_number','identification_expiration_date'),
	'fields' => array(
		'identification_document_scan' => array(
			'label' => 'Document Scan',
			'data_type' => 'attachment',
			'display_view'=>'hide',
			'display_list'=>'hide'
		),
		'identification_expiration_date'=>array(
			'label'=>'Expiration'
		),
		'identification_type_code'=>array(
			'label'=>'Type'
		),
		'identification_number'=>array(
			'label'=>'Number'
		),
		'guest_identification' => array(
			'label' => 'ID scan',
			'value' => 'guest_photo($rec["guest_id"])',
			'value_list' => 'guest_photo($rec["guest_id"],50,50)',
			'is_html'=>true,
			'display_add' => 'hide',
			'display_edit' => 'hide'
		),
		'guest_id' => array(
			//'data_type' => 'parent' // FIXME: make me work, and automatically
			'is_html'=>true,
			'value'=>'elink_value("guest",$x)'
		)
	)
);


?>
