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

function get_alerts_for_staff( $id, $limit=25,$unread_only=false )
{
	$fil = staff_filter($id);
	if ($unread_only) {
		// testing for !has_read so that null values are counted as unread.
		$fil['!has_read'] = sql_true();
	}
	$alerts = get_alerts($fil,"",$limit);
	// This loop could become a function, sql_fetch_to_array(), 
	// except for the custom tweaking of the summary field
	for ($x=0;$x<count($alerts);$x++) {
		if (strtoupper($alerts[$x]['ref_table'])=='LOG') {
			$alerts[$x]['summary']=orr($alerts[$x]['summary'],substr($alerts[$x]["log_text"],0,60));
		}
	}
	return $alerts;
}	

function get_alerts($filter,$order="",$limit="")
{
	global $alert_order;
	$def=get_def('alert');
	return get_generic($filter,orr($order,$alert_order,list_query_order($def['list_order'])),$limit,$def);
}

function get_alerts_for_log( $logno, $separator="" )
{
	/*
	 * $separator = "NF" to just return array
	 */

        global $alert_table, $alert_table_id;

	  $get_sql="SELECT staff_id from $alert_table WHERE
		ref_table='LOG' AND ref_id=$logno";

	  $alerts=sql_query( $get_sql ) or sql_warn( $get_sql );

	  $staff=sql_fetch_column($alerts,"staff_id");

        if ( $separator=="NF") {

		  return $staff; // just the IDs

        } else {

		  return get_staff_f( $staff );
	  }
}

