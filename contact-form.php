<?php
/*
Plugin Name: Lightweight Contact Form
Plugin URI: https://isabelcastillo.com/lightweight-wordpress-contact-form
Description: Light, barebones Contact Form shortcode with client-side and server-side validation.
Version: 2.0
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
Text Domain: lightweight-contact-form
License: GNU GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright 2015-2019 Isabel Castillo

Lightweight Contact Form is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Lightweight Contact Form is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Lightweight Contact Form. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Check for malicious input
 */
function lcf_malicious_input( $input ) {
	$maliciousness = false;
	$denied_inputs = array( "\r", "\n", "mime-version", "content-type", "cc:", "to:" );
	foreach ( $denied_inputs as $denied_input ) {
		if ( strpos( strtolower( $input ), strtolower( $denied_input ) ) !== false ) {
			$maliciousness = true;
			break;
		}
	}
	return $maliciousness;
}
/**
 * Validate the input, server-side
 */
function lcf_input_filter() {
	// Block spam bots by making sure honeypot field is empty
	if ( ! empty( $_POST['lcf_hundred_acre_wood_field'] ) ) {
		return false;
	}
	if ( ! ( isset( $_POST['lcf_key'] ) ) ) {
		return false;
	}
	if ( 'process_form' != $_POST['lcf_key'] ) {
		return false;
	}

	// verify Google reCAPTCHA v3 only if keys are set
	$priv_key = get_option( 'lcf_recaptcha_v3_secret_key' );
	if ( $priv_key && get_option( 'lcf_recaptcha_v3_site_key' ) ) {
		if ( empty( $_POST['lcf-grecaptcha-response'] ) ) {
	        return false;
		} else {
			$token = sanitize_text_field( $_POST['lcf-grecaptcha-response'] );
	        $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
	            'body' => array(
	                'secret'   => $priv_key,
	                'response' => $token,
	                'remoteip' => $_SERVER['REMOTE_ADDR']
	            )
	        ) );
	        $result = json_decode( $response['body'] );

			if ( empty($result->success) || ( 'lcf' != $result->action ) ) {
				return false;
			}

			if ( $result->score < 0.5 ) {
				return false;
			}		
	    }
	}

	global $lcf_strings;
	$pass  = true;
	$lcf_strings['value_name'] = isset( $_POST['lcf_contactform_name'] ) ? sanitize_text_field( $_POST['lcf_contactform_name'] ) : '';
	$lcf_strings['value_email'] = isset( $_POST['lcf_contactform_email'] ) ? sanitize_email( $_POST['lcf_contactform_email'] ) : '';
	$lcf_strings['value_message'] = isset( $_POST['lcf_message'] ) ? sanitize_textarea_field($_POST['lcf_message']) : '';

	if ( empty( $lcf_strings['value_name'] ) ) {
		$pass = false;
		$fail = 'empty';
		$lcf_strings['name_error'] = true;
	}
	if ( ! is_email( $lcf_strings['value_email'] ) ) {
		$pass = false; 
		$fail = 'empty';
		$lcf_strings['email_error'] = true;
	}
	if ( empty( $lcf_strings['value_message'] ) ) {
		$pass = false; 
		$fail = 'empty';
		$lcf_strings['message_error'] = true;
	}
			
	if ( lcf_malicious_input( $lcf_strings['value_name'] ) || lcf_malicious_input( $lcf_strings['value_email'] ) ) {
		$pass = false; 
		$fail = 'malicious';
	}
	if ( $pass == true ) {
		return true;
	} else {
		if ( $fail == 'malicious' ) {
			$lcf_strings['error'] = __( 'Please do not include any of the following in the Name or Email fields: linebreaks, or the phrases "mime-version", "content-type", "cc:" or "to:"', 'lightweight-contact-form' );
		} elseif ( $fail == 'empty' ) {
			$lcf_strings['error'] = __( 'Please complete the required fields.', 'lightweight-contact-form' );
		} elseif ( $fail == 'wrong' ) {
			$lcf_strings['error'] = __( 'Oops. Incorrect answer for the security question. Please try again. Hint: 1 + 1 = 2', 'lightweight-contact-form' );
		}
		return false;
	}
}

