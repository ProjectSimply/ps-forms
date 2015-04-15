(function ( $ ) {
	"use strict";

	$(function () {

		$('#form_name_select').change(function(e){

			var query = window.location.search, form_name_param = "form_name";

			//if there is a query string, append it, otherwise construct the query string.
			query = addParameter(query,form_name_param,encodeURIComponent($(this).val()));
			//Make sure we're not on an errant page when changing form. Go back to page 1
			query = addParameter(query,'paged',1)

			window.location.search = query; // page should reload

		});

		$('body').on('click','.ps_forms_add_rule',function(e){
			
			e.preventDefault();

			var row 		= $(this).parent().parent();
			var new_row 	= row.clone();
			var row_count	= $('.rule_row').length;

			new_row.attr('data-id','rule_'+row_count);
			$('.name input',new_row).attr('name','name[' + row_count + ']');
			$('input',new_row).val('');
			$('.param select',new_row).attr('name','validation_rule[' + row_count + ']');

			row.after(new_row);

			return false;
		});

		$('body').on('click','.ps_forms_remove_rule',function(e){
			
			e.preventDefault();

			var row 		= $(this).parent().parent();
			var row_count	= $('.rule_row').length;

			if(row_count>1)
				row.remove();
			else
				$('input',row).val('');

			return false;
		});

		var new_form = false;
		var temp_row = '';
		var temp_data = '';

		$('body').on('keyup keypress blur change focus','#new_form',function(){

			if($(this).val() != '') {
				if(new_form == false) {
					new_form=true;
					temp_row = $('.rule_row').not( document.getElementById( "rule_0" ));
					temp_data = {
						name : $('#rule_0 .name input').val(),
						rule : $('#rule_0 .select').val()
					}
					$('#rule_0 .name input').val('');
					$('#rule_0 .select').val('required');
					$('.rule_row').not( document.getElementById( "rule_0" )).remove();
				}
			}
			else {
				if(new_form == true) {
					new_form = false;
					$('#rule_0').after(temp_row);
					$('#rule_0 .name input').val(temp_data.name);
					$('#rule_0 .select').val(temp_data.rule);
				}
			}

		});

		

	});

	function addParameter(url, param, value) {
	    // Using a positive lookahead (?=\=) to find the
	    // given parameter, preceded by a ? or &, and followed
	    // by a = with a value after than (using a non-greedy selector)
	    // and then followed by a & or the end of the string
	    var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))'),
	        parts = url.toString().split('#'),
	        url = parts[0],
	        hash = parts[1],
	        qstring = /\?.+$/,
	        newURL = url;

	    // Check if the parameter exists
	    if (val.test(url))
	    {
	        // if it does, replace it, using the captured group
	        // to determine & or ? at the beginning
	        newURL = url.replace(val, '$1' + param + '=' + value);
	    }
	    else if (qstring.test(url))
	    {
	        // otherwise, if there is a query string at all
	        // add the param to the end of it
	        newURL = url + '&' + param + '=' + value;
	    }
	    else
	    {
	        // if there's no query string, add one
	        newURL = url + '?' + param + '=' + value;
	    }

	    if (hash)
	    {
	        newURL += '#' + hash;
	    }

	    return newURL;
	}

	$(document).ready(function($){
 
 
	    var custom_uploader;
	 
	 
	    $('#upload_image_button').click(function(e) {
	 
	        e.preventDefault();
	 
	        //If the uploader object has already been created, reopen the dialog
	        if (custom_uploader) {
	            custom_uploader.open();
	            return;
	        }
	 
	        //Extend the wp.media object
	        custom_uploader = wp.media.frames.file_frame = wp.media({
	            title: 'Choose Logo Image',
	            button: {
	                text: 'Choose Image'
	            },
	            multiple: false
	        });
	 
	        //When a file is selected, grab the URL and set it as the text field's value
	        custom_uploader.on('select', function() {
	            var attachment = custom_uploader.state().get('selection').first().toJSON();
	            $('#upload_image').val(attachment.url);
	            $('#upload_image_id').val(attachment.id);
	        });
	 
	        //Open the uploader dialog
	        custom_uploader.open();
	 
	    });
	 
	 
	});


}(jQuery));

