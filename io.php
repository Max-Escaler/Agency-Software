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

// Basic functions to do input/output in php

if (phpversion() < '4.3') {
	// Older version stuff:
	function file_get_contents($filename)
	{
		/*
		 * lifted from example in documentation at PHP.net for file_get_contents
		 * This function is built into 4.3+, so we can eliminate when we upgrade
		 */
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
} else {
	// newer version stuff:
	//error_reporting(E_ALL); //use to find undefined variables
	error_reporting(E_ALL ^ E_NOTICE);	
}

if (phpversion() < '5') {

	function file_put_contents($file,$string)
	{
		//obsolete after php5
		$a=fopen($file,'w');	// create file in write mode
		if (! $a)
		{
			$error = 'create';
		}
		elseif (!$size=fwrite($a,$string))
		{
			$error = 'write to';
		}
		if ($error)
		{
			$msg="Couldn't $error file $file.  Giving up!";
			outline(bigger(bold($msg)));
			die;
		}
		if (! fclose($a))
		{
			outline(bigger(bold("Warning--couldn't close $file!!")));
		}
		return $size;
	}
	
	function scandir($dir)
	{
		//obsolete once PHP 5
		if (!$handle = opendir($dir))
		{
			return false;
		}
		$files=array();
		while ($file=readdir($handle))
		{
			if ($file <> '.' && $file <> '..')
			{
				array_push($files,$file);
			}
		}
		closedir($handle);
		clearstatcache();
		return $files;
	}
	
}

function file_get_contents_url($filename)
{
    $handle = fopen ($filename, "rb");
    $contents = "";
    do
    {
        $data = fread($handle, 8192);
        if (strlen($data) == 0) {
            break;
        }
        $contents .= $data;
    }
    while(true);
    fclose ($handle);
    return $contents;
}

function header_row($fields)
{
		if (is_array($fields))
		{
			while( $fields )
			{
				$row .= "<th><b>" . array_shift($fields)."</b></th>\n";
			}
		}
		else
		{
	        $arg_count = func_num_args();
    	    for ($x=0;$x<$arg_count;$x++)
        	{
            	    $row .= "<th><b>" . func_get_arg($x) . "</b></th>\n";
	        }
		}
        return row( $row );
}

function table_row($fields,$options='')
{
		if (is_array($fields))
		{
			while( $fields )
			{
				$row .= cell(array_shift($fields));
			}
		}
		else
		{
	        $arg_count = func_num_args();
    	    for ($x=0;$x<$arg_count;$x++)
        	{
            	    $row .= cell( func_get_arg($x));
	        }
		}
        return row( $row,$options );
}

function array_to_table($arr,$options='') {
	// Takes assoc array, or query
	if (!is_array($arr)) {
		while ( ($a=sql_fetch_assoc($arr)) ) {
			$b[]=$a;
		}
		$arr=$b;
	}
	foreach ($arr as $a) {
		$body[]=table_row($a);
	}
	return table(
		header_row(array_keys($arr[0]))
		. implode('',$body)
	,$options);
}

function file_append( $file, $text )
{
// This is being put in place for log_error, but could be used for
// other purposes too.  I suppose $text could technically be binary.

	$a=fopen($file,"a");	// open file in append mode
	if (! $a)
	{
		$error = "open";
	}
	elseif (! fwrite($a,$text))
	{
		$error = "write to";
	}
	if ($error)
	{
		$msg="Couldn't $error file $file to log error $text.  Giving up!";
		outline(bigger(bold($msg)));
		die;
	}
	if (! fclose($a))
	{
		outline(bigger(bold("Warning--couldn't close error log file!!")));
	}
	return true;
}

//fixme: these should be moved to a more basic 'common.php' or the like, since this file has 
//exceeded 'I/O' functions.

function is_test_db()
{
      global $database,$db_server,$WHICH_DB;
      return ($database[$WHICH_DB] !== AG_PRODUCTION_DATABASE_NAME)
		or ($db_server[$WHICH_DB] !== AG_PRODUCTION_DATABASE_SERVER);
}

function db_read_only_mode()
{
	return defined('AG_READ_ONLY_MODE') && AG_READ_ONLY_MODE;
}

function log_error( $error_text, $silent=false, $die = false )
{
	/*
	 * This function will log errors to a file.
	 * If $mail_errors_to is defined, the emails will be generated too.
	 */

	global $UID,$NICK,$database,$WHICH_DB,$mail_errors_to;

	$db=$database[$WHICH_DB];

	if (!$silent) {
		outline( div($error_text,'','class="error"') );
	}

   	if (is_test_db()) { //don't write to log file
   	      return;
   	}

	$error_string =  datetimeof("now","SQL") . ": DB=$db: User=$NICK ($UID): $error_text\n";

	if (phpversion() >= '4.3' && AG_ERROR_LOG_BACKTRACE) { // print_r() must be able to return output
		$error_string .= "\n\n==== BACKTRACE ====\n\n".print_r(debug_backtrace(),true)."\n\n==== END BACKTRACE ====\n\n";
	}

	file_append( AG_ERROR_LOG_FILE, $error_string );

	if ($mail_errors_to) {
		global $AG_EMAIL_ERROR_CACHE;
		if ($die || !(strpos($error_text,'sql_die')===false)) { // send email now, script will die

			$vars = get_phpinfo();
			$error_string .= oline() . $vars;
			mail( is_array($mail_errors_to) ? implode($mail_errors_to,',') : $mail_errors_to,
				 "AGENCY Error: $NICK ($UID) running {$_SERVER['PHP_SELF']}",strip_tags($AG_EMAIL_ERROR_CACHE . $error_string));
		} else {
			//append email cache to be sent by page_close()
			$AG_EMAIL_ERROR_CACHE .= "\n".$error_string;
		}
	}

	sleep(1); 
	// the sleep is for programs that blindly retry an error-generating action
	// So that the logs will come no more than 1/sec, rather than dozens/sec.
	return;
}

function get_phpinfo($w = INFO_VARIABLES)
{
	ob_start();
	phpinfo(INFO_VARIABLES);
	$inf = strip_tags(ob_get_contents());
	ob_end_clean();
	if (phpversion() >= '4.3') {
		$inf = html_entity_decode($inf);
	}
	return $inf;
}

function create_system_log($type, $message)
{
	/*  
	 * When certain events occur in AGENCY we want to log it in a system 
	 * log table in the AGENCY database.
	 */
	global $UID;

	agency_query(sql_insert('tbl_system_log',array('FIELD:added_at'=>'current_timestamp',
							     'added_by'=>$UID,
							     'changed_by'=>$UID,
							     'event_type'=>$type,
							     'message'=>$message)));
}

function ts( $string )
{
// time-stamp
        return oline(bold(date("h:i:s") . "  " . $string ));
}

function div($content, $id="", $options="" )
{
    return "<div"
        . ($id ? " id=\"$id\"" : "")
        . ($options ? " $options" : "")
        . ">\n".$content.'</div>'."\n";
}

function alt($text=null,$title=null,$options=null) {
// 	return '<alt title="'.$title.'">'.$text.'</alt>';
	//this wasn't really a valid use (there is no alt tag in xhtml anyway)
	$options .= $title ? ' title="'.$title.'"' : '';
	return span($text,$options);
}

function acronym($text=null,$title=null) {
	return '<acronym title="'.$title.'">'.$text.'</acronym>';
}

function span($content=null,$options=null) {

	return '<span '.trim($options).'>'.$content.'</span>';
}

function anchor($id,$text='',$options='')
{
      return '<a name="'.$id.'" '.$options.'>'.$text.'</a>';
}

function box( $text, $color="", $border=2, $tab_options="",$cel_options=""  )
{
    return tablestart("","border=\"$border\" cellpadding=\"10\""
    		. ($tab_options ? " $tab_options" : ""))
    . row(cell( $text , ($color ? " bgcolor=\"$color\"" : "")
    . ($cel_options ? " $cel_options" : "")))
    . tableend();
}

function html_no_print($content)
{
	return span($content,' class="agencyNoPrint"');
}

function html_print_only($content)
{
	return span($content,' class="agencyPrintOnly"');
}

function print_rf($arr) {
	out('<pre>');
	print_r($arr);
	out('</pre>');
}

function dump_array( $array )
{
	return html_pre(print_r($array,true));
}

function in_array_array($needle,$haystack)
{
      //the php function in_array() doesn't work too well with arrays as the needle
      //thus, this wrapper.
      if (is_array($needle))
      {
	    foreach($needle as $value)
	    {
		  $found=in_array($value,$haystack);
		  if (!$found)
		  {
			return $found;
		  }
	    }
	    return $found;
      }
      else
      {
	    return in_array($needle,$haystack);
      }
}

function multi_implode_f($x, $y)
{
	//a formatted implode (for embedding javascript arrays in HTML)
	//to use:
	//  $result = '['.array_reduce($array,'multi_implode_f').']';

	if (!$x) { return $y; } //required to make work w/ php > 4.2.2
	$a = (is_array($x)) ? '[ ' . array_reduce($x, 'multi_implode_f') . ' ]'."\n" : $x;
	$b = (is_array($y)) ? '[ ' . array_reduce($y, 'multi_implode_f') . ' ]'."\n" : $y;
	return $a . ', ' . $b;
}

// creates an array of integers between start and end
// bedgroups use it for defining a range of beds that
// are flagged (like volunteer beds)
function create_range_array($start, $end, $step=1)
{
    for($x=$start ; $x <= $end ; $x+=$step)
    {
        $arr[] = $x;
    }
    return $arr;
}

function tabify( $string )
{
	return str_replace("\t",indent(),$string);
}

function webify( $string )
{
//    make it web safe
	return nl2br( tabify( htmlspecialchars($string, ENT_QUOTES, "UTF-8") ));
}

function dewebify( $string )
{
    // make posting normal
    return stripslashes( $string );
}

function dewebify_array( $array )
{
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			$array[$key] = dewebify_array($value);
		}
 	} elseif (is_object($array)) {
 		//don't want to dewebify objects
	} else {
		$array = dewebify($array);
	}
	return $array;
}

