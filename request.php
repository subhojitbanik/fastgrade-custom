<?php

class Request {

    public function __construct()
    {
        add_shortcode( 'get_field_val', array($this, 'get_field_val') );
        add_action('acf/input/admin_footer', array($this,'my_acf_input_admin_footer') );
        add_filter('acf/load_field/type=taxonomy', array($this,'set_tax_default') );
        add_action( 'get_header', array($this,'fg_acf_form_header', 1 ) );
        add_shortcode( 'fg_acf_request_form', array($this, 'fg_acf_request_form' ));
        add_filter('acf/prepare_field/name=_post_title', array($this, 'fg_acf_prepare_post_title_field'));
        add_filter('acf/prepare_field/name=_post_content', array($this, 'fg_acf_prepare_post_content_field'));
        add_filter('acf/prepare_field/type=hidden', array($this, 'fg_acf_prepare_hidden_field'));
        add_filter('acf/load_field/name=request_type', array($this, 'fg_acf_load_request_type_field'));
    }

    public function fg_acf_form_header(){
        if ( !is_admin() )
            acf_form_head();
    }
    public function fg_acf_request_form(){
        $options = array( 'id' => 'acf_form_tutoringrequest',
                    'label_placement' => 'top',
                    'instruction_placement' => 'field',
                    'submit_value' => __("Submit Request", 'acf'),
                    'post_title' => true,
                    'post_content' => true,
                    'uploader' => 'basic',
                    'fields' => array(
                        'field_61e548fd6fd3d',
                        'field_633893338b66a',
                        'field_62595c625113d',
                        'field_62595c8f5113e',
                        'field_62419b9df90ca',
                        'field_6338984f08a6c',
                        'field_62419ab552304',
                        'field_61e5498b6fd3f',
                        'field_62419afb52305',
                        'field_62068123679d9'
                    ),
                   );

        if(isset($_GET['request_id'])){
            $options['post_id'] = $_GET['request_id'];
            $options['updated_message'] = __( 'Request published successfully', 'acf' );
        }else{
            $options['post_id'] = 'new_post';
            $options['new_post'] = array( 'post_type' => 'tutoringrequest', 'post_status' => 'publish');
            $options['updated_message'] = __( 'Request updated successfully', 'acf' );
        }
  
        ob_start();
        acf_form( $options );
        $ret = ob_get_contents();
        ob_end_clean();
        
        return $ret;
    }
    public function fg_acf_prepare_hidden_field($field)
    {
        if($field['type'] == 'hidden'){
            $field['label'] = '';
        }

        return $field;
    }
    public function fg_acf_load_request_type_field($field)
    {
        if(is_page( 'test-public-acf-request-form' )){
            $field['default_value'] = 'public';
        }
        return $field;
    }
    public function fg_acf_prepare_post_title_field($field){
        if(is_page( 'test-public-acf-request-form' )){
            $field['label'] = "Tutoring Request Title";
        }
        return $field;
    }
    public function fg_acf_prepare_post_content_field( $field)
    {
        if(is_page( 'test-public-acf-request-form' )){
            $field['label'] = "Tutoring Need";
            $field['tabs'] = 'visual';
            $field['toolbar'] = 'basic';
            $field['media_upload'] = 0;
        }
        return $field;
    }
    public function get_ref_val($atts)
    {
        extract($atts);
        $post_id = get_post_meta($ref_post_id, $ref_key, true);
        return get_post_meta( $post_id, $key, true );
    }

    public function get_val($atts)
    {
        return get_post_meta($post_id, $key, true);
    }

    public function get_field_val($atts)
    {
        $atts = shortcode_atts( array(
            'key' => '',
            'post_id' => get_the_ID(),
            'ref_key' => '',
            'ref_post_id' => get_the_ID()
        ), $atts);

        if(!empty($atts['ref_key'])){
            return $this->get_ref_val($atts);
        }else{
            return $this->get_val($atts);
        }
    
    }

    /*
 @ Format Date time picker fields
*/

public function my_acf_input_admin_footer() {
	
    ?>
    <script type="text/javascript">
    (function($) {
        
        // JS here
        
        // acf.add_filter('date_picker_args', function( args, $field ){
            
        //     // do something to args
        //     args['minDate'] = "0";	//For example, "+1m +7d" represents one month and seven days from today.
            
            
        //     return args;
                    
        // });
        
        acf.add_filter('date_time_picker_args', function( args, $field ){
        
            console.log($field);
        // do something to args
            
            args['stepMinute'] = 30;
            args['showSecond'] = false;
        
        // return
        return args;
                
    });
    
    })(jQuery);	
    </script>
    <?php		
    }

    public function set_tax_default($field) {
        if(!is_admin() && !empty($_SESSION['searched_conditions'])){
            // $terms = get_terms($field['taxonomy'], ['number' => 1]);
            if($field['taxonomy'] == 'subject') {
                $field['default_value'] = $_SESSION['searched_conditions']['subject'];
            }

            if($field['taxonomy'] == 'grade') {
                $field['default_value'] = $_SESSION['searched_conditions']['grade'];
            }

            if($field['taxonomy'] == 'field_of_study') {
                $field['default_value'] = $_SESSION['searched_conditions']['fields_of_study'];
            }
        }
        return $field;
    }
    
}