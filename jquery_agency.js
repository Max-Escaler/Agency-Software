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

/* This file contains all the custom code for jquery and AGENCY.
   For now.  It may make more sense to break it up into separate
   files later.
 */

/* Datepicker for date fields */
$(function() {
	var today=' <a href=# class="calTodayLink fancyLink">today</a>';
	$(".field_date").after(today);
	$(".calTodayLink").click( function(event) {
		event.preventDefault();
		var tmp=new Date();
		var tmp2=1+tmp.getMonth()+'/'+tmp.getDate()+'/'+tmp.getFullYear();
		$(event.target).prev().prev().val(tmp2).change();
	});

	$('.field_date').datepick( {
			showOnFocus: false, 
    		showTrigger: '<img src="images/calendar.png" alt="Popup" class="calButton"/>',
			monthsToShow: 4,
			monthsOffset: 3,
			monthsToStep: 4,
			firstDay: 1,
			yearRange: '-100:+10'
	});
});

/* TimePicker for time fields */
$(function() {
	var clock_button='<img src="images/clock.gif" class="clockButton" />';
	var now=' <a href=# class="calNowLink fancyLink">now</a>';
	$(".field_time").after(now).after(clock_button);
	$(".clockButton").clockpick( {
		starthour: 0,
		endhour: 23,
		minutedivisions: 12
	},
	function( ntime ) {
		$(this).prev().val(ntime);
	});

	$(".calNowLink").click( function(event) {
		event.preventDefault();
		var tmp=new Date();
		var hours=tmp.getHours();
		if ( hours >= 12 ) {
			var ampm = "pm";
			hours = hours - 12;
		} else {
			var ampm = "am";
		}
		var min = tmp.getMinutes();
		if (min < 10) {
			min = '0' + min;
		}
		var tmp2= hours + ':' + min + ' ' + ampm;
		$(event.target).prev().prev().val(tmp2);
	});
});

/* Highlight errors and selected checkboxes on Engine forms */
$(function() {
	$(".engineFormError").closest("tr").addClass("engineFormError"); 
	$(".engineFormError").closest("tr").click( function() { 
		$(this).find(".engineFormError").removeClass("engineFormError");
		$(this).removeClass("engineFormError");
	});
	$(".engineForm input:checkbox:checked").closest('tr').addClass("engineFormSelected");

	$(".engineForm input:checkbox").change( function() {
		$(this).closest('tr').toggleClass('engineFormSelected');
	});
});

/* For multi forms, check the "selected" box if another input is entered for that row */
$(function() {
	$('table.multiForm input').click( function() {
		if ( ($(this).val() || $(this).prop('checked')) ) {	
			var target=$(this).closest('tr');
			var target2=$(target).closest('table').closest('tr');
			// If its an extra info row, need to get out of table and go up a row
			if ($(target2).hasClass('AdditionalInformation')) {
				target=$(target2).prev();
			}
			target=$(target).find('input').first();
			if ( (!$(target).is($(this))) && ($(target).prop('checked')===false) ) {
				$(target).click();
			}
		}
	});
});


/* Hide & Toggle minor engine messages */
$(function() {
	$(".engineMessageResultDetail").hide().before('<a href="#" class="toggleLink">details...</a>');
	$(".engineMessageResultDetail").prev().click( function(event) {
		event.preventDefault();
		$(this).next().toggle();
	});
});

/* Hide & Toggle minor messages */
$(function() {
	//$(".hiddenDetail").hide().before('<a href="#" class="toggleLink">details...</a>');
	$(".hiddenDetail").each( function() {
		var lab = $(this).children("[name=toggleLabel]");
		if (lab.val()==undefined) {
			if ( $(this).hasClass("sqlCode") ) {
				var text = "Show SQL...";
			} else if ( $(this).hasClass("configFile") ) {
				var text = "Show Configuration...";
			} else {
				var text = "details...";
			}
		} else {
			var text = lab.val();
		}
		$(this).hide().before('<a href="#" class="toggleLink fancyLink">'+text+'</a>');
	});

	$(document).on( 'click', '.toggleLink', function(event) {
		event.preventDefault();
		$(this).next().toggle();
	});
	$(".hiddenDetailShow").show();
});

