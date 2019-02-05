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

function link_housing($label='Housing')
{
	global $AG_MENU_LINKS;
	return hlink($AG_MENU_LINKS['Housing'],$label);
}

function show_project_pick($default='',$var='')
{
    global $project_select_sql;
    $var = orr($var,'agency_project_code');
    $output = selectto($var);
    $output .= do_pick_sql($project_select_sql, $default);
    if (empty($default))
    {
        $is_default='default';
    }
    $output .= selectitem('','none',$is_default);
    $output .= selectend();
    return $output;
}

function get_project_housing_pick($default='')
{
	return do_pick_sql('SELECT housing_project_code AS value, description AS label FROM l_housing_project', $default);
}

function show_project_housing_pick($default='',$var='')
{
    global $project_select_sql;
    $var = orr($var,'housing_project_code');
    $output = selectto($var);
    $output .= get_project_housing_pick($default);
    if (empty($default))
    {
        $is_default='default';
    }
    $output .= selectitem('','none',$is_default);
    $output .= selectend();
    return $output;
}

function show_unit_pick($default='')
{
    global $units_table;
    $sql = "SELECT housing_unit_code as value, housing_unit_code as label
            FROM $units_table ORDER BY housing_unit_code";
    $output = selectto('housing_unit_code');
    $output .= do_pick_sql($sql, $default);
    if (empty($default))
    {
        $is_default='default';
    }
    $output .= selectitem('','none',$is_default);        
    $output .= selectend();
    return $output;
}

function address_client($id,$date=null)
{
	return call_sql_function('address_client',$id,"'$date'");
}

function address_client_f($id,$date,$map=false)
{
	$date = orr(dateof($date,'SQL'),dateof('now','SQL'));
	if ($address = address_client($id,$date)) {
		if ($map) {
			$map_link = div(link_map($address),'','');
		}
		return div(webify($address).$map_link,'','');
	}
	return false;
}

function housing_status_f( $id )
{
// return a formatted string briefly describing
// most recent housing situation
// (currently limited to own org housing, could conceivably expand)
	global $engine;
	$def=$engine['residence_own'];
	$sql="SELECT continuous_housing_project_own($id) AS project_date, continuous_housing_own($id) AS own_date,
            a.* FROM {$def['table']} AS a WHERE client_id=$id ORDER BY residence_date DESC LIMIT 1";
	$res = agency_query($sql);
	if (sql_num_rows($res)==0) {
		return smaller(oline('(no '.org_name('short') .' Housing History)'));
	} else {
		$a=sql_fetch_assoc($res);
	}
	$project=value_generic($a['housing_project_code'],$def,'housing_project_code','list').smaller(' ('.link_unit_history($a['housing_unit_code'],false,false).')');
	$project_date=$a['project_date'];
	$own_date=$a['own_date'];
	if ($project_date !== $own_date) {
		$msg3 = smaller(oline('(Continuously in '.org_name('short').' housing since '.blue(dateof($own_date)).')'));
	}
	if ($a['residence_date_end']=='') {
		$msg0 = 'Currently';
		$msg = ' living at ';
		$msg2= ' since '.blue(dateof($project_date));
	} else {
		$msg0 = 'Moved';
		$msg = ' out of ';
		$msg2= ' on ' . blue(dateof($a['residence_date_end']));
	}

	$out = oline(alt(link_engine(array('object'=>'residence_own',
						     'action'=>'view',
						     'id'=>$a['residence_own_id']),$msg0),
			     'Click to view residence record').$msg . elink_value('l_housing_project',$a['housing_project_code']) . $msg2) . $msg3;

	//display address for scattered site
	if (in_array($a['housing_project_code'],array('SCATTERED','LEASED'))) {
		$view_link = link_engine(array('object'=>'housing_unit','id'=>$a['housing_unit_code']),smaller('view unit record',2));
		$hide = Java_Engine::hide_show_button('ClientSummaryAddress',true);
		if ( ($address = address_client_f($id,$a['residence_date_end'],true))
		    && (!preg_match('/(unknown)/i',$address,$m))) {
		} else {
			$address = ($m ? red($address).' ': red('NO ADDRESS FOUND! '))
				. link_engine(array('object'=>'housing_unit','action'=>'view','id'=>$a['housing_unit_code']),'Click here to remedy missing address');
		}
		$address = oline(smaller(bold('Address:'))) . $address;
		//get income record for subsidy type
		if ($a['housing_project_code']=='SCATTERED') {
			$inc_filt = client_filter($id);
			$inc_filt['housing_unit_code'] = $a['housing_unit_code'];
			$res = get_generic($inc_filt,'income_date DESC','1',$engine['income']);
			if (count($res)<1) {
				$address = oline('Error: no matching income record - can\'t determine Voucher Type') . $address;
			} else {
				$income = array_shift($res);
				$address = oline(smaller(bold('Voucher Type: ')).value_generic($income['fund_type_code'],$engine['income'],'fund_type_code','list')) . $address;
			}
		}
		$address = Java_Engine::hide_show_content(div($address . right($view_link),'',
									    'style="margin: 2px 2px 2px 35px; border: solid 1px black; padding: 3px;" class="clientCommentNote"')
									,'ClientSummaryAddress',true);
		$out = $hide.$out.$address;
	}
	return $out;
}
	
