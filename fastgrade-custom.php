<?php

/**
 * Plugin Name: Fastgrade Custom Code
 * Author: Knovatek
 * Description: Fastgrade Custom Code
 */

require(plugin_dir_path(__FILE__) . '/select-tutor-private-request.php');
require(plugin_dir_path(__FILE__) . '/post-type-fields-shortcodes.php');
require(plugin_dir_path(__FILE__) . '/public-requests/student-dashboard.php');
require(plugin_dir_path(__FILE__) . '/public-requests/tutor-dashboard.php');
require(plugin_dir_path(__FILE__) . '/student-dashboard/student-dashboard.php');
require(plugin_dir_path(__FILE__) . '/fastgrades-search.php');
require(plugin_dir_path(__FILE__) . '/generate-ids.php');
require(plugin_dir_path(__FILE__) . '/login.php');
require(plugin_dir_path(__FILE__) . '/change-password.php');
require(plugin_dir_path(__FILE__) . '/send-new-user-email.php');
 require(plugin_dir_path(__FILE__) . '/acf_lang_taxonmy.php');
// require(plugin_dir_path(__FILE__) . '/send-email-notification.php');

require(plugin_dir_path(__FILE__) . '/responses.php');
new Responses();

require(plugin_dir_path(__FILE__) . '/request.php');
new Request();


function fastgrades_init_session()
{
    if (!session_id()) {
        session_start();
    }
}
// Start session on init hook.
add_action('init', 'fastgrades_init_session');

function get_profile_id($user_id='')
{
    if(!empty($user_id)){
        $user = $user_id;
    }else{
        $user = get_current_user_id();
    }
    return get_user_meta($user, 'profile_id', true);
}

add_filter('template_include', 'cp_acf_load_form_head', 99);
function cp_acf_load_form_head($template)
{
    acf_form_head();
    return $template;
}

function themeslug_enqueue_script()
{

    // wp_enqueue_style( 'tui-calendar', 'https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css');
    // wp_enqueue_style( 'tui-date-picker', 'https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css');
    // wp_enqueue_style( 'tui-time-picker', 'https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css');

    // wp_enqueue_script( 'tui-calendar-code-snippet', 'https://uicdn.toast.com/tui.code-snippet/v1.5.2/tui-code-snippet.min.js', array('jquery'), '', true );
    // wp_enqueue_script( 'tui-time-picker', 'https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.min.js', array('jquery'), '', true );
    // wp_enqueue_script( 'tui-date-picker', 'https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.min.js', array('jquery'), '', true );
    // wp_enqueue_script( 'tui-calendar', 'https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js', array('jquery','tui-calendar-code-snippet'), '', true );
    wp_enqueue_style('datatablecss', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
    wp_enqueue_style('fastgrade-custom', plugin_dir_url(__FILE__) . 'style.css');

    wp_enqueue_script('fastgrade-custom', plugin_dir_url(__FILE__) . 'script.js', array('jquery', 'acf-input'), '', true);
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true);
    wp_localize_script('fastgrade-custom', 'fastgrade_custom', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        '_ajax_nonce' => wp_create_nonce("_ajax_nonce"),
    ));
}

add_action('wp_enqueue_scripts', 'themeslug_enqueue_script');

