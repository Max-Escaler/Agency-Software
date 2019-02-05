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

function staff_filter( $id )
{
	return array('staff_id'=>$id);
}

function staff_client_assignments($staff_id)
{

	$sql='SELECT staff_assign_id,'.AG_MAIN_OBJECT_DB.'_id,description,staff_assign_type_code FROM staff_assign_current LEFT JOIN l_staff_assign_type USING (staff_assign_type_code)';
	$filter = staff_filter($staff_id);

	$res = agency_query($sql,$filter);
	return $res;

}

function staff_client_assignments_ids( $staff_id ) {
	$filter=staff_filter($staff_id);
	return sql_fetch_column(agency_query('SELECT client_id FROM staff_assign_current',$filter),'client_id');
}
	
function staff_client_assigned($cid,$exclude_just_monitoring=true)
{
	global $UID;

	static $clients;
	if (!$clients) {
		$filter = staff_filter($UID);
		if ($exclude_just_monitoring) {
			$filter['!IN:staff_assign_type_code']=array('MONITOR');
		}
		$res = get_generic($filter,'','','staff_assign_current');
		$clients = array_fetch_column($res,AG_MAIN_OBJECT_DB.'_id');
	}

	return in_array($cid,$clients);
}

function staff_client_position_project_clinical($cid)
{
	//check to determine if client is residing in same project as user
	//and look at the position => project array to determine access

	//query results are cached since this function gets called once per record

	global $UID;

	static $clients = array(),$staff_project,$staff_position;
	
	if (!$client_project = $clients[$cid]) {
		$clients[$cid] = $client_project = current_residence_own($cid);
	}

	if (!$staff_project) {
		$staff_project  = staff_project($UID);
	}

	if (!$staff_position) {
		$staff_position = staff_position($UID);
	}

	if (!$staff_position or !$client_project or !$staff_project) {
		//avoid access to clients not living in our projects
		return false;
	}
	

	global $AG_STAFF_CLIENT_CLINICAL_POSITIONS_PROJECTS;

	if (!$projects){
	  if(array_key_exists($staff_position, $AG_STAFF_CLIENT_CLINICAL_POSITIONS_PROJECTS)) {
	    $projects = $AG_STAFF_CLIENT_CLINICAL_POSITIONS_PROJECTS[$staff_position];
	  }
	  else{
	    return false;
	  }
	}

	return (($client_project === $staff_project) && (($projects == "") || (in_array($staff_project, $projects))));
	

}


function staff_client_project($cid)
{
	//check to determine if client is residing in same project as user
	
	global $UID;

	static $clients = array(),$staff_project,$staff_position;

	if (!$client_project = $clients[$cid]) {
		$clients[$cid] = $client_project = current_residence_own($cid);
	}

	if (!$staff_project) {
		$staff_project  = staff_project($UID);
	}

	if (!$staff_project or !$client_project) {
		
		return false;

	}

	return $client_project===$staff_project;
}

