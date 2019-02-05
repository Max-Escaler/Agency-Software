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

define('AG_CLINICAL_REG_PAGE','clinical_reg.php');
define('AG_CLINICAL_REG_CONFIG_FILE','script_config_clinical_reg.php');

function client_get_kcid( $client_id )
{
	return call_sql_function('client_get_kcid',$client_id);
}

function client_get_id_from_kcid( $kcid )
{
	return call_sql_function('client_get_id_from_kcid',$kcid);
}

function client_get_clinical_id( $client_id )
{

	return call_sql_function('get_case_id',$client_id);

}

function client_get_id_from_clinical_id( $client_id )
{

	return call_sql_function('client_get_id_from_case_id',$client_id);

}

function is_path_enrolled($client_id,$date='now')
{
	if (!$client_id) { return false; }

	// this query is being performed ~8 times to verify path records
	// so the result is now cached 
	static $is_path_enrolled_check;

 	if (isset($is_path_enrolled_check[$client_id])) {
 		return $is_path_enrolled_check[$client_id];
 	}
 	$path_status = $is_path_enrolled_check[$client_id] = 
		sql_true(call_sql_function('is_path_enrolled',$client_id,enquote1(dateof($date,'SQL'))));

	return $path_status;
}

function is_path_tracking_form_eligible($client_id)
{
	/*
	 * Bug 28374
	 * Verify that a client has a non-canceled tier 60 benefit.
	 * The client might have more recent benefits with a different tier.
	 */

	$tier = sql_fetch_column(tier_status($client_id,'60'),'benefit_type_code');

	return $tier;

}

function clinical_last_tier_start($client_id,$tier,$date='now')
{
	if ($tier) {
		return call_sql_function('last_tier_start',$client_id,enquote1($tier),enquote1(dateof($date,'SQL')));
	} else {
		return call_sql_function('last_tier_start',$client_id,enquote1(dateof($date,'SQL')).'::date');
	}
}

function clinical_get_tier($client_id,$date,$type)
{

	return call_sql_function('tier',$client_id,enquote1(dateof($date,'SQL')),enquote1($type));

}

function form_path_tracking($rec,$def,$control) {
	$client = $rec['client_id'];
	$is_path = is_path_enrolled($client);
	$action = $control['action'];

	//-----------manual override----------//
	if (has_perm('path_admin','W') && !$is_path) { //only provide this functionality to switch to long form, not vice-versa
		$_SESSION['path_manual_override_'.$client] = $override = orrn($_REQUEST['path_manual_override'],$_SESSION['path_manual_override_'.$client]);
		$override_link = oline(smaller('Switch to: '
							 .hlink($_SERVER['PHP_SELF'].'?path_manual_override='.(!$override),
								  $override ? 'Short form' : 'Long form').' (USE CAREFULLY!)',2));
		$override_form = hiddenvar('path_manual_override',$override);
	}
	//----------end manual override-------//

	if ($is_path || $override) {
		$title = oline( $is_path ? 'PATH Enrolled Form' : red('Enrolled Form for Non-PATH-Enrolled client'));
		//nothing ?
	} else {
		global $path_tracking_long_form_only_fields;
		$title = oline('Non-PATH-Enrolled Form');
		// hide non-PATH fields
		foreach (array_keys($def['fields']) as $key) {
			if (!in_array($key,$path_tracking_long_form_only_fields)) { continue; }
			$def['fields'][$key]['display_'.$action] = 'hide';
		}
	}
	
	//determine if housing status has already been asked
	$filt = array('client_id'=>$client,'!NULL:path_housing_status_code'=>true);
	if ($client and ($last_tier = clinical_last_tier_start($client,'60'))) {
		$filt['>=:service_date']=$last_tier;
	}			  

	if ($client) {
		$old_rec = get_generic($filt,'added_at DESC','',$def);
		if (sql_num_rows($old_rec)>0) {
			$old_rec = sql_fetch_assoc($old_rec);
			$def['fields']['path_housing_status_code']['display_'.$action]      = 'hide';
			$def['fields']['path_housing_status_time_code']['display_'.$action] = 
				(in_array($old_rec['path_housing_status_code'],array('OUTDOORS','SHORTSHEL'))
				 && be_null($old_rec['path_housing_status_time_code']))
				? 'regular' : 'hide';
		} else {
			$def['fields']['path_housing_status_code']['display_'.$action]='regular';
			$def['fields']['path_housing_status_time_code']['display_'.$action]='regular';
		}
	}
	return $title.form_generic($rec,$def,$control).$override_form . $override_link;
}
/*

The functions commented out below were an initial attempt to merge two record types.
This actually worked quite well, should this need occur in the future. This attempt
was abandoned in favor of using quick_dal.php...

function object_merge_path_tracking($def,$control)
{
	if ($control['action'] == 'add') {
		global $path_tracking_dal_fields;

		$def_dal = get_def('dal');
		
		
		$dal_fields = $path_tracking_dal_fields;
		
		foreach ($dal_fields as $key) {
			$def['fields']['DAL_'.$key] = $def_dal['fields'][$key];
		}
	}
	return object_merge_generic($def,$control);
}

function post_path_tracking($rec,$def,&$mesg,$filter='')
{
	if (!$filter) {
		$rec_dal = path_get_dal_record($rec,$def);
		$rec = path_get_path_tracking_record($rec,$def);

		global $engine;
		$def_dal = get_def('dal');

		if (!$nrec = post_generic($rec_dal,$def_dal,&$mesg,$filter))
		{
			return false;
		}

	}
	return post_generic($rec,$def,&$mesg,$filter);
}

function path_get_dal_record($rec,$def)
{
	global $path_tracking_dal_fields;
	$dal_rec = array();
	foreach ($path_tracking_dal_fields as $key) {
		$dal_rec[$key] = $rec['DAL_'.$key];
	}
	$required_fields = array(// {dal field} => {path field}
					 'client_id'=>'client_id',
					 'performed_by'=>'performed_by',
					 'dal_date'=>'service_date',
					 'added_by'=>'added_by',
					 'changed_by'=>'changed_by'
					 );

	foreach ($required_fields as $df => $pf) {
		$dal_rec[$df] = $rec[$pf];
	}
	return $dal_rec;
}

function path_get_path_tracking_record($rec,$def)
{
	global $path_tracking_dal_fields;
	foreach ($path_tracking_dal_fields as $key) {
		unset($rec['DAL_'.$key]);
	}

	return $rec;
}
*/
function pss_functioning_summary($rec)
{
	global $NL;
	$check = array('dangerous_behavior',
			   'socio_legal',
			   'negative_social_behavior',
			   'self_care',
			   'community_living',
			   'social_withdrawl',
			   'response_to_stress',
			   'sustained_attention',
			   'physical',
			   'health_status');
	foreach ($rec as $key => $value) {
		if (in_array($key,$check)) {
			$pss_func[]=$value;
			$sum[$value][] = $key;
		}
	}
	krsort($sum);
	$avg = array_sum($pss_func) / count($pss_func);
	$out=array();
	$out[] = smaller(red('Avg: '.bold($avg)),2);
	foreach ($sum as $score=>$func) {
		asort($func);
		$out[]=smaller(bold($score).': '.implode(', ',str_replace('_',' ',$func)),2);
	}
	return implode($NL,$out);
}

function pss_symptom_summary($rec)
{
	global $NL;
	$check = array('depressive_symptoms',
			   'anxiety_symptoms',
			   'psychotic_symptoms',
			   'dissociative_symptoms');
	foreach ($rec as $key => $value) {
		if (in_array($key,$check)) {
			$pss_symp[]=$value;
			$sum[$value][] = $key;
		}
	}
	krsort($sum);
	$avg = array_sum($pss_symp) / count($pss_symp);
	$out=array();
	$out[] = smaller(red('Avg: '.bold($avg)),2);
	foreach ($sum as $score=>$symp) {
		asort($symp);
		$out[]=smaller(bold($score).': '.implode(', ',str_replace('_symptoms','',$symp)),2);
	}
	return implode($NL,$out);
}

function clinical_impression_summary($rec)
{
	$fields=array('schizophrenia',
			  'other_psychotic_disorders',
			  'affective_disorders',
			  'depressive_disorders',
			  'bipolar_disorders',
			  'affective_disorders_not_specified',
			  'personality_disorders',
			  'other_serious_mh_illness',
			  'alcohol_abuse',
			  'drug_abuse',
			  'serious_medical_condition',
			  'unknown',
			  'none');
	$yes=array();
	$no=array();
	foreach ($fields as $imp) {
		if (sql_true($rec[$imp])) {
			array_push($yes,bold($imp));
		} elseif (sql_false($rec[$imp])) {
			array_push($no,$imp);
		}
	}
	return oline('yes: '.implode(', ',$yes))
		.'no: '.implode(', ',$no);
}

function clinical_homeless_status_summary($rec)
{
	$fields=array('hotel',
			  'apartment_house_room',
			  'shelter',
			  'outdoors',
			  'public_abandoned_building',
			  'jail',
			  'car',
			  'psychiatric_institution');
	$yes=array();
	$no=array();
	foreach ($fields as $imp) {
		if (sql_true($rec[$imp])) {
			array_push($yes,bold($imp));
		} elseif (sql_false($rec[$imp])) {
			array_push($no,$imp);
		}
	}
	return oline('yes: '.implode(', ',$yes))
		.'no: '.implode(', ',$no);
}

// function engine_record_perm_hospital($control,$rec,$def) {
// 	if (in_array($control['action'],array('add','edit'))) {
// 		return false;
// 	}

//  	global $UID;
//  	$cid = $rec['client_id'];
// 	$staff = get_staff_clients($cid,false,true);
// 	if (in_array($UID,$staff) || has_perm('clinical')) {
// 		return true;
// 	} 
// 	return false;

