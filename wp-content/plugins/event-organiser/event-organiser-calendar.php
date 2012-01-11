<?php
/**
 * Actions for the content
 *
 * @since 1.0.0
 */

function eventorganiser_cal_action(){	
	global $wpdb, $eventorganiser_events_table;

	//Double check the page
	if(isset($_REQUEST['page'])&& $_REQUEST['page']=='calendar' && isset($_REQUEST['post_type'])&& $_REQUEST['post_type']=='event'):

		//Check action
		if(isset($_REQUEST['action'])&&($_REQUEST['action']=='Save Draft'||$_REQUEST['action']=='Publish Event'||$_REQUEST['action']=='Submit for Review')){

			//Check nonce
			check_admin_referer('eventorganiser_calendar_save');

			//authentication checks
			if (!current_user_can('edit_events')) 
				wp_die( __('You do not have sufficient permissions to create events') );

			$input = $_REQUEST['eo_event']; //Retrieve input from posted data
			
			//Set the status of the new event
			if($_REQUEST['action']=='Publish Event')
				$status='publish';
			elseif($_REQUEST['action']=='S	ave Draft')
				$status='draft';
			else
				$status='pending';
			
			if ($status !='pending' && !current_user_can('publish_events')) 
				wp_die( __('You do not have sufficient permissions to publish events') );
	
			//Set post and event details
				$input['occurrence']='once';
				$input['YmdFormated']= true;
		
			$post_input = array(
				'post_title' =>$input['event_title'],
				'post_status' => $status,
				'post_content'=>$input['event_content'],
				'post_type' => 'event',
			);

			//Insert event
			$post_id = EO_Event::insertNewEvent($post_input,$input);

			if($post_id){
				//If event was successfully inserted, redirect and display appropriate message
				$redirect = get_edit_post_link($post_id,'');

				if($status=='publish')
					$redirect=add_query_arg('message',6, $redirect);
				else
					$redirect=add_query_arg('message',7, $redirect);
				
				//Redirect to event admin page & exit
				wp_redirect($redirect);
				exit; 
			}

		}//check action
	endif; //Check page
	}

/**
 * Display content for calendar page
 *
 * @since 1.0.0
 */
function eventorganiser_calendar_page() {
	
	//Get the time 'now' according to blog's timezone
	 $now = new DateTIme(null,EO_Event::get_timezone());
	
	$AllVenues = new EO_Venues;
	$AllVenues->query();
	?>

	<div class="wrap">  
		<div id='icon-edit' class='icon32'><br/>
		</div>
	<h2><?php _e('Events Calendar', 'eventorganiser'); ?></h2>

	<div id="calendar-view">
		<span id='loading' style='display:none'>loading...</span>
		<a href="" class="view-button" id="agendaDay">Day </a>
		<a href="" class="view-button" id="agendaWeek">Week </a>
		<a href="" class="view-button active" id="month">Month </a>
	</div>



	<div id='calendar'></div>
	<span>Current date/time: <?php echo $now->format('Y-m-d G:i:s \G\M\TP');?></span>
	<div id='events-meta' class="thickbox"></div>
	<?php if(current_user_can('publish_events')||current_user_can('edit_events')):?>
	<div id='eo_event_create_cal' style="display:none;" class="thickbox">
		<form name="eventorganiser_calendar" method="post" class="eo_cal">
			<table>
			<tr>
				<th>When: </th>
				<td id="date"></td>
			</tr>
			<tr>
				<th>Event Title: </th>
				<td><input name="eo_event[event_title]" size="30" placeholder="Event TItle"></input></td>
			</tr>
			<tr>
				<th>Where: </th>
				<td><!-- If javascript is disabed, a simple drop down menu box is displayed to choose venue.
			Otherwise, the user is able to search the venues by typing in the input box.-->		
				<select size="30" id="venue_select" name="eo_event[venue_id]">
				<option>Select a venue </option>
				<?php foreach ($AllVenues->results as $thevenue):?>
					<option value="<?php echo intval($thevenue['venue_id']);?>"><?php echo $thevenue['venue_name']; ?></option>
				<?php endforeach;?>
				</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><textarea cols="30" rows="4" name="eo_event[event_content]"></textarea></td>
			</tr>
			</table>
			<input type="hidden" name="eo_event[StartDate]">
			<input type="hidden" name="eo_event[EndDate]">
			<input type="hidden" name="eo_event[StartTime]">
			<input type="hidden" name="eo_event[FinishTime]">
			<input type="hidden" name="eo_event[allday]">
	  		<?php wp_nonce_field('eventorganiser_calendar_save'); ?>
			<?php if(current_user_can('publish_events')):?>
				<p class="submit">	
					<input type="reset" class="button" id="reset" value="Cancel">
					<input type="submit" class="button button-highlighted" tabindex="4" value="Save Draft" id="event-draft" name="action">
				<span class="eo_alignright">
					<input type="submit" accesskey="p" tabindex="5" value="Publish Event" class="button-primary" id="publish" name="action">
				</span>
				<br class="clear">
				</p>
			<?php elseif(current_user_can('edit_events')):?>
				<p class="submit">	
					<input type="reset" class="button" id="reset" value="Cancel">
				<span class="eo_alignright">
					<input type="submit" accesskey="p" tabindex="5" value="Submit for Review" class="button-primary" id="submit-for-review" name="action">
				</span>
				<br class="clear">
				</p>
			<?php endif; ?>
		</form>
	</div>
	<?php endif; ?>
</div><!-- .wrap -->
<?php
}?>
