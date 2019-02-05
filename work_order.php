<?php
/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2012 by Ken Tanzer and Downtown Emergency
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

function link_work_order_my( $id=NULL,$sep=NULL ) {
        $id=orr($id,$GLOBALS['UID']);
		$sep=orr($sep,oline());
        if (!is_staff($id)) { return false; }
        $def=get_def($staff);
        $assigned_filter=array(
                'assigned_to'=>$id
        );
        $filed_filter=array(
                'added_by'=>$id
        );
        $cc_filter=array(
                'ARRAY_CONTAINS:cc_list'=>array($id)
        );
		$all_filter=array(
			array_merge($assigned_filter,$filed_filter,$cc_filter)
		);
		$assigned_label='Assigned';
		$filed_label='Filed by me';
		$cc_label='I\'m CCd on';
		$all_label='All my WOs';
        $assigned_link = link_engine_list_filter('work_order',$assigned_filter,$assigned_label,'class="fancyLink"');
        $filed_link = link_engine_list_filter('work_order',$filed_filter,$filed_label,'class="fancyLink"');
        $cc_link = link_engine_list_filter('work_order',$cc_filter,$cc_label,'class="fancyLink"');
        $all_link = link_engine_list_filter('work_order',$all_filter,$all_label,'class="fancyLink"');
		$new_link = add_link('work_order','New');
		$links=array($assigned_link,$filed_link,$cc_link,$all_link,$new_link);
		return implode($sep,$links);
}
                    
function work_order_comments( $id ) {
	if (!is_valid($id,'integer_db')) {
		return false;
	}
	$def=get_def('work_order_comment');
	$wo_filter=array('work_order_id'=>$id);
	$comments=get_generic($wo_filter,'added_at',NULL,$def);
	foreach ($comments as $c) {
		$o=
		hot_link_objects(webify($c['comment']))
		.', ' . smaller(staff_link($c['added_by']).'@'.dateof($c['added_at']))
		;
		$out[]=$o;
	}
	return implode(hrule(),$out)
		. add_link('work_order_comment','comment',NULL,$wo_filter);
}

function work_order_summary( $id ) {
	if (!is_valid($id,'integer_db')) {
		return false;
	}
	$def=get_def('work_order');
	if (count( ($wo=get_generic(array('work_order_id'=>$id),'added_at',NULL,$def))) != 1) {
		return false;
	}
	$wo=array_shift($wo);

	$o=array(
		'Priority'=>$rec["priority"],
		'By'=>staff_link($rec["added_by"]) . "@" . dateof($rec["added_at"]),
		'Assigned' => staff_link($rec["assigned_to"]),
    	'Status'=> $rec["work_order_status_code"] . ", Category: " . $rec["work_order_category_code"],
		'Where'=> $rec["housing_project_code"]  . ", " . $rec["housing_unit_code"],
	);
//	foreach ($o

}

function work_order_notify_list($id,$who,$rec=NULL) {
	// $who could be from WO or comment, and is who NOT to notify
	static $list=array();
	if (array_key_exists($id,$list)) {
		$notify=$list[$id];
	} else {
		if (!$rec) {
			$rec=get_generic(array('work_order_id'=>$id),NULL,NULL,'work_order');
		}
		$rec=$rec[0];
		$rec=sql_to_php_generic($rec,get_def('work_order'));
		$cc_list=orr($rec['cc_list'],array());

		$notify=array_unique(array_merge(array($rec['changed_by'],$rec['added_by'],$rec['assigned_to']),$cc_list));
		$list[$id]=$notify;
	}
	return array_diff($notify,$who);
}

function post_work_order_comment($rec,$def,&$mesg,$filter='',$control=array()) {
	return post_work_order($rec,$def,$mesg,$filter,$control);
}

function post_work_order($rec,$def,&$mesg,$filter='',$control=array()) {
	// FIXME: at least for now, this function is being used for comments too
	if (!($result=post_generic($rec,$def,$mesg,$filter,$control))) {
		return $result;
	}
	$rec=$result;
	// FIXME: error check
	$from_uid=orr($rec['changed_by'],$rec['added_by'],$GLOBALS['UID']);
	$from=staff_name($from_uid);
	$notify_list=work_order_notify_list($rec['work_order_id'],array($from_uid));
	$obj=$def['object'];
	$obj_noun=$def['singular'];
	$al_def=get_def('alert');
	$alert_rec=blank_generic($al_def,array(),$control);
	$alert_rec['ref_table']=$obj;
	$alert_rec['ref_id']=$result[$def['id_field']];
	switch($obj) {
		case 'work_order_comment' :
			if ( sql_true($rec['is_deleted'])) {
				$lab_text= 'had comment deleted by ' . $from;
			} elseif (!$filter) { 
				$lab_text = 'has new comment from ' . $from;
			} else {
				$lab_text= 'had comment edited by ' . $from;
			}
			break;
		case 'work_order' :
			if ( sql_true($rec['is_deleted'])) {
				$lab_text= "$obj_noun was deleted by $from";
			} elseif (!$filter) { 
				$lab_text = "New $obj_noun from $from";
			} else {
				$lab_text= "$obj_noun edited by $from";
			}
			break;
	}
	$alert_rec['alert_subject']=object_label('work_order',$rec['work_order_id']) . ' ' . $lab_text;

	//$reply_url=$_SERVER['SERVER_NAME'].'/agency/display.php?control[action]=add&control[object]=work_order_comment&control[step]=new&control[rec_init][work_order_id]='.$rec['work_order_id'];
	$reply_url=str_replace('//','/',$_SERVER['SERVER_NAME'].$GLOBALS['AGENCY_HOME_BY_URL'].'/agency/display.php?control[action]=add&control[object]=work_order_comment&control[step]=new&control[rec_init][work_order_id]='.$rec['work_order_id']);
	$reply_link=hlink($reply_url,'Reply');
	$alert_rec['alert_text']= 
		($obj != 'work_order_comment') ? '' : (
		'Comment from ' .$from . ',@' . datetimeof(orr($rec['changed_at'],'now'))
		. oline()
		. oline('-----------------------------')
		. $rec['comment']
		)
		. oline()
		. oline()
		. bigger(bold($reply_link))
		;
	$alert_rec['alert_subject_public']=$alert_rec['alert_subject'];
	$alert_rec['alert_text_public']=$alert_rec['alert_text'];
	$alert_result=post_alerts($alert_rec,$notify_list,$mesg);
	return $result; // and $alert_result ??
}
	
?>
