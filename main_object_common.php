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

/*
 *  This file is home for all functions that are common between varying
 *  main object types (eg, client and donor) as specified in the AG_MAIN_OBJECT_DB
 *  variable.
 *
 */

function client_filter( $id )
{
	return array(AG_MAIN_OBJECT_DB.'_id'=>$id);
}

function verify_command_box($display,$object)
{
      if (is_array($display)) {
		foreach ($display as $c_object => $options) {
			if (is_array($options)) {
				$value=$options['max'];
				if (is_valid($value,'integer')) {
					$display[$c_object]['max'] = $value;
				} else {
					$def = get_def($c_object);
					$display[$c_object]['max'] = orr($_SESSION['DISPLAY_'.strtoupper($object)][$c_object]['max'],$def['list_max']);
				}
			}
		}
	}
	return $display;
}

function client_selector($show_selected="Y",$text="",$form="N")
{
	global $colors,$client_select,
		$client_remove, $CLIENTS, $STAFF;
	$client_remove = orr($client_remove,$_REQUEST['client_remove']);
	$client_select = orr($client_select,$_REQUEST['client_select']);
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];

	$text=orr($text,'Enter search text to add a '.ucfirst($mo_noun));
	if (isset($client_select) )
	{
		add_id( $client_select, $CLIENTS); 
		$_SESSION['LOG_CLIENTS'] = $CLIENTS; //added this in order to be rid of phplib - temp hack
		$cl=sql_fetch_assoc(client_get($client_select));
		if ($staffs=get_staff_clients($cl[AG_MAIN_OBJECT_DB.'_id'],true)) {//only staff who want alerts
			foreach($staffs as $sid) {
				if (is_numeric($sid)) { //no outside staff
					add_id($sid,$STAFF);
					$_SESSION['LOG_STAFF'] = $STAFF;
				}
			}
		}
	}
	if (isset($client_remove) ) {
		remove_id( $client_remove, $CLIENTS ); 
		$_SESSION['LOG_CLIENTS'] = $CLIENTS;
	}
	$output =
		table(row(
			    bottomcell(
					   ( $form=="Y" ? formto($_SERVER['PHP_SELF']) : "")
					   .  (   ($show_selected=="Y") ?
						    oline(bigger(bold(ucfirst($mo_noun).'s Referenced:')))
						    . oline( show_selected_clients($CLIENTS,"removeok") )
						    : "" )
					   . client_quick_search($text,"NoForm")
					   . hiddenvar("select_to_url",$_SERVER['PHP_SELF'])
					   . ($form=="Y" ? formend() : "")
					   ,"align=\"center\""))
			,"bgcolor=\"${colors['client']}\"");
	
	return $output;
}

function show_selected_clients( $clients, $removeok="" )
{
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
	$count=count($clients);
	$otmp= ($count==0) ? "No" : "The Following";
	$output =oline($otmp .' '.ucfirst($mo_noun).'s are Selected:');
	foreach ($clients as $x)
	{
   		$output .= oline(
				     ($removeok ?  "("
					. hlink($_SERVER['PHP_SELF'] . "?client_remove="
						  . $x, smaller("Remove",2)) .")  " : "")
				     . client_link( $x ) );
	}
	return $output;
}

function client_select_prepare($force = false)
{
	global $client_select_sql;

	$def = get_def(AG_MAIN_OBJECT_DB);
	
	$client_table    = $def['table'];
	$client_table_id = $def['id_field'];

	static $query_prepared;
	$query = $client_select_sql .  " WHERE $client_table_id = ";
	if (!$query_prepared || $force) {

		$res = sql_query("PREPARE client_select (integer) AS $query\$1");
		$query_prepared = true;

		return $res;

	}

	return $query_prepared && !$force;

}

