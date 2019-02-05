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

$engine['config_file'] = array(
	'title'=>'"Viewing config file for " .$rec["object"]',
	'title_format' =>'bigger(bold($x)) . " " . smaller(link_wiki_public("Config_file","Help with Config Files"))',
	'perm'=>'admin',
	'allow_add'=>false,
	'allow_delete'=>false,
	'allow_edit'=>false,
	'id_field'=>'object',
	'fields'=>array(
		'config_file_text'=>array(
			'value_format'=>'highlight_string($x,true)',
			'is_html'=>'true'
		)
	)
);
?>
