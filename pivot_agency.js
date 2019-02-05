$( function() {
	var derivers = $.pivotUtilities.derivers;
	var renderers = $.extend($.pivotUtilities.renderers,
	 	$.pivotUtilities.c3_renderers);
	var renderers = $.extend($.pivotUtilities.renderers,
	 	$.pivotUtilities.d3_renderers);
        var dataClass = $.pivotUtilities.SubtotalPivotData;
        //var renderers = $.pivotUtilities.subtotal_renderers;
        var renderers = $.extend($.pivotUtilities.renderers,$.pivotUtilities.subtotal_renderers);
//console.log( $.pivotUtilities.c3_renderers );
	$('.pivotTable').each( function() {
		
		var data = JSON.parse( $(this).next('.pivotTableData').text() );

		data.options.renderers=renderers;
		data.options.dataClass=dataClass,
		data.options.rendererName="Table With Subtotal";
//console.log(data);
		$( this ).pivotUI(
			data.data,
			data.options
		);
	});
});