function client_get( $idnum )
{

	/*
	 * Prepare select
	 */

	client_select_prepare();

	if (!$a = sql_query( "EXECUTE client_select ($idnum)" )) {

		/*
		 * Try to prepare again
		 */

		if (!client_select_prepare($force = true) ) {

			/*
			 * It is important to exit the script here, since many things 
			 * depend on this query returning a result.
			 */
			sql_die('Couldn\'t EXECUTE prepared '.AG_MAIN_OBJECT_DB.' query.');
			
		}

	}

	if (sql_num_rows($a)==0) {

		$b = agency_query("SELECT * FROM duplication",array(AG_MAIN_OBJECT_DB.'_id_old'=>$idnum));

		if ( ($b) && (sql_num_rows($b)==1)) {

			$b = sql_fetch_assoc($b);
			return client_get( $b[AG_MAIN_OBJECT_DB.'_id']);

		}

	} elseif (sql_num_rows($a)==1) {

		return $a;

	}

	return false;

}

function client_quick_search($label="", $form="Yes")
{
      $label=orr($label,$GLOBALS['AG_TEXT']['CLIENT_QUICK_SEARCH']);
	if ($form=="Yes")
	return  formto("client_search.php")
		. formvartext("QuickSearch")
		. "<br>"
		. button($label)
		. smaller("<br>" . help('QuickSearch','','help','',false,true))
			    . " | " . hlink($GLOBALS['agency_search_url'], "advanced search") . formend()
		;
	else
	return formvartext("QuickSearch")
            . "&nbsp;&nbsp"
            . button($label)
		. hiddenvar("action","ClientSearch");
}

function process_quick_search($stop="Y",$allow_other=true,$use_old=false,$return_result=false)
{
	// Perform a client search, if submitted from previous page:
      global $action, $QuickSearch;
	$QuickSearch = orr($QuickSearch,$_REQUEST['QuickSearch']);
	$action = orr($action,$_REQUEST['action']);
	if ( ($action=="ClientSearch") && $QuickSearch )
	{
		$out = head($GLOBALS['AG_TEXT']['CLIENT_SEARCH_RESULTS']);
            $out .= client_search('N',$allow_other,$use_old);
		if ($return_result) {
			return $out;
		}
		if ($stop)
		{
			agency_top_header();
			out($out);
			page_close($silent=true);
			exit;
		}
		out($out);
	}
}

function client_links( $idnum, $name="lookup", $url="" , $options=null,$sep=NULL) {
	$sep=orr($sep,oline());
	if (be_null($idnum)) {
			return false;
	}
	if (!is_array($idnum)) {
		if (preg_match('/^\{.*\}$/',$idnum)) {
			$idnum=sql_to_php_array($idnum);
		}
	}
	foreach ($idnum as $id) {
		$out[]=client_link($id,$name,$url,$options);
	}
	return implode($sep,$out);
}


function client_link( $idnum, $name="lookup", $url="" , $options=null)
{     // Doesn't Validate or Match id with name
	// If number passed for $name, will be max length of name
	global $client_page, $client_table, $client_table_id,$client_links;
	// if passed text name for ID, simply return it.
	if ( is_array($idnum) && (!is_assoc_array($idnum)) )
	{
		$res=array();
		foreach( $idnum as $id )
		{
			array_push($res,client_link($id,$name, $url, $options));
		}
		return $res;
	}
	elseif ( is_array($idnum) )
	{
		$q=$idnum;
		$idnum=$q[AG_MAIN_OBJECT_DB.'_id'];
		if (!$name)
		{
			$name=client_name($q);
		}
	}
	elseif (! is_numeric( $idnum ))
	{
		return $idnum;
	}
	if (is_numeric($name))
	{
		$max_length=$name;
		$name="lookup";
	}
	if (isset($client_links[$idnum]))
	{
		return $client_links[$idnum];
	}
	if ($name=="lookup" || (!$name))
	{
		$name=client_name($idnum,$max_length);
	}
	$result=hlink($GLOBALS['off']. orr($url,$client_page)."?id=$idnum",$name,'',$options) . "\n";
	$client_links[$idnum]=$result;
	return $result; 
}