add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar()
{
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

add_shortcode('is_user_role', 'is_user_role_fn');
function is_user_role_fn($atts)
{

    $user = new WP_User(get_current_user_id());
    $atts =  shortcode_atts(array(
        'role' => 'administator',
    ), $atts, 'is_user_role');

    if (!empty($user->roles) && is_array($user->roles) && in_array($atts['role'], $user->roles)) {
        return 'true';
    }
}

add_shortcode('logout_url', 'logout_url_fn');
function logout_url_fn()
{
    return wp_logout_url();
}

add_action('wp_logout', 'auto_redirect_after_logout');
function auto_redirect_after_logout()
{
    wp_safe_redirect(home_url('signin'));
    exit;
}

add_action('acf_frontend/save_user', 'create_user_profile', 10, 2);

function create_user_profile($form, $user_id)
{

    $user_data = $form['record']['fields']['user'];
    $user_post_type = ($user_data['role']['value'] == 'tutor') ? 'tutor_profiles' : 'student_profile';

    // if($user_data['role']['value'] == 'tutor'){
    $post_array = array(
        'post_title' => $user_data['first_name']['value'] . ' ' . $user_data['last_name']['value'],
        'post_content' => 'Write about yourself',
        'post_author'  => $user_id,
        'post_status' => 'draft',
        'post_type' => $user_post_type
    );
    $profile_id = wp_insert_post($post_array);


    do_action( 'fg_after_create_profile', $user_id );
    
    if (!empty($profile_id)) {
        update_post_meta($profile_id, 'first_name', $user_data['first_name']['value']);
        update_post_meta($profile_id, 'last_name', $user_data['last_name']['value']);
        update_post_meta($profile_id, 'email', $user_data['user_email']['value']);
        update_user_meta($user_id, 'profile_id', $profile_id);
    }
    // }
    unset($_SESSION['form']);
    $_SESSION['form'] = $user_data['first_name']['value'] . ' ' . $user_data['last_name']['value'];
}

// update tutor profile
add_action('template_redirect', 'update_profile_url');
function update_profile_url()
{
    $profile_complete = get_post_meta(get_profile_id(),'profile_complete',true );
    global $current_user;
    global $post;
	$user_roles = $current_user->roles;

    if( !is_user_logged_in() && ( 330 == $post->post_parent || 8 == $post->post_parent) ){
        wp_redirect(home_url('/signin')); //https://fastgrades.net/signin/
        exit();
    }

    if( !in_array('administrator', $user_roles) && is_user_logged_in() && !$profile_complete && ( 330 == $post->post_parent || 8 == $post->post_parent) && !( is_page('update-profile') || is_page('update-personal-info')) ){
        if(in_array('student', $user_roles)){
            wp_redirect(home_url('/student-dashboard/update-profile/?profile_id=' . get_profile_id()));
            exit();
        }elseif(in_array('tutor', $user_roles)){
            wp_redirect(home_url('/tutor-dashboard/update-personal-info/?profile_id=' . get_profile_id()));
            exit();
        }
    }

    if (in_array('student', $user_roles) && empty($_GET['profile_id']) && is_page('update-profile')) {
        wp_redirect(home_url('/student-dashboard/update-profile/?profile_id=' . get_profile_id()));
        exit();
    }

    if (in_array('tutor', $user_roles) && empty($_GET['profile_id']) && is_page('update-personal-info') ) {
        wp_redirect(home_url('/tutor-dashboard/update-personal-info/?profile_id=' . get_profile_id()));
        exit();
    }

}
/**
 *  Update to user profile complete
 * 
 */

add_action( 'acf_frontend/save_post', 'update_profile_complete', 10, 2);

function update_profile_complete($form, $post_id){
    if($form['id'] == 'ca91515' || $form['id'] == '7b87e13'){ //7b87e13
        update_post_meta( $post_id, 'profile_complete', 1 );
    }
}
function example_filter_select($options)
{
    // (maybe) modify $options.
    return $options;
}
add_filter('elementor_form_select_filter_field_id', 'example_filter_select');

// function wpse16119876_init_session() {
//     if ( ! session_id() ) {
//         session_start();
//     }
// }
// // Start session on init hook.
// add_action( 'init', 'wpse16119876_init_session' );

function cp_test_form()
{

    ob_start();
    echo 'Testing acf_field';
    echo '<pre>';
    print_r($_SESSION['acf_frontend_form']);
    echo '</pre>';
    return ob_get_clean();
}
add_shortcode('test_form', 'cp_test_form');

function custom_all_requests_callback($query)
{

    if (!empty($_GET['request'])) {

        // Get current meta Query
        $meta_query = $query->get('meta_query');

        // If there is no meta query when this filter runs, it should be initialized as an empty array.
        if (!$meta_query) {
            $meta_query = [];
        }

        // Append our meta query
        $meta_query[] = [
            'key' => 'request_type',
            'value' => $_GET['request'],
            'compare' => '=',
        ];

        $query->set('meta_query', $meta_query);
    }
    $query->set('author',  get_current_user_id());
    return $query;
}
add_action('elementor/query/all_requests', 'custom_all_requests_callback');


function my_acf_load_tutoring_request_id($field)
{

    $field['default_value'] = get_the_ID();
    return $field;
}
add_filter('acf/load_field/name=tutoring_request_id', 'my_acf_load_tutoring_request_id');

function my_acf_load_tutor_profile_id($field)
{

    $field['default_value'] = get_user_meta(get_current_user_id(), 'profile_id', true);
    return $field;
}
add_filter('acf/load_field/name=tutor_profile_id', 'my_acf_load_tutor_profile_id');


// add_action('acf_frontend/save_post', 'my_acff_save_post', 10, 2);
function my_acff_save_post($form, $post_id)
{

    $_SESSION['acf_frontend_form'] = $form;
    // if($form['id'] == 'bb0e754'){
    //     update_post_meta( '', $meta_key:string, $meta_value:mixed, $prev_value:mixed );
    // }
}


add_shortcode('send_booking_req_public_link', 'send_booking_req_public_link_fn');
function send_booking_req_public_link_fn()
{
    return '/tutor-dashboard/tutors-tutoring-session-timetable/?request_id=' . get_post_meta(get_the_id(), 'tutoring_request_id', true). '&responce_id=' . get_the_id();
}


function dfx_mime_type($mimes)
{
    $mimes['dfx'] = 'image/x-dfx';
    return $mimes;
}
add_filter('upload_mimes', 'dfx_mime_type');

add_shortcode('response_form_conditional', 'response_form_conditional');
function response_form_conditional($atts)
{

    if (is_user_logged_in()) {

        global $current_user;
        $user_roles = $current_user->roles;
        $is_private = get_field('request_type');

        if (in_array('tutor', $user_roles) && $is_private != 'private' && !is_tutor_bid()) {
            return 'show_response_form hide_login_message hide_responded_message';
        } elseif (in_array('tutor', $user_roles) && $is_private == 'private') {
            return 'hide_response_form hide_login_message hide_responded_message';
        } elseif (in_array('tutor', $user_roles) &&  is_tutor_bid()) {
            return 'hide_response_form hide_login_message show_responded_message';
        }

        if (in_array('student', $user_roles)) {
            return 'hide_response_form hide_login_message hide_responded_message';
        }
    } else {
        return 'hide_response_form show_login_message hide_responded_message';
    }
}


add_shortcode('is_tutor_bid', 'is_tutor_bid');
function is_tutor_bid()
{

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'fastgrade_bidding',
        'author' => get_current_user_id(),
        'meta_key' => 'tutoring_request_id',
        'meta_compare' => '=',
        'meta_value'  => get_the_ID()
    );

    $bids = get_posts($args);

    if ($bids) {
        return true;
    } else {
        return false;
    }
    // ob_start();
    // print_r(get_the_ID());
    // print_r(get_current_user_id());
    // print_r($_POST['acf']);
    // return ob_get_clean();
}