function percent_encode( $string ) {
	return str_replace('%','&percent;',$string);
}

function percent_decode( $string ) {
	return str_replace('&percent;','%',$string);
}

function hard_space( $string=' ' )
{
	switch (AG_OUTPUT_MODE)
	{
		case "HTML" :
			return preg_replace('/ /',"&nbsp;",$string);
		case "TEXT" :
		default :
			return $string;
	}
}

function sqlify( $string )
{
      // add slashes for SQL posting
//       return addslashes( $string );
	return sql_escape_string($string);
}

function sqlsetify( $var, $string , $last="")
{
      // sqlify & enqout1
      // if last, skip comma

      return " $var=" . enquote1( sqlify( $string ) )
      . ( $last ? " " : ", " ) ;
}

function enquote1( $string )
{
        return "'" . $string . "'";
}

function enquote2( $string )
{
        return "\"" . $string . "\"";
}

function timeof( $datetime, $format="ampm", $secs="" )
{
	/*
	 *
	 * FIXME: this should be moved to times.php
	 *
	 * return the time portion of a date/time
	 * pass ouput format as $format, options:
	 * AMPM = w/ AM  or PM
	 * ampm = w/ am or pm
	 * 24 = 24 hour time
	 * pass an argument as $secs to get seconds
	 * SQL = synonym for 24 w/ secs.
	 * NO_SEP_SHORT: HHMM
	 * NO_SEP: HHMMSS
     * US = synonym for ampm, so that datetime of can work with a single format specified
	 */

	$sep        = ":";
	$re_secs    = "([0-5]?[0-9])";
	$re_secs24  = "([0-5][0-9])";
	$re_mins    = "([0-5]?[0-9])";
	$re_mins24  = "([0-5][0-9])";
	$re_hours   = "(2[0-3]|1[0-9]|0[0-9]|[0-9])";
	$re_hours24 = "([0-1][0-9]|2[0-3])";
	$re_sep     = "[ ]?" . $sep . "[ ]?";
	$re_ampm    = "[ ]?([aApP])[.]?[mM]?[.]?";
	$re_trail   = "[ ]*";
	$re_anchor  = "^";

	if (in_array($format,array('SQL','NO_SEP'))) {
		$secs = true;
	}

	if ($datetime=='now') {
		$datetime=datetimeof("now");
	}

	//$re_datetime = $re_hours24 . $re_sep . $re_mins24 . $sep
	//	. "((" . $re_secs24 . "))" . $re_trail;
	$re_datetime = $re_hours24 . $re_sep . $re_mins24 . 
		'(' . $sep . $re_secs24 . ")?" . $re_trail;

	$re_time = $re_hours . $re_sep . $re_mins
            . "(" . "(" . $re_sep . $re_secs . ")?"
            . "(" . $re_ampm . ")?" . ")?";
	//outline("in timeof():  $datetime");
	if( preg_match( '/'.$re_time.'/', $datetime, $results ) ) { // looser format?
		//outline("valid time found");
		$hours=intval($results[1]);
		$minutes=intval($results[2]);
		$seconds=intval($results[5]);
		$ampm= strtolower($results[7]);
		//outline("Hours: $hours, Mins: $minutes, Seconds: $seconds, AMPM: $ampm");
		
		// added the extra !$ampm to allow for timestamps w/ an am/pm - jh 11/30/05
		if ( preg_match( '/'.$re_datetime.'/', $datetime ) and !$ampm) {
			$ampm="24";
			//outline("valid datetime found");
			//outline("Hours: $hours, Mins: $minutes, Seconds: $seconds, AMPM: $ampm");
		} 
	} else {
		//outline("regex failed");
		return false;
	}

	//outline("AMPM = $ampm");
	switch ($ampm) {
	case "p" :
		//outline("PM detected");
		if ($hours < 12) {
			$hours += 12;
		}
            break;
	case "a" :
		//outline("AM detected");
            if ($hours==12) {
			$hours=0;
			break;
		}
	case 'NO_SEP':
	case 'NO_SEP_SHORT':
	case 'SQL':
	case "24" :  // avoid error if we've determined this is a datetime
		break;
	default :
		if ($hours <= 12 && $hours >0 ) {        
			// no am or pm, 1-12 hours = ambiguous--reject
			return false;
		}
	}
	//outline('hours: '.$hours);

	if ($hours >=12) {
		$ampm="p";
		$ampm_hours=$hours - 12;
	} else {
		$ampm="a";
		$ampm_hours = $hours;
	}
	if ($ampm_hours==0) {
		$ampm_hours=12;
	}
//outline("Hours: $hours, Mins: $minutes, Seconds: $seconds, AMPM: $ampm");

	// Time to build output string
	// outline("Format = $format");
	//outline("passed: $datetime");
	//outline("Regex = $re_time");
	// dump_array( $results );
	switch ($format) {
	case 'AMPM' :
		return $ampm_hours . $sep . lpad($minutes,2,'0')
			. ($secs ? $sep . lpad($seconds,2,'0') : '' )
			. ' ' . strtoupper( $ampm . 'M');
	case 'ampm' :
    case 'US'   :
		return $ampm_hours . $sep . lpad($minutes,2,'0')
			. ($secs ? $sep . lpad($seconds,2,'0') : '' )
			. ' ' . strtolower( $ampm . 'm');
	case '24' :
		return lpad($hours,2,'0') . $sep . lpad($minutes,2,'0')
			. ($secs ? $sep . lpad($seconds,2,'0') : '' );
	case 'NO_SEP_SHORT':
		$secs = false;
	case 'NO_SEP':
		$sep = '';
	case 'SQL' :
		return lpad($hours,2,'0') . $sep . lpad($minutes,2,'0')
			. ($secs ? $sep . lpad($seconds,2,'0') : '');
    case 'LDAP':
           return str_replace('-','',dateof($datetime,'SQL')).str_replace(':','',orr(timeof($datetime,'SQL'),'00:00:00'));
    case 'ICS':
               return str_replace('-','',dateof($datetime,'SQL')).'T'.str_replace(':','',orr(timeof($datetime,'SQL'),'00:00:00'));
	default :
		return false;
	}
}

function datetimeof( $datetime='now', $format="SQL" , $lines='')
{
    if ( strtolower($datetime) == "now" )
    {
        $datetime = date( "Y-m-d H:i:s" );
    }

    switch ($format) {
    case 'ISO':
	    return dateof($datetime,'SQL').'T'.(($t=timeof($datetime,'SQL')) ? $t : '00:00:00');
    case 'LDAP':
        return str_replace('-','',dateof($datetime,'SQL')).str_replace(':','',orr(timeof($datetime,'SQL'),'00:00:00'));
    case 'ICS':
        return str_replace('-','',dateof($datetime,'SQL')).'T'.str_replace(':','',orr(timeof($datetime,'SQL'),'00:00:00'));
	case '@':
		return dateof($datetime).'@'.timeof($datetime);
	}

    if (!dateof($datetime) or !timeof($datetime)) { //if anything goes wonky w/ date times, this might be the culprit - JH 7/7/05
	    return false;
    }

    return 	!be_null(trim($datetime))
	    ? 	dateof( $datetime, $format ) . ($lines=='TWO' ? '<br />':' ')
	    . ($lines=='TWO' 
		 ? smaller(timeof( $datetime, ($format=="US" ? "ampm" : $format) )) //I removed the substr(datetime,-8) bit -- if anything breaks, put it back
		 : timeof( $datetime, ($format=="US" ? "ampm" : $format) ) )
	    : false;
}

function is_assoc_array($ary)
{
	// determine if an array is associative
	foreach ($ary as $key=>$value) {
		if (! is_numeric($key)) {
			return true;
		}
	}
	return false;
}

function is_interval($interval)
{
  	if (preg_match('/([0-9]*\s*(years?|months?|mons?|weeks?|days?|hours?|minutes?|seconds?))|([0-9]{2}:[0-9]{2}:[0-9]{2})/i',$interval,$m)
	    and $left = preg_split('/([0-9]*\s*(years?|months?|mons?|weeks?|days?|hours?|minutes?|seconds?))|([0-9]{2}:[0-9]{2}:[0-9]{2})/i',$interval)) {
		$left = array_filter(str_replace(array(',',' '),'',$left)); //get rid of allowable content

		if (empty($left)) {
			return true;
		}
	}
	return false;
}

function is_timestamp( $datetime='now',$format='SQL' )
{
    if ( strtolower($datetime) == 'now' )
    {
        $datetime = date( 'Y-m-d H:i:s' );
    }
    $date=dateof($datetime, $format);
    $time=timeof(substr($datetime,-8),($format=='US' ? 'ampm' : $format));
    return (!$date || !$time) 
	  ? false
	  : $date.' '.$time;
}

