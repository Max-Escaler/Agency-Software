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

//ALL LIST-ACTION RELATED FUNCTIONS HERE.

function list_generic($control,$def,$control_array_variable='',&$REC_NUM)
{
	/*
	 * Output formatted list based on a $control[list][filter] query.
	 */
      global $engine;
      $object=$def['object'];
      $table=$def['table'];
      
	$control=list_verify_control($control);

      foreach ($engine['control_array_elements']['list'] as $key) {

		/*
		 * set the following
		 * $fields
		 * $filter
		 * $limit
		 * $max
		 * $order
		 * $position
		 * $group
		 * $reverse -- for now, eleminating this option - JH
		 * $show_totals
		 */
		$$key=orr($control['list'][$key],$engine[$object]['list_'.$key]);
		$control['list'][$key]=$$key;

      }
	  /* if custom SQL, and it changes, blow out order.  Otherwise old order, like from Advanced Search, can linger on
		* and mess things up.  Partly addresses fixme below.
		*/
	  if ($sql and ($sql != $_SESSION['open_query_sql'])) {
			$order=NULL;
	  }
	  $_SESSION['open_query_sql']=$sql;

      if (!$order && !be_null($fields)) {

		$order[$fields[0]] = in_array($def['fields'][$fields[0]]['data_type'],
							array('date','timestamp','date_past','date_future','timestamp_past','timestamp_future'));

	}

	if (is_array($order)) {

		foreach($order as $field=>$value) {

			if ($def['fields'][$field]['virtual_field'] and (!$def['fields'][$field]['view_field_only'])) {

				/*
				 * CAN'T ORDER BY FIELDS NOT IN TABLE
				 */
				unset($order[$field]);

			} elseif ($true_order=$def['fields'][$field]['order_by_instead']) {

				unset($order[$field]); //for things like client_name...
				$order[$true_order]=$value;

			}

		}

	}

      $control['list']['order']=$order;

	if ($sql = $control['sql']) {

		/*
		 * Begin Generic SQL Query handling
		 *
		 * Known Security Holes:
		 *
		 * 1) is_safe_sql overrides 'Must start query with SELECT' rule here (for use w/ multiple queries - create temporary table etc)
		 * 2) There is no check for function usage (some functions actually change or insert data)
		 * 3) the is_safe_sql function probably isn't perfect
		 *
		 * fixme: Known Bugs:
		 *
		 * *) re-ordering will fail on any column name with spaces in it
		 * *) sql in title doesn't change depending on order
		 *
		 *
		 */
		if ($control['sql_security_override'] || is_safe_sql($sql,$message,false)) {

			/* 
			 * good sql
			 */

		} else {

			return $message;

		}
		
		if (!is_array($sql)) {

 			$sql = array($sql);

		}

		// replace "--" style comments with /* */ style
		$sql = preg_replace("/(\s*)--(.*)\n/","$1/* $2 */\n",$sql);

		// replace line breaks w/ white-space
		$control['sql'] = $sql = str_replace(array("\n","\r","\t"),' ',$sql);

		$sql_count = count($sql) - 1;
		//process all queries but the last one
		for ($i=0; $i<$sql_count; $i++) {
			sql_query($sql[$i]);
		}

		if ($order) {
			// If user specifies order (clicks on column header),
			// Wrap query into subquery so that order will be handled correctly
			// This might inefficiently force two sortings, but it's clean
			$sql[$sql_count] = 'SELECT * FROM (' . str_replace(';','',$sql[$sql_count]) . ') AS generic_sql_query_temp;';
			$control[$sql]=$sql;
		}

		//process final (or only) query
		$result = list_query($sql[$sql_count],'',$order,$control,$group);

		// construct $def
		$def = config_generic_sql($def,$result);
		$control['list']['fields'] = $fields = orr($fields,$def['list_fields']);
		$control['list']['order'] = $order = orr($order,$def['list_order'],array());
		$generic_sql=true;
		/*
		 * End Generic SQL Query handling
		 */

	} else {

		$result = list_query($def,$filter,$order,$control);

	}

	if ($result) {
		$REC_NUM = $total = sql_num_rows($result);
	}

 	if (($position > $total) && ($total > 0)) {

		/*
		 * this prevents the weird buggy behavior when switching from a client with more
		 * records to a client with less records...
		 */
 		$control['list']['position']=$position=0;

 	}

	//if ($control['list']['display_add_link'] or ($total<1)) {
	if ($def['allow_add'] and (!$generic_sql) and ($control['list']['display_add_link'] or (($filter==array()) and has_perm('admin')))) {
		$add_link = link_engine(array('object'=>$object,'action'=>'add'),'Add ' . aan($def['singular']) .' '.$def['singular']);
	}

      if ($total < 1) {
	    return $add_link . html_comment($engine['no_record_flag']);
      }

	if ($max == -1) {
		/*
		 * show all records on a page
		 */
		$control['list']['max'] = $max = $total;
	}

	switch ($control['format']) {

	case 'data':
		$function = $engine['functions']['generate_list'];
		break;
	case 'medium':
		$function = $def['fn']['generate_list_medium'];
		break;
	case 'long':
		$function = $def['fn']['generate_list_long'];
		break;
	default:
		$function = $def['fn']['generate_list'];

	}

      $out = $add_link 
		. $function($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,$rec_num);

      return $out;

}

function generate_list_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	/*
	 * Return the core of the list box, including header, query results and footer
	 */

	$totals = show_list_totals($result,$total,$def,$control,$fields);
	$header= $control['list']['no_controls']  
		? tablestart() 
		: list_header($fields,$max,$position,$total,$control,$def,$control_array_variable); 
	return  $header 
		. $totals
		//. show_query($fields,$result,$control,$def,$total,$control_array_variable,&$REC_NUM)
		. show_query($fields,$result,$control,$def,$total,$control_array_variable,$rec_num)
		. $totals
		. list_footer($fields,$max,$position,$total,$control,$control_array_variable);

}

function generate_list_medium_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{

	/*
	 * place-holder function for custom medium-format lists
	 */
	return generate_list_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,$rec_num);

}

function generate_list_long_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{

	/*
	 * place-holder function for custom long-format lists
	 */
	return generate_list_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,$rec_num);

}

function list_header($fields,$max,$position,$total,$control,$def,$control_array_variable)
{
	/*
	 * Return list header, which consists of an html table start tag, and a row or two, including 
	 * column headers and list-formatting options.
	 */

      $header_options = 'class="listHeader"';
	$columns        = $control['list']['columns'];

	/*
	 * total columns, plus 2 (one for view link, one for row numbering)
	 */
      $cols           = (count($fields)+2) * $columns;

	/*
	 * Start the header output, table is closed in list_footer()
	 */
      $header = tablestart_blank('','cellpadding="0" cellspacing="0" class="listMain listObject'.ucfirst($def['object']).'"');

	if ($total>1) {

		if (!$control['list']['no_form']) {

			/*
			 * append the list control box
			 */
			$header .= row(cell(list_control($control,$def,$control_array_variable,'SMALL'),
						  "colspan=\"$cols\""));

		}

		/*
		 * List summary and navigation
		 */
		$total_records_text = list_total_records_text($control,$position,$total,$max,$def);
		$nav_links = list_links($max,$position,$total,$control,$control_array_variable);

		/*
		 * List summary is in 2 columns. $cols_a contains $total_records_list and
		 * $cols_b contains $nav_links
		 */
		$cols_a = ceil($cols/2)+1;
		$cols_b = $cols-$cols_a;
		if ($cols_b == 0) {
			/*
			 * In the case of very narrow result sets, reset so there is at least one column/column-span
			 * for both a and b, otherwise, the row spans more columns than there are in the table, 
			 * creating a very funny looking list box.
			 */
			$cols_a --;
			$cols_b ++;
		}
		$header .= row(cell(left($total_records_text),"colspan=\"$cols_a\"") . cell(right($nav_links),"colspan=\"$cols_b\""),$header_options);

	} elseif ($total==1) {

		/*
		 * List box with only 1 result row has a reduced header
		 */

		$header .=  ($control['list']['no_form'])
			? '':
			row(cell(list_control($control,$def,$control_array_variable,'MICRO'),
				   "colspan=\"$cols\""));
	}

      return $header;

}

