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

global $agency_home_url;
$engine['donor'] = array(
		'singular' => 'Donor',
		'perm_type' => 'any',
		'sel_sql' => 'SELECT *, address_names(address_id(donor_id)) AS names FROM donor',
		'cancel_add_url'=>$agency_home_url,
		'list_fields'=>array('donor_id','donor_type_code','names', 'is_inactive'),
		'child_records'=> array(
 						'donor_stat',
						'address',
						'gift',
						'gift_united_way',
						'donor_link',
						'donor_total',
						'donor_note',
						'sent_mail',
						'volunteer_reg',
						'volunteer_hours',
						'proposal'
//						'export_gift_mip'
					),
		'title' => 'ucwords($action) . "ing Donor record for " . client_link($rec["donor_id"])',
		'title_add'=>'ucwords($action) . "ing a new Donor record."',
		'fields' => array(
					'donor_comment'=>array(
								     'value_format_list'=>'smaller($x,2)'
								     ),
					'public_listing'=>array('label'=>'Annual Report Listing'),
					'names'=>array('virtual_field'=>true),
					'preferred_address_code'=>array(
										  'null_ok'=>true,
										  'data_type'=>'lookup',
										  'lookup'=>array(
													'table'=>'l_address_type',
													'value_field'=>'address_type_code',
													'label_field'=>'description'),
										  'confirm'=>array('!be_null($x)'=>'The database will set preferred_address to "HOME" for individuals and "BUSINESS" for others if left blank.'),
										  'add_query_modify_condition'=>array('be_null($x)'=>'ENGINE_UNSET_FIELD')
										  ),
					'scratch'=>array('display'=>'hide')
/*
					'mip_export_donor_id' => array('add_query_modify_condition'=>array('1==1'=>'ENGINE_UNSET_FIELD'), //always unset
										 'display_add'=>'hide',
										 'null_ok'=>true), //set on insert
					'mip_export_session_id' => array('display_add' => 'hide',
										   'display'=>'display')
*/

					)
		);
?>