function is_client( $idnum )
{
	global $client_table, $client_table_id;
	if (!is_valid('integer_db',$idnum)) {
		return false;
	}
	$q = sql_query("SELECT * from $client_table WHERE $client_table_id = $idnum");
	return ($q && (sql_num_rows($q) > 0) );
}

function client_staff_assignments($id,$type='') {

	$def   = get_def('staff_assign');
	$table = $def['table'];

	$sql='SELECT staff_assign_id,staff_id_name,description FROM '.$table
		.'_current LEFT JOIN l_staff_assign_type USING (staff_assign_type_code)';
	$filter=client_filter($id);
	if ($type) {
		$filter['staff_assign_type_code']=$type;
	}
// 	$filter[]=array('FIELD:BE_NULL(staff_assign_date_end)'=>'true',
// 			    'FIELD>=:staff_assign_date_end'=>'CURRENT_DATE');

	$order='description DESC';
	$res = agency_query($sql,$filter,$order);
	return $res;
}

function client_staff_assignments_f($id) {
	
	$def = get_def('staff_assign');

	$res=client_staff_assignments($id);
	$assigns=array();
	if (sql_num_rows($res)>0) {
		while ($a=sql_fetch_assoc($res)) {
			$staff=$a['staff_id_name']; //can either be an ID or a name
			$type=$a['description'];
			$id=$a['staff_assign_id'];
			$assigns[$staff][$type]=$id;
			// 			if (array_key_exists($staff,$assigns)) {
			// 				array_push($assigns[$staff],array($type=>$id));
			// 			} else {
			// 				$assigns[$staff]=array();
			// 				array_push($assigns[$staff],array($type=>$id));
			// 			}
		}
		foreach ($assigns as $staff => $assign) {
			
			$tmp=array();
			foreach ($assign as $type=>$id) {
				$formatted = alt(link_engine(array('object'=>'staff_assign','action'=>'view','id'=>$id),blue($type)),
						     'Click to view ' . $def['singular']);
				array_push($tmp,$formatted);
			}

			$out .= oline(bigger(staff_link($staff)).' ('.implode(', ',$tmp).')');
		}
		
	} else {
		$out = oline('No ' . $def['plural']);
	}
	return smaller($out);
}

//bringing this in from log
// used for client & staff selectors
function add_id( $id, &$selected )
{
	$selected = orr($selected,array());
	if (!in_array($id,$selected))
	{
		array_push($selected,$id);
	}
}

function get_staff_clients($client_array,$for_alerts=false,$not_just_monitoring=false) {
	//return an array of staff
	if (empty($client_array)) {
		return array();
	}
	$sql = "SELECT staff_id FROM staff_assign";
	$filter = array(
			    AG_MAIN_OBJECT_DB.'_id'=>$client_array,
			    array('FIELD:BE_NULL(staff_assign_date_end)'=>'true',
				    'FIELD>=:staff_assign_date_end'=>'CURRENT_DATE')
			    );
	if ($for_alerts) {
		$filter['send_alert']=sql_true();
	}
	if ($not_just_monitoring) {
		$filter['!staff_assign_type_code']='MONITOR';
	}
	$res=agency_query($sql,$filter);
	return sql_fetch_column($res,'staff_id');
}

function client_id_from_link($link) {
	$id = substr($link,strpos($link,'?id=')+4);
	$id = substr($id,0,strpos($id,'"'));
	return $id;
}

/*
 * The following functions deal exclusively with photos and could probably
 * be safely moved to a photo-specific file for inclusion.
 */

function client_photo_url( $idnum, $scale=1 )
{
	return object_photo_url($idnum,AG_MAIN_OBJECT_DB,$scale);
}