function list_footer($fields,$max,$position,$total,$control,$control_array_variable)
{
	/*
	 * Return list footer, including html table close tag for table started in list_header()
	 */

	if ($total>1) {

		/*
		 * Only lists with more than one row get a footer
		 */

		$footer_options = 'class="listHeader"';
		$columns = $control['list']['columns'];

		/*
		 * Column span is the same as in list header
		 */
		$cols = (count($fields)+2) * $columns;

		/*
		 * Include same list navigation links as in header
		 */
		$nav_links = list_links($max,$position,$total,$control,$control_array_variable);

		$footer = row(cell(right($nav_links),"colspan=\"$cols\""),$footer_options);

	}

	$footer .= tableend();

      return $footer;
}

function show_query($fields,$result,$control,$def,$total,$control_array_variable,&$REC_NUM)
{
	/*
	 * display main part of the list box, the query results
	 */

//       $reverse = $control['list']['reverse'];

      $position   = $control['list']['position'];
      $max        = $control['list']['max'];
      $columns    = $control['list']['columns'];
      $horizontal = $control['list']['horizontal'];

	/*
	 * Output is done in an array, and then imploded
	 */
      $out = array();

	/*
	 * Determine whether to use custom function, or the generic function. If
	 * the fields being displayed are the same as those defined in the $def array,
	 * then the custom function (which may or may not be customized) is safe.
	 * Otherwise, the show_query_row_generic function must be used.
	 */
	$which_fn = ($fields==$def['list_fields']); 
	$fn = ($which_fn) ? $def['fn']['show_query_row'] : 'show_query_row_generic';

      while ($x<$max && $position<$total) {

		/*
		 * Cycle through records, get row content and add to the output array
		 */

		$a = sql_fetch_assoc($result,$position);
		$a = sql_to_php_generic($a,$def);

		$row_content = $fn($fields,$position,$a,$control,$def,$control_array_variable,$REC_NUM);
		$row_data=array(
			
			'objectPickerObject'=>$def['object'],
			'objectPickerId'=>$a[$def['id_field']],
			'objectPickerMethod'=>'SearchResult',
			'objectPickerLabel'=>object_label($def['object'],$a[$def['id_field']]));
        $row_content = 	$row_content
			.cell(''
			. hiddenvar('objectPickerObject',$def['object'])
			. hiddenvar('objectPickerId',$a[$def['id_field']])
			. hiddenvar('objectPickerMethod','SearchResult')
			. hiddenvar('objectPickerLabel',htmlspecialchars(object_label($def['object'],$a[$def['id_field']])))
			. json_encode($row_data)
			,'class="hidden"');

		if ($reverse) {
			//pre-pend result set
			array_unshift($out,$row_content);
		} else {
			//append result set
			array_push($out,$row_content);
		}

		$position++;
		$x++;

      }

      $order = $control['list']['order'];
	if (!is_array($order)) {
		$order = array();
	}

      /*
	 * Generate column labels, one set for each set of $columns * count($fields)
	 */
      $x = 0;
      while ( $x<$columns ){

		$column_labels .= cell('','class="listSubHeader"')
			. ($def['list_hide_numbers'] 
			   ? cell('','class="listSubHeader"') 
			   : cell(smaller('#'),'class="listSubHeader"'));

		foreach($fields as $field) {

			$new_order   = $order;
			$order_field = orr($def['fields'][$field]['order_by_instead'],$field);
			$new_order[$field] = !$order[$order_field]; //opposite order of current order
			$control['list']['order']=array_order($new_order,$order_field);
			
			/*
			 * Determine which direction arrow to use
			 */
			$arrow = isset($order[$order_field]) ? 
				' class="listOrder'.($order[$order_field] ? 'Up' : 'Down').'"'
				: '';
			
			/*
			 * Create the label.
			 * Fixme: this bold() call should simply be the default
			 *        for format_list as defined in config_engine.php
			 */
			$label = bold(label_generic($field,$def,'list'));
			
			/*
			 * Add field comments to label, if defined and configured to display for this action
			 */
			if (($tmp = $def['fields'][$field]['comment']) && $def['fields'][$field]['comment_show_list']) {
				$comment = div(webify($tmp),'',' class="generalComment"');
			} else {
				$comment = '';
			}
			
			if ($x == 0) {
				/*
				 * On the first set of labels, so they are re-order links
				 */

				if ($def['fields'][$field]['virtual_field'] and (!$def['fields'][$field]['view_field_only'])) {

					/*
					 * Virtual fields can't be ordered on, so the labels are simple text
					 */
					$link = blue($label).$comment;

				} else {

					$link = alt(link_engine($control,$label,$control_array_variable,$arrow),
							'Click to sort on this column').$comment;

				}

				$column_label_contents = table(row(cell($link,'style="white-space: nowrap;"')), //don't let the labels wrap around
									 '','class="listSubHeader"');

			} else {
				
				/*
				 * simple text label for the 2nd, 3rd, etc column sets
				 */
				$column_label_contents = blue($label).$comment;
				
			}
			
			$column_labels .= cell($column_label_contents);

		}

	    $x++;

      }

      if ($horizontal==='t') {

		/*
		 * Only applicable if columns is > than 1, horizontal orders records 1,2,3,4 as
		 *
		 *  +---+---+
		 *  | 1 | 2 |
		 *  | 3 | 4 |
		 *  +---+---+
		 *
		 */

		$output=row($column_labels,' class="listSubHeader"');

		for ($i=0;$i<$max;$i+=$columns) {

			$j=0;
			while ($j<$columns) {

				$out_cell .= $out[$i+$j];
				$j++;

			}

			$wclass = $wclass=='2' ? '1' : '2';
			$output .= row($out_cell, 'class="generalData'.$wclass.'"');
			unset($out_cell);

		}

      } else {

		/*
		 * Default order, orders records down the first column, then the second, so records 1,2,3,4 would look like:
		 *
		 *  +---+---+
		 *  | 1 | 3 |
		 *  | 2 | 4 |
		 *  +---+---+
		 */

		$output=row($column_labels,' class="listSubHeader"');
		$recs_per_col=ceil(min($max,$total)/$columns);

		for ($i=0;$i<$recs_per_col;$i++) {

			$j=0;
			while ($j<$columns) {

				$dummy_cells = list_dummy_cells($fields);
				$out_cell .= orr($out[$i+($j*$recs_per_col)],$dummy_cells);
				$j++;

			}

			$wclass = $wclass=='2' ? '1' : '2';
			$output .= row($out_cell, 'class="generalData'.$wclass.'"');
			unset($out_cell);

		}

      }

      return $output;

}

function list_dummy_cells($fields)
{
	/*
	 * Generate empty cells to fill in empty rows
	 */

	$c = count($fields)+2;
	$i = 0;

	while ($i < $c) {

		$out .= cell('','class="generalData1"');
		$i ++;

	}

	return $out;

}

