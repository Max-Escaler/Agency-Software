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

function sql_set($vals)
{
    foreach($vals as $key => $value)
    {
	    if (strtoupper(substr($key,0,6)) == "FIELD:")
	    {
		    $key=substr($key,6);
	    }
	    elseif ($value || (is_numeric($value) && ($value==0)))
	    {
		    $value = sql_escape_literal($value);
	    }
	    else
	    {
		    $value="NULL";
	    }
	    $fields .= "$key=$value,";
    //        outline("sql_update key=value:  $key=$value");
    }
   	$fields = substr($fields,0,strlen($fields)-1);
	return $fields;
}

function sql_update($table,$vals,$filter,$returning = false)
{
	$query = "UPDATE $table SET " . sql_set($vals) . " WHERE " . read_filter($filter);
	
	if ($returning && !sql_supports_returning() ) {
		log_error('RETURNING method not supported by Postgresql version < 8.2');
		die;
	}

	if ($returning) {

		$query .= ' RETURNING '.$returning;

	}

	return $query;
}

function sql_insert($table,$vals,$returning = false)
{
	$query = "INSERT INTO $table ";
	
	foreach($vals as $key => $value) {

		//FIXME: what the heck is this all about?
		if (! in_array($key, array('Title') ) ) {

			if (strtoupper(substr($key,0,6)) == 'FIELD:') {

				$key=substr($key,6); // remove FIELD:
				$values .= "$value,";

			} elseif ( (!$value) && ( !is_numeric($value))) {

				$values.='NULL,';

			} else {

				$values .= sql_escape_literal($value). ",";

			}

			$labels .= "$key,";

		}
	}

	// chop off last comma
	$labels = substr($labels,0,strlen($labels)-1);
	$values = substr($values,0,strlen($values)-1);
	$query .= " ($labels) VALUES ($values) ";

	if ($returning && !sql_supports_returning()) {
		log_error('RETURNING method not supported by Postgresql version < 8.2');
		die;
	}

	if ($returning) {
		$query .= ' RETURNING '.$returning;
	}

	return $query;
}

function sql_delete($table,$filter,$method="DELETE")
{
	/*
	 * method can be "DELETE" or "MARK"
	 * DELETE does an actual delete
	 * MARK sets is_deleted to true, deleted_at to current_timestamp, and deleted_by to UID
	 */
	global $UID;
	switch ($method) {

		case 'DELETE' :
			return "DELETE FROM $table WHERE " . read_filter($filter);
		case 'MARK' :
			return sql_update($table,array('is_deleted'=>sql_true(),
								 'deleted_by'=>$UID,
								 'FIELD:deleted_at'=>'current_timestamp'), $filter);
		default :
			log_error("sql_delete: Unknown delete method: $method");
	}
}

function sql_true( $bool="generate" )
{
// evaluate whether a boolean from (Pg)SQL is true, and return true/false
// if used w/o argument, generate a true for use in inserts,updates,etc.

	switch (strtolower($bool))
    {
		case 't' :
		case 'true' :
		case 'y' :
		case 'yes' :
		case 'on' :
		case '1' :
			return true;

		case 'f' :
		case 'false' :
		case 'n' :
		case 'no' :
		case 'off' :
		case '0' :
			return false;

		case '' :
			return null;
		case "generate" :
			return 't';
		default :
			outline("Warning--non-boolean ($bool) passed to sql_true");
			return;	
     }
}

function sql_false( $bool="generate" )
{
// evaluate whether a boolean from (Pg)SQL is false, and return true/false
// if used w/o argument, generate a true for use in inserts,updates,etc.

// Avoiding redundant code and consistency by using sql_true for the specific tests.

	switch (strtolower($bool)) {
		case 'generate' :
			return 'f';
		case '' :
			return null;
		default :
			$a = sql_true( $bool );
			if ($a===false) {
				return true;
			} elseif ($a===true) {
				return false;
			}
			outline("Warning--non-boolean passed to sql_false");
			return;	
	}
}