function datetotimestamp($date)
{
	//fixme: this should be merged into datetimeof, but in the interest of not breaking anything
	//I am inserting this extra function for now (note, datetimeof, if passed a date, won't return a proper timestamp)
	return dateof($date)
		? dateof($date,'SQL').' '.orr(timeof($date,'SQL'),'00:00:00')
		: false;
}

function dateof( $datetime,$format="",$long="" )
{
	/*
	 *
	 * FIXME: this should be moved to dates.php
	 *
	 * Return the date portion of a date/time
	 *
	 * This function will also try to determine the format of $datetime
	 * and return false if it is unable to process it
	 *
	 * $format options:
	 *    US = mm/dd/yy (note, this is really m/d/yy)
	 *    US_SHORT = mm/dd
	 *    US_PAD = mm/dd/yyyy (with mm and dd always 2 digits)
	 *    US_LONG = mm/dd/yyyy
	 *    EU = dd-mm-yy
	 *    SQL = yyyy-mm-dd
	 *    TS = Unix Timestamp (uses strtotime), but doesn't deal w/ time portion
	 *    WORDY = January 1, 2005
	 *	  MONTH = 01/05
	 *	  MONTH_WORDY = January, 2005
	 *	  MONTH_SQL = 2005-01
	 *    NO_SEP = yyyymmdd
	 *    NO_SEP_SHORT = yymmdd
	 *	
	 *  $long will output 4 digit years.
	 *
	 */

	$format = orr($format,'US_LONG');

	if ($format=='US_LONG') { //the 4 digit year was already built in - JH 7/12/04
	    $format='US';
	    $long=true;
	}

	if (in_array($format,array('SQL','NO_SEP'))) {
		$long = true;
	}
	// Regexes: 

	//FIXME: should work with centuries other than 19 and 20...

	$re_mon    = '(0?[1-9]|1[0-2])';
	$re_day    = '(31|30|[12][0-9]|0?[1-9])';
	$re_year   = '(19|20)?([0-9][0-9])';
	$re_year4  = '(19|20)([0-9][0-9])';
	$us_sep    = '/';
	$re_us_sep    = '\/';
	$eu_sep    = '-';
	$sql_sep   = '-';
	$re_sql_sep   = '-';
	$re_anchor = '^';     //start of string
	$re_term   = '(.*)$'; //end of string extra ( could be a time)

    $re_us_date      = $re_anchor . $re_mon . $re_us_sep . $re_day . $re_us_sep . $re_year . $re_term;
    $re_sql_date     = $re_anchor . $re_year4 . $re_sql_sep . $re_mon . $re_sql_sep . $re_day . $re_term;
    $re_sql_date2    = $re_anchor . $re_year . $re_sql_sep . $re_mon . $re_sql_sep . $re_day . $re_term;
    $re_eu_date      = $re_anchor . $re_day . $eu_sep . $re_mon . $eu_sep . $re_year . $re_term;
    // $re_us_date4 = $re_mon . $re_us_sep . $re_day . $re_us_sep . $re_year4;
    // $re_eu_date4 = $re_year4 . $eu_sep . $re_mon . $eu_sep . $re_day;
    // these currently not used...

    if ( strtolower($datetime)=='now' ) {
	    $datetime=time();
    }

    if ( is_numeric( $datetime ) ) { // UNIX TIMESTAMP
	    $datetime = date( 'Y-m-d H:i:s', $datetime );
    }

    if ( preg_match( '/'.$re_sql_date.'/', $datetime, $results ) ) { // sql format?

	    // outline("matched sql");
	    $century   = intval($results[1]);
	    $year      = intval($results[2]);
	    $month     = intval($results[3]);
	    $day       = intval($results[4]);
	    $remainder = $results[5];

    } elseif ( preg_match( '/'.$re_us_date.'/', $datetime, $results )) { // us format?

	    $month     = intval($results[1]);
	    $day       = intval($results[2]);
	    $century   = intval($results[3]);
	    $year      = intval($results[4]);
	    $remainder = $results[5];
	    // outline("matched us");
	    
    } elseif ( preg_match( '/'.$re_eu_date.'/', $datetime, $results )) { // eu format?
	    
	    $day       = intval($results[1]);
	    $month     = intval($results[2]);
	    $century   = intval($results[3]);
	    $year      = intval($results[4]);
	    $remainder = $results[5];
	    // outline("matched eu: $re_eu_date");
	    
    } elseif ( preg_match( '/'.$re_sql_date2.'/', $datetime, $results ) ) { // as a last attempt,
	    
	    // try mangled sql, with yy-mm-dd.
	    // outline( "trying mangled sql");
	    $century   = intval($results[1]);
	    $year      = intval($results[2]);
	    $month     = intval($results[3]);
	    $day       = intval($results[4]);
	    $remainder = $results[5];

    } else {

        return false;  // couldn't find valid date.

    }

    // outline("Century: $century, Year: $year, Month: $month, Day: $day");

    //CHECK remainder for timestamp
    if (!be_null($remainder) && !timeof($remainder)) {
	    return false;
    }

    // a few val checks for bad 31st or feb dates here:
    //FIXME: leap year logic isn't complete (not years divisible by 100, except if also by 400)
    //       not a simple fix, since we aren't always dealing w/ 4-digit years.
    if (
        ($month == 2 && $day > 29)
        || ($month == 2 && $day == 29 && ($year/4 <> intval($year/4) ) )
        || ($day == 31 && in_array($month, array(4,6,9,11) ) )
    )
    {
        return false;
    }

    if ( ! $century ) { // two-digit year--which century?

	    $century = ($year < AG_DATE_CENTURY_CUTOFF) ? 20 : 19;

    }

    switch ($format) {
    case 'US' :
	    return $month . $us_sep . $day . $us_sep
		    . ($long ? $century : '' ) . lpad($year,2,'0');
    case 'US_SHORT' :
	    return $month . $us_sep . $day;
    case 'US_PAD' :
	    return lpad($month,2,'0') . $us_sep . lpad($day,2,'0') . $us_sep
		    . $century . lpad($year,2,'0');
    case 'EU' :
	    return $day . $eu_sep . $month . $eu_sep
		    . ($long ? $century : '' ) . lpad($year,2,'0');
    case 'NO_SEP_SHORT':
	    $long = false;
    case 'NO_SEP':
	    $sql_sep = '';
    case 'SQL' :
	    return ($long ? $century : ''). lpad($year,2,'0') . $sql_sep .
		    lpad($month,2,'0') .  $sql_sep . lpad($day,2,'0');
    case 'TS' :
	    return strtotime($century . lpad($year,2,'0') . $sql_sep .
				   lpad($month,2,'0') .  $sql_sep . lpad($day,2,'0'));
    case 'WORDY' :
	    return date('F j, Y',strtotime($century . lpad($year,2,'0') . $sql_sep .
						     lpad($month,2,'0') .  $sql_sep . lpad($day,2,'0')));
    case 'MONTH_WORDY' :
	    return date('F, Y',strtotime($century . lpad($year,2,'0') . $sql_sep .
						     lpad($month,2,'0') .  $sql_sep . lpad($day,2,'0')));
    case 'MONTH' :
	    return $month . $us_sep 
		    . ($long ? $century : '' ) . lpad($year,2,'0');
    case 'MONTH_SQL' :
	    return ($long ? $century : ''). lpad($year,2,'0') . $sql_sep .
		    lpad($month,2,'0');
    default :
	    return false;
    }

}

// where time is formatted hours:minutes (e.g. 17:30)
function get_minutes($time)
{
    $sep = ':';
    $t = explode($sep, $time);
    //(hours*60) + minutes
    $total = $t[0]*60 + $t[1];
    return $total;

}

function getmicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function ssn_of( $ssn, $format='default' )
{
// determine whether a string is a Social Security Number,
// and return it, optionally in a format
// (normal would be XXX-YY-ZZZZ, but you might want XXXYYZZZZ

	if (preg_match('/^([0-9]{3})[-]?([0-9]{2})[-]?([0-9]{4})$/', $ssn, $matches))
	{
	      switch($format)
		    {
		    case "no_dash":
			  return $matches[1] . $matches[2] . $matches[3];
		    case "dash":
		    default :
			  return $matches[1] . "-" . $matches[2] . "-" . $matches[3];
		    }
	}
	else return false;
}

function phone_of($phone,$format='') {
	//return false or a phone number of the form (206) 123-4567
	$legal_chars=array('(',')','-',' ','[',']','{','}','<','>');
	$area_code = AG_DEFAULT_PHONE_AREA_CODE;

	//This function is not yet perfected (it will fail if someone uses
	// (123) 4568 and return this: (206) 123-4568 ...but I don't see this as a critical problem ...
	$PHONE=str_replace($legal_chars,array(),$phone);
	if (!is_numeric($PHONE)) {
		return false;
	}
	$len=strlen($PHONE);
	switch($len) {
	case 10:
		$area_code=substr($PHONE,0,3);
		$PHONE=substr($PHONE,3);
	case 7:
		$pre = substr($PHONE,0,3);
		$main = substr($PHONE,3);
		$new_phone = '('.$area_code.') '.$pre.'-'.$main;
		return $format=='num' ? $area_code.$pre.$main : $new_phone;
	default:
		return false;
	}
}

function zipcode_of( $zip, $format="default" )
{
// determine whether a string is a zip code,
// and return it, optionally in a format

    if (preg_match('/^([0-9]{5})[-]?([0-9]{4})?$/', $zip, $matches))
    {
          switch($format)
            {
                case "zip5" :
                    return $matches[1];
                case "default" :
                default :
                  return $matches[1] . ($matches[2] ? "-" . $matches[2] : "");
            }
    }
    else return false;
}

