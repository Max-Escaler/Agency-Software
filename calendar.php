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
 * replacement functions for engine are at bottom
 * followed by ICS functions for calendar export
 */
class Calendar {

	/* public vars */
	var $def; //engine config array for calendar_appointment;
	var $id;
	var $view_type='weekly'; //only weekly is functional
	var $commands = array(); //for agency top header
	var $config; //config record from tbl_calendar

	/* private vars */
	var $is_inanimate=false; //default to staff - this is assuming that staff and inanimate calendars will never appear together
	                         //if this changes, style sheets and such will need overhaul.
	var $count; //number of calendars being merged into display

// 	var $refresh_rate = null; //browser won't refresh - critical for security or user would remain logged in indefinitely
// 	var $refresh_rate = 300; //browser refresh every 5 minutes via meta tag

	var $enable_cancelled_view = true; //set to true to turn on this feature
	var $show_cancelled_appointments = false;

	var $appointment_view = 'calendar_appointment_current';
	var $display_hours = array('day','evening','night','24 hour');
	var $current_hour_selection = 'day';
	var $display_days = array('5','7');
	var $current_day_selection = '5';
	var $display_end_date;
	var $display_start_date;
	var $display_end_time;
	var $display_start_time;
	var $display_spanning_type;

	var $cE; //an array of Calendar_Record objects

	function Calendar($id,$start,$type='weekly')
	{
		$this->def = get_def('calendar');

		$this->view_type = $type;

		//------initiallize/change display options------//
		if ($this->enable_cancelled_view) {
			$this->show_cancelled_appointments = $_SESSION['calendar_show_cancelled_appointments'] =
				orrn($_REQUEST['showCancelled'],$_SESSION['calendar_show_cancelled_appointments'],$this->show_cancelled_appointments);
			$this->appointment_view = $this->show_cancelled_appointments ? 'calendar_appointment_cancelled' : $this->appointment_view;
		}
		$this->current_day_selection = $_SESSION['calendar_current_day_selection'] = 
			orr($_REQUEST['day'],$_SESSION['calendar_current_day_selection'],$this->current_day_selection);
		$this->current_hour_selection = $_SESSION['calendar_current_hour_selection'] = 
			orr($_REQUEST['hour'],$_SESSION['calendar_current_hour_selection'],$this->current_hour_selection);

		$this->set_display_start($start);

		if (is_numeric($id)) {
			$this->cE = array($this->initialize_singular($id));
		} else {
			$this->cE = $this->initialize_group($id);
		}
	}

	function initialize_singular($id)
	{
		$cE = new Calendar_Record($id,$this->display_start_date,$this->display_end_date,$this->show_cancelled_appointments);
		$this->is_inanimate = $cE->is_inanimate();
		$this->count = 1;
		return $cE;
	}

	function initialize_group($id)
	{
		$cals = $this->ids_from_type($id);
		foreach ($cals as $cid) {
			$tmp_cE = new Calendar_Record($cid,$this->display_start_date,$this->display_end_date,$this->show_cancelled_appointments);
			$group[] = $tmp_cE;
		}

		$this->count = count($group);
		$this->is_inanimate = $tmp_cE->is_inanimate(); //only checking/setting once - assuming all the same.
		if ($this->count > 1) { //here we switch to auto-generated style
			$this->auto_generate_style();
		}
		return $group;
	}

	function ids_from_type($type)
	{
		$filter = array('calendar_type_code'=>$type);
		$res = get_generic($filter,' calendar_id','','calendar_current');
		return array_fetch_column($res,'calendar_id');
	}

	function get_spanning_event($cr,$current_day)
	{
		$id = $cr->id; //calendar record id

		$disp_start_time = $this->construct_time($this->display_start_time,0);
		$disp_end_time = $this->construct_time($this->display_end_time,0);
		$disp_end_time = $disp_end_time=='24:00:00' ? '23:59:00' : $disp_end_time;
		$start_timestamp = $current_day.' '.$disp_start_time;
		$end_timestamp = $current_day.' '.$disp_end_time;

		//first check for event ending at end of span
		if ( ($rec = $cr->end_event_records[$end_timestamp])
		     && ($rec['event_start'] < $start_timestamp) ) {
			$this->display_spanning_type = 'ending_on';
			return $rec;
		}

		//must query for events that completely span block
		$filter = array('calendar_id'=>$id,
				    '<:event_start' => $start_timestamp,
				    '>:event_end' => $end_timestamp);
		$res = get_generic($filter,'','',$this->appointment_view);
		if ($a = array_shift($res)) {
			$this->display_spanning_type = 'complete';
			return $a;
		} elseif (($a=$cr->end_blocker_records[$current_day.' 23:59:00']) && ($a['event_start'] < $start_timestamp)) {
			$this->display_spanning_type = 'complete';
			return $a;
		}
		$this->display_spanning_type = 'none';
		return false;
	}

	function display()
	{
		link_style_sheet('calendar_s.php?inan='.($this->is_inanimate ? 'Y' : ''),'screen',' class="styleSheetScreen"');
		link_style_sheet('calendar.css','screen, print'); //special print style sheet
		if ($this->refresh_rate) {
			html_meta('refresh',$this->refresh_rate); 
		}

		//PTO handling
		$pto = $this->pto_schedule_handler();

		//send super navigation to upper title bar
		$this->super_navigation();

		$sched_block = call_user_method($this->view_type.'_template',$this); //a series of rows: must be wrapped in table below
		//now some formatting and other options
		$title = $this->title();
		//a bit of navigation
		$nav_bar = $this->nav_bar();
		return $pto 
			. head($title,' class="calendar"')
			. table(row(cell($nav_bar,' colspan="'.($this->current_day_selection+1).'"')) 
				  . $sched_block,'',' cellspacing="0" cellpadding="0" class="calendar"');
	}

