<?php
/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2009 by Ken Tanzer and Downtown Emergency
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
$size=20;
$engine['report_block'] = array(
	'require_password'=>false,
	'perm'=>'reports',
	'perm_add'=>'admin',
	'perm_edit'=>'admin',
	'add_link_show'=>true,
	'list_order'=>array('report_code'=>false,'sort_order_id'=>false),
	'list_fields' => array( 'is_enabled','report_code','report_block_title','report_block_comment','report_block_sql'),
	'add_another'=>true,
	'add_another_and_remember'=>true,
	'subtitle_html'=>smaller(link_wiki_public('Reports','Report Help')),
	'fields' => array(
		'report_code' => array(
			//'value_format' => 'link_report($x_raw,$x_raw,NULL,"view")',
			'value_format' => 'link_report($x_raw,$x_raw)',
			'display_edit'=>'display', // Blocks changing report ID.  If there was need to do so, need to be SU and edit tbl_report_block
			'is_html' => true,
			'add_another_remember'=>true),
		'is_enabled' => array(
			//'value_format' => '$x ? httpimage($GLOBALS["AG_IMAGES"]["RECORD_ENABLED"],$size,$size,0) : httpimage($GLOBALS["AG_IMAGES"]["RECORD_DISABLED"],$size,$size,0)'),
			'value_format_list' => 'sql_true($x) ? ' . "httpimage('{$GLOBALS['AG_IMAGES']['RECORD_ENABLED']}',$size,$size,0) : httpimage('{$GLOBALS['AG_IMAGES']['RECORD_DISABLED']}',$size,$size,0)",
			'value_format_view' => 'sql_true($x) ? green($x) : red($x)'),
		'report_block_sql' => array(
			'value_format'=>'webify_sql($x)',
			'is_html'=>true,
			'comment' => 'Multiple statements can be used, separated by "SQL" on its own line.  Each query will be executed and displayed within the report block.'),
		'permission_type_codes'=>array(
			'data_type'=>'lookup_multi',
			'lookup_format'=>'checkbox_v',
			'label'=>'Permission',
			'comment'=>'Any one of these permissions is sufficient to run this block'),
		'suppress_output_codes'=>array(
			'data_type'=>'lookup_multi',
			'lookup_format'=>'checkbox_v',
			'lookup' => array('table' => 'l_output',
				// FIXME, should be detected automatically with table declaration
				'value_field'=>'output_code',
				'label_field'=>'description')
		),
		'sql_library_id'=>array('display'=>'hide') // Hook for future shared sql
	),
);
