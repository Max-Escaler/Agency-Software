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

$engine['alert'] = array(
				 'enable_staff_alerts'=>false,
			'title_view'=>'ucwords($action) . "ing Alert (" . elink_value($rec["ref_table"],$rec["ref_id"]) . ")"',
			 'list_fields' => array('alert_link','alert_subject','ref_table','ref_id','added_at','has_read'),
			 'list_order' => array('added_at'=>true), //initial descending order sort
			 'list_hide_view_links' => true,
			 'perm_list'=>'self',
			 'perm_view'=>'self',
			 'perm'=>'admin',
			 'allow_add' => false,
			 'fields' => array(
						 'alert_text'=>array(
									   'label'=>'Text',
									   'is_html'=>true),
						 'alert_text_public'=>array(
									   'is_html'=>true),
						 'alert_subject'=>array(
									   'label'=>'Subject',
									   'is_html'=>true),
					   'ref_table' => array(
								'label_list'=>'Type',
								'value_format_list' => 'ucwords($x)'),
					   'ref_id' => array(
							     'label_list'=>'ID #'),
					   'has_read' => array(
							       'label_list' => 'Status',
							       'value_format_list'=>'$x==\'Yes\' ? "viewed" : red(bold(\'New\'))'),
					   'alert_link' => array(
									 'is_html'=>true,
								 'value' => 'link_engine_alerts($rec["ref_table"],$rec["ref_id"])'
								 ),
						'view_record'=>array(
							'value_format'=>'view_generic_record($rec["ref_table"],$rec["ref_id"])',
							'is_html'=>true,
						),
			   ),

			 );

?>
