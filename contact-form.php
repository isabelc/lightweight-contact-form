<?php
/*
Plugin Name: Lightweight Contact Form
Plugin URI: https://isabelcastillo.com/lightweight-wordpress-contact-form
Description: Light, barebones Contact Form shortcode with client-side and server-side validation.
Version: 1.3
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GPL2
*/
$value_name	= isset($_POST['lcf_contactform_name']) ? esc_attr($_POST['lcf_contactform_name']) : '';
$value_email = isset( $_POST['lcf_contactform_email'] ) ? esc_attr( $_POST['lcf_contactform_email'] ) : '';
$value_response	= isset($_POST['lcf_response']) ? esc_attr($_POST['lcf_response']) : '';
$value_message = isset($_POST['lcf_message']) ? esc_textarea($_POST['lcf_message']) : '';
$lcf_strings = array(
	'name' 		=> '<input name="lcf_contactform_name" id="lcf_contactform_name" type="text" size="33" class="required" maxlength="99" value="'. $value_name .'" placeholder="Your name" />',
	'email'		=> '<input name="lcf_contactform_email" id="lcf_contactform_email" type="text" size="33" class="required" value="'. $value_email .'" placeholder="Your email" />',
	'response' 	=> '<input name="lcf_response" id="lcf_response" type="text" size="33" class="required" maxlength="99" value="'. $value_response .'" />',
	'message' 	=> '<textarea name="lcf_message" id="lcf_message" minlength="4" cols="33" rows="7" placeholder="Your message" class="required">'. $value_message .'</textarea>',
	'error' 	=> ''
	);

/**
 * Check for malicious input
 */
function lcf_malicious_input($input) {
	$maliciousness = false;
	$denied_inputs = array("\r", "\n", "mime-version", "content-type", "cc:", "to:");
	foreach($denied_inputs as $denied_input) {
		if(strpos(strtolower($input), strtolower($denied_input)) !== false) {
			$maliciousness = true;
			break;
		}
	}
	return $maliciousness;
}
/**
 * Block spam
 */
function lcf_spam_question($input) {
	$response = '2';
	$response = stripslashes(trim($response));
	return ($input == $response);
}
/**
 * Validate the input, server-side
 */