function show_query_row_generic($fields,$position,$rec,$control,$def,$control_array_variable='',&$REC_NUM)
{

	/*
	 * Return a row to be placed in a list box
	 *
	 * If writing a generic function to mimic this, 
	 * it must return count($fields)+2 cells as a string
	 * keeping in mind that the column labels will be for 
	 * the fields passed in the $fields variable
	 */

	if ($def['fn']['engine_record_perm']($control,$rec,$def)) {

// 		$reverse = $control['list']['reverse'];
		$MAX=$control['list']['position']+$control['list']['max'];
		$count = $reverse 
			? ($MAX-$position)+$control['list']['position']
			: $position+1;
		$control['list']['position']=$position;
		$out = $def['list_hide_view_links'] 
			? cell('', 'width="2" class="generalData1"') //hide view links
			: cell(center(list_view_link($rec,$control,$def)),'width="20" class="generalDataHeader"');
		$out .= ($def['list_hide_numbers']) ? cell('','class="generalData1"') : cell(smaller($count),'class="generalData1"');

		foreach($fields as $key) {

			//EVALUATE value
			$x_raw=$x=$rec[$key];
			$value = $def['fields'][$key] 
				? eval('return '.$def['fields'][$key]['value_list'].';')
				: $x;
			$v_f=value_generic($value,$def,$key,'list',true,$rec);
			while (preg_match('/^(.*?)link_engine\((.*?)[|]{2}(.*?)[|]{2}(.*?)[|]{2}(.*?)([|]{2}(.*?))?\)(.*)$/',$v_f,$matches)) {
				$l_rec_init=array();
				$l_obj=$matches[2];
				$l_id=$matches[3];
				$l_label=($matches[4]=='NULL') ? '' : webify($matches[4]);
				$l_action=$matches[5];
				if ($matches[7]) {
					$l_tmp=explode(',',$matches[7]);
					for ($x=0;$x<count($l_tmp);$x=$x+2) {
						$l_rec_init[$l_tmp[$x]]=$l_tmp[$x+1];
					}
				}
				$l_con = array('action'=>$l_action,'object'=>$l_obj,'id'=>$l_id,'rec_init'=>$l_rec_init);
				$l_link = link_engine($l_con,$l_label,'','target="_blank"');
				$v_f = $matches[1] . $l_link . $matches[8];
			}

			$out .= cell($v_f,'class="generalData1"');

		}

	} elseif ($def['hide_no_perm_records']) {

		/*
		 * If rows without permissions are to be hidden, the total record count is reduced
		 */
		$REC_NUM --;

	} else {

		/*
		 * User is notified that data exists, they simply don't have permissions for this specific record
		 */
		$num = count($fields);
		$out = cell().cell().cell('You don\'t have permission to view this record',' colspan="'.$num.'"');

	}

      return $out;

}

function list_view_link($rec,$control,$def,$id_field='',$label='',$control_array_variable='')
{
	/*
	 * Return a view link to the specific record
	 */
      $control['action']='view';
	$control['format']='';
      $control['id']=orr($rec[$id_field],$rec[$def['id_field']]);

      return smaller(link_engine($control,orr($label,'View'),orr($control_array_variable,'control')),2);

}

function list_total_records_text($control,$position,$total,$max,$def)
{
	/*
	 * Return text summarizing list box (total records, sort order)
	 */
	$first_rec = $position+1;
	$last_rec  = min($total,$position+$max);

	$order=$control['list']['order'];
	if (is_array($order)) {
		$order_keys=array_keys($order);
		$ORDER = $order_keys[0];
		$sort_order = label_generic($ORDER,$def,'list');
	} else {
		$sort_order = $order;
	}
	$text = ($last_rec==$total) ? "All $total records"
		: "Records $first_rec-$last_rec (of $total total records)";
	$sort_text = $sort_order ? ', ' . span('Sorted by '.$sort_order,' class="listOrder'.($order[$ORDER] ? 'Up' : 'Down').'"'): '';
	return smaller($text . $sort_text);
}

function list_links($max,$position,$total,$control,$control_array_variable)
{
	/*
	 * Return links for navigating between pages in a list box.
	 */
      $num_pages = ceil($total / $max);
      $current_page = floor($position/ $max) +1;
      $current_page = floor(($position+$max-1)/ $max) +1;
      $jump_links=array();
      for ($x=0;$x<$num_pages;$x++)
      {
	    $page=$x+1;
	    $new_position = $x * $max;
	    $control['list']['max']=$max;
	    $control['list']['position']=$new_position;
	    array_push($jump_links, ( ($page==$current_page)
		       ? dead_link($page) 
		       : link_engine($control,$page,$control_array_variable)).' ');
	    if (($current_page-1)==$page) // previous page link
	    {
		  $prev_link=link_engine($control,bold('Previous'),$control_array_variable);
	    }
	    if (($current_page+1)==$page) // next page link
	    {
		  $next_link=link_engine($control,bold('Next'),$control_array_variable);
	    }
      }

      $JUMP = list_link_format($jump_links,$current_page);
      //$links = smaller(bold("Page $current_page of $num_pages"). ($num_pages>1 
      $links = ($num_pages>1) ? smaller($prev_link,2).smaller( ' ' . bold("Page $current_page of $num_pages")
			. " $next_link $JUMP") : '';

      return $links;

}

function list_link_format($links_array,$current_page)
{
	/*
	 * Return links to specific-numbered pages of the list box. Currently configured
	 * to display 11 numbered links, the current page being in the middle, with
	 * 4 pages extending out in either direction, plus the first and last page
	 */

      $num_links=11;

      $num_pages=count($links_array);
      $start=$current_page-floor($num_links/2);
      $start = ($start<0) ? 0 : $start;
      $start = ($num_links+$start-1)>$num_pages ? $num_pages-$num_links+1 : $start;

      if ($start>0) {

		$formatted_links.=$links_array[0];

      }

      if ($start>1) {

		$formatted_links .= ' . . . ';

      }

      for ($j=0;$j<(2*floor($num_links/2))-1;$j++) {

		$formatted_links .= $links_array[$start+$j];

      }

      if (($num_pages-($start+$num_links-1))>0) {

		$formatted_links .= ' . . . '.$links_array[$num_pages-1];

      } elseif (($num_pages-($start+$num_links-1))==0) {

		$formatted_links.=$links_array[$num_pages-1];

      }

      return $formatted_links;
}

function list_query($def,$filter='',$order='',$control,$group='')
{
	/*
	 * Perform the actual query for the data going into the list box
	 */

	$order = list_query_order($order);
	$group = $control['list']['group'];
	$limit = $control['list']['limit'];

	if (is_array($def)) {

		$fn = $group ? 'get_generic' : $def['fn']['get'];
		return $fn($filter,$order,$limit,$def,false,$group,true); // returns query results, not array

	} else { // a raw query is being passed in the $def variable

		return agency_query($def,'',$order,$limit);

	}

}

function list_query_order($order)
{
	/*
	 * Construct the ORDER BY portion of the query
	 */

      if (is_array($order)) {

		$new_order = array();

		foreach($order as $field=>$desc) {

			if (!be_null($field)) {

				$new_order[$field]=($desc) ? $field . ' DESC' : $field;

			}

		}

		$order = implode($new_order,',');

      }

	return $order;

}

function list_query_string($def,$control)
{
	/*
	 * Convert query arrays into a raw string that can be
	 * passed directly to an open office template query
	 */

	$filter = $control['list']['filter'];
	$order = list_query_order($control['list']['order']);

	if ($sql = $control['sql']) {

		$select = implode('; ',$sql);
		unset($filter);

	} else {

		$select = $def['sel_sql'];
		if (preg_match('/^\s*SELECT\s*\*\s*FROM\s*'.$def['table'].'\s*$/i',$select)) {

			// add main-object name if relevant, and staff name
			$fields = array();

			foreach ($def['fields'] as $f => $pr) {

				if (is_field($def['table'],$f)) {

					$fields[] = $f;
					if ($obi = $pr['order_by_instead']) {

						$fields[] = $obi.' AS '.$f.'_name';

					}

				}

			}

			$select = 'SELECT '.implode(', ',$fields).' FROM '.$def['table'];

		}

	}

      $sql = make_agency_query($select,$filter,$order);

	return $sql;
}