function client_photo_filename( $idnum,$ver="FULL",$timestamp="" )
{
	// $ver: specify "THUMB" to get the filename for the thumbnail
      global $AG_CLIENT_PHOTO_BY_FILE,$AG_CLIENT_PHOTO_BY_URL;

      $http = $AG_CLIENT_PHOTO_BY_URL;
      $file = $AG_CLIENT_PHOTO_BY_FILE;
	$path = "pc" .  substr("0". intval($idnum/1000),-2);
	$ext = ".jpg";
	switch ($ver)
	{
	case "BASE_H" :
		return $http . '/' . $path;
		break;
	case "BASE_F" :
		return $file . '/' . $path;
		break;
	default:
		return $file . '/' . $path . "/$idnum"
			. ($ver=="THUMB" ? ".120x160" : "") 
			. ($ver=="SOURCE" ? "-$timestamp" : "")
			. $ext;
	}
}

function client_photo_transfer( $new_client, $old_client , $use_old=false)
{
	// Placeholder for function to be used in conjunction w/
	// client unduplication.

	// General idea:  Get all $old_client photos w/ client_photo()
	// Get correct filename for $new_client with client_photo_filename()
	// move the file from old to new.
	// (Probably want to remove the default symlink on the old client first)
	// Then might need other function to choose which is the default (symlinked) photo

      global $AG_CLIENT_PHOTO_BY_FILE,$AG_CLIENT_PHOTO_BY_URL;
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];

      $success = true;

      if (!has_photo($old_client)) {
		//nothing to do
		outline('No photos for '.$mo_noun.' '.$old_client.' to transfer.');
		return false;
      }
      
      $new_directory = client_photo_directory($new_client);
      $old_directory = client_photo_directory($old_client);

      $existing_photo_files=array();
      if (has_photo($new_client)) {      

		$existing_photo_files = photo_filenames($new_client);
		$current_new          = client_photo_filename($new_client);

		if (is_link($current_new)) {
			$current_new = readlink($current_new);
		}
		clearstatcache();
      }
      $old_photos  = photo_filenames($old_client);
      $current_old = client_photo_filename($old_client);
      if (is_link($current_old)) {
		//remove symbolic links
		$old_link    = $current_old;
		$current_old = readlink($current_old);
		unlink($old_link);
      }
	clearstatcache();

      foreach ($old_photos as $old_file) {

		// REMOVE THUMBNAIL
		if ($old_file == client_photo_filename($old_client,'THUMB')) {

			if (!unlink($old_file)) {
				outline("Couldn't remove $old_file");
				$success=false;
			}
			continue;
		}

		$sym_link_this = false;
		$old_file_name = substr($old_file,strlen($old_directory));
		if ( (!$current_new || $use_old) && ($old_file == $current_old) ) {
			$sym_link_this=true;
		}		 

		$new_file_name = $new_client . substr($old_file_name,strlen($old_client));
		$mod_time      = photo_time($old_file);
		if ( $new_file_name == $new_client.'.jpg') {  //file name: 6000.jpg
			$new_file_name=$new_client.'-'.$mod_time.'.jpg'; //file name: 6000-2002-10-10 12:12:12.jpg 
		}

		$count = 0;
		$new_file=$new_directory . $new_file_name;
		while (in_array($new_file,$existing_photo_files)) {
			$count++; 
			$tmp      = explode('.',$new_file);
			$tmp[0]  .= '-'.$count;  //throw a number on the end
			$new_file = implode('.',$tmp); 
		}

		if (!rename($old_file,$new_file)) {
			outline("Couldn't move $old_file to $new_file");
			$success=false;
		}

		if ($sym_link_this) {
			$new_link_file=$new_file;
		}
      }
      clearstatcache();

      //GENERATE NEW LINK
      if ($use_old || !$current_new) {
		$new_link_file = orr($new_link_file,$new_file); //default to last looped filename
		$success = update_client_photo($new_client,$new_link_file);
      }

      //UPDATE THUMBNAIL
      $res = update_thumbnail($new_client);
      
      return $success;
}