function view_staff( $staff,$def='',$action='',$control='',$control_array_variable='control' )
{
	if ($action === 'add') {
		return view_generic($staff,$def,$action,$control,$control_array_variable);
	}

      global $colors,$UID,$sys_user;
	$def = orr($def,get_def('staff'));
	$id=$staff[$def['id_field']];	
	if (sql_true($staff['is_active']) and be_null(
			$staff['staff_title']
			. $staff['supervised_by']
			. $staff['agency_project_code']
			. $staff['agency_position_code'])) {
			//Likely no staff_employment record, confirm
			$staff_emp=get_generic(staff_filter($id),NULL,NULL,get_def('staff_employment'));
			if (count($staff_emp)==0) {
				$no_staff_emp=true;
				if ($id==$sys_user) {
					$is_sys_user=true;
				} else {
					$is_sys_user=false;
				}
			}
	}
	if ($no_staff_emp and !$is_sys_user) {
		$no_staff_emp_msg = div('This user is marked active, but does not have a staff employment record.  You can '
							. add_link('staff_employment','add one now',NULL,staff_filter($id)).'.','noStaffEmployment');
	} elseif (!$is_sys_user) {
		/* engine links to list others */
		$project = link_engine_list_filter('staff',array('agency_project_code'=>$staff['agency_project_code']),
			     value_generic($staff['agency_project_code'],$def,'agency_project_code','list'),'class="fancyLink"');
		$program = link_engine_list_filter('staff',array('agency_program_code'=>$staff['agency_program_code']),
			     value_generic($staff['agency_program_code'],$def,'agency_program_code','list'),'class="fancyLink"');
		$location = link_engine_list_filter('staff',array('agency_facility_code'=>$staff['agency_facility_code']),
				 value_generic($staff['agency_facility_code'],$def,'agency_facility_code','list'),'class="fancyLink"');
	} else {
		$no_staff_emp_msg = div('The system user is used for automatic things, and should never be used for regular purposes.  You should never log in with this account.','noStaffEmployment');
	}
	$email = $staff['staff_email'];
	$position = value_generic($staff['staff_title'],$def,'staff_title','list');
	$supervisor=staff_link($staff['supervised_by']);
 	if ($staff['supervised_by']) {
		$supervisor .= smaller(' ('.value_generic(staff_position($staff['supervised_by']),$def,'staff_position_code','list').')',2);
 	}
	//get supervisees
	$res = get_generic(array('supervised_by'=>$id,'is_active'=>sql_true()),'name_last','',$def);
	if (count($res) > 0) {
		$supervisees = array();
		while ($a = array_shift($res)) {
			$supervisees[] = staff_link($a['staff_id'],staff_name($a['staff_id']))
				. smaller(' ('.value_generic(staff_position($a['staff_id']),$def,'staff_position_code','list').')',2);
		}
		$supervisees = anchor('supervisees') . implode(oline(),$supervisees);

		/*
		 * Supervisee transfer
		 */
		if (has_perm($def['perm_edit'],'RW')) {

			$supervisees .= oline('',2).staff_supervisor_transfer_form($id);

		}

	}

	if (sql_false($staff['is_active'])) {
		$inactive = row(cell(bigger(red(italic(bold('(This staff record is marked inactive ' 
								     . ($staff['terminated_on'] 
									  ? 'with an ending date of ' . dateof($staff['terminated_on']) 
									  : ' with no ending date on file') . '.)')))),' colspan="3"'));
	} 

	//photo for page of individual staff
	$photo = oline(staff_photo( $id))
		. smaller(hlink_if( AG_STAFF_PAGE . '?action=print_staff_id&id='.$id,'Print ID Card',is_id_station()),2);

	$summary = html_heading_2($staff['name_first'] . ' ' . $staff['name_last']
					  . span(", ID # " . $id,' style="font-weight: normal; font-size: 70%;"')) 
		. ($no_staff_emp ? $no_staff_emp_msg : html_heading_4(italic($position)))
		. staff_phone_f($id);


	/* staff assignments */
	$hide_button = oline(right(Java_Engine::hide_show_button('StaffAssign',false) . smaller('Staff Assignments',3)));
	$staff_assigns = div($hide_button
				   . Java_Engine::hide_show_content(assignments_f($id),'StaffAssign',false)
				   ,'',' style="float:right;"');
	
	$remote_access = staff_remote_access_status_f($id);

	// photo must span additional rows if certain conditions are met
	$photo_row_span = 11;
	if ($remote_access) { $photo_row_span ++; }
	if ($supervisees) { $photo_row_span ++; }

	$out = $staff_assigns
		. table( 
			  $inactive
			  // Photo
			  . row(cell($photo,'rowspan="' . $photo_row_span . '" valign="top" width="120"'))
			  . row(cell($summary,' colspan="2"'))
			  // Basic Info
			  . row( rightcell('Email:') . leftcell( $email ? hlink('mailto:%20'.$email,$email) : '' ))
			  .	 rowrlcell('AGENCY Username:',value_generic($staff['username'],$def,'username','list'))
			  . ($no_staff_emp ? '' :
			  	rowrlcell('Project:',$project)
			  	.	 rowrlcell('Program:',$program)
			  	.	 rowrlcell('Location:',$location)
			  	.	 rowrlcell('Supervised By:',$supervisor)
			  )
			  .    ($supervisees ? rowrlcell('Supervises:',$supervisees) : '')
			  .    (($tmp_sl = staff_language_f($staff['staff_id'])) ? rowrlcell('Language(s):',$tmp_sl) : '')
			  // Password and options
			  .	 rowrlcell('Password:',password_expires_on_f($id) . oline() . link_password_change($id))
			  // Remote access password strength
			  . ($remote_access 
			     ? rowrlcell('Remote Access:', $remote_access)
			     : '')
			  .	 rowrlcell('User Options:',link_engine(array('object'=>'user_option','id'=>$id),'Change/View'))
			  // End Summary
			  ,'',' border="3" cellspacing="2"'). oline();
	
	$out .= div(link_engine(array('object'=>'log','action'=>'list','list'=>array('filter'=>array('written_by'=>$id))),'Show all logs written by this person','',''),'','') . oline();
	
	$out .= list_all_child_records('staff',$id,$def,false);

	//staff permission check (bug 28545):
	if (has_perm('admin')) {
		$spc = staff_permission_check( $id );
		$hide_button = Java_Engine::hide_show_button('staffShowPermissionCheck',$hide = true);
		$title = section_title('Permission Check');
		$out  .= span($hide_button.'&nbsp;'.$title,' class="childListTitle"')
			. Java_Engine::hide_show_content(div($spc,'',' class="ChildListData"'),'staffShowPermissionCheck',$hide = true);
		
	}

	// outputs a form to find all records of a given type
	if ( ($staff['staff_id']==$UID) or has_perm('super')) {
		$out .= staff_record_association_form($id);
	}
	return $out;
}

