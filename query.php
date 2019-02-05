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

// query.php
// functions for constructing queries

function quick_searches()
{
// provide other 3 quick search boxes, for chaser_top_header()
	global $colors;
	return array( 
		cell(log_query(),' class="log"'),
		cell(staff_query("","sidebar"),'class="staff"'),
		//temporarily hiding i&r searching until agency data is updated.
		/*	cell(ir_query("","sidebar"),' class="iandr"')*/);
}

function quick_searches_all()
{
	//provide all 4 quick searches in one box via JS
	//I am re-writing the qs functions so they all return a uniform format (like client_qs)

	//since this will be on all pages, the function already assumes that the appropriate JS is in the head tags...

	global $colors,$AG_QUICK_SEARCHES;

	$types = array_keys($AG_QUICK_SEARCHES);
	$current_type = orr($_REQUEST['QSType'],$types[0]);
	$current_type = array_key_exists($current_type,$AG_QUICK_SEARCHES) ? $current_type : $types[0];
	foreach ($AG_QUICK_SEARCHES as $type => $label) {
		$label = (!$label and $type==AG_MAIN_OBJECT_DB) ? ucfirst(AG_MAIN_OBJECT) : $label;
		$label = orr($label,ucfirst($type));
		$tabs .= div(hlink('#',$label,'','onclick="switchQuickSearch(\''.$type.'\'); document.getElementById(\'QuickSearchText\').value = (document.getElementById(\'QuickSearchText\').value==\''.AG_DEFAULT_QUICKSEARCH_TEXT.'\') ? \'\' : document.getElementById(\'QuickSearchText\').value; document.getElementById(\'QuickSearchText\').focus(); return false;" class="'.$type.'"')
				 ,'QuickSearch'.$type,' class="QuickSearchTab'.(($current_type==$type) ? '' : 'Inactive').'"');
	}
	global $off;
	$default = $_REQUEST['QuickSearch'];
	$form = formto($off.'search.php','','class="QuickSearchForm"')
		. formvartext('QuickSearch',orr($default,AG_DEFAULT_QUICKSEARCH_TEXT),
				  'id ="QuickSearchText" onclick="this.value = (this.value==\''.AG_DEFAULT_QUICKSEARCH_TEXT.'\') ? \'\' : this.value"')
		. button('Go!')
		. hiddenvar('QSType',$current_type,'id="QuickSearchType"')
		. hiddenvar('QuickSearchAutocompleteMinLength',AG_QUICKSEARCH_AUTOCOMPLETE_MIN_LENGTH,'id="QuickSearchAutocompleteMinLength"')
		. formend();

	$links = help('QuickSearch',null,'help',' class="fancyLink"',false,true) . ' | ' .hlink($GLOBALS['agency_search_url'],'advanced search');
	return div($tabs . div( $form . $links, 'QuickSearchBox',' class="'.$current_type.'"'), 'QuickSearch');

}

function send_quick_search_js()
{
	//send required js to head
	global $AG_HEAD_TAG,$AG_QUICK_SEARCHES;
	$types = array_keys($AG_QUICK_SEARCHES);
	foreach ($types as $type) {
		$searches[] = enquote1($type);
	}
	$searches = '['.implode(',',$searches).']';
	$current_type = 	$current_type = orr($_REQUEST['QSType'],$types[0]);
	$AG_HEAD_TAG .= Java_Engine::get_js('var whichQuickSearch=\''.$current_type.'\';'
							."\n".'var QuickSearches='.$searches.';');
}

function choose_search_type( $short="" )
{
// Short option for SQL pick lists, where
// only equals/doesn't equal will likely
// make sense.

	return( 
	selectitem( "na", "(not used in this search)" )
	. ($short ? 
	(selectitem( "equal", "Equals")
	. selectitem( "notequal", "does not Equal")
	)
	:
	(selectitem( "sub", "Contains" )
	. selectitem( "start", "Starts with" )
	. selectitem( "end", "Ends with" )
	. selectitem( "equal", "Equals")
	. selectitem( "notequal", "does not Equal")
	. selectitem( "greater", "is Greater than")
	. selectitem( "less", "is Less than")
	. selectitem( "greatereq", "is Greater than or equal to")
	. selectitem( "lesseq", "is Less than or equal to")
	)
	));
}

function search_type_to( $varname, $short="" )
{
	return( cell(
	selectto( $varname )
	. choose_search_type( $short )
	. selectend() ));
}

