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


class Safe_Harbors_Client {

	function Safe_Harbors_Client($client_id,$start,$end)
	{
		// verify Safe Harbors export id
		if (!$this->verify_create_safe_harbors_id($client_id)) {
			$this->invalid = true;
			return false;
		}

		$res = get_generic(client_filter($client_id),'','',SAFE_HARBORS_CLIENT_VIEW);

		//test for safe harbors consent
		$shc_res = get_generic(client_filter($client_id),'','','safe_harbors_consent');
		if (count($shc_res)>0) {
			global $engine;
			$shc_def = $engine['safe_harbors_consent'];
			$this->shc_rec = sql_to_php_generic(array_shift($shc_res),$shc_def);
		}
		
		$this->start = $start;
		$this->end = $end;

		if (count($res)==1) {
		     

			$rec = array_shift($res);
			$rec = $this->scrub_safe_harbors_record($rec);

			foreach ($rec as $key=>$value) {
				$this->$key = $value;
			}

			// no dashes in ssn
			$this->ssn = str_replace('-','',$this->ssn);

			//get bed nights for period
			$this->bed_nights = $this->get_bed_nights($start,$end);

			//get income information
			$this->income = $this->get_income();


		} else {
			//error handling
				
		}

		$this->get_shelter_reg();

		$this->last_changed_at = $this->set_last_changed_at();
	}

	function get_ssn_quality()
	{
		/*	
		 *	SSN Quality Code Options:
		 *		1 = Full SSN Given
		 *		2 = Partial SSN reported
		 *		8 = Don't know or don't have SSN
		 *		9 = Refused
		 */
		if ($this->ssn=='999999999') {
			return 8;
		} elseif (in_array('SSN_P',orr($this->shc_rec['safe_harbors_exclude_data_codes'],array()))) {
			return 2;
		} elseif (be_null($this->ssn)) {
			return 9;
		} else {
			return 1;
		}
	}

	function set_last_changed_at()
	{
		$last = array($this->income->changed_at,$this->changed_at,$this->shelter_reg_changed_at);
		sort($last);
		return array_pop($last)."\n";
	}

	function get_bed_nights($start,$end)
	{
		$filter = client_filter($this->client_id);
		$filter['BETWEEN:bed_date'] = new date_range($start,$end);
		$filter['!bed_group'] = 'kerner';
		$res = get_generic($filter,'bed_date','','bed');

		$nights = array();
		if (count($res) > 0) {
			while ($a = array_shift($res)) {
				$nights[] = new Safe_Harbors_Program_Participation($a,$this);
			}
		} else {
			//error handling
		}

		return $nights;
	}

	function get_shelter_reg()
	{
		$filter = $this->generate_filter('shelter_reg');
		$rec = array_shift(get_generic($filter,'shelter_reg_date DESC','1','shelter_reg'));
		if ($rec) {
			/* set shelter reg dependent vars */
			$this->domestic_violence = trim($rec['svc_need_code'])=='3'
				? call_sql_function('safe_harbors_value',enquote1(sql_true())) : null;

			$this->shelter_reg_changed_at = $rec['changed_at'];

		}
	}

	function get_income()
	{
		$filter = $this->generate_filter('income');
		$res = get_generic($filter,'income_date DESC','1',SAFE_HARBORS_INCOME_VIEW);

		$rec = array_shift($res);

		return new Safe_Harbors_Income(orr($rec,array()));
	}

	function has_disabling_condition()
	{
		/*
		 * Disabling Condition Options:
		 *	0 = No
		 *	1 = Yes
		 *	8 = Don't know
		 *	9 = Refused
		 */

		// any disability yes, else don't know
		$filter = $this->generate_filter('disability');
		
		$res = get_generic($filter,'','','disability');

		return count($res)<1
			? '8'
			: '1';
	}

	function generate_filter($object)
	{
		$filter = client_filter($this->client_id);
		$filter['<=:'.$object.'_date'] = $this->end;
		$filter[] = array('NULL:'.$object.'_date_end'=>'',
					'>=:'.$object.'_date_end'=>$this->start);
		return $filter;
	}