//called for both search & individual pages
function staff_photo( $idnum, $scale=1, $all_in_array = false )
{
	$photo = hlink(  staff_photo_url($idnum,4), httpimage(staff_photo_url($idnum,$scale),120*$scale,160*$scale,0));
	return $all_in_array 
		? array($photo)
		: $photo;
}

function staff_photo_url( $idnum, $scale=1, $proto="https" )
{
	global $AG_HOME_BY_FILE, $AG_HOME_BY_URL,$AG_STAFF_PHOTO_BY_URL, $AG_STAFF_PHOTO_BY_FILE, $AG_IMAGES;
	$file = $AG_STAFF_PHOTO_BY_FILE.'/';
		switch ($proto)
		{
				case "file" :
						$base = $file;
						break;
				case "https" :
				case "http" :
					$base = $AG_STAFF_PHOTO_BY_URL.'/';
					break;
				default :
						log_error("Unknown protocol ($proto) requested from staff_photo_url");
						return false;
		}
        $rest = "st_" . $idnum;
        $rest2 = ".jpg";
 //         if ($scale<=1 && @fopen( $file . $rest . ".120X160" . $rest2 , r ))
	if ($scale<=1 && is_readable( $file . $rest . ".120x160" . $rest2 )) {
       	  //changed to lower case x;    
	  return $link= $base . $rest . ".120x160" . $rest2;
	} else {
// 		  if (@fopen( $file . $rest . $rest2 , r )) {
	          if (is_readable( $file . $rest . $rest2 )) {
			  $link=$base . $rest . $rest2;
		  } else {
			  $link=$AG_IMAGES['NO_PHOTO'];
		  }
	}
	return $link;
}

function link_staff_other($name)
{
	$label=black($name);
	$control=array('object'=>'staff_assign',
			   'action'=>'list',
			   'list'=>array('filter'=>array('staff_id_name'=>$name),
					     'fields'=>array('staff_id_name','contact_information',AG_MAIN_OBJECT_DB.'_id','staff_assign_type_code'))
			   );
	return link_engine($control,$label).smaller(' (non-'.$GLOBALS['AG_TEXT']['ORGANIZATION_SHORT'].')',2);;
}

function staff_links( $idnum, $sep=NULL ) {
	$sep=orr($sep,oline());
	foreach ($idnum as $id) {
		$out[]=staff_link($id);
	}
	return implode($sep,$out);
}

function staff_link( $idnum, $name="lookup" )
{
	/*
	 * Doesn't Validate or Match id with name
	 * right now staff don't link, so it just returns name.
	 * added global staff_links array, so lookup queries only done once per page per staff.
	 */


	global $off;

	static $staff_links = array();

	if (!$idnum) {

		return 'NULL Staff ID';

	} elseif (is_array($idnum)) {

		$res=array();

		foreach( $idnum as $id ) {

			array_push($res,staff_link($id));

		}

		return $res;

	} elseif (!is_numeric($idnum)) {

		return link_staff_other($idnum);

	}

	if ($name == 'lookup') {

		// staff lookups done previously stored in array, to avoid repetitive lookups
		if (isset($staff_links[$idnum])) {

			return $staff_links[$idnum];

		}

 		$q = 'SELECT name_first,name_last FROM staff WHERE staff_id = '.$idnum;

 		$r = agency_query( $q );
		if ($r and sql_num_rows($r) > 0) {

			$q = sql_fetch_assoc( $r );

			$first = $q['name_first'];
			$last  = $q['name_last'];

			if ( is_numeric($first) ) { // indicates a group entry--gets blue

				$result              = blue( substr($last,6) );
				$staff_links[$idnum] = $result;

				return $result;

			} else {  // regular staff entry gets red

				$name= $first . ' ' . $last;

			}

		} else {

		      $name="Staff ID # $idnum (not found)";	
		      return dead_link($name);

		}

	}

	$result=hlink($off . AG_STAFF_PAGE . '?id=' . $idnum,$name,'',' class="staffLink"');	
	$staff_links[$idnum]=$result;

	return $result;	
}