/* Toggle-able content.  This is like hiddenDetail, but without the annoying styling */
$(function() {
	$(".toggleContent").each( function() {
		var lab = $(this).children("[name=toggleLabel]");
		if (lab.val()==undefined) {
			var text = 'Show/Hide';
		} else {
			var text = lab.val();
		}
		$(this).hide().before('<a href="#" class="toggleLink fancyLink">'+text+'</a>');
	});
	$(".toggleContentShow").show();
});

/*
 * Log text, show as row in table
   Should be genericized to hiddenEngineText
   */

$(function() {
	$(".hiddenLogText").each( function() { 
		var row=$(this).closest('tr');
		var cols=$(row).children('td').length-3;
		$(row).after( '<tr><td></td><td></td><td class="revealedLogText" colspan="' + cols +'">'
			+ '<div class="revealedLogText">'
			+ $(this).html()
			+ '</div>'
			+ '</td></tr>');
		$(this).remove();
	});
});

$(function() {
	$(".listObjectLog").first().before('&nbsp;<a href="#" class="toggleLogText">Show/hide text in place</a>');
	$(".toggleLogText").click( function(event) {
		event.preventDefault();
		$("td.revealedLogText").toggle();
	});
});
$(function() {
	// move staff alerts to command bar
	//$("#engineStaffAlertForm").wrap("<td></td>").after($("#agencyTopLoginBox"));
	//$("#staffAlertContainer").hide();
//	$("#staffAlertContainer").wrap("<td></td>").insertAfter("#agencyTopLoginBox");
//	$("#agencyTopLoginBox").parent().after("test").wrap("<td></td>"));
});

$(function() {
/* To remove test warning, click on red spacer cells */
	$(document).on('click','.topNavSpacerTest', function() {
		$(".agencyTestWarningBox").remove();
		$(".topNavSpacerTest").addClass('topNavSpacer').removeClass('topNavSpacerTest');
	});
});

$(function() {
	/* Elastic text boxes */
	$(".engineTextarea").elastic();
});

$(function() {
/* Process autocomplete options */
	$(".autoComplete").each( function() {
		var opts=eval($(this).html());
		$(this).prev().autocomplete( { source: opts } );
	});

/* QuickSearch autocomplete */
	$("#QuickSearchText").autocomplete( {
		source: function( request, response ) {
			request.type = $("#QuickSearchType").val();
			lastXhr = $.getJSON( "live_search.php", request, function( data, status, xhr ) {
				if ( xhr === lastXhr ) {
					response( data );
				}
			});
		},
		minLength: $("#QuickSearchAutocompleteMinLength").val()
	});

/* TEST Client Form autocomplete */
	$(".enterClient").autocomplete( {
		source: function( request, response ) {
			//request.type = $("#QuickSearchType").val();
			request.type = 'client';
			lastXhr = $.getJSON( "live_search.php", request, function( data, status, xhr ) {
				if ( xhr === lastXhr ) {
					response( data );
				}
			});
		},
		//minLength: $("#QuickSearchAutocompleteMinLength").val()
		minLength: 2
	});

/* Test Entry Client trim autocomplete result down to ID */
	$("form.enterClient input:first").focus();
	$("form.enterClient").submit( function (e) {
		var id_repl = /^.* \(([0-9]+)\)$/;
		var search_val=$(this).find("input:first").val();
		var m;
		m = id_repl.exec(search_val);
		if ( m[1] ) {
			$(this).find("input:first").val( m[1] );
		}
	});

/* Trim autocomplete result down to ID */
	$("form.QuickSearchForm").submit( function (e) {
		var id_repl = /^.* \(([0-9]+)\)$/;
		var search_val=$("#QuickSearchText").val();
		var m;
		m = id_repl.exec(search_val);
		if ( m[1] ) {
			$("#QuickSearchText").val( m[1] );
		}
	});
/* Toggle Advanced Controls */
	$("#advancedControlButton").click( function(e) { e.preventDefault(); $(".advancedControl").toggle(); } );
});

$(function() {
/* Toggle visitor form on entry page */
	$("#enterVisitorLink").click( function (event) {
		event.preventDefault();
		$("#enterVisitorForm").toggle("slow");
	});
});

