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

class Java_Engine {

	//A class for sending JavaScript to the appropriate places on Engine-generated forms.
	//Works with the configuration file.

	//For now, all this will do is send element disable/enable scripts--later it will do form validation.

	var $def; //the configuration array passed by engine();
	var $rec;
	var $required_js=array();
	var $form;
	var $current_element;
	var $JS=array(); //a field-indexed array
	var $FLAGS=array(); //a field-indexed array for placing flags

	function Java_Engine($def=null,$rec=null,$from_scratch=false) {
		if ($from_scratch) {
			return; //this way it can be called from without engine
		}
		$this->def=$def;
		$this->rec=$rec;
		$this->form=$GLOBALS['form_name'];
		$this->fields=$def['fields'];
		$this->process_java(); //will build an array for lookup later
	}

	function from_scratch() {
		$j = new Java_Engine(null,null,true);
		return $j;
      }

	function form_element_java($field) {
		$this->current_field=$field;
		return $this->JS[$field];
	}

	function process_java() {
		//check config array
		foreach ($this->fields as $field => $params) {
			$this->current_element=$this->variable_to_element($field);
			$js=$params['java'];
			if (is_array($js)) {
				$on_event=orr($js['on_event'],array());
				foreach ($on_event as $event=>$action) {
					switch ($event) {
					case 'disable_on_select':
						$this->add_javascript($this->disable_js());
						$disables=$action; //this should be an array
						$this->JS[$field] .= ' '.$this->on_event('ONCHANGE',$this->on_event_call_fn('nullOrNot',$disables)).' ';
						foreach ($disables as $related_field) {
							$this->JS[$related_field] .= $this->default_disable($field);
						}
						break;
					case 'disable_on_null':
						$this->add_javascript($this->disable_js());
						$disables=$action; //this should be an array
						$this->JS[$field] .= ' '.$this->on_event('ONCHANGE',$this->on_event_call_fn('nullDisable',$disables)).' ';
						foreach ($disables as $related_field) {
							$this->JS[$related_field] .= $this->default_disable($field,'null');
						}
						break;
					case 'enable_on_value':
						$func_call = 'valueEnable';
						$val_ch = array_shift($action);
						$def_enab = 'value_equal';
					case 'enable_on_null':
						$func_call = orr($func_call,'nullEnable');
						$def_enab = orr($def_enab,'notnull');
						$this->add_javascript($this->disable_js());
						$enables=$action; //this should be an array
						$this->JS[$field] .= ' '.$this->on_event('ONCHANGE',$this->on_event_call_fn($func_call,$enables,$val_ch)).' ';
						foreach ($enables as $related_field) {
							$this->JS[$related_field] .= $this->default_disable($field,$def_enab,$val_ch);
						}
						break;
					case 'fill_read_boolean':
						$this->add_javascript($this->disable_js());
						$swap_tf=$action['true_false_swap']; //an optional parameter to switch true and false
						unset($action['true_false_swap']);
						$readfills=$action; //an associative array of field and value pairs
						$true=' '.$this->on_event('ONCLICK',$this->on_event_call_fn('writeableThem',array_keys($readfills))).' ';
						$false=' '.$this->on_event('ONCLICK',$this->on_event_call_many_fns('readFill',$readfills)).' ';
						$this->JS[$field]['true'] .= ($swap_tf ? $false : $true);
						$this->JS[$field]['false'] .= ($swap_tf ? $true : $false);
						break;
					case 'disable_boolean':
						$this->add_javascript($this->disable_js());
						$swap_tf=$action['true_false_swap']; //an optional parameter to switch true and false
						unset($action['true_false_swap']);
						$disables=$action; //an array of fields
						$true=' '.$this->on_event('ONCLICK',$this->on_event_call_fn('enableThem',$disables)).' ';
						$false=' '.$this->on_event('ONCLICK',$this->on_event_call_fn('disableThem',$disables)).' ';
						$this->JS[$field]['true'] .= ($swap_tf ? $false : $true);
						$this->JS[$field]['false'] .= ($swap_tf ? $true : $false);
						foreach ($disables as $related_field) {
							if ($this->def['fields'][$related_field]['data_type']=='boolean') {
								$this->JS[$related_field]['true'] .= $this->default_disable($field,false);
								$this->JS[$related_field]['false'] .= $this->default_disable($field,false);
							} else {
								$this->JS[$related_field] .= $this->default_disable($field,false);
							}
						}
						break;
					case 'populate_on_select':
						$populate_field=$action['populate_field']; //only one select may be populated at this juncture
						$populate_element=$this->variable_to_element($populate_field);
						$this->FLAG[$populate_field]['blank_selects']=true;
						$this->JS[$field] .= 
							' '.$this->on_event('ONCHANGE',
										  'populateSelect('.$this->element_to_object($this->current_element)
										  .','.$this->element_to_object($populate_element).',\'\')');
						//this function must be custom written to return a 3-dimensional javascript array
						//such that arr[0]=grouping (the select values in the first select list)
						//arr[1]=populate values for the second select list
						//arr[2]=labels for the second select list
						$table=$action['table']; //this must be specified
						$from_field=orr($action['from_field'],$field);
						$pop_field=orr($action['pop_field'],$populate_field);
						$label_field=orr($action['label_field'],$this->def['fields'][$pop_field]['lookup']['label_field'],$pop_field);

						$populate_array=$this->generate_populate_select_array($table,$from_field,$pop_field,$label_field);
						$this->add_javascript('var arrPop=new Array()'."\n".'arrPop = '.$populate_array); 

						if (!be_null($this->rec[$field])) {
							$this->add_javascript_body(
											   $this->on_event('onLoad',
														 'populateSelect('.$this->element_to_object($this->current_element)
														 .','.$this->element_to_object($populate_element).',\''
														 . $this->rec[$populate_field] . '\',\''
														 .$this->rec[$field].'\')'));

						} elseif (!be_null($this->rec[$populate_field])) {
							$this->add_javascript_body(
											   $this->on_event('onLoad',
														 'populateSelect('.$this->element_to_object($this->current_element)
														 .','.$this->element_to_object($populate_element).',\''
														 .$this->rec[$populate_field].'\')'));
						}
						break;
					default :
						$this->JS[$field] .=' '.$this->on_event($event,$action);
					}
				}
			}
		}
	}

