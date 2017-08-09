<?php
/**
 * List/Grid widget
 */
class BeRocket_CE_Widget extends WP_Widget 
{
    public static $defaults = array(
        'title'         => '',
        'type'          => 'select',
        'flag_width'    => '50',
        'currency_text'     => array('text'),
    );
	public function __construct() {
        parent::__construct("berocket_ce_widget", "WooCommerce Currency Exchange",
            array("description" => "Show currency exchange widget"));
    }
    /**
     * WordPress widget for display Curency Exchange buttons
     */
    public function widget($args, $instance)
    {
        $instance = wp_parse_args( (array) $instance, self::$defaults );
        $options = BeRocket_CE::get_ce_option('br_ce_buttons_page_option');
        set_query_var( 'title', apply_filters( 'ce_widget_title', $instance['title'] ) );
        set_query_var( 'type', apply_filters( 'ce_widget_type', $instance['type'] ) );
        set_query_var( 'options', $options );
        set_query_var( 'args', $args );
        echo $args['before_widget'];
        BeRocket_CE::br_get_template_part( apply_filters( 'ce_widget_template', 'widget' ) );
        echo $args['after_widget'];
	}
    /**
     * Update widget settings
     */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['flag_width'] = strip_tags( $new_instance['flag_width'] );
		$instance['currency_text'] = $new_instance['currency_text'];
		return $instance;
	}
    /**
     * Widget settings form
     */
	public function form($instance)
	{
        $instance = wp_parse_args( (array) $instance, self::$defaults );
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <p>
            <select class="ce_select_type" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                <option value="select"<?php if($instance['type'] == 'select') echo ' selected'; ?>><?php _e('Select', 'BeRocket_CE_domain') ?></option>
                <option value="radio"<?php if($instance['type'] == 'radio') echo ' selected'; ?>><?php _e('Radio', 'BeRocket_CE_domain') ?></option>
            </select>
        </p>
		<?php
	}
}
?>