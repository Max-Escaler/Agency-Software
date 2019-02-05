<?php

$engine['work_order_comment']=array(
	'enable_staff_alerts'=>true,
	'enable_staff_alerts_view'=>true,
	'require_password'=>false,
	'allow_skip_confirm'=>true,
	'list_fields'=>array('work_order_comment_id','added_at','added_by','work_order_id','comment'),
	'fields'=>array(

		'work_order_comment_id'=>array(
			'label'=>'ID',
        ),

		'work_order_id'=>array(
				'display'=>'display',
				'value_format'=>'elink_value("work_order",$x_raw)',
            	'value_format_add'=>'oline(elink_value("work_order",$x_raw)) . work_order_comments($rec["work_order_id"])',
				'is_html'=>true
		),
		'attachment_description'=>array(
			'row_before'=>'bigger(bold("Attachment"))',
			// FIXME: config_engine shouldn't make this an attachment if it's not an integer field
			'data_type'=>'varchar',
			'invalid'=>array(
				'be_null($x) and (!be_null($rec["attachment"]))'=>'Must describe attachment',
				'(!be_null($x)) and (be_null($rec["attachment"]))'=>'Cannot describe attachment unless there is one',
			),
		),
	)
);