function search_row( $field_name, $field_label="", $hint="", $type="",$sql="" )
{
// returns a row for search with variables assigned to
// field_nameType and field_nameText
// I.E., pass a field_name of "Ethnicity",
// It will post variables of EthnicityType (search type)
// and EthnicityText (search text/value)

	$field_label = $field_label ? $field_label : $field_name;
	$row = cell( $field_label )
	. search_type_to( $field_name . "Type",($type=="sql") );

// (little hack--if type equals sql, search_type_to does short pick list)

	switch ($type)
	{
		case "sql" :
			$row .= cell(selectto( $field_name . "Text" )
				. do_pick_sql( $sql )
				. selectend());
			break;
		case "date" :
			$row .= cell( formdate( $field_name . "Text" ));
			break;
		default :
			$row .= cell( formvartext( $field_name . "Text" ));
	}
	return row( $row . cell( $hint ));
}

function build_where_string( $fields, $alias )
// This function builds the "WHERE" string to 
// be used in the query.
{
	foreach ($fields as $name)
	{
		$type=$name."Type";
		$text=$name."Text";
		global $$type, $$text;
//outline("Name: $name<br>  Text: $text: "
//. $$text ."<br>Type: $type: " . $$type);

		if (isset($$type) && isset ($$text) )
		{
			switch ($$type)
			{
				case "sub":
					$operator = "ILIKE";
					$$text = "%" . $$text . "%";
					break;
				case start:
					$operator = "ILIKE";
					$text = $text . "%";
					break;
				case "end":
					$operator = "ILIKE";
					$text = "%" . $text;
					break;
				case "equal":
					$operator = "=";
					break;
				case "notequal":
					$operator = "<>";
					break;
				case "greater":
					$operator = ">";
					break;
				case "less":
					$operator = "<";
					break;
				case "greatereq":
					$operator = ">=";
					break;
				case "lesseq":
					$operator = "<=";
					break;
				case "na":
				//	$operator = "1=1";
				//	$$text = "";
					$name = "";
					break;
				default:
					outline("Warning--ignoring unknown search type: " 
					. $$type );
					$name = "";
					break;
			}	
			if ($name)
			{
				// This if statement is all a hack to make staff search work again
				// after removing the name_last field from the staff table.
				// See bug 4715.
				if (($alias=="staff") && ($name=="name_full"))
				{
					$name = "staff.name_last || ', ' || staff.name_first";
				}
				else
				{
					$name = $alias . "." . $name;
				}
				$$text = "'" . sqlify($$text) . "'";
				$where .= "AND " . $name  . " " .  $operator . " " 
					. $$text . "\n";
			}
		}	
	}
	$where = substr($where,4,strlen($where)-4); // take off first "AND "
	return $where;
}		

function object_quick_search($object, $query_string='')
{
	$query_string = strip_tags(orr($query_string,$_REQUEST['QuickSearch']));

	// First do the object-specific search:

	$def=get_def($object);
	$filter=object_qs_filter($query_string,$object);
	$order=orr($def['quick_search']['list_order'],$def['list_order']);
	$control = array_merge(array( 'object'=> $object,
						'action'=>'list',
						'list'=>array(
							'filter'=>$filter,
							'order'=>$order,
						),
						'page'=>'display.php'
						),
				     orr($_REQUEST['control'],array()));
	$result = call_engine($control,'control',true,true,$TOTAL,$PERM);
	if (!$PERM) { return 'No Permissions'; }
	if ($TOTAL==1) {
			// Jump to view if only 1 result
			// FIXME: this is _way_ inefficient.  Already ran query and got formatted results
			// now will run query again to fetch ID and jump
			$rec=array_shift(get_generic($filter,NULL,1,$def));
			$id=$rec[$def['id_field']];
			$object_page=orr($def['quick_search']['jump_page'],'display.php');
			if ($object_page=='display.php') {
				$jump_url="display.php?control[object]=$object&control[action]=view&control[id]=$id";
			} else {
				$jump_url=$object_page . '?action=show&id='.$id;
			}
			header('Location: '.$jump_url);
			page_close($silent=true);
			exit;
	} elseif (($TOTAL==0) and preg_match('/^([a-z_]{3,}):([0-9]*)$/i',$query_string,$m)) {

		/*
		 * No records found, so try
		 * any global QS functions/shortcuts:
		 *
		 * Engine Object search of the form {obj}:xxxx
		 * where {obj} is the name of the object (or the
		 * first 3 or more characters of the name)
		 *
		 * (This was moved from being tried first so it wouldn't
		 * interfere with custom quick searches)
		 */

		if ($found = search_engine_object($m[1],$m[2])) {
			return $found;
		}
	}

	$sub = oline($TOTAL.' ' . $def['plural'] . ' matched your search for '.bold(webify($query_string)),2);
	return $sub . $result;

}

