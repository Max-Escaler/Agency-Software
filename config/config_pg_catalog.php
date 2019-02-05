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

$engine['pg_catalog']=array(
				    'table'=>'db_agency_relations',
				    'list_fields'=>array('name','type','description'),
				    'list_order'=>array('name'=>false),
				    'virtual_field'=>false,
				    'allow_edit'=>false,
				    'allow_add'=>false,
				    'allow_delete'=>false,
				    'fields'=>array(
							  'name'=>array(
									    'is_html'=>true,
									    'value'=>'link_engine(array("object"=>$x,"action"=>"list","format"=>"raw"),$x)'
									    ),
							  'view_definition'=>array('value'=>'webify_sql($x)',
											   'is_html'=>true),
							  'description' => array('is_html' => true,
											 'value' => 'sql_manage_comment($rec["name"],$rec["type"],$x)')
							  )
				    /* for future interpretation of access_privileges
				     *               =xxxx -- privileges granted to PUBLIC
				     *          uname=xxxx -- privileges granted to a user
				     *    group gname=xxxx -- privileges granted to a group
				     * 
				     *                   r -- SELECT ("read")
				     *                   w -- UPDATE ("write")
				     *                   a -- INSERT ("append")
				     *                   d -- DELETE
				     *                   R -- RULE
				     *                   x -- REFERENCES
				     *                   t -- TRIGGER
				     *                   X -- EXECUTE
				     *                   U -- USAGE
				     *                   C -- CREATE
				     *                   T -- TEMPORARY
				     *             arwdRxt -- ALL PRIVILEGES (for tables)
				     *                   * -- grant option for preceding privilege
				     * 
				     *               /yyyy -- user who granted this privilege
				     * 
				     */
);
?>