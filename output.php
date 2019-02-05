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

A generic output class, designed to grow, but for now it will output

-Errors (add_error())
-Comments (add_comment())
-Main body (etc)
-Style (to head) (add_style())
-raw html to head (add_head())

The class must be called prior to any html output in order to work properly

It will also eventually work in TEXT mode

Once everything is added, a call to get_formatted() will return the output.

*/

class Output {

	var $errors = array();
	var $comments = array();
	var $body = '';
	var $head = '';
	var $body_options = '';
	var $title = '';
	var $mode;

	function Output() { 
		$this->mode = AG_OUTPUT_MODE;
	}

	function get_formatted() {
		global $AG_BODY_TAG_OPTIONS, $AG_HEAD_TAG;
		$AG_BODY_TAG_OPTIONS .= $this->body_options;
		$AG_HEAD_TAG .= $this->head;
		$out = $this->format_error();
		$out .= head($this->title,' class="title"');
		$out .= $this->format_comment();
		$out .= $this->body;
		return $out;
	}

	function title($t) {
		//for now, we go with the global var
		global $title;
		$this->title = $title = $t;
	}

	function add_body($body,$important=false) { $this->add_stuff('body',$body,$important); }
	function add_error($error,$important=false) { $this->add_stuff('errors',$error,$important); }
	function add_comment($comment,$important=false) { $this->add_stuff('comments',$comment,$important); }
	function add_head($head,$important=false) { $this->add_stuff('head',$head,$important); }
	function add_body_options($body_options,$important=false) { $this->add_stuff('body_options',$body_options,$important); }

	/* Private Functions */
	function add_stuff($type,$stuff,$important) {
		if (is_array($this->{$type})) {
			if ($important) {
				array_unshift($this->{$type},$stuff);
			} else {
				array_push($this->{$type},$stuff);
			}
		} elseif (is_string($this->{$type})) {
			if ($important) {
				$this->{$type} = $stuff . $this->{$type};
			} else {
				$this->{$type} .= $stuff;
			}
		} else {
			/* something is broken!! */
			outline('Invalid type passed to Output::add_stuff()');
			var_dump($this->{$type});
		}
	}

	function format_error() {
		if (empty($this->errors)) { return; }
		return div(implode( ($this->mode=='HTML' ? '<br />' : "\n"),$this->errors),'',' class="error"'); 
	}

	function format_comment() {
		if (empty($this->errors)) { return; }
		return div(implode( ($this->mode=='HTML' ? '<br />' : "\n"),$this->comments),'',' class="comment"'); 
	}

}
?>