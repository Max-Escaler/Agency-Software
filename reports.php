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

//FIXME:
//require_once 'openoffice.php';

function get_report_from_db( $report_code )
{
	$r_def=get_def('report');
	$key=$r_def['id_field'];
	$rpt=get_generic(array($key=>$report_code),'','',$r_def);
	if (count($rpt) <> 1)
	{
		return false;
	}
	$rec=sql_to_php_generic(array_shift($rpt),$r_def);
	// get sql statements
	$s_def=get_def('report_block');
	$sql_recs=get_generic(array('report_code'=>$rec['report_code']),'','',$s_def);
	$sql=array();
	while ($s=array_shift($sql_recs)) { 
		$s=sql_to_php_generic($s,$s_def);
		/* Split multiple SQL statements into array */
		$s['report_block_sql'] = preg_split( '/\n\s?SQL\s?\n/im',$s['report_block_sql'] );
		$sql[]=$s; 
	}
	$rec['report_block']=$sql;
	if (!be_null($rec['output_template_codes'])) {
		foreach(explode("\n",$rec['output_template_codes']) as $line)
		{
			$out_a[] = explode('|',$line);
		}
		$rec['output_template_codes']=$out_a;
	}
	else {
		$rec['output_template_codes'] = array();
	}
	return $rec;
}

function report_parse_var_text( $text, $get_defaults=true ) {
/*
  I'm adding the get_defaults option here, defaulting to current behavior of true.
  Recently EVAL was added as an option for defaults, and
  also link_report was changed to call this function.

  The problem with those two combined is that if the EVAL results in a 
  fatal error (not a parse error, but for example a function not found),
  the entire script will just crash.  (I discovered this when just such an
  EVAL called from a report link on the home page caused the home page to crash.)

  Apparently there is no way to trap the eval(), and the alternative is to
  write the thing to a file and include it.  That seems like a lot of hassle.

  The upshot of having link_report use the false option is that the report links won't
  cause a crash.  The report itself still won't run, but that seems more limited and reasonable,
  even if not ideal.  FIXME.

  (n.b., if you break a report in this manner, I think you will need to "view data list" from the
  reports page, and then view the report from there and edit the offending EVAL. Or better yet,
  just keep the report record in a tab after you edit it.)
*/

	$var_types=array('PICK','PICK_MULTI','DATE','TIME','TIMESTAMP','TEXT','TEXT_AREA','VALUE');
	$pick_types=array('PICK','PICK_MULTI');
	$endpick_types=array('ENDPICK','ENDPICK_MULTI');
	$lines = preg_split('/(\n)/m',$text);
	while ($line = array_shift($lines)) {
		if (preg_match('/^\s$/',$line)) {
			continue; //skip blank lines
		}
		$var=array();
		$ex = explot($line);
		if (!in_array(strtoupper($ex[0]),$var_types)) {	
			//fixme:  make me a pretty warning
			outline('Warning:  Unknown variable type ' .$ex[0]);
			continue;
		}
		$var['type']    = strtoupper($ex[0]);
        $var['name']    = $ex[1];
        $var['prompt']  = $ex[2];
	if ($get_defaults) {
        $var['default'] = $ex[3];
		if (preg_match('/^EVAL:(.*)$/i',$var['default'],$matches)) {
			$var['default'] = eval( 'return ' . $matches[1] . ';');	
		}
	}
		if (in_array($var['type'],$pick_types)) {
			while ($tmp_line = array_shift($lines))	{
				$tmp_line = explot($tmp_line);
				if (in_array(strtoupper($tmp_line[0]),$endpick_types)) {
					break;
				}
				if (strtoupper($tmp_line[0])=='SQL') {
					$tmp_sql = '';
					while ($tmp = array_shift($lines)) {
						$tmp_ex = explot($tmp);
						if (strtoupper($tmp_ex[0])=='ENDSQL') {
							// SQL query assembled and ready
							$tmp_query = agency_query($tmp_sql); 
							while ($t=sql_fetch_assoc($tmp_query)) {
								$var['options'][$t['value']]= $t['label'];
							}
							break;
						} else {
							$tmp_sql .= $tmp;
						}	
					}	
				} else {
					$var['options'][$tmp_line[0]] = $tmp_line[1];
				}
			}
		}
		$vars[]=$var;
	}
	return $vars;
}

function report_valid_request($report, &$mesg)
{
	/*
	 * validates a request for a report
     * currently, only checks for valid dates
	 */

	$valid = true;
	$report['variables']=orr($report['variables'],array());
	foreach ($report['variables'] as $var) {
		$type  = $var['type'];
		$name  = $var['name'];
		$prompt = $var['prompt'];
		$value = report_get_user_var($name,$report['report_code'],$type);
		switch ($type) {
		case 'VALUE' :
			if (!is_numeric($value)) {
				$mesg .= oline($prompt.': '.$value.' is an invalid value');
				$valid = false;
			}
			break;
		case 'DATE' :
			if (!dateof($value,'SQL')) {
				$mesg .= oline($prompt.': '.$value.' is an invalid date');
				$valid = false;
			}
			break;
		case 'TIME' :
			if (!timeof($value,'SQL')) {
				$mesg .= oline($prompt.': '.$value.' is an invalid time');
				$valid = false;
			}
			break;
		case 'TIMESTAMP' :
			if (!datetimeof($value,'SQL')) {
				$mesg .= oline($prompt.': '.$value.' is an invalid timestamp');
				$valid = false;
			}
			break;
		default: //no error checking
		}
	}
	return $valid;
}

