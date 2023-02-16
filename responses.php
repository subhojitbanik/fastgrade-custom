<?php

class Responses {

    private $message;

    public function __construct() {
        
        add_shortcode( 'response_form', array($this, 'add') );
        add_shortcode( 'get_action_btns_responce', array($this, 'get_action_buttons') );
        // add_action( 'admin_ajax_create_response', array($this, 'create') );
        // add_action( 'admin_ajax_priv_create_response', array($this, 'create') );
        add_action( 'template_redirect', array($this, 'create'), 10 );
        add_action( 'elementor/query/my_response_public_request', array( $this, 'my_response_public_request') );

    }

    public function add()
    {
        ob_start();
            echo sb_hide_bid_form();

            if(!empty( get_user_meta( get_current_user_id() , 'stripe_acc_id', true ) )){
            echo '<div class="fastgrade">';
            if( filter_input( INPUT_GET, 'success' ) === 'true' ){
                echo '<p>Your response has been submitted successfully</p>';
            }else{
                echo '<p>'.$this->message.'</p>';
            }

            ?>  <p class="sb_notie" style="display:none;">The response to this tutoring request has reached its limits.</p>
                <div class="response_form_wrapper">
                    <form action="" method="post">
                        <div class="field_wrapper">
                            <label for="title">Response Title*</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        <div class="field_wrapper">
                            <label for="content">Response Proposal*</label>
                            <textarea name="content" id="content" cols="30" rows="10" required></textarea>
                        </div>
                        <div class="field_wrapper">
                            <label for="bid_value">Response Values (in $/hr.)*</label>
                            <input type="number" name="bid_value" id="bid_value" required>
                        </div>
                        <input type="hidden" name="request_id" value="<?php echo get_the_ID(); ?>">
                        <input type="hidden" name="profile_id" value="<?php echo get_user_meta( get_current_user_id(), 'profile_id', true ); ?>">

                        <input type="submit" value="Submit Response" name="submit_response">
                    </form>
                </div>
            <?php
            echo '</div>';
        }else{
            echo '<p style="color: #fff; font-weight: 600;">Please create payment account before you start submitting the response</p>';
            echo '<a href="'.home_url('tutor-dashboard/payments' ).'" class="fgpd-stripe-btn btn-yellow">Create Payment Account</a>';
        }

        return ob_get_clean();
    }

    public function create()
    {
        if(isset($_POST['submit_response']) && !empty($_POST['title']) && !empty($_POST['content']) && !empty($_POST['bid_value'])){

            $args = array(
                'post_title' => sanitize_text_field( $_POST['title'] ),
                'post_content' => sanitize_textarea_field( $_POST['content'] ),
                'post_author' => get_current_user_id(),
                'post_status' => 'publish',
                'post_type' => 'fastgrade_bidding',
            );

            $post_id = wp_insert_post($args);

            if(!is_wp_error($post_id)){

                //the post is valid
                update_post_meta( $post_id, 'tutoring_request_id', $_POST['request_id'] );
                update_post_meta( $post_id, 'tutor_profile_id', $_POST['profile_id'] );
                update_post_meta( $post_id, 'bid_value', $_POST['bid_value'] );
                update_post_meta( $post_id, 'bid_status', 'pending' );
                update_post_meta( $post_id, 'booking_request_status', 'notsent' );

                do_action( 'after_response_sent', $post_id, $_POST );

                // $this->message = 'Your response have been submitted successfully';
                $redirect = add_query_arg( 'success', 'true', get_permalink() );
                wp_redirect( $redirect );
                exit;
            
            }else{

                //there was an error in the post insertion, 
                $this->message = $post_id->get_error_message();
                // wp_die();

            }
        }

    }

    //my_response_public_request
    public function my_response_public_request($query)
    {
        if(!empty($_GET['response_status'])){

            // Get current meta Query
            $meta_query = $query->get( 'meta_query' );
    
            // If there is no meta query when this filter runs, it should be initialized as an empty array.
            if ( ! $meta_query ) {
                $meta_query = [];
            }
            
            // $compare = ( $_GET['response_status'] == 'accepted' ) ? '=' : '!=';
            // Append our meta query
            $meta_query[] = [
                'key' => 'bid_status',
                'value' => $_GET['response_status'],
                'compare' => $compare,
            ];
    
            $query->set( 'meta_query', $meta_query );
        }
        $query->set( 'author',  get_current_user_id());
        return $query;
    }

    public function get_action_buttons($atts)
    {
        ob_start(); 

        $request_id = get_post_meta( get_the_id(), 'tutoring_request_id', true );
        $bid_status = get_post_meta( get_the_id(), 'bid_status', true );
        $response_id = get_the_ID();

        if(0 == fg_pd_get_booking_status( ['bidding' => 'yes'] ) && 'accepted' == $bid_status){

            $booking_link = home_url().'/tutor-dashboard/tutors-tutoring-session-timetable/?request_id='. $request_id .'&responce_id='.$response_id;
            ?>
                <a href="<?php echo $booking_link; ?>" class="btn-blue send_tutoring_req response_action_btn">Send Booking Request</a>
            <?php

        }
        if( 'accepted' != $bid_status ){

            $edit_link = home_url().'/tutor-dashboard/bids-responses-accepted-denied/edit-bid-response?response_id='.$response_id;
            ?>
                <a href="<?php echo $edit_link; ?>" class="btn-green edit_response response_action_btn">Edit Response</a>
            <?php
            
        }
        if(!empty( $request_id )){
            ?>
                <a href="<?php echo get_the_permalink($request_id); ?>" class="btn-yellow view_tutoring_request response_action_btn">View Tutoring Request</a>
            <?php
        }
        return ob_get_clean();
    }

}

function sb_hide_bid_form(){
    //$request_id = get_the_ID(  );
  
      $args = array(
      'posts_per_page' => -1,
      'post_type' => 'fastgrade_bidding',
      //'author' => get_current_user_id(),
      'meta_key' => 'tutoring_request_id',
      'meta_compare' => '=',
      'meta_value'  => get_the_ID()
      );
  
      //$bids = get_posts($args);
      $query = new WP_Query( $args );
      $count = $query->post_count;
  
      //print_r($count);
      if( $count >= 7 ){
        //return'true';
        ob_start();
        ?>
            <style>
                .response_form_wrapper{
                    display:none !important;
                }
                .sb_notie{
                    display:block !important;
                }
            </style>
        <?php
            return ob_get_clean();
        }
}