function currency_of($money,$format='display') {

	$money = str_replace(',','',str_replace('$','',$money));
	if (!is_numeric($money)) { return false; }
	switch (strtolower($format)) {
	case 'sql':
		return number_format($money,2,'.','');
	case 'display':
	default:
		return '$'.number_format($money,2,'.',',');
	}
}

function ssn_flip($ssn,$digit="9")
{
      //flip digits around to find similar SSNs
      //flip the $digit with the $digit-1
      $digit=$digit-1;
      $ssn=ssn_of($ssn,"no_dash");
      $tmp_ssn=ssn_array($ssn);
      $dig=$tmp_ssn[$digit-1];
      $tmp_ssn[$digit-1]=$tmp_ssn[$digit];
      $tmp_ssn[$digit]=$dig;
	return ssn_of(implode('',$tmp_ssn));
}

function ssn_array($ssn)
{
      //returns ssn as a 9 element array with each digit
      $ssn=ssn_of($ssn,"no_dash");
      for ($i=0;$i<9;$i++)
	    {
		  $new_ssn[$i] = substr($ssn,$i,1);
	    }
      return $new_ssn;
}

function red( $text )
// return text in red
{
	switch (AG_OUTPUT_MODE)
	{
		case "HTML" :
			return "<font color=\"#FF0000\">$text</font>";
		case "TEXT" :
	default :
			return $text;
	}
}

function black( $text )
// return text in black
{
    return "<font color=\"#000000\">$text</font>";
}

function gray( $text )
// return text in gray
{
    return "<font color=\"#AFAFAF\">$text</font>";
}

function green( $text )
// return text in green
{
    return "<font color=\"#00FF00\">$text</font>";
}

function blue( $text )
{
	// return text in blue
	return span($text,' style="color: #0000ff;"');
}

function white( $text )
{
    return "<font color=\"#FFFFFF\">$text</font>";
}

function color( $text, $color )
{
    return "<font color=\"$color\">$text</font>";
}

function out( $a )
{
    echo $a . "\n";
	return true;
}

function outline( $a="", $lines=1 )
{
    out( oline( $a, $lines ));
}

function oline( $a="", $lines=1, $mode="")
{
	switch (orr($mode,AG_OUTPUT_MODE))
	{
		case "HTML" :
			$sep="<br />\n";
			break;
		case "TEXT" :
			$sep="\n";
	}
	return $a . str_repeat($sep,$lines);
}

function para($a,$options='')
{
    return ("<p $options>\n$a\n</p>\n");
}

function indent($a="", $spaces="9")
{
	return str_repeat("&nbsp;",$spaces) . $a;
}

function bigger( $a,$scale=1 )
{
	switch (AG_OUTPUT_MODE)
	{
		case "HTML" :
			return("<font size=\"+$scale\">$a</font>");
		case "TEXT" :
	default :
			return $a;
	}
}

function smaller( $a, $scale=1 )
{
	switch (AG_OUTPUT_MODE)
	{
		case "HTML" :
			return("<font size=\"-$scale\">$a</font>");
			break;
		case "TEXT" :
	default :
			return $a;
			break;
	}
}

function hrule($a="")
{
     return ($a ? "<hr $a>" : "<hr>");
}

function headline( $a )
{
    out( head( $a ) );
}

function html_heading_tag($content,$level='1',$options='')
{
	return parse_html_tag('h'.$level,$content,$options);
}

function head( $a, $options='' )
{
	return html_heading_tag($a,'1',$options);
}

function subhead( $a , $options='')
{
	return html_heading_tag($a,'2',$options);
}

function subheadline( $a )
{
    out ( subhead ( center( $a ) ) );
}

function section_title( $a , $options='')
{
	return html_heading_tag($a,'3',$options);
}

//the rest of the heading tags
function html_heading_1( $a, $options='') { return html_heading_tag($a,'1',$options); }
function html_heading_2( $a, $options='') { return html_heading_tag($a,'2',$options); }
function html_heading_3( $a, $options='') { return html_heading_tag($a,'3',$options); }
function html_heading_4( $a, $options='') { return html_heading_tag($a,'4',$options); }
function html_heading_5( $a, $options='') { return html_heading_tag($a,'5',$options); }
function html_heading_6( $a, $options='') { return html_heading_tag($a,'6',$options); }

function parse_html_tag($tag,$content,$options='')
{
	if ($options) {
		$options = ' '.trim($options); //insure white-space
	}
	return '<'.$tag.$options.'>'.$content.'</'.$tag.'>';
}

function html_pre($t,$opt='')
{
	switch (AG_OUTPUT_MODE) {
	case 'TEXT':
		return $t;
	case 'HTML':
		return parse_html_tag('pre',$t,$opt);
	}
}

function headtitle( $a, $refresh=0 )
{
    html_header($a,$refresh);
    headline( center( $a ));
}

function style( $ml, $style='type="text/css"' )
{
	return "<style $style>$ml</style>";
}

function bold( $a )
{
	switch (AG_OUTPUT_MODE) {
	case 'TEXT':
		return '*'.$a.'*';
	case 'HTML':
	default:
		return '<b>' . $a . '</b>';
	}
}

function italic( $a )
{
    return "<i>" . $a . "</i>";
}

function strike( $a )
{
    return "<font style=\"text-decoration:  line-through\">$a</font>";
}

function underline( $a )
{
	switch (AG_OUTPUT_MODE) {
	case 'TEXT':
		return '_'.$a.'_';
	case 'HTML':
	default:
		return '<u>' . $a . '</u>';
	}
}

function center( $a, $opts='' )
{
      return "<div align=\"center\" $opts>$a</div>\n";
}

function right( $a , $opts='')
{
      return "<div align=\"right\" $opts>$a</div>\n";
}

function left( $a )
{
      return "<div align=\"left\">$a</div>\n";
}

function title( $a )
{
	return "<title>" . strip_tags($a) . "</title>\n";
}

function html_fieldset( $legend,$cont,$opts='')
{
	$opts = $opts ? ' '.trim($opts) : '';
	return '<fieldset'.$opts.'><legend>'.$legend.'</legend>'
		.$cont.'</fieldset>';

}

function html_list_start($options='')
{
	return '<ul '.$options.'>';
}

function html_list_end()
{
	return '</ul>';
}

function html_list($items,$options='')
{
	return html_list_start($options) . $items . html_list_end();
}

function html_list_ordered($items,$options='')
{
	return '<ol '.$options.'>'.$items.'</ol>';
}

function html_list_item($content,$options='')
{
	return '<li '.$options.'>'.$content.'</li>';
}

function html_label($contents,$for,$opts='')
{
	$opts .= ' for="'.$for.'"';
	return parse_html_tag('label',$contents,$opts);
}

function form( $contents, $next_form="", $name="", $extra="")
{
	return formto( $next_form,$name,$extra) . $contents . formend();
} 

function formto( $next_form="", $name="", $extra="", $method='post' )
// Start a form, specify page to send to.
// form name get stored globally, so it can
// get referenced by JS date selector functions.
{
	$next_form=orr($next_form,$_SERVER['PHP_SELF']);
    if ($name=="")
    {
        $GLOBALS["form_num"]++;
        $GLOBALS["form_name"]="form" . $GLOBALS["form_num"];
        $name = $GLOBALS["form_name"];
    }
    else
    {
        $GLOBALS["form_name"]=$name;
    }
	return ("<form method=\"$method\" action=\"$next_form\" name=\"$name\""
	. ($extra ? " $extra" : "") . ">\n");
}

function yesno_form($yesvar,$yesval,$novar,$noval="")
{
      // a simple two button yes/no form 
      // if $noval isn't submitted, the form will return $novar in place of the 'no' button
      $form = tablestart_blank()
	    . rowrlcell(
			formto()
			. button("Yes")
			. hiddenvar($yesvar,$yesval)
			. formend(),
			$noval 
			? ( formto()
				   . button("No")
				   . hiddenvar($novar,$noval)
				   . formend())
			: $novar )
	    . tableend();
      return $form;
}

function form_field($type,$varname,$default='',$options='', $max_file_size='')
{
      // collect all of the form field generation into one function
      // cannot yet deal with more complex issues like lookups, and checkboxes
      // so call and make sure it returns true, otherwise handle as needed.

      $type=strtolower($type);
      if ( $type=='integer' || $type=='number' || $type=='float' || $type=='ssn' || substr($type,0,7)=='numeric') 
	    return formvartext($varname,$default,$options);
      elseif ($type=='text' || $type=='varchar' || $type=='character' || $type=='interval')
	    return formvartext($varname,$default,$options);
      elseif ($type=='textarea')
	    return formtextarea($varname,$default,$options);
      elseif (substr($type,0,4)=='date')
	    return formdate($varname,$default,'Calendar',$options);
      elseif ($type=='time')
	    return formtime($varname,$default);
      elseif ($type=='password')
	    return formpassword($varname,$default,$options);
      elseif ($type=='attachment') {
		//either show existing file or show form to upload new one
		return orr($default, hiddenvar('MAX_FILE_SIZE',$max_file_size) . formfile($varname, $options));		
      } elseif ($type == 'boolean')
      {
		if (is_array($options)) {
			$true_opts=$options['true'];
			$false_opts=$options['false'];
		} else {
			$true_opts=$false_opts=$options;
		}
		
		$field = span(span(formradio($varname, 't', sql_true($default) ? 'checked="checked"' : '',$true_opts)
				  . ' Yes',' class="radioButtonSet"')
			. span(formradio($varname, 'f', (!sql_true($default)) && (!be_null($default))  ? 'checked="checked"' : '',$false_opts)
				 . ' No',' class="radioButtonSet"'),' style="white-space: nowrap;"');
      }
      elseif ($type == 'boolcheck')
      {
	    return formcheck($varname,$default,$options);
      }
      else
	    return false;
      return $field;
}