function report_get_user_var($name,$report_code,$type=NULL)
{
	// FIXME:  Validate these inputs!!!
	$varname = AG_REPORTS_VARIABLE_PREFIX.$name;
	if (!isset($_REQUEST[$varname]) and isset($_REQUEST[$varname.'_date_']) and isset($_REQUEST[$varname.'_time_'])) {
		// Reassemble timestamps
		$val=datetimeof($_REQUEST[$varname.'_date_'].' ' . $_REQUEST[$varname.'_time_']);
	} elseif ($type=='PICK_MULTI') {
		$val=is_array($_REQUEST[$varname]) ? array_keys($_REQUEST[$varname]) : $_REQUEST[$varname];
		if (is_array($val)) {
			foreach ($val as $v) {
				$new_val[]=detokenize($v,report_token_context($name,$report_code));
			}
		} else {
			$new_val=detokenize($v,report_token_context($name,$report_code));
		}
		$val=$new_val;
			
	} else {
		$val=$_REQUEST[$varname];
		if (in_array($type,array('PICK','PICK_MULTI'))) {
			$val=detokenize($val,report_token_context($name,$report_code));
		}
	}	
	$_SESSION['report_options_'.$report_code.'_'.$varname] = $val;
	return $val;
}

function report_generate($report,&$msg)
{
	/*
	 * Generates and returns an engine list,
	 * or merges query results with an open office template and exits
	 *
	 */

	// variables, used repeatedly
	$rb='report_block'; // the element within $report
	$rbs=$rb.'_sql'; // the sql field within that array

	$pattern_replace_header = $pattern_replace = array();
	$report['variables']=orr($report['variables'],array());
	foreach ($report['variables'] as $var) {
		$type  = $var['type'];
		$name  = $var['name'];
		$value = report_get_user_var($name,$report['report_code'],$type);
		switch ($type) {
		case 'DATE' :
			$value_sql = dateof($value,'SQL');
			$value_header = dateof($value);
			break;
		case 'TIME' :
			$value_sql = timeof($value,'SQL');
			$value_header = timeof($value);
			break;
		case 'TIMESTAMP' :
			$value_sql = datetimeof($value,'SQL');
			$value_header = dateof($value) . ' ' . timeof($value);
			break;
		case 'PICK_MULTI' :
			if (is_array($value)) {
				$value_sql=implode("','",$value); // When used wrapped in single quotes, 'v','a','l','u','e'
				$value_header = implode(',',$value);
			} else {
				$value_sql = $value_header = $value;
			}
			break;
		default:
			$value_sql = $value;
			$value_header = is_array($value) ? implode(',',$value) : $value;
			break;
		}
		$pattern_replace['$'.$name] = $value_sql;
		$pattern_replace_header['$'.$name] = $value_header; //contains human-readable values for date
		//set labels
		switch ($type) {
			case 'PICK' : //a pick variable, determine which was picked
				$label = $var['options'][$value];
				break;
			case 'PICK_MULTI' :
				$tmp_array = array_values(array_intersect_key($var['options'],array_flip($value)));
				// FIXME: Make me a nice separate listing function
				switch (count($tmp_array)) {
					case 0 :
						$label='';
						break;
					case 1 :
						$label=$tmp_array[0];
						break;
					case 2 :
						$label = implode(' & ',$tmp_array);
						break;
					default :
						$label = implode(', ',array_slice($tmp_array,0,-1)) . ' & ' . $tmp_array[count($tmp_array)-1];
						break;
				}
				break;	
			default : 
				$label = $var['prompt']; //FIXME: Is this right?  Doesn't seem so.  $label=$value instead?
				break;
		}
		$pattern_replace['$'.$name.'_label'] = $label;
		$pattern_replace_header['$'.$name.'_label'] = $label;
	}
	
	foreach( report_system_variables() as $k=>$v) {
		$pattern_replace['$'.$k ]=$v;
		$pattern_replace_header['$'.$k ]=$v;
	}

	// Save in report variable
	$report['pattern_replace']=$pattern_replace;
	$report['pattern_replace_header']=$pattern_replace_header;

	// sort longest to shortest keys so, for example, "date_end" is replaced before "date"
	uksort($pattern_replace,'strlen_cmp');
	$pattern = array_keys($pattern_replace);
	$replace = array_values($pattern_replace);
	
	// a separate, formatted, replace array for header and footer
	uksort($pattern_replace_header,'strlen_cmp');
	$pattern_h = array_keys($pattern_replace_header);
	$replace_h = array_values($pattern_replace_header);

	// Loop through report blocks
	foreach ($report[$rb] as $s => $sql) {
		// Strip out disabled blocks
		if (sql_false($sql['is_enabled'])) {
		    unset($report[$rb][$s]);
		    continue;
		}
		$sqls = $block_sql_footer = array();
		foreach ($sql[$rbs] as $sql2) {
			$sql3=str_replace($pattern,$replace,$sql2);
			$sqls[] = $sql3;
			$sq_footer[] = $block_sql_footer[] = html_list_item(webify_sql($sql3)); // all queries, for overall report footer
		}
		$report[$rb][$s][$rbs] = $sqls;
		// sql_pre, execution required (e.g., temp tables) for spreadsheet export on multi-block report pages
		if ($sql_pre) {
			$report[$rb][$s]['sql_pre']=$sql_pre;
		}
		if (sql_true($report[$rb][$s]['execution_required'])) {
			$sql_pre[]=$sqls;
		}
		$report[$rb][$s]['block_sql_footer']=$block_sql_footer;
		foreach( array('footer','header','title','comment') as $f) {
				$report[$rb][$s][$rb.'_'.$f]=str_replace($pattern_h,$replace_h,$sql[$rb.'_'.$f]);
		}
	}
	
	$report['report_title'] = str_replace($pattern_h,$replace_h,$report['report_title']);
	$report['report_header'] = str_replace($pattern_h,$replace_h,$report['report_header']);
	$report['report_footer'] = str_replace($pattern_h,$replace_h,$report['report_footer']);
	$report['report_comment'] = str_replace($pattern_h,$replace_h,$report['report_comment']);

	$template = orr(report_get_user_var('template',$report['report_code'],'PICK'),'screen');
	// FIXME: drop this line
	$report['output_template_codes']=orr($report['output_template_codes'],array());
	
	 $export_items=report_export_items();
	 $report_items=$report['output_template_codes'];
	$valid_templates=array_merge($export_items,$report_items);
	$valid_templates=array_fetch_column($valid_templates,'0');
	if (!in_array($template,$valid_templates)) {
	    $msg .= oline("Invalid template $template.  Valid templates are " . implode('',$valid_templates));
	    return false;
	}

	// Merge to screen
	if (be_null($template) || $template==='screen') { 
		if (in_array('O_SCREEN',$report['suppress_output_codes'])) {
			$msg.=oline('Screen output not allowed for this report');
			return NULL;
		}
		$footer = $report['report_footer'] ? span(webify($report['report_footer']),'class="reportFooter"') : '';

		/* cfg and sql output */
		$sys_footer = div(span('Configuration for ' . $report['report_title'] . ' (' . $report['report_code'] .')')
			. dump_array($report),'',' class="hiddenDetail configFile"')
			. div(span('Generated SQL:') . implode('',$sq_footer),'','class="sqlCode hiddenDetail"');

		/* Output to screen via engine list */
		// FIXME: suppress_header_row,suppress_row_numbers
		$control = array('object' => 'generic_sql_query',
				     'action' => 'list',
				     'list'   => array('fields'=>array(),'filter'=>array(),'order'=>array(),'show_totals'=>true,
							     'max' => orr($report['rows_per_page'],'-1')),
				     'export_header' => $report['report_header']); // ???

		$oo_templates = report_output_select($report);
		if (!be_null($oo_templates)) {
			$control['oo_templates'] = $oo_templates;
		}
		foreach ($report[$rb] as $sql) {
 	     	$control['sql_security_override'] = (sql_true($report['override_sql_security']) or sql_true($sql['override_sql_security']));
			$out=array();
			if ( !( ($sql['permission_type_codes']==array()) or has_perm($sql['permission_type_codes'])) ) {
				// FIXME:  perm failed.  Log or notice?
				$mesg .= oline('Insufficient permissions to run report_block');
				continue;
			}
			foreach( array('block_sql_footer',$rb.'_footer',$rb.'_header',$rb.'_title',$rb.'_comment',$rbs,$rb.'_id') as $f) {
				$out[$f]=$sql[$f];
			}
			switch ($sql['report_block_type_code']) {
			  case 'CHART' :
			     //FIXME: implement
			    break;
			  case 'PIVOT' :
			    foreach ($sql[$rbs] as $s ) {
					if (!($result=sql_query($s)) ) {
						$out['results'][]=$report['message_if_error'];
					} elseif (sql_num_rows($result)==0) {
						 $out['results'][]=$report['message_if_empty'];
			      } else {
						$out['results'][]=pivot_encode($result);
				  }	
			    }
			    break;
			  case 'TABLE' :
			  default:
		      $control['sql_pre']= $sql['sql_pre'];
			    foreach ($sql[$rbs] as $s ) {
			      $control['sql']= $s;
			      if (!($out['results'][] = call_engine($control,$control_array_variable='control',$NO_TITLE=true,$NO_MESSAGES=true,$TOTAL_RECORDS,$PERM))) {
				$out['results'][]=oline(orr($sql['message_if_error'],'There was an error processing this section'));
			      } elseif ($TOTAL_RECORDS===0) {
				 $out['results'][]=oline(orr($sql['message_if_empty'],'No records to show in this section'));
			      }
			    }
			    break;
			}
			// Supress at end, so any queries are still run
//outline(dump_array($sql));
			$out[$rb.'_type_code']=$sql[$rb.'_type_code'];
			if (!in_array('O_SCREEN',$sql['suppress_output_codes'])) {
				$outs[]=$out;
			}
		}
		// Assemble the report
		$out=array();
		$c='reportOutput';
		foreach ($outs as $o) {
			$css_class = ' ' . $report['css_class'] . ' ' . $c.ucfirst(strtolower($o['report_block_type_code'])).' ';
			$css_id = $report['css_id'];
			$out[]= div(
					($o[$rb.'_title'] ? div($o[$rb.'_title'],'','class="' .$c.'Title"') : '')
					. ($o[$rb.'_header'] ? div($o[$rb.'_header'],'','class="' .$c.'Header"') : '')
					. div(implode(oline(),$o['results'])
					. ($o['report_block_id'] ? smaller(elink('report_block',$o['report_block_id'],'view database record for this section','class="fancyLink" target="_blank"').' (Report block ID# ' . $o['report_block_id'] . '), or just ') :'')
					. div(implode('',$o['block_sql_footer']),'','class="' .$c.'Code hiddenDetail sqlCode"') // not like the others
,'','class="' .$c.'Results"')

					. ($o[$rb.'_footer'] ? div($o[$rb.'_footer'],'','class="' .$c.'Footer"') : '')
					. ($o[$rb.'_comment'] ? div($o[$rb.'_comment'],'','class="' .$c.'Comment"') : ''),$css_id,'class="'.$c . $css_class.'"');
		}
		$css_class = $report['css_class'];
		$css_id = $report['css_id'];
		return div( (!be_null($report['report_header']) ? html_heading_4(webify($report['report_header']),' class="reportHeader"') : '')
			. implode('<HR class="reportOutputSeparator">',$out)
			. (!be_null($footer) ? html_heading_4($footer,' class="reportFooter"') : '')
			. html_heading_4($sys_footer,' class="reportSysFooter"'),$css_id,'class="report $css_class"');
	}

	return report_generate_export($report, $template,$msg);
}


