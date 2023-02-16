<?php

function redirect_to_searched_query(){
    global $wp_query;
    if( isset($_POST['search-form']) ){

        // print_r($_POST);
        // wp_die();

        foreach($_POST as $key => $value){
            if(!empty($value)){
                $args[$key] = $value;
            }

        }

        wp_redirect( add_query_arg($args) );

    }

}
add_action( 'template_redirect', 'redirect_to_searched_query' );

/**
 * 
 *  filter the tutor search result
 */
add_action( 'pre_get_posts', 'fastgrades_filter_tutor_archive' );
function fastgrades_filter_tutor_archive( $query ) {
	
    $tax_query = array(
        'relation' => 'AND',
    );

    $meta_query = array(
        'relation' => 'AND',
    );

	if( $query->is_main_query() && !is_admin() && ( is_post_type_archive( 'tutoringrequest' ) || is_post_type_archive( 'tutor_profiles' ) )  ) { //&& !empty($_POST['search-form'])

		$query->set( 'posts_per_page', '6' );
        $query->set( 'order', 'ASC' );
        $conditions = array(
            'subject' => get_term_by('slug', $_GET['fg_subject'], 'subject')->term_id,
            'fields_of_study' => get_term_by('slug', $_GET['fg_field_of_study'], 'field_of_study')->term_id,
            'grade' => get_term_by('slug', $_GET['fg_grade'], 'grade')->term_id,
            'price' => $_GET['tutor_rate'],
            'country' => $_GET['fg_country'],
            'keyword'   => $_GET['search']
        );

        $_SESSION['searched_conditions'] = $conditions;

        if(is_post_type_archive( 'tutoringrequest' )){
            $meta_query[] = array(
                'key'   => 'request_type',
                'value' =>  'public',
                'compaire'  => '='
            );

            if(isset($_GET['fg_subject'])){
                $tax_query[] = array(
                    'taxonomy' => 'subject',
                    'field'    => 'slug',
                    'terms'    => $_GET['fg_subject'],
                );
            }
    
            if(isset($_GET['fg_field_of_study'])){
                $tax_query[] = array(
                    'taxonomy' => 'field_of_study',
                    'field'    => 'slug',
                    'terms'    => $_GET['fg_field_of_study'],
                );
            }
    
            if(isset($_GET['fg_grade'])){
                $tax_query[] = array(
                    'taxonomy' => 'grade',
                    'field'    => 'slug',
                    'terms'    => $_GET['fg_grade'],
                );
            }

            if(isset($_GET['fg_country'])){
                $tax_query[] = array(
                    'taxonomy' => 'cp_country',
                    'field'    => 'slug',
                    'terms'    => $_GET['fg_country'],
                );
            }

            if(isset($_GET['tutor_rate'])){
                $meta_query[] = array(
                    'key' => 'budget',
                    'compare'    => '<=',
                    'value'    => $_GET['tutor_rate'],
                    'type'	=> 'NUMERIC'
                );
            }
        }

        if(is_post_type_archive( 'tutor_profiles' )){
            if( !empty($conditions['subject']) && !empty($conditions['fields_of_study']) && !empty($conditions['grade']) ) {
                
                if(!empty(acf_repeater_search($conditions))){
                    $query->set( 'post__in',  acf_repeater_search($conditions));
                }else{
                    $query->set( 'post__in',  [0]);
                }
            
            }else{

                $meta_query = array(
                    'relation' => 'AND',
                );

                if(isset($_GET['fg_subject'])){
                    $meta_query[] = array(
                        'key' => 'tutor_pricing_$_subjects',
                        'compare'    => '=',
                        'value'    => $conditions['subject'],
                        'type'	=> 'NUMERIC'
                    );
                }

                if(isset($_GET['fg_field_of_study'])){
                    $meta_query[] = array(
                        'key' => 'tutor_pricing_$_fields_of_study',
                        'compare'    => '=',
                        'value'    => $conditions['fields_of_study'],
                        'type'	=> 'NUMERIC'
                    );
                }

                if(isset($_GET['fg_grade'])){
                    $meta_query[] = array(
                        'key' => 'tutor_pricing_$_grade',
                        'compare'    => '=',
                        'value'    => $conditions['grade'],
                        'type'	=> 'NUMERIC'
                    );
                }

                if(isset($_GET['tutor_rate'])){
                    $meta_query[] = array(
                        'key' => 'tutor_pricing_$_price',
                        'compare'    => '<=',
                        'value'    => $conditions['price'],
                        'type'	=> 'NUMERIC'
                    );
                }
                // else{
                //         $meta_query[] = array(
                //             'key' => 'tutor_pricing_$_price',
                //             'compare'    => '<=',
                //             'value'    => 1000,
                //             'type'	=> 'NUMERIC'
                //         );
                // }

            }

            if(isset($_GET['fg_country'])){
                $tax_query[] = array(
                    'taxonomy' => 'cp_country',
                    'field'    => 'slug',
                    'terms'    => $_GET['fg_country'],
                );
            }
        }

        

        // if(isset($_GET['fg_language'])){
        //     $tax_query[] = array(
        //         'taxonomy' => 'fg_language',
        //         'field'    => 'slug',
        //         'terms'    => $_GET['fg_language'],
        //     );
        // }

        // if(isset($_GET['fg_country'])){
        //     $tax_query[] = array(
        //         'taxonomy' => 'cp_country',
        //         'field'    => 'slug',
        //         'terms'    => $_GET['fg_country'],
        //     );
        // }

        // if(isset($_GET['tutor_rate'])){
        //     $meta_query[] = array(
        //         'key' => 'tutor_pricing_%_price',
        //         'compare'    => '<',
        //         'value'    => $_GET['tutor_rate'],
        //         'type'	=> 'NUMERIC'
        //     );
        // }

        // if(isset($_GET['tutor_rate'])){
        //     $meta_query[] = array(
        //         'key' => 'tutor_pricing_%_price',
        //         'compare'    => '<',
        //         'value'    => $_GET['tutor_rate'],
		// 		'type'	=> 'NUMERIC'
        //     );
        // }

		if(isset($_GET['search'])){
            $query->set( 's', $_GET['search'] );
        }

        $query->set( 'tax_query', $tax_query );
        $query->set( 'meta_query', $meta_query );
	}

}


