#!/usr/bin/php -q
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
   * The basic procedure is to parse a bunch of files in the
   * download directory, then archive them by year and month
   *
   * This currently only parses .res files, the rest are simply archived
   */

$MODE = 'TEXT';

$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$UID = $GLOBALS['sys_user'];

define('AG_KING_COUNTY_IMPORT_DIRECTORY','/shared/agency_import/king_county');

$parse_filename_patterns = array(//type=>pattern
				     'res'                             => '/\.res$/',
				     'kcid'                            => '/^kcid[0-9]{4}\.152$/',
				     'jail'                            => '/^jl[0-9]{6}\.152$/',
				     'hospital'                        => '/^ip[0-9]{6}\.152$/',
				     'payment'                         => '/^payd[0-9]{4}\.152$/',
				     'medicaid_verification'           => '/^mvtx[0-9]{4}\.152$/',
				     'medicaid_authorization_jeopardy' => '/^mvct[0-9]{4}\.152$/'
				  );

/*
 * That should be the end of configuration
 */

$holding_directory = AG_KING_COUNTY_IMPORT_DIRECTORY.'/download';

//set year
$archive_directory = AG_KING_COUNTY_IMPORT_DIRECTORY.'/archive/'.year_of('now');
if (!is_dir($archive_directory)) {

	mkdir($archive_directory);

}
//set month
$archive_directory .= '/'.lpad(month_of('now'),2,'0');
if (!is_dir($archive_directory)) {

	mkdir($archive_directory);

}

//make certain that directory is writeable
if (!is_writable(AG_KING_COUNTY_IMPORT_DIRECTORY)) {

	log_error('KC Import Error: Directory '.AG_KING_COUNTY_IMPORT_DIRECTORY.' either doesn\'t exist or cannot be written to.');
	page_close($silent = true);
	exit;

} elseif (!is_writable($holding_directory)) {

	log_error('KC Import Error: Directory '.$holding_directory.' either doesn\'t exist or cannot be written to.');
	page_close($silent = true);
	exit;

} elseif (!is_writable($archive_directory)) {

	log_error('KC Import Error: Directory '.$archive_directory.' either doesn\'t exist or cannot be written to.');
	page_close($silent = true);
	exit;

}

$error = '';

$files = array();
$dir_listing = scandir($holding_directory);
foreach ($dir_listing as $file) {

	if (in_array($file,array('.','..'))) {

		continue;

	}

	$files[] = $file;

}

foreach ($files as $basename) {

	$fullname = $holding_directory.'/'.$basename;

	//figure out if file needs to be parsed or simply archived
	foreach ($parse_filename_patterns as $type => $pattern) {

		if (preg_match($pattern,$basename)) {

			//parse and manage data
			$working_file = file($fullname);

			$func = 'import_king_county_'.$type;

			if (!function_exists($func)) {

				die(__FILE__.' couldn\'t find '.$func.'() function for parsing King County files of type '.$type);

			}

			$res = call_user_func($func,$working_file,$basename,&$error);
			if (!$res) {
			
				// file won't be moved to archive due to error
				$error .= oline('Not archiving file '.$basename.'. Please review.');
				continue 2;

			}

			continue;

		}

	}

	// archive file
 	if (!rename($fullname,$archive_directory.'/'.$basename)) {

 		//failed to archive
 		$error .= oline('Failed to archive '.$fullname);

 	}
	
}

out($error);

page_close($silent=true);

?>