function agency_query( $select, $filter="", $order="",$limit="", $offset='',$group='')
{
// Main AGENCY query function--should be used for all queries.
// define $query_display to see queries.
    $sql=make_agency_query( $select, $filter, $order,$limit,$offset,$group);
    $old_err = error_reporting(0);
    $res=sql_query( $sql ) or sql_warn( 'Couldn\'t query with: '.webify_sql($sql) );
    error_reporting($old_err);
    return $res;
}

// called only by agency_query above
function make_agency_query( $select, $filter="", $order="", $limit="", $offset='',$group='')
{
    $sql = rtrim($select,'; ') . ' ' //remove right-most semi-colon to avoid filter and order breakage
        . (($w=read_filter($filter)) ? "WHERE $w " : "")
        . ($group ? (" GROUP BY " . implode(',',$group['fields']) . " ") : "")
        . ($order ? "ORDER BY $order " : "")
	. ( ($limit || $offset) ? sql_limit($limit,$offset) : "");
	return $sql;
}

function reverse_op( $op )
{
	switch ($op)
	{
		case "AND" :
			return "OR ";
		case "OR " :
		case "OR"  :
			return "AND";
		case "<>" :
			return "=";
		case "="  :
			return "<>";
		case "IS NULL" :
			return "IS NOT NULL";
		case "IS NOT NULL" :
			return "IS NULL";
		case "IN" :
			return "NOT IN";
		case "NOT IN" :
			return "IN";
		case "LIKE" :
			return "NOT LIKE";
		case "NOT LIKE" :
			return "LIKE";
		case "ILIKE" : // postgres support
			return "NOT ILIKE";
		case "NOT ILIKE" :
			return "ILIKE";
	}
}

