<?php

$quiet=true;
require_once 'includes.php';
if (!has_perm('JILS_IMPORT','W')) {
	outline(bigger(bold("You do not have permissions to import JILS records")));
	page_close();
	exit;
}

$jils_text=$_REQUEST['jils_text'];
$client_id=$_REQUEST['client_id'];
//$jils_link=hlink('http://ingress.kingcounty.gov/inmatelookup/SearchJailData.aspx','JILS',NULL,'target="_blank"');
$jils_link=hlink('http://ingress.kingcounty.gov/inmatelookup/startpage.aspx','JILS',NULL,'target="_blank"');

if (!is_client($client_id)) {
	outline('Invalid or missing client ID.  Stopping');
	page_close();
	exit;
}

$jils_check_rec=array(
	'client_id'=>$client_id,
	'jils_check_by'=>$GLOBALS['UID'],
	'jils_check_at'=>datetimeof('now','SQL'),
	'added_by'=>$GLOBALS['UID'],
	'changed_by'=>$GLOBALS['UID']
);

if ($jils_text) {

	$jils_parse_regex = '/^.*Other Jail Search Resources(.*)Custody.Facility.*Booking Events:(.*)Data Accuracy Disclaimer.*$/s';
//	$jils_parse_regex = '/^.*Other Jail Search Resources(.*)Custody\/Facility.*Booking Events:(.*)Data Accuracy Disclaimer.*$/s';
//	outline("Client: " . client_link($client_id));
	if (preg_match($jils_parse_regex,$jils_text,$matches)) {
//outline("Matches: " . dump_array($matches));
		$name=trim($matches[1]);
		$booking=$matches[2];
		$bookings=explode("\n",$booking);
		$jail_template=array(
			'client_id'=>$client_id,
			'jail_date_accuracy'=>'E',
			'jail_date_source_code'=>'JILS',
			'jail_facility_code'=>'KCCF', // FIXME: Correct code?
			'jail_county_code'=>'KING',
			'added_by'=>$GLOBALS['UID'],
			'changed_by'=>$GLOBALS['UID'],
			'sys_log'=>'Added with jils_import, name='.$name
		);
		foreach($bookings as $b) {
//outline("Got booking: " . dump_array($b));
			if (preg_match('/^.*BA: ([0-9]*) Book Date: (.*) Release Date: ?(.*)$/',$b,$m)) {
//outline("Matched $b" . dump_array($m));
				$j=$jail_template;
				$j['ba_number']=$m[1];
				$j['jail_date']=datetimeof($m[2],'SQL');
				if (($j['jail_date_end']=datetimeof($m[3],'SQL'))) {
					$j['jail_date_end_accuracy']='E';
					$j['jail_date_end_source_code']='JILS';
				}
				$jails[]=$j;
				if(upsert_jail_record($j,$msg_tmp)) {
					$success++;
				} else {
					$fail++;
				}
				$msg[]=$msg_tmp;
				$msg_tmp='';
			}
		}
		if ($fail) {
			$post_check_skipped=true;
		} elseif (!post_generic($jils_check_rec,get_def('jils_check'),$check_msg)) {
			$post_check_failed=true;
		}

//		outline('Name: ' . webify($name));
//		outline('Jail records: ' . dump_array($jails));
//		outline('Booking: ' . dump_array($bookings));
//outline(red("matched: " . dump_array($matches)));
	} else {
		outline("Raw Form: " . webify($jils_text));
		outline("Matches: " . dump_array($matches));
preg_match('/.*/s',$jils_text,$matches);
//outline(red("Full match: " . dump_array($matches)));

	}
}
	$title=$client_title='Importing Jail Records for ' . client_link($client_id);
	$form =
	formto()
	. formtextarea('jils_text',$jils_text)
	. hiddenvar('client_id',$client_id)
	. oline()
	. bold('3. Press ')
	. button()
	;
	agency_top_header();

	if ($success or $fail) {
		outline(bigger(bold("Results for " . client_link($client_id))),2);
		out( $success ? oline(bigger(bold("$success records processed successfully"))) : '');
		out( $fail ? oline(bigger(bold("$fail records failed to import"))) : '');
		out( $msg ? bigger(div(implode(oline(),$msg),'','class="hiddenDetail"')) : '');
		if ($post_check_skipped) {
			outline('JILS Check record NOT POSTED due to errors');
		} else {
			out( $post_check_failed ? oline(bigger(bold("Posting JILS checked record failed with message: " . $check_msg))) : '');
		}
		outline();
		outline(bold("You can close this page now"));
	} else {
$jils_check_only_text=oline('If there is no information in JILS for this client, ' . add_link('jils_check',NULL,NULL,$jils_check_rec));
$help_text=
	oline("Choose Search Jail Booking Service")
	.oline("Fill in the search form and hit submit")
	.oline("Click on the client name link in the search results")
	.oline("Select and copy the whole page.  (ctrl-a,ctrl-c)")
	.oline("Paste the page into the box below.  (ctrl-v)");
$help_text=div($help_text.toggle_label("How to use JILS"),'','class="hiddenDetail"');
		outline(span(bigger(bold($client_title)),'class="engineTitle"'),2);
		outline(bold('1. Lookup the client in  '. $jils_link . '.' ));
		outline($help_text,2);

		outline(bold('2. Copy the page, and paste it into this box:'));
		out($form);
		outline();
		outline();
		out($jils_check_only_text);
	}
page_close();
exit;

?>