// }

function path_tracking_add_link($id)
{
	$client = sql_fetch_assoc(client_get($id));
	$def = get_def('path_tracking');
	$singular = $def['singular'];
	$label = 'Add '.aan($singular).' '.$singular;
	if  (!be_null($client['clinical_id'])) {
		return link_engine(array('object'=>'path_tracking','action'=>'add',
						 'rec_init'=>array('client_id'=>$client['client_id'])),
					 $label);
					 
	}
	return alt(dead_link($label),'Client must be a clinical client in order to add path tracking record.');
}

function tier_to_project( $tier )
{
	return call_sql_function('tier_program',enquote1($tier));
}

function dal_due_between_date_f( $client, $daterange )
{
	if (!has_perm('clinical')) {
		// no view into DALs
		return '';
	} elseif ($res = tier_status($client)) {
		if (sql_num_rows($res)==0) {
			//no current tiers
			return "";
		}
		$rec = sql_fetch_assoc($res);
		if ($rec['end_date'] < dateof('now','SQL')) {
			return '';
		}
	}

	$def = get_def('dal');
	$filter=array('BETWEEN:dal_date'=>$daterange, "client_id"=>$client);
	$res = get_generic($filter,'dal_date DESC','',$def);

	if ( ($a=sql_fetch_assoc($res)) && ($done_date=dateof($a['dal_date']) )) {

		return "DAL on file $done_date.";

	} else {

		$late=(dateof("now","SQL")>$daterange->end) ? "Late " : "";
		return "{$late}DAL due by " . dateof($daterange->end) . '.';

	}
}

function hospital_status_f($id,$security_override = false)
{
	$def = get_def('hospital');

	if (!$security_override && !engine_perm(array('object'=>'hospital','action'=>'view','id'=>$id))) {

		$quick_summary = true;

	}

	$res = get_generic(client_filter($id),'hospital_date DESC','1',$def);
	if (count($res) < 1) {
		return false;
	}

	$rec = array_shift($res);
	if (be_null($rec['hospital_date_end'])) {

		$days = $rec['days_in_hospital'] . ($rec['days_in_hospital'] > 1 ? ' days' : ' day');
		$text = $quick_summary
			? ('Admitted to hospital on '.dateof($rec['hospital_date']).' ('.$days.')')
			: ( (sql_true($rec['is_voluntary']) ? 'Voluntarily' : 'Involuntarily')
			    .' admitted to '.$rec['facility'].' on '.dateof($rec['hospital_date']).' ('.$days.')' );

	} elseif (days_interval($rec['dal_due_date'],'now',true) < 8 ) { //show up to 7 days late

		$range = new date_range($rec['hospital_date_end'],$rec['dal_due_date']);
		$text = $quick_summary
			? ('Released from hospital on '.dateof($rec['hospital_date_end']))
			: ( 'Released from '.$rec['facility'].' on '.dateof($rec['hospital_date_end']).'. '
			  //  .bold(dal_due_between_date_f($id,$range))
			  );

	} else {

		return false;

	}

	return oline(link_engine(array('object'=>'hospital','id'=>$rec['hospital_id']),
					 red($text)));
}

function tier_status($id,$tier=false)
{

	$filter = client_filter($id);
	if ($tier) {

		$filter['benefit_type_code'] = $tier;

	}

	$filter['!kc_authorization_status_code'] = 'CX';
	return get_generic($filter,'clinical_reg_date DESC',1,'clinical_reg');
}

function tier_status_f($id)
{

	$regs = tier_status($id);
	$def = get_def('clinical_reg');

	if (has_perm('clinical_data_entry','RW')) {

		$link_reg = div(link_clinical_reg($id,$reg),'clinicalDataEntry',' style="display: none;"');
		$expand_reg_link =  Java_Engine::hide_show_button('clinicalDataEntry',$hide=true,$link=true,' Clinical Data Entry');

	} else {

		$expand_reg_link = dead_link('Show Clinical Data Entry');

	}

	if (sql_num_rows($regs)==0) {

		$output = oline(smaller('(No MH Registrations)' . $expand_reg_link));

	} else {

		$reg = sql_fetch_assoc($regs);
		$prog = tier_to_project($reg['benefit_type_code']);
		$type_link = jump_to_object_link('clinical_reg','client',$reg['benefit_type_code']);
		$update_link = has_perm('clinical') ? ' | '.link_clinical_reg_update($id,$prog,$reg['benefit_type_code']) : '';

		if ($reg['funding_source_code'] != 'KC') {

			$funding_source = ', '.value_generic($reg['funding_source_code'],$def,'funding_source_code','list');

		}

		if (dateof(orr($reg['clinical_reg_date_end'],'now'),'SQL') < dateof('now','SQL')) {

			$output = 'Expired ' . blue($prog) . ' ('.$type_link.$funding_source.') Registration from ' . blue(dateof($reg['clinical_reg_date'])) . '-->' .blue(dateof($reg['clinical_reg_date_end']));

		} elseif (!in_array($reg['kc_authorization_status_code'],array('AA','PP','PB'))) {

			$output = acronym('Pending ','Auth Status: '.$reg['kc_authorization_status_code']) . blue($prog.$funding_source) . ' ('.$type_link.') Registration began ' . blue(dateof($reg['clinical_reg_date']));

		} else {

			$output = 'Current ' . blue($prog) . ' ('.$type_link.$funding_source.') Registration began ' . blue(dateof($reg['clinical_reg_date']));

		}

		//include Add DAL link for expired tiers within last 3 weeks
 		if (days_interval(orr($reg['clinical_reg_date_end'],'now'),'now',true) < 21) {

			$output .= div(link_quick_dal('Add DAL(s)',array('client_id'=>$id,'performed_by'=>$GLOBALS['UID'])).' | '.$expand_reg_link . $update_link,''
					   ,' style="margin: 0px 0px 0px 25px; font-size: 80%;"');

		} else {

			$output .= div($expand_reg_link,'',' style="margin: 0px 0px 0px 25px; font-size: 80%;"');

		}

	}

	$output .= clinical_pending_registrations_f($id);
	$output .= $link_reg;

	return $output;
}

function clinical_pending_registrations_f($id)
{

	$res = clinical_get_pending_registration($id);

	if (sql_num_rows($res) < 1) {
		return '';
	}

	while ($a = sql_fetch_assoc($res)) {

		$out[] = indent(elink('clinical_reg_request',$a['clinical_reg_request_id'],smaller(red('Pending '.sql_lookup_description($a['benefit_type_code'],'l_benefit_type').' registration'))));

	}

	return implode(oline(),$out);

}

function clinical_get_pending_registration($id,$type=null)
{
	$filter = client_filter($id);

	if (in_array($type,array('HOST','SAGE'))) {
		$filter['tier_program(benefit_type_code)'] = $type;
	} elseif (!be_null($type)) {
		$filter['benefit_type_code'] = $type;
	}

	return get_generic($filter,'','','clinical_reg_request_pending');
}

function medical_appointments_f($cid)
{
	$def = get_def('calendar_appointment');
	$pdal_filt = $n_filt = $p_filt = client_filter($cid);
	$n_filt['FIELD>:event_start'] = 'CURRENT_TIMESTAMP(0)';
	$p_filt['FIELD<=:event_start'] = 'CURRENT_TIMESTAMP(0)';
	$pdal_filt['dal_code']='272';

	$apps = array();

	$prev = sql_fetch_assoc(get_generic($p_filt,'event_start DESC','1','calendar_appointment_current'));
	// check dals
	if ($pdal = sql_fetch_assoc(get_generic($pdal_filt,'dal_date DESC','1','dal'))) {
		$ddate = dateof($pdal['dal_date'],'SQL');
		$clink = smaller(Calendar::link_calendar('MEDICAL',$ddate,'calendar'),2);
		$staff = staff_link($pdal['performed_by']);
		$alink = link_engine(array('object'=>'dal','id'=>$pdal['dal_id']),dateof($ddate));
		$apps[] = smaller('Last by DAL: '.$staff.' '.$alink.', ').$clink;
	}

	if ($prev) {
		$id = $prev['calendar_id'];
		$date = $prev['event_start'];
		if ($ddate !== dateof($date,'SQL')) {
			$config = Calendar_Record::grab_config($id);
			$clink = smaller(Calendar::link_calendar($id,$date,'calendar'),2);
			$type = ucfirst(strtolower($config['calendar_type_code']));
			$alink = link_engine(array('object'=>'calendar_appointment','id'=>$prev['calendar_appointment_id']),datetimeof($date,'US'));
			$staff = staff_link($config['staff_id']);
			$apps[] = smaller('Last ('.$type.'): '.$staff.' '.$alink.', ').$clink;
		}
	}

	// get one of each type of appointment
	$next_res = agency_query('SELECT DISTINCT ON (calendar_id) * FROM calendar_appointment_current',$n_filt,'calendar_id,event_start');

	while ($next = sql_fetch_assoc($next_res)) {
		$id = $next['calendar_id'];
		$date = $next['event_start'];
		$config = Calendar_Record::grab_config($id);
		$clink = smaller(Calendar::link_calendar($id,$date,'calendar'),2);
		$type = ucfirst(strtolower($config['calendar_type_code']));
		$alink = link_engine(array('object'=>'calendar_appointment','id'=>$next['calendar_appointment_id']),datetimeof($date,'US'));
		$staff = staff_link($config['staff_id']);
		array_unshift($apps,smaller('Next ('.$type.'): '.$staff.' '.$alink.', ').$clink);
		$next_e = true;
	}

	if ($next_e || $prev || $pdal) {
		return implode('<br />',$apps);
	}
	return false;
}

