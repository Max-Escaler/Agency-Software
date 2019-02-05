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

function post_file_exchange($rec,$def,&$mesg,$filter='',$control=array()) {
    if ( (!($rec=post_generic($rec,$def,$mesg,$filter,$control))) or (orr($control['action'],'add')!='add') ) {
        return $rec;
    }
    $a_def=get_def('alert');
    $al['ref_id']=$rec[$def['id_field']];
    $al['ref_table']='file_exchange';
    $al['added_by']=$al['changed_by']=$rec['added_by'];
    $al['alert_subject']='New file from ' . staff_name($rec['added_by']);
    $al['alert_text']='You have received a new file';
    $a_control=array('action'=>'add','object'=>'file_exchange');

    foreach ($rec['recipient_list'] as $x) {
        if ($x==$GLOBALS['sys_user']) {
            continue;
        }
        $al['staff_id']=$x;
        if (!post_generic($al,$a_def,$a_mesg,NULL,$a_control)) {
            $error=true;
        }
        if ($error) {
            $mesg = oline($mesg) . $a_mesg;
        }
    }
    return $rec;
}