function report_system_variables() {
// Fixme, I wanted this is agency_config.php, but the UID info not available before it is included.
	$sys_vars = array(
		'confidential' => confidential('',0,'TEXT'),
		'staff_id'=>$GLOBALS['UID'],
		'org_name'=>org_name(),
		'org_name_short'=>org_name('short'),
		'today'=>dateof('now'),
		'today_wordy'=>dateof('now','WORDY'),
		'now'=>timeof('now'),
		'UID'=>$GLOBALS['UID'],
		'UID_NAME'=>staff_name($GLOBALS['UID']),
		'org'=>org_name('short'),
		'target_date'=>dateof(target_date()),
		'target_date_wordy'=>dateof(target_date(),'WORDY'),
		'target_month'=>dateof(target_date(),'MONTH'),
		'target_month_wordy'=>dateof(target_date(),'MONTH_WORDY'),
		'org_label'=>org_name());
	uksort($sys_vars, 'strlen_cmp');
	return $sys_vars;
}

function report_generate_export_template($report,$template,&$msg)
{
	if (!is_array($report['report_block'])) {
		outline("FIXME: non-array of report_block to report_Geneerate_export_template");
		// FIXME: delete
		page_close();
		exit;
	}
	// security
	if (!$report['override_sql_security'] && !is_safe_sql($report['report_block'],$errors,$non_select = false)) {
		$msg .= div($errors);
		return false;
	}
	// execute query
	$error = '';
	for ($x=0;$x<count($report['report_block']);$x++) {
	$cnt=0;
	foreach( $report['report_block'][$x]['report_block_sql'] as $s) {
		if (!($r=agency_query($s))) {
			$error .= oline("SQL error with $s.");
			$report['report_block'][$x]['values'][$cnt][][0]=orr($report['report_block']['message_if_error'],'This block generated an error');
			
		} elseif (in_array('O_TEMPLATE',$report['report_block'][$x]['suppress_output_codes'])) {
			$msg .= oline('(block output set to suppress)');
		} else {
			
			// fetch_all causing problems with NULL
			//$report['sql'][$x]['values'] = pg_fetch_all($r);
			$tmp_rpt_def=config_generic_sql(array(),$r);
			$data_types=array();
			foreach ($tmp_rpt_def['fields'] as $k=>$v) {
				$data_types[$k]=$v['data_type'];
			}
			$report['report_block'][$x]['data_types']=$data_types;
			if (sql_num_rows($r)===0) {
			  $report['report_block'][$x]['values'][$cnt][][0]=$report['report_block'][$x]['message_if_empty'];
			} else {
			  while ($tmp=sql_fetch_assoc($r)) {
			    $report['report_block'][$x]['values'][$cnt][]=$tmp;
			  }
			}				
		}
		$cnt++;
	}
	}
	if ($error) {
		$msg .= div($error,'',' class="error"');
		return false;
		// FIXME: message_if_error suggests continuing rather than returning...
	}
	if (!AG_OPEN_OFFICE_ENABLE_EXPORT) {
		$msg .= div(AG_OPEN_OFFICE_DISABLED_MESSAGE,'',' class="error"');
		return false;
	}

	set_time_limit(120); //fixme: can be set in report record
	if (preg_match(AG_REPORTS_REGEX_TEMPLATES,$template,$m)) {
		$template = $m[1];
		$group_by = $m[4];
	}
	require_once 'openoffice.php';
	$oo_file = template_merge($report,$template);

}

