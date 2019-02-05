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
$engine['report'] = array(
					'require_password'=>false,
					'perm'=>'reports',
					'id_field'=>'report_code',
					'add_link_show'=>true,
					'list_fields' => array( 'report_code','report_title','report_category_code','block_count','report_comment'),
//					'title_format_add' =>'bigger(bold($x)) . " " . smaller(link_wiki_public("Reports","Help with writing reports"))',
//					'title_format_edit' =>'bigger(bold($x)) . " " . smaller(link_wiki_public("Reports","Help with writing reports"))',
					'subtitle_html'=>smaller(link_wiki_public('Reports','Report Help')),
					'child_records'=>array('report_block'),
					'custom'=>array(
						'my_recent_reports_max'=>10,
					),
					'fields' => array(
						'quick_sql' => array ('display_add'=>true, 'data_type'=>'text','comment'=>'SQL entered here will be posted as a Report Block'),
						'report_code' => array(
								'display_edit'=>'display',
								'comment_show_add'=>true,
								'comment_show_edit'=>true,
								'comment_show_view'=>false,
								'comment'=>'A unique label that identifies your report',
								'label' => 'Report Code',
								'force_case' => 'UPPER',
								'value_view' => '$x . smaller(link_report($rec["report_code"],"Run this report"))',
								'value_list' => '$x ." ". smaller(link_report($rec["report_code"],"Run"))',
								'is_html' => true),

						'suppress_output_codes' => array(
								'label' => 'Suppress Output for',
								'data_type' => 'lookup_multi',
								'lookup' => array('table'=>'l_output'),
								'lookup_format' => 'checkbox_v'),
						'output_template_codes' => array(
								'comment' => 'One option per line, format filename|label',
								'label' => 'Templates'),
						'block_count' => array( 'label' => 'Blocks'),
		'permission_type_codes'=>array(
			'data_type'=>'lookup_multi',
			'lookup_format'=>'checkbox_v',
			'label_format_add'=>'oline($x) . smaller(add_link("permission","Add a new Permission type","target=\"_blank\""))',
			'label_format_edit'=>'oline($x) . smaller(add_link("permission","Add a new Permission type","target=\"_blank\""))',
			'label'=>'Permission',
			'comment'=>'Any one of these permissions is sufficient',
						'variables' => array(
								'comment' => 'Specify: Type, name, prompt, default (Example: VALUE min_gift "Specify minimum gift amount" 50)'),
								'report_comment' => array( 
								'value_list' => '$x ? help("",webify($x),"Show") : ""',
								'value_view' => 'webify($x)',
								'is_html' => true
								// FIXME:  is_html_list should be an option
								// list_in_click_box would also be a nice option to have
								)
		),
		'last_generated_by'=>array(
			'value_format'=>'oline($x) .
				smaller(link_engine_list_filter("report_usage",array("report_code"=>$rec["report_code"]),
                 "Show all usage for this report",\'class="fancyLink" target="_blank"\'))',
			'is_html'=>true,
		),
	)
);