function get_subsidies( $filter )
{
	global $subsidy_select_sql;
	return agency_query( $subsidy_select_sql, $filter, 'startdate');
}

function get_residencies($filter,$order='')
{
	//	global $residency_select_sql;
	global $engine;
	return agency_query( $engine['residence_own']['sel_sql'], $filter,
			orr($order,'residence_date'));
}

function get_residencies_other($filter,$order='')
{
// residencies_other means housing with other organization
	global $residency_other_select_sql;
	return agency_query( $residency_other_select_sql, $filter,
			orr($order,'residence_date'));
}

function get_residencies_client($clientid,$filter='',$order='')
{
	$filter[AG_MAIN_OBJECT_DB . '_id']=$clientid;
	return get_residencies( $filter, $order);
}

function get_last_residence($clientid)
{
	// find most recent record for a resident (either current or last residence)
	// if unit no passed, get most recent record for that unit.
	if ($x=unit_no_of($clientid))
	{
		$filter['housing_unit_code']=$clientid;
	}
	else
	{
		$filter[AG_MAIN_OBJECT_DB . '_id']=$clientid;
	}
	return get_residencies($filter,'(COALESCE(residence_date_end,current_date)>=current_date) DESC,residence_date desc limit 1');
}

function current_residence_own($client_id)
{
	return call_sql_function('current_residence_own',$client_id);
}

function unit_no($client_id,$as_of='')
{
	return call_sql_function('unit_no',$client_id,$as_of ? enquote1($as_of) : 'current_date');
}

function last_residence_own($cid)
{
	if (!be_null($cid)) {

		return call_sql_function('last_residence_own',$cid);

	}
	return false;
}

function get_last_housing_history($client_id)
{
	global $engine;
	$filter = client_filter($client_id);
	$res = get_generic($filter,'residence_date DESC',1,$engine['housing_history']);
	if (count($res) > 0) {
		return array_shift($res);
	}
	return false;
}

function get_units( $filter=array() )
{

	/*
	 * Return a given set of units
	 */

	$def = get_def('housing_unit');

	return get_generic($filter,'housing_unit_code, housing_project_code','',$def);

}

function get_unit( $unit )
{

	/*
	 * get one specific unit
	 */

	$unit_rec = get_units(array('housing_unit_code' => $unit));

	$x = count($unit_rec);

	if ($x != 1) {

        log_error( oline("Warning from get_unit.  $x entries were found for unit $unit.  There should be exactly 1."));
        return false;
	}

	return $unit_rec;

}

function unit_no_of( $unit )
{
	/*
	 * Determine whether a string is a valid unit number,
	 * and return it, optionally in a format
	 */

	$unit_pr = '[A-Y]{1,3}[0-9]{1,4}';
	$leased_pr = 'Z[A-Z]{1}0[A-Z0-9]{3}';
	preg_match('/^('.$unit_pr.'|'.$leased_pr.')(x[0-9]{1})?$/i', $unit, $matches);

	return strtoupper($matches[1]) . $matches[2];
}