	function weekly_template()
	{
		/*
		 * a basic weekly template 
		 * initially consist of a work week, 8:00am - 5:00pm
		 * in half-hour sections per day
		 *
		 * I started to work with the hoaky php time stuff (strtotime,date,etc) but gave up
		 * in favor of working with integers and letting postgres do date calculations
		 */

		//------configure weekly template here-------//
		$start_time = $this->display_start_time;
		$end_time = $this->display_end_time;
		$time_span = 0.25; //half hour blocks
		$days = $this->current_day_selection;
		//-------------------------------------------//

		/*
		 * Note: the dimensions below in the cell_width, cell_height, and cell_spacing variables are the same size as those in 
		 * the corresponding calendar and calendarH css class. These dimensions are used to calculate sizes for cells spanning
		 * more than a single time-span slot.
		 * If they are changed here, they must be changed in calendar.css */
		$pxem = 'em';
		$cell_width = 10; //em
		$cell_height = 2; //em

		$blocks_per_day = ($end_time - $start_time)/ $time_span;
		$disp_start_time = $this->construct_time($start_time,0);
		$disp_end_time = $this->construct_time($end_time,0);
		

		$header = row(cell($current_week_link,' style=" width: '.($cell_width/2).$pxem.'; height: '.(2*($cell_height)).$pxem.';"'))
			. ( ($this->count > 1) ? row(cell('&nbsp;')):'')
			. $this->make_time_header($blocks_per_day/2,$start_time);
		$current_day = $this->display_start_date;


		for ($i = 0;$i < $days; $i++) { //loop through each day

 			//header cell contains date and day-of-week
  			$tmp_day = row(cell($this->format_day_header($current_day),' class="calendarH" colspan="'.count($this->cE).'"'),' class="calendarH"');
			
			$tmp_tab = $tmp_name_link = '';

			$multi_count = -1;
			foreach ($this->cE as $events) {

				$tmp_event = '';
				if ($this->count > 1) {
					$multi_count++;
					$name = $events->name($events->config);
					$style = 'background-color: '
						.$GLOBALS['colors']['staff']
						.';color: #000; height: .75em; width: 10em; padding: 2px 0px 2px 0px;'
						.' text-align: center;';
					$tmp_name_link .= cell($this->link_calendar($events->id,$this->display_start_date,
												  smaller($name,2),
												  'Jump to '.$name.'\'s calendar',
												  ' class="calendar'.$multi_count.'" style="'.$style.'"'));
				} else { 
					unset($multi_count);
				}
				//check for events ending at days end (23:59) or spanning entire day's block
				if ( $rec = $this->get_spanning_event($events,$current_day) ) {
					$blocks = $blocks_per_day;
					$tmp_height = $blocks * ($cell_height)/ 2;
					//overwrite first j blocks - probably a better way to do this...
					$tmp_event = row(cell($this->format_event($rec,$blocks),
								    ' class="calendar'.($rec['blocker'] ? 'B' : 'E').'" style="height: '.$tmp_height.$pxem.';"'),' class="calendar"');
				} else {
					for ($j=0;$j<$blocks_per_day;$j++) { //loop through each time-slot
						
						//calculate current time and next block time
						$time = $this->construct_time($start_time,$j,$time_span);
						$next_block_time = $this->construct_time($start_time,$j+1,$time_span);
						$date_time = $current_day.' '.$time;
						$next_block_date_time = $current_day.' '.$next_block_time;

						
						if ( ($j > 0) //don't care about events ending on start block
						     && (($rec = $events->end_event_records[$date_time]) || ($rec = $events->end_blocker_records[$date_time]) )
						     && $rec['event_start'] < $current_day.' '.$disp_start_time) {
							/*
							 * CHECK A
							 *
							 * event ending on this time slot, but starting prior to display start
							 * this is a separte IF statement because events can start and end on any given block
							 *
							 */
							$is_blocker = $rec['blocker'];

							$blocks = $j;
							$tmp_height = ($blocks * ($cell_height))/2;
							
							//overwrite first j blocks - probably a better way to do this...
							$this->display_spanning_type = 'ending_on';
							$tmp_event = row(cell($this->format_event($rec,$blocks),
										    ' class="calendar'.($is_blocker ? 'B' : 'E').'" style="height: '.$tmp_height.$pxem.';"'),' class="calendar"');
							$asymetric_flag = $this->set_asymetric_flag($blocks,$j,$ending=true);
						}
						
						if ( ($rec = $events->event_records[$date_time]) || ($rec = $events->blocker_records[$date_time]) ) {
							/*
							 * CHECK B
							 *
							 * event starting on this time
							 *
							 */ 
							$is_blocker = $rec['blocker'];
							
							$blocks = ceil($this->calculate_blocks($rec,$time_span,$blocks_per_day,$j));

							$tmp_height = $blocks *$cell_height/2;
							
							$asymetric_flag = $this->set_asymetric_flag($blocks,$j);
							if ($rec['event_end'] > $current_day.' '.$disp_end_time) {
								$this->display_spanning_type='ending_after';
							}
							$tmp_event .= row(topcell($this->format_event($rec,$blocks),
											  ' class="calendar'.($is_blocker ? 'B' : 'E').'" style="height: '.$tmp_height.$pxem.';"'),' class="calendar"')
								. $post_filler;
							$j += $blocks-1;
						} elseif ( (fmod($j,2)==0)
							     && (($rec = $events->event_records[$next_block_date_time])
								   ||$rec = $events->blocker_records[$next_block_date_time]) ) {
							/*
							 * CHECK C
							 *
							 * event starting on 'half slot', e.g. 00:15, 00:45
							 * with no previous event triggers a half-sized add link box
							 *
							 * 
							 */
							$add_link = $events->add_link($current_day,$time,true,$multi_count);
							$class = 'calendarHalf';
							$tmp_event .= row(topcell($add_link,' class="'.$class.'"'),' class="calendar"');
						} elseif ( (fmod($j,2)==0) || $asymetric_flag) {
							/*
							 * CHECK D
							 *
							 * even numbered cycle, print empty add slot
							 *
							 */
							
							$add_link = $events->add_link($current_day,$time,$asymetric_flag,$multi_count);
							$class = $asymetric_flag ? 'calendarHalf' : 'calendar';
							$tmp_event .= row(topcell($add_link,' class="'.$class.'"'),' class="calendar"');
							$asymetric_flag = false;
						} else {
							//do nothing
						}
					}
				}
				$tmp_tab .= topcell(table($tmp_event,'',' class="calendar" cellspacing="0" cellpadding="0"'));
			} //end looping through calendar_records (cE)
			$current_day = next_day($current_day);

			$d .= topcell(table($tmp_day 
						  . ($tmp_name_link ? row($tmp_name_link) : '')
						  . row($tmp_tab),'',' class="calendar" cellspacing="0" cellpadding="0"'));
		} //end looping through days
		$d = row(topcell(table($header,'',' class="calendar" cellspacing="0" cellpadding="0"')).$d);

		return $d;
	}

	function monthly_template()
	{
		return row(cell('month'));
	}

	function set_display_start($date)
	{
		//this function will determine the date of the Monday (or the first of the month etc)
		//and also determine the start and end time of the display

		//dates
		$date = dateof($date,'SQL');
		switch ($this->view_type) {
		case 'monthly':
			$st_date = start_of_month($date);
			$end_date = end_of_month($date);
			break;
		case 'weekly':
			if ($this->current_day_selection=='5') { //start on Monday
				$st_date = next_day(start_of_week($date));
			} else { //start on sunday
				$st_date = start_of_week($date);
			}
			$end_date = next_day($st_date,$this->current_day_selection);
		}
		$this->display_start_date = $st_date;
		$this->display_end_date = $end_date;
		//times
		switch ($this->current_hour_selection) {
		case 'night': //12:00 am to 8:00am
			$this->display_start_time = 0;
			$this->display_end_time = 8;
			break;
		case 'day': //8:00am to 5:00pm
			$this->display_start_time = 8;
			$this->display_end_time = 17;
			break;
		case 'evening': //5:00pm to 12:00am
			$this->display_start_time = 17;
			$this->display_end_time = 24;
			break;
		case '24 hour': //12:00am to 11:59pm
			$this->display_start_time = 0;
			$this->display_end_time = 24;
			break;
		}
	}

	function format_day_header($date)
	{
		//determine if current date
		$formatted = date('l j',strtotime($date));
		if ($date==dateof('now','SQL')) {
			$formatted = bold($formatted);
		}

		//schedule PTO link for single calendars, owned by user, on current or future dates only
		if ($this->allow_pto_schedule() && (dateof($date,'SQL') >= dateof('now','SQL'))) {
			$formatted .= right(smaller($this->link_pto_schedule($date),2));
		}

		return $formatted;
	}

	function format_time_header($time)
	{
		return str_replace(' ','&nbsp;',date('g:i a',strtotime($time)));
	}

	function make_time_header($blocks,$start)
	{
		for ($i=0; $i < $blocks/2; $i++) {
			$time = $this->construct_time($start,$i*2);
			$t .= row(cell(span($this->format_time_header($time),' class="calendarT"'),' class="calendarT"'),' class="calendarT"');
		}
		return $t;
	}