function generate_list_long_dal($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	if ($control['format'] != 'long') {
		return generate_list_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,$rec_num);
	}

	$pos = $control['list']['position'];
      $mx  = $control['list']['max'];

      while ( $x<$mx and $pos<$total) {
		$a = sql_to_php_generic(sql_fetch_assoc($result,$pos),$def);

		if (be_null($a['dal_progress_note_id'])) {
			$out .= div(view_dal($a,$def,'list',$control),'','style="width: 100%; margin: 20px 0px; "'); 

			$x++;
		}
		$pos++;
	}

	$links = list_links($max,$position,$total,$control,$control_array_variable);
	return table(row(cell(list_control($control,$def,$control_array_variable,$format='CUSTOM'),'colspan="2"'))
			 . row(leftcell(list_total_records_text($control,$position,$total,$max,$def),'class="listHeader"')
				 .rightcell($links),'class="listHeader"')
			 . row(cell($out,'colspan="2"'))
			 . row(rightcell($links,'colspan="2"'),'class="listHeader"'),'','class="" cellpadding="0" cellspacing="0"');
			 
}

function view_dal($rec,$def,$action,$control='',$control_array_variable='control')
{
	$original_rec = $rec;
	if ($control['format'] != 'long') {
		return view_generic($rec,$def,$action,$control,$control_array_variable);
	}

	foreach ($rec as $key => $value) {
		$x = $value;
		$rec[$key] = $def['fields'][$key]
			? eval('return '. $def['fields'][$key]['value_'.$action].';')
			: $value;
	}

	$summary = row(cell(value_generic($rec['dal_date'],$def,'dal_date',$action)) 
			   . cell(value_generic($rec['dal_code'],$def,'dal_code',$action))
			   . cell(value_generic($rec['total_minutes'],$def,'total_minutes',$action))
			   . cell(value_generic($rec['dal_location_code'],$def,'dal_location_code',$action))
			   . cell(value_generic($rec['contact_type_code'],$def,'contact_type_code',$action)) 
			    );

	if (be_null($rec['progress_note'])) { //progress note exists in other record
		
	} elseif ( ($other_res = $def['fn']['get'](array('dal_progress_note_id'=>$rec[$def['id_field']]),$def['id_field'],'',$def))
		     && (sql_num_rows($other_res) > 0) ) { //referencing records
		while ($a = sql_fetch_assoc($other_res)) {
			$summary .= row(cell(value_generic($a['dal_date'],$def,'dal_date',$action)) 
					    . cell(link_engine(array('object'=>$def['object'],'id'=>$a[$def['id_field']],'format'=>'data'),
								     value_generic($a['dal_code'],$def,'dal_code',$action)))
					    . cell(value_generic($a['total_minutes'],$def,'total_minutes',$action)) 
					    . cell(value_generic($a['dal_location_code'],$def,'dal_location_code',$action))
					    . cell(value_generic($a['contact_type_code'],$def,'contact_type_code',$action)) 
					    );
		}
		
	} else { //no referencing records

	}

	$total_rows = ($other_res ? sql_num_rows($other_res) : 0) +2;

	$filter=orr($control['list']['filter'],array());
	if (!array_key_exists('client_id',$filter)) { //not client-specific, add name
		$other_stuff = oline($def['singular'].' for '.client_link($rec['client_id']));
	}

	$other_stuff .= 
		oline('Performed By: '.value_generic($rec['performed_by'],$def,'performed_by',$action))
		. oline('Added At: '.value_generic($rec['added_at'],$def,'added_at','view'))
		. (!be_null($rec['dal_focus_area_codes']) 
		   ? oline(label_generic('dal_focus_area_codes',$def,$action).' :').
		   oline(value_generic($rec['dal_focus_area_codes'],$def,'dal_focus_area_codes',$action))
		   : '')
		. ($action != 'view' ? html_no_print(link_engine(array('object'=>'dal','id'=>$rec['dal_id']),'View')) : '');
	

	$summary = row(topcell($other_stuff,'style="white-space: nowrap; border-right: solid 1px black;" rowspan="'.($total_rows).'"')
			   .centercell(bold(label_generic('dal_date',$def,$action)))
			   .centercell(bold(label_generic('dal_code',$def,$action)))
			   .centercell(bold(label_generic('total_minutes',$def,$action)))
			   .centercell(bold(label_generic('dal_location_code',$def,$action)))
			   .centercell(bold(label_generic('contact_type_code',$def,$action)))
			   ) . $summary;

	if (!be_null($original_rec['progress_note'])) {
		$prog_note = value_generic($rec['progress_note'],$def,'progress_note',$action);
	}

	if (!be_null($original_rec['dal_follow_up_id'])) {
		$prog_note = oline(bold(red('This is a follow-up to ')
						. link_engine(array('object'=>'dal','id'=>$original_rec['dal_follow_up_id']),$def['singular'].' #'.$original_rec['dal_follow_up_id'])),3) 
			. $prog_note;
	}

	$out = table($summary 
			 . ($prog_note 
			    ? row(cell(div($prog_note,
						 '','class="generalTable" style="font-size: 1.2em; padding: 10px; border-top: solid 1px black;"')
					   ,'colspan="6"'))
			    : '')
			 ,'','cellspacing="0" cellpadding="0" class="textHeader" style="border-left: solid 1px #efefef; border-top: solid 1px #efefef; border-right: solid 2px #afafaf; border-bottom: solid 2px #afafaf; padding: 0px; font-size: 85%; width: 100%"');
	return $out;

}

function active_clinical_select_to($var,$default='',$opts='')
{
	$query = "SELECT client_id AS value, client_name(client_id) AS label FROM clinical_reg_current ORDER BY 2";

 	static $pick,$old_default;
 	if (!$pick or $default != $old_default) {
// 		$pick = do_pick_sql($query,$default,$add_null=true);
		//using custom function in order to include expired clients, if passed as default
		$result = agency_query($query);
		$clients = array();
		while ($a = sql_fetch_assoc($result)) {
			$clients[$a['value']] = $a['label'];
		}
		$pick = selectitem('','(none or n/a or blank)');
		if ($default and !in_array($default,array_keys($clients))) {
			$pick .= selectitem($default,client_name($default,0,$text_only = true),true);
		} 
		foreach ($clients as $value => $label) {
			$pick .= selectitem($value,$label,$default==$value,$opts);
		}
		$old_default = $default;
 	}

	return selectto($var,$opts) . $pick . selectend();
}

function link_quick_dal($label,$rec_init=array(),$options='')
{
	return link_multi_add('dal',$label,$rec_init,$options);
}

function dal_progress_note_summary($rec)
{
	global $AG_DAL_PROGRESS_NOTE_OPTIONAL;

	if (be_null($rec['dal_id'])) {
		return '';
	}

	if ($rec['dal_id'] < AG_DAL_CUTOFF) {
		return 'N/A';
	} elseif (in_array($rec['dal_code'],$AG_DAL_PROGRESS_NOTE_OPTIONAL)
		    && be_null($rec['dal_progress_note_id'])
		    && be_null($rec['progress_note'])) {
		$out = 'Progress Note Optional';
	} elseif (be_null($rec['dal_progress_note_id']) 
		    && be_null($rec['progress_note'])
		    ) {
		$out = link_engine(array('object'=>'dal','action'=>'edit','id'=>$rec['dal_id']),red('Enter Progress Note'));
	} elseif (!be_null($rec['dal_progress_note_id'])) {
		$out = link_engine(array('object'=>'dal','id'=>$rec['dal_progress_note_id']),'See DAL '.$rec['dal_progress_note_id']);
	} else {
		$out = 'Progress Note Exists';
	}

	//follow-up link
	if (dal_allow_follow_up($rec)) {
		$out .= oline() . link_quick_dal(smaller("Add Follow-Up DAL"),
							   array("client_id"=>$rec["client_id"],"dal_follow_up_id"=>$rec["dal_id"]
								   ,"performed_by"=>$GLOBALS["UID"]));
	} elseif (!be_null($rec['dal_follow_up_id'])) {
		$out .= oline() . link_engine(array('object'=>'dal','id'=>$rec['dal_follow_up_id']),
							'This DAL is a follow-up to DAL #'.$rec['dal_follow_up_id']);
	}

	return $out;
}

function dal_allow_follow_up($rec)
{
	global $AG_DAL_MEDICAL_CODES;
	return be_null($rec['dal_follow_up_id']) 
		&& !be_null($rec['progress_note']) 
		&& in_array($rec['dal_code'],$AG_DAL_MEDICAL_CODES);
}

function dal_valid_medical_codes($rec)
{

	global $AG_DAL_MEDICAL_ONLY_CODES;

	if (be_null($rec['dal_code']) or be_null($rec['performed_by'])) { return true; } //this is invalid, but caught elsewhere

	$proj = staff_project($rec['performed_by']);
	$perm = has_perm('medical','RW',$rec['performed_by']);

	if (!in_array($rec['dal_code'],$AG_DAL_MEDICAL_ONLY_CODES)) {

		return true;

	} elseif ($proj != 'MEDICAL' && $perm != 'MEDICAL') {

		return false;

	} else {

		return true;

	}

}

function dal_valid_staff_qualifications($rec)
{
	$dal_code = $rec['dal_code'];

	// get staff qualifications for given code
	$qres = agency_query('SELECT staff_qualification_codes FROM l_dal',array('dal_code' => $dal_code,
												     '!NULL:staff_qualification_codes' => ''));

	// check if code has requirements
	if (sql_num_rows($qres) < 1) {

		return true; //valid

	}

	$qrec = sql_fetch_row($qres);
	$requirements = sql_to_php_array($qrec[0]);

	// get staff qualifications
	$staff_id = $rec['performed_by'];
	$sq_res = get_generic(staff_filter($staff_id),'','','staff_qualification');

	$quals = sql_fetch_column($sq_res,'staff_qualification_code');

	// one of the staff qualifications must be present in the global array
	return !be_null(array_intersect($quals,$requirements));

}

