<?php

add_action('save_post_tutoringrequest', 'fg_generate_reference_id', 10, 3 );

function fg_generate_reference_id( $post_id, $post, $update ){

    // bail out if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // If an old post is being updated, exit
    if ( $update ) {
        return;
    }

    $random_id = mt_rand(1000, 9999);

    update_post_meta( $post_id, 'reference_id', $post_id.$random_id );

}