$( function() {
  // Webcam stuff here
	const myVideo = document.querySelector('video.videoStream');

	$('.photoDialogSelectorLink').click( function(e) {
		e.preventDefault();
		var title = $('.photoDialog').find('.photoDialogTitle').text();
		$('.photoDialog').dialog( {title: title, width: 'auto',height: 'auto', position: { my: "center top", at: "center top", of: window }}).show();
		navigator.mediaDevices.getUserMedia({video: true, audio: false}).then((stream) => {myVideo.srcObject = stream});
	});

	$(myVideo).click( function(e) {
		e.preventDefault();
		var canvas = document.querySelector('canvas.imageCapture');
		var context = canvas.getContext('2d');
		$(this).closest('.photoDialog').find('.imageSendContainer').show();
		canvas.width=myVideo.videoWidth;
		canvas.height=myVideo.videoHeight;
		context.drawImage(myVideo, 0, 0,myVideo.videoWidth,myVideo.videoHeight);
		console.log('W: ' + myVideo.videoWidth+', H: ' + myVideo.videoHeight);
	});

	$('canvas.imageCapture').click( function(e) {
		e.preventDefault();
		var canvas = document.querySelector('canvas.imageCapture');
		var form = $(this).closest('form');
		$(form).find('input[name=photo_data]').val(canvas.toDataURL('image/jpeg'));
		$(form).submit();
	});

	// Submit form when upload file selected
	$('.imageCaptureUpload').change( function() { 
		if ($(this).val() != "") {
			$(this).closest('form').submit();
		}
	});

	// Upload dialog is clunky and hidden.  A cleaner button triggers it.
	$('.imageCaptureUploadButton').click( function(e) {
		e.preventDefault();
		$(this).closest('div').find('.imageCaptureUpload').click();
	});
});


$( function() {
	$(".formWipeoutLink").click( function (e) {
		e.preventDefault();
		var varname = $(this).next().html();
		$('input[name=' + varname + ']').val('');
	});

/*
// FIXME: I think this can be removed
   $(document).on('click','.engineValueUnsetLink', function(e) {
		e.preventDefault();
		$(this).closest('.engineValueContainer').find('.engineValue').val(null);
		$(this).closest('.engineValueContainer').find('.engineValueLabel').remove();
		$(this).closest('.engineValueContainer').find('.objectPickerToForm').show();
		$(this).closest('.engineValueContainer').find('.objectPickerToggleLink').hide();	
		$(this).hide();
	});
*/
	// This replaces the doChallengeResponse function for password fields
	$(document).on('submit','form.doChallengeResponse',function() {
		$(this).find('input.passwordField').each( function() {
			$(this).val( MD5($(this).val()));
		} );
	});
});

/* Floating Headers */

// For multi-forms
$(function() {
    $('table.multiForm').floatThead();
});

/* Togglle blank rows on engine forms */
$(function() {

		if ( $('tr.engineValueRowBlank').length > 0 ) {
			function headerDisplay() {
				$('tr.engineRowBefore').each( function() {
					var last_r=$(this).nextUntil("tr.engineRowBefore","tr.engineRowAfter").last();
					var rows=$(this).nextUntil('tr.engineRowBefore').filter('.engineValueRow');
					if ( $(rows).filter(':visible').length == 0) {
						$(this).hide();
						$(last_r).hide();
					} else {
						$(this).show();
						$(last_r).show();
					}
				});
			}

			headerDisplay();
			$('a.engineRowBlankToggle').click( function(e) {
				e.preventDefault();
				$('tr.engineValueRowBlank').toggle();
				headerDisplay();
			});
		} else {
			$('a.engineRowBlankToggle').hide();	
		}
	
});

$(function() {
	function shadeItem( i ) {
		var threshhold = 128;
		var adjust = 12;
 		var color=$(i).css( "background-color" );
		var colors=color.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
		var red = (+colors[1]==0) ? 0 : ((+colors[1] < +threshhold) ? +colors[1] + +adjust : +colors[1] - +adjust);
		var green = (+colors[2]==0) ? 0 : ((+colors[2] < +threshhold) ? +colors[2] + +adjust : +colors[2] - +adjust);
		var blue = (+colors[3]==0) ? 0 : ((+colors[3] < +threshhold) ? +colors[3] + +adjust : +colors[3] - +adjust);
		if (+red + +green + +blue > 0) {
			$(i).css("background-color",'rgb('+red+','+green+','+blue+')');
		}
	}

	//$("table.engineForm  tr:visible:odd td ").each( function() {
	$("table.clientQuickLook  tr:visible:odd td ").each( function() {
		shadeItem($(this));
	});
	$("table.engineForm  tr:visible:odd td ").each( function() {
		shadeItem($(this));
	});
	$("#ClientSummary table.generalTable  tr:visible:odd td ").each( function() {
		shadeItem($(this));
	});
});

