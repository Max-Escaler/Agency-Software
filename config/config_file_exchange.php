<?php

$engine['file_exchange']=array(
	'list_fields'=>array('title','recipient_list','file_attachment'),
//	'list_fields'=>array('info','recipient_list'),
	'list_order'=>array('added_at'=>true),
	'add_link_show'=>true,
	'add_link_always'=>true,
	'perm_write'=>'housing',
	'allow_edit'=>false,

//	'list_order'=>array('zipcode'=>false),
//	'list_max'=>100,
	'fields'=>array(
		'file_attachment' => array(
			'data_type'=>'attachment',
			'attachment_use_filename_original'=>true
		),
		'recipient_list'=>array(
			'label'=>'Recipients',
			'data_type'=>'lookup_multi',
			'lookup_format'=>'checkbox_v',
			'lookup'=>array(
				'table'=>'staff',
				'label_field'=>'staff_name(staff_id)',
				'value_field'=>'staff_id'
			)
		)
/*
		'info'=>array(
			'value'=>'html_heading_tag($rec["title"]).link_attachment($rec["file_attachment"],"file_attachment")',
			'is_html'=>true
		)
*/
	)
);
//function value_generic($value,$def,$key,$action,$do_formatting=true,$rec=array())

?>