function report_generate_from_posted(&$mesg)
{
	$report = array();
	//FIXME:
	$report = get_report_from_db('AD-HOC_QUERY');
	$pre_sql=orr(detokenize($_REQUEST['sql_pre'],'generic_sql'),array());
	$saved_block=array_shift($report['report_block']);
	foreach ($pre_sql as $pre ) {
		$report['report_block'][]=array('report_block_sql'=>$pre,'execution_required'=>sql_true(),'suppress_output_codes'=>array('O_TEMPLATE'));
	}
	$saved_block['report_block_sql']=array(detokenize($_REQUEST['sql1'],'generic_sql'));
	$report['report_block'][]=$saved_block;
	$report['report_header'] = detokenize($_REQUEST["report_header"],'generic_sql');

	//fixme: this still relies on sql being acquired from the browser. even though it is checked
	//       it is still a dangerous idea.  
	//		 The SQL is now tokenized, so it shoud be safe(r?)
	//note: requested sql is tested in report_generate_openoffice() or report_generate_export()

	$template = detokenize($_REQUEST[AG_REPORTS_VARIABLE_PREFIX.'template'],'generic_sql');
	switch ($template) {
		//fixme: this is currently in too many places. make common function
	case 'sql_data_csv':
	case 'sql_data_tab':
	case 'sql_dump_full':
	case 'sql_dump_copy':
	case 'sql_dump_inserts':
break;
//		return report_generate_export($report['sql'],$template); // if succesful, this will exit the script
	case 'spreadsheet' :
		$template = AG_OPEN_OFFICE_CALC_TEMPLATE;
		break;
	default:
	}
	return report_generate_export($report,$template,$mesg);
}