	function on_event($event,$dothis) {
		return strtolower($event).'="javascript:'.htmlentities($dothis).'"'; //trying quotes
	}

	function default_disable($condition_field,$condition=null,$val='') {
		if (is_null($condition)) {
			if (!be_null($this->rec[$condition_field])) {
				return $this->disable_element();
			}
		}
		if (!$condition && sql_false($this->rec[$condition_field])) {
			return $this->disable_element();
		}
		if ($condition=='null' && be_null($this->rec[$condition_field])) {
			return $this->disable_element();
		}
		if ($condition=='notnull' && !be_null($this->rec[$condition_field])) {
			return $this->disable_element();
		}
		if ($condition=='value_equal' && ($this->rec[$condition_field] !== $val)) {
			return $this->disable_element();
		}
			
		//other functionality goes here
	}

	function disable_element() {
		return ' disabled="t"';
	}

	function on_event_call_fn($call_fn,$element_list,$opt_arg='') {
		if (!is_array($element_list)) {
			$varlist=array($element_list);
		}
		$disables=array();
		if (in_array($call_fn,array('valueEnable','nullOrNot','nullEnable','nullDisable'))) { //this function requires the called from element
			array_push($disables,$this->element_to_object($this->current_element)); 
			if ($opt_arg) {
				array_push($disables,"'".$opt_arg."'");
			}
		}
		foreach ($element_list as $varname) {
			if ($this->def['fields'][$varname]['data_type']=='boolean') {
				$this->JS[$varname]['true'] .=' id="'.$varname.'_t"';
				$this->JS[$varname]['false'] .=' id="'.$varname.'_f"';
				$obj_t = $this->id_to_object($varname.'_t');
				$obj_f = $this->id_to_object($varname.'_f');
				array_push($disables,$obj_t,$obj_f);
			} else {
				$element=$this->variable_to_element($varname);
				array_push($disables,$this->element_to_object($element));
			}
		}
		return $call_fn.'('.implode(',',$disables).')';
	}

	function on_event_call_many_fns($call_fn,$args,$nulls=false) {

		$js=array();
		foreach ($args as $varname => $value) {
			if (is_array($varname)) { //functionallity here
			} else {
				$VARNAME=$this->element_to_object($this->variable_to_element($varname));
			}
			if (is_array($value)) { //functionallity here
			} else {
				$VALUE='"'.($nulls ? null : $value).'"';
			}
			array_push($js,$call_fn.'('.$VARNAME.','.$VALUE.')');
		}
		return implode(';',$js).';';
	}

