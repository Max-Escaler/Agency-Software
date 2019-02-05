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

function post_charge_old( $charge, $system=false )
{
// this is copied almost verbatim from post_log.
// Need to take the generic stuff and make a post_record function.

	global $UID, $sys_user;
	$charge['added_by'] = $charge['changed_by'] = $UID;
//	$charge["added_at"]="NOW";

	if (! has_perm('rent','W')) {
		log_error('Can\'t Post Charge: You don\'t have permissions to do this!');
		return false;
	}
	if ($system && ($UID <> $sys_user)) {
		$charge['added_by']=$sys_user;
		$charge['comment'] .= ' [note: charges posted by system user, but invoked by ' 
			. staff_name($UID) . ' ('.$UID.')]';
	}
	$def = get_def('charge');
	$sql = sql_insert($def['table_post'], $charge);
	$a = sql_query( $sql ) or sql_warn('insert query failed:  '.$sql.'<br />');
	return $a ? true : false;
}

function void_charge_old( $charge_id, $comment )
{
	global $UID;
    	if (! has_perm('rent','W')) {
		log_error('Can\'t Void Charge: You don\'t have permissions to do this!');
		return false;
	}
	if ( be_null(trim($comment)) ) {
		outline('You must supply a comment to void a charge.');
		return false;
	}

	$charge = get_charges( array( 'charge_id'=>$charge_id ));
	if ($charge) {
		$values['is_void']=sql_true(); 
		$values['void_comment'] = $comment;
		$values['FIELD:voided_at']='CURRENT_TIMESTAMP';
		$values['FIELD:changed_at'] = 'CURRENT_TIMESTAMP';
		$values['voided_by'] = $values['changed_by'] = $UID;

		$filter['charge_id'] = $charge_id;

		$def = get_def('charge');
		$sql = sql_update($def['table_post'], $values, $filter);        
		$a = agency_query($sql);      
		return $a ? true : false;
	} else  {
		return false;
	}
}

function get_charges( $filter )
{
	$def = get_def('charge');
	return $def['fn']['get']($filter,'effective_date DESC','',$def);
}

function get_charges_for( $client="ALL", $filter="" )
{
	if ($client <> "ALL")
	{
		$filter["client_id"]=$client;
	}
	return get_charges( $filter );
}

// These rent functions originally in landlordd.php, brought over here
function skip_bad_ids( $res )
{
	if ($res["client_id"]==0 || $res["client_id"]==1 || $res["client_id"]=="")
	{
		outline(red("Skipping resident " . $res["residentid"] . ", with bad client ID."));
		return true;
	}
	else {
		return false;
	}
}

function post_security_deposit( $client_id, $project, $Unit, $InDate )
{
// post a security deposit for a resident
	$unit_rec = get_unit($Unit);
	if (! $unit_rec)
	{
		log_error( oline("Failed to get unit $Unit.  Can't post security deposit for $client_id"));
		return false;
	}
	$unit_rec = array_shift( $unit_rec );
	$sec_amount = $unit_rec["security_deposit"];
	$charge = "";
	$charge["charge_type_code"]="SECURITY";
	$charge["effective_date"]=$InDate;
	$charge["client_id"]=$client_id;
	$charge["housing_project_code"]=$project;
	$charge["housing_unit_code"]=$Unit;
	$charge["amount"]=$sec_amount;
	$charge["is_void"]=sql_false();
	$test = get_charges($charge);
	if (count($test)>0)
	{
		// security deposit already posted
outline("Security Deposit Already Posted for $client_id, $Unit, $InDate.  Skipping.");
		return false;
	}
	else
	{
		return post_charge_old( $charge,"SYSTEM" );
	}
}