function read_filter( $filter, $bool="AND", $lang="SQL")
{
// This function takes a filter array and converts it to 
// SQL. 
// (Note: adding $lang option, currently for human
// readable output, but could do other computer constructs too.

// $filter is an array of criteria in form $field=>$value
// preface fields with "!" to signify "NOT" 
// Operator defaults to "=" (Field='Value'), but other
// operators can be specified by prefixing the field.
// (i.e., $x[">=:Field"]="Value";
// Operators are listed below.
// with [!]NULL: to do null/not null (ignores value)

// outline("Here is the filter: "); dump_array( $filter );

// this is confusing, so here's an attempt at documentation (for myself!)

// SELECT ... WHERE $clause
// Our job is to return $clause, based on filter

// Normal case, $field=>$value, translates to "$field='$value'"
// We have some other cases, too.  "!" in front translates to "$field<>'$value'"
// "NULL:$Field" ==> "$field IS NULL"
// "!NULL:$Field" ==> $field IS NOT NULL"
// "OVERLAPS:$field" ==> Complicated date overlap function, takes daterange as $value
// If value is array, read_filter should be called recursively, and the
// results wrapped in parentheses.  This should reverse the boolean
// (the only reason this function would be needed anyway), so that
// you would get clause1 AND (subclause1 OR subclause2) AND...
// So a general case is "$field $op '$value' $bool (clause 2,3...)"
// I think the OVERLAP function might need special handling.
// the default $op is "=", but others can be passed as in:
// ">:$Field"=>"$value"
// Here is a list:
// =
// >
// <
// >=
// <=
// <>
// ~
// LIKE
// NOT LIKE
// ILIKE
// NOT ILIKE
// NULL
// NOT NULL
// BETWEEN (takes daterange) 
//		(now also takes literal string--must enquote values yourself)
//		(also takes array--first two values used)
// OVERLAPS (takes daterange)
// OVERLAPSORNULL (takes daterange) (same as overlaps, but start date can be null)
// IN
// NOTIN
// FIELD (says you are comparing two fields.  Practical effect is to not enquote value)
// FIELD IS LOOKED AT FIRST (Right after "!"), and taken off, so that FIELD>= or FIELDLIKE are valid
// (that's all I can think of, but we could add others later).
// no reverse operator for BETWEEN AND OVERLAP 
// to avoid confusion, let's separate the operator from the field with a ":"

	if ($lang=="human")
	{
		return oline("I (read_filter) have been asked to explain a query in language understandable by humans, but I don't know how!");
	}
	// cycle through each filter element
	if ( (! $filter) || ($filter==array()) ) // blank filter returns blank string or empty array
	{
		return "";
	}
    foreach ($filter as $field=>$value)
    {
		$op = "=";	// default case
		$field_flag="";
		// First, handle the null stuff
		if (substr($field,0,1)=="!")
        {
        	$field=substr($field,1); // take off the "!"
			$sql .= " NOT ";
		}
		// operator specified?  If so, set & delete from $field
		if (strstr($field,":"))
		{
			$tmp = explode( ":",$field);
			// starts with field?  If so, set flag for no quotes (see notes at top)
			if (substr($tmp[0],0,5)=="FIELD")
			{
				$field_flag="Y";
				$tmp[0]=substr($tmp[0],5); // and take off
			}
			$op = orr($tmp[0],"="); // hack to revert back to default if just "FIELD:" specified
			$field = $tmp[1];
			$field = preg_replace('/<----->/','::',$field); //added for casting to different types
			unset($tmp);
		}
		$field_safe=sql_escape_identifier($field);
		// If the escaped field is the same as the original,
		// use the original w/o quotes
		if ($field_safe==('"'.$field.'"')) {
			$field_safe=$field;
		}
		if (is_array($value) && is_assoc_array($value)) // if associative, call recursively
		{
			$sql .= "(" . read_filter($value, reverse_op($bool)) . ") \n$bool ";
			continue;
		}
		elseif (is_array($value)) { // if array of values, compare all to field
			$value_safe=array();
			foreach ($value as $t_val) {
				$value_safe[]= sql_escape_literal($t_val);
			}
			if (($op=="IN") || ($op=="NOT IN")) {
				$sql .= "$field_safe $op (" . implode(',',$value_safe) . ") \n$bool ";
			} elseif ( ($op == 'ARRAY_EQUALS')  or ($op == 'ARRAY_CONTAINS') ) {
				// I think for ARRAY_CONTAINS, this should be OR
				$tmp_bool= $op=='ARRAY_CONTAINS' ? 'OR' : 'AND';
				$t_sql = array();
				foreach ($value_safe as $t_val) {
					$t_sql[] = $t_val." = ANY ($field_safe)";
				}
				$t_eq = '';
				if ($op == 'ARRAY_EQUALS') {
					$t_eq = ' AND ARRAY_COUNT('.$field_safe.') = '.count($value);
				}
				$sql .= '('.implode(" $tmp_bool ",$t_sql).$t_eq.')'."\n$bool ";
			} else {
				$sql .= "($field_safe $op" . implode( " ".reverse_op($bool)." $field_safe$op", $value_safe ). ") \n $bool ";
			}
			continue;
		}
		else // this is regular old field & value
//			$value = addslashes($value); // get this out of the way before we forget
		{
		switch($op)
		{
		case "BETWEEN" :
			if (is_string($value))
			{
				// nothing to be done in this case, string is passed literally
				// caller is responsible for enquoting

				// Actually, this creates an SQL injection problem
				// So we're going to (try to) break apart the string and escape and quote each side
				// This might break or yield imperfect results if "AND" is part of either argument
				// which seems unlikely.  Mostly will be dates or numeric values,
				// but name_last BETWEEN A AND AND B AND will parse to BETWEEN 'A' AND 'AND B AND'
				// At least for now, I will lose no sleep over this

				// FIXME: This will also break if the values are already enquoted.
				// From a quick glance at the code, this is not currently being done except once in
				// clinical.php, which is not really being used.
				// Need a good regex to safely detect this.
				// Or could say if field: specified, leave it alone

				if (preg_match('/^(.*)\sAND\s(.*)$/i',$value,$matches)) {
					$value =sql_escape_literal($matches[1]) . ' AND ' . sql_escape_literal($matches[2]);
				} else {
					// Discard if no match.  It will either break the query anyway, or could be malicious
					break;
				}
			}
			else
			{
				if (@get_class($value)=="date_range") 
				{
					$d1= $value->start;
					$d2= $value->end;
				}
				elseif (is_array($value))
				{
					$d1= array_shift($value);
					$d2= array_shift($value);
				}
				if ( ! $field_flag)
				{
					$d1=sql_escape_literal($d1);
					$d2=sql_escape_literal($d2);
				}
				$value = "$d1 AND $d2";
			}
			$sql .= "$field_safe BETWEEN $value";  
			break;
		case "OVERLAPS" :
		case "OVERLAPSORNULL" :
		// daterange overlap
		{
			$f=explode(",",$field);
			$f1 = sql_escape_identifier($f[0]);
			$f2 = sql_escape_identifier($f[1]);
			if ($f1=='"'.$f[0].'"') {
				$f1=$f[0];
			}
			if ($f2=='"'.$f[1].'"') {
				$f2=$f[1];
			}

			if (@get_class($value)=="date_range")
			{
				$d1 = sql_escape_literal($value->start);
				$d2 = sql_escape_literal($value->end);
			}
			else
			{
				$d=explode(",",$value);
				$d1=sql_escape_literal(dateof($d[0],'SQL'));
				$d2=sql_escape_literal(dateof($d[1],'SQL'));
			}
			if (! ($d1 || $d2))
			{
				break;
			}
			$sql .= " $not \n ";
			if ($op=="OVERLAPSORNULL")
			{
				//$sql .= $d2 ? "((($f1<=$d2) OR ($f1 IS NULL))\n      AND " : "(";
				//$sql .=	"    (($f2>=$d1) OR ($f2 IS NULL)) )";
				$sql .= $d2 ? "((($f1 IS NULL) OR ($f1<=$d2))\n      AND " : "(";
				$sql .=	"    (($f2 IS NULL) OR ($f2>=$d1)) )";
			}
			else
			{
				$sql .= $d2 ? "(($f1<=$d2) \n      AND " : "(";
				$sql .=	"    (($f2>=$d1)))";
			}
//			$sql .=	"    (($f2>=$d1) OR ($f2 IS NULL)) )";
			break;
		}
		case "NULL" :
		case "NOT NULL" :
			$sql .="$field_safe IS $op";
			break;
		default :
			if ( ! $field_flag)
			{
				$value=sql_escape_literal($value); // no quotes around value (because it's a field name)
			}	
            $sql .= "$field_safe $op $value";
		}
		}
		$sql .= " \n$bool ";
	}
	// take off last "AND":
    $sql = substr($sql,0,strlen($sql)-4);
    return $sql;
}