/**
 * Add the validation script to the footer on the contact form page.
 */
function lcf_form_validation() {
	// only do reCaptcha if keys are set
	$grecaptcha = '';
	$site_key = get_option( 'lcf_recaptcha_v3_site_key' );
	if ( $site_key && get_option( 'lcf_recaptcha_v3_secret_key' ) ) {
		$grecaptcha = "grecaptcha.ready(function() {grecaptcha.execute('" . $site_key. "', {action: 'lcf'}).then(function(token) {document.getElementById('lcf-grecaptcha-response').value = token;});});";
	}
	?><script type='text/javascript'><?php echo $grecaptcha; ?>var honey = ['lcf-hundred-acre-wood-field','lcf-hundred-acre-wood-label'];
	var len = 2;
	for (var i = 0; i < len; i++) {
		document.getElementById(honey[i]).style.position = 'absolute';
		document.getElementById(honey[i]).style.overflow = 'hidden';
		document.getElementById(honey[i]).style.clip = 'rect(0px, 0px, 0px, 0px)';
		document.getElementById(honey[i]).style.height = '1px';
		document.getElementById(honey[i]).style.width = '1px';
		document.getElementById(honey[i]).style.margin = '-1px';
		document.getElementById(honey[i]).style.border = '0 none';
		document.getElementById(honey[i]).style.padding = '0';
	}
	var submitButton = document.getElementById('lcf_contact');
	submitButton.onclick = function() {
		if(document.getElementById('lcf-hundred-acre-wood-field').value) { 
			return false;
		}
		var hasBlank = false;
		var emailBlank = false;
		// hide previous errors
		[].forEach.call(document.querySelectorAll('.error'), function (el) {
			el.style.display = 'none';
		});
		// Check for blank fields.
		var fields = ['lcf_contactform_name', 'lcf_contactform_email', 'lcf_message'];
		var i, l = fields.length;
		var fieldname;
		for (i = 0; i < l; i++) {
			fieldname = fields[i];
			var el = document.forms['lcf-contactform'][fieldname];
			if ( el.value.trim() === '' ) {
				lcfErrorLabel(el, fieldname, "\u2191 <?php _e( 'This field is required.', 'lightweight-contact-form' ); ?>" );
				hasBlank = true;
				if ('lcf_contactform_email' == fieldname) {emailBlank = true;}// is the Email field blank?
			}
		}
	    if (!emailBlank) { // if Email is entered, validate it
	    	var eNode = document.forms['lcf-contactform']['lcf_contactform_email'];
		    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
		    if ( ! filter.test( eNode.value.trim() ) ) {
				lcfErrorLabel(eNode, 'lcf_contactform_email', "<?php _e( 'Invalid email. Please enter a valid email.', 'lightweight-contact-form' ); ?>" );
				hasBlank = true;
		    }
		}
	    if (hasBlank) {scroll(0,0);return false;}
	};
	function lcfErrorLabel(el, fieldname, text ) {
		var err = document.createElement('label');
		err.setAttribute('for', fieldname); 
		err.className = 'error';
		err.innerText = text;
		el.parentNode.insertBefore(err, el.nextSibling);
	}</script>
<?php 
}

/**
 * Add the reCAPTCHA api.js if site key is set
 * @since 2.0
 */
function lcf_recaptcha_js() {
	$site_key = get_option( 'lcf_recaptcha_v3_site_key' );
	if ( $site_key && get_option( 'lcf_recaptcha_v3_secret_key' ) ) {
		?><script src="https://www.google.com/recaptcha/api.js?render=<?php echo $site_key; ?>"></script><?php
	}
}

/**
 * Shortcode to display contact form
 */