function recent_medical_dals_f()
{
	global $UID, $AG_USER_OPTION, $AG_DAL_MEDICAL_CODES;

	$proj = staff_project($UID);

	if ($proj != 'MEDICAL') {
		return '';
	}

	$def = get_def('dal');

	$output = '';

	foreach (array('host','sage', 'pact') as $program) {
		//these settings are stored across sessions
		$hide = $AG_USER_OPTION->show_hide('recent_medical_dals_'.$program.'_f');
		$show_hide_link = $AG_USER_OPTION->link_show_hide('recent_medical_dals_'.$program.'_f');
		
		$width = $hide ? ' boxHeaderEmpty' : '';
		
		$filter = array(
				    /*
				     * limiting to last month's worth of DALs greatly increases 
				     * performance, and 40 dals are done within a few weeks.
				     */
				    'FIELD>:dal_date' => 'CURRENT_DATE - \'2 months\'::interval',

				    'IN:dal_code' => $AG_DAL_MEDICAL_CODES,
				    'staff.agency_project_code'=>'MEDICAL',
				    'tier_program(benefit_type_code)' => strtoupper($program)
				    );

 		$sql = 'SELECT dal.*,
 					tier_program(benefit_type_code)
 			  FROM dal 
 				LEFT JOIN clinical_reg_current USING (client_id)
                        LEFT JOIN staff ON (staff.staff_id = dal.performed_by ) ';

		$link_all = link_engine(array('action'=>'list',
//fixme: get this to work as generic_sql_query -- current problem involves auth_optimized_current permissions
// 							'object'=>'generic_sql_query',
// 							'sql'=>$sql,
							'object'=>'dal',
							'list'=>array('filter'=>$t_filter,
									  'order'=>array('dal_date'=>true,'dal_id'=>true),
									  'fields'=>array_merge(array('client_id'),$def['list_fields'])
									  )),smaller('complete list',2));
		
			
		$out = row(cell(bold(white('Recent Medical DALs ('.strtoupper($program).')'))
				    .oline().$link_all . $show_hide_link,'class="boxHeader'.$width.'"'),' class="'.$program.'"');
		
		if (!$hide) {

			$res = agency_query($sql,
						$filter,'dal_date DESC, dal_id DESC',40);
			
			while ($a = sql_fetch_assoc($res)) {
				$color = $color=='1' ? '2' : '1';
				
				//hide-show js for pn
				if (!be_null($a['progress_note'])) {
					$id = 'recentMed'.ucfirst($program).$a['dal_id'];
					$notelink = smaller(Java_Engine::toggle_id_display(smaller('expand note',2),$id,'block')). ' | ';
					$clink = smaller(Java_Engine::toggle_id_display('close',$id,'block'));
					$note = div(left($clink) 
							. webify(wordwrap($a['progress_note'],80))
							. right($clink),$id,
							' style="display: none; position: fixed; width: 450px height: 300px; right: 50px; top: 50px;" class="floatingBox"');
				} else { 
					$note = $notelink = ''; 
				}

				$follow_up_link = dal_allow_follow_up($a)
					? ' | '.link_quick_dal(alt('Follow-Up DAL','Add Follow-Up DALs for this progress note'),
								     array('client_id'=>$a['client_id'],'performed_by'=>$UID,
									     'dal_follow_up_id'=>$a['dal_id'])
								     ,'class="fancyLink"')
					: '';
				$out .= row(cell(smaller(client_link($a['client_id'],client_name($a['client_id'],25)),2)),' class="generalData'.$color.'"')
					. row(cell(smaller(link_engine(array('object'=>'dal','id'=>$a['dal_id']),
										 alt(value_generic($a['dal_date'],$def,'dal_date','list'),'click to view')
										 ,'',' class="fancyLink"').' | '
								 .' '. value_generic($a['performed_by'],$def,'performed_by','list')
								 . $note
								 . right($notelink
									   .'Add '.link_quick_dal(alt('DAL(s)','Multiple DALs'),
													 array('client_id'=>$a['client_id'],'performed_by'=>$UID)
													 ,'class="fancyLink"')
									   .$follow_up_link),2)),
						' class="generalData'.$color.'" style="white-space: nowrap;"');
			}
		}
		$output .= table($out,'',' style="border: solid 1px black; margin-top: 10px;" cellspacing="0px" cellpadding="2px"');
	}
	return $output;
}

function recent_staff_dals_missing_pn_f()
{
	global $UID, $AG_USER_OPTION, $AG_DAL_PROGRESS_NOTE_OPTIONAL;


	$prog = staff_program($UID);

	if ($prog != 'CLINICAL') {
		return '';
	}

	$proj = staff_project($UID);
	//these settings are stored across sessions
	$hide = $AG_USER_OPTION->show_hide('recent_staff_dals_missing_pn_f');
	$show_hide_link = $AG_USER_OPTION->link_show_hide('recent_staff_dals_missing_pn_f');
	
	$width = $hide ? ' boxHeaderEmpty' : '';
	
	$def = get_def('dal');
	$filter = array('performed_by'=>$UID,
			    'FIELD>:dal_id'=>AG_DAL_CUTOFF,
			    'NULL:progress_note'=>'',
			    'NULL:dal_progress_note_id'=>'',
			    '!IN:dal_code'=>$AG_DAL_PROGRESS_NOTE_OPTIONAL);
	
	$link_all = link_engine(array('object'=>'dal','action'=>'list',
						'list'=>array('filter'=>$filter,'order'=>'added_at DESC')),smaller('Show all',2));

	$class = $proj=='SAGE' ? 'sage' : 'host';

	$out = row(cell(bold(white('Recent DALs Needing Progress Notes'))
			    . oline() . $link_all . $show_hide_link,'class="boxHeader'.$width.'"'),' class="'.$class.'"');

	if (!$hide) {

		$res = get_generic($filter,'added_at DESC','15',$def);

		if (sql_num_rows($res) < 1) { return ''; }

		while ($a = sql_fetch_assoc($res)) {
			$color = $color=='1' ? '2' : '1';
			$out .= row(cell(smaller(client_link($a['client_id'],client_name($a['client_id'],25))
							 . right(link_engine(array('object'=>'dal','id'=>$a['dal_id']),
										   alt(value_generic($a['dal_code'],$def,'dal_code','list'),'click to view'),
										   '',' class="fancyLink"').' '
								   . value_generic($a['dal_date'],$def,'dal_date','list').' '
								   . link_engine(array('object'=>'dal','action'=>'edit','id'=>$a['dal_id']),
										     red('enter note'),'',' class="fancyLink"')
								   ),2)),
					' class="generalData'.$color.'" style="white-space: nowrap;"');
		}
		
	}
	return table($out,'',' style="border: solid 1px black; margin-top: 10px;" cellspacing="0px" cellpadding="2px"');
}

function my_staff_dals_missing_pn_f()
{
	global $UID, $AG_USER_OPTION, $AG_DAL_PROGRESS_NOTE_OPTIONAL;


	$prog = staff_program($UID);

	if ($prog != 'CLINICAL') {
		return '';
	}

	//supervisors only
	$sup_staff_res = get_generic(array('supervised_by'=>$UID),'','','staff');
	if (sql_num_rows($sup_staff_res) < 1) {
		return '';
	}

	$sup_staff = sql_fetch_column($sup_staff_res,'staff_id');

	//these settings are stored across sessions
	$hide = $AG_USER_OPTION->show_hide('my_staff_dals_missing_pn_f');
	$show_hide_link = $AG_USER_OPTION->link_show_hide('my_staff_dals_missing_pn_f');
	
	$width = $hide ? ' boxHeaderEmpty' : '';
	
	$def = get_def('dal');
	$filter = array('IN:performed_by'=>$sup_staff,
			    'FIELD>:dal_id'=>AG_DAL_CUTOFF,
			    'FIELD<:dal_date'=>'CURRENT_DATE - \'10 days\'::interval',
			    'NULL:progress_note'=>'',
			    'NULL:dal_progress_note_id'=>'',
			    '!IN:dal_code'=>$AG_DAL_PROGRESS_NOTE_OPTIONAL);
	
	$link_all = link_engine(array('object'=>'dal','action'=>'list',
						'list'=>array('filter'=>$filter,'order'=>'added_at')),smaller('Show all',2));

	$out = row(cell(bold(white('My Staff'.oline().'Missing Progress Notes'))
			    . oline() . $link_all . $show_hide_link,'class="boxHeader'.$width.'"'),' class="sage"');

	if (!$hide) {

		$res = get_generic($filter,'added_at','15',$def);

		if (sql_num_rows($res) < 1) { return ''; }

		while ($a = sql_fetch_assoc($res)) {
			$color = $color=='1' ? '2' : '1';
			$out .= row(cell(smaller(staff_link($a['performed_by'])
							 . right(link_engine(array('object'=>'dal','id'=>$a['dal_id']),
										   alt(value_generic($a['dal_date'],$def,'dal_date','list'),'click to view'),
										   '',' class="fancyLink"').' ('.$a['dal_id'].')'
								   ),2)),
					' class="generalData'.$color.'" style="white-space: nowrap;"');
		}
	}

	return table($out,'',' style="border: solid 1px black; margin-top: 10px;" cellspacing="0px" cellpadding="2px"');

}

// Quick-DAL functions

function multi_record_allow_common_fields_dal($rec_init)
{
	global $UID;
	$arr_keys = array_keys($rec_init);
	return in_array('performed_by',$arr_keys) 
		and in_array('client_id',$arr_keys)
		and in_array('dal_date',$arr_keys)
		and ($rec_init['performed_by'] == $UID);
}