function formBoolean($varname, $default='',$check_value_1='t', $check_label_1=' Yes', $check_value_2='f', $check_label_2=' No')
{
      // a two option boolean control, will default to value 1 if default evaluates to false...
      // see list_control() for usage.
      $default=orr($default,$check_value_1);
      return table_blank(row(
			     cell(formradio($varname, $check_value_1, $default===$check_value_1 ? 'checked="checked"' : ''))
			     . cell($check_label_1)
			     . cell(formradio($varname, $check_value_2, $default===$check_value_2 ? 'checked="checked"' : ''))
			     . cell($check_label_2)));
}

function formvartext( $varname, $default='', $options='' )
{
    return ("<input type=\"text\" name=\"$varname\""
		. ( ($default || (is_numeric($default) && ($default==0))) ? (" value=\"".htmlentities($default)."\"") : "" )
    . ($options ? (" " . $options) : "" )
    . "/>\n");
}

function formpassword( $varname, $default="", $options="" )
{
      //added autocomplete="off" in an attempt to foil mozilla
    return ("<input type=\"password\" autocomplete=\"off\" name=\"$varname\""
    . ( ($default || (is_numeric($default) && ($default==0))) ? (" value=\"".$default."\"") : "" )
    . ($options ? (" " . $options) : "" )
    . "/>\n");
}

function formpassword_md5($varname, $auto_focus=true, $with_hash=false, $hash_varname='',$options='')
{
      //use to pass user's password through an md5 hash prior to 
      //sending over the network. 
      //Automatically generates the required submit button.	

	$GLOBALS['AG_HEAD_TAG'].=md5_header_tags($varname,$with_hash,$hash_varname);

	if ($auto_focus) {
		form_field_focus($varname);
	}
      return formpassword($varname,'',$options);
}

function form_field_focus($varname)
{
	//this function must be called while the desired form is still the global form variable
	$form = $GLOBALS['form_name'];
 	Java_Engine::add_javascript_body(Java_Engine::on_event('onLoad','document.'.$form.'.'.htmlentities($varname).'.focus()'));
}

function md5_header_tags($varname="",$with_hash=false,$hash_varname='')
{
	global $form_name, $agency_home_url;
      $varname=orr($varname,"password");
	if (!$with_hash) {
		// The DoChallengeResponse() function was moved to agency.js
		// FIXME:  The hash version should move as well
		return '';
	}

      return  "
      <script language=\"javascript\" type=\"text/javascript\">
	 <!--
	    function do".($with_hash ? 'Hash' : '')."ChallengeResponse() {".
		($with_hash
		 ? "         str = MD5(MD5(document.$form_name.$varname.value)+document.$form_name.$hash_varname.value)"
		 : "	       str = MD5(document.$form_name.$varname.value)"
		 )
		."
            document.$form_name.$varname.value = str;
            }
         // -->
      </script>\n";
}

function formfile( $varname, $options="" )
{
    return ("<input type=\"file\" name=\"$varname\""
    . ($options ? (" " . $options) : "" )
    . "/>\n");
}

function formdate( $varname, $default="", $button_text="Calendar" ,$options='')
{
	$default = dateof($default );
	$class='class="field_date"';
	if (!stristr($options,$class)) {
		$options .= "$options $class ";
	}
	return formvartext( $varname, $default, $options );
}

function formdate_range($varname,$default='')
{
	//use daterange (object) for default, otherwise, will assume only one
	if (is_object($default))
	{
		$d1=$default->start;
		$d2=$default->end;
	}
	else
	{
		$d1=$default;
	}
	$var1=$varname.'_start';
	$var2=$varname.'_end';
	$out=tablestart()
		. row(cell('start:').cell(formdate($var1,$d1)))
		. row(cell('end:').cell(formdate($var2,$d2)))
		.tableend();
	return $out;
}

function formtime($varname, $default="",$options=NULL)
{
    //  Generates a drop-down list of times.
    //  Send the string "now" for current time
    // $default is datetime
    if ($default == "now")
    {
        $default = time();
    }

    if ($default)
    {
        $default = timeof($default);
    }
	$class='class="field_time"';
	if (!stristr($options,$class)) {
		$options .= " $class ";
	}
    return formvartext($varname, $default,$options);
}

function formtextarea( $varname, $default="", $options="", $x=60, $y=10 )
{
    return ("<textarea name=\"$varname\" cols=\"$x\" rows=\"$y\" $options>$default</textarea>\n ");
}

function formcheck( $varname, $checked="", $options='' )
{
    return ("<input type=\"checkbox\" name=\"$varname\""
    . ($checked ? ' checked="checked" ' : " ") . $options. "/>\n");
}

function form_valcheck( $varname, $value, $checked=false,$options=null )
{
	return '<input type="checkbox" name="'.$varname.'" value="'.$value.'"'
		. ($checked ? ' checked="checked"' : null) . $options."/>\n";
}

function formradio( $varname, $value, $checked="", $options='' )
{
    return ("<input type=\"radio\" "
		. ($checked ? 'checked="checked" ' : "")		
		. "name=\"$varname\" value=\"$value\""
		. $options
		. "/>\n");

}

