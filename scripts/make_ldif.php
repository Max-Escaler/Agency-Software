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
dn: cn=Jonathan Hedstrom,mail=jhedstrom@desc.org
objectclass: top
objectclass: person
objectclass: organizationalPerson
objectclass: inetOrgPerson
objectclass: mozillaAbPersonObsolete
givenName: Jonathan
sn: Hedstrom
cn: Jonathan Hedstrom
mail: jhedstrom@desc.org
modifytimestamp: 0Z
telephoneNumber: (206) 464-1570 x3014
facsimileTelephoneNumber: (206) 464-1111
pager: (206) 222-2222
mobile: (206) 333-3333
postalAddress: 515 3rd Ave
l: Seattle
st: WA
postalCode: 98104
title: Data Systems Administrator
ou: Information Services
o: Downtown Emergency Service Center
workurl: http://www.desc.org
*/

$off = dirname(__FILE__).'/../';
$quiet = true;
include $off.'command_line_includes.php';

$string=
	/*
	'dn: cn=$name_full,mail=$staff_email\n'
			. 'objectclass: top\n'
			. 'objectclass: person\n'
			. 'objectclass: organizationalPerson\n'
			. 'objectclass: inetOrgPerson\n'
			. 'objectclass: mozillaAbPersonObsolete\n'
			. 'givenName: $name_first\n'
			. 'sn: $name_last\n'
			. 'cn: $name_full\n'
			. 'mail: $staff_email\n'
			. 'modifytimestamp: 0Z\n\n';
	*/
	'dn: cn={$name_first} {$name_last}, dc=desc, dc=org\n'
	.'objectclass: top\n'
	.'objectclass: person\n'
	.'objectclass: organizationalPerson\n'
	.'objectclass: inetOrgPerson\n'
//	.'objectclass: mozillaAbPersonObsolete\n'
	.'givenName: {$name_first}\n'
	.'sn: {$name_last}\n'
	.'cn: {$name_first} {$name_last}\n'
	.'mail: {$staff_email}\n'
//	.'modifytimestamp: 0Z\n'
	.'{$ldap_phones}'
	.'postalAddress: 515 3rd Ave\n'
	.'l: Seattle\n'
	.'st: WA\n'
	.'postalCode: 98104\n'
	.'title: {$staff_position_code}\n'
	.'ou: {$agency_project_code}\n'
	.'o: Downtown Emergency Service Center\n'
	.'userpassword: {$ldap_password}\n'
//	.'workurl: http://www.desc.org\n\n'
	. '\n';
function ldap_phones($id)
{
/*
	.'telephoneNumber: (206) 464-1570 x3014\n'
	.'facsimileTelephoneNumber: (206) 464-1111\n'
	.'pager: (206) 222-2222\n'
	.'mobile: (206) 333-3333\n'
*/
	global $engine;
	$def = $engine['staff_phone'];

	$filter = array('staff_id'=>$id);
	$filter['FIELD<=:staff_phone_date']='CURRENT_DATE';
	$filter[] =array('FIELD>=:staff_phone_date_end'=>'CURRENT_DATE',
			     'NULL:staff_phone_date_end'=>true);
	$res = get_generic($filter,'','',$def);
	if (count($res)<1) {
		return '';
	}

	$mapping = array('WORK'=>'telephoneNumber',
			     'FAX'=>'facsimileTelephoneNumber',
			     'PAGER'=>'pager',
			     'MOBILE'=>'mobile');

	$phones=array();
	while ($a = array_shift($res)) {
		if (array_key_exists($a['phone_type_code'],$mapping)) {
			$phones[$mapping[$a['phone_type_code']]] = $a['number'] . 
				($a['extension'] ? ' x'.$a['extension'] : '');
		}
	}

	foreach ($phones as $type => $p) {
		$phone .= $type.': '.$p."\n";
	}
	return $phone;
	
}

function ldap_password($id)
{
	return '{md5}'.get_password($id,'MD5');
}

$staff=get_generic(array('is_active'=>sql_true()),'','','staff');

while ($s=array_shift($staff)) {
	$arr=array('name_first','name_last','staff_email','_staff_position_code','_agency_project_code');
	$arr1=array();
	$arr2=array();
	foreach($arr as $a)
	{
		array_push($arr1,'{$'.$a.'}');
		array_push($arr2, $s[$a]);
	}
	array_push($arr1,'\n');
	array_push($arr2,"\n");

	//phones
	array_push($arr1,'{$ldap_phones}');
	array_push($arr2,ldap_phones($s['staff_id']));

	//password
	array_push($arr1,'{$ldap_password}');
	array_push($arr2,ldap_password($s['staff_id']));

	$out .= str_replace($arr1,$arr2,$string);
}

out($out);

?>