function init_form_dal($def,$defaults,$control)
{
	foreach ($def['multi_add']['init_fields'] as $key) {
		$def['fields'][$key]['null_ok']=true;
		if ($key == 'client_id') {
			$cell = active_clinical_select_to('rec_init['.$key.']',$defaults[$key]);
		} else {
			$cell = form_field_generic($key,$defaults[$key],$def,$control,$Java_Engine,'rec_init');
		}
		$row .= rowrlcell(label_generic($key,$def,'add'),$cell);
	}

	return help('',oline('Please fill in the fields that will be the same for ALL the DALs you are going to enter. ',2)
				 . 'For Drop-In DALs, select the drop-in staff member, and the date. Do NOT fill in the Client field. ','',true)
		. table($row)
		. (!be_null($defaults['dal_follow_up_id']) ? hiddenvar('qdal_dal_follow_up_id',$defaults['dal_follow_up_id']) : '');
}

function form_list_row_dal($number,$rec,$def,$control)
{
	$fields = array_keys($def['fields']);
	$action = $control['action'];

	$i = 0;
	foreach ($fields as $field) {
		$f_def = $def['fields'][$field];
		if ($f_def['display_'.$action]=='hide') {
			if (in_array($field,$def['multi_add']['common_fields'])) {
				//nothing
			} else {
				$hids .= hiddenvar('RECS['.$number.']['.$field.']',$rec[$field]);
			}
			continue;
		}
		if ($field == 'client_id') {
			$cell = active_clinical_select_to('RECS['.$number.']['.$field.']',$rec[$field]);
		} else {
			$cell = form_field_generic($field,$rec[$field],$def,$control,$Java_Engine,'RECS['.$number.']');
		}
		$row .= cell($cell);
	}

	return $row . $hids;

}

function form_list_dal($RECS,$def,$control,$errors,$rec_init)
{
	if ($tmp_id = $RECS[0]['dal_follow_up_id']) {
		global $engine;
		//display progress note from follow-up
		$res = get_generic(array('dal_id'=>$tmp_id),'','','dal');
		$t_rec = sql_fetch_assoc($res);
		$follow_up_progress_note = div(html_heading_4('You are adding a follow-up DAL for the following progress note (DAL '.$tmp_id.'):')
							 . value_generic(webify($t_rec['progress_note']),$engine['dal'],'progress_note','view'),'',
							 ' style="width: 70%; border: solid 1px black; background-color: #efefbb; padding: 5px;"');
	}
	return $follow_up_progress_note . form_list_generic($RECS,$def,$control,$errors,$rec_init);
}

function multi_record_passed_dal($rec,$rec_init)
{
	$pass_field = in_array('dal_date',array_keys($rec_init)) ? 'total_minutes' : 'dal_date';
	$client = !in_array('client_id',array_keys($rec_init)) && !be_null($rec['client_id']);
	$dal_code = !in_array('dal_code',array_keys($rec_init)) && !be_null($rec['dal_code']);

	return !be_null($rec[$pass_field])
		|| !be_null($rec['progress_note'])
		|| !be_null($rec['dal_focus_area_codes'])
		|| $client
		|| $dal_code;
}

function multi_add_after_post_dal($message,$rec,$def,$rec_init)
{
	//for PATH clients
	if (in_array('client_id',array_keys($rec_init)) and is_path_tracking_form_eligible($rec['client_id'])) {
		agency_top_header();
		outline($message);
		out(html_heading_3('If applicable, please take the time to fill out a Path Tracking Form for this client.'));
		out(call_engine(array('action'=>'add','object'=>'path_tracking','page'=>'display.php',
					    'rec_init'=>array('client_id'=>$rec['client_id'],
								    'service_date'=>$rec['dal_date'],
								    'performed_by'=>$rec['performed_by'])),
				    'control',false,false,$dummy1,$dummy2));
		page_close();
		exit;
	}
}

function multi_add_blank_dal($def,$rec_init)
{
	$tm_r = multi_add_blank_generic($def,$rec_init);
	if ($tmp_id = $_REQUEST['qdal_dal_follow_up_id']) {
		$tm_r[0]['dal_follow_up_id'] = $tmp_id;
	}
	return $tm_r;
}

// end Quick-DAL functions

function conditional_release_f($id)
{
	if (!$def = get_def('conditional_release')) {
		return '';
	}

	$res = get_generic(client_filter($id),'conditional_release_date DESC','','conditional_release_current');

	if ( sql_num_rows($res) < 1) {
		// not on lra or cr
		return '';
	}

	$a = sql_fetch_assoc($res);

	$label = red('Less Restrictive Alternative/Conditional Release starting '.value_generic($a['conditional_release_date'],$def,'conditional_release_date','list'));

	return oline(qelink($a,$def,$label));
}

/* COD screening */

function form_row_cod_screening($key,$value,&$def,$control,&$Java_Engine,$rec)
{
	/*
	 * For the first question in each IDS, EDS, SDS section, prepend with
	 * over-arching category question
	 */

	if (preg_match('/^[ies]ds_data_status_code$/',$key)) {
		//these fields are completed below, at a different point
		return '';
	}

	if (preg_match('/^([ies])ds_question_a_code$/',$key,$m)) {
		$missing_key = $m[1].'ds_data_status_code';
		$missing = right(form_field_generic($missing_key,$rec[$missing_key],$def,$control,$Java_Engine));
		switch ($m[1]) {
		case 'i' :
			$prepend = row(cell(bold('1) During the past 12 months, have you had significant problems') . $missing,'colspan="2"'));
			break;
		case 'e':
			$prepend = row(cell(bold('2) During the past 12 months, did you do the following things two or more times?') . $missing,'colspan="2"'));
			break;
		case 's':
			$prepend = row(cell(bold('3) During the past 12 months, did...') . $missing,'colspan="2"'));
			break;
		}
	}

	return $prepend . form_generic_row($key,$value,$def,$control,$Java_Engine,$rec);
}

function view_row_cod_screening($key,$value,$def,$action,$rec)
{
	/*
	 * For the first question in each IDS, EDS, SDS section, prepend with
	 * over-arching category question
	 */

	if (preg_match('/^[ies]ds_data_status_code$/',$key)) {
		//these fields are completed below, at a different point
		return '';
	}

	if (preg_match('/^([ies])ds_question_a_code$/',$key,$m)) {
		$missing_key = $m[1].'ds_data_status_code';
		$missing = right(value_generic($rec[$missing_key],$def,$missing_key,$action));
		switch ($m[1]) {
		case 'i' :
			$prepend = row(cell(bold('1) During the past 12 months, have you had significant problems') . $missing,'colspan="2"'));
			break;
		case 'e':
			$prepend = row(cell(bold('2) During the past 12 months, did you do the following things two or more times?') . $missing,'colspan="2"'));
			break;
		case 's':
			$prepend = row(cell(bold('3) During the past 12 months, did...') . $missing,'colspan="2"'));
			break;
		}
	}

	return $prepend . view_generic_row($key,$value,$def,$action,$rec);
}


function form_row_conditional_release($key,$value,&$def,$control,&$Java_Engine,$rec)
{

	if ($key=='previous_reference_number')
	{
	
	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec) . row(cell(bold('Conditional Release/Less Restrictive Alternative Requirements'), 'colspan="2"'));
	}		
	
	if ($key=='comment')
	{
	 
	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec) . row(cell(bold('Conditional Release/Less Restrictive Alternative Outcome'), 'colspan="2"'));
	}

	if ($key=='compliance_plan_appointment')
	{
 
	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec) . row(cell('C: Client will take prescribed medications and comply with all lab tests.', 'colspan="2"'));
	}

	if ($key=='compliance_plan_medication')
	{
 
	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec) . row(cell('D: Client will refrain from use of alcohol and unprescribed drugs and comply with random urinalysis if requested.', 'colspan="2"'));
	}

	if ($key=='compliance_plan_substance')
	{
 
	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec) . row(cell('E: Client will refrain from acts, attempts and threats of harm to self, others or other\'s property.', 'colspan="2"'));
	}

	if ($key=='compliance_plan_threat')
	{
 
	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec) . row(cell('F: Client will possess no firearms.', 'colspan="2"'));
	}

	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec);
}


function view_row_conditional_release($key,$value,$def,$action,$rec)
{

	if ($key=='previous_reference_number')
	{
	
	return view_generic_row($key,$value,$def,$action,$rec) . row(cell(bold('Conditional Release/Less Restrictive Alternative Requirements'), 'colspan="2"'));
	}		
	
	if ($key=='comment')
	{
	 
	return view_generic_row($key,$value,$def,$action,$rec) . row(cell(bold('Conditional Release/Less Restrictive Alternative Outcome'), 'colspan="2"'));
	}

	if ($key=='compliance_plan_appointment')
	{
 
	return view_generic_row($key,$value,$def,$action,$rec) . row(cell('C: Client will take prescribed medications and comply with all lab tests.', 'colspan="2"'));
	}

	if ($key=='compliance_plan_medication')
	{
 
	return view_generic_row($key,$value,$def,$action,$rec) . row(cell('D: Client will refrain from use of alcohol and unprescribed drugs and comply with random urinalysis if requested.', 'colspan="2"'));
	}

	if ($key=='compliance_plan_substance')
	{
 
	return view_generic_row($key,$value,$def,$action,$rec) . row(cell('E: Client will refrain from acts, attempts and threats of harm to self, others or other\'s property.', 'colspan="2"'));
	}

	if ($key=='compliance_plan_threat')
	{
 
	return view_generic_row($key,$value,$def,$action,$rec) . row(cell('F: Client will possess no firearms.', 'colspan="2"'));
	}

	return view_generic_row($key,$value,$def,$action,$rec);
}