function report_user_options_form($report)
{
	$out = formto();
	$out .= $report['report_code'] ? hiddenvar('report_code',$report['report_code']) : '';
	$out .= hiddenvar('action','generate');
	if (is_array($report['variables']) and count($report['variables'])>0) {
	$opt .= html_heading_tag('Select Report Options',2);
	foreach( orr($report['variables'],array()) as $p ) {
		$varname    = AG_REPORTS_VARIABLE_PREFIX . $p['name'];	
		$userprompt = $p['prompt'];	
		$comment    = $p['comment']; // fixme in parse_cfg_file
		$multi		= false; // reset for PICK / PICK_MULTI

		// store report variables for session
		$tokenized_types=array('PICK','PICK_MULTI');
		$form_val=$_REQUEST[$varname];
		$form_val=in_array($p['type'],$tokenized_types)
				? detokenize($form_val,report_token_context($p['name'],$report['report_code']))
				: $form_val;
		$default = $_SESSION['report_options_'.$report['report_code'].'_'.$varname] = 
			orr($form_val,$_SESSION['report_options_'.$report['report_code'].'_'.$varname]);
		
		switch ($p['type']) {
		case 'PICK_MULTI' :
			$multi=true;
			$is_first=true;
			// no break, continue
		case 'PICK' :
			$label =($userprompt ? bigger(bold($userprompt)) : '' ).  ($comment ? " $comment" : '');
			$cell = $multi ? '' : selectto($varname);
			foreach( $p['options'] as $li=>$lab) {
				// default is set a) if default is passed, and equals current option
				// or default is array, and $li is one of the keys
				//$defaulti = ( ( is_array($default) and in_array($li,array_keys($default)))
				$defaulti = ( ( is_array($default) and (in_array($li,$default) or ($default[$li]=='on')))
					or ($li==$default)
					// or, b) no default is passed, but default is configured to current option
					or ( (!$default) and ($li==$p['default']) ));

				// all_option for pick_multi, detect if first option is "all-ish" and should be used to select all
				// FIXME: this should work for most existing reports, but a better method would be to indicate it in the report variables
				// section.  If you assumed an all would always be first, then it could go something like PICK_MULTI_WITH_ALL, or
				// PICK_MULTI(ALL).  Second syntax would be better for supporting multiple options going forward.

				$all_option= ($multi and $is_first and in_array(trim(strtoupper($li)),array('ALL','ANY','-1')) and preg_match('/^(all|any)/i',$lab));
				$all_class=$all_option ? ' class="checkBoxAllOption"' : '';

				$cell .= ($multi)
					? span(formcheck($varname .'['.tokenize($li,report_token_context($p['name'],$report['report_code'])).']', ($defaulti ? 'checked="checked"' : ''),$all_class)
				 		. '&nbsp;'.$lab,' class="checkBoxSet"') . oline()
					: selectitem( tokenize($li,report_token_context($p['name'],$report['report_code'])),$lab,$defaulti);
				$is_first=false;
			}
			if ($multi) {
				$cell=span($cell,'class="checkBoxGroup"');
			} else {
				$cell .= selectend();
			}
			$var_opt[]=array($label,$cell);
			break;
		case 'DATE' :
				 $var_opt[] = array($userprompt,formdate($varname,orr($default,$p['default'],dateof('now'))));
				 break;
		case 'TIME' :
				 $var_opt[] = array($userprompt,formtime($varname,orr($default,$p['default'],timeof('now'))));
				 break;
		case 'TIMESTAMP' :
				 $var_opt[]= array($userprompt,oline(formdate($varname.'_date_',orr(dateof($default),$p['default'],dateof('now'))))
												. formtime($varname.'_time_',orr(timeof($default),timeof($p['default']),timeof('now'))));
				 break;
		case 'VALUE' :
		case 'TEXT' :
			$var_opt[] = array($userprompt,formvartext($varname,orr($default,$p['default'])));
			break;
		case 'TEXT_AREA':
			$var_opt[] = array($userprompt,formtextarea($varname,orr($default,$p['default'])));
			break;
		default :
			$var_opt[] = array(alert_mark('Don\'t know how to handle a ' . $p['type']),'');
		}
	}
	foreach ($var_opt as $vo) {
		$var_opt_rows.=row(cell($vo[0],'class="leftCell"').cell($vo[1],'class="rightCell"'));

	}
	$opt=table_blank($var_opt_rows,'','class="reportUserOptionSelect"');
	}

	// output options
	$opt .= table_blank(row(cell()) . row(cell(html_heading_tag('Choose Output Format',3)
			. report_output_select($report,true))));

	$out .= $opt;
	$out .= oline(). button('Submit','','','','','class="engineButton"');
	$out .= formend();
	$out .= oline() . hrule() .oline();
	return span($out,'class="reportUserOptionSelect"');
}