function update_client_photo($id,$new_file_name)
{
      global $AG_CLIENT_PHOTO_BY_FILE;

      $file_old = $sym_link = client_photo_directory($id).$id.'.jpg';

      if (is_file($file_old)) {
		if (is_link($file_old)) {  //relies on standard naming convention
			//REMOVE OLD LINK
			if (!unlink($file_old)) {
				outline("Failed to remove $file_old");
			}
		} else { //wasn't a link, so we rename it
			$res = client_photo_move($id,$file_old);
		}
      }
	clearstatcache();
      return symlink($new_file_name,$sym_link);
}

function current_photo_file($id)
{
      if (!has_photo($id)) {
		return false;
      }

      $file = client_photo_filename($id);
      if (is_link($file)) {
		$file = readlink($file);
      }
      clearstatcache();
	return $file;
}

function update_thumbnail($id)
{
      if ($file  = current_photo_file($id)) {

		$thumb = client_photo_filename($id,"THUMB");
		if (exec("convert -geometry 120 \"$file\" $thumb"))
		{
			outline ("Couldn't convert $file to thumbnail");
			return false;
		}
		return true;
	}
	return false;
}

function client_photo_move($id,$file)
{
      //ASSUMES YOU ARE STARTING WITH AN OLD PHOTO FILE NAME eg 5000.jpg
      global $AG_CLIENT_PHOTO_BY_FILE;

      $photos   = photo_filenames($id);      //existing photos
      $client   = client_get($id);
      $time     = orr($client['last_photo_at'],photo_time($id,$file));
      $new_file = client_photo_directory($id).$id.'-'.$time.'.jpg';
	var_dump($new_file);
//       if (exec("mv \"$file\" \"$new_file\"")) {
      if (!rename($file,$new_file)) {
		outline("Couldn't move $file to $new_file");
		return false;
      }
	return true;
}

function photo_time($file)
{	
      $filedate=date('Y-m-d G:i:s',filemtime($file));
      clearstatcache();      
      return $filedate;
}

function photo_filenames($id)
{
      //returns an array of file names
      $photos=client_photo($id,1,true);
      $files=array();
      foreach($photos as $photo_rec)
      {
		if (!is_link($photo_rec['file']))
		{
			array_push($files,$photo_rec['file']);
		}
		clearstatcache();
      }
      return $files;
}

function client_photo_directory($id)
{
      global $AG_CLIENT_PHOTO_BY_FILE;
      return $AG_CLIENT_PHOTO_BY_FILE.'/pc'. substr('0'.intval($id/1000),-2).'/';
}

function client_photo( $idnum, $scale=1, $all_in_array=false )
{
	if (!$all_in_array)
	{
		return hlink(  client_photo_url($idnum,4), 
				   httpimage(client_photo_url($idnum,$scale),120*$scale,NULL,0));
	}
	$base_f=client_photo_filename($idnum,"BASE_F"); // not id-speficic
	$base_h=client_photo_filename($idnum,"BASE_H"); // "
	$photos=array();
	$files=glob("$base_f/$idnum*");
	foreach ($files as $f)
	{
		if (preg_match('/^(.*)' . $idnum . '$/',$f,$matches))
		{
			$f=$matches[1];
		}
		$hlink=$base_h."/". rawurlencode(basename($f));
		$p["file"]=$f;
		$p["http"]=hlink($hlink,httpimage($hlink,120*$scale,NULL,0));
		if (preg_match("/120x160/i",$f))
		{
			$p["size"]="thumb";
		}
		else
		{
			$p["size"]="full";
		}
		if (preg_match('/[-]([0-9]{4}([-][0-9]{2}){2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', $f, $matches ))
		{
			$p["timestamp"]=datetimeof($matches[1]);
			$p["time"]=timeof($matches[1]);
			$p["date"]=dateof($matches[1]);
		}
		elseif (preg_match('/unknown/i',$f))
		{
			$p["timestamp"]="unknown";
			$p["time"]="unknown";
			$p["date"]="unknown";
		}
		array_push($photos,$p);
	}
	return $photos;
}