function list_control($control,$def,$control_array_variable='control',$format='SMALL')
{
	/*
	 * Return an html form for controlling list display and format
	 */

      global $engine;

      $page = orr($control['page'],$_SERVER['PHP_SELF']);
      $anchor = $control['anchor'] ? '#'.$control['anchor'] : '';
      $object=$control['object'];
      $list_control_elements=$engine['list_control_elements'];
	  $action='list';

      foreach($list_control_elements as $key) {

	    $$key=orr($control['list'][$key],$engine[$object]['list_'.$key]);
	    unset($control['list'][$key]);

      }

      $pass_control .= form_encode($control,$control_array_variable);
	//-----Advanced controls-----//
	$ac_cols = 6;
	$x=0;
	$all_fields = $def['fields'];
	foreach ($all_fields as $fname=>$fconfig) {
		if ( !be_null(($q=$fconfig["display_eval_$action"])) ) {
			$disp = eval( 'return ' . $q . ';' );
		} else {
	    	$disp = $fconfig["display_$action"];
		}
		if ( (!has_perm('super_user')) && ($fconfig['never_to_form'] || ($disp=='hide')) ) {
			continue;
		}
		$x++;
		$tmp_row .= cell(form_valcheck($control_array_variable.'[list][fields][]',$fname,in_array($fname,$fields)))
			. cell(smaller(white($fconfig['label_list']),3),' class="listControlAdvanced"');
		if ($x==$ac_cols) {
			$x=0;
			$advanced_control_form .= row($tmp_row);
			$tmp_row = null;
		}
	}
	$advanced_control_form .= $tmp_row ? row($tmp_row) : null;

	/*
	 * Must have 'view' and 'list' permissions for advanced control
	 */

	$advanced_control_form = table(((engine_perm($control,'R') and has_perm($def['perm_view'],'R')) ? $advanced_control_form : '')
						 . row(cell(formBoolean($control_array_variable.'[list][horizontal]',$horizontal,'f',imgTableVert(),'t',imgTableHorz()),'class="listControl"'))
						 ,'',' width="100%" style="padding-top: 0px;" cellpadding="0" cellspacing="0" class="listControl"');

// 	$reverse = $control['list']['reverse'];

	$new_control = $control;
	$new_control['page'] = $new_control['anchor'] = null;
	$display_link = $control_array_variable=='control' ? '' 
		: oline() . link_engine($new_control,'View list on separate page','',' class="fancyLink"');
	//----End advanced controls----//
      switch ($format) {
      case 'MICRO' :
		if ($ac_button) {
			/*
			 * 3rd argument should be 'table-row', but blank works just as well for compliant browsers, 
			 * and it also works for IE (while 'table-row' does not)
			 */
			$ac_button = Java_Engine::toggle_id_display('Advanced +/-',$def['object'].'AdvancedControl','');

			$out = formto($page . $anchor)
				. $pass_control
				. table(row(cell($ac_button,' colspan="2" class="listControl" style="text-align: right;"'),'class="listControl"')
					  .row(cell(button('Re-Display!','','','','','class="listControl"'),'class="listControl"')
						 .cell($advanced_control_form,'class="listControl"')
						 ,'class="listControl" id="'.$def['object'].'AdvancedControl'.'" style="display:none;"')
					  ,'','cellspacing="0" cellpadding="0" width="100%" class="listControl"')
				
				. formend();
		}
		return $out;
	case 'CUSTOM': //this is for long-format style, so columns and fields don't make sense
		$out .= tablestart('','cellspacing="0" cellpadding="0" width="100%" class="listControl"');
		$out .= formto($page . $anchor);
		$out .= row(cell('# Per Page ' 
				     .' '. form_field('number',$control_array_variable.'[list][max]',$max,'size="3" class="listControl"'). $display_link,'class="listControl"'
				     )
// 				. cell('Reverse'
// 					 .' '. form_field('boolcheck',$control_array_variable.'[list][reverse]',$reverse),'class="listControl"')
				. cell(button('Re-Display!','','','','','class="listControl"'),
					 'class="listControl" style="text-align: right;"'),'class="listControl"')
			. $pass_control
			. formend()
			. tableend();
		return $out;
		break;
      case 'SMALL' :
      default :

		$oobutton = (AG_OPEN_OFFICE_ENABLE_EXPORT 
				 && $def['list_control_openoffice_button'] 
				 && has_perm('generic_oo_export'))
			? cell(open_office_button($control,$def,'','class="listControl"'),' class="listControl"') 
			: '';
	
	     $adv = formto($page . $anchor);
		 $adv .= tablestart('','cellspacing="0" cellpadding="0" width="100%" class=""');
		 $adv .= row(cell('# Per Page ' 
					.' '. form_field('number',$control_array_variable.'[list][max]',$max,'size="3" class="listControl"'). $display_link,'class="listControl"'
					)
				  			    . cell('Reverse'
				  				     .' '. form_field('boolcheck',$control_array_variable.'[list][reverse]',$reverse),'class="listControl"')
				 . cell('Totals'
					  .' '. form_field('boolcheck',$control_array_variable.'[list][show_totals]',$show_totals),'class="listControl"')
				 . cell('Columns'
					  .' '. form_field('number',$control_array_variable.'[list][columns]',$columns,'size="3" maxlength="1" class="listControl"')
					  ,'class="listControl"')
				 . cell(button('Re-Display!','','','','','class="listControl"')
					  . ($ac_button ? oline().$ac_button : ''),'class="listControl" style="text-align: right;"'),"class=\"listControl\"")
			 . $pass_control
			 . tableend()
			 //. Java_Engine::hide_show_content($advanced_control_form,$def['object'].'AdvancedControl',true)
			 . $advanced_control_form
			 . formend();
		 $out = table(row($oobutton.cell(div($adv . toggle_label('Advanced'),'','class="hiddenDetail"'))),'','width="100%" cellpadding="0" cellspacing="0" class="listControl"');
		 //$out = table(row($oobutton.cell($adv)),'','width="100%" cellpadding="0" cellspacing="0" class="listControl"');
		 return $out;
      }
}

function child_list( $object, $id, $page='', $PARENT='', $ID_FIELD='', &$js_hide)
{
      global $engine;

      $PARENT=orr($PARENT,AG_MAIN_OBJECT_DB);
      $NO_ENGINE_TITLE=true; //ENGINE TITLES WILL BE SURPRESSED
      $page = orr($page,$_SERVER['PHP_SELF']);

      // CONSTRUCT FILTER
      $ID_FIELD=orr($ID_FIELD,$engine[$PARENT]['id_field']);
	  $filter = array($ID_FIELD => $id );
      // CONSTRUCT CONTROL ARRAY
      $tmp_control = array(
				'object'=>$object,
				'action'=>'list',
				'page'=> $page,
				'anchor'=>$object,
				'list' => array(
						'filter' => $filter
						)
				);
	  // This is a terrible hack, for referenced fields...
	  $def=get_def($object);
	  if (!in_array($ID_FIELD,array_keys($def['fields']))) {
		$tmp_control['list']['filter_ref']=object_reference_filter_wrap($PARENT,$id,$ID_FIELD,'both',$object);
	  }

	$js_hide = ($_REQUEST[$var_name]['object']==$object) ? false : $js_hide; //don't hide the object being worked on

      $control = array_merge($tmp_control,
				     orr($_SESSION[strtoupper($var_name)],array()),
				     orr(unserialize_control($_REQUEST[$var_name]),array()) );
      $control['list']['filter']=$filter; // BLOW OUT OLD FILTER
      $control['page']=$page;             // BLOW OUT OLD PAGE
      $control['id']=$id;
	return engine_java_wrapper($control,$var_name,$js_hide);
}