	function title() {
		$cE =& $this->cE[0];
		$cnc = $this->show_cancelled_appointments
			? red(' CANCELLED APPOINTMENTS')
			: '';
		if ($this->count > 1) { //group calendar
			$title = sql_lookup_description($cE->config['calendar_type_code'],'l_calendar_type').' ('.$this->count.')';
			$GLOBALS['title'] = $title;
			return $title.$cnc;
		} else {
			$GLOBALS['title'] = $cE->text_title();
			return ($cE->html_title()).$cnc;
		}
	}

	function construct_time($start,$j,$time_size = '0.5')
	{

		$MINUTES = 60 * $time_size;
		$BLOCKS_PER_HOUR = 1.0 / $time_size;
		
		$OFFSET = $start - floor($start);
		if ($OFFSET > 0) {
			$start = floor($start);
			$j += $BLOCKS_PER_HOUR * $OFFSET;
		}

		$mins = str_pad( ($MINUTES * fmod($j,$BLOCKS_PER_HOUR)), 2, '0',STR_PAD_LEFT);
		$hour = $start + floor($j/$BLOCKS_PER_HOUR);
		$hour = str_pad($hour,2,'0', STR_PAD_LEFT);
		return $hour.':'.$mins.':00';
	}

	function calculate_blocks($rec,$block_size,$max_blocks,$current_block)
	{
		$time = hoursof($rec['event_length']);
		$blocks = $time ? ($time) / $block_size : false;
		if ($blocks && ($blocks + $current_block < $max_blocks)) {
			return $blocks;
		} else {
			return $max_blocks - $current_block;
		}
	}

	function set_asymetric_flag($blocks,$j,$ending=false)
	{
		if ( (!fmod($blocks,2)==0) && (fmod($j,2)==0)) {
			//starting on 'whole' (eg 00:00 or 00:30) AND odd-length time-slot (eg 45 minutes, 15 minutes etc)
			$asymetric_flag = true; //this will signal CHECK D to add a half-slot on the next loop around
		} elseif ( (!fmod($j,2)==0) && (fmod($blocks,2)==0) ) {
			//starting on odd, ending on odd
			$asymetric_flag = true;
			
		} elseif ($ending && (!fmod($j,2)==0)) { 
			$asymetric_flag = true;
		} else {$asymetric_flag = false; }
		return $asymetric_flag;
	}

	function format_event($rec,$blocks)
	{
		//at some point, fancy js on mouseover events to display a better
		//description/summary would be cool.
		$blocker_div = div('&nbsp;','','style="height: 10em;"');
		switch ($this->display_spanning_type) {
		case 'complete':
			$up = $this->link_calendar_by_date(dateof($rec['event_start'],'SQL'),
								     up_triangle(' title="'.'Start time: '.datetimeof($rec['event_start'],'US')
											.'" style="border-style: none;"'));
			$down = $this->link_calendar_by_date(dateof($rec['event_end'],'SQL'),
									 down_triangle(' title="'.'End time: '.datetimeof($rec['event_end'],'US')
											   .'" style="border-style: none;"'));
			$prepend = center($up).$blocker_div;
			$append = $blocker_div.center($down);
			break;
		case 'ending_on':
			if ($blocks > 4) {
				$up = $this->link_calendar_by_date(dateof($rec['event_start'],'SQL'),
									     up_triangle(' title="'.'Start time: '.datetimeof($rec['event_start'],'US')
											     .'" style="border-style: none;"'));
				$prepend = center($up).div('&nbsp;','','style="height: '.($blocks/2).'em;"');
			}  else {
				$prepend = alt('<<< ','Start time: '.datetimeof($rec['event_start'],'US'));
			}
			break;
		case 'ending_after':
			if ($blocks > 4) {
				$down = $this->link_calendar_by_date(dateof($rec['event_end'],'SQL'),
										 down_triangle(' title="'.'End time: '.datetimeof($rec['event_end'],'US')
												   .'" style="border-style: none;"'));
				$append = div('&nbsp;','','style="height: '.($blocks/2).'em;"').center($down);
			}  else {
				$append = alt(' >>>','End time: '.datetimeof($rec['event_end'],'US'));
			}
			break;
		case 'none':
		default:
			$append = $prepend = '';
		}
		$this->display_spanning_type = 'none'; //reset spanning options

		// ---- get overlapping events ---- //
		if (sql_true($rec['allow_overlap'])) { //overlap allowed, must check for events
			$filter = $o_rec_init = array('calendar_id'=>$rec['calendar_id'],
							     'event_start'=>$rec['event_start'],
							     'event_end'=>$rec['event_end'],
							     'allow_overlap'=>sql_true());
			$res = get_generic($filter,'','',$this->appointment_view);
			if (count($res) > 1) { //the initial record is included in the result set
				$overlap_recs = array();
				while ($a = array_shift($res)) {
					$overlap_recs[] = $a;
				}
				return $this->format_block_overlap($overlap_recs,$blocks);
			}
			$overlap_add_link = link_engine(array('object'=>'calendar_appointment','action'=>'add',
									  'rec_init'=>$o_rec_init),' (+)','',' title="add overlapping event on this timeslot"');
		}

		switch ($this->view_type) {
		case 'weekly':
			if ($rec['blocker']) {
				$label = $rec['label'];
				return span($label,' class="calendarB"');
			} else { //normal event
				//edit link
				$control = array('object'=>'calendar_appointment','id'=>$rec['calendar_appointment_id']);
				$link = link_engine($control,'view','',' class="calendarE"');
				
				if ($rec['client_id']) {
					$client_name_full = client_name($rec['client_id'],0,true); //grab text only
					$cm = sql_fetch_assoc(client_staff_assignments($rec['client_id'],'CM_MH'));
					$alt = $client_name_full . ' (mh cm: '.staff_name($cm['staff_id_name']).')';
					$client_link = client_link($rec['client_id'],ucwords(strtolower(client_name($rec['client_id'],20))),'',' title="'.$alt.'"');
					$out = oline($client_link);
				}
				if ($rec['description']) {
					$out .= alt(substr($rec['description'],0,$blocks*12),$rec['description']);
				}
				$out .= oline() . timeof($rec['event_start']) . ' -> ' . timeof($rec['event_end']);
				return div($link.$overlap_add_link,'',' class="calendarE"'). $prepend . span($out,' class="calendarE"') . $append;
			}
			break;
		case 'monthly':
			return div($rec['label']);
		}
	}

	function format_block_overlap($recs,$blocks)
	{
		$count = count($recs);
		foreach($recs as $rec) {
			$alt = $rec['client_id'] 
				? client_name($rec['client_id'],0,true)
				: webify($rec['description']);
			$view_link = link_engine(array('object'=>'calendar_appointment','id'=>$rec['calendar_appointment_id']),'view','',' title="'.$alt.'"');
			$tab .= cell($view_link,' class="calendarOverlapSubCell"');
		}
		$rec_init = array('calendar_id'=>$rec['calendar_id'],
					'event_start'=>$rec['event_start'],
					'event_end'=>$rec['event_end'],
					'allow_overlap'=>sql_true());
		$add_link =  link_engine(array('object'=>'calendar_appointment','action'=>'add',
							 'rec_init'=>$rec_init),'(+)','',' title="add overlapping event on this timeslot"');
		$overlap = ($blocks > 1) ? row(cell($add_link.' multiple events:',' class="calendarOverlapSubCell" colspan="'.$count.'"')) : '';
		$o_add = ($blocks > 1) ? '' : cell($add_link,' class="calendarOverlapSubCell"');
		return table($overlap.
				 row($o_add.$tab),'','class="" cellpadding="0" cellspacing="0"');
	}

