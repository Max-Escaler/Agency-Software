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
$quiet="Y";
include "includes.php";
// this was copied from query.php,
// and hacked (& cleaned a bit) for use w/ engine.

function query_type_to( $varname, $data_type="", $cell_opts="" )
{
	$opts = $data_type=='lookup'
		? ' onchange="valueToggleDisplay(this,[\'in\',\'notin\'],\'table-cell\',\'qCell'.$varname.'Check\',\'qCell'.$varname.'List\')"'
		: '';
      $_SESSION[$varname.'objectQueryType'] = $default = orr($_REQUEST[$varname],$_SESSION[$varname.'objectQueryType']);
	return cell(
			selectto( $varname , $opts)
			. choose_query_type( $data_type,$default )
			. selectend(),$cell_opts );
}

function choose_query_type( $data_type='' ,$default='')
{
// Short option for SQL pick lists, where
// only equals/doesn't equal will likely
// make sense. 
// (added null/not null to short and long lists).

// *** We need to include a way to pick multiple
// *** options, for lookup codes, and others

      $query_options = 
		array(
			'shortlist'=>
			array(
				'equal'=> 'Equals',
				'notequal'=> 'DOESN\'T Equal',
				'in'=> 'IS one of',
				'notin'=> 'IS NOT one of',
				'null'=> 'IS NULL',
				'notnull'=> 'IS NOT NULL'),
			'fulllist'=>
			array(
				'sub'=> 'Contains' ,
				'start'=> 'Starts with' ,
				'end'=> 'Ends with' ,
				'equal'=> 'Equals',
				'notequal'=> 'does not Equal',
				'greater'=> 'is Greater than',
				'less'=> 'is Less than',
				'greatereq'=> 'is Greater than or equal to',
				'lesseq'=> 'is Less than or equal to',
				'null'=> 'Is NULL',
				'notnull'=> 'Is NOT NULL',
				'between'=> 'BETWEEN'),
			'array_types'=>
			array(
				'array_contains'=>'Contains ANY of the selected values',
				'array_equals'=>'Equals EXACTLY the selected values')
			);

	switch ($data_type) {
//       $short_lookup_types=array("lookup","staff","boolean");
//       $selects = (in_array($data_type,$short_lookup_types))
// 		? $query_options['shortlist'] : $query_options['fulllist'];
	case 'lookup' :
	case 'staff':
	case 'boolean':
		$selects = $query_options['shortlist'];
		break;
	case 'array':
	case 'lookup_multi':
		$selects = $query_options['array_types'];
		break;
	default:
		$selects = $query_options['fulllist'];
	}
      $out = selectitem('na','(not used in this search)');
      foreach ($selects as $var=>$text)
      {
		$out .= selectitem($var,$text,($var==$default));
      }
      return $out;
}

/* use build_lookup_query() instead, and remove this function on the next round
function lookup_query( $l_def )
{
	// take a lookup array and make an SQL out of it
	// this maybe already exists somewhere in engine code?
	$table=$l_def["table"];
	$value=$l_def["value_field"];
	$label=$l_def["label_field"];
	return "SELECT $value AS value, $label AS label FROM $table";
}
*/

function build_filter( $post_array, $object="" )
{
// copied from build_where_string
// This function builds a filter to
// be used with list, query, etc.
// The post_array format is arbitrary,
// but has to match with query_row().

// currently, we get name => array("op"=>operator,"value"=>value)

    foreach ($post_array as $name=>$params) {
	    $type=$params["op"];
	    $text=$params["value"];
	    switch ($type) {
	    case "null":
		    $op = "NULL";
		    $text="(dummy)";
		    break;
	    case "notnull":
		    $op = "!NULL";
		    $text="(dummy)";
		    break;
	    case "sub":
                $op = "ILIKE";
                $text = "%$text%";
                break;
	    case "start":
                $op = "ILIKE";
                $text .= "%";
                break;
	    case "end":
                $op = "ILIKE";
                $text = "%$text";
                break;
	    case "equal":
                $op = "=";
                break;
	    case "notequal":
                $op = "<>";
                break;
	    case "in":
		    $op = "IN";
		    if (!is_array($text)) {
			    $text = array($text);
		    }
		    break;	
	    case "notin":
		    $op = "NOT IN";
		    if (!is_array($text)) {
			    $text = array($text);
		    }
		    break;	
	    case "greater":
                $op = ">";
                break;
	    case "less":
                $op = "<";
                break;
	    case "greatereq":
                $op = ">=";
                break;
	    case "lesseq":
                $op = "<=";
                break;
	    case 'between':
		    $op ='BETWEEN';
		    break;
	    case 'array_contains':
	    case 'array_equals':
		    $op = strtoupper($type);
		    if (!is_array($text)) {
			    $text = array($text);
		    }
		    break;
	    default:
                outline("Warning--ignoring unknown search type: "
				. $type );
                $name = "";
                break;
	    }
	    $filter["$op:$name"]=$text;
    }
    return $filter;
}