function engine_java_wrapper($tmp_control,$var_name=null,&$js_hide,$title=null,$js_ident=null)
{
	global $engine;

      $NO_ENGINE_TITLE=true; //ENGINE TITLES WILL BE SURPRESSED

	$object = $tmp_control['object'];
	$filter = $tmp_control['list']['filter'];
      $var_name = orr($var_name,'control_'.$object);
	$id = $tmp_control['id'];
      $page = orr($tmp_control['page'],$_SERVER['PHP_SELF']);

	$js_hide = (orr($_REQUEST[$var_name]['object'],$_REQUEST['control']['object'])==$object) ? false : $js_hide; //don't hide the object being worked on

      $control = array_merge($tmp_control,
				     orr($_SESSION[strtoupper($var_name)],array()),
				     unserialize_control(orr($_REQUEST[$var_name],array())) );
      $control['list']['filter']=orr($tmp_control['list']['filter_ref'],$filter); // BLOW OUT OLD FILTER
      $control['page']=$page;             // BLOW OUT OLD PAGE

      // GET RECORDS
	$OUTPUT= call_engine($control,$var_name,$NO_ENGINE_TITLE,false,$total,$PERMISSION);

      //ADD RECORD LINK
	$add_link_control = $tmp_control;
	$add_link_control['page'] = null;
	$add_link = child_list_add_link($engine[$object],$filter,$add_link_control);

      // OPTIONAL SUBTITLE HTML
      $sub_title = $engine[$object]['subtitle_html'];

	// Absolute add link HTML (displays regardless of records or not)
	$abs_add_link = $engine[$object]['add_link_absolute_html'];
	if ($tmp_code = $engine[$object]['add_link_absolute_eval']) {
		$abs_add_link .= eval('return '.$tmp_code.';');
	}

      // OPTIONAL SUBTITLE eval code
	$sub_eval_code = $engine[$object]['subtitle_eval_code'];
	if (is_array($sub_eval_code)) {
		$sub_code_array=null;
		foreach ($sub_eval_code as $code_bit) {
			$sub_code_array .= cell(eval('return '.$code_bit.';'));
		}
	} else {
		$sub_code = eval('return '.$sub_eval_code.';');
	}

	// page footer html
	global $AG_PAGE_FOOTER;
	$AG_PAGE_FOOTER .= $engine[$object]['page_footer_html'];

	// hidden html
	$hidden_html = $engine[$object]['hidden_html_absolute'];
	if ($code = $engine[$object]['hidden_eval_absolute']) {
		if (is_array($code)) {
			foreach ($code as $code_bit) {
				$hidden_html .= eval('return '.$code_bit.';');
			}
		} else {
			$hidden_html .= eval('return '.$code.';');
		}
	}

      // TITLE
	$singular=$engine[$object]['singular'];
 	$plural=$engine[$object]['plural'];

      $no_title = section_title('No '.ucwords($plural));
	$no_see_title = section_title('You don\'t have appropriate permissions for '.$singular.' records');
      $title = section_title(orr($title,$total.' '.ucwords($total > 1 ? $plural : $singular)));

	if ( ($total<1) || !$PERMISSION) {
		return anchor($object) 
			. $hidden_html
			. div(span( ($PERMISSION ? $no_title : $no_see_title) 
					. preg_replace('/^\s\(\)$/','', //get rid of empty ()
							   ' ('.($engine[$object]['add_link_show'] ? $add_link : '').
							   ($abs_add_link ? ' '.$abs_add_link : '').')')
					.' '.trim($sub_code.$sub_code_array.$sub_title),' class="childListTitle'.($PERMISSION ? 'Null' : 'Deny').'"')
				,
				'',' class="childListNullData" style="display: '.( ($js_hide and AG_LIST_EMPTY_HIDE) ? 'none' : 'block').';"');
      }
	$hide_button = Java_Engine::hide_show_button($object.'ChildList'.$js_ident,$js_hide,'','','now');
	$subtitle_stuff = ( $engine[$object]['add_link_show'] ? cell(left(smaller($add_link.($abs_add_link ? '<br />'.$abs_add_link : '')))) : null)
		. ($sub_code ? cell($sub_code) : null)
		. ($sub_code_array)
		. ($sub_title ? cell($sub_title) : null);
	$subtitle_stuff = is_null($subtitle_stuff) ? null : table(row($subtitle_stuff),'',' class="" cellpadding="3"');
	$back_to_top_link = smaller(seclink('TOP','Back to top',' class="fancyLink"'),3);
      return anchor($object)
		. $hidden_html
		. span($hide_button.'&nbsp;'.$title,' class="childListTitle"')
		. Java_Engine::hide_show_content(
							   span($back_to_top_link,' class="childListLeft"')
							   . div(
								   $subtitle_stuff
								   . oline($OUTPUT),'',' class="childListData childListData' . ucfirst($object) . '"')
							   ,$object.'ChildList'.$js_ident,$js_hide);
	
}

function open_office_button($control,$def,$header='',$options='')
{
	if (!AG_OPEN_OFFICE_ENABLE_EXPORT) {

		return '';

	}


      $sql = list_query_string($def,$control);
	  $sql_pre = $control['sql_pre'];
	if ($templates = $control['oo_templates'] and is_array($templates)) {
		//must be passed as array of the form array(template_name=>label)
		if (array_key_exists('spreadsheet',$templates)
		    and count($templates)==1) { 
			//nothing but the default template...no list
		} else {
			foreach ($templates as $t) {
				$template_list .= selectitem(tokenize($t[0],'generic_sql'),$t[1]);
				if ($t[0]=='spreadsheet') {
					$spreadsheet_found = true;
				}
			}
			if (!$spreadsheet_found) {
				$template_list .= selectitem(tokenize('spreadsheet','generic_sql'),'(default)') . $template_list;
			}
			$template_list = oline().selectto(AG_REPORTS_VARIABLE_PREFIX.'template',$options)
				.$template_list 
				. selectend();
		}
		$multi_templates = true;
	}

	$template_list = orr($template_list,hiddenvar(AG_REPORTS_VARIABLE_PREFIX.'template',tokenize('spreadsheet','generic_sql')));

      $button_text = $multi_templates ? 'Export File' : 'Spreadsheet';
      $header=orr($header,$control['export_header'],$sql);
      $form = formto(AG_OPENOFFICE_GEN_PAGE)
		. hiddenvar('sql1',tokenize($sql,'generic_sql'))
		. ($sql_pre ? hiddenvar('sql_pre',tokenize($sql_pre,'generic_sql')) : '' )
		. hiddenvar('report_header',tokenize($header,'generic_sql'))
		. hiddenvar('sql_count','1')
		. $template_list
		. button($button_text,'','','','',$options)
		. formend();
      return $form;
}

function list_title_generic($control,$def)
{
	//an attempt to generate a meaningful title for non-standard list situations
	//the default is back to title_generic
	//However, for now we just use the SQL query...
	$action='list';
	$format=$def['title_format'];
	$filter=orr($control['list']['filter'],array());
	//	$title = title_generic('list',$filter,$def);
	if (!($x=$control['title'])) {
		$x = eval('return '.$def['title_list'].';');
	}
	if (array_key_exists(AG_MAIN_OBJECT_DB.'_id',$filter) && !strstr($x,' for ')) {
		$x .= ' for '.client_link($filter[AG_MAIN_OBJECT_DB.'_id']);
	} elseif ( (array_key_exists('staff_id',$filter)||array_key_exists('staff_id_name',$filter)) ) {
		$staff=orr($filter['staff_id'],$filter['staff_id_name']);
		$x .= ' for '.staff_link($staff);
	}
	$title = $format ? eval('return '.$format.';') : bigger(bold($x));
	if ($sql = $control['sql']) {
		if (is_array($sql)) {
			$sql = $sql[count($sql) - 1];
		}
		$subtitle='Using: '.italic($sql);
	} elseif (!array_key_exists(AG_MAIN_OBJECT_DB.'_id',$filter) 
	    && !array_key_exists('staff_id',$filter)
	    && !array_key_exists('staff_id_name',$filter)) {
		$subtitle ='Using: '.italic($def['sel_sql'].($filter ? ' WHERE '.read_filter($filter) : ''));
	}
	return oline($title .' '.div(smaller($subtitle) . toggle_label("sql details..."),'','class="hiddenDetail"'));
}

