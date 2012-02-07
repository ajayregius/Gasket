/* jquery is in noconflict mode in wordpress because of prototype
 * so bind to the DOM ready event with jQuery() instead of $() and pass the $ alias to jQuery() in the anonymous function 
 * 
 * the DOM ready event allows us to execute code when the Document Object Model has been loaded in the browser
 * this is earlier than the load() event which is triggered when all elements (including images) have loaded */
jQuery(document).ready(function($){
	/* check if AllCatsChkBox is checked when page loads and check other checkboxes 
	 * being careful to only check them when it is on. Not when it is off.  */
	if($('#AllCatsChkBox').is(':checked')) {
		$("input[name='category[]']").attr({
			checked : true, 
			disabled : true 
		}); 
	}
	
	/*bind to onclick event of element with id of AllCatsChkBox*/
	$('#AllCatsChkBox').click(function(){
		/* get all inputs on the page with the name 'category[]' 
		 * change the attribute 'checked' to 
		 * boolean result ( is() ) of whether the dom element whos callback function this is (this = $('#AllCatsChkBox')) is checked  */
		$("input[name='category[]']").attr({
			//TODO looking up checked twice is technically inefficient
			//TODO cleaner if we removeAttr('disabled') instead of setting to false (as it wasn't there before) 
			checked : $(this).is(':checked'), 
			disabled : $(this).is(':checked')  
		}); 
	});
});