<?php
  /*adding Menu and submenu in admin*/
    function sendgrid_integration_admin_menu()
      {
        add_options_page(__('sendgrid-registration', 'sendgrid_table'), __('Sendgrid wordpress woocommerce', 'ac-sendgrid_table'), 'activate_plugins', 'sendgrid', 'create_api_form');
        }
        
        add_action('admin_menu', 'sendgrid_integration_admin_menu');

        /*adding Menu and submenu in admin end */
        function create_api_form()
        {
          if(isset($_POST['submit'])):
          	if($_POST['apikey']==''||$_POST['listid']==''):
          		$errormessage='Please fill all the required field';
          	else:
            $apikey=$_POST['apikey'];
            $listid=$_POST['listid'];
             update_option( 'sendgrid_key', $apikey);
             update_option( 'sendgrid_listid', $listid);
             $message="option opdated sucessfully";
            endif;
             endif;
          ?>
          <?php 
          $integrationkey=get_option( 'sendgrid_key');
          $integration_listid=get_option( 'sendgrid_listid');

        ?>
        <?php if(isset($message)):?>
        <div id="message" class="updated notice notice-success is-dismissible"><p><?php echo $message;?>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>


        <?php endif;?>
        <?php if(isset($errormessage)):?>
        <div id="message" class="updated notice notice-success is-dismissible"><p style="color:red"><?php echo $errormessage;?>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php endif;?>
        <form method="post">

         <table class="form-table">

        <tbody>
         <tr><td colspan="2"><h2>Api Credential</h2></td>
</tr>
       
          <tr class="form-field form-required">

          <th scope="row"><label for="apikey">Sendgrid apikey<span class="description">(required)</span></label></th>

          <td><input type="text" id="apikey" required="" name="apikey" value="<?php echo $integrationkey;?>"></td>

          </tr>

          <tr class="form-field form-required">

        <th scope="row"><label for="listid">Sendgrid list id<span class="description">(required)</span></label></th>

        <td><input type="text" id="listid" required="" name="listid" value="<?php echo $integration_listid;?>"></td>

          </tr>

          </tbody>

          </table>
         <p class="submit" style="float: right; padding-right: 50px;"><input type="submit" name="submit" id="submit" class="button button-primary" value="SUBMIT "></p>
       </form>
         <?php
       }
       ?>