function lcf_shortcode( $atts ) {
	$the_atts = shortcode_atts( array(
		'message_label'	=> __( 'Message', 'lightweight-contact-form' )
	), $atts, 'lcf_contact_form' );
	add_action( 'wp_footer', 'lcf_recaptcha_js' );
	if ( lcf_input_filter() ) {
		return lcf_process_contact_form( $the_atts );
	} else {
		add_action( 'wp_footer', 'lcf_form_validation', 9999 );
		return lcf_display_contact_form( $the_atts );
	}
}
add_shortcode( 'lcf_contact_form', 'lcf_shortcode' );

/**
* Process contact form
*/
function lcf_process_contact_form( $atts ) {
	global $lcf_strings;
	$subject = sprintf( __( 'Contact form message from %s', 'lightweight-contact-form' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$name_label = __( 'Name:', 'lightweight-contact-form' );
	$name = isset( $lcf_strings['value_name'] ) ? $lcf_strings['value_name'] : '';
	$email_label = __( 'Email:', 'lightweight-contact-form' );
	$email = isset( $lcf_strings['value_email'] ) ? sanitize_email( $lcf_strings['value_email'] ) : '';
	$message_label = esc_html( $atts['message_label'] );
	$message = isset( $lcf_strings['value_message'] ) ? $lcf_strings['value_message'] : '';
	$form = esc_url( getenv("HTTP_REFERER") );
	$date = date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) );
	$date = esc_html( $date );
	$filter = apply_filters( 'lcf_additional_field_values', false );
	$intro = sprintf( __( 'You are being contacted via %s :', 'lightweight-contact-form' ), $form );
	$hello_i18n = __( 'Hello,', 'lightweight-contact-form' );
	$fullmsg = ("$hello_i18n

$intro

$name_label      " . stripslashes( $name ) . "
$email_label     $email
$filter
${message_label}:

" . stripslashes( $message ) . "

-----------------------

");
	wp_mail( get_bloginfo( 'admin_email' ), $subject, $fullmsg, "Reply-To: $email\n" );

	$results = '<div id="lcf-success"><div class="lcf-success-msg">' . __( 'Your message has been sent.', 'lightweight-contact-form' ) . '</div>
<pre>' . $name_label . '    ' . stripslashes( esc_html( $name ) ) . '
' . $email_label . '   ' . esc_html( $email )   . '
' . __( 'Date:', 'lightweight-contact-form' ) . '    ' . $date . '
' . $filter . '
' . $message_label . ': ' . stripslashes( esc_textarea( $message ) ) .'</pre><p class="lcf_reset">[ <a href="'. $form . '">' . __( 'Click here to reset form', 'lightweight-contact-form' ) . '</a> ]</p></div>';
	echo $results;
}
/**
 * Display contact form
 */
function lcf_display_contact_form( $atts ) {
	global $lcf_strings;
	$lcf_form = '';
	$url = get_permalink();
	// entered values
	$name = isset( $lcf_strings['value_name'] ) ? sanitize_text_field( $lcf_strings['value_name'] ) : '';
	$email = isset( $lcf_strings['value_email'] ) ? sanitize_email( $lcf_strings['value_email'] ) : '';
	$message = isset( $lcf_strings['value_message'] ) ? sanitize_textarea_field( $lcf_strings['value_message'] ) : '';
	
	// set class attributes for fields based on whether there is an error
	$fields = array( 'name', 'email', 'message' );
	foreach ( $fields as $field ) {
		${"{$field}_class"} = empty( $lcf_strings["{$field}_error"] ) ? 'required' : 'lcf_contactform_error';
	}
	if ( isset( $lcf_strings['error'] ) ) {
		$lcf_form .= '<p class="lcf-error">' . esc_html( $lcf_strings['error'] ) . '</p>';

	}

	$lcf_form .= '<div id="lcf-contactform-wrap">
			<form action="'. esc_url( $url ) .'" method="post" name="lcf-contactform" id="lcf-contactform">
				<label for="lcf_contactform_name">' . __( 'Name', 'lightweight-contact-form' ) . '</label>
				<input name="lcf_contactform_name" id="lcf_contactform_name" type="text" size="33" class="' . esc_attr( $name_class ) . '" maxlength="99" value="'.
				stripslashes( esc_attr( $name ) ) .'" placeholder="' . __( 'Your name', 'lightweight-contact-form' ) . '" required />
				<label for="lcf_contactform_email">' . __( 'Email', 'lightweight-contact-form' ) .'</label>
				<input name="lcf_contactform_email" id="lcf_contactform_email" type="text" size="33" class="' . esc_attr( $email_class ) . '" value="'. esc_attr( $email ) .'" placeholder="' . __( 'Your email', 'lightweight-contact-form' ) . '" required />';
	// add a honeypot field to block spam
	$lcf_form .= '<label for="lcf-hundred-acre-wood-field" id="lcf-hundred-acre-wood-label">' . __( 'For Official Use Only', 'lightweight-contact-form' ) . '</label><input name="lcf_hundred_acre_wood_field" type="text" id="lcf-hundred-acre-wood-field" value="" tabindex="-1" />';
	// filter to allow insertion of more fields.
	$lcf_form .= apply_filters( 'lcf_form_fields', '', $url );
	$lcf_form .= ( '<label for="lcf_message">' . esc_html( $atts['message_label'] ) . '</label>
				<textarea name="lcf_message" id="lcf_message" minlength="4" cols="33" rows="7" placeholder="' . __( 'Your message', 'lightweight-contact-form' ) . '" class="' . esc_attr( $message_class ) . '" required>'. stripslashes( esc_textarea( $message ) ) .'</textarea>
				<div class="lcf-submit">
					<input type="hidden" id="lcf-grecaptcha-response" name="lcf-grecaptcha-response">
					<input type="hidden" name="lcf_key" value="process_form">
					<input type="submit" name="Submit" id="lcf_contact" value="' . __( 'Send', 'lightweight-contact-form' ) . '">
				</div>
			</form>
		</div>' );
	return $lcf_form;
}

add_action( 'admin_init', 'lcf_register_settings' );
/**
 * LCF reCAPTCHA settings
 * @since 2.0
 */
function lcf_register_settings() {
    add_settings_section(
        'lcf_recaptcha_keys',
        __( 'Lightweight Contact Form', 'lightweight-contact-form' ),
        'lcf_setting_description',
        'discussion'
    );
    $settings = array(
    	'lcf_recaptcha_v3_site_key' => __( 'reCAPTCHA v3 Site key', 'lightweight-contact-form' ),
    	'lcf_recaptcha_v3_secret_key' => __( 'reCAPTCHA v3 Secret key', 'lightweight-contact-form' )
    );
    foreach ( $settings as $id => $title ) {
	    register_setting(
	        'discussion',
	        $id,
	        'trim'
	    );
	    add_settings_field(
	        $id,
	        $title,
	        'lcf_setting_field',
	        'discussion',
	        'lcf_recaptcha_keys',
	        array ( 'label_for' => $id )
	    );
	}
}

/**
 * Print description text for the field.
 * @since 2.0
 */
function lcf_setting_description() {
    ?><p class="description"><?php _e( 'To get your reCAPTCHA v3 Site key and Secret key, you need to <a href="http://www.google.com/recaptcha/admin">sign up for a reCAPTCHA API key pair</a> from Google.', 'lightweight-contact-form' ); ?></p><?php
}

/**
 * Setting field callback
 * @since 2.0
 */
function lcf_setting_field( $args ) {
    printf(
        '<input type="text" class="regular-text" name="%1$s" value="%2$s" id="%1$s" />',
        $args['label_for'],
        esc_attr( get_option( $args['label_for'], '' ) )
    );
}