	function verify_create_safe_harbors_id($id)
	{
		$filter = client_filter($id);
		$filter['export_organization_code'] = 'SAFE_HARB';

		$res = get_generic($filter,'','','client_export_id');
		if ( count($res)<1 ) {
			$rec = array('client_id'=>$id,
					 'export_organization_code'=>'SAFE_HARB',
					 'FIELD:export_id'=>'make_safe_harbors_id()',
					 'FIELD:added_by'=>'sys_user()',
					 'FIELD:changed_by'=>'sys_user()',
					 'sys_log'=>'Auto-created Safe Harbors ID at export time');
 			$res = agency_query(sql_insert('tbl_client_export_id',$rec));
			if (!$res) { 
				log_error('Failed to auto-create Safe Harbors ID for client: '.$id); 
				return false;
			}
		}
		return true;
	}

	function scrub_safe_harbors_record($rec)
	{
		$excludes = $this->shc_rec['safe_harbors_exclude_data_codes'];
		if (!be_null($excludes)) {
			foreach ($excludes as $data_element) {
				switch ($data_element) {
				case 'SSN_P':
					//last 4-digits only
					$rec['ssn'] = str_replace('-','',$rec['ssn']);
					$rec['ssn'] = preg_replace('/^[0-9]{5}/','99999',$rec['ssn']);
					break;
				case 'NAME':
					$rec['name_last'] = $rec['name_first'] = $rec['name_middle'] = $rec['name_suffix'] = '';
					break;
				case 'ADDRESS':
					break;
				case 'GENDER':
				case 'DOB':
				case 'SSN':
				case 'ETHNICITY':
				default:
					unset($rec[strtolower($data_element)]);
				}
			}
		}
		//also remove identifiers (except gender) if client refused to sign, or staff didn't show form
		if ($this->exclude_all_identifiers()) {
			foreach (array('ssn','name_last','name_first','name_middle','name_suffix',
					   'dob','ethnicity') as $tmp_el) {
				unset($rec[$tmp_el]);
			}

			if ($this->shc_rec['added_at'] < SAFE_HARBORS_REMOVED_GENDER) {
				
				// gender isn't set if form was signed prior to removal of gender
				unset($rec['gender']);

			}

		}

		return $rec;
	}

	function exclude_all_identifiers()
	{
		return be_null($this->shc_rec)
			|| in_array($this->shc_rec['safe_harbors_consent_status_code'],array('NO_SHOW','NO_SIGN','REFUSED'))
			|| sql_true($this->shc_rec['domestic_violence_situation']);
	}

} // end Safe_Harbors_Client class

class Safe_Harbors_Program_Participation {

	// for now, this is a glorified bed night record
	
	function Safe_Harbors_Program_Participation($rec,$sh_client)
	{

		$this->sh_client = $sh_client;
		$this->shc_rec = $sh_client->shc_rec;
		foreach ($rec as $key => $value) {
			$this->$key = $value;
		}

		$this->set_program();
		$this->set_prior_living_situation();
		//maybe will want to set 'reason for leaving' 
		//$this->set_reason_for_leaving();
	}

	function get_services()
	{
		return array(new Safe_Harbors_Service($this->bed_date));
	}

	function set_program()
	{
		switch ($this->bed_group) {
		case 'muni':
			$this->program = 'QA';
			break;
		case 'kerner':
			$this->program = 'KSH';
			break;
		default:
			$this->program = 'MAIN';
		}
	}