// filter
function my_posts_where( $where ) {
    $where = str_replace("meta_key = 'tutor_pricing_$", "meta_key LIKE 'tutor_pricing_%", $where);
    return $where;
}
add_filter('posts_where', 'my_posts_where');

function tutor_search_form($atts){

    $atts = shortcode_atts( array(
        'archive_slug' => 'post',
    ), $atts, 'bartag' );

    $fg_subject = (!empty($_GET['fg_subject'])) ? $_GET['fg_subject'] : '';
    $fg_field_of_study = (!empty($_GET['fg_field_of_study'])) ? $_GET['fg_field_of_study'] : '';
    $fg_grade = (!empty($_GET['fg_grade'])) ? $_GET['fg_grade'] : '';
    $fg_language = (!empty($_GET['fg_language'])) ? $_GET['fg_language'] : '';
    $fg_country = (!empty($_GET['fg_country'])) ? $_GET['fg_country'] : '';
    $search = (!empty($_GET['search'])) ? $_GET['search'] : '';
    $tutor_rate = (!empty($_GET['tutor_rate'])) ? $_GET['tutor_rate'] : '';

	ob_start();
    echo '
    <div class="container grad1">    
    	<form action="'. get_post_type_archive_link( $atts['archive_slug'] ) .'" method="post" role="search">
  			<div class="form-row">
    			<div class="col-4">
					<select class="form-control" aria-label="Default select example" name="fg_subject" id="fg_subject">
					  <option value="">Select  Subject</option>
					  '. get_tax_options_list("subject", $fg_subject) .'
					</select>
   				</div>
   
    			<div class="col-4">
					<select class="form-control" aria-label="Default select example" name="fg_field_of_study" id="fg_field_of_study">
					  <option value="">Select field of study</option>
					  '. get_tax_options_list("field_of_study", $fg_field_of_study) .'
					</select>
    			</div>
	  
	  			<div class="col-4">
				<!-- <input type="text" class="form-control" placeholder=" keyword"> -->
		  			<select class="form-control" aria-label="Default select example" name="fg_grade">
					  <option value="">Select Level</option>
					  '. get_tax_options_list("grade", $fg_grade)  .'
					</select>
    			</div>
	  		</div>
		  	<div class="form-row">
   				<div class="col-4">
				<!-- <input type="text" class="form-control" placeholder="Location"> -->
					<select class="form-control" name="fg_country" aria-label="Default select example">
			  			<option value="">Select Country</option>
                        '. get_tax_options_list("cp_country", $fg_country)  .'
					</select>
    			</div>
				
				<div class="col-4">
    				<input type="text" class="form-control" placeholder="Keyword" name="search" value="'. $search .'">
    			</div>
                <div class="col-4">
					<input type="number" class="form-control" placeholder="Max rate in $/hr or leave blank for no limit" name="tutor_rate" value="'. $tutor_rate .'">
				</div>
  			</div>
			<div class="form-row">
   				
                <div class="col-md-12" style="text-align: right;">
	   				<button type="submit" class="btn btn-outline-info" name="search-form">Search</button>
    			</div>
			</div>
		</form>     
        <br>
	</div>
    ';

	return ob_get_clean();
}
// add_action( 'fastgrade_after_inner_banner', 'tutor_search_form');
add_shortcode( 'fastgrade_search_form', 'tutor_search_form');

