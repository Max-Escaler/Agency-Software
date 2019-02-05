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

// Other Date Functions could go here.

$months_with_31_days = array( "01","03","05","07","08","10","12");
$months_with_30_days = array( "04","06","09","11");
$months_without_31_days = array( "02","04","06","09","11");

function today( $format="" )
{
// function to return today's date
// functions that need a period-to-date ability
// should use this function, istead of "now",
// because today() could be modified to allow the date
// to be set to another date, so that you could say
// "run a YTD report as if it were 6/5/02", even if it's
// November now.

	return dateof( "now",$format );
}

function year_of( $date )
{
	return substr( dateof($date,"SQL"),0,4 );
}

function month_of( $date, $format='num' )
{
	switch ($format) {
	case 'num' :
		return substr( dateof($date,"SQL"),5,2 );
	case 'text':
		$date = strtotime(dateof($date));
		return date('F',$date);
	}
}

function day_of( $date )
{
	return substr( dateof($date,"SQL"),8,2 );
}

function prev_day($date="",$days=1)
{
// subtract 1 day from date
// Returns YYYY-MM-DD format

	global $months_with_30_days, $months_with_31_days, $months_without_31_days;

	// make sure we know what format we've got, and have a value
	$date = dateof(orr($date,dateof("now")),"SQL");
	if ($days == 0)
	{
		return $date;
	}
	if ($days > 1)
	{
		$date = prev_day($date,$days-1);
	}
	$day = day_of( $date );
	$month = month_of( $date );
	$year = year_of( $date );
	$day--;
	if ($day==0)
	{
		$month--;
		if ($month==0)
		{
			$month="12";
			$year--;
		}
		$day = num_days( $month, $year );
	}
	return $year . "-" . dig2($month) . "-" . dig2($day);
}

function next_day( $date, $days=1 )
{
// Add 1 day to date
// Returns YYYY-MM-DD format

	global $months_with_30_days, $months_with_31_days, $months_without_31_days;

	// make sure we know what format we've got
	$date = dateof( orr($date,'now'), "SQL" );
	if ($days == 0)
	{
		return $date;
	}
	if ($days > 1) {
		$date = next_day($date,$days-1);
	}

	$day = day_of( $date );
	$month = month_of( $date );
	$year = year_of( $date );

	$day++;

	if ( 
		($day=="32")
		|| ($day=="31" && (in_array($month,$months_without_31_days)) )
		|| ($day=="30" && $month=="02")
		|| ($day=="29" && ($month=="02") && (intval($year/4)<>($year/4))) 
	   )
	{
		$day = "01";
		$month++;
		if ($month=="13")
		{
			$month="01";
			$year++;
		}
	}
	return $year . "-" . dig2($month) . "-" . dig2($day);
}

function is_leap( $year )
{
	return ($year/4)==intval($year/4);
}

function num_days( $month, $year="" )
{
// return # of days in a month (must be 2-digit)
// specify year if leap year needs to be considered

	global $months_with_31_days, $months_with_30_days;
	if (in_array($month,$months_with_31_days))
	{
		return 31;
	}
	if (in_array($month,$months_with_30_days))
	{
		return 30;
	}
	if ($month=="02")
	{
		$year=orr($year,"1999"); // default to a non-leap year
		return is_leap($year) ? "29" : "28";
	}
	outline("Warning.  Bad month passed to num_days");
	return false;
}

function next_month( $date, $months=1 )
{
// Add months to date
// Returns YYYY-MM-DD format

	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );
	if ($months>1)
	{
		$date=next_month($date,$months-1);
	}
	$day = day_of( $date );
	$month = month_of( $date );
	$year = year_of( $date );

	$month++;
	if ($month == "13" )
	{
		$month = "01";
		$year++;
	}
	$day=min($day,num_days($month,$year));
	return $year . "-" . dig2($month) . "-" . dig2($day);
}

function last_month( $date, $months=1 )
{
// Subtract months from date
// Returns YYYY-MM-DD format

	global $months_with_30_days, $months_with_31_days, $months_without_31_days;
	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );
	if ($months>1)
	{
		$date=last_month($date,$months-1);
	}

	$day = day_of( $date );
	$month = month_of( $date );
	$year = year_of( $date );

	$month--;
	if ($month == 0 )
	{
		$month = "12";
		$year--;
	}
	if ($day == 31 )
	{
		if (in_array( $month, $months_without_31_days ))
		{
			$day=30;
		}
	}
	if ( ($day == 30 || $day == 29) && $month=="02")
	{
		$day = 29;
		if (! ($year/4==intval($year/4)) )
		{
			$day = 28;
		}
	}
	return $year . "-" . dig2($month) . "-" . dig2($day);
}

function days_in_month( $date )
{
// take a date, and return the number of days in that month
// this won't work right for the every-hundred year(?) skipping of leap year.

	global $months_with_30_days, $months_with_31_days, $months_without_31_days;
	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );

	$month = month_of( $date );
	$year = year_of( $date );

	if (in_array( $month, $months_with_31_days ))
	{
		$days = "31";
	}
	elseif (in_array( $month, $months_with_30_days ))
	{
		$days = "30";
	}
	elseif ($month == "02" )
	{
			$days = ($year/4 == intval( $year/4 ) ) ? "29" : "28";
	}
	else
	{
			outline("Warning: bad date passed to days_of_month");
	}
	return $days;
}


