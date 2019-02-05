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
 *
 * Utilities for creating or parsing EDI files
 *
 */

class Make_EDI {

	var $segment_count    = 0;
	var $hl_count         = 0;
	var $hl_par_count     = 0;

	var $transaction_type       = '';
	var $version_release_number = '';
	var $transaction            = ''; //837, 270, 271 etc

	var $interchange_control_number; // used in ISA/IEA
	var $group_control_number;       // used in GS/GE

	/* private static variables */
	var $seg_sep  = '*'; // the  field separator
	var $seg_end  = '~'; // the end of string character
	var $comp_sep = ':'; // the composite data element separator
	var $comp_end = '';  // no end character now.

	function Make_EDI($config,$interchange_control_number,$group_control_number,$batch_number) 
	{ 
		$this->config = $config;
		$this->interchange_control_number = $interchange_control_number;
		$this->group_control_number = $group_control_number;
		$this->batch_number = $batch_number;
	}

	function create_segment()
	{
		$this->segment_count ++;

		$args = func_get_args();

		$segment = implode( $this->seg_sep, $args);
		$segment .= $this->seg_end;

		return $segment;
	}
	
	function create_composite()
	{
		$args = func_get_args();

		$composite=implode( $this->comp_sep, $args);
		$composite .= $this->comp_end;

		return $composite;
	}

	function escape_string($str)
	{

		return str_replace(array($this->comp_sep,$this->comp_end,$this->seg_sep,$this->seg_end),'',$str);

	}

	function process_header()
	{
		return $this->set_isa() 
			. $this->set_gs()
			. $this->set_st();
	}

	function begin_hierarchical_transaction()
	{
		$o = $this->set_bht()
			. $this->set_source()
			. $this->set_receiver();

		return $o;		
	}

	function process_trailer()
	{
		return $this->set_se()
			. $this->set_ge()
			. $this->set_iea();
	}



	/* Interchange Control */
	function set_isa()
	{
		/*
		 * INTERCHANGE CONTOL HEADER
		 */

		$isa = $this->create_segment('ISA',
						     // 01 - Authorization Information Qualifier
						     '00',
						     // 02 - Authorization Information
						     str_pad($this->config['isa_auth_info'],10),
						     // 03 - Security Information Qualifier
						     '00',
						     // 04 - Security Information
						     str_pad($this->config['isa_sec_info'],10),
						     // 05 - Interchange ID Qualifier
						     'ZZ',
						     // 06 - 15 length of element  Interchange Sender ID
						     str_pad($this->config['sender_id'],15),
						     // 07 -Interchange ID Qualifier
						     'ZZ',
						     // 08 - 15 length of element Interchange Receiver ID
						     str_pad($this->config['int_receiver_id'],15),
						     // 09 - YYMMDD  Interchange Date
						     dateof('now','NO_SEP_SHORT'),
						     // 10 - HHMM Interchange Time
						     timeof('now','NO_SEP_SHORT'),
						     // 11 - Interchange Control Standards Identifier
						     'U',
						     // 12 - Interchange Control Version Number
						     '00401', 
						     // 13 - Interchange Control Number
						     lpad($this->interchange_control_number,9,'0'),
						     // 14 - Acknowledgement Requested
						     '0',
						     // 15 - T for TEST, P for PRODUCTION  Usage Indicator
						     $this->config['usage_indicator'],
						     // 16 - Component Element Separator
						     ':');
		return $isa;
	}


	function set_iea()
	{
		/*
		 * Interchange Control Trailer
		 */

		$iea = $this->create_segment('IEA',
						     // 01 - Number of included functional groups
						     '1',
						     // 02 - Interchange Control Number
						     lpad($this->interchange_control_number,9,'0')
						     );
		return $iea;
	}
	/* End Interchange Control */

	/* Functional Group */
	function set_gs()
	{
		/*
		 * Functional Group Header
		 */

		$gs = $this->create_segment(
						    'GS',
						    $this->transaction_type,      // 01 - Functional Identifier Code
						    $this->config['sender_id'],   // 02 - Application Sender's Code   (DESC, for DSHS)
						    $this->config['receiver_id'], // 03 - Application Receiver's Code (DSHS)
						    dateof('now','NO_SEP'),       // 04 - YYYYMMDD    // Date
						    timeof('now','NO_SEP_SHORT'), // 05 - HHMM        // Time
						    $this->group_control_number,  // 06 - Group Control Number  
						    'X',                          // 07 - Responsible Agency Code
						    $this->version_release_number // 08 - Version Release Number 
						    );
		return $gs;
	}


