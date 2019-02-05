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

// require "dates.php";
// daterange object -- 2 dates, start & end, inclusive

// functions:

// display($sep="-->") :  display start & end dates
// days() : returns number of days in period (inclusive)
// arr( $format="" ) : returns an array of dates
// itemize($sep=" ",$format="") : returns a list of all dates
// between($date) : returns boolean, $date is within daterange
// overlaps( $daterange or $date1,$date2) : returns boolean, ranges overlap
// intersect( $daterange or $date1,$date2) : returns daterange of dates in both periods

class date_range {

	var $start;
	var $end;

function date_range( $d1, $d2="" ) 
{ 
	$this->start=dateof($d1,"SQL"); 
	$this->end=dateof($d2,"SQL");
}

function display( $sep="-->" ) 
{ 
	return dateof($this->start) . $sep . dateof($this->end); 
}

function arr( $format="" ) {
	$ar=array();
	for (	$x=$this->start; 
		$x<=orr($this->end,today("SQL"));
		$x=next_day($x)
	) {
		array_push( $ar, dateof($x,$format));
	}
	return $ar;
}

function set_start( $date )
{
// set_start & set_end are the proper way to change the
// start and end of a daterange, so they stay in SQL format!
	$this->start=dateof($date,"SQL");
}

function set_end( $date )
{
// set_start & set_end are the proper way to change the
// start and end of a daterange, so they stay in SQL format!
	$this->start=dateof($date,"SQL");
}

function days()
{	return days_interval( $this->start,orr($this->end,today()))+1;
}

function itemize( $sep=" ", $format="" )
{
	return implode( $this->arr($format), $sep );
}

function between( $date )
	{ return( ($date >= $this->start) && ( ($date<=$this->stop) || $end=="") ); }

function overlaps( $daterange, $end_date="" )
{
// takes a daterange, or two dates
	if (! is_object($daterange))
	{
		$daterange = new date_range($daterange, $end_date);
	}
	return ( 
			( ($daterange->start <= $this->end) 
			||
			  (! $this->end) )
			&&
			( ($daterange->end >= $this->start)
			||
			  ( ! $daterange->end ) )
		);
}

function intersect( $dr, $end_date="" )
{
// takes a daterange, or two dates
	if (! is_object($dr))
	{
		$dr = new date_range($dr, $end_date);
	}
	$res = new date_range( max($this->start,$dr->start),
		min($this->end,$dr->end));
	return $res;
}
				
		
}
?>
