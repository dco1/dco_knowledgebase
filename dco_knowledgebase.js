$( document ).ready(function() {

	////////////
	// SEARCH //
	////////////
	
	$("#dco_kb_search_form_search_field").autocomplete({
		source: function( request, response ) {
		$("#dco_kb_search_form").removeClass().addClass	('processing');
        $.ajax({
          url: ajax_url  + "?action=dco_kb_article_search",
          dataType: "json",
          type: 'GET',
          data: {
            q: request.term ,
          },
          success: function( data ) {
	        $("#dco_kb_search_form").removeClass();
            response( data );
          }
        });
      },
      response: function(event, ui) {
            // ui.content is the array that's about to be sent to the response callback.
            if (!ui.content) {
                dco_kb_search_no_results_ask_question( event, ui );
                ui.content = { label: "No Results"}
                $("#dco_kb_search_form").removeClass().addClass('no-results');

            }
      }, 
      minLength: 3,
      focus: function( event, ui ) {
	      return false;
      },
	  select: function( event, ui ) {
	  	window.location.href =  ui.item.value ;
	  	return false;
	  }
	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		
      return $( "<li class='kb_search_autocomplete_row'>" )
        .append( "<div><a href='"+ item.value + "'><h5>" + item.label + "</h5><span>"+ item.excerpt +"</span></a></div>" )
        .appendTo( ul );
    };
    
	jQuery.ui.autocomplete.prototype._resizeMenu = function () {
	  var ul = this.menu.element;
	  ul.outerWidth(this.element.outerWidth());
	}
	
	function dco_kb_search_no_results_ask_question( event, ui){
		console.log( $("#" + event.target.id).val() );
	}
	
	////////////////////
	// AJAX Submitter //
	////////////////////
	
	function dco_kb_ajax_post_submit( dataTicket, callback_func ){
		$.ajax({
        	type: "POST",
			url: ajax_url,
			dataType: 'json',
			data: dataTicket,
			success: callback_func
    	})
	}
	
	/////////////////////////////
	// New Question Submission //
	/////////////////////////////
	
	function dco_kb_show_new_question_wrapper(){
		$("#dco_kb_new_question_wrapper").addClass('show');
		$("#dco_kb_new_user_submission_form").prepend('<a id="close-kb-wrapper" href="#"><i class="fa fa-times-circle text-danger" aria-hidden="true"></i></a>');
	}
	
	function dco_kb_hide_new_question_wrapper(){
		$("#dco_kb_new_question_wrapper").removeClass('show');
		$("#dco_kb_new_user_submission_form").closest( $('#close-kb-wrapper').remove() );
	}
	
	$("#dco_ask_a_question").on('click' , function( event ){
		event.preventDefault();
		dco_kb_show_new_question_wrapper();
	});
	  
	$(document).keydown(function( event ) {
		if ( event.keyCode == 27 ) { //esc 
			dco_kb_hide_new_question_wrapper();
		}
	});  
	
	$("#dco_kb_new_user_submission_form").on('click', '#close-kb-wrapper', function( event ){
		event.preventDefault();
		dco_kb_hide_new_question_wrapper();
	});
	
	$("#dco_kb_new_question_wrapper").on('click', function(e){
		if (e.target !== this) return;
		 dco_kb_hide_new_question_wrapper();
	});


	$('#dco_kb_new_user_submission_form').on('submit', function( event ){
		event.preventDefault();

		dataTicket = new Object();
		dataTicket.action = 'dco_kb_question_submit';
		dataTicket.question = $('input.user-question').val();
		dataTicket.name = $("input.user-name").val();
		dataTicket.email =  $("input.user-email").val();
		dataTicket.ask_a_question_nonce = $('#ask_a_question_nonce').val();
		dco_kb_ajax_post_submit( dataTicket , function( data ){
			//console.log( data );
			if ( data.success){
				$('#dco_kb_new_user_submission_form').trigger("reset");
			}
		});
	});
	
	
	////////////
	// VOTING //
	////////////
	
	
	$(".dco_kb_vote_buttons").each(function(){
		$(this).append('<p>Did you find this article helpful?</p>');
		$(this).append( $('<button type="button" name="upvote" class="dco_kb_upvote">' + $(this).attr('data-upvotes') + '</button>') );
		$(this).append( $('<button type="button" name="downvote" class="dco_kb_downvote">'+ $(this).attr('data-downvotes')  +'</button>') );
	});
	
	$(".dco_kb_vote_buttons").on('click', 'button', function(event){
		$(this).addClass('processing');
		event.preventDefault();
		dataTicket = new Object();
		var $this = $(this);
		dataTicket.action = 'dco_kb_article_vote';
		dataTicket.post_id = post_id;
		dataTicket.vote = $(this).attr('name');
		dataTicket.vote_nonce = $('#vote_nonce').val();
		dco_kb_ajax_post_submit( dataTicket , function( data ){
			if ( data.new_count ){
				$this.html(  data.new_count  );
				$this.removeClass('processing');

			}
		});
	});


});