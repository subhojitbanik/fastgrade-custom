<?php

function acf_load_written_field_choices( $field ) {
    // reset choices
    $field['choices'] = array();
    $choices = get_terms( 'written_spoken',array(
        'hide_empty' => false,
    ) );
        foreach( $choices as $choice ) {
            // vars
            $value = $choice->term_id;
            $label = $choice->name;
            // append to choices
            $field['choices'][ $value ] = $label;
        }
    // return the field
    return $field;
    
}
add_filter('acf/load_field/key=field_63e3a80cea509', 'acf_load_written_field_choices');
//add_filter('acf/load_field/key=field_639b21351659a', 'acf_load_written_field_choices');


function sb_show_lang_write_spoke_fn(){
    $profile_id = get_the_ID();
    // $tutor_id = get_users( array(
    //     "meta_key" => "profile_id",
    //     "meta_value" => $profile_id,
    //     "fields" => "ID"
    // ) );
    //print_r($profile_id);
   $language = get_field('language',$profile_id);
   $written = get_field('written',$profile_id);
   $spoken = get_field('spoken',$profile_id);
    //print_r($language);
    ob_start();
        if($language){?>
            <style>
                .sb_lang ul {
                    list-style: none;
                    margin: 5px auto;
                    display: flex;
                    color: #fff;
                    padding: 0px;
                }
                .sb_lang ul li {
                    margin: 5px;
                }
            </style>
            <div class="sb_lang" style="padding: 10px 0;">
                <ul>
                    <li>Primary Language : </li>
                    <li><?php echo $language->name; ?></li>
                    
                </ul>
                <?php if(!empty($written)){ ?>
                    <ul>
                        <li>Also Write & Speak : </li>
                        <?php
                                foreach ($written as $value) {
                                    echo'<li>'.$value.'</li>';
                                }
                            ?>  
                    </ul>
                <?php } ?>
                <!-- <ul>
                    <li>Speak: </li>
                    <?php 
                            // foreach ($spoken as $value) {
                            //     echo'<li>'.$value.'</li>';
                            // }
                        ?>  
                </ul> -->
            </div>

            <?php
        }
    return ob_get_clean();
}
add_shortcode( 'tutor_language', 'sb_show_lang_write_spoke_fn' );