function post_security_deposit_reverse( $client_id, $project, $Unit, $OutDate )
{
// post a reverse security deposit for a resident (upon move-out)

	$unit_rec = get_unit($Unit);
	if (! $unit_rec)
	{
		log_error( oline("Failed to get unit $Unit.  Can't post reverse security deposit for $client_id"));
		return false;
	}
	$unit_rec = array_shift( $unit_rec );
	$sec_amount = $unit_rec["security_deposit"];
	$charge = "";
	$charge["charge_type_code"]="SECURITY";
	$charge["effective_date"]=$OutDate;
	$charge["client_id"]=$client_id;
	$charge["housing_project_code"]=$project;
	$charge["housing_unit_code"]=$Unit;
	$charge["amount"]=0-$sec_amount;
	$charge["is_void"]=sql_false();
	$test = get_charges($charge);
	if (count($test)>0)
	{
		// security deposit reverse already posted
outline("Reverse Security Deposit Already Posted for $client_id, $Unit, $OutDate.  Skipping.");
		return false;
	}
	else
	{
		return post_charge_old( $charge,"SYSTEM" );
	}
}
	
/*
Attempt to spell out logic of pro-rating or not.

1) If tenant moves out,
	-->Own Project? --> Pro-rate (charge -> moveoutdate)
	-->otherwise --> full-month
2) If Income cert (rent effective date) changes during this period,
	--> Pro-rate (charge -> RentEndDate)
(case 1 & case 2 could both apply).  If neither, charge full-month
*/

function make_rent_charges($project,$unit,$clientid,$period,$month,$pro_rate_days="")
{
/*
pro_rate_days was originally implemented for SHA's unorthodox way of pro-rating
rents, based on 30-day months (irregardless of the actual days in the month).
As of 11/12/03, they are pro-rating based on actual days in the month.
With the default pro_rate_days, this should work just fine.
*/

	global $rent_subsidy_select_sql,$rent_subsidy_filter,$placeholder_date_range;
//outline("starting make_rent_charges");
	if ( ! $pro_rate_days)
	{
		$pro_rate_days=$month->days();
	}
	$rent_subsidy_filter_copy=$rent_subsidy_filter;
	foreach ($rent_subsidy_filter_copy as $key=>$value)
	{
		$rent_subsidy_filter_copy[$key]=$month->intersect($period);
	}
	$rent_subsidy_filter_copy['residence_own.housing_unit_code']=$unit;
	$rent_subsidy_filter_copy["client_id"]=$clientid;
	$rent_subsidy_filter_copy['is_income_certification']=sql_true();
// 	$rent_subsidy_filter_copy["income.housing_project_code"]=$project;
	// funky replacement to get daterange into query
	$rs_sql=ereg_replace($placeholder_date_range->start,$month->start,$rent_subsidy_select_sql);
	$rs_sql=ereg_replace($placeholder_date_range->end,$month->end,$rs_sql);
	$rent_subsidy_recs = agency_query($rs_sql,$rent_subsidy_filter_copy,
									'temp.housing_unit_subsidy_date,income.rent_date_effective');
	$display_recs = agency_query($rs_sql,$rent_subsidy_filter_copy,
									'temp.housing_unit_subsidy_date,income.rent_date_effective');

	//outline("Got this many rent_subsidy rows: " . sql_num_rows($rent_subsidy_recs));
	//outline("Here it is: " . display_recs($display_recs)); unset($display_recs);
	
	if (sql_num_rows($rent_subsidy_recs)==0)
	{
		$GLOBALS["landlord_log"].="WARNING:  Found 0 rent/subsidy recs for client $clientid, 
									unit $unit for period ". $period->display() . "\n";
		// outline(bigger(bold("WARNING:  Found 0 rent/subsidy recs for client $clientid, unit $unit for period ". $period->display())));
		return false;
	}

	while ($rec = sql_fetch_assoc($rent_subsidy_recs)) {

		$rec['contract_rent_end_date'] = orr($rec['contract_rent_end_date'],$period->end);
		$rec['rent_end_date']          = orr($rec['rent_end_date'],$period->end);

		$rent_charge_period    = new date_range( max($period->start,
									   $rec['rent_start_date'],
									   $rec['contract_rent_start_date']
									   ),
								     min($period->end,
									   $rec['rent_end_date'],
									   $rec['contract_rent_end_date']));
		$subsidy_charge_period = new date_range( max($period->start,
									   $rec['rent_start_date'],
									   $rec['contract_rent_start_date']),
								     min($period->end,
									   $rec['rent_end_date'],
									   $rec['contract_rent_end_date']));

		$rent_charge = round($rec['rent_amount_tenant'] * $rent_charge_period->days() / $pro_rate_days,2);
		$rent_comments .= 
			"\$$rent_charge charge for " . $rent_charge_period->days() . ' days from ' . $rent_charge_period->display()
			. ' @ $' . $rec['rent_amount_tenant'] . " per month.\n";
		$total_rent = $total_rent + $rent_charge;

		if ($rec['contract_rent_amount']>0) { // is there a subsidy?
		
			//outline(bold('contract rent amount found of ' . $rec['contract_rent_amount']));
			$subsidy_monthly  = $rec['contract_rent_amount']-$rec['rent_amount_tenant'];
			$subsidy_charge   = round($subsidy_monthly * $subsidy_charge_period->days() / $pro_rate_days,2);
			$subsidy_comments .= 
				"\$$subsidy_charge charge for " . $subsidy_charge_period->days() . ' days from ' 
				. $subsidy_charge_period->display() . " @ \$$subsidy_monthly (" . $rec['contract_rent_amount']
				. '-'.$rec['rent_amount_tenant'] . ") per month.\n";
			$total_subsidy = $total_subsidy + $subsidy_charge;
		}	
	}

	$total_rent                  = round($total_rent);
	$total_subsidy               = round($total_subsidy);
	$charge['housing_unit_code'] = $unit; 
	$charge['housing_project_code'] = $project;
	$charge['effective_date']    = $period->start;
	$charge['charge_type_code']  = 'RENT';
	$charge['client_id']         = $clientid;
	$charge['is_subsidy']        = sql_false();
	$charge['amount']            = $total_rent;
	$charge['period_start']      = $period->start;
	$charge['period_end']        = $period->end;
	$charge['comment']           = $rent_comments;

	post_charge_old($charge,'SYSTEM');

	// now subsidy charge
	if ($total_subsidy > 0) {

		$charge['charge_type_code']  = 'SUBSIDY';
		$charge['comment']           = $subsidy_comments;
		$charge['amount']            = $total_subsidy;
		$charge['is_subsidy']        = sql_true();
		$charge['subsidy_type_code'] = $rec['subsidy_type_code'];

		post_charge_old($charge,'SYSTEM');
	}
}

