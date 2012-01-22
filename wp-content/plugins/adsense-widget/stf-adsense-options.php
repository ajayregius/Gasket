<?php 

 /* Adsense widget options */
 
 require_once('stf-ad-types.php');
 
 $type_options = array();
 foreach($ad_types as $type=>$values){
	$type_options[$type] = $values['name'];
 }
 
 $hide_ads_options = array(
	"no-one" => "Do not hide ads",
	"add_users" => "Admins Only",
	"moderate_comments" => "Admins + Editors",
	"edit_published_posts" => "Admins + Editors + Authors",
	"edit_posts" => "Admins + Editors + Authors + Contributors",
	"read" => "All Registered Users"
 );
 
 
 
$options = array(

	array( "name" => __( "General Settings", 'adsense-widget' ),
		"type" => "section"),
		
	array( "type" => "open"),
	
		array(
			"type" => "text",
			"name" => __( "Adsense ID*", 'adsense-widget' ),
			"id" => "adsense_id",
			"desc" => __( "Your unique Adsense ID. This field is required for your ads to work <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#adsenseid' class='helplink' target='_blank'>(?)</a> </span>", 'adsense-widget' ),
			"std" => ""
		),
		
		array(
			"type" => "select",
			"options" => $hide_ads_options,
			"name" => __( "Hide ads for", 'adsense-widget' ),
			"id" => "hide_ads_for",
			"desc" => __( "Who will see the ads on your site <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#hide-ads' class='helplink' target='_blank'>(?)</a> </span>", 'adsense-widget' ),
			"std" => ""
		),
		
		array(
			"type" => "checkbox",
			"name" => __( "Show placeholders for admin", 'adsense-widget' ),
			"id" => "show_placeholders",
			"desc" => __( "Shows boxes in place of ads <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#show-placeholders' class='helplink' target='_blank'>(?)</a> </span>", 'adsense-widget' ),
			"std" => ""
		),
		
		array(
			"type" => "text",
			"name" => __( "Shortcode Channel ID", 'adsense-widget' ),
			"id" => "shortcode_channel_id",
			"desc" => __( "Default channel id for shortcodes <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#shortcode-channel-id' class='helplink' target='_blank'>(?)</a> </span>", 'adsense-widget' ),
			"std" => ""
		),
		
		array(
			"type" => "select",
			"options" => $type_options,
			"name" => __( "Default ad type for shortcode", 'adsense-widget' ),
			"id" => "default_ad_type",
			"desc" => __( "Default ad to be displayed using <code>[adsense]</code> shortcode with no attributes. See documentation for all shortcode options.<a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#default-ad-type' class='helplink' target='_blank'>(?)</a> </span>", 'adsense-widget' ),
			"std" => ""
		),
	
	array( "type" => "close"),
	
	array( "name" => __( "Color Scheme", 'adsense-widget' ),
		"type" => "section"),
		
		array(
			"type" => "text",
			"name" => __( "Background Color", 'adsense-widget' ),
			"id" => "google_color_bg",
			"desc" => __( "Background color for the ad unit <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#background-color' class='helplink' target='_blank'>(?)</a> </span>" , 'adsense-widget'),
			"std" => "#FFFFFF"
		),
		
		array(
			"type" => "text",
			"name" => __( "Border Color", 'adsense-widget' ),
			"id" => "google_color_border",
			"desc" => __( "Ad unit border color <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#border-color' class='helplink' target='_blank'>(?)</a> </span>" , 'adsense-widget'),
			"std" => "#EEEEEE"
		),
		
		array(
			"type" => "text",
			"name" => __( "Link Color", 'adsense-widget' ),
			"id" => "google_color_link",
			"desc" => __( "Link color <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#link-color' class='helplink' target='_blank'>(?)</a> </span>" , 'adsense-widget'),
			"std" => "#2277DD"
		),
		
		array(
			"type" => "text",
			"name" => __( "URL Text Color", 'adsense-widget' ),
			"id" => "google_color_url",
			"desc" => __( "Ad url color<a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#url-text-color' class='helplink' target='_blank'>(?)</a> </span>" , 'adsense-widget'),
			"std" => "#888888"
		),
		
		array(
			"type" => "text",
			"name" => __( "Text Color", 'adsense-widget' ),
			"id" => "google_color_text",
			"desc" => __( "Text color <a href='http://shailan.com/wordpress/plugins/adsense-widget/help/#text-color' class='helplink' target='_blank'>(?)</a> </span>" , 'adsense-widget'),
			"std" => "#666666"
		),
		
	array( "type" => "close")
	

);