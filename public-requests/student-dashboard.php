<?php

add_shortcode('check_form','check_form' );
function check_form(){
    ob_start();
        echo '<pre>';
        print_r($_SESSION['response_form']);
        echo '</pre>';
    return ob_get_clean();
}

function add_request_type_after_save($form, $post_id){
    $_SESSION['response_form'] = $form;
    $_SESSION['response_form']['paddy'] = $post_id;
    if($form['id'] == 'ab4db48'){
        $updated = update_post_meta( $post_id, 'request_type', 'public' ); //
        $_SESSION['response_form']['updated'] = $updated;
    }
    if($form['id'] == '947f89e'){
        // global $post;
        // $_SESSION['response_form'] = get_queried_object_id();
        // $_SESSION['response_form'] = 2821; //get_the_ID();
        // $tutor_responses = get_post_meta( $form['redirect_params']['tutoring_request_id'], 'tutor_responses', true );
        // if(!empty($tutor_responses)){
        //     $responses = explode(',', $tutor_responses);
        //     $responses[] = $post_id;
        //     $responses_string = implode(',', $responses);
        // }else{
        //     $responses_string = $post_id;
        // }
        
        // update_post_meta( $post_id, 'tutoring_request_id', $form['redirect_params']['tutoring_request_id'] );
    }
}
add_action( 'acf_frontend/save_post', 'add_request_type_after_save', 10, 2);

// bid_responces_student_dashboard

function get_responce_url_fn(){
    $post_id = get_the_ID();
    $request_type = get_post_meta( $post_id, 'request_type', true );

    if($request_type == 'public'){
        return get_permalink( '1384' );
    }else{
        return get_permalink( '1525' );
    }
}
add_shortcode( 'get_responce_url', 'get_responce_url_fn' );


function tutor_box_on_bids_list_fn(){
    $post_id = get_the_ID();
    $tutor_profile_id = get_the_author_meta('profile_id');//get_post_meta( $post_id, 'tutor_profile_id', false );
    $featured_img_url = get_the_post_thumbnail_url($tutor_profile_id, 'full');
    
    if(empty($featured_img_url)){
        $featured_img_url = 'https://knovatek.info/fastgrades/wp-content/uploads/2022/02/default-profile-picture.png';
    }
    $first_name = get_post_meta( $tutor_profile_id , 'first_name', true );
    $last_name = get_post_meta( $tutor_profile_id , 'last_name', true );

    $profile_link = get_permalink( $tutor_profile_id );

    ob_start();
    echo 'Profile ID : '.get_the_author_meta('profile_id');
    ?>

        <div class="tutor_box <?php echo $post_id.' '.$tutor_profile_id; ?>">
            <div class="profile_pic">
                <img src="<?php echo $featured_img_url; ?>" alt="" srcset="">
            </div>
            <div class="profile_details">
                <h4><?php echo $first_name.' '.$last_name; ?></h4>
                <a href="<?php echo $profile_link; ?>">View Profile</a>
            </div>
        </div>

    <?php

    return ob_get_clean();

}
add_shortcode( 'tutor_box_on_bids_list', 'tutor_box_on_bids_list_fn' );

function bid_responces_student_dashboard_callback( $query ) {

    if(!empty($_GET['response_id'])){

        // Get current meta Query
        $meta_query = $query->get( 'meta_query' );

        // If there is no meta query when this filter runs, it should be initialized as an empty array.
        if ( ! $meta_query ) {
            $meta_query = [];
        }

        // Append our meta query
        $meta_query[] = [
            'key' => 'tutoring_request_id',
            'value' => $_GET['response_id'],
            'compare' => '=',
        ];
        $query->set( 'meta_query', $meta_query );
    }
    return $query;
}
add_action( 'elementor/query/bid_responces_student_dashboard', 'bid_responces_student_dashboard_callback' );

add_shortcode( 'bid_responce_decline_button_text', 'bid_responce_decline_button_text_fn' );

function bid_responce_decline_button_text_fn(){
    $status = get_post_meta( get_the_id(), 'bid_status', true );
    if(!empty($status) && $status == 'declined' && $status != 'accepted'){
        return 'Declined';
    }else{
        return 'Decline';
    }    
}

add_shortcode( 'bid_responce_accept_button_text', 'bid_responce_accept_button_text_fn' );
function bid_responce_accept_button_text_fn(){
    $status = get_post_meta( get_the_id(), 'bid_status', true );
    if(!empty($status) && $status == 'accepted' && $status != 'declined'){
        return 'Accepted';
    }else{
        return 'Accept';
    }    
}