function has_photo($idnum)
{
	global $AG_CLIENT_PHOTO_BY_FILE;
	if (!$AG_CLIENT_PHOTO_BY_FILE) {
		return false;
	}
      $has_photo=client_photo_url($idnum);
      return $has_photo <> $GLOBALS['AG_IMAGES']['NO_PHOTO'];
}

/*
 * End Photo Section
 */


function generic_home_sidebar_left()
{
    $name = 'Agency';
    for ($x=0;$x<strlen($name);$x++) {
        $agency_words .= row(cell2_title($name,$x));
    }
    $agency_logo_f = div('','',
                    ' style="width: 4em; height: 17em; background: url('.$GLOBALS['AG_IMAGES']['AGENCY_LOGO_MEDIUM']
                    .') center center; margin: 0px 0px 0px 0px; opacity: .1;"');
    $agency_logo_b = table($agency_words,'',
                    ' style="width: 10em; height: 16em; background: transparent; margin: 0px 0px 0px 0px; position: relative; top: -17em;"');
    return $org_logo . $agency_logo_f . $agency_logo_b;
}

function agency_home_links()
{
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
	///* setup home links */
	$add_object_link = hlink("object_reg.php?object=" . AG_MAIN_OBJECT_DB,'Add '.ucfirst($mo_noun));
	$home_links_1 = array(add_link('feedback'),
				    link_admin(), //AGENCY admin
				    $add_object_link
				    );
	//$home_links_2 = array('add second line of commands here');
	return implode(' | ',$home_links_1)
		. ($home_links_2 ? oline() . implode(' | ',$home_links_2) : '');

}
/*
 * Object registration (adding) functions
 */

function object_reg_search_verify($object,$def,$rec)
{
//	global $rec,$def;

	$def['multi_records'] = null;
	$valid = $def['fn']['valid']($rec,$def,$out,'add');
	$out = $out ? red($out) : '';
	if (!$valid) {
		$out .= object_reg_form($object,$def,$rec);
	} else {
		$func = $object.'_reg_search';
		if (function_exists($func)) {
			$result = $func($def,$rec);
		} else {
			$result = object_reg_search($object,$def,$rec);
		}
		if ($result===null) {
			$jump="Location: display.php?control[action]=add&control[object]=$object&control[no_object_reg]=true";
			foreach ($rec as $k=>$v) {
				$jump .= '&control[rec_init]['.$k.']='.urlencode($v);
			}
			header($jump);
			page_close();
			exit;        
		}
	}
	return $out . $result;
}

function object_reg_form($object,$def,$rec)
{
//      global $def,$rec,$main_object_reg_prompt;

	$focus = array_keys($rec);
	$out .= oline(bigger(bold($main_object_reg_prompt,2)))
		. formto()
	      . tablestart('','class="engineForm"')
		. $def['fn']['form']($rec,$def,array('action'=>'add'))
		. hiddenvar('action','search')
		. hiddenvar('object',$object)
		. rowrlcell(button('Submit','','','','','class="engineButton"'),link_agency_home('Cancel','','class="linkButton"'))
	      . tableend()
		. formend();
	form_field_focus('elements["rec['.$focus[0].']"]');
	return $out;
}


