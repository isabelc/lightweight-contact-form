<?php
/*
Plugin Name: Absolute Lightest Contact Form
Plugin URI: http://isabelcastillo.com/absolute-lightest-contact-form
Description: Light, barebones Contact Form shortcode.
Version: 0.8
Author: Isabel Castillo
Author URI: http://isabelcastillo.com
License: GPL2
*/
$value_name			  = isset($_POST['alcf_contactform_name']) ? htmlentities($_POST['alcf_contactform_name']) : '';
$value_email		  = isset( $_POST['alcf_contactform_email'] ) ? htmlentities( $_POST['alcf_contactform_email'] ) : '';
$value_response		= isset($_POST['alcf_response']) ? htmlentities($_POST['alcf_response']) : '';
$value_message		= isset($_POST['alcf_message']) ? htmlentities($_POST['alcf_message']) : '';
$alcf_strings 		= array(
	'name' 		      => '<input name="alcf_contactform_name" id="alcf_contactform_name" type="text" class="required" size="33" maxlength="99" value="'. $value_name .'" placeholder="Your name" />',
	'email'		      => '<input name="alcf_contactform_email" id="alcf_contactform_email" type="text" class="required email" size="33" maxlength="99" value="'. $value_email .'" placeholder="Your email" />',
	'response' 	    => '<input name="alcf_response" id="alcf_response" type="text" size="33" class="required number" maxlength="99" value="'. $value_response .'" />',
	'message' 	    => '<textarea name="alcf_message" id="alcf_message" class="required" minlength="4" maxlength="99" cols="33" rows="7" placeholder="Your message">'. $value_message .'</textarea>',
	'error' 	      => ''
	);


/**
 * check for malicious input
 */
