<?php

add_shortcode('wc_change_pwd_form', 'wc_change_pwd_form_callback');

  function wc_change_pwd_form_callback() {
      ob_start();
      if (is_user_logged_in()) {

          global $changePasswordError, $changePasswordSuccess;

          if (!empty($changePasswordError)) {
              ?>
              <div class="alert alert-danger">
                  <?php echo $changePasswordError; ?>
              </div>
          <?php } ?>

          <?php if (!empty($changePasswordSuccess)) { ?>
              <br/>
              <div class="alert alert-success">
                  <?php echo $changePasswordSuccess; ?>
              </div>
          <?php } ?>

          <form method="post" class="wc-change-pwd-form">

              <div class="change_pwd_form">

                  <div class="log_pass">
                      <label for="user_password">New Password</label>
                      <div style="position: relative;">
                          <input type="password" name="user_password" id="user_password" />
                      </div>
                  </div>

                  <div class="log_pass">
                      <label for="user_cpassword">Confirm Password</label>
                      <div style="position: relative;">
                        <input type="password" name="user_cpassword" id="user_cpassword" />
                    </div>
                  </div>

                  <div class="log_pass">
                      <?php
                      ob_start();
                      do_action('password_reset');
                      echo ob_get_clean();
                      ?>
                  </div>

                  <div class="log_user">
                      <?php wp_nonce_field('changePassword', 'formType'); ?>
                      <button type="submit" class="register_user">Submit</button>
                  </div>

              </div>
          </form>
          <?php
      }
      $change_pwd_form = ob_get_clean();
      return $change_pwd_form;
  }

  add_action('wp', 'wc_user_change_pwd_callback');

  function wc_user_change_pwd_callback() {

      if (isset($_POST['formType']) && wp_verify_nonce($_POST['formType'], 'changePassword')) {
          global $changePasswordError, $changePasswordSuccess;

          $user = wp_get_current_user();

          $changePasswordError = '';
          $changePasswordSuccess = '';
        //   $u_opwd = trim($_POST['user_opassword']);
          $u_pwd = trim($_POST['user_password']);
          $u_cpwd = trim($_POST['user_cpassword']);

          if ($u_pwd == '' || $u_cpwd == '') {
              $changePasswordError .= '<strong>ERROR: </strong> Enter Password.,';
          }

        //   if (!wp_check_password($u_opwd, $user->data->user_pass, $user->ID)) {
        //       $changePasswordError .= '<strong>ERROR: </strong> Old Password wrong.,';
        //   }

          if ($u_pwd != $u_cpwd) {
              $changePasswordError .= '<strong>ERROR: </strong> Password are not matching.,';
          }

          if (strlen($u_pwd) < 7) {
              $changePasswordError .= '<strong>ERROR: </strong> Use minimum 7 character in password.,';
          }

          $changePasswordError = trim($changePasswordError, ',');
          $changePasswordError = str_replace(",", "<br/>", $changePasswordError);

          if (empty($changePasswordError)) {
              wp_set_password($u_pwd, $user->ID);

              wp_set_current_user($user->ID, $user->user_login);
              wp_set_auth_cookie($user->ID);
              do_action('wp_login', $user->user_login);
              $changePasswordSuccess = 'Password is successfully updated.';
          }
      }
  }