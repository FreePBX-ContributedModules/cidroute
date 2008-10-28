// FreePBX CIDroute selection script.
// Based on jQuery comboselect plugin
// by Jason Huck
// http://devblog.jasonhuck.com/
// CC-By-SA
//

(function($){
	jQuery.fn.comboselect = function(settings){
		settings = jQuery.extend({
			addbtn: ' &gt; ',	// text of the "add" button
			rembtn: ' &lt; '	// text of the "remove" button
		}, settings);
	
		this.each(function(){
			// the id of the original element
			var selectID = this.id;
			
			// ids for the left and right sides
			// of the combo box we're creating
			var leftID = selectID + '_left';
			var rightID = selectID + '_right';
			
			// the form which contains the original element
			var theForm = $(this).parents('form');
			
			// place to store markup for the combo box
			var combo = '';
			
			// copy of the options from the original element
			// var opts = $(this).children().clone();
			var opts = $(this).find('option').clone();
			
			// add an ID to each option for the sorting plugin
			opts.each(function(){
				$(this).attr('id', $(this).attr('value'));
			});
			
			// build the combo box
			combo += '<fieldset class="comboselect">';
			combo += '<select id="' + leftID + '" name="' + leftID + '" class="csleft" multiple="multiple" style="width:45%">';
			combo += '</select>';
			combo += '<fieldset>';
			combo += '<input type="button" class="csadd" value="' + settings.addbtn + '" />';
			combo += '<input type="button" class="csremove" value="' + settings.rembtn + '" />';
			combo += '</fieldset>';
			combo += '<select id="' + rightID + '" name="' + rightID + '[]" class="csright" multiple="multiple" style="width:45%">';
			combo += '</select>';
			combo += '</fieldset>';		
		
			// hide the original element and 
			// add the combo box after it
			$(this).hide().after(combo);			

			// find the combo box in the DOM and append
			// a copy of the options from the original
			// element to the left side
			theForm.find('#' + leftID).append(opts);
			
			// bind a submit event to the enclosing form
			theForm.submit(function(){
				// clear the original form element of selected options
				$('#' + selectID).find('option:selected').removeAttr('selected');	
				return true;
			});			
		});

		// double-click moves an item to the other list
		$('select.csleft').dblclick(function(){
			$(this).parent().find('fieldset input.csadd').click();
		});
		
		$('select.csright').dblclick(function(){
			$(this).parent().find('fieldset input.csremove').click();
		});

		// add/remove buttons
		$('input.csadd').click(function(){
			var left = $(this).parent().parent().find('select.csleft');
			var leftOpts = $(this).parent().parent().find('select.csleft option:selected');
			var right = $(this).parent().parent().find('select.csright');
			right.append(leftOpts);
		});
	
		$('input.csremove').click(function(){
			var left = $(this).parent().parent().find('select.csleft');
			var right = $(this).parent().parent().find('select.csright');
			var rightOpts = $(this).parent().parent().find('select.csright option:selected');
			left.append(rightOpts);
		});			

		// add any items that were already selected
		$('input.csadd').click();
	
		return this;
	};	
})(jQuery);
