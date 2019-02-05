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

function merge_object_reference_db($object,$id,&$control) {
	if ( $control['action']=='add') { // no db refs yet
		return;
	}
	$start_refs_from = is_array($control['object_references']['from']) ? $control['object_references']['from'] : array();
	$start_refs_to = is_array($control['object_references']['to']) ? $control['object_references']['to'] : array();
	$db_refs = $add_refs = array();
//outline("Trying for $object and $id");
	$refs=orr(get_object_references($object,$id),array());
//outline("Got refcohnt: " . sql_num_rows($refs) );
	while ($ref = array_shift($refs)) {
		$t_id = $ref['to_id'];
		$t_obj = $ref['to_table'];
		$f_id = $ref['from_id'];
		$f_obj = $ref['from_table'];
		if ( ($f_obj==$object) and ($f_id==$id) ) { // FROM this object, so TO another
			$ref_hash=$t_obj.':'.$t_id;
			$db_refs['to'][$ref_hash]=array(
				'object'=>$t_obj, 
				'id' => $t_id, 
				'label' => object_label($t_obj,$t_id),
				'canRemove'=>false);
		} elseif ( ($t_obj==$object) and ($t_id==$id) ) {
			$ref_hash=$f_obj.':'.$f_id;
			$db_refs['from'][$ref_hash]=array(
				'object'=>$f_obj, 
				'id' => $f_id, 
				'label' => object_label($f_obj,$f_id),
				'canRemove'=>false);
		}	
	}
	// FIXME: inefficient nested looping, must be a better way!
	foreach( $start_refs_to as $ref) {
		$skip=false;
		foreach ($db_refs['to'] as $hash=>$db) {
			if ($ref['object']==$db['object'] and $ref['id']==$db['id']) {
				$skip=true; // skip on match
			}
		}
		if (!$skip) {
			$db_refs['to'][$hash]=array('object'=>$ref['object'],'id'=>$ref['id'],'label'=>$ref['label']);
		}
	}
	foreach( $start_refs_from as $ref) {
		foreach ($db_refs['from'] as $hash=>$db) {
			if ($ref['object']==$db['object']
				and $ref['id']==$db['id']) {
					break 2; // skip on match
			}
			$db_refs['from'][$hash]=array('object'=>$ref['object'],'id'=>$ref['id'],'label'=>$ref['label']);
		}
	}
	$db_refs['pending']=$control['object_references']['pending'];
	$control['object_references']=$db_refs;
	return;
}

function process_object_reference_generic($def,$rec,&$control)
{
	global $AG_AUTH,$UID;
	// Add action always ref to something else
	$action=$control['action'];
	$sent_objects=orr($_REQUEST['selectedObject'],array());
	foreach($sent_objects as $ref) {
		$ref=json_decode(rawurldecode($ref),true);
		if (!in_array($ref['refType'],array('pending','removed'))) {
			continue;
		}
		if (!in_array($ref['object'],$def['allow_object_references'])) {
			// discard invalid objects
			continue;
		}
		$ref_hash=$ref['object'].':'.$ref['id'];
		if ($ref['refType']=='pending') {
			$obj_ref_req[$ref_hash]=$ref;
		} else {
			$removed[$ref_hash]=$ref;
		}
	}
	foreach ($control['object_references']['pending'] as $hash=>$ref) {
		$in_request = array_key_exists($hash,$obj_ref_req);
		$in_removed = array_key_exists($hash,$removed);
		if ( (!$in_request) and (!$in_removed)) {
			$obj_ref_req[$hash]=$ref;
		}
	}
//	$refs = array_merge(orr($obj_ref_req,array()),orr($control['object_references']['pending'],array()));
	$refs = $obj_ref_req;
	if (in_array($action,array('add','edit'))) {
		if ($control['step'] == 'new') {
			$refs = array();
		}
		$control['object_references']['pending'] = $refs;
		return;
	}
	if (($action != 'view') or (!$refs) or (!$_REQUEST['addReferences'])) {
		return;
	}
	// Post pending alerts from view
	if ($def['require_password'] and (!$AG_AUTH->reconfirm_password())) {
		$control['object_references']['pending']=$refs;
		return 'Couldn\'t post references.  Incorrect password for '.staff_link($UID);
	}
	foreach ($refs as $ref) {
		if ($ref['refType']<>'pending') {
			$new_refs[]=$ref;
			continue;
		}
		$ref['refType']='to';
		$ref['sys_log'] = 'Reference added through AGENCY interface.';
		$post_refs[]=$ref;
	}
	if (!post_object_references($rec,$def,$post_refs,$post_msg)) {
		return 'Failed to post reference: '.$post_msg;
	} else {
		$control['object_references']['pending']=array();
		return 'Successfully posted references';
	}
}