	function id_to_object($id) {
		return 'document.getElementById(\''.$id.'\')';
	}

	function variable_to_element($var) {
		return 'rec['.$var.']'; //this may or may not change in the future
	}

	function element_to_object($element) {
		return 'document.'.$this->form.'.elements["'.$element.'"]';
	}

	function add_javascript($js) {
		//this isn't really needed at the moment because all required js is static for disable/enable

		if (!in_array($js,$this->required_js)) { //only need to send each function once
			array_push($this->required_js,$js);
		}
	}

	function add_javascript_body($js) {
		//sends javascript to within the body tag of the page
		$GLOBALS['AG_BODY_TAG_OPTIONS'].=' '.$js;
	}

	function generate_populate_select_array($table,$from_field,$pop_field,$label_field) {
		//returns a 3-dimensional array: [parent select code][populate select code][populate select label]
		//in order to use this function, the associated sorting values must reside in the same table
		$res=agency_query("SELECT $from_field AS from_field, $pop_field AS pop_field, $label_field AS label_field FROM $table",'',"$from_field,$label_field");
		$array=array();
		while ($a=sql_fetch_assoc($res)) {
			$from=$a['from_field'];
			if ($old !== $from) {
				array_push($array,array('\''.$from.'\'','\'\'','\'---choose---\''));
			}
			$old=$from;
			$pop=$a['pop_field'];
			$label=$a['label_field'];
			array_push($array,array('\''.$from.'\'','\''.$pop.'\'','\''.$label.'\''));
		}
		//$array now contains all the information we need, and now we translate to javascript array
		return $this->php_to_js_array($array);
	}

	function php_to_js_array($array) {
		$out = '['."\n" . array_reduce($array, 'multi_implode_f') . ' ]';
		return $out;
	}

	function get_javascript() {

		return '<script language="JavaScript" type="text/javascript">'."\n"
			. implode("\n",$this->required_js)."\n"
			.'</script>'."\n";
	}
	
	function get_js($js,$options='') {
		$js = be_null($js) ? $js : "\n".$js."\n";
		return '<script language="JavaScript" type="text/javascript"'.$options.'>'
			.$js
			.'</script>'."\n";
	}

	function hide_content($content,$id,$hide=false) {

		//this function can be called statically

		//if $hide=true, the content will start off as hidden
		//little hide/add links are thrown in at the top
		$links = Java_Engine::hide_show_button($id,$hide);
		return $links.'<BR>'."\n".Java_Engine::hide_show_content($content,$id,$hide);
	}

	function hide_show_content($content,$id,$hide=false) {
		$hidden = ' style="display: none;"';
		$hide_content = ($hide) ? $hidden : null;
		return span($content, ' id="'.$id.'"'.$hide_content);
	}

	function hide_show_button($id,$hide=false,$link=false,$label=null,$send_js=false) {
		$hidden = ' style="display: none;"';
		$visible = ' class="fancyLink" onclick="javascript:blur();"';
		$hide_options = ($hide) ? $hidden : '';
		$show_options = ($hide) ? '': $hidden;

		if ($link) {
			return span(hlink('javascript:showHideElement(\''.$id.'\')','show'.$label,'',$visible), ' id="'.$id.'_show"'.$show_options)."\n"
				. span(hlink('javascript:showHideElement(\''.$id.'\')','hide'.$label,'',$visible), ' id="'.$id.'_hide"'.$hide_options)."\n";
		}
		return  span(hlink('javascript:showHideElement(\''.$id.'\')',
					 alt(html_image($GLOBALS['AG_IMAGES']['JS_SHOW'],' style="border-style: none;"')
					     ,'Click to view'),'',' onclick="javascript:blur();"')
				 ,' id="'.$id.'_show"'.$show_options)."\n"
			. span(hlink('javascript:showHideElement(\''.$id.'\')',
					 alt(html_image($GLOBALS['AG_IMAGES']['JS_HIDE'],' style="border-style: none;"')
					     ,'Click to hide'),'',' onclick="javascript:blur();"')
				 ,' id="'.$id.'_hide"'.$hide_options)."\n";
	} 

