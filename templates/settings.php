<div class="wrap">
<?php 
$dplugin_name = 'WooCommerce Currency Exchange';
$dplugin_link = 'http://berocket.com/product/woocommerce-currency-exchange';
$dplugin_price = 18;
$dplugin_desc = '';
@ include 'settings_head.php';
@ include 'discount.php';
?>
<div class="wrap br_ce_settings show_premium">
    <div id="icon-themes" class="icon32"></div>
    <h2>Curency Exchange Settings</h2>
    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <a href="#currencies" class="nav-tab nav-tab-active currencies-tab" data-block="currencies"><?php _e('Currencies', 'BeRocket_CE_domain') ?></a>
        <a href="#css" class="nav-tab css-tab" data-block="css"><?php _e('CSS', 'BeRocket_CE_domain') ?></a>
        <a href="#javascript" class="nav-tab javascript-tab" data-block="javascript"><?php _e('JavaScript', 'BeRocket_CE_domain') ?></a>
    </h2>

    <form class="ce_submit_form" method="post" action="options.php">
        <?php 
        $options = BeRocket_CE::get_ce_option(); ?>
        <div class="nav-block currencies-block nav-block-active">
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('Visual only', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <label><input name="br_ce_options[visual_only]" value="1" type="checkbox"<?php echo ($options['visual_only'] ? ' checked' : ''); ?>><?php _e('If checked convert on shop and product pages. On the checkout main currency will be used', 'BeRocket_CE_domain') ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Exchange Rates', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <div class="mastercard_data_block site_data_block">
                            <div>
                                <input type="hidden" name="br_ce_options[last_oer_data][base]" value="<?php echo $options['last_oer_data']['base']?>">
                                <button class="update_open_exchange" type="button"><?php _e('Update Rates', 'BeRocket_CE_domain') ?></button>
                                <a href="https://www.mastercard.com/global/currencyconversion/" target="_blank">Master Card</a>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="ce_currencies">
                <h2><?php _e('Additional currencies', 'BeRocket_CE_domain') ?></h2>
                <p><?php _e('Please select currencies that You needed. Unselect current WooCommerce currency.', 'BeRocket_CE_domain') ?></p>
                <?php
                $currencies = get_woocommerce_currencies();
                foreach($currencies as $name => $currency) {
                    echo '<div class="br_ce_lang_select">
                        <input name="br_ce_options[use_currency][]" value="'.$name.'" type="checkbox"'.(in_array($name, $options['use_currency']) ? ' checked' : '').'>
                        <h3>'.$currency.'</h3>
                        <div>
                            <input name="br_ce_options[currency]['.$name.']" type="text" value="'.( isset($options['currency'][$name]) && $options['currency'][$name] > 0 ? $options['currency'][$name] : '1' ).'">
                        </div>
                    </div>';
                }
                ?>
            </div>
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('Shortcode', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <ul>
                        <li><strong>[br_currency_exchange]</strong></li>
                        <li>
                            <ul style="margin-left:2em;">
                                <li><i>title</i> - title of widget</li>
                                <li><i>type</i> - select, radio</li>
                            </ul>
                        </li>
                    </ul>
                    </td>
                </tr>
            </table>
        </div>
        <div class="nav-block css-block">
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('Custom CSS', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <textarea name="br_ce_options[custom_css]"><?php echo $options['custom_css']?></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <div class="nav-block javascript-block">
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('On Page Load', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <textarea name="br_ce_options[script][js_page_load]"><?php echo $options['script']['js_page_load']?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Before Language Set', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <textarea name="br_ce_options[script][js_before_set]"><?php echo $options['script']['js_before_set']?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('After Language Set', 'BeRocket_CE_domain') ?></th>
                    <td>
                        <textarea name="br_ce_options[script][js_after_set]"><?php echo $options['script']['js_after_set']?></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'BeRocket_CE_domain') ?>" />
    </form>
</div>
<?php
$feature_list = array(
    '<a href="https://openexchangerates.org/" target="_blank">Open Exchange Rates</a> support',
    '<a href="https://currencylayer.com/" target="_blank">CurrencyLayer</a> support',
    'Auto update currency every 24 hours',
    'Detect currency via IP address',
    'User can set currency in account settings',
    'Custom element position for currency label in widgets',
    'Country flag for currency label',
    'Custom image for currency label',
    'Multiplier for exchanged currencies',
);
@ include 'settings_footer.php';
?>
</div>