function get_staff( $filter, $order="name_last,name_first" )
{
// FIXME: This function is no longer called.
//		   But it does check/correct for duplicated staff, at least somewhat
//			which the generic function doesn't.
//		  One really shouldn't be duplicating staff anyway, but I suppose stuff happens...

	// default is to only get active staff
	// but if filter is set ("is_active"="{sql_true()} OR {sql_false}")
	// Then this function won't mess with the filter.
	$def=get_def($staff);
	if (!isset($filter["is_active"]))
	{
		$filter["is_active"]=sql_true();
	}
	$a = get_generic($filter,$order,'',$def);
	// FIXME:  This doesn't really quite cover the bases for unduplicated staff
	if ((count($a)==0) && ($idnum=$filter[$def['id_field']]))
	{
		$b=agency_query("SELECT * FROM duplication_staff",array($def['id_field'].'_old'=>$idnum));
		if ( ($b) && (sql_num_rows($b)==1))
		{
			$b=sql_fetch_assoc($b);
			// Recurses itself--could be a problem if two staff pointed back at each other
			return get_staff( array($def['id_field']=$b[$def['id_field']]));
		}
	}
	return $rec;
}

function staff_name( $sid )
{
	$def=get_def('staff');
	if (!$sid) {
		return false;
	} elseif (!is_numeric($sid)) { //other organzation's staff
		return $sid;
	}
	$staff=get_generic(array($def['id_field']=>$sid,"is_active"=>array(sql_true(),sql_false())),'','',$def);
	if (count($staff)<>1) {
		return false;
	}
	$staff=array_shift($staff);
	return $staff['name_first'] . ' ' . $staff['name_last'];
}

function is_staff( $id )
{
	$def=get_def('staff');
    if (is_valid($id,'integer_db') and (count(get_generic(array($def['id_field']=>$id),'','',$def)) == 1)) {
		return true;
	}
	return false;
}

function is_self( $idnum )
{
      //Determine whether the user is the same person as $idnum.
      global $UID;
      return ($idnum==$UID);
}

function get_staff_f( $staff, $separator="<br>" )
{
// take an array of staff IDS, and return a formatted string
	$result=array();
	$staff=orr($staff,array());
	foreach ($staff as $x)
    {
    	$link = staff_link( $x );
        if ($x==$GLOBALS["UID"])
        {
			$link = bigger(bold("<<$link>>"));
        }
		array_push($result,$link);
    }
    return implode($result,$separator);
}

function pick_staff_to( $varname, $active_only="Yes", $default=-1 ,$subset=false,$options='',$inc_sys_user=false)
{
	$def=get_def('staff');
	$staff_table_id=$def['id_field'];
	$table = orr($subset,$def['table']);
	$query = "SELECT 
		CASE WHEN name_first < 'A' THEN name_last
		ELSE name_last || ', ' || name_first
		END as label, $staff_table_id as value FROM $table ";
	$order = " ORDER BY is_active DESC, (name_last ILIKE'ATTN:%') DESC,name_last, name_first ";
	$query_active = $query . ' WHERE is_active' . ($inc_sys_user ? '' : ' AND staff_id<>sys_user()');

	if ($active_only=='HUMAN') {
		$query_active .= " AND NOT name_first < 'A'" ;
	}

	$query_active .= $order;

	$active_items = do_pick_sql( $query_active, $default );

	if ($active_only) {
		$inactive_items = '';
	} else {
		$active_items = html_optgroup($active_items,'Current staff:');
		$query_inactive = $query . ' WHERE NOT is_active' . $order;
		$inactive_items = html_optgroup(do_pick_sql( $query_inactive, $default,'','','',' class="inactive"' ),'Inactive staff:');
	}

	return span( 
	selectto($varname,$options)
	. selectitem("-1","(choose from list)")
		. $active_items
		. $inactive_items
	. selectend()
	. span($GLOBALS['UID'],'class="serverData"')
	,'class="pickStaffList"')
	;
}

