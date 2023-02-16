<?php


/**
 *  Page :  My responses to public request
 *  Tutoring request title for which the tutor bid
 */
add_shortcode( 'request_title_on_bid_responce_tutor_dashboard', 'request_title_on_bid_responce_tutor_dashboard' );
function request_title_on_bid_responce_tutor_dashboard(){
    $tutoring_request_id = get_post_meta( get_the_id(), 'tutoring_request_id', true );
    return get_the_title( $tutoring_request_id );
}

/**
 *  Page :  My responses to public request
 *  Tutoring request link to view
 */
add_shortcode( 'get_tutoring_req_link_bid_response', 'get_tutoring_req_link_bid_response' );
function get_tutoring_req_link_bid_response(){
    $tutoring_request_id = get_post_meta( get_the_id(), 'tutoring_request_id', true );
    return get_the_permalink( $tutoring_request_id );
}