function populate_object_references( $control ) {
	$x=1;
	$refs_to=orr($control['object_references']['to'],array());
	$refs_from=orr($control['object_references']['from'],array());
	$refs_pending=orr($control['object_references']['pending'],array());
	foreach (array('to'=>$refs_to,'from'=>$refs_from,'pending'=>$refs_pending) as $type=>$refs) {
		foreach ($refs as $ref) {
/*
//FIXME: this should work, but doesn't because the labels can't handle embedded codes & such
			$label=$ref['label'];
			$label=(strlen($label) <= $max_length)
				? $label
				: substr($label,0,$max_length)
				. div(toggle_label('...').substr($ref['label'],$max_length),'','class="hiddenDetail"');
			$ref['label']=$label;
*/
//FIXME: For the same reason, we need to strip the tags out of labels, if they are coming from the server

			$ref['label']=strip_tags($ref['label']);
			$ref['refType']=$type;
			$ref['Number']=$x++;
			$pre_refs .= div(json_encode($ref));
		}
	}
	return div($pre_refs,'preSelectedObjects','class="serverData"');
}

function object_reference_container($def,$control) {
	global $AG_AUTH;
	$action=$control['action'];
	$needs_password=$def['require_password'];
	if ($action=='view') {
		if ($needs_password) {
			$pass1=$AG_AUTH->get_onsubmit('');
			$pass2= 'Password: ' . $AG_AUTH->get_password_field(false);
		}
		$form=	formto('','',$pass1)
			. $pending
			. $pass2
			.  button()
			. hiddenvar('addReferences','dummy')
			.formend();
	}
	$to=div(html_heading_tag('Refers To',2),'objectReferenceContainerReferenceTo','class="hidden objectReferenceContainerReferenceTo"');
	$from=div(html_heading_tag('Referenced By',2),'objectReferenceContainerReferenceBy','class="hidden objectReferenceContainerReferenceBy"');
	$pending=div($form.html_heading_tag('Pending References',2),'objectReferenceContainerReferencePending','class="hidden objectReferenceContainerReferencePending"');

	return div(html_heading_tag('Additional Info',2),'infoAdditionalContainer','class="infoAdditionalContainer hidden"')
	. div($pending . $to . $from,'objectReferenceContainer','class="objectReferenceContainer hidden"')
	. div('','objectReferenceRemovedContainer','class="objectReferenceRemovedContainer hidden"')
	. div('','','class="ajObjectSearchResult"');
}

function post_object_references( $rec,$def,$refs, &$mesg ) {
	$posted_refs = true;
	$rdef = get_def('reference');
	foreach ($refs as $ref) {
		$tmp_def=get_def($ref['object']);
		$n_ref = array(
			'to_table' => $ref['object'],
			'to_id' => $ref['id'],
			'to_id_field' => $tmp_def['id_field'],
			'from_table' => $def['object'],
			'from_id' => $rec[$def['id_field']],
			'from_id_field' => $def['id_field']);
		$check_ref=get_generic($n_ref,NULL,NULL,$rdef);
		if (count($check_ref)==1) {
			continue;
		}
		$n_ref['added_by'] = $rec['added_by'];
		$n_ref['changed_by'] = $rec['changed_by'];
		$mesg2='';
		if (!$n_alert = $rdef['fn']['post']($n_ref,$rdef,$mesg2)) {
			// only return message on failure
			$mesg .= $mesg2;
			$posted_refs = false;
		}
	}
	return $posted_refs;
}