function staff_selector($show_selected="Y",$form="N")
{
    // Display selected staff and provide list to add more
    global $STAFF, $staff_select,$staff_remove,$colors, $STAFF;

    $staff_add = orr($staff_add,$_REQUEST['staff_add']);
    $staff_select = orr($staff_select,$_REQUEST['staff_select']);
    $staff_remove = orr($staff_remove,$_REQUEST['staff_remove']);
// if id is negative, ignore.  This allows dummy value from first
// selection on staff pick list.

    if (isset($staff_select) && ($staff_add >= 0 ) ) {
        add_id( $staff_add, $STAFF ); 
	  $_SESSION['LOG_STAFF'] = $STAFF;
    }
    if (isset($staff_remove) && ($staff_remove >= 0 ) ) {
        remove_id( $staff_remove, $STAFF ); 
	  $_SESSION['LOG_STAFF'] = $STAFF;
    }
    return //bottomcell(
	    ( ($show_selected=="Y") ?
	      oline(bigger(bold("Staff Members to Alert:")))
              . show_selected_staff($STAFF,"remove ok")
	    : "")
			. ($form=="Y" ? formto($_SERVER['PHP_SELF']) : "")
            . pick_staff_to("staff_add")
            . oline(button("Add Staff","SUBMIT","staff_select","Add Staff"),1)
	    . (($form=="Y") ? formend() : "");
    //, "align=\"center\" bgcolor=\"${colors['staff']}\"");
}

function show_selected_staff( $staff, $removeok="" )
{

    $count=count($staff);
    $otmp= ($count==0) ? "No" : "The Following";
    $output =oline("$otmp Staff are Selected:");
    foreach ($staff as $x)
    {
        $output .= oline(
               ($removeok ?  "("
               . hlink($_SERVER['PHP_SELF'] . "?staff_remove="
               . $x, smaller("Remove",2)) .")  " : "")
               . staff_link( $x ) );
    }
    return $output;
}

function post_staff_a($logid, $posterid)
{
    global $STAFF;
    if (count($STAFF)== 0)
    {
        return true;
    }
    foreach ($STAFF AS $x)
    {
		$result = post_staff1( $logid, $posterid, $x );
        if ( ! $result )
        {
                sql_warn("alert insert query failed: $query<BR>");
                return $result;
        }
    }
    return $result;
}

function post_staff1($logid, $posterid, $staffid)
{
	$filter = array('ref_id'    => $logid,
			    'ref_table' => 'LOG',
			    'staff_id'  => $staffid);

	$alerts = get_alerts($filter);

	if (count( $alerts ) > 0 ) {

		outline( alert_mark() .
			   "Alert for staff " . staff_link( $staffid )
			   . " for log # $logid already exists.");
		return false;

	} else {

		$def = get_def('alert');
		$filter["added_by"]   = $posterid;
		$filter["changed_by"] = $posterid;
		$query = agency_query(sql_insert($def['table_post'],$filter));
		return $query;

	}
}

function staff_query($label = "",$format="all")
{
	$label=orr($label,"Staff Search");
	if ($format == "all")
	{
		return tablestart('','class=""') . 
			row( 
			    cell ( formto("staff_search.php")
				     . formvartext("name_fullText")
				     . hiddenvar("name_fullType","sub")
				     . button($label)
				     . formend())
			    .
			    cell(
				   formto("staff_display.php")
				   . " or " 
				   . pick_staff_to( "id" )
				   . button("Show this staff member")
				   . formend()))
			. tableend();
	}
	if ($format=="sidebar")
	{
		return tablestart("",'class="staff"') . 
			row( 
			    cell ( formto('search.php',"staffside","target='_content'")
				     . formvartext('QuickSearch',$_REQUEST['QuickSearch'],"size=18")
				     . hiddenvar('QSType','staff')
				     . button($label)
				     . formend()))
			. tableend();
		
	}
}

function project_to_program($project) {
	//given a project, returns associated program
	$project = strtoupper($project);
	$sql = 'SELECT agency_program_code FROM l_agency_project';
	$filter = array('agency_project_code'=>$project);
	$a = sql_fetch_column(agency_query($sql,$filter),'agency_program_code');
	return $a[0];
}

function is_human_staff($sid)
{
	if (!is_valid($sid,'integer_db')) {
		return false;
	}
	return sql_true(call_sql_function('is_human_staff',$sid)) ? true : false;
}

function staff_summary($rec)
{
	// FIXME: much of this is duplicated in view_staff (tracker #3196596)
	$def = get_def('staff');
	return table(rowrlcell('position: ',bold(value_generic($rec['staff_position_code'],$def,'staff_position_code','list')))
			 .rowrlcell('project: ',bold(value_generic($rec['agency_project_code'],$def,'agency_project_code','list')))
			 .rowrlcell('Supervised By: ',value_generic($rec['supervised_by'],$def,'supervised_by','list'))
			 .rowrlcell('facility: ',bold(value_generic($rec['agency_facility_code'],$def,'agency_facility_code','list')))
			 .rowrlcell('email: ',$rec['staff_email'] ? hlink('mailto:%20'.$rec['staff_email'],$rec['staff_email']) : '')
			 ,'','style="font-size: 85%;" class="" cellpadding="0" cellspacing="0"');
}