function unit_history($unit_no, $daterange_obj='',$sep='<br>')
{

	/*
	 * Make a copy of $daterange_obj, since objects are passed by
	 * reference, and we don't want to change the calling daterange.
	 *
	 * Fixme: there must be a simpler way of doing this, also, the "clone"
	 *        causes a parse error, even with the phpversion check for php
	 *        less that 5.
	 */

	if (is_object($daterange_obj)) {

		/*	if (phpversion() < '5') {

			$daterange = $daterange_obj;

		} else {
		*/

			//see bugs 24409, 25702 & 23517
			//$daterange = clone($daterange_obj);
			/*
			 * Using this clone function seems to cause a bunch of problems.
			 * Instead of using it, we will manually clone the object, 
			 * by creating a new object with the start and end times 
			 * of the old one. 

			 * Hopefully, this will now work for PHP 4, so for now I am commenting out the 
			 * special condition for PHP 4, and will test whether this causes problems 
			 * for gate2.
			 */
			
			$start = $daterange_obj->start;
			$end = $daterange_obj->end;
			$daterange = new date_range($start, $end);

			//}

	}

	/*
	 * return an array with Unit History information,
	 * optionally contained within a daterange
	 */

	if ($daterange && (! $daterange->end)) {

		$daterange->end = dateof('now','SQL');
	}

	$u = get_unit($unit_no);

	if (!$u) { return false; }

	$u = array_shift($u);

	$filter['housing_unit_code'] = $unit_no;
	
 	if ($end = $u['housing_unit_date_end']) {

 		if ($daterange) {

 			$daterange->set_end($end);

 		} else {

 			$daterange = new date_range($u['housing_unit_date'],$end);
			$set_min = true; //since the start dates of the units are not desired, this sets start to first move-in

 		}
 	}

	if ($daterange) {

		$filter['OVERLAPSORNULL:residence_date,residence_date_end'] = $daterange;

	}

	$vacant   = 0;
	$res_recs = get_residencies($filter);
	$recs     = sql_num_rows($res_recs);

	if ($recs == 0) {

		$msg = bigger(bold(red( "No Occupancy for Unit $unit_no!!")))
			. ($daterange ? oline(smaller(oline() . 'for the period ' 
				. $daterange->display())) 
				. $daterange->days() . ' vacant days' : '');

		return array('Formatted' => $msg,
				 'Summary'   => $msg,
				 'Occupied'  => 0,
				 'Vacant'    => ($daterange ? $daterange->days() : ''));

	}
					  
// 	for ($x=0;$x<sql_num_rows($res_recs);$x++)
	while ($r = sql_fetch_assoc($res_recs) ) {

		$mi = $r['residence_date'];
		$mo = $r['residence_date_end'];

		if (! $daterange ) {// if not specified, set it to be first move-in date

			$daterange = new date_range($mi,dateof('now','SQL'));

		} elseif ($daterange and $set_min and !$date_range_set) {

			$daterange->set_start($mi);
			$date_range_set = true;

		} elseif ($daterange && ($u['housing_unit_date'] > $daterange->start)) {
			
			/*
			 * If checking a daterange, and the unit starts after the daterange
			 * set the start to the unit start
			 */
			$daterange->set_start($u['housing_unit_date']);

		}

		if ( ! $last_end) { // first res.

			if ($mi > $daterange->start) { // Vacant at beginning if time period

				$t =  new date_range($daterange->start,prev_day($mi));

				$u[$q]['Range'] =  $t;
				$u['Formatted'] .= row(cell('Vacant (' . $t->days() . ' days)') . cell( $t->display()));

				$q++;
				$vacant=$vacant+$t->days();

			}

		} else {

			if ((next_day($last_end)>$mi) || ( ($last_end == '') && isset($last_end))) { // overlapping, bad data

				$warn = oline(bold(red( 'WARNING: Residencies Overlap')));

			}

			if (next_day($last_end) < $mi) { // vacancy

				$t = new date_range(next_day($last_end),prev_day($mi));

				$u[$q]['Range'] =  $t;
				$u['Formatted'] .= row(cell('Vacant (' . $t->days() . ' days)') . cell( $t->display()));

				$q++;
				$vacant=$vacant+$t->days();

			}

		}

		$uh_range = new date_range($mi,$mo);

		$u['List'][$q][AG_MAIN_OBJECT_DB . '_id'] = $r[AG_MAIN_OBJECT_DB . '_id'];

		$u['List'][$q]['Range'] = $uh_range;

		$vlink = alt(link_engine(array('object'=>'residence_own','id'=>$r['residence_own_id']),$uh_range->display()),'Click to view residence record');
		$u['Formatted'] .= row(  cell(smaller(client_link($r[AG_MAIN_OBJECT_DB . '_id']))) .cell($vlink) );

		// for occupancy, start with beginning of period
		// Adding last_end to max to adjust occupancy, see bug 7997

		$uh_range = new date_range(max($mi,$daterange->start,$last_end),min($daterange->end,orr($mo,dateof('now','SQL')))); 

		$occupied = $occupied + $uh_range->days();
		$last_end=$uh_range->end;

		$q++;

	}

	if (orr($last_end,today('SQL')) < orr($daterange->end,today('SQL'))) { // vacancy at end

		$t = new date_range(next_day($last_end),
					  $daterange->end);

		$u[$q]['Range']=$t;
		$u['Formatted'].=row(cell('Vacant (' . $t->days() . ' days)') . cell( $t->display()));
		$q++;
		$vacant=$vacant+$t->days();
	}
	$u['Formatted']=oline(bold("Unit History for Unit $unit_no"))
		. ($daterange ? smaller('for period '.$daterange->display().')'.oline()) : '')
		. tablestart('','border="1"') . $u['Formatted'];

	$u['Formatted'] .= tableend();
	$u['Formatted'] .= $warn;
	$u['Occupied']   = $occupied;
	$u['Vacant']     = $vacant;

	$u['Summary'] = bold("Unit Summary for $unit_no:")
				 . ($daterange ? smaller("$sep(For period from " . $daterange->display() . ') ') : '');

	$u['Summary'] .= "$sep$occupied occupied days, $vacant vacant days.${sep}Occupancy rate was " 
		. round($occupied/($daterange->days())*100,2) . '%';

	return $u;

}