function start_of_month( $date )
{
// take a date, and return 1st day of that month

	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );
	return year_of($date) . "-" . month_of( $date )  . "-01";

}

function start_of_year( $date )
{
// take a date, and return 1st day of that year

	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );
	return year_of($date) . '-01-01';

}

function end_of_year( $date )
{
// take a date, and return last day of that year

	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );
	return year_of($date) . '-12-31';

}

function end_of_month( $date )
{
// take a date, and return last day of that month
// this won't work right for the every-hundred year(?) skipping of leap year.

	global $months_with_30_days, $months_with_31_days, $months_without_31_days;

	// make sure we know what format we've got
	$date = dateof( $date, "SQL" );

	$month = month_of( $date );
	$year = year_of( $date );

	if (in_array($month, $months_with_31_days))
	{
		$day = "31";
	}
	elseif (in_array($month, $months_with_30_days))
	{
		$day = "30";
	}
	elseif ($month=="02")
	{
		$day = ($year/4 == intval( $year/4 ) ) ? "29" : "28";
	}
	else
	{
			outline("Warning: bad date passed to end_of_month");
	}
	return $year . "-" . dig2($month) . "-" . dig2($day);
}

function same_month( $date1, $date2 )
{
// test two dates to see if they are in the same month

	// make sure we know what formats we've got
	$date1 = dateof( $date1, "SQL" );
	$date2 = dateof( $date2, "SQL" );

	$month1 = month_of( $date1 );
	$month2 = month_of( $date2 );
	$year1 = year_of( $date1 );
	$year2 = year_of( $date2 );

	return ( ($month1==$month2) && ($year1==$year2) );
}

function days_interval( $date1, $date2 , $signed=false)
{
// return the number of days between two dates.
// Internally, this function converts to timestamps,
// so dates will only work within valid timestamp range.

	$date1 = strtotime(dateof( $date1, "SQL" ));
	$date2 = strtotime(dateof( $date2, "SQL" ));

	$range = $signed ? ($date2 - $date1) : abs($date2-$date1);
	return intval( $range / (60*60*24) + .5 );
}

function start_of_week( $date )
{
	//sunday is the start of the week
	$date = dateof($date,'SQL');
	if (!$date) {
		log_error('Bad date passed to start_of_week'.var_dump($date));
		return false;
	}
	$cycle = 0;

	//FIXME strtotime only works w/ dates within unix timestamp range
	while ($cycle < 10 ) {
		$unix_date = strtotime($date);
		$d = date('w',$unix_date);
		if ($d == '0') {
			return $date;
		}
		$date = prev_day($date);
		$cycle ++;
	}
	log_error('Call to start_of_week() cycled 10 times w/o finding start of week!'.var_dump($date));
	return false;
}

function day_of_week( $date )
{
	//returns nos 0 - 6
	$date = dateof($date,'SQL');
	if (!$date) {
		log_error('Bad date passed to start_of_week'.var_dump($date));
		return false;
	}
	return date('w',strtotime($date));
}

/*
 * date_iso_to_sql() and date_iso_to_sql2() couldn't be incorporated
 * into dateof() because YYYYMMDD is seen as numeric, thus dateof()
 * handles it as a UNIX timestamp.
 */

function date_iso_to_sql2($date)
{
	/*
	 * Expects YYMMDD
	 */

	$year = substr($date,0,2);
	$century = ($year < AG_DATE_CENTURY_CUTOFF) ? 20 : 19;
	return date_iso_to_sql($century . $date);
}

function date_iso_to_sql($date)
{
	/*
	 * Expects format YYYYMMDD
	 */

	if (preg_match('/^([0-9]{4})-?([0-9]{2})-?([0-9]{2})$/',$date,$m)) {
		return $m[1].'-'.$m[2].'-'.$m[3];
	}

	return false;
}

/* These functions were moved from times.php */


function hoursof($time)
{
	//time of format 00:00:00
	if (preg_match('/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/',$time,$m)) {
		$hours = $m[1];
		$mins = $m[2];
		$seconds = $m[3];
		return $hours + $mins/60 + $seconds/3600;
	}
	return false;
}

function minusHour($time='',$hours=1,$timestamp=true)
{
      $time=explode(':',orr($time,date('H:i:s',time())));
      $new_time=$time[0]-$hours;
      $time[0]=str_pad(
		       (  ($new_time<0)
			  ? 24+$new_time
			  : $new_time),2,'0',STR_PAD_LEFT);
      if ($timestamp)
      {
	    $date=dateof('now','SQL');
	    if ($new_time < 0 )
	    {
		  $date=prev_day($date);
	    }
	    return $date.' '.implode(':',$time);
      }
      return implode(':',$time);
}

function time_calc($str)
{
	if (preg_match('/([0-9]{2}:[0-9]{2}:[0-9]{2})\s*([+-]{1})\s*([0-9]{2}:[0-9]{2}:[0-9]{2})/',$str,$m)) {
		$res = sql_query("SELECT '{$m[1]}'::interval {$m[2]} '{$m[3]}'::interval AS ti_cal");
		if ($a = sql_fetch_assoc($res)) {
			return $a['ti_cal'];
		}
	}
	return false;
}

function time_iso_to_sql($time)
{
	if (preg_match('/^([0-9]{2})([0-9]{2})([0-9]{2})?$/',$time,$m)) {
		return $m[1].':'.$m[2].':'.orr($m[3],'00');
	}
	return false;
}

?>
