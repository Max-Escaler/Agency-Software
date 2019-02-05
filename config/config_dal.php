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


$tmp_dal_edit_days = 180; // number of days between when a dal is added 
                          // this also indicates the maximum number of days in the past a new dal can be dated

$tmp_prog_edit_days = 21; // number of days between when a dal is added
                          // and a progress note can be edited


$engine['dal'] = array(
			     'add_another'=>true,
			     'add_another_password_cycle'=>0,
 			     'add_link_show'=>false,
			     'multi_add' => array('number_of_records'=>15,
							  'init_fields' => array('client_id','performed_by','dal_location_code','contact_type_code','dal_date','dal_code'),
							  'common_fields' => array('dal_focus_area_codes','progress_note'),
							  'reference_id_field' => 'dal_progress_note_id'),
			     'enable_staff_alerts_view'=>true,
			     'subtitle_eval_code'=>'link_quick_dal("Add DAL(s)",array("client_id"=>$id,"performed_by"=>$GLOBALS["UID"]))." ".link_report("clinical/dal_progress_note.cfg","Print Progress Notes",array("cid"=>$id))',
			     'singular'=>'Mental Health DAL',
			     'list_fields'=>array('dal_date','total_minutes','dal_code','performed_by','custom1'),
			     'list_order' => array('dal_date'=>true,'added_at'=>true,'dal_id'=>false), //latest dals first
			     'perm' => 'clinical',
			     'perm_view'=>'clinical,my_client_position_project_clinical,dal',
			     'perm_list'=>'clinical,my_client_position_project_clinical,dal', 
			     'allow_delete'=>true,
			     'perm_delete'=>'dal_admin',
			     'valid_record'=>array('dal_cod_record_exists($rec)' => 'You must enter a COD assessment/screening record with matching date and performed by before entering this DAL'
							   ),
			     'fields' => array(
						     'performed_by'=>array('staff_subset'=>'staff_dal',
										   'default'=>'$GLOBALS["UID"]',
										   'display_edit' => 'display'),
						     'dal_code' => array(
										 'label'=>'Service',
										 'show_lookup_code'=>'BOTH',
										 'display_edit' => 'display',
										 'lookup_order'=>'TABLE_ORDER',
										 'valid'=>array('dal_valid_medical_codes($rec)'=>'{$Y} contains codes for medical staff only',
												    'dal_valid_staff_qualifications($rec)' => 'The selected staff does not have the required staff qualifications for the selected service',
												    '(in_array($x,array("INITCP","90CP")) && has_perm("initcp_dal")) || !in_array($x,array("INITCP","90CP"))' => '{$Y}: You don\'t have permission to enter the selected DAL modality'),
										 'confirm' => array('!in_array($x,array("TPR_C","TPR_NO"))' => 'This DAL code does not meet the criteria for a King County Concurrent Review. If you are doing a concurrent review, use DAL code 650.')
										 ),
						     'contact_type_code'=>array('display_edit' => 'display'),
						     'dal_date'=>array('value'=>'datetimeof(datetotimestamp($x))',
									     'value_list'=>'be_null($rec["post_date"]) || (days_interval($rec["dal_date"],$rec["post_date"],true) < 15) 
                                                                                  ? datetimeof(datetotimestamp($x)) 
                                                                                  : datetimeof(datetotimestamp($x)).smaller(" (".red("L").")")',
									     'data_type'=>'timestamp_past',
									     'timestamp_allow_date' => true,
									     'is_html'=>true,
									     'display_edit' => 'display',
									     'valid'=>array('($action=="edit" and $x==$ox) or be_null($x) or has_perm("old_dal_entry") or days_interval($x,"now") < '.$tmp_dal_edit_days.' or !dal_is_sent_to_county($rec["dal_code"])' =>
												  '{$Y} cannot be more than '.$tmp_dal_edit_days.' days in the past for this DAL code. If you need to enter a DAL this late, contact your supervisor'),
									     'confirm' => array('days_interval($x,"now") < '.$tmp_dal_edit_days => 'The DAL date you have entered is more than '.$tmp_dal_edit_days.' days in the past. Are you sure this is correct?')),
						     'total_minutes'=>array('display_edit'=>'display',
										    'valid'=>array('$x>=0'=>'{$Y} must be greater than or equal to 0',
													 '($x % 5)==0'=>'{$Y} must be in 5-minute increments')),
						     'dal_location_code'=>array('display_edit'=>'display',
											  'lookup_order'=>'TABLE_ORDER'),
						     'post_date'=>array('display'=>'display','display_add'=>'hide'),
						     'post_claim_number'=>array('display'=>'display','display_add'=>'hide'),
						     'post_batch'=>array('display'=>'display','display_add'=>'hide'),
						     'progress_note'=>array('textarea_width' => 80,
										    'textarea_height' =>30,
										    'is_html'=>true,
										    'value_add'=>'webify($x)',
										    'value_edit'=>'webify($x)',
										    'value'=>'!be_null($rec["dal_progress_note_id"]) ? link_engine(array("object"=>"dal","id"=>$rec["dal_progress_note_id"]),smaller("click to add/edit progress note",2)) : webify($x)',
										    'valid'=>array('$action=="add" or (days_interval($rec["added_at"],"now") < '.$tmp_prog_edit_days.') or $x==$ox or be_null($ox)'=>
													 'Cannot change {$Y} for DALs older than '.$tmp_prog_edit_days.' days',
													 '($action=="add" and (be_null($x) or $GLOBALS["UID"]==$rec["performed_by"])) or ($action=="edit" and ($x==$ox or $GLOBALS["UID"]==$rec["performed_by"])) or (in_array($rec["dal_code"],array("266","267")) && in_array($rec["performed_by"],array(1076,1077)))'=>'You can only add or edit your own {$Y}',
													 'be_null($rec["dal_progress_note_id"]) 
													 || (!be_null($rec["dal_progress_note_id"]) 
													     && be_null($x))'
													 => '{$Y} can only be filled in for DALs without associated Progress Notes'
)),
 						     'dal_focus_area_codes'=>array('lookup_format'=>'checkbox_v',
											     'lookup_order'=>'TABLE_ORDER',
											   'valid'=>array('$action=="add" or be_null($ox) or (days_interval($rec["added_at"],"now") < '.$tmp_prog_edit_days.') or $x==$ox'=>
														'Cannot change {$Y} for DALs older than '.$tmp_prog_edit_days.' days',
														'($action=="add" and (be_null($x) or $GLOBALS["UID"]==$rec["performed_by"])) or ($action=="edit" and ($x==$ox or $GLOBALS["UID"]==$rec["performed_by"])) or (in_array($rec["dal_code"],array("266","267")) && in_array($rec["performed_by"],array(1076,1077)))'=>'You can only add or edit your own {$Y}',
														'be_null($rec["dal_progress_note_id"]) 
														|| (!be_null($rec["dal_progress_note_id"]) 
														    && be_null($x))'
														=> '{$Y} can only be filled in for DALs without associated Progress Notes')),
						     'dal_progress_note_id'=>array('display_add'=>'hide',
											     'display'=>'display',
											     'is_html'=>true,
											     'value'=>'be_null($x) ? "" : link_engine(array("object"=>"dal","id"=>$x),"DAL ".$x)',
											     'label'=>'Associated Progress Note DAL'),
						     'dal_follow_up_id'=>array('is_html'=>true,
											 'display'=>'display',
											 'display_add'=>'hide',
											 'value'=>'dal_allow_follow_up($rec) ? link_quick_dal(smaller("Add Follow-Up DAL"),array("client_id"=>$rec["client_id"],"dal_follow_up_id"=>$rec["dal_id"],"performed_by"=>$GLOBALS["UID"])) : (!be_null($x) ? link_engine(array("object"=>"dal","id"=>$x),"This DAL is a follow-up to DAL #".$x) : "")',
											 'valid'=>array('be_null($x) || in_array($rec["dal_code"],$GLOBALS["AG_DAL_FOLLOW_UP_CODES"])'=>'You must use one of the following DAL codes for follow-up DALs: '.implode(', ',$GLOBALS['AG_DAL_FOLLOW_UP_CODES']))
											 ),
						     'custom1'=>array('label'=>'Progress Note Status',
									    'display'=>'hide',
									    'display_view'=>'display',
									    'display_list'=>'display',
									    'is_html'=>true,
									    'value'=>'dal_progress_note_summary($rec)',
									    'value_format'=>'smaller($x)'
									    )
									    
						     )
			     
			     );
?>