function get_tax_options_list($tax, $selected_tax){
    $terms = get_terms( array(
        'taxonomy' => $tax,
        'hide_empty' => false,
    ) );
    
    $terms_list = '';
    foreach($terms as $term){
        $selected = ($selected_tax == $term->slug) ? 'selected' : '';
        $terms_list .=  '<option value="'.$term->slug.'" '.$selected.'>'.$term->name.'</option>';
    }

    return $terms_list;
}

add_action('wp_ajax_select_fields_of_study_by_subject', 'select_fields_of_study_by_subject');
add_action('wp_ajax_nopriv_select_fields_of_study_by_subject', 'select_fields_of_study_by_subject');

function select_fields_of_study_by_subject(){
    if ( check_ajax_referer( '_ajax_nonce' ) ) {
        if(is_numeric($_POST['subject'])){
            $subject = $_POST['subject'];
        }else{
            $subject = get_term_by( 'slug', $_POST['subject'], 'subject')->term_id;
        }
        $args = array(
            'taxonomy' => 'field_of_study',
            'hide_empty' => false,
            'meta_key' => 'subject',
            'meta_value' => $subject, 
            'meta_compare' => '='
        );
        $fg_field_of_studies_by_subject = get_terms($args);

        $options = '<option value="">Select field of study</option>';
        foreach($fg_field_of_studies_by_subject as $field_of_study){
            // $options .= '<option value="'.$field_of_study['slug'].'">'.$field_of_study['name'].'</option>';
            $options .= '<option value="'.$field_of_study->slug.'">'.$field_of_study->name.'</option>';
        }

        wp_send_json_success( $options, 200, 0);
        
    }
}

add_action('wp_ajax_select_fos_by_subject_select2', 'select_fos_by_subject_select2');
add_action('wp_ajax_nopriv_select_fos_by_subject_select2', 'select_fos_by_subject_select2');

function select_fos_by_subject_select2(){
    if (!wp_verify_nonce($_POST['nonce'], 'acf_nonce')) {
        die();
    }
    
    $args = array(
        'taxonomy' => 'field_of_study',
        'hide_empty' => false,
        'search' => $_POST['s'],
        'meta_key' => 'subject',
        'meta_value' => $_POST['subject'], 
        'meta_compare' => '='
    );
    $fg_field_of_studies_by_subject = get_terms($args);
       // echo $_POST['subject'];
    // var_dump($fg_field_of_studies_by_subject);
    // $options = '<option value="">Select field of study</option>';
    $options = array();
    foreach($fg_field_of_studies_by_subject as $field_of_study){
        $options[] = array( 'id' => $field_of_study->term_id, 'text' => $field_of_study->name);
    }
   // var_dump($options);
    wp_send_json_success( $options, 200, 0);
    
}