function housing_project_from_unit($unit) {

	return call_sql_function('housing_project_from_unit',enquote1($unit));

}

function client_id_from_unit($unit) {

	return array_fetch_column(get_generic(array('housing_unit_code'=>$unit),NULL,NULL,'residence_own_current'),'client_id');

}

function client_housing_unit($cid) {

	return call_sql_function('last_residence_own_unit',$cid);

}

function link_unit_history($unit, $show_unit_link=true, $show_unit_subsidy_link=true)
{
	if (!$unit) { return; }
	static $unit_ids;
	if (!$unit_ids[$unit]) {
		$unit_ids[$unit] = sql_lookup_description($unit,'housing_unit','housing_unit_code','housing_unit_id');
	}
	$unit_id = $unit_ids[$unit];
	$def_r = get_def('residence_own');

	$fields = $def_r['list_fields'];
	array_push($fields,AG_MAIN_OBJECT_DB . '_id','moved_to_unit');

	$def_hu = get_def('housing_unit');
	$def_hus = get_def('housing_unit_subsidy');
	
	$control2 = array('object'=>'housing_unit',
				'action'=>'view',
				'id'=>$unit_id);

	$control3 = array('object'=>'housing_unit_subsidy',
				'action'=>'list',
				'list'=>array('filter'=>array('housing_unit_code'=>$unit)));

	$control = array(
			     'object'=>'residence_own',
			     'action'=>'list',
		 		 'title'=>'Unit history for ' .$unit,// . link_engine($control2,$unit),
			     'list'=>array(
						 'filter' => array('housing_unit_code'=>$unit),
						 'fields' => $fields
						 ));

	if ($show_unit_link && has_perm($def_hu['perm_view'])) {

		$unit_link = smaller(' '.alt(link_engine($control2,red('+')),'View Unit Record'),2);

	}


	if ($show_unit_subsidy_link && has_perm($def_hus['perm_view'])) {

		$unit_subsidy_link = smaller(' '.alt(link_engine($control3,red('subsidy')),'View Unit Subsidy Record'),2);

	}

	return alt(link_engine($control,$unit),'Unit History'). $unit_link . $unit_subsidy_link;

}

function link_housing_unit_subsidy($unit)
{

	if (!$unit) { return; }

	$control = array('object'=>'housing_unit_subsidy',
			     'action'=>'list',
			     'list'=>array(
						 'filter'=>array('housing_unit_code'=>$unit))
			     );

	return smaller(link_engine($control,'subsidy'),2);

}

function housing_view_current_form()
{

  foreach ( array('residence_own'=>'residents','bar'=>'bars') as $obj=>$lab ) {

		$types .= selectitem($obj,$lab,$_REQUEST['search_on']==$obj);

	}

	$select = selectto('search_on')
		. $types
		. selectend();

	$out = formto()
		.'List current '.$select.' for ' 
		. selectto('search_project')
		. selectitem('ALL','All Housing Projects')
		. get_project_housing_pick($_REQUEST['search_project'])
		. selectend()
		. button()
		. formend();

	return $out;

}

