<?php

function select_tutor_footer_code(){

    if( is_single() && 'tutor_profiles' == get_post_type() && is_user_logged_in() ){

        $count = ($_SESSION['tutor_cart']['total_count']) ? $_SESSION['tutor_cart']['total_count'] : 0;
        $select_tutor = (!in_array(get_the_ID(), $_SESSION['tutor_cart']['profile_ids'])) ? 'Select Tutor' : 'Selected';
        echo '
            <div id="tutor_cart">
                <button id="select_tutor_float_button">'. $select_tutor .'<span class="selected_tutor" >'. $count . ' </span></button>
                <button id="submit_private_request"><a href="'.home_url( 'send-private-request').'">Create Request</a></button>
            </div>      
        ';

        echo '
        <script>
            jQuery(document).ready(function($) {
                $("button#select_tutor_float_button").click(() => {
                    $.ajax({
                        type: "POST",
                        url: "'. admin_url( "admin-ajax.php" ). '",
                        data: {
                            _ajax_nonce: "'. wp_create_nonce( "_ajax_nonce" ) .'",
                            profile_id: ' . get_the_ID(). ',
                            action: "select_tutor",
                        },
                        success: (res) => {
                            console.log(res);
                            $("#select_tutor_float_button").html(\'Selected <span class="selected_tutor" >\' + res.data.total_count + \'</span>\');
                            $("#after_select_tutor_popup").css("opacity","1");
                            $("#after_select_tutor_popup").css("bottom","90px");
                        }
                    });
                });
            });
        </script>
        ';

    }

    if(is_page( 'send-private-request' )){
        echo '
        <script>
            jQuery(document).ready(function($) {
                $(".remove_tutor").click(function(e){
                    var profile_id = $(this).closest("article").attr("id");
                    $.ajax({
                        type: "POST",
                        url: "'. admin_url( "admin-ajax.php" ). '",
                        data: {
                            _ajax_nonce: "'. wp_create_nonce( "_ajax_nonce" ) .'",
                            profile_id: profile_id.replace("post-",""),
                            action: "remove_tutor",
                        },
                        success: (res) => {
                            console.log(res);
                            $("#post-" + res.data.removed_profile_id).css("transition","all 0.5sec ease");
                            $("#post-" + res.data.removed_profile_id).css("display","none");
                            $("#post-" + res.data.removed_profile_id).remove();
                            var count = $("#selected_tutors .elementor-posts-container article").length;
                            console.log(count);
                            if(count <= 0){
                                var ele = "<div class=\'selected_tutor_empty\'><p class=\'text-align-center\'>You have not selected any tutor, Please click below link to find more tutors.</p><a href=\'/tutor_profiles/\'>Find A Tutor</a></div>";
                                $("#selected_tutors .elementor-posts-container").empty();
                                $("#selected_tutors .elementor-posts-container").html(ele);
                            }
                        }
                    });
                });
            });
        </script>
        ';
    }

}
add_action( 'wp_footer', 'select_tutor_footer_code', 100 );

function add_selected_tutor_to_seesion(){

    if ( check_ajax_referer( '_ajax_nonce' ) ) {

        if(!is_array($_SESSION['tutor_cart']['profile_ids'])){
            $_SESSION['tutor_cart']['profile_ids'] = array();
        }

        if(!in_array($_POST['profile_id'], $_SESSION['tutor_cart']['profile_ids'])){
            $_SESSION['tutor_cart']['profile_ids'][] = $_POST['profile_id'];
            $_SESSION['tutor_cart']['total_count'] = count($_SESSION['tutor_cart']['profile_ids']);
            wp_send_json_success($_SESSION['tutor_cart'], 200, 0);
        }else{
            wp_send_json_success($_SESSION['tutor_cart']);
        }
        
        // $data['status_msg'] = 'success';
    }

}
add_action('wp_ajax_select_tutor', 'add_selected_tutor_to_seesion');
add_action('wp_ajax_nopriv_select_tutor', 'add_selected_tutor_to_seesion');

function remove_selected_tutor_from_session(){

    if ( check_ajax_referer( '_ajax_nonce' ) ) {

        if(in_array($_POST['profile_id'], $_SESSION['tutor_cart']['profile_ids'])){
            if (($key = array_search($_POST['profile_id'], $_SESSION['tutor_cart']['profile_ids'])) !== false) {
                unset($_SESSION['tutor_cart']['profile_ids'][$key]);
            }
            $_SESSION['tutor_cart']['total_count'] = count($_SESSION['tutor_cart']['profile_ids']);
            $_SESSION['tutor_cart']['removed_profile_id'] = $_POST['profile_id'];
            wp_send_json_success($_SESSION['tutor_cart'], 200, 0);
        }else{
            wp_send_json_success($_SESSION['tutor_cart']);
        }
        
        // $data['status_msg'] = 'success';
    }

}
add_action('wp_ajax_remove_tutor', 'remove_selected_tutor_from_session');
add_action('wp_ajax_nopriv_remove_tutor', 'remove_selected_tutor_from_session');

function tutor_responce_private_request(){

    if ( check_ajax_referer( '_ajax_nonce' ) ) {

        $tutor_responses = get_post_meta( $_POST['request_id'], 'selected_tutors', true );

        $tutor_responses[get_profile_id()] = $_POST['status'];

        update_post_meta( $_POST['request_id'], 'selected_tutors', $tutor_responses );
        update_post_meta( $_POST['request_id'], 'cancellation_reason', $_POST['cancelation_reason'] );

        
        do_action( 'after_update_private_response_status', $_POST, get_profile_id() );
        
        wp_send_json_success( $_POST, 200, 0);
    }

}
add_action('wp_ajax_tutor_responce_private_request', 'tutor_responce_private_request');
add_action('wp_ajax_nopriv_tutor_responce_private_request', 'tutor_responce_private_request');

//selected_tutor_private_request

/**
 * Update the posts widget or portfolio widget query.
 *
 * @since 1.0.0
 * @param \WP_Query $query The WordPress query instance.
 */
function selected_tutor_private_request_callback( $query ) {

    if(!empty($_SESSION['tutor_cart']['profile_ids'])){
        $query->set( 'post__in', $_SESSION['tutor_cart']['profile_ids'] );
    }else{
        $query->set( 'post__in', array('0') );
        echo '
        <div class="selected_tutor_empty">
            <p class="text-align-center">You have not selected any tutor, Please click below link to find more tutors.</p>
            <a href="/tutor_profiles/">Find A Tutor</a>
        </div>
        ';
    }

    return $query;

}
add_action( 'elementor/query/selected_tutor_private_request', 'selected_tutor_private_request_callback' );

/**
 * Update the posts widget or portfolio widget query.
 *
 * @since 1.0.0
 * @param \WP_Query $query The WordPress query instance.
 */
function tutor_profiles_private_requests_callback( $query ) {

    $request_id = $_GET['response_id'];
    $profile_ids_with_response = get_post_meta( $request_id, 'selected_tutors', true );
    asort($profile_ids_with_response);
    $profile_ids = array_keys($profile_ids_with_response);

    if(is_array($profile_ids))
        $query->set( 'post__in', $profile_ids );

        $query->set( 'orderby', 'post__in' );
    $_SESSION['test_profile_ids'] = $profile_ids;
    return $query;

}
add_action( 'elementor/query/tutor_profiles_private_requests', 'tutor_profiles_private_requests_callback' );

/**
 * Update the posts widget or portfolio widget query.
 *
 * @since 1.0.0
 * @param \WP_Query $query The WordPress query instance.
 */
function all_private_requests_callback( $query ) {

    // if(!empty($_GET['request'])){

        // Get current meta Query
        // $meta_query = $query->get( 'meta_query' );

        // If there is no meta query when this filter runs, it should be initialized as an empty array.
        // if ( ! $meta_query ) {
        //     $meta_query = [];
        // }

        // // Append our meta query
        // $meta_query[] = [
        //     'key' => 'request_type',
        //     'value' => 'private',
        //     'compare' => '=',
        // ];

        // $meta_query[] = [
        //     'key' => 'selected_tutors',
        //     'value' => ['357'],
        //     'compare' => 'LIKE',
        // ];
        remove_action( 'elementor/query/all_private_requests', 'all_private_requests_callback' );
        $ids = get_request_ids_for_tutor();
        if(!empty($ids)){
        $query->set( 'post__in', $ids );
        }else{
            $query->set( 'post__in', array('0') );
            echo '
            <div class="selected_tutor_empty">
                <p class="text-align-center">No privete requests found.</p>
            </div>
            ';
        }
        // $query->set( 'meta_query', $meta_query );
    // }
    return $query;
}
add_action( 'elementor/query/all_private_requests', 'all_private_requests_callback' );

add_shortcode('request_ids','get_request_ids_for_tutor' );
function get_request_ids_for_tutor(){
    $request_ids = [];
    $args = array(
        'post_type' => 'tutoringrequest',
        'meta_key' => 'request_type',
        'meta_value' => 'private',
        'meta_compare' => '=',
        'numberposts' => -1
    );
    $requests = get_posts($args);

    // print_r($requests);
    // wp_reset_postdata();
    // exit();
    $profile_id = get_user_meta(get_current_user_id(),'profile_id',true );
    foreach($requests as $request){
        $tutor_responses = get_post_meta( $request->ID, 'selected_tutors', true );
        if(is_array($tutor_responses) && array_key_exists($profile_id,$tutor_responses)){
            $request_ids[] = $request->ID;
        }
    }

    // print_r($request_ids);
    // wp_reset_postdata();

    return $request_ids;
}

function set_viewing_profile_id(){
    $_SESSION['viewing_profile_id'] = get_the_id();
}
add_action( 'wp_content', 'set_viewing_profile_id' );

function update_private_request_after_save($form, $post_id){

    $tutors_with_response = array();
    foreach( $_SESSION['tutor_cart']['profile_ids'] as $profile_id ){
        $tutors_with_response[$profile_id] = 'Pending';
    }
    if($form['id'] == '85885e7'){
        update_post_meta( $post_id, 'request_type', 'private' );
        update_post_meta( $post_id, 'selected_tutors',  $tutors_with_response );
        do_action('after_sent_private_request', $post_id, $tutors_with_response);
    }
}
add_action( 'acf_frontend/save_post', 'update_private_request_after_save', 10, 2);

function private_request_status($atts){

    $request_id = $_GET['response_id'];

    $tutor_responses = get_post_meta( $request_id, 'selected_tutors', true );

    if(is_array($tutor_responses)){
        return $tutor_responses[get_the_ID()];
    }

}
add_shortcode( 'private_request_status', 'private_request_status' );

add_shortcode( 'private_request_accept_button_text', 'private_request_accept_button_text_fn' );
function private_request_accept_button_text_fn(){

    $status = get_post_meta( get_the_id(), 'selected_tutors', true );
    $tutor_id = get_profile_id();

    if(!empty($status[$tutor_id]) && $status[$tutor_id] == 'accepted' && $status[$tutor_id] != 'declined'){
        return 'accepted';
    }else{
        return 'accept';
    }    
}

add_shortcode( 'private_request_decline_button_text', 'private_request_decline_button_text_fn' );
function private_request_decline_button_text_fn(){

    $status = get_post_meta( get_the_id(), 'selected_tutors', true );
    $tutor_id = get_profile_id();

    if(!empty($status[$tutor_id]) && $status[$tutor_id] == 'declined' && $status[$tutor_id] != 'accepted'){
        return 'declined';
    }else{
        return 'decline';
    }    
}   

function private_request_status_tutor_dashboard($atts){

    $tutor_responses = get_post_meta( get_the_ID(), 'selected_tutors', true );

    if(is_array($tutor_responses)){
        return $tutor_responses[get_profile_id()];
    }

}
add_shortcode( 'private_request_status_tutor_dashboard', 'private_request_status_tutor_dashboard' );

add_action( 'wp_footer', 'after_select_tutor_popup' );
function after_select_tutor_popup(){
    echo '
        <div id="after_select_tutor_popup">
            <p>Want to select more tutors? <a href="/tutor_profiles/">Click Here</a></p>
        </div>
    ';
}

add_shortcode( 'send_booking_req_private_link', 'send_booking_req_private_link_fn' );
function send_booking_req_private_link_fn(){
    return '/tutor-dashboard/tutors-tutoring-session-timetable/?request_id=' . get_the_id();
}


function sb_priv_req_cancel_modal_fn(){
    ob_start();
    $check =  do_shortcode( "[get_booking_status tutoring_req='yes']");
    //echo $check;
    if($check <= 1){ ?>
        <style>
            .priv-req-container{
                display: none;
                width: 100%;
                height: 100vh;
                position: fixed;
                opacity: 0.9;
                background: transparent;
                z-index: 40000;
                top:0;
                left: 0;
                overflow: hidden;
                animation-name: fadeIn_Container;
                animation-duration: 1s;
            }
            .sb-modal{
                display:none;
                top: 30%;
                right: 15%;
                min-width: 250px;
                margin: 0 auto;
                position: fixed;
                z-index: 40001;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0px 0px 10px #000;
                margin-top: 30px;
                animation-name: fadeIn_Modal;
                animation-duration: 0.8s;
            }
        </style>
        <button class="sb-open" style="text-transform: uppercase;"><?php echo do_shortcode('[private_request_decline_button_text]'); ?></button>
        <div class="priv-req-container" id="priv-req-container"></div>
        <div class="sb-modal" id="priv-req-pop-modal">
            <div class="sb-header">
                <a href="#" class="cancel">X</a>
            </div>
            <div class="content">
                <form>
                    <textarea name="priv_req_cancel_reason" id="priv_req_cancel_reason" cols="20" rows="5" placeholder="Enter cancellation reason" style="margin-bottom:15px;"></textarea>	
                    <button class="private-req-response-btn decline_btn" >Submit</button>					
                </form>
            </div>							
        </div>

        <script>
            jQuery(document).ready(function ($) {
                    $(".sb-open").click(function(){
                    $("#priv-req-container").css("display","block");
                    $("#priv-req-pop-modal").css("display","block");
                });
                $(".cancel").click(function(){
                    $("#priv-req-container").fadeOut();
                    $("#priv-req-pop-modal").fadeOut();
                });
            });
        </script>
        <?php
    }
    return ob_get_clean();
}
add_shortcode( 'private_req_cancel_popup', 'sb_priv_req_cancel_modal_fn' );

function show_priv_req_cancel_reason_fn(){
    $request_id = $_GET['response_id'];
    if($reason = get_field('cancellation_reason',$request_id)){ 
        ob_start();
        ?>
        <h5>Cancellation Reason : <?php echo $reason; ?></h5>

        <?php
        return ob_get_clean();
    }
}
add_shortcode( 'show_priv_req_cancel_reason', 'show_priv_req_cancel_reason_fn' );





?>