function report_output_select($report,$form=false)
{
	// Return an array of output options
	$labels['list']='Templates';
	$labels['x_items']='Export Formats';
	if (AG_OPEN_OFFICE_ENABLE_EXPORT) {
		foreach( $report['output_template_codes'] as $o) {
			$label=orr($o[1],$o[0]);
			$list[] = array($o[0],$label);
		}
	}
	//get csv, tab, sql dump options, if permissions correct
	$x_items=report_export_items();
	if (sql_false($report['allow_output_screen'])) {
		unset($x_items['screen']);
	}
	if ( ($report['allow_output_spreadsheet'] == sql_false()) or (!AG_OPEN_OFFICE_ENABLE_EXPORT)) {
		unset($x_items['spreadsheet']);
	}

	$result = array_merge($list,$other,$x_items);
	if (!$form) {
		return $result;
	}
	$out= selectto(AG_REPORTS_VARIABLE_PREFIX . 'template');
	foreach(array('list','other','x_items') as $grp) {
		foreach($$grp as $r) {
			$g[]= selectitem(tokenize($r[0],report_token_context('template',$report['report_code'])),$r[1],$r[0]==report_get_user_var('template',$report['report_code']));
		}
		if (count($g)>0) {
			$out .=html_optgroup(implode($g),$labels[$grp]);
		}
		$g=NULL;
	}
	$out .= selectend();
	return $out;
}

function report_token_context($var,$report_code) {

	return 'report_' . $report_code . '_' . $var;

}

function explot( $line )
{
	while ( preg_match('/^([^\s\"]+)\s?(.*)$/',$line,$matches) ||
		 preg_match('/^\s?\"(.*?)\"(.*)$/',$line,$matches)) {
			$split[]=$matches[1];
			$line=trim($matches[2]);
		} 
	return $split;
}

function report_generate_export( $report,$template,&$mesg) {

	/* template handling */
	switch ($template) {

	case 'sql_data_csv':
	case 'sql_data_tab':
	case 'sql_dump_full':
	case 'sql_dump_copy':
	case 'sql_dump_inserts':

		return report_generate_export_sql($report['report_block'][0]['report_block_sql'][0],$template,$mesg); // if succesful, this will exit the script

	case 'report_export_db_screen':
	case 'report_export_db_file':
		preg_match('/report_export_db_(.*)$/',$template,$matches);
		return  report_export_db($report['report_code'],$matches[1]);

	case 'spreadsheet' :

		if (in_array('O_SPREAD',$report['suppress_output_codes'])) {
			$mesg .= 'Generic spreadsheet option not allowed for this report.';
			return false;
		}
		if (!AG_OPEN_OFFICE_ENABLE_EXPORT) {
			$mesg .= AG_OPEN_OFFICE_DISABLED_MESSAGE;
			return false;
		}
		$template = AG_OPEN_OFFICE_CALC_TEMPLATE;
		break;
	default:
	}
	return report_generate_export_template($report,$template, $mesg);
}

function report_generate_export_sql($sql,$format,&$mesg)
{
	/*
	 *    Expects format to be one of:
	 *
	 *	case 'sql_data_csv':
	 *	case 'sql_data_tab':
	 *	case 'sql_dump_full':
	 *	case 'sql_dump_copy':
	 *	case 'sql_dump_inserts':
	 */
	if (!is_safe_sql($sql,$errors,$non_select = false)) {
		$message .= $errors;
		return false;
	}

	if (has_perm('sql_dump')) {
		preg_match('/^sql_(dump|data)_([a-z]*)$/',$format,$m);
		//header("Content-Type: text; charset=ISO-8859-1");
		//header("Content-Type: application/octet-stream");
		if ($m[1]=='data') {
			switch ($m[2]) {
				case 'csv' :
					$delimiter=',';
					$quotes=true;
					$c_type='text/csv';
					break;
				case 'tab' :
					$delimiter="\t";
					$quotes=false;
					$c_type='text/tab-delimited';
					break;
				default :
					// unknown format;
					$c_type='text/plain';
					break;
			}
			header('Content-Type: ' . $c_type);
			//header('Accept-Ranges: bytes');
			//header('Content-Transfer-Encoding: binary');
			//header('Pragma: public');
			header('Content-Disposition: attachment; filename="agency_data.csv"');
			$out=sql_data_export($sql,$delimiter,'',$quotes);
			//$len=strlen($out);
			//header('Content-Length: ' . $len);
			//header('Content-Range: bytes 0-' . ($len-1) . '/' . $len);
			out($out);
		} elseif ($m[1]=='dump') {
			header('Content-Disposition: attachment; filename="agency_sql_dump.sql"');
			out(sql_commentify($GLOBALS['AG_TEXT']['CONFIDENTIAL_STATEMENT']));
			out(sql_dump($sql,strtoupper($m[2])));
		}
		page_close($silent=true);
		exit;
	}

	$mesg .= oline(alert_mark('You aren\'t allowed to perform an SQL Dump'),4);
	return false;
}

