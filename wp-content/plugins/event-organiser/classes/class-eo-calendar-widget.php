<?php 
/**
 * Class used to create the event calendar widget
 */
class EO_Calendar_Widget extends WP_Widget
{
	var $w_arg = array(
		'title'=> '',
		);

  function EO_Calendar_Widget()  {
	$widget_ops = array('classname' => 'widget_calendar', 'description' => 'Displays calendar' );
	$this->WP_Widget('EO_Calendar_Widget', 'Events Calendar', $widget_ops);
  }
 

  function form($instance)  {
	
    $instance = wp_parse_args( (array) $instance, $this->w_arg );
?>
  <p>
	  <label for="<?php echo $this->get_field_id('title'); ?>">TItle:  </label>
	  <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title'];?>" />
  </p>
  
<?php
  }
 

  function update($new_instance, $old_instance){
    
	foreach($this->w_arg as $name => $val){
		if( empty($new_instance[$name]) ){
			$new_instance[$name] = $val;
		}
	}
	return $new_instance;
    }

 
 
  function widget($args, $instance){
	wp_enqueue_script( 'eo_front');
	extract($args, EXTR_SKIP);

	//Set the month to display (DateTIme must be 1st of that month)
	$month = new DateTime();
	$month->modify('first day of this month');

	//Echo widget
    	echo $before_widget;
    	echo $before_title;
	echo $instance['title'];
    	echo $after_title;
	echo "<div class='eo-calendar eo-calendar-widget' id='eo_calendar'>";
	echo $this->generate_output($month);
	echo "</div>";
    	echo $after_widget;
  }


function generate_output($month,$args=array()){
	//Month should be a DateTime object of the first day in that month		
	$today = new DateTime();
	if(empty($args))
		$args=array();
	
	//Month details
	$firstdayofmonth= intval($month->format('N'));
	$lastmonth = clone $month;
	$lastmonth->modify('last month');	
	$nextmonth = clone $month;
	$nextmonth->modify('next month');
	$daysinmonth= intval($month->format('t'));

	//Retrieve the start day of the week from the options.
	$startDay=intval(get_option('start_of_week'));

	//How many blank cells before inserting dates
	$offset = ($firstdayofmonth-$startDay +7)%7;

	//Number of weeks to show in Calendar
	$totalweeks = ceil(($offset + $daysinmonth)/7);

	//Get events for this month
	$start = $month->format('Y-m-d');
	$end = $month->format('Y-m').'-'.$daysinmonth;

	$required = array('numberposts'=>-1,'showrepeats'=>1,'start_before'=>$end,'start_after'=>$start);
	$query_array = array_merge($args,$required);

	$events=  eo_get_events($query_array);
	
	//Populate events array
	$tableArray =array();
	foreach($events as $event):
		$date = esc_html($event->StartDate);
		$tableArray[$date][]= $event->post_title;
	endforeach;
	
	$daysofweek=array('S','M','T','W','T','F','S');

	$before = "<table id='wp-calendar'>";
	$title ="<caption>".$month->format('F Y')."</caption>";
	$head="<thead><tr>";
	for ($d=0; $d <= 6; $d++): 
			$day = $daysofweek[($d+$startDay)%7];
			$head.="<th title='".$day."' scope='col'>".$day."</th>";
	endfor;

	$head.="</tr></thead>";

	$foot = "<tfoot><tr>";
	$foot .="<td id='prev' colspan='3'><a title='Previous month'  href='?eo_month=".$lastmonth->format('Y-m')."'>&laquo; ".$lastmonth->format('M')."</a></td>";
	$foot .="<td class='pad'>&nbsp;</td>";
	$foot .="<td id='next' colspan='3'><a title='Next month' href='?eo_month=".$nextmonth->format('Y-m')."'>".$nextmonth->format('M')."&raquo; </a></td>";
	$foot .= "</tr></tfoot>";

	$body ="<tbody>";

	$currentDate = clone $month;
		
	$post_link = EO_Event::link_structure();
			
	for($w = 0; $w <= $totalweeks-1; $w++):
		$body .="<tr>";
		$cell = $w*7;
 		foreach ($daysofweek as $i => $day): 
			$cell = $cell+1;
			if($cell<=$offset ||$cell-$offset > $daysinmonth): 
					$body .="<td class='pad' colspan='1'>&nbsp;</td>";
			else:
				$class=array();
				if($currentDate==$today){
					$class[] ='today';
				}
				if(isset($tableArray[$currentDate->format('Y-m-d')])):
					$class[] ='event';
					$titles = implode(', ',$tableArray[$currentDate->format('Y-m-d')]);
					$classes = implode(' ',$class);
					$link = add_query_arg('ondate',$currentDate->format('Y-m-d'),$post_link);
					$body .="<td class='".$classes."'> <a title='".$titles."' href='".$link."'>".($cell-$offset)."</a></td>";
				else:
					$classes = implode(' ',$class);
					$body .="<td class='".$classes."'>".($cell-$offset)."</td>";
				endif;
				$currentDate->modify('+1 day');
			endif;
		 endforeach;
		$body .="</tr>";
		endfor;
	$body .="</tbody>";
	$after = "</table>";

	return $before.$title.$head.$foot.$body.$after;
}
 

}
add_action( 'widgets_init', create_function('', 'return register_widget("EO_Calendar_Widget");') );?>