function query_row( $f_def, $row_opts='',$cell_opts="" )
{
// copied from search_row, but to update with engine functionality
// f_def is the field def.

// returns a row for search with variables assigned to
// create_filter_{$field_name}_value and
// create_filter_{$field_name}_key.

	global $form_name_group;
	if ($f_def=="header")
	{
		$disclaimer_link = hlink('#groupingDisclaimer',red('*'),'','class="fancyLink"');
		return row(
			     cell(bold("Field Name"),$cell_opts) . cell(bold("Condition"),$cell_opts) . cell(bold("Value"),$cell_opts) 
			     . cell(bold('Group'.$disclaimer_link),$cell_opts),$row_opts);
	}
	$label = $f_def["label_view"];
	$type = $f_def["data_type"];
	$lookup = $f_def["lookup"];
	$field_name = $f_def["field"]; // this is currently part of a standard field def, but I think should be
	$row = cell( smaller(bold($label)),$cell_opts )  . query_type_to( "create_filter_{$field_name}_key",$type,$cell_opts);
	$form_name="create_filter_{$field_name}_value";
	$_SESSION[$form_name.'default'] = $default = orr($_REQUEST[$form_name],$_SESSION[$form_name.'default']);
	$default_group=$_REQUEST[$form_name_group];
	is_array($default) && ($default=array_keys($default));
	is_array($default_group) && ($default_group=array_keys($default_group));
	$default_group=orr($default_group,array());

	$format='checkbox';
	$m_cell_opts = $cell_opts;
	switch ($type) {
	case 'lookup_multi':
//  		$cell .= do_pick_sql( lookup_query($lookup), $default, $true,$format,$form_name);
		$cell .= do_pick_sql( build_lookup_query($f_def,'edit'), $default, $true,$format,$form_name);
		break;
	case 'lookup' :
		if (is_array($default)) {
			$list_opts = 'style="display:none;" ';
			$list_disab = ' disabled="t"';
			$check_default = $default;
		} else {
			$list_default = $default;
			$check_opts = ' style="display:none;"';
			$check_disab = ' disabled="t"';
		}
//             $cell .= do_pick_sql( lookup_query($lookup), $check_default, $true,$format,$form_name,$check_disab );
            $cell .= do_pick_sql( build_lookup_query($f_def,'edit'), $check_default, $true,$format,$form_name,$check_disab );
// 		$extra_cell = cell(selectto( $form_name,$list_disab ) . do_pick_sql(lookup_query($lookup),$list_default) . selectend(),
		$extra_cell = cell(selectto( $form_name,$list_disab ) . do_pick_sql(build_lookup_query($f_def,'edit'),$list_default) . selectend(),
					 $cell_opts.' '.$list_opts.'id="qCellcreate_filter_'.$field_name.'_keyList"');
		$m_cell_opts .= ' '.$check_opts.' id="qCellcreate_filter_'.$field_name.'_keyCheck"';
            break;
	case 'date_past':
	case 'date_future':
	case 'date' :
		//must collapse two into one
		$d1=$_REQUEST[$form_name.'_start'];
		$d2=$_REQUEST[$form_name.'_end'];
		if ($d1 && $d2) {
			$default = new date_range($d1,$d2);
		} else {
			$default = dateof($d1,'SQL');
		}
		//$row .= formdate( $form_name,$default,"",true );
            $cell .= formdate_range( $form_name,$default,'',true );
            break;
	case 'staff' :
		$cell .= pick_staff_to( $form_name,false,$default );
		break;
	case 'boolean' :
		$cell .= formBoolean( $form_name,$default );
		break;
	default :
            $cell .= formvartext( $form_name,$default );
	}
	$group = cell(formcheck($form_name_group . '[' . $field_name . ']',in_array($field_name,$default_group)),$cell_opts);
	$row .= cell($cell,$m_cell_opts);
	return row( $row .$extra_cell. $group,$row_opts);
}