function landlord( $run_date, $recs_filter=array())
{
$DEBUG=true;
// function to run and assess rent, subsidy & security charges
	global $units_table, $residency_table,$unit_residency_select_sql,$unit_residency_filter,$UID;
	// First, do a little date calculation
	$date  = dateof($run_date,'SQL');
	$month = new date_range(start_of_month($date),end_of_month($date));

	// Put some validity checks here:
	$unit_residency_filter_copy = $unit_residency_filter;

	foreach ($unit_residency_filter_copy as $key => $value) {
		$unit_residency_filter_copy[$key]=$month;
	}

	if ($recs_filter<>array()) {
		$unit_residency_filter_copy=array_merge($recs_filter,$unit_residency_filter_copy);
	}
	//eliminate Scattered Site units from getting charges
// 	$unit_residency_filter_copy["!{$units_table}.housing_project_code"] = 'SCATTERED';
	$unit_residency_filter_copy['l_housing_project.auto_calculate_rent_charges']=sql_true();

	$unit_recs=agency_query($unit_residency_select_sql,$unit_residency_filter_copy,"housing_unit_code, residence_date");
//$show_recs=agency_query($unit_residency_select_sql,$unit_residency_filter_copy,"housing_unit_code,residence_date");
//out(display_recs( $show_recs )); unset($show_recs); 
//outline(bold("Found this many total records to analyze" . sql_num_rows($unit_recs)));
	$res_during_month=array();
// 	for ($q=0;$q<sql_num_rows($unit_recs); $q++)
	while ($rec = sql_fetch_assoc($unit_recs)) {

		set_time_limit( 30 ); // avoid timeouts

		// outline("here is the unit/res rec number $q I got: " . dump_array($rec));
		$DEBUG && out("Unit " . $rec["housing_unit_code"] . ", Client " . $rec["client_id"] . ": ");

		// IF totally blank, skip
		if ( ! ($rec["client_id"] && $rec["housing_unit_code"] && $rec["residence_date"])) {
//			$GLOBALS["landlord_log"].="WARNING VACANT UNIT: \n" . dump_array($rec);
			continue;
		}

		// add client_id to list of clients resident during month
		array_push($res_during_month,$rec["client_id"]);
		// set up filter to look for manual RENT or SUBSIDY charge
		$existing_rent_filter="";
		$existing_rent_filter["housing_unit_code"]=$rec["housing_unit_code"];
		$existing_rent_filter["client_id"]=$rec["client_id"];
		$existing_rent_filter["period_start"]=$month->start;
		$existing_rent_filter["period_end"]=$month->end;
		$existing_rent_filter["charge_type_code"]=array("RENT","SUBSIDY");
		$existing_rent_filter["is_void"]=sql_false();
		$manual_filter=$existing_rent_filter;
		$manual_filter["!added_by"]=$GLOBALS["sys_user"];  // Not the AGENCY user
		$manual_charges=get_charges($manual_filter);
		$security_deposit_filter["is_void"]=sql_false();
		$security_deposit_filter["housing_unit_code"]=$rec["housing_unit_code"];
		$security_deposit_filter["client_id"]=$rec["client_id"];
		$security_deposit_filter["charge_type_code"]="SECURITY";
// Test 1:  existing manual charge?
		if (count($manual_charges)>0) 
		{										
			// Test 1: Yes
			$DEBUG && outline("Test 1 is Yes");
			$GLOBALS["landlord_log"] .= "Skipping because manual charge found for unit " . $rec["housing_unit_code"] .
					" and client " . $rec["client_id"];
			continue;
		}										
		// Test 1: No
		// adjust blank date (current residence) to end of month
		// Adding a day to this, so move-outs can be distinguished from whole-month res.
		$rec["residence_date_end"]=orr($rec["residence_date_end"],next_day($month->end));
		$residency_period=new date_range($rec["residence_date"],$rec["residence_date_end"]);

		// Test 2: Is the residency for the whole month? (& not a move-in or move-out)
		if (($month->intersect($residency_period)==$month) 
			&& ($month->start>$residency_period->start) 
			&& ($month->end<$residency_period->end)) {										

			// Test 2: Yes
			$DEBUG && outline("Test 2 is Yes (whole-month residency, not move-in)");
			unset($existing_rent_filter["period_start"]);
			$existing_rent_filter["BETWEEN:period_start"]=$month;
			$rc=get_charges($existing_rent_filter);
			if (count($rc) > 0)
			{
				$DEBUG && outline("has charge, continue");
				// charges found--continuing
				continue;
			}
			make_rent_charges($rec["housing_project_code"],$rec["housing_unit_code"],$rec["client_id"],$month,$month);
			continue;
		}	
		// Test 2: No

		// Test 3: Is it a move-out? (& not a move-in too, as in move-in & out during month)
		if (($residency_period->end<=$month->end) && ($residency_period->start<=$month->start)) {										
			// Test 3: Yes
			$DEBUG && outline("Test 3 is Yes (move-out)");
			// Test 3A: Is it a transfer within building?
			if (substr($rec["moved_to_unit"],0,1)==substr($rec["housing_unit_code"],0,1))
			{									
				// Test 3A: Yes
				$DEBUG && outline("Test 3A is Yes (same-building transfer)");
				// period end could be one day earlier, to avoid over-charge
				// search for existing charges with either date
				$existing_rent_filter["period_end"]=array($residency_period->end,
													prev_day($residency_period->end));
				$rc=get_charges($existing_rent_filter);
				// look for charge for this time period
				if (count($rc)>0)
				{
					continue;
				}
				$existing_rent_filter["period_end"]=$month->end;
				$existing_rent_filter["charge_type_code"]=array("RENT","SUBSIDY");
				$rc=get_charges($existing_rent_filter);
				for ($cnt=0;$cnt<count($rc);$cnt++)
				{
					$rc=array_shift($rc);
					void_charge_old($rc["charge_id"],"Original whole month charge voided: Unit transfer in same building");
				}
/*
As of 11/12/03, SHA is doing traditional pro-rating, and this code is not needed:
				if ($month->days()==31) // take one day of residency, to avoid over-charge
				{
					$residency_period->end=prev_day($residency_period->end);
					// move-out on 1st?  (minus 1 day equals prev month, no charge)
					if ($residency_period->end < $month->start)
					{
						continue;
					}
				}
*/
				make_rent_charges($rec["housing_project_code"],$rec["housing_unit_code"],$rec["client_id"],$month->intersect($residency_period),$month);
				continue;
			} 
			// Test 3B:  Is it a transfer within agency's projects?
			elseif ($rec["moved_to_unit"])
			{										
				// Test 3B: Yes	
				$DEBUG && outline('Test 3B is Yes (transfer to other ' . org_name('short') . ' project');
/*
As of 11/12/03, SHA is doing traditional pro-rating, and this code is not needed:
				if ($month->days()==31) // take one day of residency, to avoid over-charge
				{
					$residency_period->end=prev_day($residency_period->end);
				}
*/
				$existing_rent_filter["period_end"]=$residency_period->end;
				$rc=get_charges($existing_rent_filter);
				// look for charge for this time period
				if (count($rc)==0) // no charges yet
				{
					$existing_rent_filter["period_end"]=$month->end;
					$existing_rent_filter["charge_type_code"]=array("RENT","SUBSIDY");
					$rcs=get_charges($existing_rent_filter);
					while ($rc=array_shift($rcs))
					{
						void_charge_old($rc["charge_id"],
						'Original whole month charge voided: tranferred to other '.$GLOBALS['AG_TEXT']['ORGANIZATION'].' project');
					}
					// do a pro-rate based on actual days
					if (! ($residency_period->end < $month->start))
					{
						make_rent_charges($rec["housing_project_code"],
											$rec["housing_unit_code"],$rec["client_id"],
										$month->intersect($residency_period),$month);
					}
				}
				post_security_deposit_reverse($rec["client_id"],$rec["housing_project_code"],
								$rec["housing_unit_code"],$residency_period->end);
				continue;
			}
			else
			// Test 3C:  (always true if executed):  moved out, to not-own project 
			{										
				// Test 3C: Yes
				$DEBUG && outline('Test 3C is Yes (moved-out, not to ' . org_name('short') . ' project');
				// charge a full month's rent
				// Look for whole month charge
				unset($existing_rent_filter["period_start"]);
				unset($existing_rent_filter["period_end"]);
				$existing_rent_filter["BETWEEN:period_start"]=$month;
				$existing_rent_filter["BETWEEN:period_end"]=$month;
				$rc=get_charges($existing_rent_filter);
				if (count($rc)==0)
				{
					$DEBUG && outline("no rent found, making rent charges");
					make_rent_charges($rec["housing_project_code"],$rec["housing_unit_code"],$rec["client_id"],$month,$month);
				}
				$DEBUG && outline("posting security deposit revers");
				post_security_deposit_reverse($rec["client_id"],$rec["housing_project_code"],
								$rec["housing_unit_code"],$residency_period->end);
				continue;
			}
		}
		// Test 4: (always true if executed) -- It's a move-in
		$DEBUG && outline("Test 4 is Yes (move-in)");
		unset($existing_rent_filter["period_start"]);
		$existing_rent_filter["BETWEEN:period_start"]=$month;
//outline("Here is the filter to look for existing charges: ");
//outline(dump_array($existing_rent_filter));
		$rc=get_charges($existing_rent_filter);
		// look for charge for this time period
		if (count($rc)>0)
		{
			continue;
		}
/*
As of 11/12/03, SHA is doing traditional prorating, and this code is not needed:
		// If moved in on 1st, of 31 day month,
		// take one day of residency, to avoid over-charge
		// with pro-rated move-in based on 30 days
		if ( ($month->days()==31) && ($month->start==$residency_period->start) ) 
		{
			$residency_period->start=next_day($residency_period->start);
		}
*/
		make_rent_charges($rec["housing_project_code"],$rec["housing_unit_code"],$rec["client_id"],$month->intersect($residency_period),$month);
		$security_depost_filter["effective_date"]=$residency_period->start;
		$sec=get_charges($security_deposit_filter);
		if (count($sec)>0)
		{
			continue;
		}
		if (! stristr($rec["move_in_type"],"transfer")) // no security dep. for unit transfers
		{
			post_security_deposit($rec["client_id"],$rec["housing_project_code"],
								$rec["housing_unit_code"],$residency_period->start);
		}
		continue;
	}
	// look for orpaned charges (created by late posting of last month move-out
	// must be rent or subsidy, not manually added,
	// not belonging to a resident, not already voided.
	// during current month
	$orphan_charges_filter["charge_type_code"]=array("RENT","SUBSIDY");
	$orphan_charges_filter["added_by"]=$GLOBALS["sys_user"];
	if (count($res_during_month)>0) // Don't Push Bad Filter (see bug 9495)
	{
		$orphan_charges_filter["!IN:client_id"]=$res_during_month;
	}
	$orphan_charges_filter["is_void"]=sql_false();
	$orphan_charges_filter["BETWEEN:period_start"]=$month;
	$orphan_charges_filter["BETWEEN:period_end"]=$month;
//outline("Here is the orphan filter: " . read_filter($orphan_charges_filter));
	if ($recs_filter<>array()) // DON'T do orphan charges for all if running for subset!!
	{
		#$orphan_charges_filter["client_id"]=$cid;
		$orphan_charges_filter=array_merge($recs_filter,$orphan_charges_filter);
	}		
	$orphan_charges=get_charges($orphan_charges_filter);
//outline("I got this many orphans: " . sql_num_rows($orphan_charges));
	// And void them
	for ($w=0;$w<count($orphan_charges);$w++)
	{
		$orphan=array_shift($orphan_charges);
//outline("I'm going to void this orphan charge " . dump_array($orphan));
		void_charge_old($orphan["charge_id"],"Client not resident during month");
	}

$DEBUG && 	outline(bigger(bold("Here is the final log: ")),2);
$DEBUG && 	outline($GLOBALS["landlord_log"]);
}