	function nav_bar()
	{
		switch ($this->view_type) {
		case 'weekly':
			$nw = $this->link_calendar_by_date(next_day($this->display_start_date,7),
								     '>','Jump to next week',' class="navCalendar"');
			$pw = $this->link_calendar_by_date(prev_day($this->display_start_date,7),
								     '<','Jump to last week',' class="navCalendar"');
		case 'monthly':
			$nm = $this->link_calendar_by_date(next_month($this->display_start_date),
								     '>>','Jump to next month',' class="navCalendar"');
			$pm = $this->link_calendar_by_date(last_month($this->display_start_date),
								     '<<','Jump to last month',' class="navCalendar"');
		}

		$cur = div(oline($this->link_calendar_by_date(dateof('now','SQL'),'Jump to Today','','class="calendarConBox"')).$disp_types
			     ,'',' style="float: left;"');
		$month = month_of($this->display_start_date,'text');
		$year = year_of($this->display_start_date);

		return div($cur.$pm.$pw.bigger(bold($month.' '.$year),2).$nw.$nm,'',' class="navCalendar"');
	}

	function super_navigation()
	{
		foreach ($this->display_hours as $h) {
			$checked = $h==$this->current_hour_selection;
			$hours[] = formradio('',$_SERVER['PHP_SELF'].'?hour='.$h,$checked
						   ,($checked ? '' : ' onclick="location.href = this.value"'))
				.smaller(white($h),3);
		}
		$hours = formto().implode('<br />',$hours).formend();

		foreach ($this->display_days as $d) {
			$checked = $d==$this->current_day_selection;
			$days[] = formradio('',$_SERVER['PHP_SELF'].'?day='.$d,$checked
						  ,($checked ? '' : ' onclick="location.href = this.value"'))
				.smaller(white($d.' day'),3);
		}
		$days = formto().implode('<br />',$days).formend();
		if ($this->enable_cancelled_view) {
			$cancelled_link = right(hlink($_SERVER['PHP_SELF'].'?showCancelled='
							.($this->show_cancelled_appointments ? '' : 'Y'),
							smaller(white('Show '.($this->show_cancelled_appointments ? 'Current' : 'Cancelled').' Appointments'),2))
							,' style="margin-top: 3px;"');
		}

		$report_link = $this->is_inanimate
			? right(hlink(CALENDAR_REPORT_INANIMATE_URL,smaller(white('Calendar Report'),2)))
			: right(hlink(CALENDAR_REPORT_MEDICAL_URL,smaller(white('Medical Calendar Report'),2)));

		//--- list calendars ---//
		$list = oline(white(smaller('Go to calendar:',3)))
			.formto()
			.selectto('id','style="font-size: 70%;"')
			.Calendar::get_select_list()
			.selectend()
			.button('Go','','','','',' style="font-size: 65%;"')
			.formend();

		$jump = white(smaller('Jump to date:',3))
			.formto('','',' style="display:inline;"')
			.formvartext('st','',' style=" font-size: 65%; width: 50px;"')
			.button('Go','','','','',' style="font-size: 65%;"')
			.formend();

		

		$this->commands[] = cell(table(
							 row(topcell($hours,'rowspan="2"').topcell($list,'colspan="2"'))
							 . row(topcell(table(row(topcell($days,' style=" padding-left: 15px;"')
											 .topcell($jump . $cancelled_link . $report_link
												    ,' style=" padding-left: 15px;"')),'','class=""')))
						 ,'', 'cellspacing="3" bgcolor="#333333" class=""'));
	}

	function get_select_list($id='')
	{
		$id = orr($id,$_SESSION['calendar_id']);
		$res = agency_query('SELECT calendar_type_code,COUNT(*) FROM calendar_current GROUP BY 1');
		while ($a = sql_fetch_assoc($res)) {
			$label = sql_lookup_description($a['calendar_type_code'],'l_calendar_type').' ('.$a['count'].')';
			$group_s .= selectitem($a['calendar_type_code'],$label,$id==$a['calendar_type_code']);
		}

		$res = get_generic('','','','calendar_current');
		while ($a=array_shift($res)) {
			$label = Calendar_Record::name($a);
			$indi_s .= selectitem($a['calendar_id'],$label,$id==$a['calendar_id']);
		}

		return html_optgroup($group_s,'Group Calendars')
			. html_optgroup($indi_s,'Individual Calendars');
	}

	function link_pto_schedule($date)
	{
		return hlink($_SERVER['PHP_SELF'].'?force_pto='.dateof($date,'SQL'),alt('Schedule PTO','Schedule PTO for this (entire) day')
				 ,'',' onclick="'.call_java_confirm('Are you sure you want to schedule PTO for the entire day of '.dateof($date)).'"');
	}

	function allow_pto_schedule()
	{
		if (!$this->is_inanimate && count($this->cE)==1) {
			global $UID;
			$cEt = $this->cE[0];
			$staff_id = $cEt->config['staff_id'];
			if ($UID == $staff_id) {
				return true;
			}
		}

		return false;

	}

	function pto_schedule_handler()
	{

		//successfully scheduled pto, catching redirect
		if ($date = dateof($_REQUEST['pto_success'])) {
			return 'Successfully scheduled PTO for '.dateof($date);
		}

		//verify allowed
		if (!$this->allow_pto_schedule() or !($date = dateof($_REQUEST['force_pto'],'SQL'))) {
			return false;
		}

		$cEt = $this->cE[0];

		global $UID;
		sql_begin();
		$res = call_sql_function('force_calendar_pto',$cEt->id,enquote1($date),enquote1('PTO'),$UID);
		if (sql_true($res)) {
			header('Location: '.$_SERVER['PHP_SELF'].'?pto_success='.$date);
			sql_end();
		} else {
			$message = div(oline('Couldn\'t schedule PTO for '.dateof($date).':')
					   . webify(sql_last_notice()),'',' class="error"');
			sql_abort();
		}

		return $message;
	}

	function link_calendar_by_date($date,$label,$alt='',$options='')
	{
		$base = $_SERVER['PHP_SELF'].'?st=';
		$link = hlink($base . dateof($date,'SQL'),$label,'',$options);
		if ($alt) {
			$link = alt($link,$alt);
		}
		return $link;
	}

	function link_calendar($calendar_id='',$date='',$label='',$alt='',$options='')
	{
		global $calendar_url;

		$perm = true;
		$l_opts = array();
		if ($calendar_id) {
			$config = Calendar_Record::grab_config($calendar_id);

			// permission check
			$perm = false;
			if ($p_type = $config['calendar_permission_list']) {
				$perm = has_perm($p_type);
			} else {
				$perm = true;
			}
			$l_opts[] = 'id='.$calendar_id;
		}

		if (!$label) {
			$label = $config ? Calendar_Record::name($config).'\'s calendar' : 'Calendar';
		}
		if ($sdate=dateof($date,'SQL')) {
			$l_opts[] = 'st='.$sdate;
		}
		if ($alt) {
			$options .= ' title="'.$alt.'"';
		}
		$stuff = implode('&',$l_opts);
		$query_string = !be_null($stuff) ? '?'.$stuff : '';
		$link = $perm 
			? hlink($calendar_url.$query_string,$label,'',$options)
			: dead_link($label);
		return $link;
	}

