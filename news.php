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


function generate_list_news($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	$pos=$control['list']['position'];
      $mx=$control['list']['max'];
      while ( $x<$mx and $pos<$total) {
		$a = sql_fetch_assoc($result,$pos);
		//determine priority
		$priority = $a['news_priority_code'];
		$class = $priority == 'NORMAL' ? '' : ' news'.ucfirst(strtolower($priority));

		$link = link_engine(array('object'=>'news','id'=>$a['news_id']),'View');

		$entry = div(view_news($a,$def,'list',$control) . html_heading_6($link),'','class="news'.$class.'"'); 

		if ($priority == 'UTMOST') {
 			$utmost .= $entry; 
		} else {
			$out .= $entry;
		}

		$pos++;
		$x++;
	}

	return navigate_news($def) . $utmost . $out . list_links($max,$position,$total,$control,$control_array_variable);
}

function view_news($rec,$def,$action,$control='',$control_array_variable='control')
{

	$out  = html_heading_3(value_generic($rec['subject'],$def,'subject','view'));
	$out .= div(oline(value_generic($rec['posted_at'],$def,'posted_at','view'))
			. 'Posted by '.staff_link($rec['added_by']));
	$out .= para(value_generic($rec['news_text'],$def,'news_text','view'));

	$priority = $rec['news_priority_code'];
	$class = $priority == 'NORMAL' ? '' : ' news'.ucfirst(strtolower($priority));

	if ($action == 'view') {
		$out = navigate_news($def) . div($out,'','class="news'.$class.'"');
	}

	return $out;
}

function list_title_news($control,$def)
{
	return html_heading_2($def['singular']);
}

function navigate_news($def)
{
	//get 10 most recent
	$res=get_generic('','posted_at DESC',10,'news');
	while ($a = array_shift($res)) {
		$latest .= html_list_item(link_engine(array('object'=>'news','id'=>$a['news_id']),$a['subject']));
	}

	$all_config = array('object'=>'news','action'=>'list','list'=>array('filter'=>array()));

	$latest = html_heading_5(link_engine($all_config,'Latest '.$def['singular'])).html_list($latest);

	$search = html_heading_5('Search '.$def['singular']) 
		. div(formto($off.'search.php')
			. formvartext('QuickSearch',$_REQUEST['QuickSearch']) 
			. button('Go') 
			. hiddenvar('QSType','news')
			. formend()
			);

	$add = html_list(
			     html_list_item(link_engine($all_config,'All News'))
			     . html_list_item(add_link('feedback','Leave AGENCY Feedback'))
			     . html_list_item(link_engine(array('object'=>'news','action'=>'add'),'Add '.$def['singular']))
			     );
	return div($latest . $add . $search,'newsNavigate');
}

function news_search()
{
	$query = $_REQUEST['QuickSearch'];
	foreach (array('subject','news_text') as $field) {
		$filter['ILIKE:'.$field] = '%'.$query.'%';
	}
	$filter = array($filter);
	$control = array_merge(array( 'object'=> 'news',
						'action'=>'list',
						'list'=>array('filter'=>$filter),
						'format'=>'data'
						),
				     orr($_REQUEST['control'],array()));
	$result = call_engine($control,'',true,true,$TOTAL,$PERM);
	$sub = oline('Found '.$TOTAL.' results for '.bold($query));
	return $sub . $result;

}
?>
