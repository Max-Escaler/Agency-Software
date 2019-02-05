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

$engine['address'] =array(
	'allow_delete'=>true, 
	'list_fields'=> array('address_names','city','state_code','zipcode','address_type_code'),
	'valid_record'=>array(
				    '(be_null($rec["address_date_end"]) and be_null($rec["address_obsolete_reason_code"]))
				    or (!be_null($rec["address_date_end"]) and !be_null($rec["address_obsolete_reason_code"]))'=>
				    'Address Obsolete Date and Reason required for obsolete addresses'
				    ),
	'fields'=>array(
			    'address_date'=>array(
							  'label'=>'Effective Date',
							  'default'=>'NOW'),
			    'address_date_end'=>array(
							  'label'=>'Obsolete Date')
			    )
	 );
?>