	function generate_time_list ($var_name,$current_time='',$start_time='00:00:00', $max_time='24', $on_time=0, $time_size='0.25')
	{
		$hours = hoursof(timeof($start_time,'SQL'));
		$tot = ($max_time - $hours)/$time_size;
		for ($i=$on_time; $i <= $tot; $i++) {
			$sql_time = Calendar::construct_time($hours,$i,$time_size);
			if ($sql_time=='24:00:00') {
				$sql_time = '23:59:00';
			}
			$label_time = timeof($sql_time);
			$items .= selectitem($sql_time,$label_time,$sql_time==$current_time);
		}
		return selectto($var_name)
			. selectitem('','(select time)')
			. $items
			. selectend();
	}

	function default_menu()
	{
		global $title;
		$title = 'AGENCY Calendar';
		
		link_style_sheet('calendar.css','screen, print');

		$res = agency_query('SELECT calendar_type_code,COUNT(*) FROM calendar_current GROUP BY 1');
		$out = div('Group Calendars','',' class="calMenu"');
		$li = '';
		while ($a = sql_fetch_assoc($res)) {
			$label = sql_lookup_description($a['calendar_type_code'],'l_calendar_type').' ('.$a['count'].')';
			$li .= html_list_item(Calendar::link_calendar($a['calendar_type_code'],'',$label));
		}
		$out .= html_list($li,' class="calMenu"');

		$res = get_generic('','staff_id,calendar_type_code,inanimate_item_code','','calendar_current');
		$out .= div('Individual Calendars','',' class="calMenu"');
		$li = '';

		while ($a = array_shift($res)) {
			$label = Calendar_Record::name($a);
			$text = Calendar::link_calendar($a['calendar_id'],'',$label);
			$link = link_engine(array('object'=>'calendar','id'=>$a['calendar_id']),smaller('(config)',2));
			$text = $text.'&nbsp;&nbsp;&nbsp;'.$link;
			$li .= html_list_item($text);
		}
		$out .= html_list($li);

		$out .= div('Calendar Administration','',' class="calMenu"');
		$out .= html_list(html_list_item(link_engine(array('object'=>'calendar','action'=>'add'),'Add a new calendar')));
		return head($title,' style="text-align: center;"')
			. $out;
	}

	function auto_generate_style()
	{
		global $AG_HEAD_TAG;
		$styles_full = $styles_half = array();

		if ($this->is_inanimate) {
			$r_init = 200;
			$g_init = 255;
			$b_init = 254;

			$r_d = 10;
			$g_d = 20;
			$b_d = 10;
		} else {
			$r_init = 213;
			$g_init = 222;
			$b_init = 222;

			$r_d = 25;
			$g_d = 25;
			$b_d = 25;
		}

		for ($i=0; $i < $this->count; $i++) {
			$color = str_pad(dechex(fmod(abs($r_init-($r_d*($i+1))),255)),2,'0')
				. str_pad(dechex(fmod(abs($g_init-$g_d*($i+1)),255)),2,'0')
				. str_pad(dechex(fmod(abs($b_init-$b_d*($i+1)),255)),2,'0');
			$styles_full[] = 'a.calendar'.$i;
			$styles_half[] = 'a.calendarHalf'.$i;
			$style .='
				a.calendar'.$i.', a.calendarHalf'.$i.' { background-color: #'.$color.';}';
		}
		$list_full = implode(', ',$styles_full);
		$list_half = implode(', ',$styles_half);
		$list_styles = $list_full.', '.$list_half;
		$style .= '
			'.$list_styles.'{ text-decoration: none; 
							     display: block;
							     width: 9.9em;
							     margin-left: 0.1em;
			  }
		      '.$list_full.' { height: 1.9em; }
                  '.$list_half.' { height: 0.9em; }';
		$AG_HEAD_TAG .= style($style);
	}

	function my_calendar()
	{
		global $UID, $AG_USER_OPTION;

		$def = get_def('calendar_appointment');
		$res = get_generic(staff_filter($UID),'','','calendar_current');

		if (count($res) < 1) {
			return '';
		}


		$c_rec = array_shift($res);
		$c_id = $c_rec['calendar_id'];
		
		//upcoming appointments
		
		//these settings are stored across sessions
		$hide = $AG_USER_OPTION->show_hide('my_calendar_future');
		$show_hide_link = $AG_USER_OPTION->link_show_hide('my_calendar_future');
		
		$width = $hide ? ' boxHeaderEmpty' : '';

		$out = row(cell(oline(bold(white('Upcoming Appointments')))
				    . Calendar::link_calendar($c_id,'',white(smaller('Go to calendar',2))) . $show_hide_link
				    ,'class="boxHeader'.$width.'" colspan="2"'),'class="future"');
		if (!$hide) {
			$future_filter = array('calendar_id'=>$c_id,
						     'NOT NULL:client_id'=>sql_true,
// 						     '<=:event_start'=>next_day('now',4),
						     'FIELD>=:event_start'=>'CURRENT_TIMESTAMP');
			$future_res = get_generic($future_filter,'event_start',15,'calendar_appointment_current');
			
			while ($a = array_shift($future_res)) {
				$color = $color=='1' ? '2' : '1';
				$out .= row(topcell(value_generic($a['event_start'],$def,'event_start','list'))
						.topcell(smaller(client_link($a['client_id'],client_name($a['client_id'],25))
								     . right(Calendar::link_calendar($c_id,$a['event_start'],'view','Go to Calendar')),2))
						,'class="generalData'.$color.'"');
			}
		}
			
		$future_out = table($out,'',' style="border: solid 1px black; margin-top: 10px;" cellspacing="0px" cellpadding="2px"');
			
		//recent appointments

		//these settings are stored across sessions
		$hide = $AG_USER_OPTION->show_hide('my_calendar_past');
		$show_hide_link = $AG_USER_OPTION->link_show_hide('my_calendar_past');

		$width = $hide ? ' boxHeaderEmpty' : '';
		
		$out = row(cell(oline(bold(white('Recent Appointments')))
				    . Calendar::link_calendar($c_id,'',white(smaller('Go to calendar',2))) . $show_hide_link
				    ,'class="boxHeader'.$width.'" style="" colspan="2"'),'class="past"');
		
		if (!$hide) {
			$past_filter = array('calendar_id'=>$c_rec['calendar_id'],
						   'NOT NULL:client_id'=>sql_true,
						   'FIELD<=:event_start'=>'CURRENT_TIMESTAMP');
			$past_res = get_generic($past_filter,'event_start DESC',15,'calendar_appointment_current');
			
			while ($a = array_shift($past_res)) {
				$color = $color=='1' ? '2' : '1';
				$out .= row(topcell(value_generic($a['event_start'],$def,'event_start','list'))
						.topcell(smaller(client_link($a['client_id'],client_name($a['client_id'],25))
								     . right('Add '.link_quick_dal(alt('DAL(s)','Multiple DALs'),
													     array('client_id'=>$a['client_id'],'performed_by'=>$UID)
													     ,'class="fancyLink"')),2))
						,'class="generalData'.$color.'"');
			}
		}

		$past_out = table($out,'',' style="border: solid 1px black; margin-top: 10px;" cellspacing="0px" cellpadding="2px"');
			
		return $future_out . $past_out;
		
	}

} // End class Calendar


class Calendar_Record {
	// This is a very basic class that will contain an array of records, and a config array
	// to be used by Calendar