function list_verify_control($c)
{
	/*
	 * Verify that passed list options are valid. Any user-input-able
	 * (text fields) data should be cleaned here.
	 */

	global $engine;

	/*
	 * Columns
	 */
      $columns = abs(intval($c['list']['columns']));
	$columns = ($columns > 0) ? $columns : $engine[$c['object']]['list_columns'];
	$c['list']['columns'] = $columns;

	/*
	 * max
	 */
	$def = $engine[$c['object']];
	$max = intval($c['list']['max']);
	$max = $max ? $max : $def['list_max'];
	$c['list']['max'] = $max;
	
	return $c;
}

function show_list_totals($result,$total,$def,$control,$fields)
{
	/*
	 * Display the totals row for a list box
	 */
	if ($control['list']['show_totals']
		and (sql_num_rows($result) > 1)) {

		$columns=$control['list']['columns'];
		$cols=(count($fields)+2) * $columns;

		$pos = $control['list']['position'];
		$max = $control['list']['max'];

		$page_totals = $TOTALS = array();

		for ($i=0; $i<$total; $i++) {

			$a=sql_fetch_assoc($result,$i);
			$TOTALS = calculate_list_totals_generic($a,$def,$TOTALS);

			if ($i >= $pos && $i < $pos+$max && $max < $total) { //hide page totals if all recs displayed

				$page_totals = calculate_list_totals_generic($a,$def,$page_totals);

			}

		}
		foreach ($fields as $field) {

			if ($TOTALS[$field]) {
				$has_totals=true;
			}
			$row .= cell(value_generic($TOTALS[$field],$def,$field,'list',false)
					 . ((be_null($page_totals)) ? '' : oline() . smaller(value_generic($page_totals[$field],$def,$field,'list'))));

		}
		
		if (!be_null($page_totals)) {

			$page_totals_f = oline() . alt(smaller('(Page)'),'Totals for this page');

		}

		return $has_totals
			? row(cell('Totals:' . $page_totals_f).cell().$row,'class="listTotals"')
			: '';

	} else {

		return false;

	}

}

function calculate_list_totals_generic($rec,$def,$TOTALS)
{
	/*
	 * For now, a very basic function, but eventually it will
	 * be able to provide better totalling functionality
	 */

	foreach ($rec as $key=>$value) {
		$TOTALS[$key] = orr($TOTALS[$key],0);
		$type = $def['fields'][$key]['data_type'];
		switch ($type) {
		case 'currency' :
		case 'float' :
		case 'integer' :
			if ($def['id_field'] !== $key) { //exclude primary key
				if ($eval = $def['fields'][$key]['total_value_list']) {
					$x = $value;
					$value = eval('return '.$eval.';');
				}
				$TOTALS[$key] += $value;
				break;
			}
		default :
			$TOTALS[$key] = null;
		}
	}
	return $TOTALS;
}

function list_all_child_records($object,$id,$def,$output=false)
{
	if (!$child_records = $def['child_records']) {
		return false;
	}

	$s_button = Java_Engine::toggle_tag_display('div',bold('show empty records'),'childListNullData','block',$output);
	$h_button = Java_Engine::toggle_tag_display('div',bold('hide empty records'),'childListNullData','block',false);

	$out .= oline(smaller(Java_Engine::hide_show_buttons($s_button,$h_button,$output,'inline',AG_LIST_EMPTY_HIDE)));
	if ($output) {
		out($out);
		$out = '';
	}
	$child_records = $def['child_records'];
	$js_hide = true;

	$display_object = get_object_display_settings($object);
	foreach ($child_records as $c_object) {
 		if ($display_object[$c_object]['show']) {
			$js_hide = !$display_object[$c_object]['js_show'];
			$page=$_SERVER['PHP_SELF'];
			$out .= child_list($c_object,$id,$page,$object,$def['id_field'],$js_hide);
			if ($output) {
				out($out);
				$out = '';
			}
		}
	}

	return $out;
}

function child_list_add_link($def,$filter,$control)
{
	if (!$def['allow_add']) {
		return '';
	}

	$object = $control['object'];
	$id = $control['id'];
      $singular=$def['singular'];
	$plural=$def['plural'];
	if (is_array($def['object_union'])) {
		global $engine;
		//$tmp_link=null;
		$tmp_link=array();
		foreach ($def['object_union'] as $obj) {
			$tmp_sing = $engine[$obj]['singular'];
			array_push($tmp_link,link_engine(array('object'=>$obj,
									    'action'=>'add',
									    'rec_init'=>$filter),
								    orr($engine[$obj]['add_link_label'],
									  smaller('Add '.aan($tmp_sing).' '.$tmp_sing))));
		}
//		$add_link = table($tmp_link,'',' class="" style="display: inline; vertical-align: -50%;"');
		$add_link = implode($tmp_link,' | ');
	} elseif ($code=$def['add_link_alternate']) {
		$rec=$filter;
		$add_link=eval('return '.$code.';');
	} else {
		$add_link=link_engine(array('object'=>$object,
						    'action'=>'add', 
						    'rec_init'=>$filter),
					    orr($def['add_link_label'], // PUT CUSTOM add_link_label INTO CONFIG FILE
						  'Add '.aan($singular).' '.$singular));
	}
	return $add_link;
}

/*
 * Below functions are for multiple-record add functionality, adapted from quick-DAL entry
 */

function init_form_generic($def,$defaults,$control)
{
	/*
	 * Generate the initial data gathering form that collects
	 * the common values that will not be required for the subsequent
	 * form.
	 */

	foreach ($def['multi_add']['init_fields'] as $key) {
		$def['fields'][$key]['null_ok']=true;
		$cell = form_field_generic($key,$defaults[$key],$def,$control,$Java_Engine,'rec_init');
		$row .= rowrlcell(label_generic($key,$def,'add'),$cell);
	}

	//pass along initial defaults not in init_fields
	foreach ($defaults as $key => $val) {
		if (!in_array($key,$def['multi_add']['init_fields'])) {
			$row .= rowrlcell(label_generic($key,$def,'add'),value_generic($val,$def,$key,'add'));
			$out .= hiddenvar('rec_init['.$key.']',$val);
		}
	}

	return help('','Fill in the fields that will be the same for all the '.$def['plural'].' you are going to enter. '
				 . link_wiki('AGENCY_Multi_Record_Entry','Wiki help','target="_blank"'),'',$expanded=true) . table($row) . $out;
}

function form_list_header_generic($def,$control,&$row_count)
{
	/*
	 * Generate the header-row for the list form (list form = one-row per record form)
	 */

	$fields = array_keys($def['fields']);
	$action = $control['action'];

	$row_count = 0;

	foreach ($fields as $field) {
		$f_def = $def['fields'][$field];
		if ( !be_null(($q=$f_def["display_eval_$action"])) ) {
			$disp = eval( 'return ' . $q . ';' );
		} else {
	    	$disp = $f_def["display_$action"];
		}
		if ($disp=='hide') {
			continue;
		}
		$row_count ++;
		$row .= cell(label_generic($field,$def,$action));
	}
	return row($row);
}

function form_list_row_generic($number,$rec,$def,$control)
{
	/*
	 * Generate a row for the given record, complete with form variables and any
	 * necessary hidden variables.
	 */
	$fields = array_keys($def['fields']);
	$action = $control['action'];

	$i = 0;
	foreach ($fields as $field) {
		$f_def = $def['fields'][$field];
		if ( !be_null(($q=$f_def["display_eval_$action"])) ) {
			$display = eval( 'return ' . $q . ';' );
		} else {
	    	$display = $f_def["display_$action"];
		}
		if ($display=='hide') {
			if (in_array($field,$def['multi_add']['common_fields'])) {
				//nothing
			} else {
				$hids .= hiddenvar('RECS['.$number.']['.$field.']',$rec[$field]);
			}
			continue;
		}
		$cell = form_field_generic($field,$rec[$field],$def,$control,$Java_Engine,'RECS['.$number.']');
		
		$row .= cell($cell);
	}

	return $row . $hids;

}

