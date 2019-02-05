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

//FIXME:  This code could be nicer and cleaner

/* Client Selector */
function qs_object( searchText, obj, MyClients ) {
		var curPage = window.location.pathname;
		var args = { QuickSearch: searchText, QuickSearchObject: obj, MyClients: MyClients, select_to_url: curPage };
		return args;
};	

/* helper functions for object selector, get max & currently selected */

function get_max_select( el ) {
	return $(el).closest('.engineValueContainer').find('input[name=objectPickerMaxSelect]').val();
}

function get_cur_select( el ) {
	return $(el).closest('.engineValueContainer').find('.engineValueGrouping').length;
}

/* Object Picker */
function addSelectedObject( object ) {
	// Should have id, label, obj, canRemove, refType

	// Check if exists
	var dupe=false;
	$(".selectedObject").each( function() {
		data=$(this).data('selectedObject');
		if ( (object.object==data.object) && (object.id==data.id) && (data.refType!='removed') ) {
			dupe = true;
		}
	});
	if (dupe) {
		return false;
	}
	/* FIXME: this is an ugly hack, and also won't work for donor or other flavors */
 	if (object.object=='client') {
		var tmp_base = '<a href=client_display.php?id=' + object.id +' class=' + object.object + 'Link>';
	} else {
		var tmp_base = '<a href=display.php?control[action]=view&control[object]=' + object.object + '&control[id]=';
	}
	// Here's another hack, to handle label passed as link or plain text	
	var tmp_lab=$("object.label").text();
	if (tmp_lab=='') {
		tmp_lab=object.label;
	}
	var lab = tmp_base + tmp_lab + '</a>';
	var val_remove = object.canRemove ? ' <a href=# class=selectedObjectRemove>(remove)</a>' : '';
	var text = $('<span class="selectedObject"><li>' + lab + val_remove + "</li></span>");
	text.data('selectedObject',object);
	if (object.object=='info_additional') {
		$("#infoAdditionalContainer").show().append(text).data("selectedObject",object);
	} else {
		var tmpRefType=object.refType.charAt(0).toUpperCase() + object.refType.slice(1);
		$("#objectReferenceContainerReference" + tmpRefType).show().append(text).data("selectedObject",object);
		$("#objectReferenceContainer").show();
	}
}

// lifted from http://stackoverflow.com/questions/6524288/jquery-event-for-user-pressing-enter-in-a-textbox
(function($) {
    $.fn.onEnter = function(func) {
        this.bind('keypress', function(e) {
            if (e.keyCode == 13) func.apply(this, [e]);    
        });               
        return this; 
     };
})(jQuery);

//Trigger search on enter in Object Picker fields
$(function() {
	$('input[name=objectPickerSearchText]').onEnter( function( e ) {
		e.preventDefault();
		$(this).closest('div').find('input.objectPickerSubmit').last().click();
	} );
});

$(function() {
	$(document).on('click','.objectPickerSubmit', function(event) {
		event.preventDefault();
		// Find method on page
		var method =$(event.target).closest('div').find("[name=objectPickerMethod]").val();
		if (!method) {
			// Or, coming from a search row result
			method = $(event.target.row).find("[name=objectPickerMethod]").val();
		}
		if ( method=='Search') {
			var parentDiv = $(event.target).closest('div').parent().closest('div');
			if (parentDiv.hasClass('objectPickerToForm')) {
				var target = $(this).closest('.engineValueContainer').find('input.engineValue');
				//var target = $(this).closest('.engineValueContainer');
				if (target.id) {
					var id = target.id;
				} else {
					var base = 'RandomDivId';
					var x=0;
					while ($('#'+(base + ++x)).length>0) {}
					var id = base + x;
					$(target).attr('id',id);
				}
			} else {
				var id=null;
			}
		}
		if ( method=='SearchResult') {
			var id = $(this).find('span.serverData').html();
		}

		var selected = process_selector_event( event,method,id );
		if (selected !== undefined) {
			if (selected.target) {
				var dupe=false;
				$('#'+selected.target).closest('.engineValueContainer').find('.engineValueGrouping input').each( function() {
					if (selected.id==$(this).val()) {
						dupe=true;
					}
				});
				if (dupe) {
					return;
				}
				var max_select=get_max_select($('#'+selected.target));
				var cur_select=get_cur_select($('#'+selected.target));
				var close_button=$('<img></img>').attr('src','images/close_button.png').attr('title','Click to Remove').addClass('selectorRemoveItem');
				var new_label=$('<p/>').addClass('engineValueLabel').html(selected.label).append(close_button); 
				var new_value=$('#'+selected.target).clone().val( selected.id ).removeClass('engineValueTemplate');
				var new_group= $('<span></span').addClass('engineValueGrouping').append(new_value).append(close_button).append(new_label);
				if (cur_select<max_select) {
					$('#'+selected.target).after( new_group );
				}
				if (cur_select+1>=max_select) {
					$('#'+selected.target).closest('.engineValueContainer').find('.objectPickerToForm').hide();
					$(this).closest('div.ajObjectSearchResult').dialog('close').hide();
					
				}					
				$('#'+selected.target).change();
			} else {
				addSelectedObject( selected );
			}
		}
	});
});

