<?php

$engine['work_order']=array(
	'require_password'=>false,
	'allow_skip_confirm'=>true,
	'enable_staff_alerts'=>true,
	'enable_staff_alerts_view'=>true,
	'object_label'=>'"WO " . $rec["work_order_id"] . ": " . $rec["title"]',
	'child_records'=>array('work_order_comment'),
	//'list_fields'=>array('work_order_id','priority','assigned_to','added_at','work_order_status_code','work_order_category_code','housing_project_code','housing_unit_code','title','description'),
	'list_fields'=>array('status_f','text_f'),
	//'list_order'=>array('work_order_status_code IN (\'PENDING\',\'REOPENED\')'=>true,'priority'=>true,' COALESCE((SELECT max(changed_at) FROM work_order_comment woc WHERE woc.work_order_id=work_order.work_order_id),work_order.changed_at)'=>true),
	//'list_order'=>array('work_order_status_code IN (\'PENDING\',\'REOPENED\')'=>true,'priority'=>false,'last_touch_at'=>true),
	'list_order'=>array('is_open'=>true,/*'priority'=>false,*/'last_touch_at'=>true),
	'subtitle_eval_code'=>'link_work_order_my(NULL," | ")',
	'add_link_show'=>false,
	'quick_search'=>array(
		'match_fields'=>array('title','description'),
		'match_fields_numeric'=>array('work_order_id'),
//		'match_fields_date'=>array('dob'),
//		'match_fields_custom'=>array('/^[a-z]{1,4}[0-9]{2,5}$/i'=>array('FIELDIN:client_id'=>'(SELECT client_id FROM residence_own WHERE lower(housing_unit_code)=lower(\'$x\') ORDER BY residence_date DESC limit 1)'))
		),
	'fields'=>array(
		'priority'=>array(
			'invalid'=>array(
				'($x_raw<=5) and ($x_raw>=1)'=>'{$Y} must be between 1 and 5',
			),
			'value_format'=>'($x==1) ? bigger(bold(red($x))) : ( ($x==2) ? bigger(bold($x)) : $x)'
		),
		'status_f'=>array(
			'label'=>'Info',
			'display_view'=>'hide',
			'value_format'=>
				'smaller(oline("Priority: " . value_generic($rec["priority"],get_def("work_order"),"priority","view",true,$rec))'
				. ' . oline("By: " . staff_link($rec["added_by"]) . "@" . dateof($rec["added_at"]))'
				. ' . oline(($rec["assigned_to"] ? "Assigned: " . staff_link($rec["assigned_to"]) : "(Unassigned)"))'
				. ' . ($rec["cc_list"] ? oline("CCs: " . staff_links($rec["cc_list"])) : "")'
				. ' . oline("Status: " . $rec["work_order_status_code"] . ", Category: " . $rec["work_order_category_code"])'
				. ' . ( ($qxq=$rec["housing_project_code"]) ? oline("Where: " . $qxq . ", " . $rec["housing_unit_code"]) : "")'
				. ' . oline("Last touch: " . datetimeof($rec["last_touch_at"],"US")) '
				.')',
			'is_html'=>true
		),
		'comments'=>array(
			'value'=>'work_order_comments($rec["work_order_id"])',
			'is_html'=>true
		),
		'text_f'=>array(
			'label'=>'Description',
			'display_view'=>'hide',
			'value_format'=> 'oline(bold("WO " . $rec["work_order_id"].":".(sql_true($rec["is_open"]) ? $rec["title"] : strike($rec["title"])))) . hot_link_objects(webify($rec["description"])) . oline() . (($qxqq=work_order_comments($rec["work_order_id"])) ?  div($qxqq.toggle_label("comments"),"","class=\"toggleContent workOrderComments\"") : add_link("work_order_comment","Comment","target=_BLANK",array("work_order_id"=>$rec["work_order_id"]))) ',
			'is_html'=>true
		),
		'blocked_by_ids'=>array(
			'selector_object'=>'work_order',
			'value_format'=>'object_links($x_raw,"work_order")',
			'is_html'=>true,
		),
		'blocker_of_ids'=>array(
			'selector_object'=>'work_order',
			'value_format'=>'object_links($x_raw,"work_order")',
			'is_html'=>true,
		),
/*        'recipient_list'=>array(
            'selector_object'=>'staff',
            'data_type'=>'staff_list',
            'label'=>'Recipients',
            'row_before'=>'bigger(bold("Recipients"))'
        ),
*/
        'cc_list'=>array(
            'selector_object'=>'staff',
            'data_type'=>'staff_list'
        ),
		'title'=>array(
			'row_before'=> 'bigger(bold("Main Info"))'
		),
		'agency_project_code'=>array(
			'default'=>'FACILITY',
			'row_before'=> 'bigger(bold("Categories"))'
		),
		'target_date'=>array(
			'row_before'=>'bigger(bold("Dates and Hours"))'
		),
		'work_order_status_code'=>array(
			'lookup_group'=>'grouping',	
			'show_lookup_code'=>'DESCRIPTION',
			'label'=>'Status'
		),
		'work_order_category_code'=>array(
			'show_lookup_code'=>'DESCRIPTION',
			'label'=>'Category'
		),
		'assigned_to'=>array('data_type'=>'staff'),
		'work_order_id'=>array('label'=>'ID'),
		'housing_project_code'=>array(
			 'label'=>'Building',
			 'label_list'=>'Project',
			 'display'=>'hide',
			//'display_edit'=>'display',
			 'java'=> array(
				 'on_event'=> array(
					 'populate_on_select'=> array(
						'populate_field'=>'housing_unit_code',
						'table'=>'housing_unit_current'
					)
				 )
			 )
		),
		'housing_unit_code'=>array(
		   'show_lookup_code'=>'CODE',
		   'label'=>'Unit',
			'display'=>'hide',
		   'is_html'=>true,
		   'value'=>'link_unit_history($x,true,false)'
	   ),
		'comment'=>array(
			'data_type'=>'text',
			'display'=>'hide',
			'display_add'=>true,
			'display_edit'=>'force',
		),
	)
);