function form_list_generic($RECS,$def,$control,$errors,$rec_init)
{
	/*
	 * Generate the multi-record entry form, with one row per record.
	 */

	$rows = $def['fn']['form_list_header']($def,$control,$row_count);
	foreach ($RECS as $number => $rec) {
		$w = $w==1 ? 2 : 1;
		if (in_array($number,$errors)) {
			$w = 'Error';
		}
		$rows .= row($def['fn']['form_list_row']($number,$rec,$def,$control),' class="generalData'.$w.'"');
	}

	if ($def['fn']['multi_record_allow_common_fields']($rec_init)) {
		$common = '';
		foreach ($def['multi_add']['common_fields'] as $field) {
			$common .=  oline(bold(label_generic($field,$def,'add')))
				. oline(form_field_generic($field,$RECS[0][$field],$def,$control,$JUNK,'RECS[0]'));
		}
	}
	return table(row(topcell(table($rows,'','class="multiAddForm"')) . topcell(div($common))));
}

function multi_add_title_generic($def,$rec_init)
{
	/*
	 * This slightly mis-named function actually returns a
	 * table with any common fields/values
	 */
	foreach ($rec_init as $key => $value) {

		$title .= view_generic_row($key,$value,$def,'add',$rec_init);

	}
	return table($title);
}

function multi_record_passed_generic($rec,$rec_init,$def)
{

	/*
	 * Determine if a record has been passed, based on what is
	 * required to pass a record. For example, if a column has a 
	 * default, if a user enters one record, it shouldn't assume that
	 * the user entered 10 records simply because the column with the
	 * default value isn't blank in all 10.
	 */
	$passed = false;
	//first, generate an array of elements _specified_ as init fields, but not passed via rec_init
	$missing = array();
	$rec_init_fields = array_keys($rec_init);
	foreach ($def['multi_add']['init_fields'] as $field) {
		if (!in_array($field,$rec_init_fields)) {
			$missing[] = $field;
		}
	}
	//now, add any fields that are still required
	foreach (array_keys($def['fields']) as $field) {
		if ( !be_null(($q=$def['fields'][$field]["display_eval_$action"])) ) {
			$disp = eval( 'return ' . $q . ';' );
		} else {
	    	$disp = $def['fields'][$field]["display_$action"];
		}
		if (!in_array($field,$rec_init_fields) && !in_array($field,$missing) && ($disp != 'hide')) {
			$missing[] = $field;
		}
	}

	$check_fields = array_merge($missing,$def['multi_add']['common_fields']);
	foreach ($rec as $key => $value) {
		if (in_array($key,$check_fields) && (!be_null($value)) && ($value<>$def[$key]['default'])) {
			$passed = true;
		}
	}
	return $passed;
}

function valid_multi_record_generic(&$records,&$def,&$message,&$errors,$rec_init)
{
	/*
	 * Wrapper function for valid_generic, to determine if a set of records
	 * is valid.
	 */
	$common_fields=orr($def['multi_add']['common_fields'],array());
	$i = 0;
	$tmp = array();
	foreach ($records as $number => $rec) {
		if ($i++==0 && $def['multi_add']['common_fields_required']) {
			// require common fields for first record, retain default in $tmp array
			foreach ($common_fields as $field) {
				$tmp[$field] = $def['fields'][$field]['null_ok'];
				$def['fields'][$field]['null_ok'] = false;
				$common_val[$field]=$rec[$field];
			}
			$restore = true;
		} elseif ($restore) {
			// restore default from $tmp array
			foreach ($common_fields as $field) {
				$def['fields'][$field]['null_ok'] = $tmp[$field];
			}
		}
		if ($def['fn']['multi_record_passed']($rec,$rec_init,$def)) {
			foreach ($common_fields as $field) {
				$rec[$field]=$common_val[$field]; // tack common vals on to each record
			}
			$t_v = valid_generic($rec,$def,$message,'add');
			$valid = $t_v ? orrn($valid,$t_v) : $t_v;
			if (!$t_v) {
				array_push($errors,$number);
			}
		} else {
			if ($skip++ > 1) {
				unset($records[$number]); // save two extra rows, the discard rest of recs
			}
		}
	}

	return $valid;
}

function post_multi_records_generic($records,$def,&$message,$rec_init)
{

	/*
	 * Wrapper function for post_generic to post a set of records
	 */

	sql_begin();
	$success = true;
	$one = true;
	foreach ($records as $n => $rec) {
		if ($def['fn']['multi_record_passed']($rec,$rec_init,$def)) {
			if ($reference_id) {
				$rec[$def['multi_add']['reference_id_field']] = $reference_id;
			}
			if ($tmp_r = post_generic($rec,$def,$message)) {
				$n_records[$n] = sql_to_php_generic($tmp_r,$def);
			} else {
				$success = false;
			}
			if ($one and $def['fn']['multi_record_allow_common_fields']($rec_init) and $success) {
				$reference_id = $tmp_r[$def['id_field']];
			}
		}
		$one = false;
	}
	if ($success) {
		sql_end(); 
		$message .= 'Records posted';
		return $n_records;
	}

	sql_abort();

	return false;
}

function multi_record_process_generic($def,$unique_id)
{
	/*
	 * Wrapper function for process_generic to process a set of records
	 */

	$recs = $_REQUEST['RECS'];
	$RECS = orr($_SESSION['RECS'.$unique_id],array());

	foreach ($RECS as $key => $rec) {
		process_generic($RECS[$key],$recs[$key],$def);
	}

	return $RECS;
}

function multi_record_allow_common_fields_generic($rec_init)
{
	/*
	 * Placeholder function
	 */
	return true;
}

function view_list_generic($RECS,$def,$control,$rec_init)
{

	/*
	 * Generate a view of the entered records for previewing them prior to posting
	 */

	$rows = $def['fn']['form_list_header']($def,$control,$row_count);

	foreach ($RECS as $number => $rec) {
		foreach ($rec as $key => $value) {
			$x = $value;
			$rec[$key] = $def['fields'][$key]
				? eval('return '. $def['fields'][$key]['value_'.$control['action']].';')
				: $value;
		}

		if ($def['fn']['multi_record_passed']($rec,$rec_init,$def)) { //use dal date as a 'passed' record
			$w = $w==1 ? 2 : 1;

			$confirm_message = '';
			if (!confirm_generic($rec,$def,$confirm_message,'add',null)) {
				$rows .= row(cell($confirm_message,' class="warning" colspan="'.$row_count.'"'));
			}

			$rows .= row($def['fn']['view_list_row']($number,$rec,$def,$control),' class="generalData'.$w.'"');
		}
	}

	foreach ($def['multi_add']['common_fields'] as $field) {
		if (!be_null($RECS[0][$field])) {
			//evaluate $x from value
			$x = $RECS[0][$field];
			$val = $def['fields'][$field]
				? eval('return '.$def['fields'][$field]['value_'.$control['action']].';')
				: $x;
			$extra .= bold(label_generic($field,$def,'add'))
				. div(value_generic($val,$def,$field,'view'),'','class="multiAddForm"');
		}
	}

	return table($rows,'','class="multiAddForm"') . $extra;
}

function view_list_row_generic($number,$rec,$def,$control)
{
	/*
	 * Return a row for the record preview
	 */
	$fields = array_keys($def['fields']);
	$action = $control['action'];

	$i = 0;
	foreach ($fields as $field) {
		$m_add=$def['multi_add'];
		if ($m_add['common_fields_required'] and in_array($field,$m_add['common_fields'])) {
			$def[$field]['null_ok']=false;
		}
		$f_def = $def['fields'][$field];
		if ( !be_null(($q=$f_def["display_eval_$action"])) ) {
			$disp = eval( 'return ' . $q . ';' );
		} else {
	    	$disp = $f_def["display_$action"];
		}
		if ($disp=='hide') {
			continue;
		}
		$cell = value_generic($rec[$field],$def,$field,'view');
		$row .= cell($cell);
	}

	return $row;

}

function multi_add_after_post_generic($message,$rec,$def,$rec_init)
{
	//placeholder function
	return '';
}