function report_export_items()
{
	$options=array();
	$options[] = array('screen' , 'Show on screen');
	$options[] = array('spreadsheet', 'Generate spreadsheet');

	if (has_perm('sql_dump')) {
	  $options[] = array('sql_dump_inserts' , 'SQL Dump (insert commmands)');
	  $options[] = array('sql_dump_full'    , 'SQL Dump (column insert commands)');
	  $options[] = array('sql_dump_copy'    , 'SQL Dump (copy commands)');
	  $options[] = array('sql_data_csv'     , 'CSV file');
	  $options[] = array('sql_data_tab'     , 'Tab-delimited file');
	  $options[] = array('report_export_db_screen' , 'Export this report for sharing with another system (on screen)');
	  $options[] = array('report_export_db_file' , 'Export this report for sharing with another system (file download)');
	}
	return $options;
}

function report_export_db($report_code,$destination='screen') {
	// This generates the SQL needed to load a report into another AGENCY system
	if ( !(has_perm('sql_dump') and has_perm($report['permission_type_codes'])) ) {
		return alert_mark('You do not have the necessary permissions to export this report.');
	}
	$r_def=get_def('report');
	$rb_def=get_def('report_block');
	$report=get_report_from_db($report_code);
	$filter=array('report_code'=>$report_code);
	$unset_fields=array('changed_at','added_at','sort_order_id','sort_order_id_manual','report_id','report_block_id','block_count','last_generated_at','last_generated_by','quick_sql');
	$format='FULL';
	foreach(array($r_def,$rb_def) as $def ) {
		$table=$def['table_post'];
		$rep=get_generic($filter,list_query_order($def['list_order']),NULL,$def,$true);
		for ($x=0;$x<count($rep);$x++) {
			$rep[$x]['changed_by']='sys_user()';
			$rep[$x]['added_by']='sys_user()';
			$def['fields']['added_by']['data_type']='integer'; // changing field to integer is a trick/hack to avoid enquoting
			$def['fields']['changed_by']['data_type']='integer';
			if (array_key_exists('report_category_code',$def['fields'])) {
				$rep[$x]['report_category_code']='COALESCE( (SELECT report_category_code FROM l_report_category WHERE report_category_code='
				.enquote1(sql_escape_string($rep[$x]['report_category_code'])) . '),'.enquote1('GENERAL') . ')';
				$def['fields']['report_category_code']['data_type']='integer';
			}
		}

		foreach ($unset_fields as $f) {
			unset($def['fields'][$f]);
		}		
		$comment='Inserting ' . ( (count($rep) > 1) ? $def['plural'] : $def['singular']);
		$rep_f.=sql_build_inserts($def,$rep,$table,$format,$comment);
	}
	$comment='This is the SQL to load the ' . $report['report_title']  . ' report.'
		. "\nThis report was exported from AGENCY running at " . org_name() . ' at ' . datetimeof('now','US')
		. ' by ' . staff_name($GLOBALS['UID']) .'.';
	$final=sql_commentify($comment).$rep_f;
	if ($destination=='screen') {
		return div(webify($final),'','class="sqlCode"');
	} else {
		$filename='add.report.'.strtolower($report['report_code']).'.sql';
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		out($final);
		page_close(true);
		exit;
	}
}

