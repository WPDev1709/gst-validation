<?php

/**
* Plugin Name: GST Validation
* Description: GST input and validation through API plugin
* Version: 1.0
* Author: Jumana
* Author URI: https://jumana.com/
**/

// add_action( 'woocommerce_before_order_notes', 'bbloomer_add_custom_checkout_field' );
  
// function bbloomer_add_custom_checkout_field( $checkout ) { 
//    $current_user = wp_get_current_user();
//    $saved_gst_no = $current_user->gst_no;
//    woocommerce_form_field( 'gst_no', array(        
//       'type' => 'text',        
//       'class' => array( 'form-row-wide' ),        
//       'label' => 'GST Number',        
//       'placeholder' => 'Add GST Number',        
//       // 'required' => true,
//       // 'pattern' => '^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$',      
     
//       'default' => $saved_gst_no,        
//    ), $checkout->get_value( 'gst_no' ) ); 
// }


add_action( 'woocommerce_before_order_notes', 'display_extra_fields_after_billing_address' );
function display_extra_fields_after_billing_address ($checkout) {
	$current_user = wp_get_current_user();
   $saved_gst_no = $current_user->gst_no;
	_e( "Add GST Number: ", "gst_no");
	?>
	<br>
	<input type="text" name="gst_no" class="form-row-wide" placeholder="Add GST Number" default="<?$saved_gst_no?>" style="border-radius: 3px;border: 1px solid #d5d5d5;">
  <?php 
}



 add_action( 'woocommerce_checkout_process', 'bbloomer_validate_new_checkout_field' );
  
function bbloomer_validate_new_checkout_field() {    
	 $regex = "/^([0][1-9]|[1-2][0-9]|[3][0-5])([a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}[1-9a-zA-Z]{1}[zZ]{1}[0-9a-zA-Z]{1})+$/";
   if ( !empty($_POST['gst_no']) ) {
   	
		// wc_add_notice( $_POST['gst_no'], 'error' );
   	$gstin = trim($_POST['gst_no']);
   	$url = 'https://cleartax.in/f/compliance-report/'.$gstin.'/';
   	$obj = json_decode(file_get_contents($url), true);
   	// wc_add_notice( $obj );
   
   		if(!preg_match($regex, trim($_POST['gst_no']))){
			wc_add_notice( 'Please enter Valid GST Number', 'error' );
   		}
		elseif ($obj['taxpayerInfo'] == null) {
   		wc_add_notice( 'GST Number not found', 'error' );
   		}
   	
      
   }

  else{
  	return preg_match($regex, $_POST['gst_no']);
  }
}

add_action( 'woocommerce_checkout_update_order_meta', 'bbloomer_save_new_checkout_field' );
  
function bbloomer_save_new_checkout_field( $order_id ) { 
    if ( $_POST['gst_no'] ) update_post_meta( $order_id, '_gst_no', esc_attr( $_POST['gst_no'] ) );
}
  
add_action( 'woocommerce_admin_order_data_after_billing_address', 'bbloomer_show_new_checkout_field_order', 10, 1 );
   
function bbloomer_show_new_checkout_field_order( $order ) {    
   $order_id = $order->get_id();
   echo    $order_id;
   ?>
   <p><?php echo $order->get_meta('_gst_no'); ?></p>
   <?php
   if ( get_post_meta( $order_id, '_gst_no', true ) ) echo '<p><strong>gst Number:</strong> ' . get_post_meta( $order_id, '_gst_no', true ) . '</p>';
}
 
add_action( 'woocommerce_email_after_order_table', 'bbloomer_show_new_checkout_field_emails', 20, 4 );
  
function bbloomer_show_new_checkout_field_emails( $order, $sent_to_admin, $plain_text, $email ) {
    if ( get_post_meta( $order->get_id(), '_gst_no', true ) ) echo '<p><strong>gst Number:</strong> ' . get_post_meta( $order->get_id(), '_gst_no', true ) . '</p>';

}



 add_action( 'wpo_wcpdf_after_order_data', 'wpo_wcpdf_delivery_date', 10, 2 );
function wpo_wcpdf_delivery_date ($template_type, $order) {
    if ($template_type == 'invoice') {
        ?>
        <tr class="gst-no">
            <th>GST Number:</th>
            <td><?php echo $order->get_meta('_gst_no'); ?></td>
        </tr>
        <?php
    }
}

add_action('woocommerce_order_details_before_order_table', 'my_custom_order_manipulation_function');
function my_custom_order_manipulation_function( $order ) {
    ?>
    <p><b>GST NUMBER: <?php echo $order->get_meta('_gst_no'); ?></b></p>
    <?php
}