function staff_phone_f($id)
{
	if (!$def = get_def('staff_phone')) {
		return '';
	}

	$filter = array('staff_id'=>$id);
	$filter['FIELD<=:staff_phone_date']='CURRENT_DATE';
	$filter[] =array('FIELD>=:staff_phone_date_end'=>'CURRENT_DATE',
			     'NULL:staff_phone_date_end'=>true);
	$res = get_generic($filter,'','',$def);
	if (count($res)<1) {
		$phone = 'no phone numbers';
	} else {
		$indent = ' style="margin-left: 25px;"';
		while ($a = array_shift($res)) {
			$type = value_generic($a['phone_type_code'],$def,'phone_type_code','list');
			$link_rec = link_engine(array('object'=>'staff_phone','id'=>$a['staff_phone_id']),$type,'',' class="fancyLink"');
			$tmp = $link_rec.': '
				.value_generic($a['number'],$def,'number','list')
				. ($a['extension'] ? ' (x'.value_generic($a['extension'],$def,'extension','list').')' : '');

			if ($a['direct_dial_number']) {
				$tmp .= div('DD: '.value_generic($a['direct_dial_number'],$def,'direct_dial_number','list'),
						'',$indent);
			}

			if ($a['voice_mail_number'] or $a['voice_mail_extension']) {
				$tmp .= div('VM: '.value_generic($a['voice_mail_number'],$def,'voice_mail_number','list')
						. ($a['voice_mail_extension'] ?
						   ' (x'.value_generic($a['voice_mail_extension'],$def,'voice_mail_extension','list').')' : ''),
						'',$indent);
			} 
			$phones[] = div($tmp);
		}
		$phone = implode("\n",$phones);
	}
	return $phone;
}

function staff_language_f($id)
{
	if (!$def = get_def('staff_language')) {
		return '';
	}

	$lang = webify(call_sql_function('staff_language_f',$id));
	if ($lang) {
		$lang = seclink('staff_language',$lang,'onclick="javascript:showHideElement(\'staff_languageChildList\')"');
	} else {
		$lang = 'No languages';
	}
	return $lang;
}

function form_row_staff_request($key,$value,&$def,$control,&$Java_Engine,$rec)
{
	$transfer_fields = array('staff_id');
	$new_fields      = array('name_last','name_first','name_first_legal','prior_employee_code','prior_staff_id','size_head','home_address','home_phone','gender_code');

	if (!in_array($key,array_merge($transfer_fields,$new_fields))) {
		return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec);
	}

	if (sql_true($rec['is_transfer']) and in_array($key,$new_fields)) {
		return hiddenvar('rec['.$key.']',$value);
	} elseif (sql_false($rec['is_transfer']) and in_array($key,$transfer_fields)) {
		return hiddenvar('rec['.$key.']',$value);
	}

	return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec);
}

function staff_project($id)
{
	$p = array_fetch_column(get_generic(staff_filter($id),'','','staff'),'agency_project_code');
	
	return $p[0];
}

function staff_program($id)
{
	$p = array_fetch_column(get_generic(staff_filter($id),'','','staff'),'agency_program_code');
	
	return $p[0];
}

function staff_position($id)
{
	$p = array_fetch_column(get_generic(staff_filter($id),'','','staff'),'staff_position_code');
	
	return $p[0];
}