function object_reg_search($object,$def,$rec)
{
	$reg_search_fields = $def['registration']['search_fields']; // orr w/ something as default?
	foreach ($reg_search_fields as $x)	{
		$$x=$rec[$x];
	}
	$filter = build_object_match_filter($object,$def,$rec);
	$object_search_rank = eval( "return " . $def['registration']['match_result_order'].';' );

	$obj = $def['object'];
	$obj_label = $def['singular'];
	$objs = $def['plural'];
	$tmp_object = 'control_'.$object.'_reg';
	$control=array(
		'object'=>$tmp_object,
		'action'=>'list',
		'list'=>array(
			'filter'=>$filter,
			'order'=>array('match'=>true)
		)
	);

	$title = "Review existing $objs for a match";
	$out .= oline(smaller('(searching on name:'.bold($name_last.', '.$name_first).', dob: '.bold($dob).', ssn: '.bold($ssn).')'));
	$out.=oline() . oline(red("Review the following $objs to make sure $obj_label is not already registered"));
	$def['sel_sql'] = "SELECT *,$object_search_rank as match FROM " . $def['table'];

	//------ Highlight exact matches ------//
	foreach ($reg_search_fields as $field) {
		$def['fields'][$field]['value_format_list'] = '(strtolower($x)==strtolower(\''.$rec[$field].'\')) ? bigger(bold($x)) : $x';
		$REC[$field] = $rec[$field];
	}

	// FIXME: HACK ALERT: stuff a temporary def for engine to use
	$GLOBALS['AG_ENGINE_TABLES'][$tmp_object]=$tmp_object;
	$t_def=$def;
	$t_def['list_hide_view_links'] = true;
	$t_def['object']=$tmp_object;
	$GLOBALS['engine'][$tmp_object]=$t_def;
	// /HACK
	$out.=call_engine($control,$tmp_object,$NO_TITLE=true,$NO_MESSAGES=false,$TOT,$PERM);
	$continue_link=link_engine(array('object'=>$obj,
		'action'=>'add',
		'no_object_reg'=>true,
		'rec_init'=>$REC),
		"If the $obj_label is not already registered, proceed here");
	$return_url = $_SERVER['PHP_SELF'].'?object='.$object;
	foreach ($REC as $k=>$v) {
		$return_url .= '&rec['.$k.']='.urlencode($v);
	}
	$return_link=hlink($return_url,'Return to search form');
	$out .= oline() . bigger(oline(bigger($continue_link,2),2).$return_link);
	return ($TOT==0)
	? NULL
	: $out;
}

function build_object_match_filter($object,$def,$rec)
{
	foreach ($rec as $k=>$v) {
		$$k=$v;
	}
	//Name stuff
	$meta_first = levenshteinMetaphoneDistance($name_first,'name_first');
	$meta_last =  levenshteinMetaphoneDistance($name_last,'name_last');

	// FIXME: I Am a hack!
	if ($object=='guest') {
	$filter=array(
					array('FIELD<=:'.$meta_first => LEVENSHTEIN_ACCEPT,
						'FIELD<=:'.$meta_last => LEVENSHTEIN_ACCEPT,
						'ILIKE:name_alias'=>"%$name_last%",
						'ILIKE:name_alias '=>"%$name_first%"));
	} else {
	$filter=array(
					array(array('FIELD<=:'.$meta_first => LEVENSHTEIN_ACCEPT,
						'FIELD<=:'.$meta_last => LEVENSHTEIN_ACCEPT),
						array('ILIKE:name_alias'=>"%$name_last%",
						'ILIKE:name_alias '=>"%$name_first%")));
	}
	if ( ssn_of($ssn) and ssn_of($ssn)!='999-99-9999') { //Don't search on 999-99-9999 or blank

		//SSN variations
		for ($x=0;$x<11;$x++) {
			//find matching ssn's off by one digit
			if ($x==3 || $x==6) continue;
			$ssn_search[] = substr_replace($ssn,'_',$x,1);
		}
		
		$filter[0][] = array(" LIKE : ssn "=>ssn_flip($ssn));
		$filter[0][] = array(" LIKE : ssn "=>ssn_flip($ssn,7));
		$filter[0][] = array(" LIKE : ssn "=>ssn_flip($ssn,5));
		$filter[0][] = array(" LIKE : ssn "=>$ssn_search);
	}

	if (dateof($dob)) { //set DOB
		$filter[0]["dob"]=dateof($dob,"SQL"); 
	} elseif (!is_assoc_array($filter[0])) {
		//must make filter an associative array, or read_filter will barf
		$filter[0]['NULL:'.$def['id_field']]=true; //or primary key  IS NULL -- will never return true
	}

	$filter=array_filter($filter); //remove blanks which crash sql searches
	return $filter;
}

?>