function lcf_input_filter() {
	if(!(isset($_POST['lcf_key']))) { 
		return false;
	}
	$_POST['lcf_contactform_name'] = stripslashes(trim($_POST['lcf_contactform_name']));
	$_POST['lcf_contactform_email'] = stripslashes(trim($_POST['lcf_contactform_email']));
	$_POST['lcf_message'] = stripslashes(trim($_POST['lcf_message']));
	$_POST['lcf_response'] = stripslashes(trim($_POST['lcf_response']));

	global $lcf_strings;
	$pass  = true;
	
	if(empty($_POST['lcf_contactform_name'])) {
		$pass = FALSE;
		$fail = 'empty';
		$lcf_strings['name'] = '<input class="lcf_contactform_error" name="lcf_contactform_name" id="lcf_contactform_name" type="text" size="33" maxlength="99" value="'. esc_attr($_POST['lcf_contactform_name']) .'" placeholder="Your name" />';
	}
	if(!is_email($_POST['lcf_contactform_email'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$lcf_strings['email'] = '<input class="lcf_contactform_error" name="lcf_contactform_email" id="lcf_contactform_email" type="text" size="33" value="'. esc_attr($_POST['lcf_contactform_email']) .'" placeholder="Your email" />';
	}
	if (empty($_POST['lcf_response'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$lcf_strings['response'] = '<input class="lcf_contactform_error" name="lcf_response" id="lcf_response" type="text" size="33" maxlength="99" value="'. esc_attr($_POST['lcf_response']) .'" placeholder="1 + 1 =" />';
	}
	if (!lcf_spam_question($_POST['lcf_response'])) {
		$pass = FALSE;
		$fail = 'wrong';
		$lcf_strings['response'] = '<input class="lcf_contactform_error" name="lcf_response" id="lcf_response" type="text" size="33" maxlength="99" value="'. esc_attr($_POST['lcf_response']) .'" placeholder="1 + 1 =" />';
	}
	if(empty($_POST['lcf_message'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$lcf_strings['message'] = '<textarea class="lcf_contactform_error" name="lcf_message" id="lcf_message" cols="33" rows="7" placeholder="Your message">'. esc_textarea( $_POST['lcf_message'] ) .'</textarea>';
	}
			
	if(lcf_malicious_input($_POST['lcf_contactform_name']) || lcf_malicious_input($_POST['lcf_contactform_email'])) {
		$pass = false; 
		$fail = 'malicious';
	}
	if($pass == true) {
		return true;
	} else {
		if($fail == 'malicious') {
			$lcf_strings['error'] = '<p class="lcf-error">Please do not include any of the following in the Name or Email fields: linebreaks, or the phrases "mime-version", "content-type", "cc:" or "to:"</p>';
		} elseif($fail == 'empty') {
		
			$lcf_strings['error'] = '<p class="lcf-error">Please complete the required fields.</p>';
		} elseif($fail == 'wrong') {
			$lcf_strings['error'] = '<p class="lcf-error">Oops. Incorrect answer for the security question. Please try again.<br />Hint: 1 + 1 = 2</p>';
		}
		return false;
	}
}

/**
 * Add the validation script to the footer on the contact form page.
 */
function lcf_form_validation() {
	?><script type='text/javascript'>
jQuery(document).ready(function(){
jQuery('#lcf_contact').click(function(event) {
	var msg = 'This field is required.';
	var hasBlank = false;
	var emailBlank = false;
	var mathBlank = false;
	jQuery( '.error' ).hide();// hide previous errors
	jQuery('#lcf-contactform').find('#lcf_contactform_name, #lcf_contactform_email, #lcf_response, #lcf_message').each(function() {
		if ( jQuery.trim( jQuery( this ).val() ) == '' ) {
			var attrName = jQuery(this).attr('name');
	        var errorLabel = jQuery( '<label />' );
			errorLabel.attr( 'for', attrName );
			errorLabel.addClass( 'error' );
			errorLabel.text( msg );
			jQuery( this ).after( errorLabel );
			hasBlank = true;
			if ( 'lcf_contactform_email' == attrName ) { emailBlank = true; }// is the Email field blank?
			if ( 'lcf_response' == attrName ) { mathBlank = true; }// is the Math field blank?
		}
    });
    if ( ! emailBlank ) { // if the Email is entered, validate it
	    var sEmail = jQuery.trim( jQuery('#lcf_contactform_email').val() );
	    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
	    if ( ! filter.test(sEmail) ) { // Bad email, so add an error
	        var errorLabel = jQuery( '<label />' );
			errorLabel.attr( 'for', 'lcf_contactform_email' );
			errorLabel.addClass( 'error' );
			errorLabel.text( 'Please enter a valid email.' );
			jQuery( '#lcf_contactform_email' ).after( errorLabel );
			hasBlank = true;
	    }
	}
	if ( ! mathBlank ) { // if the math response is entered, validate it
		var sMath = jQuery.trim( jQuery('#lcf_response').val() );
		if ( ! jQuery.isNumeric(sMath) ) { // Not numeric, so add an error
			var errorLabel = jQuery( '<label />' );
			errorLabel.attr( 'for', 'lcf_response' );
			errorLabel.addClass( 'error' );
			errorLabel.text( 'Please solve the math problem. The answer must be a number.' );
			jQuery( '#lcf_response' ).after( errorLabel );
			hasBlank = true;
		}
	}
    if ( hasBlank ) { return false; }
});
});</script>
<?php 
}

/**
 * Shortcode to display contact form
 */
function lcf_shortcode( $atts ) {
	$the_atts = shortcode_atts( array(
		'message_label'	=> 'Message'
	), $atts, 'lcf_contact_form' );

	if (lcf_input_filter()) {
		return lcf_process_contact_form( $the_atts );
	} else {
		wp_enqueue_script( 'jquery' );
		add_action( 'wp_footer', 'lcf_form_validation', 9999 );
		return lcf_display_contact_form( $the_atts );
	}
}
add_shortcode( 'lcf_contact_form', 'lcf_shortcode' );

/**
* Process contact form
*/
function lcf_process_contact_form( $atts ) {
	$subject = sprintf( 'Contact form message from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$name = esc_html( $_POST['lcf_contactform_name'] );
	$email = sanitize_email( $_POST['lcf_contactform_email'] );
	$form = esc_url( getenv("HTTP_REFERER") );
	$date = date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) );
	$date = esc_html( $date );
	$filter = apply_filters( 'lcf_additional_field_values', false, $_POST );
	$message_label = esc_html( $atts['message_label'] );
	$message = esc_html( $_POST['lcf_message'] );
	$intro = sprintf( 'You are being contacted via %s :', $form );

$fullmsg = ("Hello,

$intro

Name:      $name
Email:     $email
$filter
${message_label}:

$message

-----------------------

");
	$fullmsg = stripslashes(strip_tags(trim($fullmsg)));
	
	wp_mail( get_bloginfo('admin_email'), $subject, $fullmsg, "Reply-To: $email\n" );

	$results = ( '<div id="lcf-success"><div class="lcf-success-msg">Your message has been sent.</div>
<pre>Name:    ' . $name    . '
Email:   ' . $email   . '
Date:    ' . $date . '
' . $filter . '
' . $message_label . ': ' . $message .'</pre><p class="lcf_reset">[ <a href="'. $form .'">Click here to reset form</a> ]</p></div>' );
	echo $results;
}
/**
 * Display contact form
 */
function lcf_display_contact_form( $atts ) {
	global $lcf_strings;
	$url = get_permalink();
	$captcha_box = '<label for="lcf_response"> 1 + 1 = </label>
					'. $lcf_strings['response'];
	$lcf_form = ( $lcf_strings['error'] . '
		<div id="lcf-contactform-wrap">
			<form action="'. esc_url( $url ) .'" method="post" id="lcf-contactform">
					<label for="lcf_contactform_name">Name</label>
					'. $lcf_strings['name'] .'
					<label for="lcf_contactform_email">Email</label>
					'. $lcf_strings['email'] );
	// filter to allow insertion of more fields.
	$lcf_form .= apply_filters( 'lcf_form_fields', $captcha_box, $url );
	$lcf_form .= ( '<label for="lcf_message">' . esc_html( $atts['message_label'] ) . '</label>
					'. $lcf_strings['message'] .'
				<div class="lcf-submit">
					<input type="submit" name="Submit" id="lcf_contact" value="Send">
					<input type="hidden" name="lcf_key" value="process">
				</div>
			</form>
		</div>
' );
	return $lcf_form;
}