function staff_record_association_form($id = false)
{
	// generates a form to find all records of a particular object
	// type that are associated (added_by, changed_by, deleted_by, performed_by, etc) with the given staff_id
	// must be called prior to $AG_HEAD_TAG output.

	global $AG_ENGINE_TABLES;

	foreach ($AG_ENGINE_TABLES as $key) {
		$def = get_def($key);
		$inc = false;
		if (is_array($def['fields']) && has_perm($def['perm_list'])) {
			foreach ($def['fields'] as $field => $pr) {
				if ($pr['data_type'] == 'staff') { $inc = true; }
			}
		}
		if ($inc) {
			$tmp_ar[$key] = ucwords($def['plural']);
		}
	}
	asort($tmp_ar);
	$sel = selectitem('','(choose from list)');
	$staffs = array();
	foreach ($tmp_ar as $key => $plural) {
		$def = get_def($key);
		$sel .= selectitem($key,$plural);
		$staffs[] = array(enquote1($key),enquote1(''),enquote1('(choose from list)'));
		foreach ($def['fields'] as $field=> $pr) {
			if ($pr['data_type'] == 'staff') {
				$staffs[] = array(enquote1($key),enquote1($field),enquote1(str_replace('&nbsp;',' ',strip_tags($pr['label_list']))));
			}
		}
	}

	global $AG_HEAD_TAG;
	$AG_HEAD_TAG .= Java_Engine::get_js('var arrPop=new Array()'."\n".'arrPop = '.Java_Engine::php_to_js_array($staffs)); 

	$out .= anchor('staffAssoc');
	$out .= formto($_SERVER['PHP_SELF'].'#staffAssoc');
	$out .= 'Find all '.selectto('find_staff_object',' onchange="populateSelect(document.'.$GLOBALS['form_name'].'.find_staff_object,document.'.$GLOBALS['form_name'].'.find_staff_object_field,\'\')"');
	$out .= $sel;
	$out .= selectend();
	$out .= ' where '.selectto('find_staff_object_field').selectend().' equals ';
	$out .= $id ? staff_link($id) : pick_staff_to('find_staff_object_staff_id',$active_only = false);
	$out .= button('Go!');
	$out .= formend();

	if (($obj = $_REQUEST['find_staff_object']) && ($fi = $_REQUEST['find_staff_object_field'])
	    && ($id || ($id = $_REQUEST['find_staff_object_staff_id']))) {
		$out .= call_engine(array('page'=>'display.php','object'=>$obj,'action'=>'list','list'=>array('filter'=>array($fi=>$id))),'','','',$d,$d);
	}

	return div($out,'',' class="staff" style="padding: 5px; border: solid 1px black; width: 75%; margin-top: 50px; "');
}

function agency_menu_staff()
{
	/*
	 * Staff Menu
	 */
	
	
	// Staff Schedule
	/*
	$control = array('object'=>'staff_schedule',
			     'action'=>'list',
			     'list'=>array('display_add_link'=>true));
	
	$menu['Staff Scheduling'] = link_engine($control,'Browse Staff Schedules');
	*/
	
	// Staff Request Form
	$add_control = array('object'=>'staff_request',
				   'action'=>'add',
				   'rec_init'=>array('is_transfer'=>sql_false()));
	$transfer_control = $add_control;
	$transfer_control['rec_init']['is_transfer'] = sql_true();
	$control = array('object'=>'staff_request',
			     'action'=>'list',
			     'list'=>array('display_add_link'=>true));
	$menu['New Staff/Transfer Requests'] = html_list(html_list_item(link_engine($add_control,'Add New Staff Request'))
									 . html_list_item(link_engine($transfer_control,'Add Staff Transfer Request'))
									 . html_list_item(link_engine($control,'Browse New Staff/Transfer Requests'))
							

);
	
	$control['object'] = $add_control['object'] = 'staff_termination';
	$add_control['rec_init'] = null;
	
	$menu['Staff Termination Requests'] = html_list(html_list_item(link_engine($add_control,'Add Staff Termination Request'))
									. html_list_item(link_engine($control,'Browse Staff Termination Requests')));
	
	return array($menu,$errors);
}

function staff_id_from_username($uname)
{

	global $AG_AUTH;
	foreach (array('username','username_unix') as $x)
	{
		$res = $GLOBALS['AG_AUTH_DEFINITION']['CASE_SENSITIVE_USERNAME']
			? get_generic(array($x => $uname),' is_active = true DESC',1,'staff')
			: get_generic(array("FIELD:LOWER($x)" => sql_escape_literal(strtolower($uname))),' is_active = true DESC',1,'staff');
		if (count($res) == 1) {
			$rec = array_fetch_column($res,'staff_id');
			return $rec[0];
		}
	
	}
	return false;
}

function staff_id_from_email($email)
{
	$res = get_generic(array('FIELD:LOWER(staff_email)' => sql_escape_literal(strtolower($email))),' is_active = true DESC',1,'staff');
	if (count($res) == 1) {
		$rec = array_fetch_column($res,'staff_id');
		return $rec[0];
	}
	return false;
}

function staff_is_supervised_by($staff_id,$user = null)
{
	global $UID;
	
	$user = orr($user,$UID);

	return sql_true(call_sql_function('is_supervised_by',$staff_id,$user));
}

function staff_remote_access_status_f($id)
{

	global $UID;

	$global=(AG_AUTH_INTERNAL_ACCESS_ONLY===true) ? '' : 'not ';
	$global=smaller(oline().italic("(remote access is {$global}being enforced)"));

	if (Auth::staff_remote_login_allowed($id)) {
	  
	  $out = 'Remote access allowed';

	}

	if ($UID === $id) { 

	  global $AG_AUTH;

	  $username = $_SESSION['AUTH']['username'];
	  
	  if (($pwd = $AG_AUTH->get_raw_password($username)) //if md5, can't check password strength
	      && !is_secure_password($pwd,$username,$dummy)) {
	    
	    $out = red(($out ? oline($out.'*') : '') . smaller(' '.bold('*').' Password not strong enough for remote access'));
	    
	  } 
	  elseif ($out){

	    $out = span($out.$global,' class="message"');

	  }
	}
	elseif($out)  {
	  
	  $out = span($out.$global,' class="message"');
	  
	}
	
	
	return $out;

}