	function set_ge()
	{
		/*
		 * Functional Group Trailer
		 */

		$ge = $this->create_segment('GE',
						    '1',                          // Number of transaction sets included
						    $this->group_control_number); // Group Control Number

		return $ge;
	}
	/* End Functional Group */

	/* Transaction Set */
	function set_st()
	{
		$st = $this->create_segment('ST',
						    $this->transaction,
						    $this->batch_number);
		return $st;
	}

	function set_se()
	{
		$se = $this->create_segment('SE',
						    $this->segment_count - 2 + 1,    /*  we need to send segment count from ST through SE, we have a count
												  including ISA and GS and not including SE, so virtually we subtract two
												  and add 1 or more simply... */
						    $this->batch_number);
		return $se;
	}
	/* End Transaction Set */

	/* Hierarchical Transaction */
	function set_bht()
	{
		/*
		 * Hierarchical Transaction Header
		 */

		$bht = $this->create_segment('BHT',
						     // 01 - Hierarchical Structure Code
						     $this->config['bht_struct_code'],
						     // 02 - Transaction Set Purpose Code
						     $this->config['bht_purp_code'],
						     // 03 - Reference Identification (? I think anything works ?)
						     $this->config['sender_id'].str_pad($this->batch_number,10,'0',STR_PAD_LEFT),
						     // 04 - YYYYMMDD  Date
						     dateof('now','NO_SEP'),
						     // 05 - HHMM Time
						     timeof('now','NO_SEP_SHORT'));
		return $bht;
	}

	function set_hierarchical_level($par,$level,$child)
	{
		$this->hl_count++;
		return $this->create_segment('HL',
						     $this->hl_count,     // 01 - Hierarchical Id Number
						     $par,                // 02 - Parent ID
						     $level,                // 03 - Hierarchical Level Code
						     $child);                // 04 - Hierarchical Child Code
	}

	function set_nm1($ent,$orgn_name,$id_qual,$id,$person='2',$first='',$middle='',$suffix='')
	{
		return $this->create_segment('NM1',
						     // 01 - Entity Identifier Code
						     $ent, //gateway provider?
						     // 02 - Entity Type Qualifier
						     $person, // non-person = 2, person = 1
						     // 03 - Name Last (or organization)
						     trim($orgn_name),
						     trim($first), // 04 - Name First
						     trim($middle), // 05 - Name Middle
						     '', // 06 - Name Prefix - not used
						     trim($suffix), // 07 - Name Suffix
						     $id_qual, // 08 - ID Code Qualifier
						     $id    // 09 - ID Code
						     );
	}
	/* Note: this is the end of the generalized workings - from here on out it is tailored to medicaid lookup/service data submission (fixme?)*/
	function set_source() { }
	function set_receiver() { }
	function set_submitter() { }

} /* End class Make_EDI */

class Make_EDI_270 extends Make_EDI {

	var $transaction            = '270';
	var $transaction_type       = 'HS';
	var $version_release_number = '004010X092A1';

	function set_source()
	{
		/*
		 * Loop 2000A - Information Source Level
		 */
		$this->hl_par_count = '1';
		$hl_seg = $this->set_hierarchical_level('','20','1');

		/*
		 * Loop 2100A
		 */
		$nm1_seg = $this->set_nm1('2B','DSHS','PI',$this->config['receiver_id']);

		return $hl_seg . $nm1_seg;
	}

	/*
	 *  Information Source Loop 2000A
	 *     Information Receiver Loop 2000B
	 *        Subscriber Loop 2000C
	 *           Dependent Loop 2000D
	 *              Eligibility or Benefit Information
	 *           Dependent Loop 2000D
	 *              Eligibility or Benefit Information
	 *        Subscriber Loop 2000C
	 *           Eligibility or Benefit Information
	 *    ...
	 */

	function set_receiver()
	{
		/*
		 * Loop 2000B
		 */

		$hl = $this->set_hierarchical_level($this->hl_par_count,'21','1');
		$par = $this->hl_par_count = $this->hl_count;
		/*
		 * Loop 2100B
		 */
		$nm1 = $this->set_nm1('1P','Downtown Emergency Service Center','SV',$this->config['sender_id']);

		return $hl . $nm1;
	}