	/* public vars */
	var $id;
	var $config;
	var $event_records; //array indexed by start timestamp
	var $end_event_records; //array indexed by end timestamp
	var $blocker_records;  //start time, lunch end time
	var $end_blocker_records;
	var $start;
	var $end;

	function Calendar_Record($id,$start,$end,$get_cancelled = false)
	{
		$this->id = $id;
		$this->start = $start;
		$this->end = $end;
		$this->get_cancelled = $get_cancelled;

		$this->config = $this->grab_config($id);
		$this->is_inanimate();
		$this->grab_events();
		$this->construct_blocker_events();
	}

	function grab_config($id)
	{
		if (!is_numeric($id)) {
			//this will get a configuration for a calendar of same type
			//this is mainly used for permissions, which really shouldn't be varying very much
			//between calendars of same type
			$id = array_shift(get_generic(array('calendar_type_code'=>$id),'','1','calendar'));
			$id = $id['calendar_id'];
		}
		if ($id) {
			$res = get_generic(array('calendar_id'=>$id),'','','calendar');
			if (count($res) !== 1) {
				outline('Couldn\'t find configuration for calendar id "'.$id.'".');
				exit;
			}
			return array_shift($res);
		}
		return false;
	}

	function grab_events()
	{
		$dates = new date_range($this->start,next_day($this->end));
		$filter = array('calendar_id'=>$this->id,
				    array('BETWEEN:event_start'=>$dates,
					    'BETWEEN:event_end'=>$dates));
		$res = get_generic($filter,'event_start','','calendar_appointment'.($this->get_cancelled ? '_cancelled': '_current'));
		$events = $ends = array();
		while ($a = array_shift($res)) {
			$events[$a['event_start']] = $a;
			$ends[$a['event_end']] = $a;
		}
		$this->event_records = $events;
		$this->end_event_records = $ends;
	}

	function is_inanimate()
	{
		return be_null($this->config['staff_id']);
	}

	function construct_blocker_events()
	{
		$blocks = $ends = array();
		$s = $this->start;

		$day_start_time = '00:00:00';
		$day_end_time = '23:59:00';
 		$lunch_start = $this->config['standard_lunch_hour_start'];
 		$lunch_end = $this->config['standard_lunch_hour_end'];
		while($s < $this->end) {
			$i=day_of_week($s);
			$s_time = $this->config['day_'.$i.'_start'];
			$e_time = $this->config['day_'.$i.'_end'];

			if ($s_time == $e_time) { //entire day is blocked out
				$e_start = $s.' 00:00:00';
				$e_end = $s.' 23:59:00';
				$rec = array('blocker'=>true,
						 'label'=>'No Sched',
						 'event_start'=>$s.' '.$day_start_time,
						 'event_end'=>$s.' '.$day_end_time,
						 'event_length'=>'23:59:00');
				$blocks[$e_start] = $rec;
				$ends[$e_end] = $rec;
				$s = next_day($s);
				continue;
			}

			//starting block
			if ($day_start_time !== $s_time) {
				$e_length = $s_time; //since the day start time is at 00:00:00
				$e_start = $s.' '.$day_start_time;
				$e_end = $s.' '.$s_time;
				$rec = array('blocker'=>true,
						 'label'=>'Start of day',
						 'event_start'=>$e_start,
						 'event_end'=>$e_end,
						 'event_length'=>$e_length);
				$blocks[$e_start] = $rec;
				$ends[$e_end] = $rec;
			}
			//day end block
			if ($day_end_time !== $e_time) {
				$e_length = time_calc($day_end_time.'-'.$e_time);
				$e_start = $s.' '.$e_time;
				$e_end = $s.' '.$day_end_time;
				$rec = array('blocker'=>true,
						 'label'=>'End of day',
						 'event_start'=>$e_start,
						 'event_end'=>$e_end,
						 'event_length'=>$e_length);
				$blocks[$e_start] = $rec;
				$ends[$e_end] = $rec;
			}

			//lunch break
			if ($lunch_start !== $lunch_end) {				
				$e_length = time_calc($lunch_end.'-'.$lunch_start);
				$e_start = $s.' '.$lunch_start;
				$e_end = $s.' '.$lunch_end;
				if ($lunch_start >= $e_time) {
					//this lunch block is after the day end. nothing to do
				} else {
					if ($e_end > ($s.' '.$e_time)) {
						$e_end = $s.' '.$e_time;
						$e_length = time_calc($e_time.'-'.$lunch_start);
					}
					$rec = array('blocker'=>true,
							 'label'=>'Lunch',
							 'event_start'=>$e_start,
							 'event_end'=>$e_end,
							 'event_length'=>$e_length);
					$blocks[$e_start] = $rec;
					$ends[$e_end] = $rec;
				}
			}
			$s = next_day($s);
		}

		//clean-up blocker/real-event overlap
		foreach($this->event_records as $e_time_start=>$e_rec) { //cycle through events to find overlap
			$e_time_end = $e_rec['event_end'];
			foreach ($blocks as $b_time_start=>$b_rec) {
				$b_time_end = $b_rec['event_end'];
				if ( ($e_time_start > $b_time_start) and ($e_time_start < $b_time_end) ) { //starting
					$new_rec = $b_rec;
					$new_rec['event_length'] = time_calc(timeof($e_time_start,'SQL').'-'.timeof($b_time_start,'SQL'));
					$new_rec['event_end'] = $e_time_start;
					$ends[$e_time_start] = $blocks[$b_time_start]=$new_rec; //update blocker record
					$ends[$b_time_end] = null; //unset old end-indexed record
					if ($b_time_end > $e_time_end) { //create an after-event blocker
						$new_rec = $b_rec;
						$new_rec['event_start'] = $e_time_end;
						$new_rec['event_length'] = time_calc(timeof($b_time_end,'SQL').'-'.timeof($e_time_end,'SQL'));
						$blocks[$e_time_end] = $ends[$b_time_end] = $new_rec;
					}
				}
				
				if ( ($e_time_end < $b_time_end) and ($e_time_end > $b_time_start) ) { //ending
					$new_rec = $b_rec;
					$new_rec['event_length'] = time_calc(timeof($b_time_end,'SQL').'-'.timeof($e_time_end,'SQL'));
					$new_rec['event_start'] = $e_time_end;
					$ends[$b_time_end] = $blocks[$e_time_end]=$new_rec; //update blocker record
					$blocks[$b_time_start] = null; //unset old start-indexed record
					if ($b_time_start < $e_time_start) { //create a pre-event blocker
						$new_rec = $b_rec;
						$new_rec['event_end'] = $e_time_start;
						$new_rec['event_length'] = time_calc(timeof($e_time_start,'SQL').'-'.timeof($b_time_start,'SQL'));
						$blocks[$b_time_start] = $ends[$e_time_start] = $new_rec;
					}
				}
			}
		}
    		$this->blocker_records = $blocks;
    		$this->end_blocker_records = $ends;
	}

	function add_link($day,$time,$half_height=false,$multi=null)
	{
		if ($this->get_cancelled) {
			return false;
		}
		$start = $day.' '.$time;
		$control = array('object'=>'calendar_appointment',
				     'action'=>'add',
				     'rec_init'=>array('calendar_id'=>$this->id,
							     'event_start'=>$start)
				     );
		$msg = 'Click to add event'.(isset($multi) ? ' for '.$this->name($this->config) : '');
		$class = ($half_height ? 'calendarHalf' : 'calendar') . $multi;
		return link_engine($control,smaller('+'),'',' title="'.$msg.'" class="'.$class.'"');
	}