function show_alerts($UID, $limit='')
{
      $mesg =' (You have no new alerts)';

      //process any acknowledgements
      $acknowledge = orr($_REQUEST['acknowledge'],array()); //an array containing alert_ids to acknowledge

      foreach ($acknowledge as $alert_id => $value) {

		$success = acknowledge_alert($alert_id); //mark as read

		if (!$success) {

			$fail[$alert_id] = "Couldn't acknowledge alert #$alert_id.";

		}
      }

      $alerts = get_alerts_for_staff($UID,$limit,true);
      if (count($alerts) > 0) {

		$submit_button = button('Acknowledge Selected Alerts','','','','',' style="font-size: 75%;"');

		//some formatting
		$right_cell = table_blank(row(cell(center(bold('Description'))).cell(right($submit_button),'width="2"')),'','width="100%"');
		$rows = row(cell(bold('When')).cell(bold('From')).cell($right_cell,'colspan="2"'));

		//output javascrit for fanciness
		global $AG_HEAD_TAG;

		$AG_HEAD_TAG .= style('tr.staffDataSelected {background-color: #f7ffb2; }');


		// FIXME:  All this JS stuff should go.
		// The alert checkboxes can use the checkBoxGroup functionality

		/* Javascript Functions */
		$AG_HEAD_TAG .= java_engine::get_js(<<<EOF
     function checkRow(obj,e,rowNum) {
          if (obj.parentNode.parentNode.parentNode) {
                 highlightRow(obj.parentNode.parentNode.parentNode,obj.checked);
           }
	     // If shift key, set all checkboxes's between last and current to the current state
	     if (e.shiftKey && lastClick != -1) {
		     var first = lastClick < rowNum ? lastClick : rowNum;
		     var last = lastClick > rowNum ? lastClick : rowNum;
		     var current = document.getElementById("alert" + rowNum);
		     if (!current) { return; }
		     var state = current.checked;
		     for (var i = first; i <= last; i++) {
			     var checkBox = document.getElementById("alert" + i);
			     if (checkBox && checkBox.checked != state) {
				     checkBox.checked = state;
			     }
                       if (checkBox.parentNode.parentNode.parentNode) {
                             highlightRow(checkBox.parentNode.parentNode.parentNode,checkBox.checked);
                       }
		     }
	     }
	     lastClick = rowNum;
     }

     function highlightRow(row,checked) {
           var cl = row.className;
           //toggle between "staffDataN" "staffDataN staffDataSelected"
           var cl_match = /^staffData(1|2)(\sstaffDataSelected)?$/;
           var res = cl.match(cl_match);
           if (res != null) {
                if (checked) {
                      row.className = 'staffData' + res[1] + ' staffDataSelected';
                } else {
                      row.className = 'staffData' + res[1];
                }
           }
     }
EOF
);
/* End Javascript Functions */

		$i = 0;
		foreach ($alerts as $x) {
			// Redirect reference to referenced table
			if ($x['ref_table']=='reference') {
				$r_def=$r_def ? $r_def : get_def('reference');
				$refs=(get_generic(array($r_def['id_field']=>$x['ref_id']),NULL,NULL,$r_def));
				if (count($refs) < 1) {
					// Bad reference, skip
					continue;
				}
				$ref_rec=array_shift($refs);
				$x['ref_table']=$ref_rec['from_table'];
				$x['ref_id']=$ref_rec['from_id'];
				$x['alert_subject']=preg_replace('/^(reference)/i',$x['ref_table'],$x['alert_subject']);
			}

			$extra=$action=$user=$action2=$user2='';
			$color_flip = !$color_flip;
			$rowclass = $color_flip ? '2' : '1';

			    //engine stuff here -- send to alert record
				preg_match('/.* ([a-z]*)$/i',$x['alert_subject'],$matches);
				$action=strtolower($matches[1]);
				$ref_def=get_def($x['ref_table']);
				$ref=get_generic(array($ref_def['id_field']=>$x['ref_id']),'','',$x['ref_table']);
				if ($ref=array_shift($ref)) {
					if (array_key_exists('staff_id',$ref)) {
						$extra = staff_link($ref['staff_id']);
					}
					if (array_key_exists('client_id',$ref)) {
						$extra .= client_link($ref['client_id']);
					} else {
						$refs=object_references_f($x['ref_table'],$x['ref_id'],NULL,$sep=', ');
						$extra .= $refs;
					}
					switch ($action) {
						case 'added' :
							$user=$ref['added_by'];
							break;
						case 'edited' :
							$user=$ref['changed_by'];
							break;
						case 'deleted' :
							$user=$ref['deleted_by'];
							break;
						case 'attention' : // flagged--log,or after-the-fact-reference
							$action='';
							if (strtoupper($x['ref_table']=='LOG')) {
								$user=$ref['written_by'];
							} else {
								$user=$ref['changed_by']; // show who changed last, I guess??
							}	
							if ($x['changed_by'] <> $ref['changed_by']) {
								$action2='flagged';
								$user2=$x['changed_by'];
							}
							break;
						default :
							$user=orr($ref['written_by'],$ref['changed_by']);
							break;
					}
				}
				$alert_description = strtoupper($x['ref_table'])=='LOG' ? "Log: " . $ref['subject'] : ucfirst($ref_def['singular']);
 				$alert_link=smaller(link_engine(array('object'=>'alert','id'=>$x['alert_id']),'detail'));
 				$ref_link=link_engine(array('object'=>$x['ref_table'],'id'=>$x['ref_id']),$alert_description);

			$rows .=row(
					cell(smaller(datetimeof($x['added_at'],'US','TWO')))
					. cell(smaller(staff_link($x['added_by'])))
					. cell(
						 "$ref_link "
						 . smaller("$action by " .staff_link($user).": ") 
						 . ($action2 ? smaller("$action2 by " .staff_link($user2).": ") : '')
						 . ($extra? smaller("($extra)") : '')
						 . ($alert_link ? " $alert_link" : ''))
					. cell(
						 right(form_field('boolcheck','acknowledge['.$x['alert_id'].']','',
									' id="alert'.$i.'" onclick="checkRow(this,event,'.$i.')" title="Hold down Shift key to select multiple alerts"')) 
						 ),
					'class="staffData'.$rowclass.'"');
			
			$i ++;
		}

		$rows .= row(cell(table_blank(row(bottomcell(left($show_link))
							    . cell(right($submit_button)))
							,'','width="100%"')
					,'colspan="4"'));
		
		$table = table($rows,'','class="staff"');
		$mesg = 'Here are your most recent alerts: ';
      }

      global $NICK;
      $greet = oline(bigger(bold('Hi ' . red(ucwords(strtolower($NICK))).' '.$mesg)));
	
      return table_blank(row(cell($greet)) 
				 . row(cell(form($table))),'','width="100%"');
}