function housing_view_meals_form()
{

  /*   	foreach ( array('meal_count'=>'meal counts') as $obj=>$lab ) {

		$types .= selectitem($obj,$lab,$_REQUEST['search_on']==$obj);
  
  	}
  
	$select = selectto('search_on')
	.  selectitem('meal_count','meal counts text',$_REQUEST['search_on']=='meal_count')
	  . selectend();
  
	$_REQUEST['search_on'] = 'meal_count';*/

	$out = formto()
	        .'List current meal count for ' 
		. hiddenvar('search_on', 'meal_count') 	  
		. selectto('search_project')
		. selectitem('ALL','All Housing Projects')
		. get_project_housing_pick($_REQUEST['search_project'])
		. selectend()
	        . button()
		. formend();

	return $out;

}

function process_housing_current_form()
{

	//this is very clunky
	$object  = $_REQUEST['search_on'];
	$project = $_REQUEST['search_project'];

	switch ($object) {
	case 'residence_own':

		if ($project !== 'ALL') {

			$create_filt = 'create_filter_housing_project_code_value='.$project;
			$filter['housing_project_code'] = 'equal';

		}

		$filter['residence_date_end'] = 'null';

		foreach ($filter as $key=>$val) {

			$create_filt .= '&create_filter_'.$key.'_key='.$val;

		}

		$url = 'action=add&query_object='.$object.'&'.$create_filt;
		header('Location: object_query.php?'.$url);

		exit;

	case 'meal_count':

	        if ($project == 'ALL') {

		  $filter['housing_project_code'] != 'null';

		}

	        else $filter = array('housing_project_code'=>$project);

		$control = array(
				     'object'=>'meal_count',
				     'action'=>'list',
				     'list'=>array('filter'=>$filter));

		global $engine;

		$out = call_engine($control,'',$hide_title=false,$no_messages=false,$tot,$perm);

		agency_top_header();

		out(oline(link_engine(array('object'=>'meal_count','action'=>'add'),'Add a Housing Project Meal Count')));
		out($out);

		exit;

	case 'bar':

		$filter = array('FIELD<=:bar_date'=>'CURRENT_DATE',
				    array('FIELD>=:bar_date_end'=>'CURRENT_DATE',
					    'NULL:bar_date_end'=>true)
				    );

		if ($project == 'ALL') {

			foreach (array() as $proj) {
				// FIXME: 
				$barred_from['barred_from_'.$proj] = sql_true();

			}

			$filter[] = $barred_from;

		} elseif ($project == 'SCATTERED') {

			break;

		} else {

			$proj_field = 'barred_from_'. strtolower($project);
			$filter[$proj_field] = sql_true();

		}

		$control = array(
				     'object'=>'bar',
				     'action'=>'list',
				     'list'=>array('filter'=>$filter));

		//fixme: this won't work with the planned functioning of get_def()
		global $engine;
		$engine['bar']['fields']['photo'] = $engine['client']['fields']['custom5'];

		array_unshift($engine['bar']['list_fields'],'photo',AG_MAIN_OBJECT_DB . '_id','non_client_name_full');
		$out = call_engine($control,'',$hide_title=true,$no_messages=false,$tot,$perm);

		agency_top_header();
		out(housing_view_current_form());
		out(smaller(oline(hlink('reports/user_options.php?filename=general/bars.cfg','Bar Report'),2)));
		out($out);

		exit;
	}
	
}