	function tabbed_java($content_array,$width='100%',$ini_show) {
		//$content_array should be an array containing desired content and id and label
		//first element will be visible on load
		//tabs will have an id with 'Tab' appended to the end
		$ids=array();
		$output=$tabs='';
		$total=count($content_array)-1;
		$j=0;
		$i=1;
		foreach ($content_array as $type=>$def) {
			$id = $def['id'];
			$idFocus = $def['idFocus'];
			$label = $def['label'];
			$content = $def['content'];
			array_push($ids,$id);

			//define styles for layout
			$color = ' width: '.$width.'; margin: 0px 0px -13px 0px; background-color: '.$def['color'].';'; //???!!! negative margin fixes errant space??
			$main_style = ' style="'.$color.' display: '.($id==$ini_show ? 'block' : 'none').';"';

			$tab_position =  ' text-align: right; position: relative; left: '.(-15*$j).'px;';
			$j++;
			if ($id==$ini_show) {
				$zindex = $total;
			} else {
				$zindex = $total-$i;
				$i++;
			}
			$tab_style=' style="z-index:'.$zindex.';'.$tab_position.'"';
			
 			$tabs .= span($label, ' id="'.$id.'Tab"'.$tab_style.' onclick="javascript:show'.$id.'();getElementById(\''.$idFocus.'\').focus()"');
 			$output .= div($content,$id,$main_style);
		}
		$js = Java_Engine::tabbed_java_build($ids);
 		$JS = Java_Engine::get_js($js);
		return $JS //the negative margin offsets the extra space caused by positioning
			.div($tabs,null,' style=" margin-left: '.((-$total*15)-2).'px; margin-right: '.((-$total*15)-2).'px;"')
			.$output; 
	}

	function tabbed_java_build($ids) {

		$total = count($ids)-1;
		$JS='';
		foreach ($ids as $ID) {

			$JS .= 'function show'.$ID.'() {'."\n";
			$i=1;
			foreach ($ids as $id) {
				if ($id==$ID) {
					$z_index = $total;
				} else {
					$z_index = $total-$i;
					$i++;
				}

				$show = ($id==$ID) ? 'block' : 'none';
				$JS .= "\n\t".'document.getElementById(\''.$id.'Tab\').style.zIndex="'.($z_index).'";'."\n";
				$JS .= "\t".'document.getElementById(\''.$id.'\').style.display="'.$show.'";';
			}
			$JS .= "\n\t".'}'."\n";
		}
		return $JS;
	}
				
	function hide_show_buttons($s_button,$h_button,$force_js_send=false,$dis='inline',$ini_hide=true)
	{
		Java_Engine::onclick_switch_js($force_js_send);
		static $hide_show_button_id;
		if (is_null($hide_show_button_id)) { 
			$hide_show_button_id = 0; 
		} else { 
			$hide_show_button_id++;
		}
		$sid = 'showButton'.$hide_show_button_id;
		$hid = 'hideButton'.$hide_show_button_id;
		return span($s_button,' id="'.$sid.'" onclick="javascript:blur();switchTwo(\''.$sid.'\',\''.$hid.'\',\''.$dis.'\')" style="display: '
				.($ini_hide ? $dis : 'none').';"')
			.span($h_button,' id="'.$hid.'" onclick="javascript:blur();switchTwo(\''.$sid.'\',\''.$hid.'\',\''.$dis.'\')" style="display: '
				.($ini_hide ? 'none' : $dis).';"');
	}

	function toggle_table_row_display($link_text,$class)
	{
		return Java_Engine::toggle_tag_display('tr',$link_text,$class,'table-row');
	}

	function toggle_table_cell_display($link_text,$class,$both=true)
	{
		if ($both) {
			$tags = array('td','th');
		} else {
			$tags = 'td';
		}
		return Java_Engine::toggle_tag_display($tags,$link_text,$class,'table-cell');
	}

	function toggle_tag_display($tag,$link_text,$class,$display='inline',$force_js_send=false)
	{
		Java_Engine::toggle_css_class_js($force_js_send);
		if (is_array($tag)) {
			$tag = "['".implode("','",$tag)."']";
		} else {
			$tag = "'".$tag."'";
		}
		return hlink('#',$link_text,''
				 ,' class="fancyLink" onclick="javascript:toggleDisplayClass('.$tag.',\''.$class.'\',\''.$display.'\');blur();return false;"');		
	}

	function toggle_id_display($link_text,$id,$display='inline')
	{	
		return hlink('javascript:toggleDisplayId(\''.$id.'\',\''.$display.'\')',$link_text,'',' class="fancyLink"');
	}