function link_report($report_code,$label='',$init=array(),$action='',$template=null)
{
	/*
	 * Generate link to a report, by default to options page
	 * Use action="generate" to directly run report
	 * $init optionally pre-fills variables
	 * Specify template, esp. for generate
	 */

	$redirect=in_array($action,array('view','edit','delete','clone'));
	if (!($rep=get_report_from_db($report_code))) {
		out(div("warning: link_report couldn't find report $report_code",'','class="warning"'));
		return false; 
	}
	$r_def=get_def('report');
	$label=orr($label,$rep['report_title']);
	$key=$r_def['id_field'];
	$url = $GLOBALS['off']
			. ($redirect
				? 'display.php?control[object]=report&control[id]='.$report_code
				: AG_REPORTS_URL . '?' .$key.'='.$report_code);
	$url .= $action ? (($redirect ? '&control[action]=' : '&action=') . $action ): '';
	
	$tokenized_types=array('PICK','PICK_MULTI');  // FIXME
	foreach(report_parse_var_text($rep['variables'],false) as $v) {
		$r_var[$v['name']]=$v['type'];
	}
	if (!be_null($init) && is_assoc_array($init)) {
		// Template & Action specified in variable array
		// override.
		if (!isset($init['template']) and (!be_null($template))) {
			$init['template']=$template;
		}
		if ((!isset($init['action'])) and (!be_null($action))) {
			$init['action']=$action;
		}
		foreach ($init as $var => $val) {
			if (in_array($r_var[$var],$tokenized_types)) {
				$val=tokenize($val,report_token_context($var,$report_code));
			}
			$url .= '&'.AG_REPORTS_VARIABLE_PREFIX.$var.'='.$val;
		}
	} else {
		$url .= $template ?  '&'.AG_REPORTS_VARIABLE_PREFIX.'template='. tokenize($template,report_token_context('template',$report_code)) : '';
		$url .= $action ?  '&'.AG_REPORTS_VARIABLE_PREFIX.'action='. $action : '';
	}
	$perm = $rep['permission_type_codes'];
	//return hlink_if($url,$label,(be_null($perm) || ($perm==array()) || has_perm($perm)));
	return alt(hlink_if($url,$label,(be_null($perm) || ($perm==array()) || has_perm($perm))),$rep['report_title'].': '.$rep['report_comment']);
}

function track_report_usage($report)
{
	global $UID;
	$IP = $_SERVER['REMOTE_ADDR'];
	// FIXME: This defaults to screen, just as it does in the generate code
	// Really, that should be done once before both actions
	// The problem is that when a session times out, the token for output code
	// is sometimes no longer valid, leading to a null value and then a DB error on this insert
	$output=orr(report_get_user_var('template',$report['report_code'],'PICK'),'screen');
	$record = array('generated_by' => $UID,
			    'report_id' => $report['report_id'],
			    'report_code' => $report['report_code'],
			    'report_name' => $report['report_title'],
			    'output_format' => $output,
			    'generated_from' => $IP,
			    'added_by' => $GLOBALS["sys_user"],
			    'generated_at' => datetimeof("now","SQL"),
			    'changed_by' => $GLOBALS["sys_user"] );
	return agency_query(sql_insert('tbl_report_usage', $record));  
}

function list_report($control,$def,$control_array_variable='',&$REC_NUM)
{
		if ( ($recent=my_recent_reports()) ) {
			$lists['My Recent Reports']=$recent;
		}
		/* Custom formatting for report list */
		$order="COALESCE(report_category_code,'GENERAL'),report_title";
		$result = list_query($def,array(),$order,$control);
		if (($REC_NUM=sql_num_rows($result)) == 0 ) {
			$out = oline('No reports found');
		} else {
			for ($count=1;$count<=$REC_NUM;$count++) {
				$rep=sql_fetch_assoc($result);
				$sortkey = ucwords(str_replace('/',' / ',strtolower(value_generic(orr($rep['report_category_code'],'GENERAL'),$def,'report_category_code','list',false))));
				$comment=(($com=$rep['report_comment'])) ? span($com . toggle_label('comment...'),'class="hiddenDetail"') : '';
				$lists[$sortkey][]= html_list_item(link_report($rep['report_code'],$rep['report_title']) . ' ' . $comment);
				if ($rep['report_category_code']=='HIDDEN') {
					$hidden_value=$sortkey;
				}

			}
			foreach($lists as $sec => $list) {
				$item = html_heading_tag($sec,4) . html_list(implode('',$list));
				if ($sec==$hidden_value) {
					$hidden = $item;
				} else {
					$out .= $item;
				}
			}
		}
		$out .= $hidden ? oline() . span($hidden . toggle_label('Show hidden reports...'),'class="hiddenDetail"').oline() : '';
		$out .= oline() . smaller(italic(add_link('report','Add a new report')));
		return div($out,'','class="listMain listObjectReport listObjectReportCustom"');
}

function post_report($rec,$def,&$mesg,$filter='',$control=array()) {
	if (!be_null($filter) and ($filter!=array())) {
		return post_generic($rec,$def,$mesg,$filter,$control);
	}
	if (($new_rec=post_generic($rec,$def,$mesg,$filter,$control))) { // and ($sql=$rec['quick_sql'])) {
		$b_def=get_def('report_block');
		$control2=array();
		$rec_init=array('report_code'=>$rec['report_code'],'report_block_sql'=>$rec['quick_sql']);
		$block=blank_generic($b_def,$rec_init,$control2);
		if (post_generic($block,$b_def,$mesg,$filter,$control2)) {
			return $new_rec;
		} else {
			return false;
		}
	}

}

function my_recent_reports( $uid ) {
	// This returns an array, not a string as you might expect
	$uid=orr($uid,$GLOBALS['UID']);
	$def=get_def('report');
	$limit=$def['custom']['my_recent_reports_max'];
	if (!$limit) { return ''; }
	$reps=sql_to_php_array(sql_assign(sprintf('SELECT array( (SELECT report_code FROM (SELECT DISTINCT ON (report_code) report_code,generated_at FROM report_usage WHERE generated_by=%d AND report_code IS NOT NULL ORDER BY report_code,generated_at DESC) foo ORDER BY generated_at DESC LIMIT %d)) boo',$uid,$limit)));
	foreach($reps as $r) {
		$out[]=html_list_item(link_report($r));
	}
	return $out;
}


?>
