<?php if( $title ) echo $args['before_title'].$title.$args['after_title'];
global $wpdb;
$options = BeRocket_CE::get_ce_option('br_ce_buttons_page_option');
$currencies = get_woocommerce_currencies(); 
$current_currency = BeRocket_CE::get_woocommerce_currency();
$rand = rand();
?>
<div>
    <?php if ( ! isset($type) || $type == "" || $type == 'select' ) { ?>
    <select class="br_ce_currency_select">
        <?php
        echo '<option data-text="'.$currencies[$current_currency].'" value="">'.$currencies[$current_currency].'</option>';
        foreach($options['use_currency'] as $currency_slug)
        {
            if( $current_currency != $currency_slug ) {
                echo '<option data-text="'.$currencies[$currency_slug].'" value="'.$currency_slug.'"'.( ( isset(BeRocket_CE::$currency) && BeRocket_CE::$currency == $currency_slug ) ? ' selected' : '').'>'.$currencies[$currency_slug].'</option>';
            }
        }
        ?>
    </select>
    <?php } elseif ($type == 'radio') {
        echo '<div><label><input class="br_ce_select_currency br_ce_" name="ce_select_currency_'.$rand.'" type="radio" value=""'.( ( ! isset(BeRocket_CE::$currency) || BeRocket_CE::$currency == "" ) ? ' checked' : '').'>'.$currencies[$current_currency].'</label></div>';
        foreach($options['use_currency'] as $currency_slug)
        {
            if( $current_currency != $currency_slug ) {
                echo '<div><label><input class="br_ce_select_currency" name="ce_select_currency_'.$rand.'" type="radio" value="'.$currency_slug.'"'.( ( isset(BeRocket_CE::$currency) && BeRocket_CE::$currency == $currency_slug ) ? ' checked' : '').'>'.$currencies[$currency_slug].'</label></div>';
            }
        }
    } ?>
</div>