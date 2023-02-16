<?php

add_shortcode( 'fg_login_form', 'fg_login_form' );
function fg_login_form($atts){

    $args = [
        'echo' => false,
        'label_username' => 'Email Address',
        'remember' => true,
    ];
    return wp_login_form( $args );
}

function fg_login_redirect($url, $request, $user)
{   
    // print_r($url);
    // print_r($user->roles);
    // exit;
    $user_roles = $user->roles;
    $profile_id = get_user_meta( $user->ID, 'profile_id', true);
    $tutor_dashboard_url = home_url('/tutor-dashboard/update-personal-info/');
    $tutor_dashboard = add_query_arg( array('profile_id' => $profile_id ), $tutor_dashboard_url );
    $student_dashboard_url = home_url('/student-dashboard/update-profile');
    $student_dashboard = add_query_arg( array('profile_id' => $profile_id ), $student_dashboard_url );

    // if(!empty($_GET['redirect_to']))
    $redirect_to = '';

    if (in_array('tutor', $user_roles)) {

        // print_r($user->roles);
        // print_r($tutor_dashboard);
        // exit;

        if (empty($redirect_to)) {
            // wp_safe_redirect( $tutor_dashboard );
            $url = $tutor_dashboard;
        } else {
            // wp_safe_redirect( $redirect_to );
            $url = $redirect_to;
        }

    } elseif (in_array('student', $user_roles)) {

        // print_r($student_dashboard);
        // exit;

        if (empty($redirect_to)) {
            // wp_safe_redirect( $student_dashboard );
            $url = $student_dashboard;
        } else {
            $url = $redirect_to;
            // wp_safe_redirect( $redirect_to );
        }

    }
    return $url;
}
add_filter( 'login_redirect', 'fg_login_redirect', 10, 3 );

add_action( 'template_redirect', 'fg_redirect_after_register');
function fg_redirect_after_register(){

    if(is_page( 'user-register' ) && is_user_logged_in() ){
        $user = get_user_by('ID', get_current_user_id());
        $url = '';
        $request = '';
        wp_safe_redirect( fg_login_redirect($url, $request, $user) );
    }
}