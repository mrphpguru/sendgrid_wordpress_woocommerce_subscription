<?php
/*
Plugin Name: Sendgrid wordpress woocommerce subscription
Description: Plugin for Acount Registration using Sendgrid API
Plugin URI: http://cosmowhiz.com/
Author URI: http://cosmowhiz.com/
Author: Rajesh Kumar, mrphpguru
License: Public Domaia
Version: 100.1
*/

include_once 'include/admin_menu.php';


function sendgrid_wordpress_woocommerce_subscription_admin_init() 
{
       /* Register our stylesheet. */
       wp_register_style('sendgrid_wordpress_woocommercePluginStylesheet', plugins_url('css/stylesheet.css', __FILE__) );
}
add_action( 'admin_init', 'sendgrid_wordpress_woocommerce_subscription_admin_init' );

function myplugin_activate_sendgrid() 
{
  add_option( 'sendgrid_key', '', '', 'yes' );
  add_option( 'sendgrid_listid', '', '', 'yes');
}
register_activation_hook( __FILE__, 'myplugin_activate_sendgrid');

function my_plugin_removeoption_sendgrid()
{
	delete_option('sendgrid_key');
	delete_option('sendgrid_listid');
	//delete_option('integration_listid');
}
register_deactivation_hook( __FILE__, 'my_plugin_removeoption_sendgrid');

add_action('register_form','show_newsletter_field');
add_action('user_register', 'register_extra_newslatter_fields');

function show_newsletter_field()
{
?>
    <p>
    <label> <input id="newsletter" type="checkbox" tabindex="30" size="25" value="1" name="newsletter" checked />Subscribe to Newsletter
   
    </label>
    </p>
<?php
}

function register_extra_newslatter_fields ( $user_id, $password = "", $meta = array() )
{
    update_user_meta( $user_id, 'newsletter', $_POST['newsletter'] );
}
add_action( 'user_register', 'after_user_registration', 10, 1 );

function after_user_registration($user_id)
{
    	$user_info = get_userdata($user_id);
    	update_user_meta( $user_id, 'newsletter', $user_info->newsletter);
    	if($user_info->newsletter=='1'){
    	if ( isset( $_POST['first_name'] ) ){
    		$firstname=$_POST['first_name'];
    	}
    	else{
    	$firstname='';	
    	}
    	if ( isset( $_POST['last_name'] ) ){
    		$last_name=$_POST['last_name'];
    	}
    	else{
    	$last_name='';	
    	}
    	$email=$user_info->user_email;
        $apikeyvalue=get_option( 'sendgrid_key');
        $listidvalue=get_option( 'sendgrid_listid');
        require 'vendor/autoload.php';
    	error_reporting(-1);
    	ini_set('display_errors', 'On');
    //creating contact
    	$url = 'https://api.sendgrid.com/v3/';
    	$request =  $url.'contactdb/recipients';  //12345 is list_id
    	$params = array(array(
    'email' => $email,
    'first_name' => $firstname,
    'last_name' => $last_name
    ));
    $json_post_fields = json_encode($params);
    $ch = curl_init();
    $headers = 
    array("Content-Type: application/json",
    "Authorization: Bearer ".$apikeyvalue."");
    curl_setopt($ch, CURLOPT_POST, true);   
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_post_fields);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
    print "Error: " . curl_error($ch);
    } else {
    curl_close($ch);
    }
    // adding newly created contact to listcreating contact
    $recipientsid=base64_encode($email);
    $url2 = 'https://api.sendgrid.com/v3/';
    $request2 =  $url2.'contactdb/lists/'.$listidvalue.'/recipients/'.$recipientsid;  //12345 is list_id
    $ch = curl_init();
    $headers = 
    array("Content-Type: application/json",
    "Authorization: Bearer ".$apikeyvalue."");
    curl_setopt($ch, CURLOPT_POST, true);   
    curl_setopt($ch, CURLOPT_URL, $request2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
      print "Error: " . curl_error($ch);
    } 
    else 
    {
      // Show me the result
      curl_close($ch);
    }

}


	}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	/*
* Addding subscription  checkbox to the checkout page
*/
add_action('woocommerce_after_order_notes', 'checkbox_custom_checkout_field');

function checkbox_custom_checkout_field( $checkout ) 
{
  $checked = $checkout->get_value( 'newslater' ) ? $checkout->get_value( 'newslater' ) : 1;
  woocommerce_form_field( 'newslater', array(
  'type'          => 'checkbox',
  'checked'=>'checked',
  'class'         => array('checkbox_field'),
  'label'         => __('Subscribe to Newsletter'),

  ), $checked);

}

add_action('woocommerce_checkout_update_order_meta', 'checkbox_custom_newslater_meta');

function checkbox_custom_newslater_meta( $order_id ) 
{
  if ($_POST['newslater']){
  	$newslater=$_POST['newslater'];
  }
  else{
  	$newslater=0;
  }	
   update_post_meta( $order_id, '_news_later', $newslater);
 
}

add_action('woocommerce_thankyou', 'Thankyouorder_And_Subscribe_Newlater');

function Thankyouorder_And_Subscribe_Newlater( $order_id ) 
{
      $order = new WC_Order( $order_id );
      global $wpdb;
     $order_id =  $order->id;

     $table = $wpdb->prefix . 'postmeta';
     $sql = 'SELECT * FROM `'. $table . '` WHERE post_id = '. $order_id; 
      $result = $wpdb->get_results($sql);
      foreach($result as $res) {
        if( $res->meta_key == '_billing_first_name'){
        $firstname = $res->meta_value;      
        }
        if( $res->meta_key == '_billing_last_name'){
         $last_name = $res->meta_value;  
        }
        if( $res->meta_key == '_billing_email'){
        $email = $res->meta_value;  
          }
        if( $res->meta_key == '_news_later'){
        $newslater = $res->meta_value;  
          }
        }
        if($newslater=='1'):
        
        $apikeyvalue=get_option( 'sendgrid_key');
        $listidvalue=get_option( 'sendgrid_listid');
         require 'vendor/autoload.php';

       
    error_reporting(-1);
    ini_set('display_errors', 'On');
    //creating contact
    $url = 'https://api.sendgrid.com/v3/';
    $request =  $url.'contactdb/recipients';  //12345 is list_id
    $params = array(array(
    'email' => $email,
    'first_name' => $firstname,
    'last_name' => $last_name
    ));
    $json_post_fields = json_encode($params);
    $ch = curl_init();
    $headers = 
    array("Content-Type: application/json",
    "Authorization: Bearer ".$apikeyvalue."");
    curl_setopt($ch, CURLOPT_POST, true);   
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_post_fields);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
    print "Error: " . curl_error($ch);
    } else {
    curl_close($ch);
    }
    // adding newly created contact to listcreating contact
    $recipientsid=base64_encode($email);
    $url2 = 'https://api.sendgrid.com/v3/';
    $request2 =  $url2.'contactdb/lists/'.$listidvalue.'/recipients/'.$recipientsid;  //12345 is list_id
    $ch = curl_init();
    $headers = 
    array("Content-Type: application/json",
    "Authorization: Bearer ".$apikeyvalue."");
    curl_setopt($ch, CURLOPT_POST, true);   
    curl_setopt($ch, CURLOPT_URL, $request2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
    print "Error: " . curl_error($ch);
    } else {
    // Show me the result
    curl_close($ch);
    }
        endif;
    }
}
?>