function select_object_to( $formname,$default='' )
{
	global $AG_ENGINE_TABLES,$engine;
	$choose_obj .= formto() 
		. oline('Choose Object Type: ' )
			  . selectto($formname);
	foreach ($AG_ENGINE_TABLES as $key)
	{
		$tmp[$key] = ucfirst($engine[$key]['plural']);
	}
	asort($tmp);
	foreach ($tmp as $key=>$plur) {
		$choose_obj .= selectitem($key,$plur." ($key)",($key==$default) ? "DEFAULT" : "");
	}
	return oline($choose_obj. selectend())
		. button('Go','','switch_obj_type')
		. button('Skip Search and show all records','','switch_obj_type')
		. formend();
}

function get_posted_filter($object)
{
	$def=get_def($object);
	$filter=array();
	$obj_fields=array_keys($def['fields']);
	//a mungy little hack to get date ranges working
	$REQUEST=$_REQUEST;
	$dates=array();
	foreach ($REQUEST as $key=>$value)
	{
		$tmpkey=$key;
		if (substr($tmpkey,-6)=='_start')
		{
			unset($REQUEST[$key]);
			$tmpkey=substr($tmpkey,0,strlen($tmpkey)-6);
			$dates[$tmpkey]['start']=$value;
		}
		elseif (substr($tmpkey,-4)=='_end')
		{
			unset($REQUEST[$key]);
			$tmpkey=substr($tmpkey,0,strlen($tmpkey)-4);
			$dates[$tmpkey]['end']=$value;
		}
	}
	foreach ($dates as $key=>$val_arr)
	{
		$keykey=substr($key,0,strlen($key)-6).'_key';
		if (!be_null($val_arr['start']))
		{
			$d1=$val_arr['start'];
			if ($REQUEST[$keykey]=='between')
			{
				$d2=orr($val_arr['end'],$d1);
				$newval=new date_range($d1,$d2);
				$REQUEST[$key]=$newval;
			}
			else
			{
				$REQUEST[$key]=$d1;
			}
		}
	}
	//end of mungy hacking
	foreach ($REQUEST as $key=>$value)
	{
		if ($key=="create_filter_any_all")
		{
			$any_all=$value;
		}
		if (!(preg_match('/^create_filter_(.*)_(key|value)$/',$key,$matches)))
		{
			continue;
		}
		$field=$matches[1];
		$piece=$matches[2];
		if (!in_array($field,$obj_fields)) {
			// Field not in object, discard
			continue;
		}
		if ($piece=="key")
		{
			$tmp[$field]["op"]=$value;
		}
		else
		{
			is_array($value) && ($value=array_keys($value)); //checkboxes from web form
			$tmp[$field]["value"]=$value;
		}
		$t=$tmp[$field];
		if ( $t["op"] && ($t["op"]<>"na"))
		{
			$filter[$field]=$tmp[$field];
		}
	}
	$filter=build_filter($filter,$type);
	if ($any_all=="or")
	{
		$filter = array( $filter );
	}
	return $filter;
}

