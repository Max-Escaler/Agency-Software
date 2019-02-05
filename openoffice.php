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
 * System variables available for merging into OO output
 * Should be in agency_config, but UID not available yet.
 */

function ooify($value)
{
       $cg_oo = $GLOBALS['AG_OPEN_OFFICE_TRANSLATIONS'];
       return str_replace(array_keys($cg_oo),array_values($cg_oo),htmlspecialchars($value,ENT_QUOTES));
}

function xml_parse_row( $row )
{
// get the contents of a row, and return an array of cells
	$x=0;
	$row_start_regexp = "<table:table-row\s?[^<]*>";
	$cell_start_regexp = "<table:table-cell\s?[^<]*>";
	$cell_empty_regexp = "<table:table-cell\s?[^<]*\/>";
	$row_end_regexp = "<\/table:table-row>";
	$cell_end_regexp = "<\/table:table-cell>";
	while ($row) // parse until gone
	{
		if ( ! preg_match("/^($cell_empty_regexp)(.*)/i",$row,$matches))
		{
			preg_match("/^($cell_start_regexp.*?$cell_end_regexp)(.*)/i",$row,$matches);
		}
		$cells[$x++]=$matches[1];
		$row=$matches[2];
	}
	return $cells;
}

function xml_parse_cell( $cell )
{
// Take a cell, and parse it into array of tags / content
	$tag_regexp = "<.*?>";
	$text_regexp = ".*?";
	$x=0;
	while ($cell)
	{
		if ( ! preg_match("/^($tag_regexp)(.*)/i",$cell,$matches))
		{
			preg_match("/^($text_regexp)(<.*)/i",$cell,$matches);
		}
		$contents[$x++]=$matches[1]; 
		$cell=$matches[2];
	}
	return $contents;
}

function xml_parse_tag( $tag )
{
// Take a tag, and parse it into array of attributes (inc. name)
	while ($tag <> "<>")
	{
		preg_match('/^<(.[^ >]*)[ ]?(.*)?>$/',$tag,$matches);
		$attr=$matches[1];
		$tag="<" . $matches[2] . ">";
		$value="";
		if (preg_match('/^(.*?)(="?(.*?)"?)?$/',$attr,$matches))
		{
			$attr=strtolower($matches[1]);
			$value=$matches[3];
		}
		$tags[$attr]=$value;
	}
	return $tags;
}

function xml_assemble_tag( $attrs )
{
// take an array of tag attributes, and assemble a tag
	foreach( $attrs as $key=>$value)
	{
		$str[$key]=$key . ($value ? "=\"$value\"" : "");
	}
	return "<" . implode(" ",$str) . ">";
}

function xml_assemble_cell( $tags )
{
// take an array of tags, and assemble a tag
	return implode("",$tags) . "\n";
}

function unzip( $zip_file )
{
	if (!function_exists('zip_open')) { //check for php-zip libraries
		die('zip functions appear to be missing from PHP');
	}

	if (!is_readable($zip_file)) {
		log_error('Function unzip() doesn\'t have access to '.$zip_file);
		page_close($silent=true);
		exit;
	}
	$zip = zip_open($zip_file);

	if ($zip) {
    	while ($zip_entry = zip_read($zip)) 
		{
	        if (zip_entry_open($zip, $zip_entry, "r")) 
			{
				$zip_files[zip_entry_name($zip_entry)]= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
        	    zip_entry_close($zip_entry);
	        }
    	}
	}
	zip_close($zip);
	return $zip_files;
}


function oo_preg_pattern( $pattern )
{
	// silly little function to wrap patterns for preg_replacing
	return "/\\\${$pattern}/";
}

function oo_preg_value( $value )
{
	// silly little function to wrap values for preg_replacing
	return preg_replace('/\n/s','</text:p><text:p>',htmlspecialchars(str_replace('$','\\$',$value)));
}