/* All, None, Invert for checkbox selections */
$(function() {
	var min_count = 4;
	// Create and add the controls
	var all = $('<a href="#"/>').html('All').addClass('checkboxSelectAll');
	var none = $('<a href="#"/>').html('None').addClass('checkboxSelectNone');
	var invert = $('<a href="#"/>').html('Invert').addClass('checkboxSelectInvert');
	var links = $('<span />').addClass('checkboxSelector').prepend( all,',',none,',',invert);
	$('span.checkBoxGroup:not(".skipSelectorControl")').each( function() {
		if ( $(this).find('span.checkBoxSet').length >= min_count ) {
			$(this).prepend( $(links).clone() );
		}
	});

	$('a.checkboxSelectAll').click(function( e ) {
		e.preventDefault();
		var checks=$(this).closest('.checkBoxGroup').find('input[type=checkbox]');
		var all_opt=$(checks).filter('.checkBoxAllOption');
		if ($(all_opt).length==1) {
			$(checks).prop('checked',false);
			$(all_opt).prop('checked',true);
		} else {
			$(checks).prop('checked',true);
		}
	});

	$('a.checkboxSelectNone').click(function( e ) {
		e.preventDefault();
		$(this).closest('.checkBoxGroup').find('input[type=checkbox]').prop('checked',false);
	});

	$('a.checkboxSelectInvert').click(function( e ) {
		e.preventDefault();
		var checks=$(this).closest('.checkBoxGroup').find('input[type=checkbox]');
		var all_opt=$(checks).filter('.checkBoxAllOption');
		var checks_not_all=$(checks).filter(':not(".checkBoxAllOption")');
	
		if ($(all_opt).length==1) {
			if ( (!$(all_opt).is(':checked')) && ($(checks_not_all).filter(':checked').length == 0) ) {
				$(all_opt).prop('checked',true);
			}  else {
				if ( ($(all_opt).is(':checked')) && ($(checks_not_all).filter(':checked').length == 0) ) {
					$(all_opt).prop('checked',false);
				} else {
					$(all_opt).prop('checked',false);
					$(checks_not_all).each( function() {
						$(this).prop('checked', !$(this).prop('checked'));
					});
				}
			}
		} else {
			$(checks).each( function() {
				$(this).prop('checked', !$(this).prop('checked'));
			});
		}
	});

	$('span.checkBoxGroup input[type=checkbox]').click( function() {
	// This is for if actual boxes (not the selector controls) get clicked
		var group = $(this).closest('.checkBoxGroup');
		var all = $(group).find('.checkBoxAllOption');
		if ( $(all).length==1) {
			var checks_all = $(group).find('input[type=checkbox]:not(".checkBoxAllOption")');
			var checks_checked = $(checks_all).filter(':checked');
			if ( (this==$(all).get(0)) ) {
			// If an "all" checkbox gets checked, uncheck everything else
				if ($(this).is(":checked") ) {
					$(checks_checked).prop('checked',false);
				}
			} else if ( ($(checks_all).length == $(checks_checked).length) ) {
			// If all boxes are checked, uncheck them, and check all option
				$(all).prop('checked',true);
				$(checks_all).prop('checked',false);
			} else if ($(all).is(":checked")) {
				// If not all checked, uncheck the all option
				$(all).prop('checked',false);
			}
		}
	});


});

$(function() {
	// "Pick myself" option for staff dropdowns
	var pick_link = $('<a href="#">Pick Myself<a/>').addClass('pickMyselfLink');
	$('span.pickStaffList').append( $(pick_link) );
	$('a.pickMyselfLink').click( function(e) {
		e.preventDefault();
		var plist=$(this).closest('span.pickStaffList');
		var myid=$(plist).find('span.serverData').html();
console.log("ID: " + myid);
		if ($(plist).find('option[value='+myid+']').length==1) {
			$(plist).find('select').val(myid);
		} else {
			$(this).remove();
		}
	});
});
	
