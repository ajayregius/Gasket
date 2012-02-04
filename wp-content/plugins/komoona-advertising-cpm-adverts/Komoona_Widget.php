<?php

if(!class_exists('Komoona_Widget')) : // declare Kmn_Func class only if class has not been defined (working with multiple Komoona plugins)

/**
 * Komoona Widget Class
 */
abstract class Komoona_Widget extends WP_Widget {

    /**
     * @access private
     * @var string plugin type
     */
    private $__type;
    
    /** constructor */
    function Komoona_Widget($type) {
        
        $this->__type = $type;
        
        $description = sprintf('Show %s Unit in your sidebar', Kmn_Func::komoona_plugin_name($this->__type));
        
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'Komoona', 'description' => $description );

        /* Widget control settings. */
        $control_ops = array( 'width' => 200, 'height' => 100, 'id_base' => $this->__type . '_widget' );

        $komoona_name = Kmn_Func::komoona_plugin_name($this->__type);
        
        parent::WP_Widget($this->__type . '_widget', $name = $komoona_name, $widget_ops, $control_ops);
    }

    // outputs the content of the widget
    function widget($args, $instance) {
        extract( $args );

        /* Our variables from the widget settings. */
        $title = apply_filters('widget_title', $instance['title'] );

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Display the widget title if one was input (before and after defined by themes). */
        if ( $title ) {
          echo $before_title . $title . $after_title;
        }

        // Ad unit (verify that Komoona is enabled)
        $widget_code = get_option($this->__type . '_widget_layout_id');
        $widget_code = str_replace('\"', '"', $widget_code);
        
        if(strpos($widget_code, '<div id="') === FALSE) {
            $widget_code = '<div id="' . $widget_code . '"></div>';
        }
        
        echo $widget_code;
        
        /* After widget (defined by themes). */
        echo $after_widget;
    }

    // processes widget options to be saved
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    // outputs the options form on admin
    // outputs the options form on admin
    function form($instance) {
        // make sure the plugin is already installed, if not add link to the settings page
        $komoona_username = get_option($this->__type . '_username');
        $komoona_layout = get_option($this->__type . '_widget_layout_id');
        if(strlen($komoona_username) == 0 && strlen($komoona_layout) == 0) :
            $settings_link = sprintf('<a href="options-general.php?page=%s_options">settings page</a>', $this->__type); 
        ?>
            <p><span style="font-weight:bold;line-height: 140%;display:block;"><?php _e('Your plugin is not configured yet.', 'komoona'); ?></span><br/><?php _e('Please go to the plugin', 'komoona'); ?> <?php echo $settings_link; ?><?php _e(' to start using the plugin', 'komoona'); ?></p>
        <?php else:
        $title = esc_attr($instance['title']); ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php endif;
    }
} // class KomoonaWidget

class Komoona_Ads_Widget extends Komoona_Widget {
    function Komoona_Ads_Widget() {
        parent::Komoona_Widget(Kmn_Func::KOMOONA_ADS);
    }
} // class Komoona_Ads_Widget

class Komoona_AdSense_Widget extends Komoona_Widget {
    function Komoona_Adsense_Widget() {
        parent::Komoona_Widget(Kmn_Func::KOMOONA_ADSENSE);
    }
} // class Komoona_AdSense_Widget

class Komoona_Cpm_Widget extends Komoona_Widget {
    function Komoona_Cpm_Widget() {
        parent::Komoona_Widget(Kmn_Func::KOMOONA_CPM);
    }
} // class Komoona_Cpm_Widget

endif; // Komoona_Widget exists
?>