	function name($config,$link_staff=false)
	{
		// A similar "title" has been added to calendar and appointment views
		return $config['staff_id']
			? ($link_staff ? staff_link($config['staff_id']) : staff_name($config['staff_id']))
			: sql_lookup_description($config['inanimate_item_code'],'l_inanimate_item');
	}

	function text_title()
	{
		if ($this->is_inanimate()) {
			return 'Scheduling Calendar for '.$this->name($this->config);
		}
		$def = get_def('calendar');
		return $def['singular'].' for '.$this->name($this->config);
	}

	function html_title()
	{
		if ($this->is_inanimate()) {
			return $this->text_title();
		}
		$def = get_def('calendar');
		return $def['singular'].' for '.staff_link($this->config['staff_id']);
	}
}

// non-generic engine functions

function form_row_calendar_appointment($key,$value,&$def,$control,&$Java_Engine,$rec)
{
	if (!in_array($key,array('event_end','event_start','client_id','allow_overlap'))) {
		return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec);
	}
	//don't allow editing of times if passed w/ rec_init
	$rec_init = $control['rec_init'];
	if (isset($rec_init['event_end']) && in_array($key,array('event_end','event_start','allow_overlap'))) {
		return hiddenvar('rec['.$key.']',$value)
			. view_generic_row($key,$value,$def,$control['action'],$rec);
	}

	//this is all so we can get a drop list of times
	$action=$control['action'];
      $pr=$def['fields'][$key];
      $label=$pr['label_'.$action];
      $not_valid_flag=$pr['not_valid_flag'];

	//first, client id
	$conf = Calendar_Record::grab_config($rec['calendar_id']);
	if ( in_array($key,array('client_id','allow_overlap')) && $conf['staff_id']) {
		return form_generic_row($key,$value,$def,$control,$Java_Engine,$rec);
	} elseif (in_array($key,array('client_id','allow_overlap'))) {
		
		return hiddenvar('rec['.$key.']',$value);
	}
	//if client appointment record, we don't allow time edits
	if (($action=='edit') && !be_null($rec['client_id']) && in_array($key,array('event_start','event_end'))) {
		return hiddenvar('rec['.$key.']',$value)
			. view_generic_row($key,$value,$def,$control['action'],$rec);
	}


	$date = dateof(orr($value,$rec['event_start']));
	if ($st = is_timestamp($rec['event_start'],'SQL')) {
		//----determine max time----//
		$filter = array('calendar_id'=>$rec['calendar_id'],'>:event_start'=>$st,':event_start<----->date'=>dateof($st,'SQL'));
		$res = get_generic($filter,' event_start','1','calendar_appointment_current');
		if ($a=array_shift($res)) {
			$max_timestamp = $a['event_start'];
		}
		//----determine minimum time----//
		$filter = array('calendar_id'=>$rec['calendar_id'],'<=:event_end'=>$st,':event_start<----->date'=>dateof($st,'SQL'));
		$res = get_generic($filter,' event_start DESC','1','calendar_appointment_current');
		if ($a=array_shift($res)) {
			$min_timestamp = $a['event_end'];
		}
	}
	$end_time = '24';
	$start_time = '00:00:00';
	switch ($key) {
	case 'event_end':
		$current_time = timeof(orr($value,Calendar::construct_time(hoursof(timeof($rec['event_start'],'SQL')),1)),'SQL');
		$on = 1;
		$alt = $max_timestamp ? html_image($GLOBALS['AG_IMAGES']['CLOCK'],
							     ' title="Next event starts at: '.datetimeof($max_timestamp,'US')
							     .'" style="display: inline; vertical-align: text-bottom;"') 
							     : '';
		break;
	case 'event_start':
		$current_time = timeof($value,'SQL');
		$on = 0;
		$alt = $min_timestamp ? html_image($GLOBALS['AG_IMAGES']['CLOCK'],
							     ' title="Previous event ends at: '.datetimeof($min_timestamp,'US')
							     .'" style="display: inline; vertical-align: text-bottom;"') 
			: '';
	}
	
	$time_field = Calendar::generate_time_list('rec['.$key.'_time_]',$current_time,$start_time,$end_time,$on);
	
	$field = hiddenvar("rec[$key]",$value)
 		. oline(formdate('rec['.$key.'_date_]',$date,'Calendar',$element_options))
		. $time_field . $alt;

	$label = oline($label . ' date')
		. right('time');
	$label = $not_valid_flag ? red($label) : $label;
	
	return rowrlcell($label,$field);
}

function valid_calendar_appointment($rec,&$def,&$mesg,$action,$rec_last)
{
	//determine if record overlaps existing record
	//the db does this too, but this provides _clean_ user errors
	//record okay, fall through to normal validity checking
	$valid = valid_generic($rec,$def,$mesg,$action,$rec_last);
	if (!$valid) {
		return false;
	}

	$filter = array('calendar_id'=>$rec['calendar_id'],
			    array('FIELD:1'=>'2', //FIXME: need way to create this query w/o this little placeholder
				    array('<:event_start'=>$rec['event_end'],
					    '>:event_end'=>$rec['event_start']),
				    array('<:event_start'=>$rec['event_end'],
					    '>:event_end'=>$rec['event_end']),
				    array('>=:event_start'=>$rec['event_start'],
					    '<=:event_end'=>$rec['event_end'])
				    ));
	if (sql_true($rec['allow_overlap'])) {
		$filter['!event_start']=$rec['event_start'];
		$filter['!event_end']=$rec['event_end'];
	}
	if ($action=='edit') {
		$filter['<>:calendar_appointment_id'] = $rec['calendar_appointment_id'];
	}
 	$res = get_generic($filter,' event_start','','calendar_appointment_current');
	if (count($res) > 0) { //records exist
		$a =array_shift($res);
		$link = Calendar::link_calendar($a['calendar_id'],$a['event_start'],'View Calendar');
		$m = $a['event_start'] < $rec['event_start'] 
			? 'Ending at: '.datetimeof($a['event_end'],'US')
			: 'Starting at: '.datetimeof($a['event_start'],'US');
		$mesg .= oline('The entered times overlap another event ('.$m.' '.$link.').');
		return false;
	}

	//now we must check our 'blocker' events
	$start = $rec['event_start'];
	$end = $rec['event_end'];
	$start_date = dateof($start,'SQL');
	$end_date = dateof($end,'SQL');

	$cal_s = new Calendar_Record($rec['calendar_id'],$start_date,next_day($start_date));

	//check schedule_ahead_period
	if ($max_ahead = $cal_s->config['maximum_appoinment_date']) {
		if ( ($tperm = $cal_s->config['schedule_ahead_permission'])
		     && has_perm($tperm,'W')) {
			//has permission to schedule as far as desired
		} elseif (dateof($start,'SQL') > $max_ahead) {
			$mesg .= oline('You don\'t have permission to schedule past '.dateof($max_ahead));
			return false;
		}
	}

	//check end date
	if ($c_end_date = $cal_s->config['calendar_date_end']) {
		if (dateof($start,'SQL') > $c_end_date) { //can't schedule ahead of end date
			$mesg .= oline(dateof($start).' is past the end date ('.dateof($c_end_date).') for this calendar.');
			return false;
		}
	}

	//make sure start isn't on blocker
	foreach ($cal_s->blocker_records as $b_st=>$b_rec) {
		$b_end = $b_rec['event_end'];
		if ( ($start >= $b_st) and ($start < $b_end) ) {
			$mesg .= oline('The start date falls within a blocked out ('.$b_rec['label'].') event:')
				.'starting on '.datetimeof($b_st).' and ending on '.datetimeof($b_end);
			return false;
		}
	}

	if ($cal_s->is_inanimate() && !be_null($rec['client_id'])) {
		$mesg .= oline('You can\'t schedule clients for this calendar.');
		return false;
	}

	//make sure end isn't on blocker
	if ($start_date==$end_date) {
		$cal_e =& $cal_s;
	} else {
		$cal_e = new Calendar_Record($rec['calendar_id'],$end_date,next_day($end_date));
	}
	foreach ($cal_e->blocker_records as $b_st=>$b_rec) {
		$b_end = $b_rec['event_end'];
		if ( ($end > $b_st) and ($end <= $b_end) ) {
			$mesg .= oline('The end date falls within a blocked out ('.$b_rec['label'].') event:')
				.'starting on '.datetimeof($b_st).' and ending on '.datetimeof($b_end);
			return false;
		}
	}
	return $valid;
}