function oo_merge_set( $data_recs, $template_strings, $group_field="" )
{
		global $DEBUG;
		$result=array();
        $sys_vars=report_system_variables();
        $tot_recs=is_array($data_recs) ? count($data_recs) : sql_num_rows($data_recs);
        for ($count=0; $count < $tot_recs; $count++)
        {
			$template_values=array();
   	        $x = is_array($data_recs) ? array_shift($data_recs) : sql_fetch_assoc($data_recs);
			$x = array_merge($sys_vars,$x);
			$id = $x[$group_field];
			uksort($x,'strlen_cmp');
			$keys = array_map("oo_preg_pattern",array_keys($x));
			$values = array_map("oo_preg_value",array_values($x));
			foreach ($template_strings as $template_string)
			{
				$match=preg_replace($keys,$values,$template_string);
/*
if(preg_match('/between/i',$match))
{
	outline(webify("Matched: $match"));
	outline(webify("template: $template_string"));
	outline(red("Keys: " . dump_array($keys)));
	outline(green("Values: " . dump_array($values)));
}
*/
				$result["$id"].=$match;
			}
		}
$DEBUG && outline(red("RESULT: " . dump_array($result)));
		return $result;
}

function oowriter_merge_new( $data_recs, $template, $data_eval="",$file_replace="",$group_field="donor_id" )
{

	if (!AG_OPEN_OFFICE_ENABLE_EXPORT) {

		die(AG_OPEN_OFFICE_DISABLED_MESSAGE);

	}

// This is a quick, dirty hack to create an oowriter file
// Create a oowriter file that serves as a template
// put $START and $END tags in (i.e., on on each line)
// Will be replaced with the data in data recs, as
// processed by $data_eval string
// eval should refer to data elements in $x, as in $x["field"]

// If eval is null, it will loop through all the field in the record
// and replace $field with value, a la the current report writer

// eval doesn't work now with re-write
// it could be added back in, but we haven't been using it.

		$seps=array();
		$targs=array();
		// convert [legacy] result to array format:
		if (!is_array($data_recs))
		{
			$data_recs=array($data_recs);
		}
		$sets=count($data_recs);
		// Unpack file
        $file_replace=orr($file_replace,array());
    	$template=preg_match('/\/tmp\//s',$template)
        	? $template
	        : AG_TEMPLATE_DIRECTORY . '/' . $template;
	    $zip_files=unzip($template);
		$contents=$zip_files["content.xml"];
		// Look for start and end tags
        if ( ! preg_match('/^(.*?)(\$START)(.*)(\$END)(.*?)$/s',$contents,$matches))
        {
                outline("I can't find \$START and \$END tags in the template file $template.  Unable to proceed");
                return false;
        }
        $doc_head=$matches[1];
        $doc_tail=$matches[5];
        $query_target=$matches[3];
        // Check for sub queries starting with $START.  ($REC-START and $REC-END OK as legacy)
        for ($x=0;$x<$sets;$x++)
		{
			if ( preg_match('/^(.*?)(\$(REC-)?START)(.*?)(\$(REC-)?END)(.*)$/s',$query_target,$matches))
			{
				array_push($seps,$matches[1]);
				array_push($targs,$matches[4]);
				$query_target=$matches[7];
			}
        }
		array_push($seps,$query_target); // final separator
		// do parent replacement first, special handling:
		$parent_recs=array_shift($data_recs);
		$sets--;
		if (is_array($parent_recs))
		{
			$save_parent_recs = $parent_recs;
		}
		else
		{
			$save_parent_recs=sql_fetch_to_array($parent_recs);
			$parent_recs=$save_parent_recs; // forces parent_recs to array, so that counter is not screwy
		}
		// Merge child records:
		for ( $x=0;$x<$sets;$x++)
		{
			$sep_values[$x]=oo_merge_set( $parent_recs, array($seps[$x]), $group_field );
			$recs=array_shift($data_recs);
			$child[$x]=oo_merge_set($recs, array($targs[$x]), $group_field );
		}	
		$sep_values[$x]=oo_merge_set( $parent_recs, array($seps[$x]), $group_field );
		// Assemble Document:
		$new_doc = $doc_head;
		for ($x=0;$x<count($save_parent_recs);$x++)
		{
			$id=$save_parent_recs[$x][$group_field];
			for ($y=0;$y<=$sets;$y++)
			{
				$new_doc .= $sep_values[$y][$id] . $child[$y][$id];
			}
			$new_doc .= $sep_values[$y][$id];
		}
		$new_doc .= $doc_tail;
        $zip_files["content.xml"] = $new_doc;
(0 or $DEBUG) && outline(webify("new contents = $new_doc"));
        $zip = new zipfile();
        foreach ($zip_files as $name=>$data)
        {
				//replace filenames (for photo attachments)
                if (array_key_exists($name,$file_replace))
                {
                        $data=$file_replace[$name];
                }
                $zip->addfile($data,$name);
        }
        return $zip;
}

