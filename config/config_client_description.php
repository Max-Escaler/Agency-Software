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


$engine['client_description'] = array(
						  'list_fields' => array('client_description_date','height_ft','weight_lbs','hair','eyes','hygiene','clothing'),
						  'fields' => array(
									  'height_ft' => array('valid' => array('be_null($x) || preg_match("/^[0-9]{1}\'(\s?[0-9]{1}[0-2]{0,1}\")$/",$x)' => '{$Y} should be of the form F\'II" where F is feet and II is inches'))));

?>