function object_selector_generic( $object='', &$div_id='',$filter=array(), $max_count=1, $label='',$class='')
{
	$def=get_def($object);
	$object_label = $def['singular'];
	//$main_def=get_def(AG_MAIN_OBJECT);
	$main_def=get_def(AG_MAIN_OBJECT_DB);
	$object_label = $max_count > 1 ? $def['plural'] . smaller(' (max: ' . $max_count . ')') : $def['singular'];
	$main_id_field=$main_def['id_field'];
	$obj_opt='object="'.$object.'"';
	$Uobj = ucfirst($object);
	$div_id=orr($div_id,'ObjectSelector'.$Uobj);
	$label=div(orr($label,"Select " . ucfirst(orr($object_label,'objects'))),'','class="objectSelectorTitle objectSelectorTitle'.$Uobj.'"');
	$op=hiddenvar('objectPickerObject',$object);
	$method='Pick';
	$id_field=$def['id_field'];
	/* Get label */
	switch ($object) {
		// Objects with object_name() function in db:
		case AG_MAIN_OBJECT_DB :
		case 'guest' :
			if ($GLOBALS['AG_DEMO_MODE']) {
				$label_field='TEXT('.enquote1('XXXXXX, XXX').')';
				break;
			} // else fall through...
//		case AG_MAIN_OBJECT_DB :
		case 'staff' :
		case 'guest' :
			$label_field= $object . '_name(' . $id_field . ')';
			$method='Search';
			break;
		case 'housing_unit' :
			$label_field='housing_unit_code'; //not numeric ID
			break;
		default :
			$label_field = "'" . $Uobj . "' || ' ' || " . $id_field;
			$method='Search'; //FIXME: does Pick make any sense for selector?
	}
	$op .= hiddenvar('objectPickerMethod',$method);
	if ($method=='Search') {
		$my_control=(in_array($main_id_field,array_keys($def['fields'])) and count(staff_client_assignments_ids($GLOBALS['UID'])) > 0) ? (formcheck('objectPickerMyClients',true) . ' My clients first') : '';
//		$my_caselist_control=(in_array($main_id_field,array_keys($def['fields'])) and count(staff_client_assignments_ids($GLOBALS['UID'])) > 0) ? (formcheck('objectPickerMyCaselist',false) . ' My full caselist') : '';
		// FIXME, hack for guest, which has a client_id field in it
		if ($object=='guest') {
			$my_control='';
			$my_caselist_control='';
		}
		$op .= form_field('text','objectPickerSearchText')
			. hiddenvar('objectPickerMaxSelect',$max_count) // FIXME: this will break with multiple selectors on one form
			. $my_control
//			. $my_caselist_control
			. button('Search','','','','','class="objectPickerSubmit"');
	} elseif ($method=='Pick') {
		$op .= selectto('objectPickerPickList',$obj_opt )
			. do_pick_sql("SELECT $id_field AS value,$label_field AS label FROM " . $def['table'] . " ORDER BY $label_field")
			. selectend()
			. button('Add','','','','','class="objectPickerSubmit" ' . $obj_option);
	}
    $object_picker = div($op,'','class="objectSelectorPick' . $Uobj.'"');

    return
        div( span($label)
			. span($show_selected)
			. span($object_picker)
			. span($submit . $cancel),
			$div_id,'class="' . $object . ' ' . $class . '"');
}

function info_additional_f($object,$id,$id_field=NULL,$sep='') {
	$sep=orr($sep,$GLOBALS['NL']);
	$filter=object_reference_filter_wrap( $object,$id,$id_field,'from','info_additional');
	$key='info_additional_type_code';
//toggle_query_display();
	$recs=agency_query("SELECT * FROM info_additional LEFT JOIN info_additional_type USING ($key)",$filter);
//toggle_query_display();
	while ($rec=sql_fetch_assoc($recs)) {
		$label=$rec['description'] . $rec['info_additional_value'];
		$link=elink($object,$id,$label);
		if (sql_true($rec['info_additional_value'])) {
			$major[]=$link;
		} else {
			$minor[]=$link;
		}
	}
	$maj = $major ? implode($sep,$major) : '';
	$min = $minor ? div(toggle_label("more info...") . implode($sep,$minor),'','class="hiddenDetail"') : '';
	return $maj . $min;
}
		
function object_references_f($object,$id,$id_field=NULL,$sep='',$ref_types='to',$inc_objs=NULL, $exc_objs=NULL) {
	$r_ctrl=array(); 
	$sep=orr($sep,$GLOBALS['NL']);
	$ref_types=orr($ref_types,'to');
	merge_object_reference_db($object,$id,$r_ctrl);
	$refs=orr($r_ctrl['object_references'][$ref_types],array());
	foreach( $refs as $k=>$v) {
		if ( ! (( $exc_objs and in_array($v['object'],$exc_objs) )
				or ($inc_objs and !in_array($v['object'],$inc_objs))
				or ( ($v['object']=='info_additional' and !($inc_objs and in_array("info_additional",$inc_objs))) ) )) {
			$out[]=elink($v['object'],$v['id'],$v['label']);
		}
	}
	return $out ? implode($sep,$out) : '';
}