	function toggle_css_class_js($force=false) {
		$js = '
			function toggleDisplayClass(tagName,cssClass,displayOpt) {
                     if (typeof(tagName)=="object") {
                           for (var i=0; i<tagName.length; i++) {
                                 var els = document.getElementsByTagName(tagName[i]);
					   toggleDisplayClassElements(els,cssClass,displayOpt);
                           }
                     } else {
			         var els = document.getElementsByTagName(tagName);
				   toggleDisplayClassElements(els,cssClass,displayOpt);
			   }
			}
		      
		      function toggleDisplayClassElements(els,cssClass,displayOpt) {
				for (var i=0;i<els.length;i++) {
					if (els[i].className==cssClass) {
						if (els[i].style.display !=="none") {
							els[i].style.display="none";
						} else {
							els[i].style.display=displayOpt;
						}
					} 
				}
			}';
		$njs = Java_Engine::get_js($js);
		if ($force) { 
			out($njs);
			return;
		}
		if (!strpos($GLOBALS['AG_HEAD_TAG'],$njs)) {
			$GLOBALS['AG_HEAD_TAG'] .= $njs; //send actual script to header
		}
	}

// 	function toggle_css_id_js() {
// 		$js = '
// 			function toggleDisplayId(tid,displayOpt) {
// 			   var el = document.getElementById(tid);
// 			   if (el.style.display !== "none") {
// 				el.style.display="none";
// 			   } else {
// 				el.style.display=displayOpt;
// 			   }
// 		     }';
// 		$njs = Java_Engine::get_js($js);
// 		if (!strpos($GLOBALS['AG_HEAD_TAG'],$njs)) {
// 			$GLOBALS['AG_HEAD_TAG'] .= $njs; //send actual script to header
// 		}
// 	}

	function onclick_switch_js($force=false)
	{
		$js = '
			function switchTwo(a,b,dis) {
				var aEl = document.getElementById(a);
				var bEl = document.getElementById(b);
				var aVis = aEl.style.display;
				if (aVis=="none") {
					aEl.style.display = dis;
					bEl.style.display = "none";
				} else {
					aEl.style.display = "none";
					bEl.style.display = dis;
				}					
			}';
		$njs = Java_Engine::get_js($js);
		if ($force) { 
			out($njs);
			return;
		}
		if (!strpos($GLOBALS['AG_HEAD_TAG'],$njs)) {
			$GLOBALS['AG_HEAD_TAG'] .= $njs; //send actual script to header
		}
	}

	//FIXME: Many of these scripts could be moved to a .js file since they are static

	function disable_js() {
		return 
			'function nullOrNot(obj) {
			var opt = obj.options[obj.selectedIndex].value;
			if (opt=="" || opt=="-1") {
				for (var i=1;i < arguments.length;i++) {
					enableIt(arguments[i]);
				}
			} else {
				for (var i=1;i < arguments.length;i++) {
					disableIt(arguments[i]);
				}
			}
		}
		
		function nullEnable(obj) {
			if (obj.value=="") {
				for (var i=1;i < arguments.length;i++) {
					enableIt(arguments[i]);
				}
			} else {
				for (var i=1;i < arguments.length;i++) {
					disableIt(arguments[i]);
				}
			}
		}
		
		function valueEnable(obj,val) {
			if (obj.value==val) {
				for (var i=2;i < arguments.length;i++) {
					enableIt(arguments[i]);
				}
			} else {
				for (var i=2;i < arguments.length;i++) {
					disableIt(arguments[i]);
				}
			}
		}
		
		function nullDisable(obj) {
			if (obj.value=="") {
				for (var i=1;i < arguments.length;i++) {
					disableIt(arguments[i]);
				}
			} else {
				for (var i=1;i < arguments.length;i++) {
					enableIt(arguments[i]);
				}
			}
		}
		
		function disableThem() {
			for (var i = 0; i < arguments.length; i++) {
				disableIt(arguments[i]);
			}
			
		}
		function enableThem() {
			for (var i = 0; i < arguments.length; i++) {
				enableIt(arguments[i]);
			}
		}
		
            function writeableThem() {
                  for (var i =0; i < arguments.length; i++) {
                       writeIt(arguments[i]);
                  }
            }

            function readFill(obj,val) {
                 obj.readonly = true;
                 obj.value = val;
            }

            function writeIt(obj) {
                 obj.readonly = false;
                 obj.value = "";
            }

		function disableIt(obj)	{ 
			obj.disabled = true; 
			obj.value = "";
		}	
		function enableIt(obj){ obj.disabled = false; }';
	}

} //end class Java_Engine
?>