function oowriter_merge( $data_recs, $template, $data_eval="",$file_replace="",$group_field="" )
{

	/*
	 * Create a oowriter file that serves as a template
	 * put $START and $END tags in (i.e., on on each line)
	 * Will be replaced with the data in data recs, as
	 * processed by $data_eval string
	 * eval should refer to data elements in $x, as in $x["field"]
	 *
	 */

	if (!AG_OPEN_OFFICE_ENABLE_EXPORT) {

		die(AG_OPEN_OFFICE_DISABLED_MESSAGE);

	}

	// If eval is null, it will loop through all the field in the record
	// and replace $field with value, a la the current report writer

	$file_replace=orr($file_replace,array());
	$template=preg_match('/\/tmp\//s',$template)
		? $template
		: AG_TEMPLATE_DIRECTORY . '/' . $template;
	$zip_files=unzip($template);
	$contents=$zip_files['content.xml'];
	$styles=$zip_files['styles.xml'];

	if ( ! preg_match('/^(.*)(\$START)(.*?)(\$END)(.*)$/s',$contents,$matches)) {

		outline("I can't find \$START and \$END tags in the template file $template.  Unable to proceed");
		return false;
	}

	$head = $matches[1];
	$tail = $matches[5];
	$sep  = $matches[3];

	if (preg_match('/^(.*)?(\$SKIP-START)(.*)(\$SKIP-END)(.*)?$/s',$sep,$matches)) {

		$tail = $matches[5] . $tail;
		$skip_last=$matches[3];
		$sep=$matches[1];

	}

	// Check for $REC-START and $REC-END tags, for grouping
	if ( $group_field && preg_match('/^(.*)(\$REC-START)(.*?)(\$REC-END)(.*)$/s',$sep,$matches)) {

		$grp_head = $matches[1];
		$grp_tail = $matches[5];
		$sep      = $matches[3];

		if (preg_match('/^(.*)?(\$SKIP-REC-START)(.*)(\$SKIP-REC-END)(.*)?$/s',$sep,$matches)) {

			$grp_rec_tail=$matches[3];
			$grp_tail=$matches[5] . $grp_tail;
			$sep=$matches[1];

		}

    }

	/*
	 outline(webify("Here is the orig: $contents"));
	 outline("Here are my matches: " . dump_array($matches));
	 outline("Data rec count: " . sql_num_rows($data_recs));
	 outline("Data eval = $data_eval");
	 outline("Group field = $group_field");
	 outline("Group head = " . webify($grp_head));
	 outline("Group tail = " . webify($grp_tail));
	 outline(webify("Sep = $sep"));
	*/

	$tot_recs = is_array($data_recs) ? count($data_recs) : sql_num_rows($data_recs);
	for ($count = 1; $count <= $tot_recs; $count++) {

		$x = is_array($data_recs) ? array_shift($data_recs) : sql_fetch_assoc($data_recs);
		$is_last=($count==$tot_recs);

		if ($count == 1) {

			/*
			 * Run this once, to replace in headers and footers
			 * You could use this to, for example, pass 'global vars' from query (runtime)
			 * That were the same for all records.
			 * Right now this will use the first record, but there should be no assumption
			 * that any particular record from the set will be the one evaluated.
			 * Example:
			 * SELECT 'HEADER' AS my_header_string,'SESSION ID' AS session_id,client_id, added_at FROM foo...
			 */

			foreach($x as $field=>$value) {

				$styles=preg_replace('/\$' . $field . '/',ooify($value),orr($styles,$styles));

			}

		}

		if ($group_field && ($x[$group_field]<>$old[$group_field])) { // Create new Header

			$new_group = true;
			$old_head  = $new_head;
			$old_tail  = $new_tail;

			if ($data_eval) {

				$new_head = ooify(eval( 'return ' . $data_eval . ';' )) . $grp_head;
				$new_tail = ooify(eval( 'return ' . $data_eval . ';' )) . $grp_tail;

			} else {

				$new_head = $grp_head;
				$new_tail = $grp_tail;

				foreach ($x as $field=>$value) {

					//outline("Field = $field, Value = $value");
					$new_head=preg_replace('/\$' . $field . '/',ooify($value),$new_head);
					$new_tail=preg_replace('/\$' . $field . '/',ooify($value),$new_tail);

				}

			}

		} else {

			$new_group = false;

		}

		if ($data_eval) {

			$new_rec = htmlspecialchars(eval( 'return ' . $data_eval . ';' )) . $sep;

		} else {

			$new_rec = $sep;

			foreach ($x as $field=>$value) {

				$new_rec = str_replace('$' . $field ,ooify($value),$new_rec);

			}

		}

		if (!$group_field) {

			$new_contents .= $new_rec;

		} elseif ( $is_last ) {

			if ($new_group) {

				// assemble previous group, plus new, last group
				$new_contents .= $old_head . $new_recs . $old_tail . $new_head . $new_rec . $new_tail;
				$new_recs=""; // shouldn't be needed

			} else {

				// get last record into group and assemble
				$new_recs .= $grp_rec_tail . $new_rec;
				$old_head = $new_head;
				$old_tail = $new_tail;
				$new_contents .= $old_head . $new_recs . $old_tail;
				$new_recs = "";

			}

		} elseif ($new_group) {

			// Assemble old group
			$new_contents .= $old_head . $new_recs . $old_tail;
			$new_recs = "";

		} else {

			// simply add new record within group
			$new_recs .= $grp_rec_tail;

		}

		$new_recs .= $new_rec;
		$old = $x;

		if ( !( $is_last || ($count==1) || (!$new_group) )) {

			$new_contents .= $skip_last;

		}

	}

	global $UID;
	$sys_vars=report_system_variables();
	$new_full= $head . $new_contents . $tail;
	foreach( $sys_vars AS $key=>$value ) {

		$new_full=preg_replace('/\$' . "$key/i",ooify($value),$new_full);
		$styles=preg_replace('/\$' . "$key/i",ooify($value),$styles);

	}

	// outline(webify("new contents = $new_contents"));
	$zip_files["content.xml"] = $new_full;

	$zip_files["styles.xml"] = $styles;

	//	outline("Here is content.xml: " . webify($zip_files["content.xml"]));

	$zip = new zipfile();
	//outline("File replacement array = " . dump_array($file_replace));

	foreach ($zip_files as $name=>$data) {

		//outline("Processing $name");
		if (array_key_exists($name,$file_replace)) {

			//outline("Replacing $name");
			$data=$file_replace[$name];

		}

		$zip->addfile($data,$name);

	}

	return $zip;

}

