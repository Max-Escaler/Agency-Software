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

$engine['employment_status']=array(
					     'plural'=>'employment status',
					     'list_fields'=>array('employment_date','employment_date_end','employment_status_code',
									  'employment_level_code','employer_name'),
					     'fields'=>array(
								   'application_status_code'=>array('valid'=>array('be_null($x) xor !be_null($rec["application_date"])'=>'{$Y} is required (ONLY) with application date'),
														'data_type'=>'lookup',
														'lookup'=>array('table'=>'l_employment_application_status',
																    'value_field'=>'employment_application_status_code',
																    'label_field'=>'description')
														),
								   'application_date'=>array('valid'=>array('!be_null($x) or !be_null($rec["employment_date"])'=>'Either {$Y} or Employment Date (or both) must be filled in.')),
								   'employment_date'=>array('valid'=>array('!be_null($x) or !be_null($rec["application_date"])'=>'Either {$Y} or Application Date (or both) must be filled in.',
															 'dateof($x,"SQL") >= dateof($rec["application_date"],"SQL")'=>'{$Y} must be greater than or equal to Application Date')),
								   'employment_date_end'=>array('valid'=>array('be_null($x) || (dateof($x,"SQL") >= dateof($rec["employment_date"],"SQL"))'=>'{$Y} must be greater than or equal to Employment Date')),
								   'employment_status_code'=>array(
												    'label_list'=>'Status'),
								   'employment_description'=>array('valid'=>array('in_array($rec["employment_status_code"],array("UNKNOWN","UNEMPLOYED"))
													     || !be_null($x) || !be_null($rec["comment"]) ||!be_null($rec["employer_name"])'
																  =>'Field Job title: must specify an employer,job title or a comment for this employment status'),
													     'label'=>'Job title or type of work'),
								   'comment'=>array('valid'=>array('in_array($rec["employment_status_code"],array("UNKNOWN","UNEMPLOYED"))
													     || !be_null($x) || !be_null($rec["employer_name"]) || !be_null($rec["employment_description"])'
													     =>'Field Comment:must specify an employer,job title or a comment for this employment status')
											  ),
								   'employer_name'=>array('valid'=>array('in_array($rec["employment_status_code"],array("UNKNOWN","UNEMPLOYED"))
													     || !be_null($x) || !be_null($rec["comment"]) || !be_null($rec["employment_description"])'
														     =>'Field Employer Name:must specify an employer,job title or a comment for this employment status')
												  ),
								   'employment_termination_code'=>array('valid'=>array('be_null($x) xor !be_null($rec["employment_date_end"])'=>'{$Y} is (ONLY) required with employment end date'))
								   )
					     
					     );
?>