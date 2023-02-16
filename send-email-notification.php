<?php


/**
 *  File : select-tutor-private-request.php
 * 
 *  Args
 *  $post_id : Tutoring Request ID
 *  $tutors_with_response : Array of Tutor Profile ID as key and and default response status of respective tutor
 * 
 */
// add_action( 'after_sent_private_request', 'send_email_after_sent_private_request_email', 20, 2 );

function send_email_after_sent_private_request_email($post_id, $tutors){
    foreach($tutors as $tutor_profile_id => $status){
        $tutor_id = get_post_field( 'post_author', $tutor_profile_id );
        $tutor_email = get_the_author_meta('user_email', $tutor_id);

        $subject = 'Student has sent you a private request';
        $message = get_private_request_email();

        wp_mail( $tutor_email, $subject, $message );
    }
}

function get_private_request_email(){
    ob_start();
    ?>

    <!doctype html><html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head><title></title><!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style type="text/css">#outlook a { padding:0; }
          body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
          table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
          img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
          p { display:block;margin:13px 0; }</style><!--[if mso]>
        <noscript>
        <xml>
        <o:OfficeDocumentSettings>
          <o:AllowPNG/>
          <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
        </xml>
        </noscript>
        <![endif]--><!--[if lte mso 11]>
        <style type="text/css">
          .mj-outlook-group-fix { width:100% !important; }
        </style>
        <![endif]--><!--[if !mso]><!--><link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css"><style type="text/css">@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);</style><!--<![endif]--><style type="text/css">@media only screen and (min-width:480px) {
        .mj-column-per-100 { width:100% !important; max-width: 100%; }
      }</style><style media="screen and (min-width:480px)">.moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">@media only screen and (max-width:480px) {
      table.mj-full-width-mobile { width: 100% !important; }
      td.mj-full-width-mobile { width: auto !important; }
    }</style></head><body style="word-spacing:normal;"><div><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;"><tbody><tr><td style="width:200px;"><img height="auto" src="https://fastgrades.net/wp-content/uploads/2022/01/cropped-logo.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="200"></td></tr></tbody></table></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"><p style="border-top:solid 4px #042D59;font-size:1px;margin:0px auto;width:100%;"></p><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 4px #042D59;font-size:1px;margin:0px auto;width:550px;" role="presentation" width="550px" ><tr><td style="height:0;line-height:0;"> &nbsp;
    </td></tr></table><![endif]--></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"><div style="font-family:helvetica;font-size:32px;font-weight:700;line-height:1;text-align:center;color:#134478;">Student has sent you a private request</div></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"><div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:18px;line-height:20px;text-align:center;color:#000000;">A student has sent you a private request. Please make sure you have booking slot available and accept or decline as per your choice.</div></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"><div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:16px;line-height:1;text-align:center;color:#000000;">If you have any questions please <a href="">contact us</a></div></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"><div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:16px;line-height:1;text-align:center;color:#000000;">or send an email to <a href="mailto:admin@fastgrades.net">admin@fastgrades.net</a></div></td></tr><tr><td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tr><td align="center" bgcolor="#134478" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px;background:#134478;" valign="middle"><a href="https://fastgrades.net/tutor-dashboard/private-requests/" style="display:inline-block;background:#134478;color:#ffffff;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank">Click here to view request</a></td></tr></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></div></body></html>

    <?php
    return ob_get_clean();
}


/**
 *  File : student-dashboard.php
 *  
 *  Args
 *  $form : submitted form values
 *        response_id : Response ID
 *        status : Response status (accepted/declined)
 */

// add_action( 'after_update_public_response_status', 'send_email_after_update_public_response_status', 20, 1 );

/**
 *  file : reponses.php
 * 
 *  $arg  
 *  $post_id : response ID
 *  $form : Submitted form data
 *        title : Response Title
 *        content : Response Proposal
 *        request_id : Tutoring Request ID
 *        profile_id : Tutor Profile ID
 *        bid_value : Response Bid Value
 */
// add_action( 'after_response_sent', 'send_email_after_response_sent', 20, 2 );