//resgister  redirect

function my_page_template_redirects($user_id)
{
    if (is_user_logged_in()) {
        $current_user = new WP_User($user_id);
        $user_roles = $current_user->roles;
        $tutor_dashboard = home_url('/tutor-dashboard/');
        $student_dashboard = home_url('/student-dashboard/');
        if (!empty($_GET['redirect_to']))
            $redirect_to = $_GET['redirect_to'];

        // print_r(wp_get_referer());

        foreach ($user_roles as $role) {
            if ($role == 'tutor') {

                if (empty($redirect_to)) {
                    wp_safe_redirect($tutor_dashboard);
                } else {
                    // return $redirect_to;
                    wp_safe_redirect($redirect_to);
                }
            } elseif ($role == 'student') {

                if (empty($redirect_to)) {
                    wp_safe_redirect($student_dashboard);
                } else {
                    // return $redirect_to;
                    wp_safe_redirect($redirect_to);
                }
            }
        }
    }
}
// add_action('template_redirect', 'my_page_template_redirects', 10);
// add_action('um_on_login_before_redirect', 'my_page_template_redirects', 10);

function pd_create_private_req_redir()
{
    if (!is_user_logged_in() && is_page(1567)) {
        // $current_usr = new WP_User(get_current_user_id());
        // $usr_roles = $current_usr->roles;
        // foreach ($usr_roles as $rle) {
        //     if ($rle == 'tutor') {
        // echo'<h3>Please login as Student</h3>';
        $link = add_query_arg('redirect_to', urlencode(get_permalink(1567)), home_url('/signin'));
        wp_redirect($link);
        exit();
        //     }
        // }
    }
}
// add_action('template_redirect', 'pd_create_private_req_redir', 25);