function form_create_filter( $def, $grouping=false )
{
	$class="generalData";
	$out .= oline(
			bigger(bold('Choose search criteria for '.$def['singular'].' records.')),2)
			. tablestart('',' cellpadding="0" cellspacing="0" style="border: solid 1px black;"')
			. formto()
			. oline("I want to " . selectto("create_filter_any_all")
			. selectitem("and","Match All of the Following (logical AND)")
			. selectitem("or","Match Any of the Following (logical OR)")
			. selectend()
			. oline())
;
	$out .= query_row('header',' class="'.$class.'2"');
	foreach ($def["fields"] as $key=>$value)
	{
		if (!$def['fields'][$key]['virtual_field']) {
			$color = $c ? '2' : '1';
			$c = !$c;
			$value["field"]=$key; // field should be element in feild array
			$out .= query_row($value,' class="'.$class.$color.'"',' class="'.$class.$color.'"' );
		}
	}
	$out .= row(cell('',' colspan="4" class="'.$class.'2"'),' class="'.$class.'2"');

	global $obj_var,$type;
	$out .= row(cell(button("Search Now",'','','','',' style="font-size:125%; border:solid 1px black;"')
			     . anchor('groupingDisclaimer')
			     . oline() 
			     . div(red('*').' Grouping functionality is experimental. It may not work for some objects (It is known to not work with '.bold('Logs').'). Re-ordering by column will also not work when grouping on fields.','',' style="font-size: 85%;"')
			     ,' colspan="4" style="padding: 5px;"'),' class="'.$class.'2"')
		. hiddenvar("action","add") 
		. hiddenvar($obj_var,$type)			
		. formend() 
		. tableend();
	return $out;
}

global $colors;

$obj_var="query_object"; // move me into control array??
$fil_var="query_filter"; // or these session variables into existing processing routines.
$control_var='query_control'; //to avoid confusion with other session variables...for now
$notitle=true; //suppres the call_engine() title, we'll use our own for now, until this is part of engine

// grouping
$form_name_group="query_group";
is_array($group=$_REQUEST[$form_name_group]) && ($group=array_keys($group));
if ($group==array()) { unset($group); }
//to avoid confusion with variable namespace, I set $_SESSION variables to uppercase...
if (isset($_REQUEST[$fil_var]))
{
// FIXME: I think fil_var is unused
//	$_SESSION[strtoupper($fil_var)]=$_REQUEST[$fil_var];
}
$filter=$_SESSION[strtoupper($fil_var)];
if (isset($_REQUEST[$obj_var]))
{
      if (!($_REQUEST[$obj_var]===$_SESSION[strtoupper($obj_var)]))
      {  //A different object has been selected, blow out key SESSION vars
	    $_SESSION[strtoupper($control_var)]=array();
	    $filter=array();
      }
      $_SESSION[strtoupper($obj_var)]=$_REQUEST[$obj_var];
}

// adding support for 'show all'
if ( ($tmp = $_REQUEST['switch_obj_type']) and ($tmp != 'Go') ) {
	$_SESSION[strtoupper($control_var)]['list']['filter'] = $_SESSION[strtoupper($fil_var)] = $filter = array('FIELD:TRUE'=>'TRUE');
}

$type=orr($_SESSION[strtoupper($obj_var)],AG_MAIN_OBJECT_DB);
// Select object type here:
$object_select = select_object_to( $obj_var, $type );

//hack/fix for showing photo

