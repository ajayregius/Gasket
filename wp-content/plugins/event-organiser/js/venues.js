jQuery(document).ready(function() {

	//Loads country selector
	jQuery('#country-selector').selectToAutocomplete({
	        'sort': true,
	      });
	
	if(typeof google !=="undefined"){
		//Retrieve Lat-Lng values from form and load map as page loads.
		var eo_venue_Lat = jQuery("#eo_venue_Lat").val();
		var eo_venue_Lng = jQuery("#eo_venue_Lng").val();;
		var map;
		var marker;
		initialize(eo_venue_Lat,eo_venue_Lng);

		//Every time form looses focus, use input to display map of address
		jQuery(".eo_addressInput").blur(function(){
			address="";
			jQuery(".eo_addressInput").each(function(){
				if(jQuery(this).attr('id')!='country-selector'){
					address = address+" "+jQuery(this).val();
				}
			})
		codeAddress(address);
		});
	}
});

/**
 * Function that puts a marker on the Google Map at the latitue - longtitude co-ordinates (Lat, Lng)
 * @since 1.0.0
 */
function initialize(Lat,Lng) {
	if(typeof google !=="undefined"){
		var latlng = new google.maps.LatLng(Lat,Lng);
		var myOptions = {
			zoom: 15,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("venuemap"),myOptions);
		marker = new google.maps.Marker({
			position: latlng, 
			map: map
		});
	}
	}
	

/**
 * Converts string address into Latitude - Longtitude co-ordinates and then 
 * adds these as the value of two (hidden) input forms.
 * @since 1.0.0
 */	
	function codeAddress(addrStr) {
		var geocoder;
		geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': addrStr}, function(results, status) {
      		if (status == google.maps.GeocoderStatus.OK) {
			map.setCenter(results[0].geometry.location);
			marker.setMap(null);
			marker = new google.maps.Marker({
				map: map,
				position: results[0].geometry.location
			});
		jQuery("#eo_venue_Lat").val(results[0].geometry.location.lat());
		jQuery("#eo_venue_Lng").val(results[0].geometry.location.lng());;
      		} 
	});
	}


