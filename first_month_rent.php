<?php

  //fixme: this can be removed. the functionality has been moved to the
  // function housing_first_month_rent_calculator()

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

include "includes.php";

headtitle("First Month Rent Calculator");
outline("");

if (isset($_POST["movein"]) && isset($_POST["rent"]))
{
	$movein=dateof($_POST["movein"],"SQL");
	$rent=$_POST["rent"];
	if ($movein)
	{
		$first_month = new date_range($movein,end_of_month($movein));
		$days = $first_month->days();
		$days_month = days_in_month( $movein );
		$first_rent = round($rent / $days_month * $days);
		out( bigger(bold(oline("At a rent of \$$rent per month,")
			. oline("For a period of $days days, " . $first_month->display())
			. oline("The first month rent is \$$first_rent",2))));
	}
}


out(formto($_SERVER['PHP_SELF'])
	. oline("Enter Move-in Date-->" . formdate("movein",dateof("now")))
	. oline("Enter Montly Rent-->" . formvartext("rent"))
	. button("Calculate Now") . formend());
page_close();
?>