function housing_first_month_rent_calculator()
{

	// returns a pro-rated first month's rent

	if (isset($_POST['hfmrc_movein']) && isset($_POST['hfmrc_rent'])) {

		$movein = dateof($_POST['hfmrc_movein'],'SQL');
		$rent   = $_POST['hfmrc_rent'];

		if ($movein) {
			$first_month = new date_range($movein,end_of_month($movein));
			$days = $first_month->days();
			$days_month = days_in_month( $movein );

			
			/* This was for the old SHA pro-rating based on 30 days:
			if ($movein==start_of_month($movein))
			{
				$first_rent = $rent;
			}
			else
			{
				$first_rent = round($rent * min($days,30) / 30);
			}
			*/

			// This is the new, sane way to pro-rate rent:
			$first_rent = round($rent / $days_month * $days);
			$out = bigger(bold(oline("At a rent of \$$rent per month,")
						 . oline("For a period of $days days, " . $first_month->display())
						 . oline("The first month rent is \$$first_rent",2)));
		}
	}

	global $AG_HEAD_TAG;
	$rent_calc_js = <<<EOF
	function doRentCalc() {
		var movein = document.rentCalc.hfmrc_movein.value;
		var rent   = document.rentCalc.hfmrc_rent.value;
		var dOb    = new Date(movein);

		if (!dOb.getDate()) {
			alert('Invalid date for move in: '+movein);
			return false;
		}

		if (isNaN(parseInt(rent))) {
			alert('Invalid rent amount: '+rent);
			return false;
		}

		var days = getDaysInMonth(dOb.getMonth(),dOb.getFullYear());

		var eOb = new Date();
		eOb.setDate(days);
		eOb.setMonth(dOb.getMonth());
		eOb.setFullYear(dOb.getFullYear());
		var daysLeft = days - dOb.getDate() + 1;
		var proRent = Math.round(rent/days*daysLeft);
		var message = 'At a rent of $'+rent+' per month,<br />For a period of '+daysLeft
                + ' days, '+(1+dOb.getMonth())+'/'+dOb.getDate()+'/'+dOb.getFullYear()+'-->'
                +(1+eOb.getMonth())+'/'+eOb.getDate()+'/'+eOb.getFullYear()+'<br />'
                +'The first month rent is $'+proRent+'<br />';
 		document.getElementById('rentReturn').innerHTML = message;

   		return false;
	}

EOF;

	$AG_HEAD_TAG .= Java_Engine::get_js($rent_calc_js);

	$out .= div('','rentReturn',' style="color: red; font-size: 120%; "')
		. formto($_SERVER['PHP_SELF'].'#first_month_rent_calculator','rentCalc',' onsubmit="return doRentCalc();"')
		. oline('Enter Move-in Date-->' . formdate('hfmrc_movein',dateof('now')))
		. oline('Enter Montly Rent-->' . formvartext('hfmrc_rent'))
		. button('Calculate Now') . formend();

	return anchor('first_month_rent_calculator').$out;

}

function agency_menu_housing()
{
	/*
	 * Housing Miscellany ( formerly housing_menu.php )
	 */
	
	process_housing_current_form(); // this may exit the script and re-direct to object_query.php

	$menu['Current Residents'] = housing_view_current_form();
	$menu['Unit History'] = bigger(hlink('unit_history.php','View Unit Histories'));
	$menu['First Month Rent Calculator'] = housing_first_month_rent_calculator();
	$menu['Bars'] = link_engine(array('object'=>'bar','action'=>'add'),'Add a non-client bar');
	//All new stuff to line break - sd
	$menu['Project Meal Counts'] = housing_view_meals_form();

	$h_def = get_def('housing_unit');
	if (has_perm($h_def['perm_list'])) {
		$tmp = array('housing_unit','housing_unit_subsidy');
		$out_all = $out_cur = '';
		$dummy = true;
		
		// output links in case of empty tables
		$s_button = Java_Engine::toggle_tag_display('div',bold('show empty records'),'childListNullData','block',$dummy);
		$h_button = Java_Engine::toggle_tag_display('div',bold('hide empty records'),'childListNullData','block',$dummy);
		$cur_out .= oline(smaller(Java_Engine::hide_show_buttons($s_button,$h_button,$dummy,'inline',AG_LIST_EMPTY_HIDE)));
		
		foreach ($tmp as $object) {
			$udef = get_def($object);
			$sing = $udef['singular'];
			$control = $control_all = array('object'=>$object, 'action'=>'list');
			$control['list']['filter'] = array('NULL:'.$object.'_date_end'=>true);
			$title_all = 'Browse ALL '.$sing.' records';
			$title_cur = 'Browse CURRENT '.$sing.' records';
			$out_cur   .= engine_java_wrapper($control,$object.'cur',$dummy,$title_cur,'Cur');
			$out_all   .= engine_java_wrapper($control_all,$object.'all',$dummy,$title_all,'All');
		}
		
		$menu['Housing Unit Maintenance'] = oline($out_cur) . $out_all;
	
	}

	return array($menu,$errors);
}

function agency_menu_shelter()
{
	$control = array('object'=>'shelter_count','action'=>'list','list'=>array('display_add_link'=>true));

	$menu['Daily Counts'] = call_engine($control,'',true,true,$dummy,$dummy);
	
	return array($menu,$errors);
}

function can_occupy_residence_own($cid,$unit,$ondate) {
/*
   Check how many people (other than client)
   are living in a unit, and compare with
   max allowed.
*/
	$max = call_sql_function('allowed_occupant',"'$unit'","'$ondate'");
	$filter = array("housing_unit_code"=>$unit,
		"<=:residence_date"=>$ondate,
		">=:COALESCE(residence_date_end,current_date)"=>$ondate,
		"<>:client_id"=>$cid);
	$found = count_rows("residence_own",$filter);
	return $found < $max;
}


?>
