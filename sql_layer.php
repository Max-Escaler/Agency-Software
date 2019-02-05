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
   * Functions to abstract database calls
   *
   * All should use DB defined by $WHICH_DB
   */

define('AG_DATABASE_UNDEFINED_ERROR','Database Type Unknown or not set');

  /*
   * This is the maximum integer range in PostgreSQL (ie, 2^31 - 1)
   */
define('AG_POSTGRESQL_MAX_INT',2147483647);

function sql_query($query,$params=array())
{
	global $query_display,$WHICH_DB;
	static $query_count = 0;
	static $query_count_logged = 0;
	$query_count++;
	if ($query_display) {
		$query_count_logged++;
		$string="Querying ($WHICH_DB) with $query. $query_count queries, $query_count_logged displayed.";
		out( ($GLOBALS['MODE']=='TEXT')
			? $string
			: div(webify($string),'',' class="sqlCode"'));
	}
	switch ($GLOBALS['WHICH_DB']) {
	case 'my' :
		return mysql_query($query);
	case 'pg' :
		return pg_query_params($query,$params);
	default :
		return AG_DATABASE_UNDEFINED_ERROR;
	}
}
	
/*
 * Transaction functions
 */

function sql_begin()
{
	/*
	 * Start a transaction
	 */
	return sql_query('BEGIN');
}

function sql_end()
{
	/*
	 * End a transaction
	 */
	return sql_query('END');
}

function sql_abort()
{
	/*
	 * Abort a transaction
	 */
	return sql_query('ABORT');
}

function sql_escape_string($s)
{
	switch ( $GLOBALS['WHICH_DB'] ) {
	case 'my':
		return mysql_escape_string($s);
	case 'pg':
		return pg_escape_string($s);
	}
}

function sql_escape_identifier($s)
{
	switch ( $GLOBALS['WHICH_DB'] ) {
	case 'my':
		return mysql_escape_identifier($s); // ??
	case 'pg':
		// 5.4.4+
		if (function_exists('pg_escape_identifier')) {
			return pg_escape_identifier($s);
		} else {
			return '"' . $s . '"';
		}
	}
}

function sql_escape_literal($s)
{
	switch ( $GLOBALS['WHICH_DB'] ) {
	case 'my':
		return mysql_escape_literal($s);
	case 'pg':
		// 5.4.4+
		if (function_exists('pg_escape_literal')) {
			return pg_escape_literal($s);
		} else {
			return enquote1(sql_escape_string($s));
		}
	}
}

