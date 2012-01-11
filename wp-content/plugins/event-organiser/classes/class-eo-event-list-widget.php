<?php
/**
 * Class used to create the event list widget
 */
class EO_Event_List_Widget extends WP_Widget{

	var $w_arg = array(
		'title'=> 'Events',
		'numberposts'=> 5,
		'event-category'=> '',
		'venue_id'=> NULL,
		'venue'=> '',
		'orderby'=> 'eventstart',
		'showpastevents'=> 0,
		'order'=> 'ASC'
		);

  function EO_Event_List_Widget(){
    $widget_ops = array('classname' => 'EO_Event_List_Widget', 'description' => 'Displays a list of events' );
    $this->WP_Widget('EO_Event_List_Widget', 'Events', $widget_ops);
  }
 
  function form($instance){	
	$instance = wp_parse_args( (array) $instance, $this->w_arg );
  ?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'dbem'); ?>: </label>
	<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
</p>
  <p>
  <label for="<?php echo $this->get_field_id('numberposts'); ?>">Number of events:   </label>
	  <input id="<?php echo $this->get_field_id('numberposts'); ?>" name="<?php echo $this->get_field_name('numberposts'); ?>" type="number" size="3" value="<?php echo $instance['numberposts'];?>" />
</p>
  <p>
  <label for="<?php echo $this->get_field_id('event-category'); ?>">Event categories:   </label>
  <input  id="<?php echo $this->get_field_id('event-category'); ?>" class="widefat" name="<?php echo $this->get_field_name('event-category'); ?>" type="text" value="<?php echo $instance['event-category'];?>" />
   <em>list category slug(s), seperate by comma. Leave blank for all</em>
</p>
  <p>
  <label for="<?php echo $this->get_field_id('venue'); ?>">Venue:   </label>
	<?php 	$venues = new EO_Venues;
			$venues->query();?>
	<select id="<?php echo $this->get_field_id('venue'); ?>" name="<?php echo $this->get_field_name('venue'); ?>" type="text">
		<option value="" <?php selected($instance['venue'], ''); ?>>All Venues </option>
		<?php foreach ($venues->results as $thevenue):?>
			<option <?php  selected($instance['venue'],$thevenue['venue_slug']);?> value="<?php echo $thevenue['venue_slug'];?>"><?php echo $thevenue['venue_name']; ?></option>
		<?php endforeach;?>
	</select>
</p>

  <p>
  <label for="<?php echo $this->get_field_id('orderby'); ?>">Order by</label>
	<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>" type="text">
		<option value="eventstart" <?php selected($instance['orderby'], 'eventstart'); ?>>Start date </option>
		<option value="title" <?php selected($instance['orderby'], 'title');?>>Event Title </option>
	</select>
	<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" type="text">
		<option value="asc" <?php selected($instance['order'], 'asc'); ?>>ASC </option>
		<option value="desc" <?php selected($instance['order'], 'desc');?>>DESC </option>
	</select>
</p>
  <p>
    <label for="<?php echo $this->get_field_id('showpastevents'); ?>">Include past events  </label>
	<input type="checkbox" id="<?php echo $this->get_field_id('showpastevents'); ?>" name="<?php echo $this->get_field_name('showpastevents'); ?>" <?php checked($instance['showpastevents'],1);?> value="1" />
  </p>
<?php
  }
 

  function update($new_instance, $old_instance){  
	foreach($this->w_arg as $name => $val){
		if( empty($new_instance[$name]))
			$new_instance[$name] = $val;
    	}
	return $new_instance;
    }

 
 
  function widget($args, $instance){
	extract($args, EXTR_SKIP);

	$events = eo_get_events($instance);
	
    	echo $before_widget;
    	echo $before_title;
	echo $instance['title'];
    	echo $after_title;

	if($events):	
		echo '<ul class="eo-events eo-events-widget">';
		foreach ($events as $event):
			//Check if all day, set format accordingly
			if($event->event_allday){
				$format = get_option('date_format');
			}else{
				$format = get_option('date_format').'  '.get_option('time_format');
			}
			echo '<li><a title="'.$event->post_title.'" href="'.get_permalink($event->ID).'">'.$event->post_title.'</a> on '.eo_format_date($event->StartDate.' '.$event->StartTime, $format).'</li>';
		endforeach;
		echo '</ul>';
	endif;
     	echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("EO_Event_List_Widget");') );?>
