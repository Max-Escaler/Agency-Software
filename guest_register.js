function unitValidate() {
	return (jQuery.inArray($('div.guestLoginUnit input').val(),unitValidate.validUnits) > -1);
}

function secretValidate() {
	var secret_regex = /^([0-9]{4})$/; // 4 year DOB
	var secret_val = $('div.guestLoginSecret input').val();
	return (secret_val !== undefined) && secret_val.match(secret_regex);
}


function inputsValidate() {
	var unit = $('div.guestLoginUnit');
	var secret = $('div.guestLoginSecret');

	if (! ((unit.length==1) && secret.length==1)) {
		// Stop if we're on the wrong page
		return true;
	}

	var goButton = $('div.guestLoginGo');
	//var secred_regex=/([0-9]{1,2}[\-\/]?){2}[0-9]{2,4}/; // for a full dob
	var secret_regex = /^([0-9]{4})$/; // 4 year DOB

	var our_form = $(goButton).closest('form');

	var unit_val = $(unit).find('input').val();
	var secret_val = $(secret).find('input').val();

	var check_unit = unitValidate();
	var check_secret = secretValidate();
	if ( check_unit && check_secret) {
		//$(unit).find('input').removeAttr('disabled');
		$('div.guestHeaderBlock').hide();
		$('div.guestResponseMessage').hide();
		$('.guestLogoutButton').hide();
//		$('h1').next().html('Accessing...').next().show();
		$('h1').next().html('').next().show();
//		$(secret).hide().
		$(our_form).submit();
		return true;
	} else if (check_unit) {
		$(secret).show();
/*
		var keyboard=$(unit).find('input').keyboard().getkeyboard();
		if (keyboard) {	
			keyboard.destroy();
		}
*/
//$(unit).find('input').attr('disabled','disabled');
		//$(secret).show().find('input').focus().val( $(secret).show().find('input').val() );
//		if ($(secret).find('input:focus').length==0) {
		if (jQuery.inArray($(unit).find('input').val(), inputsValidate.validUnits) > -1) {
			$(secret).find('input').focus().caretToEnd();
		}
		return false;
	} 
}

$( function() {

	inputsValidate.validUnits= jQuery.parseJSON( $( '#housingUnitCodes' ).html() );
	unitValidate.validUnits= jQuery.parseJSON( $( '#housingUnitCodes' ).html() );
	var loading = $('<img></img>').attr('src','images/loading-big.gif').hide();
	$('h1').next().after( $( loading ) );
//.before($('div.guestResponseMessage'));
  $('div.guestLoginUnit input').blur( function() {
		if ( (!unitValidate()) && (!($('div.guestLoginSecret input:visible').length>0))) {
			$(this).focus();
		}
	});

  $('div.guestLoginUnit input').focus( function() {
		if (secretValidate() && unitValidate()) {
			$('div.guestLoginGo').closest('form').submit();
			return false;
		}
		if (unitValidate()) {
				$(this).val('');
		}
	});

/*	$('.calTodayLink').remove();
	var params = {
		alwaysOpen: false,
		stayOpen: false,
		layout: 'custom', 
		customLayout: { 'default': ['7 8 9', '4 5 6', '1 2 3', '{b} 0 /'] } ,
		usePreview: false,
		autoAccept: true,
		position: { at2: 'right center', my: 'left center' }
	};
*/
// Remove the comment block for an onscreen keyboard
/*
	$('div.guestLoginSecret input').keyboard( params );
	$('div.guestLoginUnit input').keyboard( params );
*/
	$('div.guestLoginUnit input').focus();
	var watch = setInterval( function() {
		if (inputsValidate()) {
			clearInterval(watch);
		} 
		} ,200);
});