function pd_show_tutor_pricing_fn()
{

    if (!empty($_SESSION['searched_conditions']['subject'])) {
        $subject = get_term($_SESSION['searched_conditions']['subject'])->name;
    } else {
        $subject = '';
    }

    if (!empty($_SESSION['searched_conditions']['fields_of_study'])) {
        $fields_of_study = get_term($_SESSION['searched_conditions']['fields_of_study'])->name;
    } else {
        $fields_of_study = '';
    }

    if (!empty($_SESSION['searched_conditions']['grade'])) {
        $grade = get_term($_SESSION['searched_conditions']['grade'])->name;
    } else {
        $grade = '';
    }


ob_start();
?>
    <table id="example" class="display text-center" style="width:100%; text-align: center;">
        <thead>
            <tr>
                <th>Subjects</th>
                <th>Fields of Study</th>
                <th>Grade</th>
                <th>Price ($/hr.)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $pricetab = get_field('tutor_pricing', $post->ID);
            //var_dump($pricetab);
            foreach ($pricetab as $pitm) {
                $subj = get_term($pitm['subjects'])->name;
                $fos = get_term($pitm['fields_of_study'])->name;
                $grad = get_term($pitm['grade'])->name;
                $pricetm = $pitm['price'];

            ?>
                <tr>
                    <td><?php echo $subj; ?></td>
                    <td><?php echo $fos; ?></td>
                    <td><?php echo $grad; ?></td>
                    <td>$<?php echo $pricetm; ?>/hr</td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Subjects</th>
                <th>Fields of Study</th>
                <th>Grade</th>
                <th>Price ($/hr.)</th>
            </tr>
        </tfoot>
    </table>
    <table id="exampl" class="display text-center" style="width:100%; text-align: center; display:none;">
        <thead>
            <tr>
                <th>Subjects</th>
                <th>Fields of Study</th>
                <th>Grade</th>
                <th>Price ($/hr.)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $priceta = get_field('tutor_pricing', $post->ID);
            //var_dump($pricetab);
            foreach ($priceta as $pitm) {
                $subj = get_term($pitm['subjects'])->name;
                $fos = get_term($pitm['fields_of_study'])->name;
                $grad = get_term($pitm['grade'])->name;
                $pricetm = $pitm['price'];

            ?>
                <tr>
                    <td><?php echo $subj; ?></td>
                    <td><?php echo $fos; ?></td>
                    <td><?php echo $grad; ?></td>
                    <td>$<?php echo $pricetm; ?>/hr</td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Subjects</th>
                <th>Fields of Study</th>
                <th>Grade</th>
                <th>Price ($/hr.)</th>
            </tr>
        </tfoot>
    </table>
    <script>
        jQuery(document).ready(function($) {
            $('#example').DataTable({
                searchCols: [{
                        'search': '<?php echo $subject; ?>'
                    },
                    {
                        'search': '<?php echo $fields_of_study; ?>'
                    },
                    {
                        'search': '<?php echo $grade; ?>'
                    },
                    null
                ],

                // initComplete: function() {
                //     this.api()
                //         .columns()
                //         .every(function() {
                //             var column = this;
                //             var select = $('<select class="select"><option value=""></option></select>')
                //                 .appendTo($(column.header()))
                //                 .on('change', function() {
                //                     var val = $.fn.dataTable.util.escapeRegex($(this).val());

                //                     column.search(val ? '^' + val + '$' : '', true, false).draw();
                //                 });

                //             column.data().unique().sort().each(function(d, j) {
                //                 select.append('<option value="' + d + '">' + d + '</option>');
                //             });
                //         });
                // },
            });




        });
    </script>
    <?php
    if (!empty($subject) || !empty($fields_of_study) || !empty($grade)) {
    ?>
        <script>
            jQuery(document).ready(function($) {
                $('#example_wrapper').prepend('<center><h4 style="margin: 5px auto;">Tutor Pricing for searched parameters</h4><p style="font-size:16px">click on show all to see all pricing for tutor </p></center>');
                $('#example_length').append('<button id="reset" style="margin: 0 15px;font-size: 16px;padding: 5px 15px;">Show All</button>');
                $('#reset').on('click', function() {
                    $('#example_wrapper').hide();
                    $('#exampl').show();
                    $('#exampl').DataTable({
                        destroy: true,
                        initComplete: function() {
                            this.api()
                                .columns()
                                .every(function() {
                                    var column = this;
                                    var select = $('<select class="select"><option value=""></option></select>')
                                        .appendTo($(column.header()))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                                        });

                                    column.data().unique().sort().each(function(d, j) {
                                        select.append('<option value="' + d + '">' + d + '</option>');
                                    });
                                });
                        },
                    });
                    $('#exampl_wrapper').prepend('<h4 style="margin: 5px auto; text-align:center;">Tutor Pricing </h4>');
                });
            });
        </script>
    <?php } else { ?>
        <script>
            jQuery(document).ready(function($) {
                $('#example_wrapper').prepend('<h4 style="margin: 5px auto; text-align:center;">Tutor Pricing </h4>');
                //$('#example_length').append('<button id="reset" style="margin: 0 15px;font-size: 16px;padding: 5px 15px;">Show All</button>');
            });
        </script>
<?php    }
    return ob_get_clean();
}
add_shortcode('pd_show_tutor_pricing', 'pd_show_tutor_pricing_fn');


