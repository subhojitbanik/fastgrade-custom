<?php

function pdsc_post_title_fn($atts){

    $atts = shortcode_atts( array(
        'id' => '',
        'slug'  => '',
    ), $atts );

    // $att = '';
    // foreach($atts as $att_key, $value){
    //     if(!empty($value)){
    //         $att = $att_key;
    //     }
    // }

    // if($att == 'param'){
    //     $att 
    // }

    $title = get_the_title($_GET['response_id']);

    return $title;
}
add_shortcode( 'pdsc_post_title', 'pdsc_post_title_fn' );

function pdsc_post_meta_fn($atts){

    $atts = extract(shortcode_atts( array(
        'key' => '',
        'post_id'  => get_the_id(),
        'single' => 'false'
    ), $atts ));


    $post_meta = get_post_meta( $_GET['response_id'], $key, $single );

    return $post_meta;
}
add_shortcode( 'pdsc_post_meta', 'pdsc_post_meta_fn' );

function pdsc_user_fn($atts){

    $atts = extract(shortcode_atts( array(
        'key' => '',
        'user'  => 'current',
        'single' => 'false'
    ), $atts ));

    if($user == 'current'){
        $user = get_current_user_id();
    }elseif($user == 'author'){
        $user = get_the_author_ID();
    }else{
        return 'Specify the user type [pdsc_user user="author/current"]';
    }

    $user_info = get_userdata($user);

    return $user_info->$key;

}
add_shortcode( 'pdsc_user', 'pdsc_user_fn' );