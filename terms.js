jQuery(document).ready(function($) {
	// Begin by disabling the button
	// Button id:  continue
	// Checkbox id:  terms
	var count = 0;
	$('#continue').attr("href", "http://iq-express.com/paying-later/?add-to-cart=1973");
	$('#continue').click(function(e) {
		if(!$('#terms').is(':checked')) 
		{
			if( count < 1)
			{
				$('.error').toggle();
				count++;
			}
			e.preventDefault();
		}
	});
});