function do_radio_sql($query,$var,$add_null=false,$default='',$spacer='')
{
	$result = agency_query($query);

	while ($a = sql_fetch_assoc($result)) {

		/*
		 * For the <label for="{$el_id}"> tag, which allows users to click
		 * on the label to change the associated radio button.
		 */
		$el_id = htmlentities($var.$a['value']);

		$out .= span(formradio($var, $a['value'], ($default==$a['value']) ? 'checked="checked"' : '',' id="'.$el_id.'"')
				 . '&nbsp;'.html_label($a['label'],$el_id),' class="radioButtonSet"') . $spacer;
	}
	if ($add_null) {
		$out .= formradio_wipeout($var);
	}
	return $out;
}

function do_checkbox_sql($query,$var,$default='',$spacer='',$skip_control=false)
{
	$result = agency_query($query);

	!is_array($default) && ($default=array($default));

	while ($a = sql_fetch_assoc($result)) {
		$out .= span(formcheck($var.'['.$a['value'].']', (in_array($a['value'],$default) ? 'checked="checked"' : ''))
				 . '&nbsp;'.$a['label'],' class="checkBoxSet"') . $spacer;
	}
	$skip_class=$skip_control ? ' skipSelectorControl' : '';
	return span($out,'class="checkBoxGroup' . $skip_class . '"');
}

