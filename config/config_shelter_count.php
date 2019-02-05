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

$engine['shelter_count']=array(
	'singular'=>'Daily Shelter Count',
	'perm'=>'mail_entry',
	'perm_list'=>'any',
	'perm_view'=>'any',
	'prepend_finished_add_eval'=>'link_engine(array("object"=>"shelter_count","action"=>"list","list"=>array("display_add_link"=>true)),"Browse Daily Shelter Counts");',
	'cancel_add_url'=>'display.php?control[action]=list&control[object]=shelter_count&control[list]=a%3A1%3A%7Bs%3A16%3A%22display_add_link%22%3Bb%3A1%3B%7D',
	'list_fields'=>array('shelter_count_date','breakfast_total','breakfast_temperature','dinner_total','dinner_temperature','towels'),
	'list_order'=>array('shelter_count_date'=>true),
	'valid_record' => array( '(dateof($rec["shelter_count_date"],"SQL") <= "2007-09-21")
					 || (dateof($rec["shelter_count_date"],"SQL") > "2007-09-21" && !be_null($rec["breakfast_time"]) && !be_null($rec["breakfast_temperature"]) && !be_null($rec["breakfast_firsts"]) && !be_null($rec["breakfast_seconds"]))' => 'Breakfast data must be completed'),
	'fields' => array( 
				'shelter_count_date'=>array(
								    'data_type'=>'date_past',
								    'display_edit'=>'display',
								    'valid'=>array(//field has a unique constraint fixme: this should be generalized
											 '($action=="edit" and $rec_last["shelter_count_date"]==$x) or be_null($x) or (sql_num_rows(get_generic(array("shelter_count_date"=>$x),
															  "","","tbl_shelter_count"))<1)'
											 =>'Record for this date already exists')),

				/* Breakfast */
				'breakfast_firsts'=>array(
							    'valid'=>array('be_null($x) || $x>=0'=>'{$Y} must be greater than or equal to zero.')),
				'breakfast_seconds'=>array(
							    'valid'=>array('be_null($x) || $x>=0'=>'{$Y} must be greater than or equal to zero.')),
				'breakfast_menu_description'=>array('textarea_width'=>40,'textarea_height'=>6),
				'breakfast_temperature'=>array('label'=>'Breakfast Temperature (&deg;F)'),

				/* Dinner */
				'dinner_firsts'=>array(
							    'valid'=>array('be_null($x) || $x>=0'=>'{$Y} must be greater than or equal to zero.')),
				'dinner_seconds'=>array(
							    'valid'=>array('be_null($x) || $x>=0'=>'{$Y} must be greater than or equal to zero.')),
				'dinner_menu_description'=>array('textarea_width'=>40,'textarea_height'=>6),
				'dinner_temperature'=>array('label'=>'Dinner Temperature (&deg;F)'),

				'towels'=>array(
						    'valid'=>array('$x>=0'=>'{$Y} must be greater than or equal to zero.'))
								  
		 ));
				
?>