function acknowledge_alert($alert_id,$undo=false)
{
// Mark an alert as acknowledged
	global $UID;

	$def = get_def('alert');

	// First, get the alert
	$alert_fil = array('alert_id' => $alert_id);
	$alert     = get_alerts($alert_fil);

	if (!($alert && (count($alert)==1))) {

		log_error("Acknolwedge alert couldn't retrieve alert #$alert_id.");
		return false;

	}

	$alert = array_shift($alert);

	if (!($alert['staff_id'] == $UID)) { // acknolwedge your own alerts only, but could add sys perms

		log_error("Sorry " . staff_name($UID) . "($UID), you can only acknowledge your own alerts");
		return false;

	}

	$ack=array();
	$ack['has_read']         = $undo ? sql_false() : sql_true(); //allow to unmark as read
	$ack['FIELD:read_at']    = 'current_timestamp';
	$ack['changed_by']       = $UID;
	$ack['FIELD:changed_at'] = 'current_timestamp';

	if (db_read_only_mode()) {

		outline(alert_mark('Can\'t acknowledge alerts at this time. DB is in read-only mode.'));
		return false;

	} 

	$attempt = agency_query(sql_update($def['table_post'],$ack,$alert_fil));

	if (!$attempt) {
		log_error("Unable to post acknowledged alert $alert_id to Database");
		return false;
	}

	return true;
}

function link_engine_alerts($ref_table,$ref_id)
{
      if ($ref_table=='LOG')
      {
	    //return hlink('log_browse.php?action=show&id='.$ref_id,'View');
	    return log_link($ref_id);
      }
      else
      {
	    $control = array('object'=>strtolower($ref_table),
			     'id'=>$ref_id);
	    return link_engine($control,'View');
      }
}

function view_alert($rec,$def,$action,$control='',$control_array_variable='control')
{
	if ($action === 'add') {
		return view_generic($rec,$def,$action,$control,$control_array_variable);
	}
	
	// create link to record
	$subject = link_engine(array('object'=>$rec['ref_table'],
					     'id'=>$rec['ref_id']),webify($rec['alert_subject']));
	$rec['alert_subject'] = $subject;
	//fill in client link
	// expecting form: client: <CLIENT_NAME> (<CLIENT_ID>)
	$text = webify($rec['alert_text']);
	$tmp = preg_match("/.*?client:\s?([a-z|\s-,]+)\s\(([0-9]+)\).*?/is",$text,$m);
	$text = str_replace($m[1],client_link($m[2]),$text);

	$rec['alert_text'] = $text;
	foreach (array('has_read','ref_table','ref_id','read_at','alert_id','alert_link') as $key) {
		$def['fields'][$key]['display_view'] = 'hide';
	}

	return view_generic($rec,$def,$action,$control,$control_array_variable);
}

function staff_alerts_f($object,$id,$sep='') {
	$sep=orr($sep,$GLOBALS['NL']);
	if ($id>0 and (intval($id)==$id)) { $alerts=get_alerts(array('ref_table'=>$object,'ref_id'=>$id)); }
	while ($rec=array_shift($alerts)) {
		$link=staff_link($rec['staff_id']);
		if ($rec['staff_id']==$GLOBALS['UID']) { //Alert to user
			$to_me=true;
			$link=bigger($link);
		}
		$out[]=$link;
	}
	if ($out) {
		return $to_me 
			? div(implode($sep,$out),'','class="myAlert"')
			: implode($sep,$out);
	}
}

function post_alerts($alert_rec,$notify_list,&$msg) {		
	$al_def=get_def('alert');
	foreach( $notify_list as $n) {
		if (be_null($n)) { continue; } // Not sure why this would happen, but rather than debug, just skip!
		$alert_rec['staff_id']=$n;
		// At least for now, since this might not work,
		// Skipping any error checking and just letting it
		// continue if the alerts fail.
		if (!post_generic($alert_rec,$al_def,$mesg,NULL,$dummy_control)) {
			$any_fail=true;
			$msg.=oline($mesg);
			$mesg='';
		}
	}
	return !$any_fail;
}

?>