function do_pick_sql( $query, $default_value="",$add_null=false, $format='',$form_pre="" ,$opts='')
// This function takes an SQL query that should return (at least) two fields:
// value and label.  The resulting array is fed into a pick box.

// format hacks:
// checkbox provides a bunch of checkboxes, so you could select many.  Also takes $form_pre
// array provides clean return, which is probably silly as there
// are easier ways of getting a query into an array

{
//	outline("Query = $query");
	$format=orr($format,'picklist');
	!is_array($default_value) && ($default_value=array($default_value));
	$result = agency_query($query);
	$add_null && ($null = selectitem('','(none or n/a or blank)') );
	while ($item=sql_fetch_assoc($result))
	{
		if (array_key_exists('grouping',$item)) {
			$group=true;
			$g=$item['grouping'];
			if ($g != $g_old) {
				if ($out) {
					$g_out[] = html_optgroup($out,$g_old);
					$out='';
				}
				$g_old=$g;
			}
		}
		$is_default = in_array($item['value'],$default_value);
		if ($format=='picklist')
		{
			$out .= selectitem($item['value'],$item['label'],$is_default,$opts);
		}
		elseif ($format=='checkbox')
		{
			$out .= oline(formcheck("{$form_pre}[{$item['value']}]",$is_default,$opts) . ' ' . $item['label'] . ' (' . $item['value'] . ')');
		}
	}
	if ($group) {
			$g_out[] = html_optgroup($out,$g);
	}

	$result =  $null . ($group ? implode('',$g_out) : $out);
	return ($format=='checkbox') ? span($result,'class="checkBoxGroup"') : $result;
}

function sql_assign( $select,$filter='', $order='',$limit='', $offset='' )
{
// Takes select and filter, does a query
// that should return one row, with one field
// which is the value that is returned.

	$result = sql_fetch_row(agency_query($select,$filter,$order,$limit,$offset));
	return $result[0];
}

function sql_lookup_description($code,$l_table,$v_field='',$d_field='description')
{
	$v_field = orr($v_field,substr($l_table,2).'_code');
	$sql = "SELECT {$d_field} FROM {$l_table}";
	$filt = array($v_field=>$code);
	$res = agency_query($sql,$filt,'','1');
	$a = sql_fetch_assoc($res);
	return $a[$d_field];
}

function sql_lookup_value_exists($value,$table,$v_field='')
{
	$v_field = orr($v_field,substr($table,2).'_code');
	if (!is_array($value)) {
		$value = array($value);
	}

	$sql = "SELECT TRUE FROM {$table}";
	$exists = true;
	foreach ($value as $t_v) {
		$filter = array($v_field=>$t_v);
		$res = agency_query($sql,$filter,'','1');
		$exists = $res ? $exists and sql_num_rows($res)==1 : false;
	}

	return $exists;
}

function count_rows( $table, $filter="" )
{
// counts the number of records in a table
    $sql = "SELECT COUNT(*) from $table";
    $count = sql_fetch_row(agency_query($sql,$filter));
    return $count[0];        
}