function object_qs_filter($qs_text,$object=AG_MAIN_OBJECT_DB)
{
	$qs_text=trim($qs_text);
	if (!$qs_text) {
		return false;
	}
	$def=get_def($object);
	switch( strtolower($object) ) {
/*
		// Custom handling should be doable & done via config file, but you
		// could do it here for something complex or funky
		case 'staff':
			$filter['ILIKE:staff_name(staff_id)']="%$qs_text%";
			break;
*/
		default :
			$qdef=$def['quick_search'];
			if  (is_numeric($qs_text) and ($mf=$qdef['match_fields_numeric'])) {
				foreach($mf as $m) {
					$filter[]=array($m=>$qs_text);
				}
			}
			elseif (dateof($qs_text) and ($mf=$qdef['match_fields_date'])) {
				foreach($mf as $m) {
					$filter[]=array($m=>dateof($qs_text,'SQL'));
				}
			} elseif  (ssn_of($qs_text) and ($mf=$qdef['match_fields_ssn'])) {
				foreach($mf as $m) {
					$filter[]=array($m=>ssn_of($qs_text));
				}
			} elseif ((!$filter) and (!be_null($qdef['match_fields_custom']))) {
					//Escaping of qs_text needs to be conditional, see below.
					//Otherwise, things can get double-escaped 
	//				$qs_text=sql_escape_string($qs_text);
					foreach ($qdef['match_fields_custom'] as $mc_k=>$mc_v) {
						// $x in custom field string will be replaced with qs_text

						if (preg_match($mc_k,$qs_text,$matches)) {
							// If custom match starts with field, the value will not be escaped by read_filter
							// So we have to do it ourselves here, to avoid SQL injection
							// If not, read_filter will take care of it.
							$qs_temp=preg_match('/^[!]?FIELD/i',key($mc_v)) ? sql_escape_string($qs_text) : $qs_text;

							$search=array('$x','$UID');
							$replace=array($qs_temp,$GLOBALS['UID']);
							for( $x=0; $x<count($matches); $x++) {								
								$search[]='$m'.$x;
								$replace[]=$matches[$x];

							}
							$filter[]=array(str_replace($search,$replace,key($mc_v))=>str_replace($search,$replace,$mc_v[key($mc_v)]));
							break;
						}
					}
			}
			if (!$filter) {
				$filter=array();
				$match_fields=orr($qdef['match_fields'],$def['list_fields']);
				foreach ($match_fields as $field) {
					//$filter['ILIKE:'.$field.'::text'] = '%'.$qs_text.'%'; //FIXME: Why doesn't read_filter handle this?
					$filter["ILIKE:TEXT($field)"] = '%'.$qs_text.'%';
				}
			}
			//Convert filter to OR
			if (count($filter)>1) {
				$filter = array($filter);
			}
			break; // End default case
	}
	return $filter;
}

function search_engine_object($obj,$id,$redirect=true)
{
	global $AG_ENGINE_TABLES,$engine;
	$len = strlen($obj);
	$found = array();
	foreach ($AG_ENGINE_TABLES as $o) {
		$t_o = substr($o,0,$len);
		if ($t_o === $obj) {
			$found[] = $o;
		}
	}
	if ( $redirect && (count($found) == 1) ) {
		$def =& $engine[$found[0]];
		$res = get_generic(array($def['id_field']=>$id),'','',$def);
		if (count($res) > 0) {
			header('Location: display.php?control[object]='.$found[0].'&control[action]=view&control[id]='.$id);
			page_close();
			exit;
		}
	} elseif (count($found) > 0) {
		foreach ($found as $o) {
			$def =& $engine[$o];
			$res = get_generic(array($def['id_field']=>$id),'','',$def);
			if (count($res) > 0) {
				$out .= oline(link_engine(array('object'=>$o,'id'=>$id),'View '.$o.' id #'.$id));
			}
		}
		return $out;
	}
	return 'No objects found for '.bold($obj.':'.$id);
}
?>