function dal_cod_record_exists($rec)
{
	/*
	 * For COD assessment/screening DALs only, verifies that an
	 * assessment or screening record with matching data exists.
	 */

	$code = $rec['dal_code'];

	switch ($code) {
	case '630' :
		$type = 'screening';
		break;
	case '631' :
		$type = 'assessment';
		break;
	default :
		//return true for non-COD DALs
		return true;
	}

	$def = get_def('cod_'.$type);

	//find matching record
// 	$performed_by = $rec['performed_by']; removing this requirement - bug 21060
	$date         = dateof($rec['dal_date'],'SQL');
	$client_id    = $rec['client_id'];

	if (be_null($date) or be_null($client_id)) {
		// can't query until these required fields are entered
		return true;
	}

	$filter = array('client_id'   => $client_id,
// 			    $type.'_by'   => $performed_by, removing this requirement - bug 21060
			    $type.'_date' => $date);

	$res = get_generic($filter,'','',$def);

	return sql_num_rows($res) > 0;

}

function dal_is_sent_to_county($code)
{
	$filter = array('dal_code' => $code);

	$res = agency_query('SELECT * FROM l_dal',$filter);

	$a = sql_fetch_assoc($res);

	return !be_null($a['cpt_code']) && $a['cpt_code'] !== 'xx3xx';
}

function agency_menu_clinical()
{
	// DSHS import/export EDI medicaid lookup files
	$menu['Links'] = html_list(
					   html_list_item(hlink_if('dshs_edi.php','Import/Export DSHS Medicaid Lookup Files',has_perm('dshs_medicaid')))
					   );

	return array($menu,$errors);
}

/* Import Functions */

function import_king_county_res($file_array,$basename,&$error) {

	global $UID;

	$total_transactions = -2; //don't count header and footer

	static $auth_actions_def, $auth_response_def, 
		$auth_actions_template, $auth_response_template;

	if (!$auth_actions_def) {

		$auth_actions_def = get_def('kc_authorization_actions_taken');
		
		$auth_actions_template = unset_system_fields_all(array_flip(array_keys($auth_actions_def['fields'])));
		unset($auth_actions_template[$auth_actions_def['id_field']],$auth_actions_template['king_county_batch_number'],
			$auth_actions_template['king_county_batch_date']);

		$auth_actions_template = array_keys($auth_actions_template);

	}

	if (!$auth_response_def) {

		$auth_response_def = get_def('kc_authorization_response');

		$auth_response_template = unset_system_fields_all(array_flip(array_keys($auth_response_def['fields'])));
		unset($auth_response_template[$auth_response_def['id_field']],$auth_response_template['king_county_batch_number'],
			$auth_response_template['king_county_batch_date']);

		$auth_response_template = array_keys($auth_response_template);

	}

	sql_begin();
	foreach ($file_array as $line) {

		$parsed = explode("\t",rtrim($line,"\n")); //get rid of linebreak
		$trans = array_shift($parsed);

		$total_transactions ++;
		switch ($trans) {
		case '901.01' :
			//archived in kc_authorization_actions_taken
			$action = array_shift($parsed);
			if ($action != 'A') {
				$error .= oline('Warning, first element od 901.01 transaction is something other than "A"');
				return false;
			}

			$new_rec = array_combine($auth_actions_template,$parsed);
			$new_rec['king_county_batch_number'] = $batch_number;
			$new_rec['king_county_batch_date'] = $batch_date;
			$new_rec['changed_by'] = $new_rec['added_by'] = $UID;

	
			$res = agency_query(sql_insert($auth_actions_def['table_post'],$new_rec));

			if (!$res) {
				sql_abort();
				$error .= 'Failed to insert 901.01 transaction for batch '.$batch_number.'. The entire file needs to be re-imported.';
				return false;
			}

			break;
		case '902.01' :
			//archived in kc_authorization_response
			$action = array_shift($parsed);
			if ($action != 'A') {
				$error .= oline('Warning, first element od 902.01 transaction is something other than "A"');
				return false;
			}

			$new_rec = array_combine($auth_response_template,$parsed);
			$new_rec['king_county_batch_number'] = $batch_number;
			$new_rec['king_county_batch_date'] = $batch_date;
			$new_rec['changed_by'] = $new_rec['added_by'] = $UID;

			$res = agency_query(sql_insert($auth_response_def['table_post'],$new_rec));

			if (!$res) {
				sql_abort();
				$error .= 'Failed to insert 902.01 transaction for batch '.$batch_number.'. The entire file needs to be re-imported.';
				return false;
			}

			break;
		case '999.01': //footer
			$reported_total_transactions = $parsed[3];
			break;
		case '000.03': //header
			$batch_date = date_iso_to_sql($parsed[1]);
			$batch_number = $parsed[0]; //this gets set first
			//make sure batch hasn't already been imported
			$filter = array('king_county_batch_number' => $batch_number);
			$res1 = get_generic($filter,'','',$auth_actions_def);
			$res2 = get_generic($filter,'','',$auth_response_def);

			if (sql_num_rows($res1) > 0 || sql_num_rows($res2) > 0) {

				sql_abort();
				$error .= oline('Batch '.$batch_number.' appears to have already been imported.');
				return false;
			}

			break;
		default:
			$error .= oline('Unknown transaction type: '.$trans);
		}

		if (is_null($batch_number)) {
			$error .= oline('File '.$basename.' missing batch number--cannot import');
			return false;
		}

		
	}
	sql_end();

	if ($reported_total_transactions != $total_transactions) {
		$error .= oline('Warning for file '.$basename.': Total transactions ('.$total_transactions.') doesn\'t match reported number ('.$reported_total_transactions.')');
	}

	return true;
}

function import_king_county_kcid($file_array,$basename,&$error) {

	global $UID;

	foreach ($file_array as $line) {

		// new staff kcid
		if (preg_match('/^(NC)?\s+([0-9]+)\s+([0-9]+)\s+AB/',$line,$m)) {

			$staff_kcid = $m[2];
			$staff_mh_id = $m[3];

			// make sure kcid is blank
			$res = get_generic(array('NULL:kc_staff_id' => '', 'old_mh_id' => $staff_mh_id),'','','staff');
			if (sql_num_rows($res) == 1) {

				$up_res = agency_query(sql_update('tbl_staff',array('kc_staff_id' => $staff_kcid,
												  'changed_by'  => $UID,
												  'FIELD:changed_at' => 'CURRENT_TIMESTAMP'),
									  array('old_mh_id' => $staff_mh_id),
									  '*'));

			}

			// verify match
			$res = get_generic(array('kc_staff_id' => $staff_kcid,'old_mh_id' => $staff_mh_id),'','','staff');
			if (sql_num_rows($res) != 1) {

				$error .= oline('There appears to be a kcid/linkage mismatch ('.$staff_kcid.'/'.$staff_mh_id.') in file '.$basename);
				return false;

			}

		}

		// new client kcid
		if (preg_match('/^(NC)?\s+([0-9]+)\s+([0-9]+)$/',$line,$m)) {

			$client_kcid = $m[2];
			$client_clinical_id = $m[3];

			// make sure kcid is blank
			$res = get_generic(array('NULL:king_cty_id' => '', 'clinical_id' => $client_clinical_id),'','','client');
			if (sql_num_rows($res) == 1) {

				$up_res = agency_query(sql_update('tbl_client',array('king_cty_id' => $client_kcid,
												   'changed_by' => $UID,
												   'FIELD:changed_at' => 'CURRENT_TIMESTAMP'),
									  array('clinical_id' => $client_clinical_id),
									  '*'));

			}

			// verify match
			$res = get_generic(array('king_cty_id' => $client_kcid,
							 'clinical_id' => $client_clinical_id),'','','client');
			if (sql_num_rows($res) != 1) {
				
				$error .= oline('There appears to be a kcid/clinical ID mismatch ('.$client_kcid.'/'.$client_clinical_id.') in file '.$basename.'. Please review');
				return false;

			}

		}

	}

	return true;


}

function import_king_county_medicaid_verification($file_array,$basename,&$error)
{

	global $UID;

	static $kcmv_def, $kcmv_template;
	if (!$kcmv_def) {

		$kcmv_def = get_def('kc_medicaid_verification');

		$kcmv_template = unset_system_fields_all(array_flip(array_keys($kcmv_def['fields'])));
		unset($kcmv_template[$kcmv_def['id_field']],$kcmv_template['king_county_batch_number'],
			$kcmv_template['king_county_batch_date'],$kcmv_template['report_generated_at'],
			$kcmv_template[AG_MAIN_OBJECT_DB.'_id']);

		$kcmv_template = array_keys($kcmv_template);

	}

	sql_begin();
	/*
	 * find batch number, check if imported
	 */
	preg_match('/mvtx([0-9]{4})/',$basename,$m);
	$batch_number = $m[1];
	$filter = array('king_county_batch_number' => $batch_number);
	$res = get_generic($filter,'','',$kcmv_def);
	if (sql_num_rows($res) > 0) {

		sql_abort();
		$error .= oline('Batch '.$batch_number.' appears to have already been imported.');
		return false;
	}

	foreach ($file_array as $line) {

		/*
		 * First time around, grab common info out of header
		 */
		if (preg_match('/^Medicaid\sVerification\sReport\s*RUN\s*([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s([:0-9]{5})\s*AGENCY/',$line,$m)) {

			$generated_at = dateof($m[1],'SQL').' '.timeof($m[2].':00','SQL');

		} elseif (preg_match('/^[0-9]*\s[0-9]*/',$line)) {

			$parsed = explode("\t",rtrim($line,"\n"));

			$new_rec = array_combine($kcmv_template,$parsed);
			$new_rec['report_generated_at'] = $generated_at;
			$new_rec['king_county_batch_number'] = $batch_number;
			$new_rec['king_county_batch_date'] = dateof($generated_at,'SQL');
			$new_rec['changed_by'] = $new_rec['added_by'] = $UID;

			$new_rec['benefit_start_date'] = dateof($new_rec['benefit_start_date'],'SQL');
			$new_rec['benefit_expire_date'] = dateof($new_rec['benefit_expire_date'],'SQL');

			// set client id
			$new_rec[AG_MAIN_OBJECT_DB.'_id'] = client_get_id_from_clinical_id($new_rec['clinical_id']);

			$res = agency_query(sql_insert($kcmv_def['table_post'],$new_rec));

			if (!$res) {

				sql_abort();
				$error .= 'Failed to insert Medicaid Verification Report for batch '.$batch_number.'. The entire file ('.$basename.') needs to be re-imported.';
				return false;
			}


		}

	}
	sql_end();

	return true;
}