add_shortcode( 'student_name', 'get_student_name' );
function get_student_name(){

    $profile_id = get_profile_id(get_the_author_meta('ID'));

    $first = get_post_meta( $profile_id, 'first_name', true );
    if(is_user_logged_in()){
        $last = get_post_meta( $profile_id, 'last_name', true );
    }else{
        $last = '';
    }
    
    return $first.' '.$last;
}

add_shortcode( 'complete_profile_message', 'complete_profile_message' );
function complete_profile_message(){

    global $current_user;
    global $post;
	$user_roles = $current_user->roles;
    if(in_array('tutor', $user_roles )){

        $profile_complete = get_post_meta(get_profile_id(),'profile_complete',true );
        if( $profile_complete ){
            return 'yes';
        }else{
            return 'no';
        }
        
    }

}


add_shortcode( 'student_complete_profile_message', 'student_complete_profile_message' );
function student_complete_profile_message(){

    global $current_user;
    global $post;
	$user_roles = $current_user->roles;
    if(in_array('student', $user_roles )){

        $student_timezone = get_post_meta(get_profile_id(),'student_timezone',true );
        //print_r($student_timezone);
        if( $student_timezone ){
            return 'yes';
        }else{
            return 'no';
        }
        
    }

}

// Bonus offer Winners!!...

function custom_bonus_offer_winners_callback($query)
{
    $query->set( 'post__in', array( 6763,6789,6806,6796,6794,6793,6809,6812,6828,6833,6838,7094,6845,6856,6884,6892,6893,6896,6897,6899 ));
    return $query;
}
add_action('elementor/query/bonus_offer_winners', 'custom_bonus_offer_winners_callback');