// $records is a result from a sql_query()
function display_recs($records)
{
    $tot_recs = sql_num_rows($records);
    if ($tot_recs > 0)
    {
        $output = oline("Total Records returned:  $tot_recs");
        $output .= tablestart("display","BORDER=1");
		$first=true;
        for ($x=0;$x<$tot_recs;$x++)
        {
			$row_out="";
			$row = sql_fetch_assoc($records);
            while(list($key,$value) = each($row))
            {
	    		if ($first)
				{
					$header .= boldcell($key);
				}
                $row_out .= cell("$value");
            }
			$first=false;
            $rows .= row($row_out);
        }
        $output .= row($header) . $rows . tableend();
    }
    else
    {
        $output = "0 records";
    }
    return $output;
}

function update_table_from_import($table,$imp_table,$key="",$skip_fields=array())
{
	$key=orr($key,$table."_id");
	$rec=sql_fetch_assoc(sql_query("SELECT * FROM $table limit 1"));
	if (! $rec )
	{
		log_error("update_table_from_import: $table has no records");
		return false;
	}
	foreach ($rec as $field=>$value)
	{
		if (! in_array($field,$skip_fields))
		{
			$where .= " ((t1.$field IS NULL AND t2.$field IS NOT NULL)
					OR
				      (t1.$field IS NOT NULL AND t2.$field IS NULL)
					OR
				      (t1.$field <> t2.$field))  \n OR ";

		}
	}
	$where=substr($where,0,strlen($where)-4);
	$query = "SELECT COALESCE(t1.$key,t2.$key) AS $key, t1.$key AS ${table}_$key, t2.$key AS ${imp_table}_$key
			 FROM $table AS t1 
				FULL OUTER JOIN $imp_table AS t2 USING ($key) 
			WHERE	(t1.$key IS NULL) 
				OR (t2.$key IS NULL)
				OR $where ";
	$updates=agency_query($query);
	for ($x=0;$x<sql_num_rows($updates);$x++)
	{
		$rec=sql_fetch_assoc($updates);
		if ($rec[$key]==$oldkey) // skip duplicate values for $key
		{
			$rec=sql_fetch_assoc($updates);
			$x++;
		}
		$filter=array("$key"=>$rec[$key]);
		if ($rec["${table}_$key"] && $rec["${imp_table}_$key"])
		{
			$newrec=sql_fetch_assoc(agency_query("SELECT * FROM $imp_table",$filter));
			sql_query(sql_update($table,$newrec,$filter));
		}
		elseif ($rec["${table}_$key"] && (! $rec["${imp_table}_$key"]))
		{
			sql_query(sql_delete($table,$filter));
		}
		elseif ( (!$rec["${table}_$key"]) && $rec["${imp_table}_$key"])
		{
			$newrec=sql_fetch_assoc(agency_query("SELECT * FROM $imp_table",$filter));
			sql_query(sql_insert($table,$newrec));
		}
		$oldkey=$rec[$key];
	}
}

function levenshteinMetaphoneDistance($string,$field)
{
	$string = sqlify($string);
      /*
	 * Returns a postgres statement suitable for insertion into a filter
	 *
	 * The CASE statement in the denominator is to prevent division by zero errors
	 */
      return "LEVENSHTEIN(METAPHONE($field,".METAPHONE_MAX_LENGTH."),
              METAPHONE('$string',".METAPHONE_MAX_LENGTH."))<----->numeric 
              / ( CASE WHEN LENGTH(METAPHONE('$string',".METAPHONE_MAX_LENGTH.")) = 0 THEN 1
                       ELSE LENGTH(METAPHONE('$string',".METAPHONE_MAX_LENGTH."))
                  END )<----->numeric";
}

function call_sql_function()
{
	$args = func_get_args();
	$func = array_shift($args);
	if (!$func) {
		outline ('call_sql_function() requires a function name');
		return false;
	}
	$sql_args = implode(',',$args);
	$res = agency_query("SELECT {$func}({$sql_args})");
	$t = sql_fetch_row($res);
	return $t[0];
}

function toggle_query_display($state=NULL)
{
	global $query_display;
	$query_display = orr($state,$query_display) ? null : 'Y';
	return true;
}