function alcf_malicious_input($input) {
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
function alcf_spam_question($input) {
	$response = '2';
	$response = stripslashes(trim($response));
	return ($input == $response);
}
/**
 * filter input
 */
function alcf_input_filter() {

	if(!(isset($_POST['alcf_key']))) { 
		return false;
	}
	$_POST['alcf_contactform_name']     = stripslashes(trim($_POST['alcf_contactform_name']));
	$_POST['alcf_contactform_email']    = stripslashes(trim($_POST['alcf_contactform_email']));
	$_POST['alcf_message']  = stripslashes(trim($_POST['alcf_message']));
	$_POST['alcf_response'] = stripslashes(trim($_POST['alcf_response']));


	global $alcf_strings;
	$pass  = true;
	
	if(empty($_POST['alcf_contactform_name'])) {
		$pass = FALSE;
		$fail = 'empty';
		$alcf_strings['name'] = '<input class="alcf_contactform_error" name="alcf_contactform_name" id="alcf_contactform_name" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['alcf_contactform_name']) .'" placeholder="Your name" />';
	}
	if(!is_email($_POST['alcf_contactform_email'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$alcf_strings['email'] = '<input class="alcf_contactform_error" name="alcf_contactform_email" id="alcf_contactform_email" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['alcf_contactform_email']) .'" placeholder="Your email" />';
	}
	
		
	if (empty($_POST['alcf_response'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$alcf_strings['response'] = '<input class="alcf_contactform_error" name="alcf_response" id="alcf_response" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['alcf_response']) .'" placeholder="1 + 1 =" />';
	}
	if (!alcf_spam_question($_POST['alcf_response'])) {
		$pass = FALSE;
		$fail = 'wrong';
		$alcf_strings['response'] = '<input class="alcf_contactform_error" name="alcf_response" id="alcf_response" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['alcf_response']) .'" placeholder="1 + 1 =" />';
	}
	if(empty($_POST['alcf_message'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$alcf_strings['message'] = '<textarea class="alcf_contactform_error" name="alcf_message" id="alcf_message" cols="33" rows="7" placeholder="Your message">'. $_POST['alcf_message'] .'</textarea>';
	}
	
		
	if(alcf_malicious_input($_POST['alcf_contactform_name']) || alcf_malicious_input($_POST['alcf_contactform_email'])) {
		$pass = false; 
		$fail = 'malicious';
	}
	if($pass == true) {
		return true;
	} else {
		if($fail == 'malicious') {
			$alcf_strings['error'] = '<p class="alcf-error">Please do not include any of the following in the Name or Email fields: linebreaks, or the phrases "mime-version", "content-type", "cc:" or "to:"</p>';
		} elseif($fail == 'empty') {
		
			$alcf_strings['error'] = '<p class="alcf-error">Please complete the required fields.</p>';
		} elseif($fail == 'wrong') {
			$alcf_strings['error'] = '<p class="alcf-error">Oops. Incorrect answer for the security question. Please try again.<br />Hint: 1 + 1 = 2</p>';
		}
		return false;
	}
}
/**
 * shortcode to display contact form
 */
function alcf_shortcode() {
	if (alcf_input_filter()) {
		return alcf_process_contact_form();
	} else {
		return alcf_display_contact_form();
	}
}
add_shortcode('alcf_contact_form','alcf_shortcode');
/**
 * template tag to display contact form
 */
function alcf_contact_form() {
	if (alcf_input_filter()) {
		echo alcf_process_contact_form();
	} else {
		echo alcf_display_contact_form();
	}
}

/**
* Enqueue validation script
*/
function alcf_enqueue_scripts() {

	wp_register_script('alcf-validate', plugins_url( 'validate.js' , __FILE__ ), array('jquery'), false, true);

	// @todo set Contact page id.
	if (is_page(122)){
		wp_enqueue_script('alcf-validate');
	}
}
add_action('wp_enqueue_scripts', 'alcf_enqueue_scripts');
/**
* process contact form
*/
function alcf_process_contact_form($content='') {
	$subject	= sprintf( 'Contact form message from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$success 	= 'Your message has been sent.';
	$name 		= $_POST['alcf_contactform_name'];
	$email 		= $_POST['alcf_contactform_email'];
	$offset 	= -4;
	$form 		= getenv("HTTP_REFERER");
	$date 		= date("l, F jS, Y @ g:i a", time() + $offset * 60 * 60);
	$headers = "Reply-To: $email\n";
	$message = $_POST['alcf_message'];
	$intro = sprintf( 'You are being contacted via %s:', home_url() ); 

$fullmsg   = ("Hello,

$intro

Name:      $name
Email:     $email
Message:

$message

-----------------------

Additional Information:

URL:    $form
Date:   $date

");
	$fullmsg = stripslashes(strip_tags(trim($fullmsg)));
	
	wp_mail(get_bloginfo('admin_email'), $subject, $fullmsg, $headers);

	$results = ( '<div id="alcf-success"><div class="alcf-success-msg">' . $success . '</div>
<pre>Name:    ' . $name    . '
Email:   ' . $email   . '
Date:    ' . $date . '
Message: ' . $message .'</pre><p class="alcf_reset">[ <a href="'. $form .'">Click here to reset form</a> ]</p></div>' );
	echo $results;
}
/**
 * display contact form
 */
function alcf_display_contact_form() {
	global $alcf_strings;

	$captcha_box = '<fieldset class="alcf-response">
					<label for="alcf_response"> 1 + 1 = </label>
					'. $alcf_strings['response'] .'
				</fieldset>';
	$alcf_form = ($alcf_strings['error'] . '
		<div id="alcf-contactform-wrap">
			<form action="'. get_permalink() .'" method="post" id="alcf-contactform">
				<fieldset class="alcf-name">
					<label for="alcf_contactform_name">Name</label>
					'. $alcf_strings['name'] .'
				</fieldset>
				<fieldset class="alcf-email">
					<label for="alcf_contactform_email">Email</label>
					'. $alcf_strings['email'] .'
				</fieldset>
					' . $captcha_box . '
				<fieldset class="alcf-message">
					<label for="alcf_message">Message</label>
					'. $alcf_strings['message'] .'
				</fieldset>
				<div class="alcf-submit">
					<input type="submit" name="Submit" id="alcf_contact" value="Send">
					<input type="hidden" name="alcf_key" value="process">
				</div>
			</form>
		</div>
' );
	return $alcf_form;
}