	function set_subscriber($person,$start,$end)
	{
		/* 
		 * Loop 2000C - Subscriber loop
		 */
		$hl = $this->set_hierarchical_level($this->hl_par_count,'22','0');
		$trn = $this->create_segment('TRN', // Trace
						     '1', //??
						     $person['trace_id'], '9'.str_pad($person['trace_id'],9,'0',STR_PAD_LEFT)
						     );

		$nm1 = $this->set_nm1('IL',$person['name_last'],'MI',orr($person['pic'],'NA'),'1',
					    $person['name_first'],$person['name_middle'],$person['name_suffix']);
		$ref = $this->create_segment('REF',
						     'SY',
						     str_replace('-','',$person['ssn']));

		$dmg = $this->create_segment('DMG',
						     'D8',
						     dateof($person['dob'],'NO_SEP'));

		$dtp = $this->create_segment('DTP',
						     '307',
						     'RD8',
						     dateof($start,'NO_SEP').'-'.dateof($end,'NO_SEP'));
		$eq = $this->create_segment('EQ',
						    '30');

		return $hl . $trn . $nm1 . $ref . $dmg . $dtp . $eq;
						    
		

	}
}

class Make_EDI_837 extends Make_EDI {

	var $transaction            = '837';
	var $transaction_type       = 'HC';
	var $version_release_number = '004010X098A1';
	var $service_count          = 0;
	/*
header[1]=set_isa()
header[2]=set_gs("HC") ;"HC" ;specifies 837 transactions
header[3]=set_837() ; start the 837 transaction
header[4]=set_submitter() ; loop 1000A
header[5]=set_receiver() ; loop 1000B
header[6]=set_provider() ; loop 2000A
header[7]=set_provider_name() ; loop 2010AA	
	*/

	function begin_hierarchical_transaction()
	{
		$o = $this->set_bht()
			. $this->create_segment('REF','87','004010X098A1')
			. $this->set_submitter()
			. $this->set_receiver()
			. $this->set_provider();

		return $o;		
	}

	/* Hierarchical Transaction */
	function set_bht()
	{
		/*
		 * Hierarchical Transaction Header
		 */

		$bht = $this->create_segment('BHT',
						     // 01 - Hierarchical Structure Code
						     $this->config['bht_struct_code'],
						     // 02 - Transaction Set Purpose Code
						     $this->config['bht_purp_code'],
						     // 03 - Reference Identification (? I think anything works ?)
						     $this->config['sender_id'].str_pad($this->batch_number,10,'0',STR_PAD_LEFT),
						     // 04 - YYYYMMDD  Date
						     dateof('now','NO_SEP'),
						     // 05 - HHMM Time
						     timeof('now','NO_SEP_SHORT'),
						     // 06 - Transaction Type Code
						     'RP'
						     );
		return $bht;
	}

	function set_submitter()
	{

		/*
		 * Loop 1000A 
		 */

		$nm1_seg = $this->set_nm1('41',$this->config['sender_name'],'46',$this->config['sender_id']);
		$per_seg = $this->create_segment('PER',
							   'IC',
							   'Data Systems Administrator',
							   'TE',
							   '2064641570');

		return $nm1_seg . $per_seg;

	}

	function set_receiver()
	{
		/*
		 * Loop 1000B
		 */

		return $this->set_nm1('40',$this->config['receiver_name'],'46',$this->config['receiver_id']);
	}

	function set_provider()
	{
		/*
		 * Loop 2000A 
		 */

		$this->hl_par_count = '1';
		$hl_seg = $this->set_hierarchical_level('','20','1');

		$prv_seg = $this->create_segment('PRV',
							   'PT',
							   'ZZ',
							   $this->config['sender_taxonomy_code']); //desc taxonomy code

		/*
		 * Loop 2010A
		 */
		$nm1_seg = $this->set_nm1('85',
						  $this->config['sender_name'],
						  'XX',
						  $this->config['sender_npi']);

		$n3_seg  = $this->create_segment('N3',
							   $this->escape_string($this->config['sender_address']));

		$n4_seg  = $this->create_segment('N4',
							   $this->config['sender_city'],
							   $this->config['sender_state'],
							   $this->config['sender_zipcode']);

		$ref_seg = $this->create_segment('REF',
							   'EI',
							   $this->config['sender_ein']);

		return $hl_seg . $prv_seg . $nm1_seg . $n3_seg . $n4_seg . $ref_seg;

	}



