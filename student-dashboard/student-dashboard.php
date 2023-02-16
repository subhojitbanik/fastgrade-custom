<?php

/**
 *  change menu links dynamically
 */
function wpse_14405784( $items, $menu, $args) {

    global $current_user;
    $profile_id = get_user_meta( get_current_user_id(), 'profile_id', true );
    // if($menu->name == 'Emp menu'){

        foreach( $items as $item ) {

            if ( $item->title == 'Update Personal Profile' )  {
    
                $item->url = add_query_arg( 'profile_id', $profile_id, $item->url );
            }

            // if ( $item->title == 'My Profile' )  {
    
            //     $item->url = get_permalink( $profile_id );
            // }

        }
        return $items;

    // }
    
}
add_filter( 'wp_get_nav_menu_items','wpse_14405784', 11, 3 );