function template_merge( $data_sets, $template='',$extra_vars=array())
{
if ($debug) { outline(blue("data sets: " . dump_array($data_sets))); }
	if (!AG_OPEN_OFFICE_ENABLE_EXPORT) {
		die(AG_OPEN_OFFICE_DISABLED_MESSAGE);
	}
	require_once($off . 'bundled/tbs/tbs_class.php');
	require_once($off . 'bundled/tbs/plugins/tbs_plugin_opentbs.php');

	if ($template=='spreadsheet') {
		$template=AG_OPEN_OFFICE_CALC_TEMPLATE;
	}	
	$template = orr($template,AG_OPEN_OFFICE_CALC_TEMPLATE);
	$default_template = preg_match('/'. AG_OPEN_OFFICE_CALC_TEMPLATE.'/',$template);

	//FIXME: sloppy handling of temp directory?
    $template=preg_match('/\/tmp\//s',$template)
        ? $template
        : AG_TEMPLATE_DIRECTORY . '/' . $template;

	$global_vars=array_merge($extra_vars,report_system_variables());
	//$global_vars['debug_msg']=dump_array($data_sets);
	$blocks=$data_sets['report_block'];
	unset($data_sets['report_block']);
	$doc = new clsTinyButStrong;
	$doc->PlugIn(TBS_INSTALL, OPENTBS_PLUGIN);
	if ($default_template) {
		$load_string=$template;
	} elseif (preg_match('/new_monthly.*xls(x|m)/',$template)) {
		// FIXME: This is specific to a particular SPC Excel template
		//FIXME: Which files to load, and how to know?
		$load_string=$template;
		$sub_sheets=array(1,2,3,4);
//		$debug=true;
	} else {
		$load_string=$template;
		$default_block_merge='data'; // Temporary placeholder to keep compatibility with SPC templates
	}
	$doc->LoadTemplate($load_string);

	//$doc->SetOption('noerr','true');
	// Global blocks
//outline("Global vars = " . dump_array($global_vars));
//outline("Report vars = " . dump_array($global_vars));

/*
	// Process (Word) headers/footers
	$head_and_foot=$doc->Plugin(OPENTBS_GET_HEADERS_FOOTERS);
if ($debug ) {
	outline("Headers and footers: " . dump_array($head_and_foot));
}
*/

	if ($sub_sheets) {
		foreach($sub_sheets as $ss) {
			$doc->PlugIn(OPENTBS_SELECT_SHEET, $ss);
			$doc->MergeField('global',$global_vars);
			$doc->MergeField('report',$data_sets);
		}
	} else {
		$files=$doc->Plugin(OPENTBS_GET_OPENED_FILES);
		foreach ($files as $f) {
if ($debug) {
	outline("Doing $f");
}
			if ($doc->PlugIn(OPENTBS_SELECT_FILE, $f) ) {
				$doc->MergeField('global',$global_vars);
				$doc->MergeField('report',$data_sets);
			} else {
				if ($debug) {
					outline("Failed to select $f");
				}
			}
		}
		$doc->PlugIn(OPENTBS_SELECT_MAIN);
	}

	for ($x=0;$x<count($blocks);$x++) {
		$blocks[$x]['suppress_output_codes']=orr($blocks[$x]['suppress_output_codes'],array());  // FIXME!
		if (in_array('O_TEMPLATE',$blocks[$x]['suppress_output_codes']) or (!sql_true($blocks[$x]['is_enabled'])) or ($blocks[$x]['report_block_type_code']=='PIVOT')) {
			unset($blocks[$x]);
			$blocks=array_values($blocks);
			//$x--;
		}
	}

	if ($sub_sheets) {
		foreach($sub_sheets as $ss) {
			$doc->PlugIn(OPENTBS_SELECT_SHEET, $ss);
			if ($doc->GetBlockSource('sections')) {
				$doc->MergeBlock('sections',range(1,count($blocks)));
			}
		}
	} else {
			$doc->PlugIn(OPENTBS_SELECT_MAIN);
			if ($doc->GetBlockSource('sections')) {
				$doc->MergeBlock('sections',range(1,count($blocks)));
			}
	}

	$doc->MergeBlock('sections',range(1,count($blocks)));
	// FIXME: move me to a config file or something
	// This is now array(datatype(ope),format string)
	$type_conversions=array(
		//'float'=>array('num','0.00'),
		'float'=>array('num',NULL),
		'integer'=>array('num','0.'),
		AG_MAIN_OBJECT_DB =>array( 'num','0.'),
		'date'=>array('date','mm/dd/yy'),
		'time'=>array('time','rr:nn ampm'),
		'timestamp'=>array('date','mm/dd/yy rr:nn ampm'),
		'datetime'=>array('date','mm/dd/yy rr:nn ampm'),
	);

	// Report blocks
	for ($x=1;$x<=count($blocks);$x++) {
		// Template numbering starts at 1, arrays at 0
		$block=$blocks[$x-1];
		//$block_vals=$block['values'];
		// Do office doc-needed conversions, eg for line breaks
		// Fixme:  need to determine office doc type first, as conversion will vary
if ($debug) {outline('Block values: ' . dump_array($block['values']));}
		for($a=0;$a<count($block['values']);$a++) {
			$new_vals=array();
			for($b=0;$b<count($block['values'][$a]);$b++) {
				//$new_vals[$b]=str_replace("\n",'</text:p><text:p>',$block['values'][$a][$b]);
				$new_vals[$b]=$block['values'][$a][$b];
			}
if ($debug) {outline('New vals: ' . dump_array($new_vals));}
			// FIXME, hack for empty data coming through as single element null array
			if ( (count($new_vals[0])==1) and (!$new_vals[0][0])) {
				$new_vals=array();
			}

			$block_vals[$a]=$new_vals;
		}



		unset($block['values']);
if ($debug) {
	outline("Block values: " . dump_array($block_vals));
}

		if ($default_template) {
			// Block vars
			$doc->MergeBlock('section'.$x,array($block));
			// Block values
			$doc->MergeBlock("data$x",range(1,count($block_vals)));
			for ($y=1;$y<=count($block_vals);$y++) {
				$headers=array();
				$header_keys=array_keys($block_vals[$y-1][0]);
				//$headers=array_keys($block_vals[$y-1][0]);
				$header_keys_with_ope=array();
				foreach ($header_keys as $h) {
					$headers[]=ucwords(str_replace('_',"\n",$h));
					$data_type=$block['data_types'][$h];
//					$ope_type=$type_conversions[$data_type][0];
					$ope_format=$type_conversions[$data_type][1];
					if ( !$ope_format) {
						$ope_type=$type_conversions[$data_type][0];
					}	
					$header_keys_with_ope[]=$h. ($ope_type ? ';ope=tbs:' . $ope_type : '') . ($ope_format ? ';frm='.enquote1($ope_format):'');
					//is_ken() and ($header_keys=$header_keys_with_ope);
				}	

				//$doc->MergeBlock("headers$x-$y,headers{$x}-{$y}a",$headers);
				$doc->MergeBlock("headers{$x}-{$y}a",$header_keys_with_ope);
		 		$doc->MergeBlock("headers$x-$y",$headers);
				$doc->MergeBlock("values$x-$y",$block_vals[$y-1]);
			}
		} else {
			$block_name=orr($block['block_merge_name'],$default_block_merge,'b'.$x-1);
			// Only merge if the block exists
			// FIXME:  Using GetBlockSource is hack, since I don't know how to get a block list or test for existence more directly
//outline("Block = $block_name");
			if ($sub_sheets) {
				foreach($sub_sheets as $ss) {
					$doc->PlugIn(OPENTBS_SELECT_SHEET, $ss);
					if ($doc->GetBlockSource($block_name)) {
						$doc->MergeBlock($block_name,$block_vals[0]);
					}
				}
			} else {
				$doc->PlugIn(OPENTBS_SELECT_MAIN);
				if ($doc->GetBlockSource($block_name)) {
					$doc->MergeBlock($block_name,$block_vals[0]);
					$debug && outline("Found block $block_name");
				} else {
					$debug && outline("NOT Found block $block_name");
				}	
			}	
		}
	}
if ($debug) {
	outline("Load String = $load_string");
	outline();
	$doc->PlugIn(OPENTBS_DEBUG_INFO,false);
	outline();
	outline();
	$doc->PlugIn(OPENTBS_DEBUG_XML_CURRENT);
}
	office_mime_header(basename($template));
	$doc->Show(OPENTBS_NOHEADER + OPENTBS_DOWNLOAD );
	page_close($silent=true);
	exit;
	
}
function office_mime_header($filename)
{
// 7/21/14 update fleshing out various MS Office Mimetypes based on list from: http://filext.com/faq/office_mime_types.php
	preg_match('/^(.*)\.([a-z]{3,5})$/i',$filename,$matches);
	$ext=strtolower($matches[2]);
	$name=orr($matches[1],'agency_report');
	$filename=$name. '.' . $ext;
	switch ($ext) {
	// PDF Format:
		case 'pdf' :
			$type='application/pdf';
			break;

	// MS Excel Formats:
		case 'xls' :
		case 'xlt' :
		case 'xla' :
			$type='application/vnd.ms-excel';
			break;
		case 'xlsx' :
			$type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			break;
		case 'xltx' :
			$type='application/vnd.openxmlformats-officedocument.spreadsheetml.template';
			break;
		case 'xlsm' :
			$type='application/vnd.ms-excel.sheet.macroEnabled.12';
			break;
		case 'xltm' :
			$type='application/vnd.ms-excel.template.macroEnabled.12';
			break;
		case 'xlam' :
			$type='application/vnd.ms-excel.addin.macroEnabled.12';
			break;
		case 'xlsb' :
			$type='application/vnd.ms-excel.sheet.binary.macroEnabled.12';
			break;

	// MS Word Formats:
		case 'doc' :
		case 'dot' :
			$type='application/msword';
			break;
		case 'docx' :
			$type='application/vnd.openxmlformats-officedocument.wordprocessingml.document';
			break;
		case 'dotx' :
			$type='application/vnd.openxmlformats-officedocument.wordprocessingml.template';
			break;
		case 'docm' :
			$type='application/vnd.ms-word.document.macroEnabled.12';
			break;
		case 'dotm' :
			$type='application/vnd.ms-word.template.macroEnabled.12';
			break;

	// MS PowerPoint Formats:
		case 'ppt' :
		case 'pps' :
		case 'ppa' :
		case 'pot' :
			$type='application/vnd.ms-powerpoint';
			break;
		case 'pptx' :
			$type='application/vnd.openxmlformats-officedocument.presentationml.presentation';
			break;
		case 'potx' :
			$type='application/vnd.openxmlformats-officedocument.presentationml.template';
			break;
		case 'ppsx' :
			$type='application/vnd.openxmlformats-officedocument.presentationml.slideshow';
			break;
		case 'ppam' :
			$type='application/vnd.ms-powerpoint.addin.macroEnabled.12';
			break;
		case 'pptm' :
			$type='application/vnd.ms-powerpoint.presentation.macroEnabled.12';
			break;
		case 'potm' :
			$type='application/vnd.ms-powerpoint.template.macroEnabled.12';
			break;
		case 'ppsm' :
			$type='application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
			break;

	// LibreOffice/OpenOffice Formats:
		case 'odt' :
		case 'sxw' :
			$type='application/vnd.sun.xml.writer';
			break;
		case 'ods' :
		case 'sxc' :
			$type='application/vnd.sun.xml.calc';
			break;

	// Default:
		default :
			$type='application/octet-stream';
	}

	header ( 'Content-type: ' . $type);
	header ( 'Content-Disposition: attachment; filename=' . $filename );
	header ( 'Content-Description: AGENCY Generated Office Data' );
	return;
}

