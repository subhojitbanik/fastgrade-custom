jQuery(document).ready(function($){

      // if (typeof acf == 'undefined') { return; }

      $('.public-req-response-btn a').click(function(e){

            e.preventDefault();

            var status = ( $(this).closest('.elementor-widget-button').hasClass('accept_btn') ) ? 'accepted' : 'declined';
            var post_id = $(this).closest('article').attr('id');
            console.log('Status : ' + status);
            var data = {
                  '_ajax_nonce': fastgrade_custom._ajax_nonce,
                  'action' : 'update_response_status',
                  'status' : status,
                  'response_id' : post_id.replace('post-','')
            }
            jQuery.post(fastgrade_custom.ajax_url, data, function(response) {
                  console.log(response);
                  if(response['data']['status'] == 'declined'){

                        $("#" + response['data']['response_id']).addClass('declined');
                        $("#" + response['data']['response_id']).removeClass('accepted');

                        $("#post-" + response['data']['response_id'] + " .decline_btn a").text('Declined');
                        $("#post-" + response['data']['response_id'] + " .accept_btn a").text('Accept');

                  }else{

                        $("#" + response['data']['response_id']).addClass('accepted');
                        $("#" + response['data']['response_id']).removeClass('declined');

                        $("#post-" + response['data']['response_id'] + " .accept_btn a").text('Accepted');
                        $("#post-" + response['data']['response_id'] + " .decline_btn a").text('Decline');
                  }
            });
      })


      $('.public-req-response-btn').click(function(e){

            e.preventDefault();
            var CancelReason = $('#public_req_cancel_reason').val();
            var status = ( $(this).closest('.elementor-widget-button').hasClass('accept_btn') ) ? 'accepted' : 'declined';
            var post_id = $(this).closest('article').attr('id');
            console.log('Status : ' + status);
            var data = {
                  '_ajax_nonce': fastgrade_custom._ajax_nonce,
                  'action' : 'update_response_status',
                  'status' : status,
                  'response_id' : post_id.replace('post-',''),
                  'cancelation_reason' : CancelReason
            }
            jQuery.post(fastgrade_custom.ajax_url, data, function(response) {
                  console.log(response);
                  if(response['data']['status'] == 'declined'){

                        $("#pub-req-container").fadeOut();
                        $("#pub-req-pop-modal").fadeOut();

                        $("#" + response['data']['response_id']).addClass('declined');
                        $("#" + response['data']['response_id']).removeClass('accepted');

                        $("#post-" + response['data']['response_id'] + " .decline_btn a").text('Declined');
                        $("#post-" + response['data']['response_id'] + " .accept_btn a").text('Accept');

                        $("#post-" + response['data']['response_id'] + " .sb-open").text('Declined');

                  }else{

                        $("#" + response['data']['response_id']).addClass('accepted');
                        $("#" + response['data']['response_id']).removeClass('declined');

                        $("#post-" + response['data']['response_id'] + " .accept_btn a").text('Accepted');
                        $("#post-" + response['data']['response_id'] + " .decline_btn a").text('Decline');

                        $("#post-" + response['data']['response_id'] + " .sb-open").text('Decline');
                  }
            });
      })

      $('.private-req-response-btn ').click(function(e){

            e.preventDefault();
            var CancelReason = $('#priv_req_cancel_reason').val();
            var status = ( $(this).closest('.elementor-widget-button').hasClass('accept_btn') ) ? 'accepted' : 'declined';
            var post_id = $(this).closest('article').attr('id');
            console.log('Status : ' + status);
            var data = {
                  '_ajax_nonce': fastgrade_custom._ajax_nonce,
                  'action' : 'tutor_responce_private_request',
                  'status' : status,
                  'request_id' : post_id.replace('post-',''),
                  'cancelation_reason' : CancelReason
            }
            jQuery.post(fastgrade_custom.ajax_url, data, function(response) {
                  console.log(response);
                  if(response['data']['status'] == 'declined'){
                        $("#priv-req-container").fadeOut();
                        $("#priv-req-pop-modal").fadeOut();
                        $("#" + response['data']['request_id']).addClass('declined');
                        $("#" + response['data']['request_id']).removeClass('accepted');

                        $("#post-" + response['data']['request_id'] + " .decline_btn a").text('Declined');
                        $("#post-" + response['data']['request_id'] + " .accept_btn a").text('Accept');

                        $("#post-" + response['data']['request_id'] + " .sb-open").text('Declined');



                  }else{

                        $("#" + response['data']['request_id']).addClass('accepted');
                        $("#" + response['data']['request_id']).removeClass('declined');

                        $("#post-" + response['data']['request_id'] + " .accept_btn a").text('Accepted');
                        $("#post-" + response['data']['request_id'] + " .decline_btn a").text('Decline');
                        $("#post-" + response['data']['request_id'] + " .sb-open").text('Decline');

                  }
            });
      })
      
      $("#acff-post-field_623eab5bc7bbf").on('change', function(e){
            var above_eighteen = $(e.currentTarget).val();
            if(above_eighteen == 'not_above'){
                  $('#acff-user-field_a3dcbb5ec52b93').attr('placeholder', 'Parent\'s email address');
            }else{
                $('#acff-user-field_a3dcbb5ec52b93').attr('placeholder', ' Email address');
            }
      })

      $('[data-type="user_email"] input').keyup(function (e) {
            var email = $(e.currentTarget).val();
            $('[data-type="username"] input').val(email);
      })

      /**
       *  change ajax data options for fields_of_Study field depending on subject selected
       */
	
	jQuery(document).on("change", ".select2-hidden-accessible", function( )
	{
		
		if($('[data-name="tutor_pricing"]').length !== 0)
		{
			var sTempId = $(this).attr("id").replace("acff-post-field_61e68a0098130-", "").replace("-field_61e68ab098132", "");
			$("#acff-post-field_61e68a0098130-"+sTempId+"-field_61e68b2498133").find('option[value]').remove( );
		}
		
		if ($(this).attr("id") == "acff-post-field_ab4db482915c8a")
		{
			console.log($('[data-name="tutor_pricing"]'));
			$("#acff-post-field_ab4db480bdc9c3").find('option[value]').remove( );
		}
		
		
	});
	/*jQuery(document).on("change", ".select2-hidden-accessible", function( )
	{
		var data = $(this);
		
		console.log(acf.getField($(this).data('key')));
		
		
		acf.add_filter('select2_ajax_data', function( data, args, $input, $field )
		{
			var field = acf.getField(data.field_key);
			//console.log(field.data.taxonomy);
			//var subject = acf.getField($('[data-taxonomy="subject"]').closest('.acf-field').data('key'));
			var subject = acf.getField($(this).data('key'));
			console.log(data);
			var fos = field.data.taxonomy;
			if( fos.localeCompare("field_of_study") == 0)
			{
				//console.log(subject);
				data.action = 'select_fos_by_subject_select2'
				data.subject = subject.val();
			}

			return data;
		});
	});*/
	//field_61e68ab098132
	
	var sOldSubjectValue = 0;
      acf.add_filter('select2_ajax_data', function( data, args, $input, $field ){
            var field = acf.getField(data.field_key);
			
             //console.log(field.data.taxonomy);
			 
             //console.log(data);
             if($('[data-name="tutor_pricing"]').length !== 0){
                  // console.log('Tutor Pricing');
                   //console.log($field);
                   //console.log( $field.closest('tr.acf-row').find('[data-name="subjects"]').data('key') );
				   
				   //$field.closest('tr.acf-row td:nth-child(2)').addClass("add");
				   //$field.closest('tr.acf-row').find('td:nth-child(2)').addClass("add");
				   
                   //var subject = acf.getField($field.closest('tr.acf-row').find('[data-name="subjects"]').data('key'));
                   var subject = ($field.closest('tr.acf-row').find('td:nth-child(2)').find("select"));
				   
				   
             }
             else{
                  var subject = acf.getField($('[data-taxonomy="subject"]').closest('.acf-field').data('key'));

					//$("#acff-post-field_ab4db480bdc9c3").find('option[value]').remove( );
             }
            // console.log(subject);
			
			//$field.closest('tr.acf-row').find('td:nth-child(3)').find("select").find('option[value]').remove( );
			 
			 //var subject = acf.getField($('[data-taxonomy="subject"]').closest('.acf-field').data('key'));
            //console.log($('[data-taxonomy="subject"]').parent().closest('.acf-field').data('key'));
            var fos = field.data.taxonomy;
            if( fos.localeCompare("field_of_study") == 0){
                  // console.log('hello');
                  // var subject = acf.getField($("[data-key="+data.field_key+"]").closest('[data-taxonomy="subject"]').closest('.acf-field').data('key'));
                  // console.log($("[data-key='"+data.field_key+"']").closest('[data-taxonomy="subject"]').closest('.acf-field').data('key'));
                  // console.log(subject.val());
				 // var sSubjectValue = ($field.closest('tr.acf-row').find('td:nth-child(2)').find("select").val( ));
                  // console.log(subject.data('key'));
                  data.action = 'select_fos_by_subject_select2'
                  data.subject = subject.val();
            }
			
			/* if (sOldSubjectValue != subject.val())
				$field.closest('tr.acf-row').find('td:nth-child(3)').find("select").find('option[value]').remove( ); */
			
			sOldSubjectValue = subject.val();
              
            return data;
      });

      /**
       *  change result to recieved from server
       */

      acf.add_filter('select2_ajax_results', function( json, params, instance ){
            var field = acf.getField(instance.data.field.data.key);
            var fos = field.data.taxonomy;
            // console.log(fos);
            if(fos.localeCompare("field_of_study") == 0){
                  // console.log(json);
                  json.results = json.data
				  
            }
            return json;
        
      });

      $('#fg_subject').on('change', function(e){

            e.preventDefault();

            var subject = $(this).val();
            // console.log('subject : ' + subject);
            var data = {
                  '_ajax_nonce': fastgrade_custom._ajax_nonce,
                  'action' : 'select_fields_of_study_by_subject',
                  'subject' : subject
            }
            jQuery.post(fastgrade_custom.ajax_url, data, function(response) {
                   console.log(response.data);
                  $('#fg_field_of_study').html(response.data);
            });
      })

      // Show Password for login form and registration form
      console.log('password show');
      $(":password").after('<span toggle="#acff-user-field_21bf37a" class="fa fa-fw fa-eye field-icon toggle-password" style="cursor: pointer;position: absolute;right: 10px;top: 50%;transform: translateY(-50%);"></span>');
      // $("input#acff-user-field_4f2c29b").after('<span toggle="#acff-user-field_4f2c29b" class="fa fa-fw fa-eye field-icon toggle-password" style="margin-left: -30px; cursor: pointer;"></span>');
      // $("input#password").after('<span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password" style="margin-left: -30px; cursor: pointer;"></span>');
      //password-text toggle
      $(".toggle-password").click(function() {
            $(this).toggleClass("fa-eye fa-eye-slash");
            var input = $(this).prev();
            console.log(input);
            if (input.attr("type") == "password") {
                  input.attr("type", "text");
            } else {
                  input.attr("type", "password");
            }
      });

      var field = acf.getField('field_85885e72915c8a');
      // field.val(31);
      console.log(field);

      $(".page-id-32 button.fea-submit-button").attr("disabled", true);
      $("#acff-post-field_6300743c9f39d-yes").removeAttr("checked");
      $('#acff-post-field_6300743c9f39d-yes').change(function(){
            console.log(this.checked);    
            if(this.checked){
                $("button.fea-submit-button").removeAttr("disabled");
            }else{
                $(".page-id-32 button.fea-submit-button").attr("disabled", true);
            }
      });
});