function multi_add_blank_generic($def,$rec_init)
{
	/*
	 * Wrapper function for blank_generic to gather a set of blank records
	 */
	$action='add';
	// First, wipe out default values for visible fields
	$n_def=$def;
	foreach($def['fields'] as $f=>$d) {
		if ( !be_null(($q=$d["display_eval_$action"])) ) {
			$disp = eval( 'return ' . $q . ';' );
		} else {
	    	$disp = $d["display_$action"];
		}
		if (($disp<>'hide')) {
			$n_def['fields'][$f]['default']=NULL;
		}
	}
	for ($i=0; $i < $def['multi_add']['number_of_records']; $i++) {
		$tm_r[$i] = blank_generic($n_def,$rec_init);
	}
	return $tm_r;
}

function link_multi_add($object,$label,$rec_init=array(),$options='')
{

	/*
	 * Generate a link to the multi_add data-entry page
	 */

	$def = get_def($object);
	if (!engine_perm(array('object'=>$def['object'],'action'=>'add'))) {
		return dead_link($label);
	}

	$query = '?reset=1&object='.$object;
	if (!be_null($rec_init) and is_array($rec_init)) {
		foreach ($rec_init as $key=>$val) {
			$query .= '&rec_init_defaults['.$key.']='.$val;
		}
	}
	return hlink(AG_QUICK_DAL_PAGE.$query,$label,'',$options);

}

function multi_hide_fields_generic($def,$rec_init)
{
	/*
	 * Modify the $def array to convert normally displayed fields to hidden
	 */

	foreach (array_keys($def['fields']) as $f) {
		if ($def['fields'][$f]['null_ok'] 
		    or $def['fields'][$f]['system_field']
			or in_array($f,$def['multi_add']['common_fields'])
		    or in_array($f,array_keys($rec_init))) {
			$def['fields'][$f]['display_add'] = 'hide';
		}
	}
	return $def;
}

/*
 * END multiple-record add functions
 */

function object_child_command_box_generic($object,$parent_id)
{
	/*
	 * Make a command box for child record display options.
	 * Currently only used for staff and client pages.
	 */

	if (!$def = get_def($object)) {
		return false;
	}

	if (!$child_records = $def['child_records']) {
		return false;
	} elseif (is_assoc_array($child_records)) {
		// this is for child records that are grouped (eg, client)
		$child_records = array_keys($child_records);
	}

	$display_object = get_object_display_settings($object);
	// sort child records by plural
	$new = array();
	foreach ($child_records as $child) {
		// Skip child records not enabled in engine
		if (in_array($child,$GLOBALS['AG_ENGINE_TABLES'])) {
			$t_def = get_def($child);
			$new[$child] = $t_def['plural'];
		}
	}
	asort($new);
	$child_records = array_keys($new);

      $COLUMNS = 3;
	$tmp = tablestart('','class="pick" width="100%"');
	$row='';
	while($j<$COLUMNS) {
		$j++;
		$row .= cell(acronym('record type','Child Record Options'))
			.cell(acronym('load','Load records'))
			.cell(acronym('show','Initially show records'))
			.cell(acronym('max','Maximum number of records to load'))
			.cell();
	}
	$tmp .= row($row);
	while ($x<count($child_records)) {
		$row='';
		for ($j=0;$j<$COLUMNS;$j++) {
			$c_object = $child_records[$x+$j];
			$o_def    = get_def($c_object);
			$show     = $display_object[$c_object]['show'];
			$max      =  $display_object[$c_object]['max'];
			$js_show  = $display_object[$c_object]['js_show'];
			$sing     = ucwords($o_def['plural']);
			$label    = jump_to_object_link($c_object,$object,$sing);
			$row .= $c_object 
			      ? ( cell(alt("$label: ",'Jump to '.$sing))
				    . cell(form_field('boolcheck',"display[$c_object][show]",$show,
							    ' title="Load records into page (de-select for faster page loading)"'))
				    . cell(form_field('boolcheck',"display[$c_object][js_show]",$js_show,
							    ' title="Select to initially show records (hidden records will still be loaded)"'))
				    . cell(form_field('number',"display[$c_object][max]",$max,'size="2"')) 
				    . cell())
			      : '';
		}
		$tmp .= row($row);
		$x+=$COLUMNS;
	}
	$tmp .= tableend();

	//handle image display for objects with images/photos
	$test_fn=$object.'_photo';
	if ($GLOBALS['AG_'.strtoupper($object).'_PHOTO_BY_FILE']
	    && function_exists($test_fn)
	    && count($test_fn($parent_id,1,true)) > 1) {
 		$photos = oline(hlink($_SERVER['PHP_SELF'] . '?id='.$parent_id.'&display_all_photos='.(!$_REQUEST['display_all_photos']),
 					    smaller($_REQUEST['display_all_photos'] 
 							? 'Show current photo only' 
 							: 'Show all photos'),
 					    '',' class="fancyLink"'));
 	}

	$box = row(cell($tmp,'colspan="4"'))
		. row(leftcell(button('Re-Display','','','','','class="engineButton"')
				   . hlink($_SERVER['PHP_SELF'].'?id='.$parent_id.'&RESET_COMMAND_BOX=Y','Reset','','class="linkButton"'),'colspan="3"'));
	$hide_button = Java_Engine::hide_show_button('objectCommandBox',true,true,' display controls');
	$box =  Java_Engine::hide_show_content(formto($_SERVER['PHP_SELF'] . '?id='.$parent_id).tablestart('','class="pick" width="100%"')
							   . $box
							   . tableend().formend()
							   ,'objectCommandBox',true)
		. right(smaller($photos . $hide_button));
	return $box;

}

function get_object_display_settings($object)
{
	/*
	 * Get the user's display settings for the give $object
	 */

	if (!$def = get_def($object)) {
		return false;
	}

	if (!$child_records = $def['child_records']) {
		return false;
	} elseif (is_assoc_array($child_records)) {
		// this is for child records that are grouped (eg, client)
		$child_records = array_keys($child_records);
	}
	//get defaults
	foreach($child_records as $x) {
		$c_def = get_def($x);
		$display_object_default[$x]['show']    = $c_def['parent_show'];
		$display_object_default[$x]['js_show'] = $c_def['parent_js_show'];
		$display_object_default[$x]['max']     = orr($c_def['list_max'],20);
	}

	$OBJECT = strtoupper($object);
	global $AG_USER_OPTION;
	$USER_PREFS = $AG_USER_OPTION->get_option($OBJECT.'_DISPLAY_OPTIONS');

      $reset = $_REQUEST['RESET_COMMAND_BOX'];
	$request_display = verify_command_box($_REQUEST['display'],$object);
      // GET LATEST display
      $display=($reset)
		? $display_object_default
		: array_merge($display_object_default,
				  orr($USER_PREFS,array()),  //retains selection across sessions
				  orr($_SESSION['DISPLAY_'.$OBJECT],array()),
				  orr($request_display,array()));
      foreach ($child_records as $c_object) {
		$KEY = 'CONTROL_'.strtoupper($c_object);
		// MERGE INTO CONTROL ARRAYS
		$tmp_control    = unserialize_control($_REQUEST[strtolower($KEY)]);
		$_SESSION[$KEY] = unserialize_control($_SESSION[$KEY]);
		$_SESSION[$KEY]['list']['max'] = ($reset)
			? $display_object_default[$c_object]['max']
			: orr($request_display[$c_object]['max'],
				$tmp_control['list']['max'],
				$_SESSION[$KEY]['list']['max'],
				$USER_PREFS[$c_object]['max'],
				$display_object_default[$c_object]['max']);
		//MERGE MAX NUM RECORDS
		$display[$c_object]['max'] = $_SESSION[$KEY]['list']['max'];
      }
      update_session_variable('DISPLAY_'.$OBJECT,$display);
	$AG_USER_OPTION->set_option($OBJECT.'_DISPLAY_OPTIONS',$_SESSION['DISPLAY_'.$OBJECT]);
      return $_SESSION['DISPLAY_'.$OBJECT];
}

?>