function formradio_wipeout ($varname,$label='')
{
	//provides a js button to wipeout radio box values in a form
	global $AG_HEAD_TAG;
	$js = Java_Engine::get_js('
					  function clear_radio( nam ) {
						  for (var i=0; i<nam.length; i++) {
							  nam[i].checked=false;
						  }
						  
					  }');
	if (!strpos($AG_HEAD_TAG,$js)) {
		$AG_HEAD_TAG .= $js;
	}
	$label = orr($label,smaller(' unset',2));
	$onclick = 'javascript: clear_radio(document.'.$GLOBALS['form_name'].'.elements[\''.$varname.'\']); return false';
	return hlink('#',$label,'',' onclick="'.$onclick.'"');

}

function formvar_wipeout($varname,$label='',$submit=true)
{
	$label = orr($label,smaller(' unset',2));
	return hlink('#',$label,NULL,'class="formWipeoutLink"').span($varname,'class="formWipeoutInfo serverData"');
}

function yes_no_radio($name, $label, $default="")
{
    return formradio("$name", "Y", ($default=="Y") )
            . "&nbsp;Yes&nbsp;&nbsp;\n"
            . formradio("$name", "N", ($default=="N") )
            . "&nbsp;No&nbsp;&nbsp;\n"
            . $label . "\n";
}

function alert_mark( $message="" )
{
	$out = $message
		? webify('>>>> ') . $message . webify(' <<<<')
		: webify('>>>> ');
	return span($out,' class="alertMark"');
}

function selectto_multiple($varname, $size='',$options='')
{
	$size = orr($size,7);
	$options = 'multiple="multiple" size="'.$size.'" '.trim($options);
	return selectto($varname,$options);
}

function selectto( $varname, $options='' )
{
    return( "<select name=\"$varname\" ".$options.">\n");
}

function selectitem( $value, $text="", $default="", $options='')
{
    $text = $text ? $text : $value;
    return( '<option value="'.htmlentities($value).'"'
        . ($default ? ' selected' : '') 
		. ($options ? ' '.trim($options).' ' : ''). '>'.htmlentities($text).'</option>'."\n" );
}

function html_optgroup($options,$label,$opts="")
{
	return '<optgroup label="'.$label.'" '.$opts.'>'."\n"
		. $options
		.'</optgroup>';
}

function selectend()
{
    return( "</select>\n" );
}

function call_java_confirm($text)
{
	return 'javascript: return confirm(\''.$text.'\')';
}

function formend()
{
    return ("</form>\n");
}

function tablestart_blank( $title="", $options="")
{
	if (!stristr($options,'class')) {
		$options .= ' class=""';
	}
      return tablestart($title,$options);
}

function tablestart( $title="", $options="" )
{
	if (! stristr($options,"class"))
	{
		$options .= ' class="generalTable"';
	}
    return("<table $options>\n");
}


function tableend()
{
    return("</table>\n");
}

function table_blank($rows, $title='',$options='')
{
      if (! stristr($options,'class'))
      {
	    $options .= ' class=""';
      }
      return table($rows,$title,$options);
}

function table($rows,$title="",$options="")
{
	return tablestart( $title, $options )
			. $rows
			. tableend();
}

function rowstart($options="")
{
    return("<tr" . ($options ? " $options" : "") . ">\n");
}

function rowend()
{
    return("</tr>\n");
}

function row( $row, $options="" )
{
    return( rowstart($options) . $row . rowend() );
}

function rowbreak()
{
    return( rowend() . rowstart() );
}

function cellbreak()
{
    return( cellend() . cellstart() );
}

function cellstart()
{
    return ("<td>");
}

function cellend()
{
    return ("</td>\n");
}

function cell( $contents="", $args="",$tag='td' )
{
    if ($args) {
	    $args=' ' . $args;
    }
// If contents are blank, _or_ only tags, need to add a &nbsp;
    if ( (!$contents) || ($contents==smaller("")) || ($contents==smaller("",2)) ) {
	    $contents.='&nbsp;';
    }
    return '<'.$tag . $args . '>'.$contents.'</'.$tag.'>'."\n";
}

function h_cell($content='',$options='')
{
	return cell($content,$options,'th');
}

function boldcell( $contents, $args="" )
{
        return( cell( bold($contents), $args ));
}

function strikecell( $contents, $args="")
{
    return ($contents ? cell(strike($contents), $args) : cell(""));
}

function leftcell( $contents, $args="" )
{
    return( cell( $contents,  'align="left" ' . $args ));
}

function topcell( $contents, $args="" )
{
    return( cell( $contents,  'valign="top" ' . $args ));
}

function bottomcell( $contents, $args="" )
{
    return( cell( $contents,  'valign="bottom" ' . $args ));
}

function rightcell( $contents, $args="" )
{
    return( cell( $contents, 'align="right" ' . $args ));
}

function centercell( $contents, $args="" )
{
    return( cell( $contents, 'align="center" ' . $args ));
}

function rlcell( $rcell, $lcell, $args="" )
{
    return( rightcell( $rcell,$args ) . leftcell( $lcell, $args) );
}

function rowrlcell( $rcell, $lcell, $args="" )
{
    return( row( rlcell( $rcell,$lcell,$args )  ) );
}


function js_link($label,$js,$options='')
{
	$options = 'onclick="javascript:'.htmlentities($js).'" '.trim($options);
	return hlink('javascript:void(0);',$label,'',$options);
}

function js_add_content_link($content,$id,$label,$options='')
{
	$js = 'document.getElementById(\''.$id.'\').innerHTML+=\''.str_replace("\n",' ',$content).'\'';

	return js_link($label,$js,$options);
}

function js_chop_and_hide($str,$len,$label='... (more)')
{
	/*
	 * Splits a string at the first word near $len, and hides the rest
	 *
	 * Note: this function assumes that $str is already free of 
	 * html tags (which could break if $len falls in the middle)
	 */

	static $id;
	$id ++;

	if (strlen($str) > $len) {
		//find first word greater than $len
		$cut = strpos($str,' ',$len)+1;
		$pre_link_str = substr($str,0,$cut);
		$hidden_str = substr($str,$cut,strlen($str));
		$show_link = html_no_print(js_link(' [hide]','document.getElementById("AGENCYchopNhide'.$id.'").style.display="none";document.getElementById("AGENCYchopNhideShow'.$id.'").style.display="inline"',' class="fancyLink"'));
		$str = $pre_link_str.js_link($label,'document.getElementById("AGENCYchopNhide'.$id.'").style.display="inline";this.style.display="none"',' id="AGENCYchopNhideShow'.$id.'"').span($hidden_str . $show_link,' id="AGENCYchopNhide'.$id.'" style="display: none;"');
	}

	return $str;

}

function js_link_disable($message = '')
{

	/*
	 * Disable a link by passing as the onclick argument, with
	 * an optional message to be displayed via a javascript alert
	 */

	if ($message) {

		$message = 'alert('.enquote1(htmlentities($message,ENT_QUOTES)).');';

	}

	return 'javascript: '. $message . ' return false;';

}

function hiddenvar( $name, $value='', $options='' )
{
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\" $options/>\n";
}

function hiddenarray( $name, $array )
{
	foreach ($array as $key=>$value)
	{
		$out .= hiddenvar( $name . "[" . $key . "]",$value);
	}
	return $out;
}

function orr()
{
      // IF ALL TEST TO be_null(), THE LAST ARGUMENT IS RETURNED
      $args=func_get_args();
      foreach ($args as $a) {
		if (!be_null($a)) {
			return $a;
		}
      }
      return $a;
}

function orrn()
{
      // IF ALL TEST TO is_null(), THE LAST ARGUMENT IS RETURNED
      $args=func_get_args();
      foreach ($args as $a) {
		if (!is_null($a)) {
			return $a;
		}
      }
      return $a;
}

function not_is_null($o)
{
	//for use with array_filter
	return !is_null($o);
}

function not_be_null($o)
{
	//for use with array_filter
	return !be_null($o);
}

function confidential( $options='',$rel_size=0, $output_mode='' )
{
	$header = 'Notice of Confidentiality';
	$con_text=$GLOBALS['AG_TEXT']['CONFIDENTIAL_STATEMENT'];
	switch (orr($output_mode,AG_OUTPUT_MODE)) {
		case 'HTML' :
			$con_text_f = ($rel_size > 0) ? bigger($con_text) : smaller($con_text);
			//return tablestart($header, "border=\"5\" style=\"clear: both;\"" . $options ) .
			return tablestart($header, 'class="confidentialBox" ' . $options ) .
			row(cell(bigger($con_text_f))) . tableend();
		case 'TEXT' :
		default :
			return $header . ': ' . $con_text;
	}
}

function html_start($title='')
{ //these were written to capture rather than send the text...
	//uncomment this when we are good and ready to be displayed in standards compliance mode

	html_meta('Content-Type','text/html; charset=UTF-8'); //fall-back character encoding
      return ''//'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n"
		. "<html>\n<head>\n"
  		//. '<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />'
  		. '<link rel="shortcut icon" href="images/agency_logo_small.png" type="image/png" />'
		. ($title ? title($title) : '')
		. ($GLOBALS['AG_HEAD_TAG'] ? $GLOBALS['AG_HEAD_TAG'] : '')
		. "\n</head>\n<body".$GLOBALS['AG_BODY_TAG_OPTIONS'].">\n"
		. anchor('TOP');
}

function html_end()
{
      return "\n</body>\n</html>\n";
}

function html_meta($http_equiv,$content)
{
	$GLOBALS['AG_HEAD_TAG'] .= '<meta http-equiv="'.$http_equiv.'" content="'.$content.'"/>'."\n";
}

function html_header($title, $refresh=0)
{
	//uncomment this when we are good and ready to be displayed in standards compliance mode
//  	out('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n");
	out("<html>\n<head>");
	out(title($title));
	html_meta('Content-Type','text/html; charset=UTF-8'); //fall-back character encoding
	//out('<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />'."\n");
	out('<link rel="shortcut icon" href="images/agency_logo_small.png" type="image/png" />'."\n");
	out($GLOBALS['AG_HEAD_TAG']);
	out("</head>\n<body".$GLOBALS['AG_BODY_TAG_OPTIONS'].">\n");
 	out(anchor('TOP'));
}

function html_footer()
{
    out("</body>\n</html>\n");
}

function help($title='',$text='',$label='',$options=null,$expanded=false,$new_window=false)
{
	static $i = 0;
	$i ++;
	if ( (!$text)
		&& ($rec=array_shift(get_generic(array('help_title'=>$title),'','','help'))) ) {
			$text = sql_true($rec['is_html']) ? $rec['help_text'] : str_replace('<br/>','<p>',webify($rec['help_text']));
	}
	$label = orr($label,'Help');
	$id = str_replace(' ','_',$title).'agencyHelp'.$i;
	$link = smaller(Java_Engine::toggle_id_display($label,$id,'block'));
	$clink = smaller(Java_Engine::toggle_id_display('close',$id,'block'));
	$display = $expanded ? 'block' : 'none';
	if ($title) {
		$title = html_heading_3(ucwords(str_replace('_',' ',$title)));
	}
 	return $new_window 
			? hlink("javascript: HelpWin('$title','" . addslashes($text) . "')", orr($label,"help with $title"),'',$options)
			: ( $link . div($title . $text. right($clink),$id,'class="agencyHelp" style="display: '.$display.'"'));
}

/* Writes a message to a file. Creates file if it doesn't exist.
* Use carefully:  'apache' user can write to only so many places
* Currently used by a cronjob (not run by apache) for testing bednights.
* $filename should include path, otherwise it writes to the current
* directory.
*/
function flag_file($filename, $message="")
{
    // add a check for permission
    $fh = fopen($filename,"w+");
    if ($fh)
    {
        fwrite($fh,$message);
	chown($filename,"apache");
        fclose($fh);
    }
    return;
}

function get_flag($filename)
{
    if (file_exists($filename))
    {
        $ret = 1;
    }
    else
    {
        $ret = 0;
    }
    return($ret);
}

function dig2( $int )
{
	// return a 2 digit number, either padded with 0 or truncated

	return substr( "0$int",-2);
}

function set_unavailable($label,$message)
{
    outline(bigger(bold("$label is currently unavailable")));
    outline(bigger($message));
    exit;
}

// set boolean in flag record
function set_sys_flag($flag, $value="true")
{
    global $flag_table, $UID, $sys_user;
    
    if (get_sys_flag($flag))
    {    
        $values = array("is_flag" => $value, 
            "changed_at" => "NOW()", "changed_by" => (orr($UID,$sys_user)));
        $filt = array("flag_name" => $flag);
        if (@agency_query(sql_update($flag_table, $values, $filt)))
        {
            $result = true;
        }
        else 
        {
            log_error("Unable to set flag $flag to $value:  Please contact your
                System Administrator");
            $result = false;
        }
    }
    else
    {
        $result = false;
    }
    return $result;
}

// get flag record and return it if only one record is in the results
function get_sys_flag($name)
{
    global $flag_table;
    $flag_field = "flag_name";
    $res = agency_query("SELECT * FROM $flag_table", array($flag_field => $name));
    $count = sql_num_rows($res);    
    if ($count == 1)
    {
        $res = agency_query("SELECT * FROM $flag_table", 
            array($flag_field => $name));
        $result = $res;
    }
    elseif ($count > 1)
    {
        log_error("More than one $name flag exists.  Please contact your System
            Administrator");
        $result = false;
    }
    else
    {
        log_error("Flag $name doesn't exist.  Please contact your System
            Administrator.");
        $result = false;
    }
    return $result;    
}

function force_case($form_value,$fcase)
{
      $fcase = strtolower($fcase);
      switch($fcase)
	    {
	    case "upper" :
		  return strtoupper($form_value);
	    case "lower" :
		  return strtolower($form_value);
	    case "ucwords" :
		  return ucwords($form_value);
	    case "ucfirst" :
		  return ucfirst($form_value);
	    default :
		  return $form_value;
	    }
}

function cancel_button($cancel_url,$cancel_text,$def="",$action="")
{
	//fixme: this function should be phased out
      if (!($action))
	    {
		  $out = hlink($cancel_url,$cancel_text,"button");
	    }
      elseif ($action=="add")
	    {
		  if ($def["cancel_add_url"]) 
			$cancel_url = $def["cancel_add_url"];
		  $out = hlink($cancel_url,$cancel_text,"button");
	    }
      else
	    {
		  $out = formto()
			. hiddenvar("control[action]","view")
			. button($cancel_text)
			. formend();
	    }
      return $out;
}

function lpad($string,$n,$pad_string = ' ')
{
	return str_pad($string,$n,$pad_string,STR_PAD_LEFT);
}

function rpad($string,$n,$pad_string = ' ')
{
	return str_pad($string,$n,$pad_string,STR_PAD_RIGHT);
}

function bpad($string,$n,$pad_string = ' ')
{
	return str_pad($string,$n,$pad_string,STR_PAD_BOTH);
}

function is_valid_agency($value,$type,$label="",&$mesg)
{
//FIXME:  Doesn't seem to be used anywhere
      // A validity checker for specific AGENCY data such as client, staff.
      // Passes through to is_valid so that only one function is needed in practice.
      $valid=true;
      if (($type==AG_MAIN_OBJECT_DB) && (!is_client($value)) )
      {
		$def=get_def(AG_MAIN_OBJECT_DB);
	    $mesg.= $label ? oline('Field $label is not a valid ' . $def['singular']) : '';
	    $valid=false;
      }
      elseif ( ($type=="staff")  && (!is_staff($value)) )
      {
		$def=get_def('staff');
	    $mesg.= $label ? oline('Field $label is not a valid ' . $def['singular']) : '';
		$valid=false;
      }
      else
      {
	    $valid=is_valid($value,$type,$label,$mesg);
      }
      return $valid;
}

function form_encode($a,$MAIN)
{
      //ENCODE AN ARRAY INTO HTML HIDDEN VARIABLES
      //$MAIN IS THE TOP-LEVEL VARIABLE NAME
      //MAKES RECURSIVE CALLS TO ITSELF IN THE EVENT OF A RECURSIVE ARRAY
      if (!is_array($a)) {
		$a = addslashes($a);
		return hiddenvar($MAIN,$a);
      }

      foreach($a as $formvar => $val) {
		if (!is_array($val) and !is_object($val)) {
			$val = addslashes($val);
			$encoded .= hiddenvar(be_null($MAIN) ? $formvar :"{$MAIN}[{$formvar}]",htmlentities($val));
		} else {
			$encoded .= form_encode($val,be_null($MAIN) ? $formvar : "{$MAIN}[{$formvar}]");
		}
      }
      return $encoded;
}

function array_order($A,$KEY)
{
      // RETURN ARRAY WITH $key AS THE FIRST ELEMENT, 
      // PREVIOUS ARRAY ORDER IS PRESERVED
	if (is_array($A)) {
		$B = array();
		$B[$KEY]=$A[$KEY];
		unset($A[$KEY]);
		$B=array_merge($B,$A);
		return $B;
	}
	return $A;
}

function is_integer_all($value)
{
	/* 
	 * A more generous version of is_int, or is_integer in that
	 * it deals with strings too
	 */

 	return is_numeric($value) && (((string) $value)===((string) intval($value)));
}

function is_valid($value,$type)
{
      // Attempt at a more generic validity checker.

      if ( (($type=="integer") || ($type=="float") || ($type=="number")) && (!is_numeric($value)) )
      {
	    return false;
      }
      elseif ( ($type=='integer_db') and ( !(is_numeric($value) and (abs($value) < AG_POSTGRESQL_MAX_INT) )))
      {
	    return false;
      }
      elseif ( ($type=="integer") && ($value<>intval($value)) )
      {
	    return false;
      }
      elseif ( ($type=="date") && (!dateof($value)) )
      {
	    return false;
      }
      elseif ( ($type=="date_past") && (dateof($value,"SQL") > dateof("now","SQL")) )
      {
	    return false;
      }
      elseif ( ($type=="date_future") && (dateof($value,"SQL") < dateof("now","SQL")) )
      {
	    return false;
      }
      elseif ( ($type=="ssn")  && (!ssn_of($value)) )
      {
	    return false;
      }
      return true;
}

function html_comment($a)
{
      return '<!--'.$a.'-->';
}

function html_image($url,$options='')
{
      return "<img src=\"$url\" $options />";
}

function down_triangle($options='')
{
	global $AG_IMAGES;
      return html_image($AG_IMAGES['DOWN_TRIANGLE'],'align="middle"'.$options);
}

function up_triangle($options)
{
	global $AG_IMAGES;
      return html_image($AG_IMAGES['UP_TRIANGLE'],'align="middle"'.$options);
}

function imgTableHorz()
{
	global $AG_IMAGES;
      return html_image($AG_IMAGES['TABLE_HORZ'],'align="middle"');
}

function imgTableVert()
{
	global $AG_IMAGES;
      return html_image($AG_IMAGES['TABLE_VERT'],'align="middle"');
}

function update_session_variable($sess_name,$a)
{
      global $$sess_name;
	$_SESSION[$sess_name]=$$sess_name=array_merge(orr($_SESSION[$sess_name],array()),orr($a,array()));
}

function be_null( $val )
{
	// like is_null, only for our purposes
	return (!$val) && (!is_numeric($val));
}

function aan($text) {
      return (in_array(strtoupper(substr($text,0,1)),array('A','E','I','O','U','Y'))) ? 'an' : 'a'; //GRAMMAR GRAMMAR GRAMMAR
}

function str_plural($str,$count)
{
	//fixme: doesn't account for str ending in s
	return ($count > 1) ? $str.'s' : $str;
}

function loading_message($message='loading...')
{
	global $AG_IMAGES;
	return div(oline(smaller($message,2)).html_image($AG_IMAGES['page_loading_animation']),'page_loading',' style="float: left;"');
}

function agency_top_header($commands="")
{
	global $UID, $title, $database,$WHICH_DB,$testing_area_message,$db_server;
	global $AUID, $AG_AUTH;
	if (is_test_db()) {
		$test_db_warning=div('Warning: test database '
         . $database[$WHICH_DB] . '@' .$db_server[$WHICH_DB] . ' ' 
          .  help('test_database','','tell me more',' class="fancyLink"',false,true),'','class="agencyTestWarningBox"');
	}
	if (AG_SHOW_AUTH_TOP_LOGIN_BOX) {
		$login_box = $AG_AUTH->top_login_box(); //must come prior to AG_HEAD_TAG being output by html_header()
	}

	if ($AG_AUTH->must_change_password()) {
		$password_bar = div('You must change your password! '.link_password_change($UID,'Click Here to Change',' style="color: #fff;"')
					  ,'',' style="background-color: #f00; color: #fff; text-align: center; font-size: 155%;"');
		if ( basename($_SERVER['PHP_SELF']) != AG_STAFF_PAGE ) {
			out($password_bar);
			exit;
		}
	}
	send_quick_search_js();
	html_header($title);
	$out .= ($testing_area_message);
	$out .= $password_bar;

	if (has_perm('user_switch','S',orr($AUID,$UID))) {
		$tmp_id_check = $AUID ? "staff_id=$AUID" : 'FALSE';
		$switch_identity = 
			div(formto(htmlspecialchars($_SERVER['REQUEST_URI'])) 
				. selectto('ASSUME_IDENTITY')
				. selectitem('-1','(choose from list)')
				. do_pick_sql("SELECT staff_id AS value, CASE WHEN name_first < 'A' THEN name_last ELSE name_last || ', ' || name_first END as label FROM staff WHERE is_active AND ($tmp_id_check OR staff_id NOT IN (SELECT staff_id FROM permission_current WHERE permission_type_code='SUPER_USER')) AND NOT name_first < 'A' AND staff_id != sys_user() ORDER BY 2",$AUID,false)
				. selectend()
				. button("Switch Identity") 
				. formend(),'agencySuperUser');
		if ($AUID) {
			$curr_id = smaller(' | (current identity is ') . staff_link($UID) . smaller(') ');
			$switch_id1=$switch_identity;
		} else {
			$switch_id2=oline(div($switch_identity.toggle_label("Impersonate..."),'','class="hiddenDetail"'));
		}
	}
	$demo_mode_link = has_perm('demo_mode')
		? '&nbsp;&nbsp;|&nbsp;&nbsp;'.hlink($_SERVER['PHP_SELF'].'?demoMode='.($GLOBALS['AG_DEMO_MODE'] ? 'N' : 'Y')
			  ,'Turn Demo Mode '.($GLOBALS['AG_DEMO_MODE'] ? 'Off' : 'On'))
		: '';
	$staff_link=staff_link(orr($AUID,$UID));
	// FIXME: Can you assume Kiosk identity? Not sure.
	$user_logout_msg= ($AG_AUTH->kiosk_active())
		? smaller('You are running in Kiosk mode as ') . $staff_link . smaller( ' ('.Auth::logout('reset') .')')
		: smaller('If you are not ') . $staff_link . smaller(', please '.Auth::logout());
	   if ($update_engine=update_engine_control()) {
			$update_engine=oline(div($update_engine.toggle_label("Update engine..."),'','class="updateEngine hiddenDetail"'));
		}

		if (has_perm('admin')) { //FIXME: This is more a pref than a perm
			$engine_browser = oline(div(engine_browser_control(). toggle_label("Quick browse..."),'','class="engineBrowser hiddenDetail"'));
		}
       $out = show_top_nav(table(row(
                       cell($user_logout_msg . $curr_id
						 . $switch_id1
                         . smaller( $demo_mode_link)
                       . $user_msg . $test_db_warning, $test_style)
))
                        ,$commands,$switch_id2.$update_engine.$engine_browser.$login_box)
                . $out; //test box in middle
	out($out);
}

function show_top_nav( $firstcell="",$cells="",$auth='')
{
	global $colors,$AG_MENU_LINKS,$off,$AG_AGENCY_ADVISORY,$AG_AGENCY_ADVISORY_STYLE;
	$cells=orr($cells,array());
	$standard = $auth ? 2 : 1;
	$cell_count=(count($cells)+$standard)*2;
	$cell_count_hack=$cell_count+1;
	foreach($AG_MENU_LINKS as $link)
	{
		$links .=oline(span($link,'class="menuLink"'));
	}
	//$links = help('MENU',$links,'MENU'); // Uncomment this hack to make your menu collapsible.
	$bugzilla_link = ($tmp_link = link_bugzilla()) ? ' | '.$tmp_link : '';
	$extra=is_test_db() ? 'Test' : '';
	$spacer_plain=cell('','class="topNavSpacer"');
	$spacer=cell('','class="topNavSpacer' . $extra . '"');
	$out = tablestart("",'width="98%" cellpadding="0" cellspacing="0" border="0"')
		. row(
			topcell($firstcell,"align=\"left\" colspan=\"$cell_count\"") . $spacer_plain . rightcell(table(row(
				cell(organization_logo_small(),'class="logo"')
				. cell(agency_logo_small(),'class="logo"') 
				. cell(link_agency_donate())),'width="80"'),'width="80"'))
		. rowstart() 
		. bottomcell($links,'id="topMenuCell"')
		. $spacer;
	if ($auth) {
		$out .= bottomcell($auth,'class="staff"').$spacer;
	}
	foreach($cells as $cell)
	{
		$out .= $cell . $spacer;
	}
	$out .= cell( quick_searches_all(),'valign="bottom" align="right" width="1*" colspan="3"');
	$out .= rowend();

	/*
	 * Read-Only Advisory Warning
	 */
	if (db_read_only_mode()) {
		$AG_AGENCY_ADVISORY = oline('Database is in <em>read-only</em> mode for maintenance. Adding or Changing Data will not work.') . $AG_AGENCY_ADVISORY;
	}

	/*
	 * Password Expiration Warning
	 */
	$pc=password_expires_on_f($GLOBALS['UID'],true);
	if ($pc) {
		$AG_AGENCY_ADVISORY = oline($pc) . $AG_AGENCY_ADVISORY;
	}

    if (!be_null($AG_AGENCY_ADVISORY)) {
         $cell_count = $cell_count+3;
         $style = orr($AG_AGENCY_ADVISORY_STYLE,'background-color: #faa; border: solid 1px #333; margin-top: 2px;');
         $out .= row(cell(center($AG_AGENCY_ADVISORY,' style="'.$style.'"'),' colspan="'.$cell_count.'"'.' style="padding-top: 2px;"'));
    }
   $out.=row(str_repeat(cellstart() . ' '.cellend().' ',$cell_count+2),'style="height: 1px;"');
   $out .= tableend();
   return div($out,'agencyTopHeader');
}

function strlen_cmp_reverse($a,$b) {
	$la = strlen($a);
	$lb = strlen($b);

	return ($la==$lb)
		? 0 : (($la > $lb) ? -1 : 1);
}

function strlen_cmp( $a,$b )
{
	$la = strlen($a);
	$lb = strlen($b);

	return ($la == $lb) 
		? 0 : (($la < $lb) ? 1 : -1);
}

function get_extension($path)
{
	$p = pathinfo($path);
	return $p['extension'];
}

function agency_error_handler($error_no, $error_str, $error_file, $error_line)
{
	/*
	 *
	 * An AGENCY error handler to capture (and possibly notify sys admins) in
	 * the event of php errors.
	 *
	 * To use this function, this line:
	 *
	 * set_error_handler('agency_error_handler');
	 *
	 * must be set in agency_config_local.php
	 *
	 */

	$html = ini_get('html_errors');
	$display = ini_get('display_errors') && (error_reporting() & $error_no);
	$log = function_exists('log_error'); //attempt to send email/write to log

	$mesg = $html
		? $error_str.' in <b>'.$error_file.'</b> on line <b>'.$error_line.'</b></div>'
		: $error_str.' in '.$error_file.' on line '.$error_line."\n";

	
	switch ($error_no) {
	case E_ERROR:
	case E_USER_ERROR:
	case E_CORE_ERROR:
	case E_COMPILE_ERROR:
		$t = ($html ? '<div class="warning"><b>Fatal error</b>: ' : 'Fatal error: ').$mesg;
		$log && log_error($t,$silent = true, $die = true);
		if ($display) { echo $t; }
		die();
		break;
	case E_WARNING:
	case E_USER_WARNING:
	case E_CORE_WARNING:
	case E_COMPILE_WARNING:
		$t = ($html ? '<div class="warning"><b>Warning</b>: ' : 'Warning: ').$mesg;
		$log && log_error($t,$silent = true, $die = false);
		if ($display) { echo $t; }
		break;
	case E_NOTICE:
	case E_USER_NOTICE:
		$t = ($html ? '<div class="warning"><b>Notice</b>: ' : 'Notice: ').$mesg;
		if ($display) { echo $t; }
		break;
	default:
		$t = ($html ? '<div class="warning">' : '').'Unknown error type: ['.$error_no.'] '.$mesg;
		$log && log_error($t,$silent = true, $die = false);
		if ($display) { echo $t; }
		break;
	}

}

function sys_log_append($sys_log,$message)
{

	if (!be_null($sys_log)) {

		$sys_log .= "\n";

	}

	return $sys_log . $message;

}

function human_readable_size($size) 
{ 
	/*
	 * Takes a size in bytes and 
	 * returns a size in B, KB, MB or GB
	 */
     $mod = 1024;
     $units = array('B','KB','MB','GB');
     
     $counter = 0;
     while ($size > $mod && $counter <3) {
	     $size = $size / $mod;
	     $counter++;
     }
     
     return round($size) . ' ' . $units[$counter]; 
}

function org_name($format='') {
	switch( strtolower($format)) {
		case 'short' :
			$x =  $GLOBALS['AG_TEXT']['ORGANIZATION_SHORT'];
			break;
		case 'long' :
		case '' :
		case NULL :
			$x = $GLOBALS['AG_TEXT']['ORGANIZATION'];
			break;
		case 'full' :
			$x = $GLOBALS['AG_TEXT']['ORGANIZATION']
				 . ' (' . $GLOBALS['AG_TEXT']['ORGANIZATION_SHORT'] .')';
			break;
	}
	return $x;
}

function toggle_label($label) {
	// Hacky function to embed a label for hiddenDetail...
	return hiddenvar('toggleLabel',$label);
}

function syntaxify( $string, $lang ) {
	require_once($off.'bundled/phppgadmin/syntax_highlight.php');
	return syntax_highlight($string,$lang);
}

function generic_f( $object, $label_exec, $filter, $limit=NULL,$order='',$sep='',$pre='',$post='') {
	$sep=orr($sep,oline());
	$def=get_def($object);
	$label_exec=orr($label_exec,'object_label($object,$rec["'.$def['id_field'].'"])');
	$res=get_generic($filter,$order,$limit,$def);
	if (count($res)==0) {
		return false;
	}
	while ($rec=array_shift($res)) {
		$label = eval('return ' .$label_exec.';');
		$result[]=$pre . link_engine(array('object'=>$object,'id'=>$rec[$def['id_field']]),$label,$dummy_control,'class="'.$object.'Link"');
	}
	return implode($result,$sep) . $post;
}	

function array_links( $ids, $eval_label,$sep=NULL ) {
	$sep=orr($sep,oline());
	foreach ($ids as $x) {
		$out[]=eval('return ' . $x . ';');
	}
	return implode($sep,$out);
}

function object_links( $ids, $object,$sep=NULL,$action='view',$object_label_format=NULL ) {
	$sep=orr($sep,oline());
	foreach ($ids as $x) {
		$out[]=link_engine(array('object'=>$object,'id'=>$x,'action'=>$action),object_label($object,$x,NULL,$object_label_format));
	}
	return implode($sep,$out);
}


?>
