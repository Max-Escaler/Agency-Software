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

function HelpWin( title, text )
{
	helpwindow=open("","","width=300,height=250,resizable=yes,scrollbars=yes");
	helpwindow.document.write( "<h1>" + title + "</h1>" + text );
	helpwindow.document.close();
}

function getOffset(el,t) {
	var o=0;
	while(el) {
		 o += el[t];
		 el = el.offsetParent
	}
	return o;
}

function showHideElement(id) { 
	var vis = document.getElementById(id).style.display;
	
	if (vis=="none") {
		showElement(id);
	} else {
		hideElement(id);
	}
}

function showElement(id) {
	var element;
	if (element = document.getElementById(id)) {
		element.style.display = "";
	} else {
		return false;
	}

	showElement(id+"_hide");
	hideElement(id+"_show");
}

function hideElement(id) {
	var element;

	if (element = document.getElementById(id)) {
		element.style.display = "none";
	} else {
		return false;
	}

	hideElement(id+"_hide");
	showElement(id+"_show");

}

function multiShow() {
	for (var i=0; i<arguments.length; i++) {
		showElement(arguments[i]);
	}
}

function multiHide() {
	for (var i=0; i<arguments.length; i++) {
		hideElement(arguments[i]);
	}
}

function toggleDisplayId(tid,displayOpt) {
	var el = document.getElementById(tid);
	if (el.style.display !== "none") {
		el.style.display="none";
	} else {
		el.style.display=displayOpt;
	}
}

/* quick search */
function switchQuickSearch(which) {
	whichQuickSearch=which;
      setQuickSearch();
	document.getElementById('QuickSearchText').focus(); 
}

function setQuickSearch() {
	for (var i=0; i < QuickSearches.length; i++) {
		var el=document.getElementById('QuickSearch'+QuickSearches[i]);
		if (QuickSearches[i]==whichQuickSearch) {
			el.className='QuickSearchTab';
		} else {
			el.className='QuickSearchTabInactive';
		}
	}
	document.getElementById('QuickSearchBox').className=whichQuickSearch.toLowerCase();
      document.getElementById('QuickSearchType').setAttribute('value',whichQuickSearch.toLowerCase());
}

function getDaysInMonth(month,year)
{
	var daysInMonths = new Array (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var days = daysInMonths[month];
	//calculate leap year
	if ( (month==1) && (year%4==0) && (!(year%100==0) || (year%400==0)) ) {
		days = 29;
	}
	return days;
}

function populateSelect( fFrom, fPop, intStart, fFromStart ) {
		var a = arrPop;
		var b, c, d, intItem, intType;

	// intStart=Starting value of field being populated
	// fFromStart= Starting value of field doing the populating
	// fFrom=object doing the populating
	// fPop=object being populated
	//
	// arrPop is an array (populated by server) with:
	// 0 - grouping (matches with fFrom value
	// 1 - select value
	// 2 - select label
	//
	// FIXME: this could all be simplified and cleaned up tremendously
	//        that could probably be said of everything in this file!

	// Note this function was hastily hacked to work with non-select lists
	// (e.g., for selectors).  It appears to work, but may have problems
	if ( (fFrom===undefined) || (fPop===undefined) ) {
		return;
	}
	if ( fFrom.type=='select') {
		if ( intStart !== "" ) {
			for ( b = 0; b < a.length; b++ ) {
				if ( a[b][1] == intStart ) {
					intType = a[b][0];
				}
			}
			for ( c = 0; c < fFrom.length; c++ ) {
				if ( fFrom.options[ c ].value == intType ) {
					fFrom.selectedIndex = c;
				}
			}
		}
 		 if ( intType == null ) {
			intType = fFrom.options[ fFrom.selectedIndex ].value;
		}

	} else {
		intType =  $(fFrom).val();
	}

	if ( (fFromStart !== null) && (fFromStart !== undefined) ) { //a method to start with a blank list
		intType=fFromStart;
	}

	var opt;
	$(fPop).find('option').remove();
	for ( d = 0; d < a.length; d++ ) {
		if ( a[d][0] == intType ) {
			if ( $(fPop).find('option').filter("[value='"+a[d][1]+"']").length == 0 ) {
				opt = $("<option></option>").html(a[d][2]).attr("value",a[d][1]).attr('selected',( a[d][1] == intStart && intStart !== ""));
				$(fPop).append( opt );
			}
		}
	}
}