function serve_office_doc($doc,$filename) {
	// serve an office file, externally converted if necessary, and exit

	$need_conversion_formats=array('pdf','xls','doc');
	$oo_to_ms=array(
		'sxw'=>'doc',
		'odt'=>'doc',
		'sxc'=>'xls',
		'ods'=>'xls'
	);

	$pi=pathinfo($filename);
	$ext=$pi['extension'];
	$base=$pi['basename'];
	if (preg_match('/(.*)\.'.$ext.'$/i',$base,$matches)) {
		$base=$matches[1];
	}
	if (AG_OPEN_OFFICE_EXTERNAL_CONVERSION_ENABLED) {
		if (AG_OPEN_OFFICE_PREFER_MS_FORMATS
			and array_key_exists($ext,$oo_to_ms)) {
			$ext=$oo_to_ms[$ext];
		}
		if (AG_OPEN_OFFICE_ALWAYS_PDF) {
			$ext='pdf';
		}
	}
	if (in_array(strtolower($ext),$need_conversion_formats)) {
		if (!AG_OPEN_OFFICE_EXTERNAL_CONVERSION_ENABLED) {
			log_error('External conversion not enabled');
			page_close();
			exit;
		}

		// FIXME: test for enabled
		$conv_file=tempnam(sys_get_temp_dir(),$base);
		$conv_file2=$conv_file.".$ext";
		rename($conv_file,$conv_file2);
		file_put_contents($conv_file2,$doc->data());
		office_mime_header($base.'.'.$ext);
		passthru("/usr/bin/unoconv --stdout -f $ext $conv_file2");
		unlink($conv_file2);
	} else {
		office_mime_header($filename);
		echo($doc->data());
	}
	page_close($silent=true); //no footer on oo files
	exit;	
}

function oo_get_upload_template($var_name)
{
	//returns the out_form variable, suitable for the template_merge() function
	global $UID;
	if ($_FILES[$var_name]['error']=='0') { //successful upload
		$name = $_FILES[$var_name]['name'];
		$type = array_pop(explode('.',$name));
		$file = '/tmp/'.$UID.'AgencyTemplate.'.$type;
		$res = move_uploaded_file($_FILES[$var_name]['tmp_name'],$file);
		if (!$res) {
			out('Failed to move uploaded file');
			exit;
		}
	} elseif ($_FILES[$var_name]['error'] == UPLOAD_ERR_NO_FILE) { //no file uploaded
		$file = null;
	} else {
		out('File upload error '.$_FILES[$var_name]['error']);
		exit;
	}
	return $file;
}

function is_oo_writer_doc($file)
{
	if (preg_match('/.(sxw|odt)$/i',$file,$m)) {
		return $m[1];
	}
	return false;
}

function is_oo_calc_doc($file)
{
	if (preg_match('/.(sxc|ods)$/i',$file,$m)) {
		return $m[1];
	}
	return false;
}

?>
