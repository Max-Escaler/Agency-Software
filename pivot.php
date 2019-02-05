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

//Support for pivot/crosstab/graph dynamic data
// uses PivotTable.js  https://pivottable.js.org/

link_javascript('pivot.min.js');
link_style_sheet('pivot.min.css','screen');
link_javascript('c3.min.js');
link_javascript('c3_renderers.min.js');
link_style_sheet('c3.min.css','screen');
link_javascript('d3.min.js');
link_javascript('d3_renderers.min.js');
link_javascript('subtotal.min.js');
link_style_sheet('subtotal.min.css','screen');
link_javascript('pivot_agency.js');

function pivot_encode( $data ) {
// Take an array or query result and output for PivotTable.js
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_id=$mo_def['id_field'];
	$mo_noun=AG_MAIN_OBJECT;
	$mo_func='client_name';
	while ( ($a=(is_array($data) ? array_shift($data) : sql_fetch_assoc($data))) ) {
		if (array_key_exists('staff',$a)) {
			$a['staff']=staff_name($a['staff']);
		}
		if (array_key_exists($mo_noun,$a)) {
			$a[$mo_noun]=strip_tags($mo_func($a[$mo_noun]));
		}
		if (array_key_exists($mo_id,$a)) {
			$a[$mo_id]=strip_tags($mo_func($a[$mo_id]));
		}
		$recs[]=$a;
	}
	$keys=array_keys($recs[0]);
	$table=table_blank(NULL,'Pivot table test','class="pivotTable"');
	$data=array(
		'data'=>$recs,
		'options'=>array(
			'vals'=>$keys,
		),
	);
	$data_div=div(json_encode($data),'','class="serverData pivotTableData"');
	$out= ''
//		. oline(	bigger(bold("Table: ")))
		. $table
//		.oline()
//		. oline(bigger(bold("Data hidden here:")))
		. $data_div
	;
	return $out;
}

?>