if ($type) {
	$def=$engine[$type];
	$engine[$type]['list_control_openoffice_button']=true; 
	$sel_sql=$def["sel_sql"];

	if ($_REQUEST["action"]=="add") {
		$filter = get_posted_filter($type);
		$_SESSION[strtoupper($control_var)]['list']['filter']=$_SESSION[strtoupper($fil_var)]=$filter;
	}

	$tmp_control = array(
				   'object'=>$type,
				   'action'=>'list',
				   'page'=> $_SERVER['PHP_SELF'],
				   //                'anchor'=>$object,
				   'list' => array(
							 'filter' => $filter,
							 ));
      $$control_var = array_merge($tmp_control,
					    orr(unserialize_control($_SESSION[strtoupper($control_var)]),array()),
					    orr(unserialize_control($_REQUEST[$control_var]),array()) );

	//customX fix hack for photo
	if (in_array('customXobjects',orr(${$control_var}['list']['fields'],array()))) {
		$engine[$type]['fields']['customXobjects'] = $engine['staff']['fields']['staff_photo'];
	} elseif (in_array('customXobjectc',orr(${$control_var}['list']['fields'],array()))) {
		$engine[$type]['fields']['customXobjectc'] = $engine[AG_MAIN_OBJECT_DB]['fields']['custom5'];
	}

	//moved hack from engine_list.php
	// small, probably temporary hack for search results
	// if client_id exists in the record, but not in list fields,
	// and it's not in the filter (as a pure client_id=x)
	// , then add it to the list fields
	$rec_fields = $engine[$type]['fields'];
	$tmp_fields = $engine[$type]['list_fields'];
	$filter = orr($filter,array());
	foreach (array(AG_MAIN_OBJECT_DB.'_id','staff_id') as $key) {
		if (array_key_exists($key,$rec_fields) 
		    && (!in_array($key,$tmp_fields))
		    && (!array_key_exists($key,$filter)
			  || (is_array($filter[$key])))
		    && (!in_array($type,array('staff','client')))) {
			
			array_push($engine[$type]['list_fields'],$key);
			//get photo into results hack
			$xw = substr($key,0,1);
			if ($xw=='s') {
				$engine[$type]['fields']['customXobject'.$xw] = $engine['staff']['fields']['staff_photo'];
			} else {
				$engine[$type]['fields']['customXobject'.$xw] = $engine[AG_MAIN_OBJECT_DB]['fields']['custom5'];
			}
			array_push($engine[$type]['list_fields'],'customXobject'.$xw);
			//end photo hack
		}
	}
	
	$fields=orr(${$control_var}['list']['fields'],$engine[$type]['list_fields']);
	// end hack

	if (isset($fields)) {
		${$control_var}['list']['fields']=$fields;
	}
	if (is_array($group))
	{
		${$control_var}['list']['group']['fields']=$group;
		${$control_var}['list']['group']['values']=array('count(*)');
		${$control_var}['list']['fields']=array_merge(${$control_var}['list']['group']['fields'],array('count'));
	}
	if (${$control_var}['list']['filter']) {

	      $sql_f = bigger(bold("Searching the $database[$WHICH_DB] database using:"))
			. div(webify_sql(make_agency_query($sel_sql,$filter,null,null,null,$group ? ${$control_var}['list']['group'] : "")),'','class=" sqlCode"');
		$out .= div(toggle_label('sql details...') . $sql_f,'','class="hiddenDetail"');
		set_time_limit(120); //2 minutes enough?

		$results = oline(call_engine($$control_var,$control_var,$notitle,$no_messages=false,$tot,$perm));
		if (strstr($results,$engine['no_record_flag'])) {
		      $results = oline(bigger('No records found'),2);
		}
		$out .= oline($results);
	}
	// Construct filter here:
	$out .= form_create_filter($def);
} 

$title="AGENCY Advanced Search Page";

$js='function valueToggleDisplay(v_item,vval,displayOpt)
{
	var tog = false;
	var cval = v_item.options[v_item.selectedIndex].value;
	for (i in vval) {
		if (cval == vval[i]) {
			tog = true;
		}
	}
	for ( i=3; i<arguments.length; i += 2 ) {
		var t_s = document.getElementById(arguments[i]);
		var t_h = document.getElementById(arguments[i+1]);
		if (tog) {
			t_s.style.display=displayOpt;
			var t_in = t_h.getElementsByTagName(\'select\');
			for (j=0;j<t_in.length; j++) {
				t_in[j].disabled=true;
			}
			var t_in = t_s.getElementsByTagName(\'input\');
			for (j=0;j<t_in.length; j++) {
				t_in[j].disabled=false;
			}
			t_h.style.display="none";
		} else {
			t_s.style.display="none";
			var t_in = t_s.getElementsByTagName(\'input\');
			for (j=0;j<t_in.length; j++) {
				t_in[j].disabled=true;
			}
			var t_in = t_h.getElementsByTagName(\'select\');
			for (j=0;j<t_in.length; j++) {
				t_in[j].disabled=false;
			}
			t_h.style.display=displayOpt;
		}
	}
}';

$AG_HEAD_TAG .= Java_Engine::get_js($js)
     .style('
		tr.generalData1 select,tr.generalData2 select ,
		tr.generalData1 input,tr.generalData2 input { font-size: 75%;}
            #advQueryObjectPick {padding: 5px; background-color: '.$colors['pick'].'}
            #advQueryObjectPick input { border: solid 1px black; }
            #advQueryObjectPick input,#advQueryObjectPick select { font-size: 75%; margin: 3px;}
');

agency_top_header(array(cell(div($object_select,'advQueryObjectPick'))));

out( 
    oline(bigger(bold($title),2))
	. $out );
page_close();
?>