function import_king_county_medicaid_authorization_jeopardy($file_array,$basename,&$error)
{

	global $UID;

	static $kcmaj_def, $kcmaj_template;
	if (!$kcmaj_def) {

		$kcmaj_def = get_def('kc_medicaid_authorization_jeopardy');

		$kcmaj_template = unset_system_fields_all(array_flip(array_keys($kcmaj_def['fields'])));
		unset($kcmaj_template[$kcmaj_def['id_field']],$kcmaj_template['king_county_batch_number'],
			$kcmaj_template['king_county_batch_date'],$kcmaj_template['report_generated_at'],
			$kcmaj_template['potential_cancellation_date'],$kcmaj_template[AG_MAIN_OBJECT_DB.'_id']);

		$kcmaj_template = array_keys($kcmaj_template);

	}

	sql_begin();
	/*
	 * find batch number, check if imported
	 */
	preg_match('/mvct([0-9]{4})/',$basename,$m);
	$batch_number = $m[1];
	$filter = array('king_county_batch_number' => $batch_number);
	$res = get_generic($filter,'','',$kcmaj_def);
	if (sql_num_rows($res) > 0) {

		sql_abort();
		$error .= oline('Batch '.$batch_number.' appears to have already been imported.');
		return false;
	}

	foreach ($file_array as $line) {

		/*
		 * First time around, grab common info out of header
		 */
		if (preg_match('/[a-z\s]*([0-9]{2}\/[0-9]{2}\/[0-9]{4})[a-z\s]*([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s([:0-9]{5})/i',$line,$m)) {

			$generated_at = dateof($m[2],'SQL').' '.timeof($m[3].':00','SQL');
			$potential_cancellation_date = dateof($m[1],'SQL');

		} elseif (preg_match('/NO\sAUTHORIZATIONS\sIN\sJEOPARDY\sOF\sCANCEL*/', $line)) {
                        sql_abort();
                        return true;

		} elseif (preg_match('/^[0-9]*\s[0-9]*/',$line)) {

			$parsed = explode("\t",rtrim($line,"\n"));

			$new_rec = array_combine($kcmaj_template,$parsed);
			$new_rec['report_generated_at'] = $generated_at;
			$new_rec['potential_cancellation_date'] = $potential_cancellation_date;
			$new_rec['king_county_batch_number'] = $batch_number;
			$new_rec['king_county_batch_date'] = dateof($generated_at,'SQL');
			$new_rec['changed_by'] = $new_rec['added_by'] = $UID;

			$new_rec['benefit_start_date'] = dateof($new_rec['benefit_start_date'],'SQL');
			$new_rec['benefit_end_date'] = dateof($new_rec['benefit_end_date'],'SQL');

			// set client id
			$new_rec[AG_MAIN_OBJECT_DB.'_id'] = client_get_id_from_clinical_id($new_rec['clinical_id']);

			$res = agency_query(sql_insert($kcmaj_def['table_post'],$new_rec));

			if (!$res) {

				sql_abort();
				$error .= 'Failed to insert Medicaid Authorizations in Jeopardy Report for batch '.$batch_number.'. The entire file ('.$basename.') needs to be re-imported.';
				return false;
			}

		}
		
	}
	sql_end();

	return true;
}

function import_king_county_jail($file_array,$basename,&$error)
{
	sql_begin();
	foreach ($file_array as $line) {

		if (preg_match('/^\s*[0-9]{1,6}\s*[0-9]{1,5}\s*[A-Z]*/',$line)) {

			$case_id  = substr($line,11,7);
			$date_in  = trim(substr($line,101,10));
			$date_out = trim(substr($line,115,10));

			//find client_id
			$client = get_generic(array('clinical_id'=>$case_id),'','','client');

			if (sql_num_rows($client) !== 1) {

				$error .= 'There is no corresponding client_id for case_id '.$case_id.' in tbl_client. Import of '.$basename.' has been aborted. Please review.';
				sql_abort();

				return false;
			}

			$client    = sql_fetch_assoc($client);
			$client_id = $client['client_id'];

			$rec = array('client_id'     => $client_id,
					 'jail_date'     => $date_in,
					 'jail_date_end' => $date_out);

			jail_record_insert_update($rec,'KC','E',$error,false);

		}
		
	}
	sql_end();
	return true;

}

function import_king_county_hospital($file_array,$basename,&$error)
{
	global $UID;

	sql_begin();
	foreach ($file_array as $line) {

		if (preg_match('/^\s*(INV|VOL)?\s*([A-Z]{2})?\s*[0-9]{1,6}\s*[0-9]{1,5}\s*[A-Z]*/',$line)) {

			$case_id  = substr($line,21,4);
			$date_in  = trim(substr($line,62,10));
			$date_out = trim(substr($line,73,10));
			$facility = trim(substr($line,84,25));
			$this_vol  = trim(substr($line,1,3));
			
			/*
			 * The voluntary/involuntary status is only reported with the first record
			 * of each type, so below is the logic to keep track of which type is 
			 * being imported.
			 */

			$cur_vol   = be_null($this_vol) ? $old_vol : $this_vol;
			$voluntary = $cur_vol == 'VOL';
			$old_vol   = $cur_vol;


			//find client_id
			$client = get_generic(array('clinical_id'=>$case_id),'','','client');

			if (sql_num_rows($client) !== 1) {

				$error .= 'There is no corresponding client_id for case_id '.$case_id.' in tbl_client. Import of '.$basename.' has been aborted. Please review';
				sql_abort();
				return false;

			}

			$client    = sql_fetch_assoc($client);
			$client_id = $client['client_id'];

			// find existing record, if there is one
			$filter    = array('client_id' => $client_id, 'hospital_date' => $date_in);
			$existing  = get_generic($filter,'','','hospital');

			/*
			 * Already in DB?
			 *
			 * 0 Rows = no
			 * 1 Row  = yes, check whether end date is missing (and gets added)
			 *          or if it exists, in which case it should match
			 * 2 Rows = problem!
			 */

			if (sql_num_rows($existing)>1) {

				$error .= "Found more than one record for Case ID $client_id on $date_in. Import of ".$basename.' has been aborted. Please review';
				return false;

			} elseif (sql_num_rows($existing) == 1) {

				$existing = sql_fetch_assoc($existing);

				if ($existing['hospital_date_end']) { // release record already exists

					if (!$date_out) {

						// admit record, release already posted, nothing to do

					} elseif (dateof($date_out)<>dateof($existing['hospital_date_end'])) {

						$error .= "Hospital Import: Conflicting release dates for Case ID $client_id on $date_in\n"
							."Existing date: ".dateof($existing[$db_end_field])."\n"
							."Auto-import date: ".dateof($date_out);
						continue;

					}

				} elseif ($date_out) { // no errors, post release if not already

					// update, set release date
					$new_rec = array('hospital_date_end'             => dateof($date_out,'SQL'),
							     'hospital_date_end_source_code' => 'KC',
							     'hospital_date_end_accuracy'    => 'E',
							     'changed_by'                    => $UID,
							     'FIELD:changed_at'              => 'CURRENT_TIMESTAMP');

					$result = sql_query(sql_update('tbl_hospital',$new_rec,array('client_id'=>$client_id,'hospital_date'=>$date_in)));

				}

			} else { // record doesn't yet exist in DB

				// post record
				$new_rec=array('client_id'         => $client_id,
						   'hospital_date'     => dateof($date_in,'SQL'),
						   'hospital_date_end' => dateof($date_out,"SQL"),
						   'is_voluntary'      => $voluntary ? sql_true() : sql_false(),
						   'facility'          => $facility,
						   'added_by'          => $UID,
						   'changed_by'        => $UID);

				if (!be_null($new_rec['hospital_date_end'])) {

					$new_rec['hospital_date_end_source_code'] = 'KC';
					$new_rec['hospital_date_end_accuracy']    = 'E';

				}

				$result = sql_query(sql_insert('tbl_hospital',$new_rec));

			}

		}

	}

	sql_end();

	return true;
}