	function set_prior_living_situation()
	{
		/*
		 * For 1st shelter, prior situation or unknown if missing.
		 * If spent last night at agency, report.  Otherwise,
		 * report living situation if homeless, disregard
		 * otherwise.
		 */

		// 1) determine if first stay (ever?!)
		$filter = client_filter($this->client_id);
		$filter['<:bed_date']=$this->bed_date;
		$first = get_generic($filter,'','1','bed');

		// 2) determine if last night
		$filter = client_filter($this->client_id);
		$filter['bed_date']=prev_day($this->bed_date);
		$last = get_generic($filter,'','1','bed');

		if (sql_num_rows($last)==1) {
			//last night logic
			$days = call_sql_function('continuous_shelter_stay',
							  $this->client_id,
							  enquote1(prev_day($this->bed_date)));
			if ($days==0) {
				log_error('continuous_shelter_stay appears to be broken for client: '.$this->client_id);
			} else {
				$length = hud_days($days);
			}
			
			$this->prior = array('residence_code'=>'1',
						   'length_of_stay'=>$length
						   );
						   
		 
		} else {

			//get living situation record (most recent record _prior_ to bednight)
			$filter = client_filter($this->client_id);
			$filter['<=:residence_date'] = $this->bed_date;
			$residence = array_shift(get_generic($filter,'residence_date DESC','1','housing_history'));
			
			if(count($first)==1) /* not first stay, check living situation*/ {
				if ($residence) {
					$report = 'HOMELESS' == sql_lookup_description($residence['living_situation_code'],'l_living_situation','','housing_status');
					$res_rec = $report ? $residence : false;
				}
			} elseif (count($first)<1) /* first stay, always report */{
				$res_rec = $residence;
			}

	
		$this->prior = array();
			if ($res_rec and 
			    $r_code = call_sql_function('safe_harbors_value',enquote1('l_facility'),
								  enquote1($res_rec['living_situation_code']))) {
				$this->prior['residence_code'] = $r_code;
				$this->prior['length_of_stay'] = hud_days(days_interval($res_rec['residence_date'],
													  orr($res_rec['residence_date_end'],
														dateof('now','SQL'))));
				if ($zip = $res_rec['zipcode']) {
					$this->prior['zip'] = array('code'=>$zip,'quality'=>'1');
				}
				if ($city = $res_rec['city']) {
					$this->prior['city'] = $city;
				}
				if ($state = $res_rec['state_code']) {
					$this->prior['state'] = $state;
				}

				if (in_array('ADDRESS',orr($this->shc_rec['safe_harbors_exclude_data_codes'],array()))
				    || $this->sh_client->exclude_all_identifiers()) {
					$this->prior['zip'] = array('code'=>'99999','quality'=>'9'); 
					//set city and state null or something here?
					$this->prior['city'] = '';
					$this->prior['state'] = '';
				}
			} else {
				$this->prior = array(); //there isn't an unknown option
			}
		}
		return;
	}

	function set_reason_for_leaving(){
		/* how about:
		 ** if barred 4, if moved to housing 1, if died 9, if they've left (??) 10
		 ** what if they haven't left?
		 ** for example, for 10 consecutive bednights, what should the reason for leaving be for the first 9? 
		 ** -jb
		 */

	}

} // end Safe_Harbors_Program_Participation

class Safe_Harbors_Service {

	function Safe_Harbors_Service($date)
	{
		//this is assuming only one service per night, and very basic.
		//later, can be extended to include other services
		//but logic will need complete re-work

		$this->date_start = $date;
		$this->date_end = next_day($date); 
		$this->quantity = 1;
		$this->unit = 1;
		$this->type = HUD_ORG_SERVICE_TYPE;
	}

} // end Safe_Harbors Service


class Safe_Harbors_Income {

	function Safe_Harbors_Income($rec)
	{
		if (!be_null($rec)) {
			$this->has_income_data = true;
			foreach($rec as $key=>$value) {
				$this->$key = $value;
			}
			
			$this->incomes = array(array($this->income_primary_code,$this->monthly_income_primary));
			if ($this->income_secondary_code) {
				$this->incomes[] = array($this->income_secondary_code,$this->monthly_income_secondary);
			}

			if ($this->income_interest_code) {
				$this->incomes[] = array($this->income_interest_code,$this->monthly_interest_income);
			}

			$this->other = array_filter(array($this->other_assistance_1_code,
								    $this->other_assistance_2_code));
			$this->employed = in_array('1' /* safe harbors value */,array($this->income_primary_code,$this->income_secondary_code))
				? '1' : null;
		} else {
			$this->has_income_data = false;
		}
	}
} // end Safe_Harbors_Income

function hud_days($days)
{
	if ($days<8) {
		/*
		 *	Length of Stay in previous place options:
		 *		1 = One week or less
		 *		2 = More than one week, but less than one month
		 *		3 = One to three months
		 *		4 = More than three months, but less than one year
		 *		5 = One year or longer
		 */
		$length = '1';
	} elseif ($days < 31) {
		$length = '2';
	} elseif ($days <= 90) {
		$length = '3';
	} elseif ($days < 365) {
		$length = '4';
	} else {
		$length = '5';
	}

	return $length;
}

function hud_get_clients($start,$end)
{
	$filter['BETWEEN:bed_date'] = new date_range($start,$end);
	$filter['!bed_group'] = 'kerner';

	$res = agency_query('SELECT DISTINCT client_id FROM bed',$filter,'client_id');

	$clients = array();
	while ($a = sql_fetch_assoc($res)) {
		set_time_limit(240);
		$tmp = new Safe_Harbors_Client($a['client_id'],$start,$end);
		if (!$tmp->invalid) {
			$clients[] = $tmp;
		}
	}
	return $clients;
}

?>