function balance_by_project($client_id) {

	$charge_def    = get_def('charge');
	$payment_def   = get_def('payment');
	$charge_table  = $charge_def['table'];
	$payment_table = $payment_def['table'];

	global $query_display;
	//fixme: talkin' about your huge sql...perhaps this should be a db function...
	//fixme2: this would be a piece of cake with the balance_project and balance_combined views
	$sql = "SELECT housing_project_code,
		subs_charge-subs_payment AS subsidy_balance,
		non_subs_charge-non_subs_payment AS client_balance
		FROM
		(SELECT DISTINCT

             COALESCE(
             (SELECT SUM(amount) FROM charge c0 WHERE client_id={$client_id} AND NOT is_void 
                    AND NOT is_subsidy
                    AND COALESCE(UPPER(c0.housing_project_code),'other')=COALESCE(UPPER(c.housing_project_code),'other')), 0) 
             AS non_subs_charge,
             COALESCE(
             (SELECT SUM(amount) FROM charge c0 WHERE client_id={$client_id} AND NOT is_void 
                    AND is_subsidy
                    AND COALESCE(UPPER(c0.housing_project_code),'other')=COALESCE(UPPER(c.housing_project_code),'other')), 0) 
             AS subs_charge,
             COALESCE(
             (SELECT SUM(amount) FROM payment p0 WHERE client_id={$client_id} AND NOT is_void 
                    AND NOT is_subsidy
                    AND COALESCE(UPPER(p0.housing_project_code),'other')=COALESCE(UPPER(c.housing_project_code),'other')), 0) 
             AS non_subs_payment,
             COALESCE(
             (SELECT SUM(amount) FROM payment p0 WHERE client_id={$client_id} AND NOT is_void 
                    AND is_subsidy
                    AND COALESCE(UPPER(p0.housing_project_code),'other')=COALESCE(UPPER(c.housing_project_code),'other')), 0)
             AS subs_payment,
		 COALESCE(c.housing_project_code,'Other') AS housing_project_code
		 FROM 
             (SELECT housing_project_code FROM charge WHERE client_id={$client_id}
                    UNION SELECT housing_project_code FROM payment WHERE client_id={$client_id}) c ) as a

		UNION
		
		SELECT
		'Total' AS housing_project_code, 
		(SELECT COALESCE(SUM(amount),0) FROM charge WHERE client_id={$client_id} AND NOT is_void AND is_subsidy)-
		(SELECT COALESCE(SUM(amount),0) FROM payment WHERE client_id={$client_id} AND NOT is_void AND is_subsidy) AS subsidy_balance,
		(SELECT COALESCE(SUM(amount),0) FROM charge WHERE client_id={$client_id} AND NOT is_void AND NOT is_subsidy)-
		(SELECT COALESCE(SUM(amount),0) FROM payment WHERE client_id={$client_id} AND NOT is_void AND NOT is_subsidy) AS client_balance";

	$res = agency_query($sql);

	$style = ' style="border-bottom: solid 1px red; margin: 0px;"';
	$tright = ' style="border-right: solid 1px red; border-bottom: solid 1px red;"';
	$right = ' style="border-right: solid 1px red;"';
	$hrow = row(rightcell(bold('Project'),$tright).centercell(bold('Client Balance'),$tright).centercell(bold('Subsidy Balance'),$style));
	$rows=null;
	$balances = array();
	while($a=sql_fetch_assoc($res)) {
		$proj = $a['housing_project_code'];
//		$program = orr(project_to_program($proj),'ALL_ACCESS'); //for has_perm
		$program = orr($a['housing_project_code'],'ALL_ACCESS'); //for has_perm
		$cbal = $a['client_balance'];
		$sbal = $a['subsidy_balance'];
		$balances[$proj] = array($program,$cbal,$sbal);  //quick and dirty numeric indexing. Careful below.
	}
	$totals = $balances['Total'];
	unset($balances['Total']);
	foreach ($balances as $proj => $bal) {
		$program = $bal[0];
		$cbal = $bal[1];
		$sbal = $bal[2];
		if (!in_array($cbal,array('0','0.00')) || !in_array($sbal,array('0','0.00'))) {
 			if (has_perm($program) || ($program=='ALL_ACCESS') 
				|| (strtoupper($program=='HOUSING')) && has_perm('RENT')) { // RENT permissions work to.  See bug 14190.
				$rows .= row(rightcell($proj,$right).centercell('$'.$cbal,$right).centercell('$'.$sbal));
			} else {
				$rows .= row(cell('You need '.ucfirst($program).' permissions for this balance','colspan="3"'));
			}
		}
	}
	if (is_null($rows)) {
		return '';
	}

	$rows .= row(rightcell('Total',$right).centercell('$'.$totals[1],$right).centercell('$'.$totals[2]));
	$rows = $hrow.$rows;
	$style = ' style="font-size: 80%;"';
	return table($rows
			 ,'',' bgcolor="" cellspacing="0" cellpadding="2"'.$style);
}

?>