function sql_metadata($table)
{
	static $cache;
	if ($cache and array_key_exists($table,$cache)) {
		return $cache[$table];
	}

	switch ($GLOBALS['WHICH_DB']) {
	case 'my' :
	      outline('No metadata function for MySQL');
	      return false;
	case 'pg' :

	      $fields = array();

	      $meta = sql_query("SELECT a.attname, format_type(a.atttypid, a.atttypmod), a.attnotnull, a.atthasdef, a.attnum
                                         FROM pg_class c, pg_attribute a
                                         WHERE c.relname = '$table'
                                            AND a.attnum > 0 
                                            AND a.attrelid = c.oid
                                            AND NOT a.attisdropped
			                 ORDER BY a.attnum");
		/*
		 * Returns an array indexed on field name, with reference table and reference column name 
		 */
		$primary_keys = sql_primary_keys($table);
	      $constraints  = foreign_key_constraint($table);

	      while ($a = sql_fetch_assoc($meta)) {

			$b['field']   = $a['attname'];
			$b['null_ok'] = sql_false($a['attnotnull']);

			if (in_array($a['attname'],explode(',',$primary_keys))) {

				$b['primary_key'] = true;

			}

			if (is_array($constraints) && array_key_exists($b['field'],$constraints)) {

				$b['lookup_table']  = $constraints[$b['field']]['ref_table'];
				$b['lookup_column'] = $constraints[$b['field']]['ref_column'];

			}

			$type = $a['format_type'];
			if (preg_match('/^char(acter)?(\s?(.*))?/i',$type,$m)) {

				$rest = $m[3];

				$is_array=preg_match('/\[\]$/i',$rest) ? '[]' : '';

				if (preg_match('/varying/i',$rest)) {

					$b['data_type'] = 'varchar' . $is_array;

				} else {

					$b['data_type'] = 'character' . $is_array;

				} 
				
				if (preg_match('/\(([0-9]{1,4})\)/',$rest,$matches)) {

					$b['length']=$matches[1];

				}

				if ($type == 'char') {

					$be['length']=1;

				}

			} elseif (preg_match('/^time(stamp)?([(][0-9][)])? ?with(out)?/i',$type,$matches)) {

				$b['data_type'] = 'time'.$matches[1]; //time or timestamp
				$b['timezone']  = $matches[3] !== 'out';

			} elseif (in_array($type,array('json','jsonb'))) {
				// FIXME: handling json/b as TEXT for now.  Is this good enough?
				$b['data_type'] = 'text';
			} elseif (false) { 

				//for reltime, abstime
				//fixme: either finish or get rid of this section
				outline('here is time'.$type);
				$b['data_type']='time';

			} else {

				$b['data_type'] = $a['format_type'];

			}

			/*
			 * find and add defaults
			 */
			if (sql_true($a['atthasdef'])) {

				$default = sql_field_default($table,$a['attname']);
				$dtype   = ($b['data_type'] == 'varchar' ? 'character varying' : $b['data_type']);
				$dtype   = ($dtype == 'character') ? 'bpchar' : $dtype;
				$field   = $a['attname'];

				if ( ( (strstr($default,$dtype) || strstr($default,str_replace('_past','',$dtype)) /* allow for date_past or timestamp_past */) 
					 && strstr($default,'\'now\'::text'))
				     || ($default=='now()') //pgsql >= 8.1 changed the default syntax for current_timestamp to now()
				     ) {

					$default='current_'.$dtype;

				} elseif (strstr($default,'nextval(')) {

					//fixme: this should probably maintain the name of the sequence
					$default = 'POSTGRES_SEQUENCE';

				} elseif ( strstr($default,"'::$dtype") ) {

					$tmp=preg_match("/\'(.*)\'\:\:(character varying|time|bpchar)/i",$default,$matches);
					$default=$matches[1];

				} elseif ($dtype == 'boolean') {

					$default= (strtolower($default)=='false') ? sql_false() : sql_true();

				}

				$b['default'] = $default;

			}

			/*
			 * column comments
			 */
			if ($tmp_comment = sql_field_comment($table,$a['attname'])) {

				$b['comment'] = $tmp_comment;

			}

			$fields[$a['attname']]=$b;
			unset($b);

	      }

          $cache[$table]=$fields;
	      return $fields;	
	      break;
	default :
	      return 'Database Type Unknown or not set (sql_metadata)';

	}

}

function sql_query_metadata($res,$fields=null)
{
	switch ($GLOBALS['WHICH_DB']) {
	case 'my' :
		outline('No sql_query_metadata function for MySQL');
		return false;
	case 'pg':
		$fields = orr($fields,sql_field_names($res));
		$meta = array();
		$i = 0;
		foreach ($fields as $field) {
			/*
			 * FIXME: there is much more potential for metadata here
			 */
			$f_meta = array();
			$f_meta['data_type'] = sql_field_type($res,$i);
			$meta[$field] = $f_meta;
			$i++;
		}
		return $meta;
	default:
		return 'Database Type Unknown or not set (sql_query_metadata)';
	}
	
}

function sql_field_default($table,$field_name)
{
      //returns the default value for a field
      // searches pg_attrdef table
      switch ($GLOBALS['WHICH_DB'])
      {
      case 'my' :
	    outline('No field_default function for MySQL');
	    return false;
      case 'pg' :
	    $sql = sql_query("SELECT d.adsrc 
                            FROM pg_attrdef d, pg_attribute a, pg_class c
                            WHERE c.relname='$table'
                               AND a.attrelid = c.oid
                               AND a.attrelid = d.adrelid 
                               AND d.adnum = a.attnum
                               AND a.attname = '$field_name'");
	    $default = sql_fetch_assoc($sql);
	    return $default['adsrc'];
      default :
	    return 'Database Type Unknown or not set (sql_field_default)';
      }
}

function sql_field_comment($table,$field)
{
	static $cached_comments;

	switch ($GLOBALS['WHICH_DB']) {
	case 'my':
		outline('No field_comment function for MySQL');
		return null;
	case 'pg':
		if (!isset($cached_comments)) {
			$c_sql = ' 
SELECT c.relname AS table,a.attname AS column,pg_catalog.col_description(a.attrelid, a.attnum) AS comment
FROM pg_catalog.pg_attribute a, pg_class c
WHERE  a.attrelid = c.oid
AND pg_catalog.col_description(a.attrelid, a.attnum) IS NOT NULL;
';
			$comments=agency_query($c_sql);
			while ($c = sql_fetch_assoc($comments)) {
				$cached_comments[$c['table']][$c['column']]=$c['comment'];
			}
			if (!isset($cached_comments)) {
				$cached_comments=array();
			}
		}

		return isset($cached_comments[$table][$field])
			? $cached_comments[$table][$field]
			: '';
	}
}

function sql_relation_kind($sql_relation)
{
	  // This function seems unused as it's no longer called by is_table or is_view
	  // It might have other uses, though, so leaving it for now.
      switch ($GLOBALS['WHICH_DB'])
      {
      case 'my' :
	    outline('No relation_kind function for MySQL');
	    return false;
      case 'pg' :
	    $sql = sql_query("SELECT c.relkind 
                          FROM pg_class c 
                          WHERE c.relname='$sql_relation'");
	    $KIND = sql_fetch_assoc($sql);
	    switch($KIND['relkind'])
	    {
	    case 'r' : 
		  return 'table';
	    case 'v' :
		  return 'view';
	    case 'i' :
		  return 'index';
	    case 'S' :
		  return 'sequence';
	    case 'c' :
		  return 'composite type';
	    case 's' :
		  return 'special';
	    case 't' :
		  return 'TOAST';
	    default:
		  return false;
	    }
      default :
	    outline('Database Type Unknown or not set (sql_relation_kind)');
	    return false;
      }
}

function is_table($table)
{
	  static $cache;
	  if (!is_array($cache)) {
		$cache=sql_fetch_column(agency_query('select tablename from pg_tables where tableowner=(select current_user)'),'tablename');
	  }
	  return in_array($table,$cache) ? true : false;
}

function is_view($view)
{
	  static $cache;
	  if (!is_array($cache)) {
		$cache=sql_fetch_column(agency_query('select viewname from pg_views where viewowner=(select current_user)'),'viewname');
	  }
	  return in_array($view,$cache) ? true : false;
}

function isTableView($relation)
{
      return is_table($relation) ? true : is_view($relation);
}

function is_field($table,$field)
{
      $meta=sql_metadata($table);
      return array_key_exists($field,$meta);
}

function sql_table_constraints($table)
{
	switch ($GLOBALS['WHICH_DB']) {

	case 'my':
		outline('No sql_table_constraints() function for MySQL');
		return false;
		break;
	case 'pg':
		$sql = "SELECT consrc, conname FROM pg_catalog.pg_constraint r 
		LEFT JOIN  pg_catalog.pg_class c ON (c.oid=r.conrelid) WHERE c.relname ~ '^{$table}$' AND r.contype = 'c'";

		$res = sql_query($sql);
		$constraints = array();
		while ($a = sql_fetch_assoc($res)) {
			$constraints[$a['conname']] = $a['consrc'];
		}
		return $constraints;
	}

}

function foreign_key_constraint($table)
{
      //returns a two element array containing the lookup table, and the field in the table
      //false if no foreign key_constraint;
      switch ($GLOBALS['WHICH_DB'])
      {
      case 'my' :
	    outline('No foreign_key_constriant function for MySQL');
	    return false;
      case 'pg' :
	    $sql = sql_query("SELECT c.relname AS table_name, foreign_column_number, column_number
                                 FROM
                                     (SELECT confrelid AS table_oid,
	                             conkey AS column_number,
	                             confkey AS foreign_column_number
	                             FROM pg_constraint r, pg_class c
                                     WHERE c.oid = r.conrelid
                                     AND c.relname = '$table'
                                     AND r.contype = 'f'
                                     ) AS table_list,
                                   pg_class c
                                WHERE c.oid = table_list.table_oid");
	    //	    $table_fields = sql_query("SELECT * FROM $table LIMIT 0");

	    $table_fields_res=sql_query("SELECT a.attname
                                         FROM pg_class c, pg_attribute a
                                         WHERE c.relname = '$table'
                                            AND a.attnum > 0 
                                            AND a.attrelid = c.oid
			                 ORDER BY a.attnum");
	    $table_fields=sql_fetch_column($table_fields_res,'attname');
	    while ($a = sql_fetch_assoc($sql))
	    {
		  $col_num = (int)trim($a['column_number'],'\{,\}') - 1; //php and postgresql are offset by 1
		  //		  $col_name = sql_field_name($table_fields,$col_num);  // this will be a distinct result for the $table
		  $col_name=$table_fields[$col_num];
		  
		  $ref_table = $a['table_name'];
		  //$res = sql_query("SELECT * FROM $ref_table LIMIT 0");
		  $res=sql_query("SELECT a.attname
                                         FROM pg_class c, pg_attribute a
                                         WHERE c.relname = '$ref_table'
                                            AND a.attnum > 0 
                                            AND a.attrelid = c.oid
			                 ORDER BY a.attnum");
		  $res_fields=sql_fetch_column($res,'attname');
		  $ref_col_num = (int)trim($a['foreign_column_number'],'\{,\}') - 1; //php and postgresql are offset by 1
		  $ref_col_name = $res_fields[$ref_col_num];
		  $constraints[$col_name] = array('ref_table'=>$ref_table,
						  'ref_column'=>$ref_col_name);
	    }
	    return $constraints;
      default :
	    outline('Database Type Unknown or not set (foreign_key_constraint)');
	    return false;
	    
      }
}

function sql_indexes($table)
{
	$sql = "SELECT c2.relname AS name, i.indisprimary AS is_primary, i.indisunique AS is_unique, pg_catalog.pg_get_indexdef(i.indexrelid) AS def
              FROM pg_catalog.pg_class c, pg_catalog.pg_class c2, pg_catalog.pg_index i
              WHERE c.relname = '$table' AND c.oid = i.indrelid AND i.indexrelid = c2.oid
              ORDER BY i.indisprimary DESC, i.indisunique DESC, c2.relname";
	$res=sql_query($sql);
	$indexes=array();
	while ($a=sql_fetch_assoc($res))
	{
		$def=$a['def'];
		$tmp=explode('USING btree',$def);
		$column=trim($tmp[1],'() ');
		$indexes[$column]=$a;
	}
	return $indexes;
}

function sql_field_type($res,$field_number)
{
	switch ($GLOBALS['WHICH_DB'])
	{
	case 'my' :
		return mysql_field_type($res,$field_number);
	case 'pg' :
		return pg_field_type($res,$field_number);
	default :
		return AG_DATABASE_UNDEFINED_ERROR;
	}
}

function sql_primary_keys($table)
{
	static $keys;
	$keys[$table]=orr($keys[$table],call_sql_function('primary_key',sql_escape_literal($table)));
	return $keys[$table];
}

function sql_num_rows($res,$eng='')
{
	if ($res) {

		$eng = orr($eng,$GLOBALS['WHICH_DB']);

		switch ($eng) {

		case 'my' :
			return mysql_num_rows($res);

		case 'pg' :
			return pg_num_rows($res);

		default :
			return AG_DATABASE_UNDEFINED_ERROR;
		}

	}
}

function sql_limit( $limit, $offset='')
{
	switch ($GLOBALS['WHICH_DB'])
	{
		case 'my' :
			return ' LIMIT ' . ($offset ? "$offset, " : '' ) . $limit;
		case 'pg' :
			return " LIMIT $limit" . ($offset ? " OFFSET $offset" : '');
		default :
			return AG_DATABASE_UNDEFINED_ERROR;
	}
}

function sql_fetch_assoc($res,$row=null,$eng='')
{
	/* 
	 * $row parameter only applicable to Postgres
	 */
	if ($res) {

		$eng = orr($eng,$GLOBALS['WHICH_DB']);
		switch ($eng) {

		case 'my' :
			return mysql_fetch_assoc($res);

		case 'pg' :
			return pg_fetch_array($res,$row, PGSQL_ASSOC);

		default :
			return AG_DATABASE_UNDEFINED_ERROR;

		}

	}

}

function sql_fetch_row($res,$row=null,$eng=null)
{
	/*
	 * $row parameter only applicable to Postgres
	 */
	if ($res) {

		$eng = orr($eng,$GLOBALS['WHICH_DB']);

		switch ($eng) {

		case 'my' :
			return mysql_fetch_row($res);

		case 'pg' :
			return pg_fetch_row($res,$row);

		default :
			return AG_DATABASE_UNDEFINED_ERROR;
		}

	}
}

function sql_data_seek($res,$pos)
{
	switch ($GLOBALS['WHICH_DB'])
	{
		case 'my' :
			return mysql_data_seek($res,$pos);
		case 'pg' :
            // can't use pg_result_seek until php 4.3.0
			//return pg_result_seek($res,$pos);
			// This doesn't work as a substitute--i.e., seek 0 will leave pointer at 1
            return pg_fetch_row($res,$pos);
            
		default :
			return AG_DATABASE_UNDEFINED_ERROR;
	}
}

function sql_affected_rows($res)
{
	switch ($GLOBALS['WHICH_DB'])
	{
		case 'my' :
			return mysql_affected_rows($res);
		case 'pg' :
			return pg_affected_rows($res);
		default :
			return AG_DATABASE_UNDEFINED_ERROR;
	}
}
	
function sql_num_fields($res)
{
	switch ($GLOBALS['WHICH_DB'])
	{
	case 'my' :
		return mysql_num_fields($res);
	case 'pg' :
		return pg_num_fields($res);
	default :
		return AG_DATABASE_UNDEFINED_ERROR;
	}
}

function array_fetch_column( $array, $field_name )
{
// Take an associative array, return a column
// Taken from sql_fetch_column
        while ( $row=array_shift( $array) )
        {
                $col[]=$row[$field_name];

        }
        return $col;
}

function sql_fetch_column( $result, $field_name )
{
// Take a sql result, return an array
		$col=array();	
        while ( $row=sql_fetch_assoc( $result) )
        {
                $col[]=$row[$field_name];
        }
        return $col;
}

function sql_fetch_to_array( $result )
{

	if ($result) {

		switch ($GLOBALS['WHICH_DB']) {

		case 'pg' :

			$ary = pg_fetch_all($result);

			break;
			
		default :
		case 'my' :

			/*
			 * No fetch_all function for mysql, replace once there is
			 */
			
			$ary = array();
			
			while ($a = sql_fetch_assoc($result)) {
				
				$ary[] = $a;
				
			}
			
			break;
			
		}

		return $ary;

	}

}

function sql_error( $res='' )
{
	/*
	 * Optionally pass a result set, for pg-specific error messaging
	 */
	switch ($GLOBALS['WHICH_DB'])
	{
		case 'my' :
        		$msg = '<br />The MYSQL server reported error #' . mysql_errno() .
			       '<br />The error text was: ' . mysql_error();
			break;
		case 'pg' :
			$err = oline($res ? pg_result_error( $res ) : pg_last_error());
        		$msg = oline('The PostgreSQL server reported an error.')
			       . oline("The error text was: $err");
			break;
		default :
			$msg = AG_DATABASE_UNDEFINED_ERROR;
			break;
	}
	// Include backtrace?
	// if has_perm is not defined, it's a very low-level error
	// (likely db connection) and should be safe to dump debugging details
	if ( be_null($GLOBALS['UID']) or (!function_exists('has_perm')) or has_perm('admin')) {
		$msg .= div(dump_array(debug_backtrace()).toggle_label('Debugging details'),'','class="hiddenDetail"');
	}
	if ($GLOBALS['mode']=='TEXT') {
		$msg=strip_tags($msg);
	}
	return $msg;
}	

function sql_last_notice($res = '')
{
	$res = orr($res,$GLOBALS['AG_DB_CONNECTION']);
	return pg_last_notice($res);
}

function sql_die( $text='Database Error',$res='' )
{
        $msg= bigger($text) . sql_error($res);
	  log_error( "sql_die: $msg" );
	  die($msg);
}

function sql_warn( $text='Warning.  Database Error.  Continuing.', $res='' )
{
	$msg= bigger($text) . sql_error($res) .'<br />The program is continuing';
	out(div($msg,'',' class="sqlCode"'));
	log_error( "sql_warn: $msg", $silent=true );
}

function db_connect($db='',$eng='',$die_func='sql_die')
{
	global $db_server, $db_user_name, $db_password, $database,$WHICH_DB, $db_port;

	$eng  = orr($eng,$WHICH_DB);
	$db   = orr($db,$database[$eng]);

	$port = orr($db_port[$eng],'5432');

	switch ($eng) {
	case 'my'  :
		if ( !function_exists('mysql_connect') ) {
			die('MySQL functions appear to be missing from PHP');
		}
    		$link = @mysql_connect($db_server[$eng],$db_user_name[$eng],$db_password[$eng])
			or $die_func("Could not connect to MYSQL @ $db_server[$eng] server");
		@mysql_select_db("$db") or $die_func("Could not get $db db from $eng");
		return $link;
	case 'pg' :
		if ( !function_exists('pg_connect') ) {
			die('PostgreSQL functions appear to be missing from PHP');
		}
		$res = @pg_connect("host=$db_server[$eng] user=$db_user_name[$eng] password=$db_password[$eng] dbname=$db port=$port");
		
		if (!$res) {
			$die_func("Could not connect to Postgres @ {$db_server[$WHICH_DB]} server for database $db");
		}
		return $res;
	default :
		log_error('Database Type '.$eng.' Unknown or not set');
		return false;
	}
}

function db_close($res=null)
{
	global $AG_DB_CONNECTION;
	$res = orr($res,$AG_DB_CONNECTION);

	switch ($GLOBALS['WHICH_DB']) {

	case 'my':
		return mysql_close($res);

	case 'pg':
		return pg_close($res);

	default:
		log_error('Database Type '.$WHICH_DB.' Unknown or not set');
		return false;
	}
}

// returns field names only in the result
function sql_field_name($res, $field_num)
{
	switch ($GLOBALS['WHICH_DB'])
	{
		case 'my' :
			return mysql_field_name($res, $field_num);
		case 'pg' :
			return pg_field_name($res, $field_num);
		default :
			return "Database Type Unknown or not set";
	}

}

function sql_field_names($res)
{
	$num = sql_num_fields($res);
	$fields = array();
	for ($i=0; $i<$num; $i++) {
		array_push($fields,sql_field_name($res,$i));
	}
	return $fields;
}

function sql_to_php_array($dbarr) {

	if ( !preg_match('/^\{.*\}$/',$dbarr)) { return false; }

	// Copied from http://php.net/manual/en/ref.pgsql.php, posted by ChrisKL

    /**
     * Change a db array into a PHP array
     * @param $dbarr String representing the DB array
     * @return A PHP array
     */

        // Take off the first and last characters (the braces)
        $arr = substr($dbarr, 1, strlen($dbarr) - 2);

        // Pick out array entries by carefully parsing.  This is necessary in order
        // to cope with double quotes and commas, etc.
        $elements = array();
        $i = $j = 0;        
        $in_quotes = false;
        while ($i < strlen($arr)) {
            // If current char is a double quote and it's not escaped, then
            // enter quoted bit
            $char = substr($arr, $i, 1);
            if ($char == '"' && ($i == 0 || substr($arr, $i - 1, 1) != '\\')) 
                $in_quotes = !$in_quotes;
            elseif ($char == ',' && !$in_quotes) {
                // Add text so far to the array
                $elements[] = substr($arr, $j, $i - $j);
                $j = $i + 1;
            }
            $i++;
        }
        // Add final text to the array
        $elements[] = substr($arr, $j);

        // Do one further loop over the elements array to remote double quoting
        // and escaping of double quotes and backslashes
        for ($i = 0; $i < sizeof($elements); $i++) {
            $v = $elements[$i];
            if (strpos($v, '"') === 0) {
                $v = substr($v, 1, strlen($v) - 2);
                $v = str_replace('\\"', '"', $v);
                $v = str_replace('\\\\', '\\', $v);
                $elements[$i] = $v;
            }
        }

        return $elements;
}

function php_to_sql_array($array)
{
	if (!is_array($array)) {
		return $array;
	}

	return '{'.implode(',',$array).'}';
}

function sql_commentify($text)
{
	return "/*\n *    ".str_replace("\n","\n *    ",wordwrap($text))."\n */\n\n\n";
}

function sql_copy_from($table,$rows,$delimiter="\t",$null_as="\\\N")
{
	global $AG_DB_CONNECTION;
	switch ($GLOBALS['WHICH_DB'])
	{
	case 'my' :
		return 'Error (sql_copy_from()): MySQL doesn\'t support the copy command.';
	case 'pg' :
		return pg_copy_from($AG_DB_CONNECTION,$table,$rows,$delimiter,$null_as);
	default :
		return AG_DATABASE_UNDEFINED_ERROR;
	}
}

/* export functions */

function sql_copy_to($table,$delimiter="\t",$null_as="\\\N")
{
	global $AG_DB_CONNECTION;
	switch ($GLOBALS['WHICH_DB'])
	{
	case 'my' :
		return 'Error (sql_copy_to()): MySQL doesn\'t support the copy command.';
	case 'pg' :
		return pg_copy_to($AG_DB_CONNECTION,$table,$delimiter,$null_as);
	default :
		return AG_DATABASE_UNDEFINED_ERROR;
	}
}


function sql_dump($sql,$format='FULL',$table_name='tmp_export_table')
{
	if (is_array($sql)) {
		$sql = implode(";\n\n",$sql);
	}
	$res = agency_query($sql);
	$def['fields'] = sql_query_metadata($res);
	$table = sql_build_table($def,$table_name);

	switch ($format) {
	case 'COPY':
		$inserts = sql_build_copy($def,$res,$table_name);
		break;
	case 'INSERTS':
	case 'FULL':
		$inserts = sql_build_inserts($def,$res,$table_name,$format);
		break;
	default:
		$inserts = 'Error: Unknown format ('.$format.') passed to sql_dump().';
	}

	$final_sql = $table . $inserts;
	if ($format == 'COPY' || sql_verify_dump($final_sql)) {
		return $final_sql;
	}
	return 'WARNING: SQL Dump contains errors.'."\n\n\n".$final_sql;
}

function sql_build_table($def,$name)
{
	$fields = array_keys($def['fields']);
	$table = 'CREATE TABLE '.$name.' ('."\n";
	foreach ($fields as $field) {
		$tab_def[] = sql_build_column($field,$def['fields'][$field]);
	}
	$table .= implode(",\n",$tab_def)."\n".');'."\n";
	return sql_commentify('Create statement for "'.$name.'"')
		. $table ."\n\n";
}

function sql_build_column($name,$def)
{
	// enquote names with spaces
	if (strpos($name,' ')) {
		$name = enquote2($name);
	}

	return "\t" . $name . "     " . $def['data_type'];
}

function sql_build_inserts($def,$res,$table,$format,$comment='')
{
	$fields = array_keys($def['fields']);

	// enquote names with spaces
	foreach ($fields as $i => $name) {
		if (strpos($name,' ')) {
			$name = enquote2($name);
		}
		$fields[$i] = $name;
	}

	$field_list = implode(', ',$fields);
	while ($a = (is_array($res) ? array_shift($res) : sql_fetch_assoc($res))) {
		$tmp_vals = array();
		foreach ($fields as $field) {
			$tmp_vals[] = sql_build_value($a[$field],$def['fields'][$field]['data_type']);
		}
		$tmp_vals = implode(', ',$tmp_vals);
		switch ($format) {
		case 'FULL':
			$inserts .= 'INSERT INTO '.$table.' ('.$field_list.') VALUES ('.$tmp_vals.');'."\n\n";
			break;
		case 'INSERTS':
			$inserts .= 'INSERT INTO '.$table.' VALUES ('.$tmp_vals.');'."\n\n";
			break;
		}
	}
	return sql_commentify(orr($comment,'Inserting data...'))
		. $inserts."\n\n\n";
}

function sql_build_copy($def,$res,$table)
{
	$fields = array_keys($def['fields']);

	// enquote names with spaces
	foreach ($fields as $i => $name) {
		if (strpos($name,' ')) {
			$name = enquote2($name);
		}
		$fields[$i] = $name;
	}

	$field_list = implode(', ',$fields);
	$copy = 'COPY '.$table.' ('.$field_list.') FROM stdin;'."\n";
	while ($a = sql_fetch_assoc($res)) {
		$a = array_values($a);
		foreach ($a as $key=>$val) {
			if (be_null($val)) {
				$a[$key] = "\\N";
			}
		}
		$copy .= implode("\t",$a)."\n";
	}
	$copy .= '\.';

	return $copy;
}

function sql_build_value($value,$type)
{
	if (be_null($value)) { 
		$type = 'null';
	}
	switch ($type)
	{
	case 'int4':
	case 'bigint':
	case 'integer':
	case 'oid':
	case 'smallint':
	case 'float8':
	case 'double precision':
	case 'numeric':
	case 'real':
		break;
	case 'null':
		$value = 'NULL';
		break;
	default:
		$value = sql_escape_literal($value);
	}
	return $value;
}

function sql_verify_dump($sql)
{
	sql_begin();
	$res = sql_query($sql);
	sql_abort();
	return $res ? true : false;
}

function sql_get_sequence($name)
{
	$res = agency_query('SELECT NEXTVAL('.sql_escape_literal($name).')');
	if ($res && sql_num_rows($res) > 0) {
		$a = sql_fetch_assoc($res);
		return $a['nextval'];
	}
	return false;
}

function sql_drop_alter_relations_array()
{
	/*
	 * Returns an array of relations that can be dropped or altered.
	 */

	switch ($GLOBALS['WHICH_DB']) {
	case 'my' :
		return 'Error drop/alter list not created for MySQL';
	case 'pg' :
		return array('AGGREGATE',   'DATABASE',    'GROUP',       'OPERATOR',    'SCHEMA',      'TABLESPACE',  'TYPE',        'VIEW',
				 'CAST',        'DOMAIN',      'INDEX',       'ROLE',        'SEQUENCE',    'UNIQUE',
				 'CONVERSION',  'FUNCTION',    'LANGUAGE',    'RULE',        'TABLE',       'TRIGGER',     'USER');
	default :
		return AG_DATABASE_UNDEFINED_ERROR;
	}
}

function sql_strip_comments($sql)
{
	/*
	 * strip out comments from sql
	 */

	return preg_replace(array(
					  '/--.*'."\n".'/i', // -- style comments
					  '/\/\*.*\*\//is'   // /* */ style comments
					  )
					  ,"\n",$sql);
}

?>
