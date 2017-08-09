var br_saved_timeout;
var br_savin_ajax = false;
var br_do_not_stop_loading = false;
(function ($){
    $(document).ready( function () {
        $('.ce_submit_form').submit( function(event) {
            event.preventDefault();
            if( !br_savin_ajax ) {
                br_savin_ajax = true;
                var form_data = $(this).serialize();
                form_data = 'action=br_ce_settings_save&'+form_data;
                var url = ajaxurl;
                clearTimeout(br_saved_timeout);
                destroy_br_saved();
                $('body').append('<span class="br_saved br_saving"><i class="fa fa-refresh fa-spin"></i></span>');
                $.post(url, form_data, function (data) {
                    if( ! br_do_not_stop_loading ) {
                        if($('.br_saved').length > 0) {
                            $('.br_saved').removeClass('br_saving').find('.fa').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-check');
                        } else {
                            $('body').append('<span class="br_saved"><i class="fa fa-check"></i></span>');
                        }
                        br_saved_timeout = setTimeout( function(){destroy_br_saved();}, 5000 );
                    }
                    br_savin_ajax = false;
                }, 'json').fail(function(data) {
                    if( ! br_do_not_stop_loading ) {
                        if($('.br_saved').length > 0) {
                            $('.br_saved').removeClass('br_saving').addClass('br_not_saved').find('.fa').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-times');
                        } else {
                            $('body').append('<span class="br_saved br_not_saved"><i class="fa fa-times"></i></span>');
                        }
                        br_saved_timeout = setTimeout( function(){destroy_br_saved();}, 5000 );
                        $('.br_save_error').html(data.responseText);
                    }
                    br_savin_ajax = false;
                });
            }
        });
        function destroy_br_saved() {
            $('.br_saved').addClass('br_saved_remove');
            var $get = $('.br_saved');
            setTimeout( function(){$get.remove();}, 200 );
        }
        $(window).on('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                switch (String.fromCharCode(event.which).toLowerCase()) {
                case 's':
                    event.preventDefault();
                    $('.ce_submit_form').submit();
                    break;
                }
            }
        });
        $('.br_ce_settings .nav-tab').click(function(event) {
            event.preventDefault();
            $('.nav-tab-active').removeClass('nav-tab-active');
            $('.nav-block-active').removeClass('nav-block-active');
            $(this).addClass('nav-tab-active');
            $('.'+$(this).data('block')+'-block').addClass('nav-block-active');
        });
        $('.update_open_exchange').click(function(event) {
            br_do_not_stop_loading = true;
            $(this).parents('form').trigger('submit');
            update_open_exchange();
        });
        function update_open_exchange() {
            if( !br_savin_ajax ) {
                br_savin_ajax = true;
                var url = ajaxurl;
                var form_data = {action: 'open_exchange_load'}
                $.post(url, form_data, function (data) {
                    br_savin_ajax = false;
                    location.reload();
                }).fail(function() {
                    br_savin_ajax = false;
                    location.reload();
                });
            } else {
                setTimeout(update_open_exchange, 250);
            }
        }
        $(document).on('change', '.ce_select_type', function(e) {
            if($(this).val() == 'image') {
                $('.ce_image_type').show();
            } else {
                $('.ce_image_type').hide();
            }
        });
        $(document).on('click', '.berocket_aapf_upload_icon', function(e) {
            e.preventDefault();
            $p = $(this);
            var custom_uploader = wp.media({
                title: 'Select custom Icon',
                button: {
                    text: 'Set Icon'
                },
                multiple: false 
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $p.prevAll(".berocket_aapf_selected_icon_show").html('<i class="fa"><image src="'+attachment.url+'" alt=""></i>');
                $p.prevAll(".berocket_aapf_icon_text_value").val(attachment.url);
            }).open();
        });
        $(document).on('click', '.berocket_aapf_remove_icon',function(event) {
            event.preventDefault();
            $(this).prevAll(".berocket_aapf_icon_text_value").val("");
            $(this).prevAll(".berocket_aapf_selected_icon_show").html("");
        });
        $(document).on('click', '.add_br_currency_text', function(event) {
            event.preventDefault();
            var field_name = $(this).data('field_name');
            var name = $(this).data('name');
            var id = $(this).data('id');
            $('.br_currency_text').append($('<li><i class="button fa fa-caret-left"></i><i class="button fa fa-caret-right"></i><div style="clear:both;"></div><input type="hidden" name="'+field_name+'" value="'+id+'"><span class="br_type_of_text">'+name+'</span></li>'));
        });
        $(document).on('click', '.br_currency_text li', function(event) {
            event.preventDefault();
            $(this).remove();
        });
        $(document).on('click', '.br_currency_text li .fa-caret-right', function(event) {
            event.preventDefault();
            event.stopPropagation();
            var $li = $(this).parent();
            if( $li.next().is('.br_currency_text li') ) {
                $li.next().after($li);
            }
        });
        $(document).on('click', '.br_currency_text li .fa-caret-left', function(event) {
            event.preventDefault();
            event.stopPropagation();
            var $li = $(this).parent();
            if( $li.prev().is('.br_currency_text li') ) {
                $li.prev().before($li);
            }
        });
        $(document).on('change', '.br_ce_currency_site', function() {
            $('.site_data_block').hide();
            $('.'+$(this).val()+'_data_block').show();
        });
    });
})(jQuery);