function sql_nextval($seq)
{
	$res = sql_query('SELECT NEXTVAL('.sql_escape_literal($seq).')');
	$tmp = sql_fetch_assoc($res);
	return $tmp['nextval'];
}

function webify_sql($sql)
{
	if (is_array($sql)) {
		$sql = implode(";\n\n",$sql);
	}
	$temp = explode(' ',trim($sql));
	$new_sql = '';
	$count = count($temp);
	for ($i=0; $i<$count; $i++) {
		$word = $temp[$i];
		$next_word = $temp[$i+1];
		if (in_array(strtolower($next_word),array('from','where','order','group'))) {
			$line = "\n";
		} else {
			$line = ' ';
		}
		$new_sql .= $word.$line;
	}
	return syntaxify(webify($new_sql),'SQL');
}

function sql_to_php($sql,$mapping)
{
	/* attempts to convert an SQL statement into an executable PHP statement
	 * 
	 * $mapping is an array of the form ("sql_value"=>"php_value")
	 * 
	 * the double replacement via md5 sums avoids ">=" being translated to ">=="
	 *
	 * FIXME: this currently isn't in use, as it is not thoroughly tested, and there is no good way
	 * to check whether the resulting php-code is executable. The end goal is to automatically read
	 * db constraints for validity checks.
	 *
	 */

	$default_sql_replacement = array('>=' => '>=',
						   '<=' => '<=',
						   '='  => '==');

	foreach ($default_sql_replacement as $t_s => $t_p) {
		$default_mapping[$t_s] = md5($t_p);
		$flip_mapping[md5($t_p)] = $t_p;
	}

	$mapping = array_merge($default_mapping,$mapping);

	$s_mapping = array_keys($mapping);
	$p_mapping = array_values($mapping);

	$php = str_replace($s_mapping,$p_mapping,$sql);

	$s_mapping = array_keys($flip_mapping);
	$p_mapping = array_values($flip_mapping);

	$php = str_replace($s_mapping,$p_mapping,$php);

	return $php;
}

function sql_data_export($sql,$delimiter="\t",$null='',$quotes=false)
{

	/*
	 * This is in BETA.  There may be escaping issues.
	 * Not sure about the tab delimited, but CSV specifies quotes should
	 * be escaped as two quote characters.  This is currently implemented.
	 */

	if (is_array($sql)) {
		$sql = implode(";\n\n",$sql);
	}

	if ($res = sql_query($sql)) {
		$def['fields'] = sql_query_metadata($res);
		$export = implode($delimiter,array_keys($def['fields']))."\n";
		while ($a = sql_fetch_assoc($res)) {
			$a = array_values($a);
			foreach ($a as $key => $val) {
				if (be_null($val)) {
					$a[$key] = $null;
				}
				if ($quotes) {
					$a[$key] = enquote2(str_replace('"','""',$a[$key]));
				}
			}
			$export .= implode($delimiter,$a)."\n";
		}
	} else {
		$export = 'SQL contained errors.';
	}
	return $export;
}

function set_postgresql_version()
{
	$pg = pg_version();

	define('AG_POSTGRESQL_VERSION',$pg['server']);
}

function sql_supports_returning()
{
	return (AG_POSTGRESQL_VERSION >= '8.2');
}

function sql_manage_comment($relname,$type,$comment)
{

	/*
	 * Provide a link for changing, and displaying the existing
	 * DB comment on a DB relation.
	 */

	$link_text = right(smaller('Change',2));

	if (be_null($comment)) {

		$link_text = 'Set comment';

	}

	return webify($comment) . hlink('',$link_text,'',' onclick="'.js_link_disable(
														'Feature not yet implemented.\n\n'
														. 'Use:\n\n'
														. ' SET COMMENT ON {TABLE|VIEW} object_name IS \\\'text\\\'\n\n'
														. 'from psql to add or change the comment.').'"');

}

?>