function title_calendar_appointment($action,$rec,$def)
{
	$id = $rec['calendar_id'];
	if ($id) {
		$res = get_generic(array('calendar_id'=>$id),'','','calendar');
		$a = array_shift($res);
		$title = ucfirst($action).'ing '.$def['singular'].' for '.Calendar_Record::name($a,true);
		return bigger(bold($title));
	}
	return false;
}

function engine_record_perm_calendar_appointment($control,$rec,$def)
{
	$id = $rec['calendar_id'];
	$config = Calendar_Record::grab_config($id);
	$action = $control['action'];
	if ($p_type = $config['calendar_permission_'.$action]) {
		return has_perm($p_type);
	}
	return true;
}

function post_calendar_appointment($rec,$def,&$mesg,$filter='',$control=array())
{
	// Needs a special function so that repeating events are processed correctly

	// first make a new rec, and post the orginal appointment
	$new_rec = $rec;
	unset($new_rec['repeat_until']); // virtual fields, not in the database
	unset($new_rec['event_repeat_type_code']);
	$result = post_generic($new_rec,$def,$mesg,$filter,$control);
	// if there's a filter, that means it's an update, so we don't do any repeating stuff
	// if there's no repeat information, we also just return
	if ($filter || be_null($rec['repeat_until']) || be_null($rec['event_repeat_type_code']))
	{
		return $result;
	}
// FIXME:  This code could be made much cleaner! (MUCH cleaner!) (MUCH MUCH cleaner!)
	$query = 'SELECT make_repeating_calendar_events('
			 . implode(array($rec['calendar_id'],
							 "'" . $rec['event_start'] . "'::timestamp + '" . $rec['event_repeat_type_code'] . "'::interval",
							 "('(" . $rec['event_end'] . "'::time - '" . $rec['event_start'] . ")'::time)::time",
							 "'" . $rec['repeat_until'] . "'::date + 1",
							 "'" . $rec['event_repeat_type_code'] . "'::interval",
							 "'" . sqlify($rec['description']) . "'::text",
							 be_null($rec['client_id']) ? 'NULL' : $rec['client_id'],
							 $GLOBALS['UID'],
							 "'" . (sql_true($rec['allow_overlap']) ? sql_true() : sql_false()) . "'::bool"),',')
			. ')';
	 $res = sql_query($query);
	 $mesg .= oline('REPEATING EVENTS ')  . oline(webify(sql_last_notice()),2);
	 $mesg .= oline($res ? 'We tried to schedule your repeating events.  Please review lines above for possible conflicts.'
						: 'Failed to schedule repeating events');
	 return $result ;
}

function calendar_appointment_to_ics( $app )
{
        $vcal_template= oline('BEGIN:VCALENDAR')
        . oline('VERSION:0.1')
        . oline('PRODID:-//AGENCY/www.desc.org//NONSGML v0.1//EN')
        . oline('BEGIN:VEVENT')
        . oline('DTSTART:$date_start')
        . oline('DTEND:$date_end')
        . oline('SUMMARY:$summary')
        . oline('DESCRIPTION:$description')
        . oline('URL:$url')
        . oline('END:VEVENT')
        . oline('END:VCALENDAR');
        ;
/*
    Line breaks in ICS files are written out "backslash, n" \n,
        so they should be in single quotes like '\n', or double quotes "\\n"
        Per the spec, lines _should_ be wrapped at 75 chars, which we haven't accounted for.
*/

        $client_id=$app['client_id'];
        $url='https://' . $_SERVER['SERVER_NAME'] . $GLOBALS['AG_HOME_BY_URL'] .
             '/display.php?control[object]=calendar_appointment&control[action]=view&control[id]=' . $app['calendar_appointment_id'];
        $description= (!be_null($app['comments']) ? $app['comments'] . '\n\n' : '')
                                  .  'Length: ' . $app['event_length']
                                  . (sql_true($app['allow_overlap']) ? " (overlap allowed)" : '' )
                                  . '\n\nCalendar: ' . $app['calendar_title']
                                  . '\n\nAdded by ' . staff_name($app['added_by']) . ' @ ' . datetimeof($app['added_at'],'US')
                                  . (($app['added_at']<>$app['changed_at'])
                                                ? '\nChanged by ' . staff_name($app['changed_by']) . ' @ ' . datetimeof($app['changed_at'],'US') : '')
                                  . '\n\nExported from AGENCY @ ' . datetimeof('now','US') ;
    $summary=$app['description']
                        . ($client_id ? client_name($client_id) : '')
                        . (!be_null($app['calendar_appointment_resolution_code'])
                                        ? ' (' . $app['calendar_appointment_resolution_code'] . ')'
                                        : '');
        $replace=array('$date_start'=>datetimeof($app['event_start'],'ICS'),
                                '$date_end'=>datetimeof($app['event_end'],'ICS'),
                                '$summary'=>$summary,
                                '$description'=>$description,
                                '$url'=>$url
                                );
        $ics=str_replace(array_keys($replace),array_values($replace),$vcal_template);
        return $ics;
	}

function calendar_to_ics( $cal_id)
{
	$cal_head='
	BEGIN:VCALENDAR
	VERSION:2.0
	PRODID:-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN
	BEGIN:VTIMEZONE
	TZID:/mozilla.org/20070129_1/America/Los_Angeles
	X-LIC-LOCATION:America/Los_Angeles
	BEGIN:DAYLIGHT
	TZOFFSETFROM:-0800
	TZOFFSETTO:-0700
	TZNAME:PDT
	DTSTART:19700308T020000
	RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3
	END:DAYLIGHT
	BEGIN:STANDARD
	TZOFFSETFROM:-0700
	TZOFFSETTO:-0800
	TZNAME:PST
	DTSTART:19701101T020000
	RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11
	END:STANDARD
	END:VTIMEZONE
	END:VCALENDAR' . '\n';

	$cal_begin='BEGIN:VCALENDAR' . '\n';
	$cal_foot='END:VCALENDAR' . '\n';

	//FIXME:  Make me adjustable
	$range=new date_range(prev_day('',30),next_day('',60));

	$filter=array('BETWEEN:event_start'=>$range,
                'calendar_id'=>$cal_id);
	$cal_def = get_def('calendar_appointment');
	$recs=get_generic($filter,'','',$cal_def);
	while ( $x=array_shift( $recs ) ) {
            $file .= oline(calendar_appointment_to_ics($x),2);
	}
	return $cal_head . $file;
}

?>