// add_shortcode( 'acf_repeater_search', 'acf_repeater_search' );
function acf_repeater_search($conditions){
    // $tutor_pricing = get_field('tutor_pricing', 3220 );
    $args = array(
        'post_type' => 'tutor_profiles',
        'posts_per_page' => -1
    );
    $tutor_profiles = get_posts( $args );
    // $tutor_pricing = array();
    $subject = $conditions['subject'];
    $fields_of_study = $conditions['fields_of_study'];
    $grade = $conditions['grade'];
    $price = $conditions['price'];

    $tutors = array();

    foreach($tutor_profiles as $tutor){
        $tutor_pricing = get_field('tutor_pricing', $tutor->ID);
        foreach($tutor_pricing as $check){
            // if($is_and_or == 'and'){
                if( $check['subjects'] == (int)$subject && $check['fields_of_study'] == (int)$fields_of_study && $check['grade'] == (int)$grade ){ //&& $check['price'] <= (int)$price
                    if(!empty($price) && $check['price'] > (int)$price){
                        continue;
                    }
                    $tutors[] = $tutor->ID;
                    $_SESSION['searched_tutor_rates'][$tutor->ID] = $check['price'];
                }
            // }elseif($is_and_or == 'or'){
            //     if( $check['subjects'] == (int)$subject || $check['fields_of_study'] == (int)$fields_of_study || $check['grade'] == (int)$grade || $check['price'] <= (int)$price){
            //         $tutors[] = $tutor->ID;
            //         $_SESSION['searched_tutor_rates'][$tutor->ID] = $check['price'];
            //     }
            // }
        }
    }

    if(empty($tutors)) unset($_SESSION['searched_tutor_rates']);
    // ob_start();
    //     echo '<pre>';
    //     print_r($tutors);
    //     echo '</pre>';
    // return ob_get_clean();
    return $tutors;
}

add_shortcode( 'global_query', 'global_query' );
function global_query(){
    
    global $query_string;
    $conditions = array(
        'subject' => get_term_by('slug', $_GET['fg_subject'], 'subject')->term_id,
        'fields_of_study' => get_term_by('slug', $_GET['fg_field_of_study'], 'field_of_study')->term_id,
        'grade' => get_term_by('slug', $_GET['fg_grade'], 'grade')->term_id,
        'price' => $_GET['tutor_rate'],
    );
    ob_start();
    var_dump( $GLOBALS['wp_query']->request );
    // echo '<hr>';
    // print_r( $_SESSION['searched_conditions'] );
    // print_r( $_SESSION['searched_tutor_rates'] );
    return ob_get_clean();
}

add_shortcode( 'get_searched_parameter', 'get_searched_parameter' );
function get_searched_parameter($atts){
    switch ($atts['parameter']) {
        case 'subject':
            $searched_subject = (!empty($_SESSION['searched_conditions']['subject'])) ? get_term_by('term_id', $_SESSION['searched_conditions']['subject'], 'subject')->name : '';
            return $searched_subject;
            break;
        case 'field_of_study':
            $searched_field_of_study = (!empty($_SESSION['searched_conditions']['field_of_study'])) ? get_term_by('term_id', $_SESSION['searched_conditions']['field_of_study'], 'field_of_study')->name : '';
            return $searched_field_of_study;
            break;
        case 'price':
            $searched_tutor_rates = (!empty($_SESSION['searched_tutor_rates'][get_the_id()])) ? '$'.$_SESSION['searched_tutor_rates'][get_the_id()].'/hr' : '';
            return $searched_tutor_rates;
            break;
        
        default:
            return '';
            break;
    }
    // return '';
}