function staff_supervisor_transfer_form($sid)
{
	/*
	 * Returns a form, and contains the functionality for
	 * transferring $sid to another staff. This will only
	 * transfer active staff.
	 */

	global $AG_AUTH, $UID;

	$action         = $_REQUEST['sstrans'];
	$new_supervisor = (int) $_REQUEST['sstrans_new_id'];
	$perm = 'admin';

	$anchor = 'supervisees';

	switch ($action) {

	case 'post' :
		if (!has_perm($perm)) {
			return red('No permission for transferring supervisees');
		}

		if ($AG_AUTH->reconfirm_password() && is_staff($new_supervisor)) {

			/*
			 * Password confirmed, so the transfer is processed
			 */

			$def = get_def('staff_employment');

			//fixme: use filters
			$res = agency_query('UPDATE ' . $def['table_post'] . ' SET supervised_by = '.$new_supervisor
						.', changed_by = '.$UID.', changed_at=CURRENT_TIMESTAMP, 
                                    sys_log=COALESCE(sys_log||E\'\n\',\'\')||\'Transferring all staff from '.$sid.' to '.$new_supervisor.'\' '
						. 'WHERE staff_employment_id IN (SELECT staff_employment_id FROM staff_employment_latest WHERE supervised_by = '.$sid.')
                                       AND staff_id IN (SELECT staff_id FROM staff WHERE supervised_by = '.$sid.' AND is_active)');

			if ($res) {

				return span('Successfully transfered the above listed staff to '.staff_link($new_supervisor),' class="message"');

			} else {

				return red('Transfer failed');

			}


		}

		$message = oline(red('Incorrect password for '.staff_link($UID)));

	case 'confirm' :
		if (!has_perm($perm)) {
			return red('No permission for transferring supervisees');
		}

		if (is_staff($new_supervisor)) {

			return $message
				. formto(AG_STAFF_PAGE.'#'.$anchor,'',$AG_AUTH->get_onsubmit(''))
				. 'Enter password for '.staff_link($UID).' to confirm transfer of all supervisees from '
				. staff_link($sid) . ' to '. staff_link($new_supervisor). $AG_AUTH->get_password_field()
				. hiddenvar('sstrans_new_id',$new_supervisor)
				. hiddenvar('id',$sid)
				. hiddenvar('sstrans','post')
				. button('Confirm','','','','','class="engineButton"')
				. formend();

		}

	case 'ini' :
		/*
		 * Return a form
		 */
		if (!has_perm($perm)) {
			return red('No permission for transferring supervisees');
		}

		return formto(AG_STAFF_PAGE.'#'.$anchor)
			. hiddenvar('sstrans','confirm')
			. hiddenvar('id',$sid)
			. 'Transfer ' . italic('all') . ' ' . staff_link($sid).'\'s supervisees to '.pick_staff_to('sstrans_new_id','HUMAN')
			. button('Go','','','','','class="engineButton"')
			. formend();
	default :

		return hlink_if(AG_STAFF_PAGE.'?id='.$sid.'&sstrans=ini#'.$anchor,'Transfer supervisees',has_perm($perm));

	}

}

function staff_permission_check( $id )
{
	/*
	 * Bug 28545
	 * Function to test & display which permissions a staff person has.
	 * This is _not_ the same as looking in the database, because this
	 * will use has_perm and all it's built-in permissions.
	 * This will not reflect engine permissions, such as my_project, my_client
	 */

	$list = get_generic('','permission_type_code','','l_permission_type');
	$out = header_row('Permission Type','Description','Read','Write','Super');

	while( $item = array_shift( $list )) {
		$ptc = $item['permission_type_code'];
		$row = cell($ptc, 'class="generalData1"') . cell($item['description'], 'class="generalData1"');
		foreach( array('R','W','S') AS $y ) {
			$row .= cell( (has_perm($ptc,$y,$id) ? green('Y') : red('-')), 'class="generalData1"' );
		}
		$w = $w==2 ? 1 : 2;
		$out .= row($row, ' class="generalData'.$w.'"');
	}		
	return table($out,'','style="border: solid 1px black;" cellspacing="0px" cellpadding="2px"' );

}

?>
