<?php
/*
Plugin Name: Lightweight Contact Form
Plugin URI: https://isabelcastillo.com/absolute-lightest-contact-form
Description: Light, barebones Contact Form shortcode.
Version: 1.1.alpha3
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GPL2
*/
$value_name	= isset($_POST['lcf_contactform_name']) ? esc_attr($_POST['lcf_contactform_name']) : '';
$value_email = isset( $_POST['lcf_contactform_email'] ) ? esc_attr( $_POST['lcf_contactform_email'] ) : '';
$value_response	= isset($_POST['lcf_response']) ? esc_attr($_POST['lcf_response']) : '';
$value_message = isset($_POST['lcf_message']) ? esc_textarea($_POST['lcf_message']) : '';
$lcf_strings = array(
	'name' 		=> '<input name="lcf_contactform_name" id="lcf_contactform_name" type="text" class="required" size="33" maxlength="99" value="'. $value_name .'" placeholder="Your name" />',
	'email'		=> '<input name="lcf_contactform_email" id="lcf_contactform_email" type="text" class="required email" size="33" value="'. $value_email .'" placeholder="Your email" />',
	'response' 	=> '<input name="lcf_response" id="lcf_response" type="text" size="33" class="required number" maxlength="99" value="'. $value_response .'" />',
	'message' 	=> '<textarea name="lcf_message" id="lcf_message" class="required" minlength="4" cols="33" rows="7" placeholder="Your message">'. $value_message .'</textarea>',
	'error' 	=> ''
	);


/**
 * check for malicious input
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
 * check for spam
 */
function lcf_spam_question($input) {
	$response = '2';
	$response = stripslashes(trim($response));
	return ($input == $response);
}
/**
 * filter input
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
 * shortcode to display contact form
 */
function lcf_shortcode() {
	wp_enqueue_script( 'lcf-validate' );
	if (lcf_input_filter()) {
		return lcf_process_contact_form();
	} else {
		return lcf_display_contact_form();
	}
}
add_shortcode( 'lcf_contact_form', 'lcf_shortcode' );
/**
* Enqueue validation script
*/
function lcf_enqueue_scripts() {
	wp_register_script('lcf-validate', plugins_url( 'validate.js' , __FILE__ ), array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', 'lcf_enqueue_scripts');
/**
* process contact form
*/
function lcf_process_contact_form($content='') {
	$subject = sprintf( 'Contact form message from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$name = esc_html( $_POST['lcf_contactform_name'] );
	$email = sanitize_email( $_POST['lcf_contactform_email'] );
	$form = esc_url( getenv("HTTP_REFERER") );
	$date = date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) );
	$date = esc_html( $date ); 
	$message = esc_html( $_POST['lcf_message'] );
	$intro = sprintf( 'You are being contacted via %s:', home_url() ); 

$fullmsg = ("Hello,

$intro

Name:      $name
Email:     $email
Message:

$message

-----------------------

Additional Information:

URL:    $form
");
	$fullmsg = stripslashes(strip_tags(trim($fullmsg)));
	
	wp_mail( get_bloginfo('admin_email'), $subject, $fullmsg, "Reply-To: $email\n" );

	$results = ( '<div id="lcf-success"><div class="lcf-success-msg">Your message has been sent.</div>
<pre>Name:    ' . $name    . '
Email:   ' . $email   . '
Date:    ' . $date . '
Message: ' . $message .'</pre><p class="lcf_reset">[ <a href="'. $form .'">Click here to reset form</a> ]</p></div>' );
	echo $results;
}
/**
 * display contact form
 */
function lcf_display_contact_form() {
	global $lcf_strings;
	$captcha_box = '<label for="lcf_response"> 1 + 1 = </label>
					'. $lcf_strings['response'];
	$lcf_form = ($lcf_strings['error'] . '
		<div id="lcf-contactform-wrap">
			<form action="'. esc_url( get_permalink() ) .'" method="post" id="lcf-contactform">
					<label for="lcf_contactform_name">Name</label>
					'. $lcf_strings['name'] .'
					<label for="lcf_contactform_email">Email</label>
					'. $lcf_strings['email'] .'
					' . $captcha_box . '
					<label for="lcf_message">Message</label>
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
