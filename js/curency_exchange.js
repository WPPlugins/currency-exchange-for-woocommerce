(function ($){
    $(document).ready( function () {
        ce_execute_func( the_ce_js_data.script.js_page_load );
        $(document).on( 'change', '.br_ce_currency_select, .br_ce_select_currency', function(event){
            ce_execute_func( the_ce_js_data.script.js_before_set );
            var val = $(this).val();
            $.cookie( 'br_ce_language', val, { path: '/', domain: document.domain } );
            $('.br_ce_currency_select').val(val);
            $('.br_ce_'+val).prop('checked', true);
            ce_execute_func( the_ce_js_data.script.js_after_set );
        });
        if( the_ce_js_data.visual_only ) {
            fx.base = the_ce_js_data.base;
            fx.rates = the_ce_js_data.rates;
            if( the_ce_js_data.current != 'none' ) {
                fx.settings = {
                    from : fx.base,
                    to : the_ce_js_data.current
                };
                ce_money_replace();
            }
        }
        jQuery(document).ajaxComplete(function() {
            if( the_ce_js_data.visual_only ) {
                ce_money_replace();
            }
        });
    });
    if( the_ce_js_data.visual_only ) {
        $(document).on('change', '.variations select', function() {
            ce_money_replace();
        });
    }
})(jQuery);
function ce_money_replace() {
    if( the_ce_js_data.current != 'none' ) {
        jQuery('span.amount').each(function(i, o) {
            if( ! jQuery(o).is('.exchanged') ) {
                var money = accounting.unformat(jQuery(o).text());
                money = fx.convert(money);
                money = accounting.formatMoney(money, the_ce_js_data.accounting);
                jQuery(o).html(money).addClass('exchanged');
            }
        });
    }
}
function ce_execute_func ( func ) {
    if( the_ce_js_data.script != 'undefined'
        && the_ce_js_data.script != null
        && typeof func != 'undefined' 
        && func.length > 0 ) {
        try{
            eval( func );
        } catch(err){
            alert('You have some incorrect JavaScript code (Currency Exchange)');
        }
    }
}