/*
* jQuery plugin which improves the country-select interface.
* This plug-in was not made by me. See copyright info below.
*/
/*
Version: 1.0.1

Documentation: http://baymard.com/labs/country-selector#documentation

Copyright (C) 2011 by Jamie Appleseed, Baymard Institute (baymard.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
(function($){
  var settings = {
    'sort': false,
    'sort-attr': 'data-priority',
    'sort-desc': false,
    'alternative-spellings': true,
    'alternative-spellings-attr': 'data-alternative-spellings',
    'remove-valueless-options': true,
    'copy-attributes-to-text-field': true,
    'autocomplete-plugin': 'jquery_ui',
    handle_invalid_input: function( context ) {
      context.$text_field.val( '' );
    },
    handle_select_field: function( $select_field ) {
      return $select_field.hide();
    },
    insert_text_field: function( $select_field ) {
      var $text_field = $( "<input></input>" );
      if ( settings['copy-attributes-to-text-field'] ) {
        var attrs = {};
        var raw_attrs = $select_field[0].attributes;
        for (var i=0; i < raw_attrs.length; i++) {
          var key = raw_attrs[i].nodeName;
          var value = raw_attrs[i].nodeValue;
          if ( key !== 'name' && key !== 'id' ) {
            attrs[key] = raw_attrs[i].nodeValue;
          }
        };
        return $text_field.attr( attrs )
          .val( $select_field.find('option:selected:first').text() )
          .insertAfter( $select_field );
      }
    },
    extract_options: function( $select_field ) {
      var options = [];
      var $options = $select_field.find('option');
      var number_of_options = $options.length;
      var original_number_of_options = number_of_options;
      
      // go over each option in the select tag
      $options.each(function(){
        var $option = $(this);
        var option = {
          'real-value': $option.attr('value'),
          'label': $option.text()
        }
        if ( settings['remove-valueless-options'] && option['real-value'] === '') {
          // remove options without a value
          number_of_options--;
        } else {
          // prepare the 'matches' string which must be filtered on later
          option['matches'] = option['label'];
          var alternative_spellings = $option.attr( settings['alternative-spellings-attr'] );
          if ( alternative_spellings ) {
            option['matches'] += ' ' + alternative_spellings;
          }
          // give each option a weight paramter for sorting
          if ( settings['sort'] ) {
            var weight = parseInt( $option.attr( settings['sort-attr'] ), 10 );
            if ( weight ) {
              option['weight'] = weight;
            } else {
              option['weight'] = original_number_of_options;
            }
          }
          // add option to combined array
          options.push( option );
        }
      });
      // sort the options based on weight
      if ( settings['sort'] ) {
        if ( settings['sort-desc'] ) {
          options.sort( function( a, b ) { return b['weight'] - a['weight']; } );
        } else {
          options.sort( function( a, b ) { return a['weight'] - b['weight']; } );
        }
      }
      
      // return the set of options, each with the following attributes: real-value, label, matches, weight (optional)
      return options;
    }
  };
  
  var public_methods = {
    init: function( customizations ) {
      settings = $.extend( settings, customizations )
	selected = jQuery(this).children( ":selected" ),
	value = selected.val() ? selected.text() : "";
	
      return this.each(function(){
        var $select_field = $(this);
        
        var options = settings['extract_options']( $select_field );
        var $text_field = settings['insert_text_field']( $select_field );
        settings['handle_select_field']( $select_field );
        
        var context = {
          '$select_field': $select_field,
          '$text_field': $text_field,
          'options': options,
          'settings': settings
        };
        if ( typeof settings['autocomplete-plugin'] === 'string' ) {
          adapters[settings['autocomplete-plugin']]( context );
        } else {
          settings['autocomplete-plugin']( context );
        }
      });
    }
  };
  
  var adapters = {
    jquery_ui: function( context ) {
      // loose matching of search terms
      var filter_options = function( term ) {
        var split_term = term.split(' ');
        var matchers = [];
        for (var i=0; i < split_term.length; i++) {
				  if ( split_term[i].length > 0 ) {
				    matchers.push( new RegExp( $.ui.autocomplete.escapeRegex( split_term[i] ), "i" ) );
				  }
				};
				
				return $.grep( context.options, function( option ) {
  				var partial_matches = 0;
  				for ( var i=0; i < matchers.length; i++ ) {
  				  if ( matchers[i].test( option.matches ) ) {
  				    partial_matches++;
  				  }
  				};
  				return (!term || matchers.length === partial_matches );
				});
      }
      // update the select field value using either selected option or current input in the text field
      var update_select_value = function( option ) {
        if ( option ) {
          context.$select_field.val( option['real-value'] );
        } else {
          var option_name = context.$text_field.val().toLowerCase();
          var matching_option = { 'real-value': false };
          for (var i=0; i < context.options.length; i++) {
            if ( option_name === context.options[i]['label'].toLowerCase() ) {
              matching_option = context.options[i];
              break;
            }
          };
          context.$select_field.val( matching_option['real-value'] || '' );
          if ( matching_option['real-value'] ) {
            context.$text_field.val( matching_option['label'] );
          }
  		    if ( typeof context.settings['handle_invalid_input'] === 'function' && context.$select_field.val() === '' ) {
  		      context.settings['handle_invalid_input']( context );
  		    }
        }
      }
      // jQuery UI autocomplete settings & behavior
      context.$text_field.autocomplete({
        'minLength': 0,
        'delay': 0,
        source: function( request, response ) {
          response( filter_options( request.term ) );
        },
       select: function( event, ui ) {
          update_select_value( ui.item );
				},
				change: function( event, ui ) {
				  update_select_value( ui.item );
				}
      });
      // force refresh value of select field when form is submitted
      context.$text_field.parents('form:first').submit(function(){
        update_select_value();
      });
      // select current value
      update_select_value();
    }
  };

  $.fn.selectToAutocomplete = function( method ) {
    if ( public_methods[method] ) {
      return public_methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return public_methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.fn.selectToAutocomplete' );
    }    
  };
  
})(jQuery);