add_shortcode( 'public_request_status_student_dashboard', 'public_request_status_student_dashboard_fn' );
function public_request_status_student_dashboard_fn(){
    return get_post_meta( get_the_id(), 'bid_status', true );
}

add_action( 'wp_ajax_update_response_status', 'update_response_status');
add_action( 'wp_ajax_nopriv_update_response_status', 'update_response_status');
function update_response_status(){
    if ( check_ajax_referer( '_ajax_nonce' ) ) {
        $status = $_POST['status'];
        if(!empty($status)){
            update_post_meta( $_POST['response_id'] , 'bid_status', $status );
            
            update_post_meta( $_POST['response_id'], 'cancellation_reason', $_POST['cancelation_reason'] );


            do_action( 'after_update_public_response_status', $_POST );
            // echo 'Status Changed to : '. $status;
            wp_send_json_success( $_POST, 200, 0);
            // wp_die();
        }
    }
}

add_shortcode( 'public_request_form', 'public_request_form' );

function public_request_form(){

    $settings = array(
        'post_id' => 'new_post',
        'new_post'      => array(
            'post_type'     => 'tutoringrequest',
            'post_status'   => 'publish'
        ),
        'post_title' => true,
        'post_content' => true,
        'fields' => array('field_61e548fd6fd3d', 'field_61e5498b6fd3f', 'field_62419ab552304', 'field_62595c625113d', 'field_62595c8f5113e', 'field_62419b9df90ca', 'field_62419afb52305'),
        'uploader' => 'basic',
        'submit_value'  => 'Create new tutoring request'
    );
    acf_form( $settings );


}

add_shortcode( 'tutor_response_form', 'tutor_response_form' );

function tutor_response_form(){

    $settings = array(
        'post_id' => 'new_post',
        'new_post'      => array(
            'post_type'     => 'fastgrade_bidding',
            'post_status'   => 'publish'
        ),
        'post_title' => true,
        'post_content' => false,
        'fields' => array('field_627d167899453','field_61fe769cc19ac','field_61fe7536c19aa', 'field_61fe755cc19ab'),
        'uploader' => 'basic',
        'return' => '?submitted=true',
        'updated_message' => __("Respose Submitted Successfully.", 'acf'),
        'submit_value'  => 'Submit'
    );
    acf_form( $settings );

}

add_action('acf/save_post', 'my_acf_save_post', 5);
function my_acf_save_post( $post_id ) {

    // Get previous values.
    $prev_values = get_fields( $post_id );

    // Get submitted values.
    $values = $_POST['acf'];

    // Check if a specific value was updated.
    if( isset($_POST['acf']['field_627d167899453']) ) {
        // Update post 37
        $my_post = array(
            'ID'           => $post_id,
            'post_content' => $_POST['acf']['field_627d167899453'],
        );

        // Update the post into the database
        wp_update_post( $my_post );
    }
}




function sb_public_req_cancel_modal_fn(){
    ob_start();
     ?>
        <style>
            .pub-req-container{
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
            .sb-modal .sb-header, .sb-modal .content {
                height: auto;
            }
        </style>
        <button class="sb-open" style="text-transform: uppercase;"><?php echo do_shortcode('[bid_responce_decline_button_text]'); ?></button>
        <div class="pub-req-container" id="pub-req-container"></div>
        <div class="sb-modal" id="pub-req-pop-modal">
            <div class="sb-header">
                <a href="#" class="cancel">X</a>
            </div>
            <div class="content">
                <form>
                    <textarea name="public_req_cancel_reason" id="public_req_cancel_reason" cols="20" rows="5" placeholder="Enter cancellation reason" style="margin-bottom:15px;"></textarea>	
                    <button class="public-req-response-btn decline_btn" >Submit</button>					
                </form>
            </div>							
        </div>

        <script>
            jQuery(document).ready(function ($) {
                    $(".sb-open").click(function(){
                    $("#pub-req-container").css("display","block");
                    $("#pub-req-pop-modal").css("display","block");
                });
                $(".cancel").click(function(){
                    $("#pub-req-container").fadeOut();
                    $("#pub-req-pop-modal").fadeOut();
                });
            });
        </script>
        <?php
    
    return ob_get_clean();
}
add_shortcode( 'public_req_cancel_popup', 'sb_public_req_cancel_modal_fn' );


function show_pub_req_cancel_reason_fn(){
    if($reason = get_field('cancellation_reason')){ 
        ob_start();
        ?>
        <h5>Cancellation Reason : <?php echo $reason; ?></h5>

        <?php
        return ob_get_clean();
    }
}
add_shortcode( 'show_pub_req_cancel_reason', 'show_pub_req_cancel_reason_fn' );