function object_reference_filter($object,$id,$id_field=NULL,$ref_types='both',$ref_object=NULL) {
	if (!($id and is_numeric($id))) { return array('FIELD:false'=>'true'); }

	$def=get_def($object);
	$id_field=orr($id_field,$def['id_field']);
	$filter_from=array("FIELD:COALESCE(from_id_field,'$id_field')"=>"'$id_field'",
		'from_id'=>$id,'from_table'=>$def['table']);
	$filter_to=array("FIELD:COALESCE(to_id_field,'$id_field')"=>"'$id_field'",
		'to_id'=>$id,'to_table'=>$def['table']);
	if ($ref_object) {
		$filter_to['from_table']=$ref_object;
		$filter_from['to_table']=$ref_object;
	}
	switch($ref_types) {
		case 'to' :
			$filter['a']=$filter_to;
			break;
		case 'from' :
			$filter['b']=$filter_from;
			break;
		case 'both' :
		default :
			$filter["a"]=$filter_from;
			$filter["b"]=$filter_to;
//			$filter=array($filter);
	}
	$filter=array($filter);
//outline("Returning: " . read_filter($filter));
	return $filter;  
}

function object_reference_filter_wrap( $object,$id,$id_field=NULL,$ref_types='both',$ref_object=NULL,$ref_id_field=NULL) {

	$o_def=get_def($object);
	$id_field=orr($id_field,$o_def['id_field']);

	$r_def=get_def($ref_object);
	$ref_id_field=orr($ref_id_field,$r_def['id_field']);
	$fkey="SELECT CASE WHEN to_table='$object' THEN from_id WHEN from_table='$object' THEN to_id END FROM reference WHERE ";
	$filter["FIELDIN:$ref_id_field"]="($fkey" . read_filter(object_reference_filter($object,$id,$id_field,$ref_types,$ref_object)) . ")";
	return $filter;
}

function get_object_references($object,$id,$id_field=NULL) {
	if (!(is_numeric($id) and (intval($id)==$id))) { 
		// Ref table currently only holds integers, skip anything else
		return false;
	}
	$order="to_table='".AG_MAIN_OBJECT_DB."' DESC,to_table,to_id";
	return get_generic(object_reference_filter($object,$id,$id_field),$order,'',get_def('reference'));
}

function info_additional_label($id) {
	$def=orr($def,get_def('info_additional'));
	$filter=array('info_additional_id'=>$id);
	$key='info_additional_type_code';
	$rec=agency_query("SELECT * FROM info_additional LEFT JOIN info_additional_type USING ($key)",$filter);
	if ($rec2=sql_fetch_assoc($rec)) {
		$l = $rec2['description'] . ' ' . $rec2['info_additional_value'];
	} else {
		$l = 'info_additional $id not found';
	}
	return $l;
}

function object_label($object,$id,$recs=NULL,$format='default') {
	$def = get_def($object);
	if (! ($id and $def)) {
		return false;
	}
	if ($def['object_label']) {
		if (is_array($def['object_label'])) {
			// $label is really eval'able code
			if (array_key_exists($format,$def['object_label'])) {
				$label=$def['object_label'][$format];
			} elseif (array_key_exists('default',$def['object_label'])) {
				$label=$def['object_label']['default'];
			} else {
				$label=array_shift($def['object_label']);
			}
		} else {
			$label = $def['object_label'];
		}
		if (preg_match('/^\{.*\}$/',$id)) {
			$id=sql_to_php_array($id);
		}

		if (is_array($id)) {
			$filter=array($def['id_field']=>$id);
		} else {
			$filter=array($def['id_field']=>$id);
		}
		$recs = get_generic($filter,'','',$def);
		foreach ($recs as $rec) {
			$out[]= eval('return ' . $label . ';');
		}
		$l=implode($sep,$out);
	} else {
		$l = $def['singular'] . ' ' . $id;
	}
	return $l;
}

function object_reference_form( $objs, &$show_link, $target_div='') {
	foreach ($objs as $obj ) {
		$t_def=get_def($obj);
		$div_id='';
		$object_refs .= object_selector_generic($obj,$div_id);
		$tab_links .= html_list_item(hlink("#$div_id",$t_def['plural']));
	}
	$label = 'Refer to ' . ((count($objs)==1) ? $t_def['plural'] : 'other records') . '...';
	$tabs = html_list($tab_links,'class="'.$obj.'"');	
	$show_link = hlink('',$label,NULL,'class="fancyLink objectSelectorShowLink"');
	$object_refs = div($tabs . $object_refs,'objectSelectorForm','class=objectSelectorForm');
	return $object_refs;
}

?>
