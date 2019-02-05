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


$engine['news'] = array('enable_staff_alerts_view'=>true,
				'singular'=>'AGENCY News',
				'plural'=>'AGENCY News',
				'perm_add'=>'super_user',
				'perm_edit'=>'super_user',
				'list_order'=>array('posted_at'=>true),
				'list_max'=>15,
				'list_fields'=>array('posted_at','subject','added_by'),
				'title'=>'"AGENCY News: ".$rec["subject"]',
				'add_link_show'=>true,



		 'custom_css'=>'
		 div.news { border: solid 1px #c0cbc0; 
                        padding: 3px 10px 10px 10px; margin: 5px;
                        width: 65%;
             }

             div.news h3 { margin: 0px 0px 8px 0px; }

             div.news, #newsNavigate { font-family: "Lucida Sans Unicode", Verdana, Helvetica,Arial, sans-serif; }

             #newsNavigate a, div.news a { text-decoration: none; }
             #newsNavigate a:hover, div.news a:hover { text-decoration: underline; }

             div.news h6 { text-align: right; margin: 0px; }

             div.news div { font-size: 77%; color: #555; }

             div.newsRed { background-color: #eaa38b; }

             div.newsUtmost { background-color: #c0cbc0; border: dashed 3px red; margin-bottom: 15px; }

             #newsNavigate { float: right; width: 30%; }
             #newsNavigate ul, #newsNavigate div { font-size: 77%; }
		 '
				);

?>