function process_selector_event( event,method,target_el ) {
	switch (method) {
		case 'Pick':
			var obj_name = $(event.target).closest('div').find("[name=objectPickerObject]").val();
			var obj_id = $(event.target).closest('div').find("[name=objectPickerPickList]").val();
			var obj_text = $(event.target).closest('div').find("[name=objectPickerPickList] :selected").text();
			//addSelectedObject( { id: obj_id, label: obj_text, object: obj_name, canRemove: true, refType: 'to' });
			return { id: obj_id, label: obj_text, object: obj_name, canRemove: true, refType: 'pending', target: target_el };
			break;
		case 'SearchResult':
			var obj_name = $(event.target).closest('tr').find("[name=objectPickerObject]").val();
			var obj_id = $(event.target).closest('tr').find("[name=objectPickerId]").val();
			var obj_text = $(event.target).closest('tr').find("[name=objectPickerLabel]").val();
			//addSelectedObject( { id: obj_id, label: obj_text, object: obj_name, canRemove: true, refType: 'to' });
			return { id: obj_id, label: obj_text, object: obj_name, canRemove: true, refType: 'pending', target: target_el };
			break;
		case 'Search':
			var search_text = $(event.target).closest('div').find("[name=objectPickerSearchText]").val();
			var obj = $(event.target).closest('div').find("[name=objectPickerObject]").val();
			var my = $(event.target).closest('div').find("[name=objectPickerMyClients]").prop('checked');
			var args = qs_object(search_text,obj,my);
			var spinner = $('<img/>').attr('src','images/loading.gif');
			$.ajax({
				method: "get",
				url: "ajax_selector.php",
				data: args,
				beforeSend: function(){$(event.target).after($(spinner));},
				complete: function(){ $(spinner).remove();}, 
				success: function(html){
					$("#aj_client_selector_my_clients").val(false);
					$(".ajObjectSearchResult").html(html).dialog( { position: { my: "center top", at: "center top" }, modal: true, title: 'Select a ' + obj, width: 'auto' } ); 
					//var button = '<td><button type="button" class="objectPickerSubmit">SELECT</button></td>';
					var button = '<td><button type="button" class="objectPickerSubmit">SELECT'
							+ (target_el ? '<span class="serverData">'+target_el+'</span>' : '')
							+ '</button></td>';
					$(".ajObjectSearchResult tr.generalData2,tr.generalData1").each( function(i) {
						$(this).children("td:eq(1)").html($(button));
					});
					$(".ajObjectSearchResult").show(); 
					var length=$(".ajObjectSearchResult tr.generalData2,tr.generalData1").length;
					if (length==1) { // FIXME: and autoselect_on_1
						$('.ajObjectSearchResult button').click();
						$('.ajObjectSearchResult').dialog('close').hide();
					}
					if (length==0) {
						$('.ajObjectSearchResult').dialog('close').hide();
					}
					var tab=$(".ajObjectSearchResult table"); // FIXME ???? orphaned ??
					}
			});
	}
};

/*
 * Object Reference Container & Additional Information
 */

$(function() {
	$("#objectReferenceContainer").draggable();


	$("#infoAdditionalContainer").draggable();
	var closeButton = '<a class="fancyLink objectSelectorHideLink">close</a>';
	$(".objectSelectorForm").tabs().draggable().hide().append(closeButton);
	addPreSelectedObjects();
	$(".objectSelectorShowLink").click( function(event) {
		event.preventDefault();
		$(".objectSelectorForm").show(); // FIXME: Which one?
		$(this).hide();
	} );
	$(".objectSelectorHideLink").click( function(event) {
		event.preventDefault();
		$(".objectSelectorForm").hide(); // FIXME: which one?
		$(".ajObjectSearchResult").hide();
		$(".objectSelectorShowLink").show(); // FIXME: which one?
	});
	$(document).on('click','.selectedObjectRemove', function(event) {
		event.preventDefault();
		data=$(this).closest('span').data('selectedObject');
		if ( ($(this).closest('div').find('span')).length==1) {
			$(this).closest('div').hide();
		}
		if (data.canRemove) {
			//$(this).closest('span').remove();
			data.refType='removed';
			$(this).closest('span').data('selectedObject',data);
			$("#objectReferenceRemovedContainer").append( $(this).closest('span') );
			if ($("#objectReferenceContainer span").length==0) {
				$("#objectReferenceContainer").hide();
			}
		}
	});


	/* On submit, add references to form */
	/* Also, remove all template values */
	$("form").submit( function(event) {
		var form=this;
		$(".selectedObject").each( function() {
			var data='<input type="hidden" name="selectedObject[]" value = "'+encodeURIComponent(JSON.stringify($(this).data('selectedObject')))+'">';
		$(form).append(data);
		});
		$('.engineValueTemplate').remove();
	});

	/* Remove selector items on close button */
	$(document).on('click','.selectorRemoveItem', function(e) {
		e.preventDefault();
		var max_select=get_max_select($(this));
		var cur_select=get_cur_select($(this));
		if (cur_select-1<max_select) {
			$(this).closest('.engineValueContainer').find('.objectPickerToForm').show();
		}
		$(this).closest('.engineValueGrouping').remove();
	});

});

/* Add pre-selected objects from server to form */
function addPreSelectedObjects() {
	$("#preSelectedObjects > div").each( function() {
		addSelectedObject( jQuery.parseJSON( $(this).html()));
	});
};


