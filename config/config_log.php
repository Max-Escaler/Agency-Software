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

$engine['log'] = array(
	'allow_object_references'=>array('client'),
//	'add_another'=>true,
	'enable_staff_alerts'=>true,
	'enable_staff_alerts_view'=>true,
	'include_info_additional'=>true,
	'object_label'=>'sql_assign("SELECT SUBSTRING(COALESCE(subject,log_text) FROM 0 FOR 50) FROM " . $def["table"],array($def["id_field"] => $id))',
	'list_fields'=>array('custom1','log_type_code','staff_alerts','clients','additional','subject'),
	'quick_search'=>array(
		'jump_page'=>'display.php',
		'match_fields'=>array('log_text','subject'),
		'match_fields_numeric'=>array('log_id')
		),
//	'subtitle_eval_code'=>'($total>0) ? smaller(hlink("log_browse.php?action=show_client_logs&cid=".$id,"Show full text of these logs")) : ""',
	'subtitle_html'=>smaller(hlink('log_browse.php?action=browse','Go to log index')),
	'allow_edit'=>false,
	'allow_delete'=>false,
//	'allow_add'=>false,
//	'add_link_show'=>true,
	'list_hide_view_links' => true,
	'perm'=>'any',
	'list_order'=>array('added_at'=>true),
	'list_max'=>25,
	'title_view'=>'"Log ".$rec["log_id"] . " " . smaller(hlink(AG_LOG_URL,"go to Log index"))',
	'title_add' =>'"Add A New Log Entry"',
	'cancel_add_url' => AG_LOG_URL,

	'fields' => array(
		'clients'=>array(
			'data_type'=>'references', // This doesn't mean anything currently
			'value_format'=>'smaller($x)',
			'is_html'=>'true',
			'value'=>'object_references_f($def["object"],$rec[$def["id_field"]],NULL,NULL,NULL,array("client"))' ),
		'staff_alerts'=>array(
			'data_type'=>'alerts', // This doesn't mean anything currently
			'value_format'=>'smaller($x)',
			'is_html'=>'true',
			'value'=>'staff_alerts_f($def["object"],$rec[$def["id_field"]])' ),
		'additional'=>array(
			'data_type'=>'references',
			'value_format'=>'smaller($x)',
			'is_html'=>'true',
			'label_view'=>'additional information',
			'value'=>'div(info_additional_f($def["object"],$rec[$def["id_field"]],NULL,NULL,NULL),"","class=\"infoAdditionalContainer\"")' ),
		'log_type_code'=>array(
			'show_lookup_code'=>'CODE',
			'data_type'=>'lookup_multi',
			'lookup_format'=>'checkbox',
			'value_format'=>'smaller($x)',
			'label'=>'In logs',
			'valid'=>array('count($x)>0'=>'You must specify at least one log')),
		'written_by'=>array(
			'label'=>'Author',
			'default'=>'$GLOBALS["UID"]',
			'display'=>'display'),
		'occurred_at'=>array('label'=>'Event Time',
			'comment'=>'Leave blank unless this is a very late entry'),
	   'subject'=>array(
			'is_html'=>true,
			//'value_format_list'=>'smaller($x)',
			'value_list'=>'div(elink("log",$rec["log_id"],$x) . div(webify($rec["log_text"]),"","class=\"hiddenLogText\"")
							  ,"",(isset($rec["_staff_alert_ids"]) 
							 && in_array($GLOBALS["UID"],sql_to_php_array($rec["_staff_alert_ids"])))
							  ? " style=\"background-color: #FFC0C0; padding: 3px;\""
							  : null)'
			  ),
		'log_text' => array( 'comment' => 'Compose your log here'),
		'custom1'=>array(
			  'data_type'=>'html',
			  'display'=>'hide',
			  'display_list'=>'display',
			  'label'=>'Added at/#/Author',
			  'label_format'=>'smaller($x)',
			  'value'=>'smaller(oline("#".$rec["log_id"]." by " .staff_link($rec["written_by"])) . dateof($rec["added_at"]) . " " . timeof($rec["added_at"]))'
		  )

   )
);

?>