	function set_subscriber($dal)
	{
		/*
		 * Loop 200B
		 */

		$hl_seg = $this->set_hierarchical_level($this->hl_par_count,'22','0');

		$sbr_seg = $this->create_segment('SBR',
							   'P',
							   '18',
							   '',
							   '',
							   '',
							   '',
							   '',
							   '',
							   $dal['medicaid_status']
							   );

		/*
		 * Loop 2010A
		 */

		$nm1_seg = $this->set_nm1('IL',$dal['name_last'],'MI',$dal['king_cty_id'],'1',$dal['name_first'],$dal['name_middle'],$dal['name_suffix']);

		$n3_seg = $this->create_segment('N3',
							  $this->escape_string($dal['street_address']));

		$n4_seg = $this->create_segment('N4',$dal['city'],$dal['state'],$dal['zipcode']);

		$dmg_seg = $this->create_segment('DMG',
							   'D8',
 							   dateof($dal['dob'],'NO_SEP'),
							   $dal['gender']);


		return $hl_seg . $sbr_seg . $nm1_seg . $n3_seg . $n4_seg . $dmg_seg;

	}

	function set_payer($rec)
	{
		/*
		 * Loop 2010B
		 */

		$nm1_seg = $this->set_nm1('PR',$this->config['receiver_name'],'PI',$this->config['receiver_id']);

		return $nm1_seg;
	}

	function set_claim($claim_number,$rec)
	{
		/*
		 * Loop 2300
		 */

		$clm_seg = $this->create_segment('CLM',
							   $claim_number,
							   '0',
							   '',
							   '',
							   // CLM06 - location info (action code 1=new, 8=delete
							   $this->create_composite($rec['location_cpt_code'],'',$rec['action_code']),
							   'Y',
							   'C', //provider accept assigment code
							   'Y', //benefits assignment certification
							   'N'); // whatever this is, if it's N, CLM11 is empty

		$ref_seg = $this->create_segment('REF',
							   'G1',
							   $rec['kc_authorization_id']);

		$hi_seg = $this->create_segment('HI',
							  $this->create_composite('BK',$rec['diagnosis']));

		return $clm_seg . $ref_seg . $hi_seg;

	}

	function set_service_location($rec)
	{
		/*
		 * Loop 2310D
		 */

		$nm1_seg = $this->set_nm1('FA',
						  $rec['service_location_name'],
						  'XX', //NPI
						  $this->config['sender_npi']);

		$n3_seg = $this->create_segment('N3',
							  $this->escape_string($rec['service_location_address']));

		$n4_seg = $this->create_segment('N4',
							  $rec['service_location_city'],
							  $rec['service_location_state'],
							  $rec['service_location_zipcode']);

		return $nm1_seg . $n3_seg . $n4_seg;
	}

	function set_service($rec)
	{
		/*
		 * Loop 2400
		 */

		$this->service_count ++;
		$lx_seg = $this->create_segment('LX',
							  $this->service_count);


		$sv1_seg = $this->create_segment('SV1',
							   (!be_null($rec['dal_modifiers'])
							    ? $this->create_composite('HC',$rec['service_cpt_code'],$rec['dal_modifiers'])
							    : $this->create_composite('HC',$rec['service_cpt_code'])),
							   '0',
							   'MJ',
							   $rec['total_minutes'],
							   $rec['location_cpt_code'],
							   '',
							   '1');

		$dtp_seg = $this->create_segment('DTP',
							   '472',
							   'D8',
							   dateof($rec['dal_date'],'NO_SEP'));

		$ref_seg = $this->create_segment('REF',
							   '6R',
							   $rec['dal_id']);

		return $lx_seg . $sv1_seg . $dtp_seg . $ref_seg;
	}

	function set_rendering_provider($rec)
	{
		/*
		 * Loop 2420A
		 */

		$nm1_seg = $this->set_nm1('82',
						  $this->config['sender_name'],
						  'XX', //XX - NPI, 24 - EIN
						  $this->config['sender_npi']);

		$ref_seg = $this->create_segment('REF',
							   'N5',
							   $rec['kc_staff_id']);

		$ref_seg2 = $this->create_segment('REF',
							    'EI',
							    $this->config['sender_ein']);

		return $nm1_seg . $ref_seg . $ref_seg2;
	}
}

?>