function import_king_county_payment($file_array,$basename,&$error)
{
	global $UID;

	$type = (strpos($file_array[0],'Payment Data') === false) ? 'adjustment' : 'payment';

	sql_begin();
	foreach ($file_array as $line) {

		if (preg_match('/^\s*[0-9]{6}\s*/',$line)) {

			$rec = explode("\t",$line);
			foreach ($rec as $k => $v) {

				$rec[$k] = trim($v);

			}

			switch ($type) {

			case 'payment' :
                        $new_rec['authorization_number'] = $rec[0];
                        $case_id                         = $rec[1];

				// find client
                        $client = get_generic(array('clinical_id'=>$case_id),'','','client');
                        if (sql_num_rows($client) !== 1) {

					$error .= oline('There is no corresponding client_id for case_id '.$case_id.' in tbl_client. Import of '.$basename.' has been aborted. Please review.');
					  sql_abort();
					  return false;

                        }

                        $client = sql_fetch_assoc($client);

                        $new_rec['client_id']               = $client['client_id'];
                        $new_rec['payment_amount']          = $rec[4];
                        $new_rec['case_rate_code']          = $rec[5];
                        $new_rec['payment_period_date']     = dateof($rec[6],'SQL');
                        $new_rec['payment_period_date_end'] = dateof($rec[7],'SQL');
                        $new_rec['payment_month']           = $rec[8];
                        $new_rec['payment_year']            = $rec[9];
                        $new_rec['payment_date']            = $rec[10];

				break;

			case 'adjustment' :

                        $new_rec['authorization_number'] = $rec[0];
                        $case_id = $rec[1];

				// find client
                        $client = get_generic(array('clinical_id'=>$case_id),'','','client');

                        if (sql_num_rows($client) !== 1) {

					$error .= oline('There is no corresponding client_id for case_id '.$case_id.' in tbl_client. Import of '.$basename.' has been aborted. Please review.');
					sql_abort();
					return false;
                        }

                        $client = sql_fetch_assoc($client);

                        $new_rec['client_id'] = $client['client_id'];
                        $new_rec['adjustment_amount'] = $rec[3];
				
                        if ($d=dateof($rec[4],'SQL')) {
                                $new_rec['payment_period_date'] = $d;
                        }

                        if ($d=dateof($rec[5],'SQL')) {
                                $new_rec['payment_period_date_end'] = $d;
                        }

                        $new_rec['adjustment_year']        = $rec[6];
                        $new_rec['adjustment_month']       = $rec[7];
                        $new_rec['adjustment_type_code']   = $rec[8];
                        $new_rec['adjustment_reason_code'] = $rec[9];
                        $new_rec['payment_date']           = $rec[12];

				break;
			}

			$client_id = $new_rec['client_id'];
			$filter    = $new_rec;
			$existing = get_generic($filter,'','','payment_kc');

			if (sql_num_rows($existing)>1) {
				
				$error .= oline("Found more than one record for Case ID $client_id on {$new_rec['payment_date']}");
				continue;
				
			} elseif (sql_num_rows($existing)==1) {
				
                        //no real updates possible, import just goes...or it doesn't
				
			} else { // record doesn't yet exist in DB
				
                        $new_rec['added_by']   = $UID;
                        $new_rec['changed_by'] = $UID;

                        $result = sql_query(sql_insert('tbl_payment_kc',$new_rec));

                }


		}


	}
	sql_end();

	return true;

}

function clinical_reg_verify_type_client_combination($client_id,$type,$date='',&$message)
{

	global $AG_SCRIPT_CONFIG_CLINICAL_REG;

	require_once SCRIPT_CONFIG_FILE_DIRECTORY.'/'.AG_CLINICAL_REG_CONFIG_FILE;

	$script_config = $AG_SCRIPT_CONFIG_CLINICAL_REG[$type];

	$date = orr($date,dateof('now','SQL'));

	switch ($type) {

	case 'host_60' : // cannot currently be enrolled in HOST
	case 'host_61' : // cannot currently be enrolled in HOST
	case 'pact_57' :
	case 'pact_58' :

		$proj = $script_config['benefit_project'];

		if (!be_null(clinical_get_tier($client_id,$date,$proj))) {

			// check if benefit has been termina

			$message .= oline('Client is already enrolled in '. $proj .' for '.dateof($date));
			return false;
		}

		// or pending HOST
		if (sql_num_rows(clinical_get_pending_registration($client_id,$proj)) > 0) {
			$message .= oline('Client has a pending '. $proj .' registration');
			return false;
		}
		break;

	case 'sage' : // cannot currently be enrolled in SAGE

		if (!be_null(clinical_get_tier($client_id,$date,'SAGE'))) {
			$message .= oline('Client is already enrolled in SAGE for '.dateof($date));
			return false;
		}

		// or pending SAGE
		if (sql_num_rows(clinical_get_pending_registration($client_id,'SAGE')) > 0) {
			$message .= oline('Client has a pending SAGE registration');
			return false;
		}

		break;
	case 'sage_cob' :
		//must be currently, or recently enrolled in SAGE
		if (sql_num_rows(get_generic(array(AG_MAIN_OBJECT_DB.'_id'=>$client_id,
							     'FIELDBETWEEN:clinical_reg_date_end' => 'CURRENT_DATE - 60 AND CURRENT_DATE + 30',
							     'tier_program(benefit_type_code)' => 'SAGE'),'','','clinical_reg')) < 1) {

			$message .= oline('No SAGE benefits to process a COB on');
			return false;
		}
		break;
	case 'sage_exit' :
		if (sql_num_rows(get_generic(array(AG_MAIN_OBJECT_DB.'_id'=>$client_id,
							     'FIELD>=:clinical_reg_date' => 'CURRENT_DATE - \'1 year 30 days\'::interval',
							     'tier_program(benefit_type_code)' => 'SAGE'),'','','clinical_reg')) < 1) {

			$message .= oline('No SAGE benefits to exit');
			return false;
		}
		break;
	case 'host_60_exit' :
	case 'host_61_exit' :
	case 'pact_57_exit' :
	case 'pact_58_exit' :
	

		$proj = $script_config['benefit_project'];
		$btc = $script_config['benefit_type_code'];

		//fixme: this is overly generous.jh. 
		//Made less generous by adding benefit_type_code_constraint. jb.
		if (sql_num_rows(get_generic(array(AG_MAIN_OBJECT_DB.'_id'=>$client_id,
							     'NULL:clinical_reg_date_end' => '',
							     'benefit_type_code' => $btc,
							     'tier_program(benefit_type_code)' => $proj),'','','clinical_reg')) < 1) {
			$message .= oline('No '. $proj .' registrations to exit');
			return false;
		}

	}

	return true;
}

function link_clinical_reg($id,$rec)
{

	global $AG_SCRIPT_CONFIG_CLINICAL_REG;

	require_once SCRIPT_CONFIG_FILE_DIRECTORY.'/'.AG_CLINICAL_REG_CONFIG_FILE;

	foreach ($AG_SCRIPT_CONFIG_CLINICAL_REG as $type => $config) {

		$mesg = '';
		if (clinical_reg_verify_type_client_combination($id,$type,'',$mesg)) {
			$link = hlink(AG_CLINICAL_REG_PAGE . '?type='.$type.'&client_id='.$id,$config['title']);

			if (preg_match('/update/i',$type)) {

				$link .= '&nbsp;' . hlink(AG_CLINICAL_REG_PAGE . '?type='.$type.'&step=print_update&client_id='.$id,red(smaller('Print Update')));

			}

		} else {
			$link = dead_link(alt($config['title'],strip_tags($mesg)));
		}

		$output .= html_list_item($link);

	}

	return html_list($output);

}

function link_clinical_reg_update($id,$program,$project)
{
	global $AG_SCRIPT_CONFIG_CLINICAL_REG;
	
	require_once SCRIPT_CONFIG_FILE_DIRECTORY.'/'.AG_CLINICAL_REG_CONFIG_FILE;
	
	$program = strtolower($program);
	$project = strtolower($project);

	
	if ($config = $AG_SCRIPT_CONFIG_CLINICAL_REG[$program.'_update']) {
		$type = $program.'_update';
	
	} elseif ($config = $AG_SCRIPT_CONFIG_CLINICAL_REG[$program.'_'.$project.'_update']) {
		$type = $program.'_'.$project.'_update';

	} else {
		return '';

	}

	return hlink(AG_CLINICAL_REG_PAGE . '?type='.$type.'&step=print_update&client_id='.$id,red(smaller('Print Update')));

}

function clinical_link_kc_authorization_response($auth_id)
{
	/*
	 * returns links to kc_authorization_response and kc_authorization_actions_taken
	 */

	$response = link_engine(array('object' => 'kc_authorization_response',
						'action' => 'list',
						'list' => array('filter' => array('authorization_number' => $auth_id))),
					'view KC Authorization Response Records for this registration');
	
	$actions = link_engine(array('object' => 'kc_authorization_actions_taken',
						'action' => 'list',
						'list' => array('filter' => array('authorization_number' => $auth_id))),
					'view KC Authorization Actions Taken Records for this registration');

	return oline($auth_id) . oline($response) . $actions;

}

function clinical_kc_transmission_link($object,$id)
{

	/*
	 * Function returns a link to a list of exports, and a
	 * link to re-flag the record for upload
	 */

	if (!has_perm('clinical_data_entry') || !is_view('export_kc_'.$object)) {

		// Nothing to do if the export view doesn't exist
		return false;

	}

	$def = get_def('export_kc_transaction');

	$filter = array('object_type' => $object,
			    'object_id' => $id);

	$res = get_generic($filter,'','',$def);

	$count = sql_num_rows($res);

	if ( $count > 0 ) {

		$links .= oline(link_engine(array('action' => 'list',
							    'object' => $def['object'],
							    'list' => array('filter' => $filter)),'This record has been sent to KC '.$count.' '.str_plural('time',$count)));

	}

	$links .= link_engine(array('action' => 'add','object' => $def['object'],'rec_init'=>$filter),'Flag for KC upload');

	return $links;


}

function clinical_valid_exit_reason_combination($rec)
{
	/*
	 * For case-rate benefits, must have a case-rate code, etc.
	 */

	$filter = array();

	$prog = tier_to_project($rec['benefit_type_code']);

	$filter['clinical_exit_reason_code'] = $rec['clinical_exit_reason_code'];

	if ($prog == 'SAGE') { //case-rate

		$filter['!NULL:case_rate_code'] = '';

	} else { //non-case-rate

		$filter['!NULL:non_case_rate_code'] = '';

	}

	$res = agency_query('SELECT * FROM l_clinical_exit_reason',$filter);

	return sql_num_rows($res) == 1;
}

?>
