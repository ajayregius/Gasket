<?php 
/*
Plugin Name: Adsense Widget
Plugin URI: http://shailan.com/wordpress/plugins/adsense-widget/
Description: Simply the best <strong>Google Adsense Widget</strong>. Select ad type easily on your site. Define slot & channel ids for statistics. Color customization via php. Easy to use shortcode <code>[adsense]</code>! Powered by <a href="http://shailan.com/">Shailan.com</a>. Visit: <a href="http://shailan.com/wordpress/plugins/adsense-widget/">Plugin page</a> | <a href="http://shailan.com/wordpress/plugins/adsense-widget/help/">Documentation</a> | <a href="http://shailan.com/wordpress/plugins/adsense-widget/shortcode/">Shortcode Usage</a>.
Version: 1.6
Author: Matt Say
Author URI: http://shailan.com/
*/

if(!class_exists('stf_adsense')){
class stf_adsense extends WP_Widget {
    function stf_adsense() {	
		$widget_ops = array('classname' => 'stf-adsense', 'description' => __( 'Google adsense widget' ) );
		$this->WP_Widget('stf-adsense', __('Adsense'), $widget_ops);
		$this->alt_option_name = 'stf_adsense';	
		
		$this->version = "1.6";
		$this->settings_key = "stf_adsense";
		$this->options_page = "adsense-widget";
		
		// Default widget options
		$this->widget_defaults = array(
			'title' => '',
			'slot' => '',
			'channel'	=> '',
			'type' => 'banner',
			'content' => 'text_image',
			'adsense_id' => ''
		);
		
		// Ad content types
		$this->content_types = array( 'text', 'image', 'text_image' );
		
		// Include ad type array
		require_once("stf-ad-types.php");
		$this->ad_types = $ad_types;
		
		// Include options array
		require_once("stf-adsense-options.php");
		$this->options = $options;
		$this->settings = $this->get_plugin_settings();
		
		if(!is_admin()){
			wp_enqueue_style( "stf-adsense", WP_PLUGIN_URL . "/adsense-widget/adsense-widget.css", false, "1.0", "all");	
		}
		
		add_action('admin_menu', array( &$this, 'stf_adsense_admin') );
    }
	
	function get_plugin_settings(){
		$settings = get_option( $this->settings_key );		
		
		if(FALSE === $settings){ // Options doesn't exist, install standard settings
			// Create settings array
			$settings = array();
			// Set default values
			foreach($this->options as $option){
				if( array_key_exists( 'id', $option ) )
					$settings[ $option['id'] ] = $option['std'];
			}
			
			// Move adsense id option to new settings
			$adsid = get_option('shailan_adsense_id');
			if(!empty($adsid)){ $settings['adsense_id'] = $adsid; }
			
			$settings['version'] = $this->version;
			// Save the settings
			update_option( $this->settings_key, $settings );
		} else { // Options exist, update if necessary
			
			if( !empty( $settings['version'] ) ){ $ver = $settings['version']; } 
			else { $ver = ''; }
			
			if($ver != $this->version){ // Update needed
			
				// Move adsense id option to new settings
				$adsid = get_option('shailan_adsense_id');
				if(!empty($adsid)){ $settings['adsense_id'] = $adsid; }
			
				// Add missing keys
				foreach($this->options as $option){
					if( array_key_exists ( 'id' , $option ) && !array_key_exists ( $option['id'] ,$settings ) ){
						$settings[ $option['id'] ] = $option['std'];
					}
				}
				
				update_option( $this->settings_key, $settings );
				
				return $settings; 
			} else { 
			
				// Move adsense id option to new settings
				$adsid = get_option('shailan_adsense_id');
				if( !empty($adsid) ){
					$settings['adsense_id'] = $adsid; 
					update_option( $this->settings_key, $settings );
					delete_option('shailan_adsense_id');
				}
			
				// Everythings gonna be alright. Return.
				return $settings;
			} 
		}		
	}
	
	function update_plugin_setting( $key, $value ){
		$settings = $this->get_plugin_settings();
		$settings[$key] = $value;
		update_option( $this->settings_key, $settings );
	}
	
	function get_plugin_setting( $key, $default = '' ) {
		$settings = $this->get_plugin_settings();
		if( array_key_exists($key, $settings) ){
			return $settings[$key];
		} else {
			return $default;
		}
		
		return FALSE;
	}
	
function stf_adsense_admin(){

	if ( @$_GET['page'] == $this->options_page ) {		
		
		if ( @$_REQUEST['action'] && 'save' == $_REQUEST['action'] ) {
		
			// Save settings
			// Get settings array
			$settings = $this->get_settings();
			
			// Set updated values
			foreach($this->options as $option){					
				if( $option['type'] == 'checkbox' && empty( $_REQUEST[ $option['id'] ] ) ) {
					$settings[ $option['id'] ] = 'off';
				} else {
					$settings[ $option['id'] ] = $_REQUEST[ $option['id'] ]; 
				}
			}
			
			// Save the settings
			update_option( $this->settings_key, $settings );
			header("Location: admin.php?page=" . $this->options_page . "&saved=true&message=1");
			die;
		} else if( @$_REQUEST['action'] && 'reset' == $_REQUEST['action'] ) {
			
			// Start a new settings array
			$settings = array();
			delete_option( $this->settings_key );
			
			// Set standart values
			// foreach($this->options as $option){ $settings[$option['id']] = $option['std']; }
			
			// Save the settings
			// update_option( $this->settings_key, $settings );			
			header("Location: admin.php?page=" . $this->options_page . "&reset=true&message=2");
			die;
		}
		
		// Enqueue scripts & styles
		wp_enqueue_script( "jquery" );
		wp_enqueue_script( "tweetable", WP_PLUGIN_URL . '/adsense-widget/js/jquery.tweetable.js', 'jquery' );
		wp_enqueue_style( "tweetable", WP_PLUGIN_URL . '/adsense-widget/css/tweetable.css' );
		wp_enqueue_style( "stf-adsense", WP_PLUGIN_URL . "/adsense-widget/adsense-widget-admin.css", false, "1.0", "all");	
		wp_enqueue_style( "google-droid-sans", "http://fonts.googleapis.com/css?family=Droid+Sans:regular,bold&v1", false, "1.0", "all");	
		
		
	}

	$page = add_options_page( __('Adsense Widget Options', 'adsense-widget') , __('Adsense Widget', 'adsense-widget'), 'edit_themes', $this->options_page, array( &$this, 'options_page') );
}

function options_page(){
	global $options, $current;

	$title = "Adsense Widget Options";
	
	$options = $this->options;	
	$current = $this->get_plugin_settings();
	
	$messages = array( 
		"1" => __("Adsense Widget settings saved.", "adsense-widget"),
		"2" => __("Adsense Widget settings reset.", "adsense-widget")
	);
	
	$navigation = '<div id="stf_nav"><a href="http://shailan.com/wordpress/plugins/adsense-widget/">Plugin page</a> | <a href="http://shailan.com/wordpress/plugins/adsense-widget/help/">Usage</a> | <a href="http://shailan.com/wordpress/plugins/adsense-widget/shortcode/">Shortcode</a> | <a href="http://shailan.com/donate/">Donate</a> | <a href="http://shailan.com/wordpress/">Get more widgets..</a></div>
	
<div class="stf_share">
	<div class="share-label">
		Like this plugin? 
	</div>
	<div class="share-button tweet">
		<a href="http://twitter.com/share" class="twitter-share-button" data-url="http://shailan.com/wordpress/plugins/adsense-widget/" data-text="I am using #adsense #widget on my #wordpress blog, Check this out!" data-count="horizontal" data-via="shailancom">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
	</div>
	<div class="share-button facebook">
		<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
		<fb:like href="http://shailan.com/wordpress/plugins/adsense-widget/" ref="plugin_options" show_faces="false" width="300" font="segoe ui"></fb:like>
	</div>
</div>
	
	';
	
	$footer_text = '<em><a href="http://shailan.com/wordpress/plugins/adsense-widget/">Adsense widget</a> by <a href="http://shailan.com/">SHAILAN </a></em>';
	
	include_once( "stf-page-options.php" );

}

    function widget($args, $instance) {		
	
		extract( $args );
		$widget_options = wp_parse_args( $instance, $this->widget_defaults );
		extract( $widget_options, EXTR_SKIP );
		
		$settings = $this->get_plugin_settings();
		
		if( empty($adsense_id) ){
			$adsense_id = $this->get_plugin_setting('adsense_id', '');
		}
		
		$msg = '';
		
		if(empty($adsense_id)){
			echo "<div class='warning'>Please enter your google ads id in the <a href=\"". get_bloginfo('url') . "/wp-admin/options-general.php?page=adsense-widget\">Adsense Widget Options Panel</a>.</div>";

		} else {
			
			if(!empty($slot) && $slot != ""){
				$ad_slot = ' google_ad_slot = "'.$slot.'"; ';
			} else {
				$ad_slot = "\n\t /* ad slot is empty */ ";
				$ad_colors = "";
				
				if( !empty( $settings['google_color_border'] ) ){ $ad_colors .= "\n\tgoogle_color_border = \"".$settings['google_color_border']."\";"; }
				if( !empty( $settings['google_color_link'] ) ){ $ad_colors .= "\n\tgoogle_color_link = \"".$settings['google_color_link']."\";"; }
				if( !empty( $settings['google_color_url'] ) ){ $ad_colors .= "\n\tgoogle_color_url = \"".$settings['google_color_url']."\";"; }
				if( !empty( $settings['google_color_text'] ) ){ $ad_colors .= "\n\tgoogle_color_text = \"".$settings['google_color_text']."\";"; }
				if( !empty( $settings['google_color_bg'] ) ){ $ad_colors .= "\n\tgoogle_color_bg = \"".$settings['google_color_bg']."\";"; }
				
				if(empty($ad_colors)){
					$ad_colors = "/* adcolors not defined */";
				} 
			}
				
			if(!empty($channel)){
				$ad_channel = ' google_ad_channel = "'.$channel.'"; ';
			} else { $ad_channel = "/* ad channel is empty */"; }
			
			$ad_type = ' google_ad_type = "'.$content.'"; ';
			
			$ad = $this->ad_types[$type];
			$ad_class = $ad['classname'];
			$ad_size = $ad['script'];

			if( !current_user_can( $settings['hide_ads_for'] ) ){
			
				echo $before_widget;
				if (!empty($instance['title']))
					echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
			
				// Echo adsense code
				echo "<div class=\"adsense ".$ad_class."\">";
				
				// Script part
				echo "<script type=\"text/javascript\"><!--
		google_ad_client = \"".$adsense_id."\";";
				echo "\n\t" . $ad_size;
				echo "\n\t" . $ad_slot;
				echo "\n\t" . $ad_channel;
				echo "\n\t" . $ad_type;
				echo "\n\t" . $ad_colors;
				echo "\n\t //-->
		</script>
		<script type=\"text/javascript\"
		src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">
		</script>"; 
		
				echo "</div>";
				echo $after_widget; 
	
			} elseif( current_user_can('administrator') && 'on' == $settings['show_placeholders'] ) {
			
				echo $before_widget;
				if (!empty($instance['title']))
					echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
				// Echo adsense code
				echo "<div class=\"adsense placeholder ".$ad_class."\">";
					echo "<table><tr><td class=\"placeholder-text\"><small>" . __("Adsense Unit") . "</small><br />" . $ad['name'] .  "</td></tr></table>";
				echo "</div>";
				echo $after_widget; 
				
			} else {
				
				// Hide everything..
				
			}		
		}
    }

    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    function form($instance) {  
		
		$widget_options = wp_parse_args( $instance, $this->widget_defaults );
		extract( $widget_options, EXTR_SKIP );
		
		if (!empty($instance['title'])) 
			$title = esc_attr($instance['title']);
			
		if (!empty($instance['type'])):
			$type = $instance['type'];
		else:
			$type = null;
		endif;
		
		if(empty($adsense_id)){
			$adsense_id = get_option('shailan_adsense_id', '');
		}
		
		?>
		
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :', 'adsense-widget'); ?> <small><a href="http://shailan.com/wordpress/plugins/adsense-widget/help/#title" target="_blank" rel="external">(?)</a></small> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label><br />

		<p><label for="<?php echo $this->get_field_id('adsense_id'); ?>"><?php _e('Adsense id :', 'adsense-widget'); ?> <small><a href="http://shailan.com/wordpress/plugins/adsense-widget/help/#adsense-id" target="_blank" rel="external">(?)</a></small> <input class="widefat" id="<?php echo $this->get_field_id('adsense_id'); ?>" name="<?php echo $this->get_field_name('adsense_id'); ?>" type="text" value="<?php echo $adsense_id; ?>" /></label><br />
		
		
		<p><label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Ad type:', 'adsense-widget'); ?> <small><a href="http://shailan.com/wordpress/plugins/adsense-widget/help/#ad-type" target="_blank" rel="external">(?)</a></small> <select name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>" >
		 <?php 
		  foreach ($this->ad_types as $key=>$ad) {  
			$option = '<option value="'.$ad['key'] .'" '. ( $ad['key'] == $type ? ' selected="selected"' : '' ) .'>';
			$option .= $ad['name'];
			$option .= '</option>\n';
			echo $option;
		  }
		 ?>
		</select></label><br /> 
		<small></small></p>	
		
		<p><label for="<?php echo $this->get_field_id('slot'); ?>"><?php _e('Slot ID (Optional):', 'adsense-widget'); ?> <small><a href="http://shailan.com/wordpress/plugins/adsense-widget/help/#slot-id" target="_blank" rel="external">(?)</a></small> <input class="widefat" id="<?php echo $this->get_field_id('slot'); ?>" name="<?php echo $this->get_field_name('slot'); ?>" type="text" value="<?php echo $slot; ?>" /></label><br />
		<small>Slot id for the ad you created.( Eg. 1234567890 )</small><br /> 
			
		<p><label for="<?php echo $this->get_field_id('channel'); ?>"><?php _e('Channel ID (Optional):', 'adsense-widget'); ?> <small><a href="http://shailan.com/wordpress/plugins/adsense-widget/help/#channel-id" target="_blank" rel="external">(?)</a></small> <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo $channel; ?>" /></label><br /> 
		<small>Your channel id.( Eg. 1234567890 )</small><br /> 
		
		<p><label for="<?php echo $this->get_field_id('content'); ?>"><?php _e('Content type :', 'adsense-widget'); ?> <small><a href="http://shailan.com/wordpress/plugins/adsense-widget/help/#content" target="_blank" rel="external">(?)</a></small> <select name="<?php echo $this->get_field_name('content'); ?>" id="<?php echo $this->get_field_id('content'); ?>" >
		 <?php 
		  foreach ( $this->content_types as $content_type ) {  
			$option = '<option value="'. $content_type .'" '. ( $content_type == $content ? ' selected="selected"' : '' ) .'>';
			$option .= $content_type;
			$option .= '</option>\n';
			echo $option;
		  }
		 ?>
		</select></label><br /> 
		<small></small></p>	
			
		<div class="widget-control-actions">
			<p><small>Powered by <a href="http://shailan.com/wordpress/plugins/adsense-widget/" title="Wordpress Tips and tricks, Freelancing, Web Design">Shailan.com</a> | <a href="http://shailan.com/wordpress/" title="Get more wordpress widgets and themes">Get more..</a></small></p>
		</div>
		
		<?php
    }

} // class stf_adsense 

add_action('widgets_init', create_function('', 'return register_widget("stf_adsense");'));

function stf_adsense_shortcode($atts, $content = null ){
	extract(shortcode_atts( array(
		'align' => 'none',
		'type' => '',
		'slot' => '',
		'channel' => '',
		'userid' => ''
	), $atts));
	
	$settings = get_option('stf_adsense');
	if( $channel=="" && array_key_exists( 'shortcode_channel_id', $settings ) ){ $channel = $settings['shortcode_channel_id']; }
	if( $type=="" && array_key_exists( 'default_ad_type', $settings ) ){ $type = $settings['default_ad_type']; }
	if( $type=="" ){ $type="banner"; }
	
	// Open adsense layer
	$adcode = "\n<div class=\"stf-adsense-shortcode align".$align."\">";

	ob_start();
	$args = array(
		'title' => '',
		'type' => $type,
		'slot' => $slot,
		'channel' => $channel,
		'adsense_id' => $userid
	);
	the_widget('stf_adsense', $args);
	$widget_code = ob_get_contents();
	ob_end_clean();
	$adcode .= $widget_code;	
	
	// Close the layer
	$adcode .= "\n</div>\n";
	return $adcode;	
}; add_shortcode('adsense', 'stf_adsense_shortcode'); 

} // class exist check
