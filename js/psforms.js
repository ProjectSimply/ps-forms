(function ( $ ) {
  "use strict";

  $(function () {

    $(document).ready(function(){

      nicerFormElements();

      init_multipage();

    });

    var oldSubmitMessages = {};

    var validate_field = function(field,message) {


      var name      = field.attr('name'),
          form      = field.closest('form'),
          note      = field.parent('.ps-input-holder').attr('class');

      var validation = $('.ps-validation[data-for="' + name + '"]', form);
          var error = $('.ps-error-message[data-for="' + name + '"]', form);

          //Set the  default transitions for the error message, can be overriden by data-variable
          var messageTransitionIn = 'ps-transition-bounceDown-in';
          if(error.attr('data-transition-in'))
            messageTransitionIn = error.attr('data-transition-in');

          var messageTransitionOut = 'ps-transition-moveTop-out';
          if(error.attr('data-transition-out'))
            messageTransitionOut = error.attr('data-transition-out');

          validation.attr('class','ps-validation');

          switch(message){
            case 'success':

              error.hide();
              //if there was an error showing, add a leaving transition and then deactivate
              if(error.hasClass('active')) {
                error.attr('class','active ps-error-message').addClass(messageTransitionOut);
              }
              //add correct icon and set off flip in transition
              validation.addClass('foundicon-checkmark ps-transition-flipX-in ps-validation-success');
              
              $(field).closest('.ps-input-holder').removeClass('error');
              //add success class to input
              $(field).closest('.ps-input-holder').addClass('success');

            break;
            case 'no validation':
            break;
            default:

              //reset the error class ready to transition and add html
              error.attr('class','ps-error-message');
              error.show();

              //add correct icon and set off flip in transition
              validation.addClass('foundicon-remove ps-transition-flipX-in ps-validation-error');
              //add error message and make message field active
              error.html(message).addClass('active ' + messageTransitionIn);

              //incase the field content was valid but now isn't amend the class
              $(field).closest('.ps-input-holder').addClass('error').removeClass('success');
              
            break;
          }

    } 


    var show_error_notice = function(type,message) {

      $('.ps-fixed-message').remove();

      var errorMessage = document.createElement('div');
      $(errorMessage).addClass('ps-fixed-message ps-transition-bounceDown-in ' + type);
      $(errorMessage).html(message);

      $('body').prepend(errorMessage);

      setTimeout(function(){

        $(errorMessage).addClass('ps-transition-bounceUp-out');


      },7000);

    }

    //Capture radio and checkbox selected vals into commas seperated list
    var prepare_multiples = function(name,form) {
      var data = '';

      $('input[name="' + name + '"]:checked',form).each(function(i){
        data += $(this).val();
        if(i+1 != $('input[name="' + name + '"]:checked',form).length)
          data += ',';
      });

      return data;

    }

    //Run our function on blur and on change but prevent any unwanted duplicate
    //firings
    var blurChange = function(thisField){

        clearTimeout(blurChange.timeout);

        blurChange.timeout = setTimeout(function(){
              
        var name      = thisField.attr('name'),
            form      = thisField.closest('form'),
            val       = thisField.val();

        if(thisField.attr('type') == 'checkbox')
          val = prepare_multiples(name,form);

        //Post call to data
        var postdata = {
              'action'    : 'ps_check_field',
              'name'      : name,
              'value'     : val,
              'form'      : $('input[name=ps-form-name]',form).val()
            };

        $.ajax({
            'type'      : 'GET',
            'dataType'  : 'json',
            'url'       : ajaxURL,
            'data'      : postdata,
            'success'   : function(data) {

              validate_field(thisField,data);

            }

        });

        }, 100);
    }



    //  ***************************************** //
    //  Validate as we go along                   //
    //  ***************************************** //
    $('body').on('blur change','input, textarea, select','.ps-ajax-form',function(){

      if($(this).is(':input[type=button], :input[type=submit], :input[type=reset]'))
        return true;

      blurChange($(this));
      
      

    });

    var nicerFormElements = function() {
        //  ***************************************** //
        //  radio button action //
        //  ***************************************** //
        $('body').on('click','label.radio',function(){
            var name = $('input',this).attr('name');
            $('input[name="' + name + '"]').parent().removeClass('selected');
            $(this).addClass('selected');
        });

        //  ***************************************** //
        //                    checkbox button action  //
        //  ***************************************** //
        $('body').on('click', 'label.checkbox input', function(){
            $(this).parent().children('span').toggleClass('foundicon-checkmark');
        });
    } 
    

    //  ***************************************** //
    //  Let's hijack the submission of the form!  //
    //  ***************************************** //
    $('body').on('submit','.ps-ajax-form',function(e){

      var postdata = {
        'action'    : 'ps_forms_validate_data',
        'inputs'  : {}
      };

      e.preventDefault();

      var thisform = $(this), 
          thisName = $(thisform).children(':input[type=hidden]').attr('value');

      //Loop all the inputs
      $('input, textarea, select',thisform).not(':input[type=button], :input[type=submit], :input[type=reset]').each(function(i,elem){
        var val = $(elem).val();

        if($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio')
          val = prepare_multiples($(elem).attr('name'),thisform);

        postdata['inputs'][$(elem).attr('name')] = val;
      });

      $('input[type=radio]:not(:checked),input[type=checkbox]:not(:checked)',thisform).each(function(i,elem){

        if(typeof(postdata['inputs'][$(elem).attr('name')]) == 'undefined')
          postdata['inputs'][$(elem).attr('name')] = '';
      });

      $.ajax({
        'type'      : 'GET',
        'dataType'  : 'json',
        'url'       : ajaxURL,
        'data'      : postdata,
        'success'   : function(data) {

          if( data.errors ){

            if($('input[type=submit]',thisform).hasClass('alert') == false)
              show_error_notice('alert',data.message);

            //filter through all inputs and add the correct message to the correct input
            //add transition filter class to stagger error messages down the form
            var i = 0;

            var result = [], keys = Object.keys(data.errors);

            var viewError = setInterval(function(){

                var name = keys[i], message = data.errors[keys[i]];
                var thisField = $('input[name="' + name + '"], select[name="' + name + '"]',thisform);

                validate_field(thisField,message);

                if(i==0 && $('.ps-input-holder.error').length > 0) {
                  var scrollto = $('.ps-input-holder.error').eq(0).offset().top-140;
                  if(scrollto<0) scrollto = 0;

                  if(scrollto < $(window).scrollTop())
                    $('html,body').animate({'scrollTop':scrollto},1000);
                }

                if ( i > keys.length)
                    clearInterval(viewError);
                i++;

              }, 500);

          } else {

                //Successful submission!
                show_error_notice('success',data.message);

                //reset inputs
                $('input, textarea, select',thisform).not(':input[type=button], :input[type=hidden], :input[type=submit], :input[type=reset]').removeClass('success').val('');
                $('.active',thisform).removeClass('active');

                //reset validation
                $('input, ps-input-holder',thisform).removeClass('error success');
                $('.ps-validation').attr('class','ps-validation').html('');
                $('.ps-error-message').attr('class','ps-error-message').html('');

             

          }
        }
      });

    });
  
    //Let's get our multpage forms cooking
    var init_multipage = function() {

      $('body').on('click','.ps-next-page',function(e){
        e.preventDefault();

        var form = $(this).closest('form');

        var currentPage = parseInt($(this).closest('.ps-form-page').attr('data-ps-form-page'));

        load_page(currentPage, form);

      });

    }

    //Loads the next page in multipage forms
    var load_page = function(currentPage,form) {
      var oldPage = $('.ps-form-page[data-ps-form-page=' + (currentPage) + ']',form);
      var newPage = $('.ps-form-page[data-ps-form-page=' + (currentPage+1) + ']',form);

      
      form.height(oldPage.outerHeight());

      //Put the new page on into view, but hidden, so we can get it's width
      newPage.css({
        'display'     : 'block',
        'visibiltity' : 'hidden'
      });
      

      newPage.css({
        'position'    : 'absolute',
        'top'         : 0,
        'left'        : 0,
        'width'       : newPage.width()
      }).addClass('ps-transition-moveRight-in');

      oldPage.addClass('ps-transition-moveLeft-out');


      form.height(newPage.outerHeight());

      var sto = setTimeout(function(){
        oldPage.attr('style','').hide().removeClass('active-page');
        newPage.attr('style','').addClass('active-page');
        form.attr('style','');
      },1000);


    }


  });

}(jQuery));