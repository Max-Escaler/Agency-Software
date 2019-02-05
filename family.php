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

function family_status_f($id) {
	// Get all households this person has been a part of
	$fdef=get_def('family_member');
	$f1=array(
		'client_id'=>$id,
		'household_head_id'=>$id
		);
	$f2=array('NULL:family_member_date_end'=>'dummy',
			'>=:family_member_date_end'=>target_date()
		);
	$filter=array($f1,$f2);
	$households=get_generic($filter,NULL,NULL,$fdef);
	$hh_unique=array_unique(array_fetch_column($households,'household_head_id'));
/*
	if (count($households)==0) {
		$add_dep_link=add_link('family_member','Add a dependent to this household','target="_blank"',array('household_head_id'=>$id));
		$add_dep_link2=add_link('family_member','Add this person as a dependent to another household','target="_blank"',array('family_member_id'=>$id));
		return oline($add_dep_link) . $add_dep_link2;
	}
*/
	// List all households
	$hh_processed=array();
//	while ($household=array_shift($households)) {
	foreach ($hh_unique as $hh) {
		//$hh=$household['household_head_id'];
		if (in_array($hh,$deps_processed)) {
			continue;
		} else {
	//		$hh_processed[]=$hh;
		}

//		$active_hh=($household['family_member_date_end'] and ($family_member_date_end < target_date())) ? false : true;
//		$out[]='HOH: ' . ($is_head ? client_name($id) : client_link($hh));
		if (true) {
			$f2=array('household_head_id'=>$hh);
			$fms=get_generic($f2,'household_head_id='.$id.',family_member_date DESC',NULL,$fdef);
			//$fms[]=array('client_id'=>$hh,'is_head'=>true,'family_member_date_end'=>$household['family_member_date_end']); // Add HOH
			$fms[]=array('client_id'=>$hh,'is_head'=>true); // Add HOH
			foreach($fms as $fm) {	
				$dep=$fm['client_id'];
		if (in_array($dep,$deps_processed)) {
			continue;
		}
				$is_self = ($dep==$id) ? true : false;
				$is_former=$fm['family_member_date_end'] and ($fm['family_member_date_end'] < dateof('now','SQL'));
				$found_self=orr($found_self,($is_self and (!$is_former)));
				$line = ($is_self ? bold(client_name($dep)) : client_link($dep));
				if ($fm['is_head'] and ($is_self)) {
					$label = 'HOH';
				} elseif ($fm['is_head']) {
					$label = $saved_label;
				} elseif (!$is_self) {
						$label = smaller(elink('family_member',$fm['family_member_id'],value_generic($fm['family_relation_code'],$fdef,'family_relation_code','list')));
				} else {
						$label = smaller(value_generic($fm['family_relation_code'],$fdef,'family_relation_code','list'));
						$saved_label = smaller(elink('family_member',$fm['family_member_id'],'HOH'));
				}
				$line .= ($label ? " ($label)" : '');
				if ($fm['family_member_date_end'] and ($fm['family_member_date_end'] < dateof('now','SQL'))) {
					$former[]=$line;
				} else {
					$out[] = $line; 
				}
				$deps_processed[]=$dep;
			}
		}
}
		$prev_hhs_filter=array(
			array('client_id'=>$id,'household_head_id'=>$id),
			'FIELD<:COALESCE(family_member_date_end,target_date())'=>'target_date()'
		);
		if ($hh_unique) {
			$prev_hhs_filter['!IN:household_head_id']=$hh_unique;
		}
		$previous_hhs=get_generic($prev_hhs_filter,NULL,NULL,$fdef);
		//$previous_hhs=array_unique(array_fetch_column(get_generic($prev_hhs_filter,NULL,NULL,$fdef),'household_head_id'));
		foreach ($previous_hhs as $ph_rec) {					
			if (($ph_rec['client_id']==$id) and (!in_array($ph_rec['household_head_id'],orr($deps_processed,array())))) {
				$former_hhs[]=client_link($ph_rec['household_head_id']);
outline("proc " . dump_array($deps_processed));
			} elseif (($ph_rec['household_head_id']==$id) and (!in_array($ph_rec['household_head_id'],orr($deps_processed,array())))) {
				$former[]=client_link($ph_rec['client_id']). ' (' . smaller(elink('family_member',$ph_rec['family_member_id'],value_generic($ph_rec['family_relation_code'],$fdef,'family_relation_code','list'))) .')';
			}
		}
		$tmp=array_pop($out);
		if ($tmp) { array_unshift($out,$tmp); }
		$form_h='';
		if ($former and ($former != array())) {
			$form_f .= oline('',2) . smaller(oline('Former Members'))
			. implode(oline(),$former);
		}
		if ($former_hhs and ($former_hhs != array())) {
			$form_f .= oline('',2) . smaller(oline('Former Households'))
			. implode(oline(),$former_hhs);
		}
		if (count($hh_unique) > 0 ) {
			$add_dep_link=add_link('family_member','Add a dependent to this household','target="_blank"',array('household_head_id'=>$hh));
		} else {
			$add_dep_link=oline(add_link('family_member','Add a dependent to this household','target="_blank"',array('household_head_id'=>$id)))
					. add_link('family_member','Add this person as a dependent to another household','target="_blank"',array('family_member_id'=>$id));
		}
		$hh_f[] = ($out ? (oline(implode(oline(),$out))
			. oline() ) : '')
			. smaller($add_dep_link)
			. ($form_f ?  $form_f : '');

		$out=$deps=$head=$former=array();
	return $error. implode(oline(),$hh_f);
}

?>
