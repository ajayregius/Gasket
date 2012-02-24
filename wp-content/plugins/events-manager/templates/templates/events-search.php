<?php 
/* 
 * By modifying this in your theme folder within plugins/events-manager/templates/events-search.php, you can change the way the search form will look.
 * To ensure compatability, it is recommended you maintain class, id and form name attributes, unless you now what you're doing. 
 * You also must keep the _wpnonce hidden field in this form too.
 */
// echo"<pre>";print_r($_COOKIE);//exit;
 //echo"<pre>";print_r($_REQUEST);exit;
 
/*setcookie("em_search", base64_encode($_REQUEST['em_search']),time()+604800);
setcookie("scope_0", base64_encode($_REQUEST['scope_0']),time()+604800);
setcookie("scope_1", base64_encode($_REQUEST['scope_1']),time()+604800);
setcookie("state", base64_encode($_REQUEST['state']),time()+604800);*/
	// echo"<pre>";print_r($_COOKIE);	
?>
<script type="text/javascript">

//$(document).ready(function() {
  // Handler for .ready() called.
  
  
  /*$("#reset").click(function() {
  alert('reset');//$("#target").click();
});*/
  
  function reset12(ele) {

//alert(ele);
//alert(mainform.em_search.value);
document.getElementById('em_search').value = '';
document.getElementById('em-date-start-loc').value = '';
document.getElementById('em-date-start').value = '';
document.getElementById('em-date-end-loc').value = '';
document.getElementById('em-date-end').value = '';


tags = ele.getElementsByTagName('select');
    for(i = 0; i < tags.length; i++) {
        if(tags[i].type == 'select-one') {
            tags[i].selectedIndex = 0;
        }
        else {
            for(j = 0; j < tags[i].options.length; j++) {
                tags[i].options[j].selected = false;
            }
        }
    }
	
	 tags = ele.getElementsByTagName('input');
    for(i = 0; i < tags.length; i++) {
        switch(tags[i].type) {
            case 'password':
            case 'text':
                tags[i].value = '';
                break;
            case 'checkbox':
            case 'radio':
                tags[i].checked = false;
                break;
        }
    }

return false;
}



</script>
<div class="em-events-search" style="float:left;">
	<?php 
	//add our own scripts for this page
	wp_enqueue_script('em-events-submission', plugins_url().'/events-manager/includes/js/submission-form.js', array('jquery'), 1);
	
	global $em_localized_js;
	$s_default = get_option('dbem_search_form_text_label');	
	$s = !empty($_REQUEST['search']) ? $_REQUEST['search']:$s_default;
	if( empty($_REQUEST['country']) && empty($_REQUEST['page']) ){
		$country = get_option('dbem_location_default_country');
	}elseif( !empty($_REQUEST['country']) ){
		$country = $_REQUEST['country'];
	}
	//convert scope to an array in event of pagination
	if(!empty($_REQUEST['scope']) && !is_array($_REQUEST['scope'])){ $_REQUEST['scope'] = explode(',',$_REQUEST['scope']); }
	//get the events page to display search results
	?>
	<form action="<?php echo EM_URI; ?>" method="post" class="em-events-search-form" name="mainform">
	
<fieldset id="example_section_1">

<div id="multicheck" style="width:630px;float:left;height:auto">
		<?php if( !empty($search_categories) || (get_option('dbem_search_form_categories') && empty($search_categories)) ): ?>	
		<!-- START multi select Category Search -->	
			<div id="allcheck"  style="margin-top:10px;"><input id="AllCatsChkBox" name="CatAll" type="checkbox" value="ALL" 
			<?php if (isset($_REQUEST['CatAll']))
			{
				if (isset($_REQUEST['CatAll']) &&  $_REQUEST['CatAll']=='ALL') 
				{
				echo 'checked="checked"';
				}
			} 
			else 
			{ 
			if (isset($_COOKIE['CatAll']) &&  $_COOKIE['CatAll']=='ALL' && $_REQUEST['action']!='search_events') 
			echo 'checked="checked"'; 
			} ?> >Click for All categories.</div>
			<div id="individualcheck" >	
			<?php //var_dump($_REQUEST['category']);echo $_REQUEST['category'];exit; 
			foreach(EM_Categories::get(array('orderby'=>'category_name')) as $EM_Category): ?>
			 <div id="checks" style="width:150px;float:left;">
			 <input name="category[]" type="checkbox"  class="em-events-search-category" value="<?php echo $EM_Category->id; ?>"  <?php 

if(isset($_REQUEST['action']) && $_REQUEST['action']=='search_events')
{
 if (isset($_REQUEST['CatAll']) &&  $_REQUEST['CatAll']=='ALL') 
 {
 echo 'checked="checked"';
 }
 else
 {
  if (isset($_REQUEST['category']) && in_array($EM_Category->id,$_REQUEST['category']))
  
   echo 'checked="checked"';
 }

}
else
{
 if (isset($_COOKIE['CatAll']) &&  $_COOKIE['CatAll']=='ALL') 
 {
 echo 'checked="checked"';
 }
 else
 {
 if (isset($_COOKIE['Category']) &&  in_array($EM_Category->id, $_SESSION['my_cookie_arr'])) echo 'checked="checked"';
 }
 
}


?>><?php echo $EM_Category->name; ?></div>
			<?php endforeach; ?>		
		<!-- END Multi Category Search -->
		<?php endif; ?>
		</div>
		</div>
		<div id="categories" style="margin-left:100px;">
		<?php if( !empty($search_countries) || (get_option('dbem_search_form_countries') && empty($search_countries)) ): ?>
		<!-- START Country Search -->
		<select name="country" class="em-events-search-country">
			<option value=''><?php echo get_option('dbem_search_form_countries_label'); ?></option>
			<?php 
			//get the counties from locations table
			global $wpdb;
			$countries = em_get_countries();
			$em_countries = $wpdb->get_results("SELECT DISTINCT location_country FROM ".EM_LOCATIONS_TABLE." WHERE location_country IS NOT NULL AND location_country != '' ORDER BY location_country ASC", ARRAY_N);
			foreach($em_countries as $em_country): 
			?>
			 <option value="<?php echo $em_country[0]; ?>" <?php echo (!empty($country) && $country == $em_country[0]) ? 'selected="selected"':''; ?>><?php echo $countries[$em_country[0]]; ?></option>
			<?php endforeach; ?>
		</select>
		<!-- END Country Search -->	
		<?php endif; ?>
		
		<?php if( !empty($search_regions) || (get_option('dbem_search_form_regions') && empty($search_regions)) ): ?>
		<!-- START Region Search -->
		<select name="region" class="em-events-search-region">
			<option value=''><?php echo get_option('dbem_search_form_regions_label'); ?></option>
			<?php 
			if( !empty($country) ){
				//get the counties from locations table
				global $wpdb;
				$em_states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT location_region FROM ".EM_LOCATIONS_TABLE." WHERE location_region IS NOT NULL AND location_region != '' AND location_country=%s ORDER BY location_region", $country), ARRAY_N);
				foreach($em_states as $state){
					?>
					 <option <?php echo (!empty($_REQUEST['region']) && $_REQUEST['region'] == $state[0]) ? 'selected="selected"':''; ?>><?php echo $state[0]; ?></option>
					<?php 
				}
			}
			?>
		</select>	
		<!-- END Region Search -->	
		<?php endif; ?>
		
		
		<?php if( !empty($search_towns) || (get_option('dbem_search_form_towns') && empty($search_towns)) ): ?>
		<!-- START City Search -->
		<select name="town" class="em-events-search-town">
			<option value=''><?php echo get_option('dbem_search_form_towns_label'); ?></option>
			<?php 
			if( !empty($country) ){
				//get the counties from locations table
				global $wpdb;
				$cond = !empty($_REQUEST['region']) ? $wpdb->prepare(" AND location_region=%s ", $_REQUEST['region']):'';
				$cond .= !empty($_REQUEST['state']) ? $wpdb->prepare(" AND location_state=%s ", $_REQUEST['state']):'';
				$em_towns = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT location_town FROM ".EM_LOCATIONS_TABLE." WHERE location_town IS NOT NULL AND location_town != '' AND location_country=%s $cond ORDER BY location_town", $country), ARRAY_N);
				foreach($em_towns as $town){
					?>
					 <option <?php echo (!empty($_REQUEST['town']) && $_REQUEST['town'] == $town[0]) ? 'selected="selected"':''; ?>><?php echo $town[0]; ?></option>
					<?php 
				}
			}
			?>
		</select>
		<!-- END City Search -->
		<?php endif; ?>
		</div>
		<div id="state" style="margin-top:10px;float:left;">
		<?php if( !empty($search_states) || (get_option('dbem_search_form_states') && empty($search_states)) ): ?>
		<!-- START State/County Search -->
		<!--Choose state-->
		<select name="state" class="em-events-search-state">
			<option value=''><?php echo get_option('dbem_search_form_states_label'); ?></option>
			<?php 
			if( !empty($country) ){
				//get the counties from locations table
				global $wpdb;
				$cond = !empty($_REQUEST['region']) ? $wpdb->prepare(" AND location_region=%s ", $_REQUEST['region']):'';
				$em_states = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT location_state FROM ".EM_LOCATIONS_TABLE." WHERE location_state IS NOT NULL AND location_state != '' AND location_country=%s $cond ORDER BY location_state", $country), ARRAY_N);
				foreach($em_states as $state){
					?>
					 <option <?php echo (!empty($_REQUEST['state']) && $_REQUEST['state'] == $state[0]) ? 'selected="selected"':((isset($_COOKIE['state']) && $_COOKIE['state']!='' && $_COOKIE['state']==$state[0] && $_REQUEST['action']!='search_events')? 'selected="selected"':''); ?>><?php echo $state[0]; ?></option>
					<?php 
				}
			}
			?>
		</select>
		<!-- END State/County Search -->
		<!--or distance from Postcode-->
		<?php endif; ?>
		</div>
		<?php if( !empty($search_dates) || (get_option('dbem_search_form_dates') && empty($search_dates)) ): ?>
		<br/>
		
		<div id="dates" style="float:left;margin-top:10px;margin-left:100px;">
		<!-- START Date Search -->
		<span class="em-events-search-dates">
			<?php _e('Dates from','dbem'); ?>:
			<input type="text" id="em-date-start-loc" size="6" value="<?php if( (!empty($_REQUEST['scope'][0])  || empty($_REQUEST['scope'][0])) && $_REQUEST['action']=='search_events'  ) {echo $_REQUEST['scope'][0];}else
{
if(isset($_COOKIE['scope_0']) && $_COOKIE['scope_0']!='' && $_REQUEST['action']!='search_events')  echo $_COOKIE['scope_0'];
}  ?>"/>
			<input type="hidden" id="em-date-start" name="scope[0]" value="<?php if( (!empty($_REQUEST['scope'][0])  || empty($_REQUEST['scope'][0])) && $_REQUEST['action']=='search_events'  ) {echo $_REQUEST['scope'][0];}else
{
if(isset($_COOKIE['scope_0']) && $_COOKIE['scope_0']!='' && $_REQUEST['action']!='search_events')  echo $_COOKIE['scope_0'];
}  ?>" />
			<?php _e('to','dbem'); ?>
			<input type="text" id="em-date-end-loc" size="6" value="<?php if( (!empty($_REQUEST['scope'][1])  || empty($_REQUEST['scope'][1])) && $_REQUEST['action']=='search_events'  ) {echo $_REQUEST['scope'][1];}else
{
if(isset($_COOKIE['scope_1']) && $_COOKIE['scope_1']!='' && $_REQUEST['action']!='search_events')  echo $_COOKIE['scope_1'];
}  ?>"/>
			<input type="hidden" id="em-date-end" name="scope[1]" value="<?php if( (!empty($_REQUEST['scope'][1])  || empty($_REQUEST['scope'][1])) && $_REQUEST['action']=='search_events'  ) {echo $_REQUEST['scope'][1];}else
{
if(isset($_COOKIE['scope_1']) && $_COOKIE['scope_1']!='' && $_REQUEST['action']!='search_events')  echo $_COOKIE['scope_1'];
}  ?>" />
		</span>
		<!-- END Date Search -->
		<?php endif; ?>
		</div>
		<div id="gen_search" style="float:left;width:390px;margin-top:10px;">
		<?php do_action('em_template_events_search_form_header'); ?>
		General search word
		<?php if( !empty($search_text) || (get_option('dbem_search_form_text') && empty($search_text)) ): ?>
		<!-- START General Search -->
		<?php /* This general search will find matches within event_name, event_notes, and the location_name, address, town, state and country. */ ?>
		<input type="text" name="em_search" class="em-events-search-text" value="<?php echo (isset($_REQUEST['em_search']) && $_REQUEST['em_search']!='')? $_REQUEST['em_search']:((isset($_COOKIE['em_search']) && $_COOKIE['em_search']!='' && $_REQUEST['action']!='search_events')? $_COOKIE['em_search']:$s); ?>" onfocus="if(this.value=='<?php echo $s_default; ?>')this.value=''" onblur="if(this.value=='')this.value='<?php echo $s_default; ?>'"  id="em_search"/>
		<!-- END General Search -->
		<?php endif; ?>		
		optional
		</div>
		<div id="sub" style="float:left;margin-top:10px;margin-left:10px;">
		<?php do_action('em_template_events_search_form_ddm'); //depreciated, don't hook, use the one below ?>
		<?php do_action('em_template_events_search_form_footer'); ?>
		
		<input type="hidden" name="action" value="search_events" />
		<input type="submit" value="<?php echo $s_default; ?>" class="em-events-search-submit" />
		<input type="reset" value="<?php echo 'Reset '; ?>" class="em-events-search-submit" onclick="return reset12(example_section_1);" id="reset123"/>	
		</div>	
		</fieldset>
	</form>	
</div>

<div class="share"> <a href="#">Share it</a><!--<textarea  name="link" class="em-events-search-text" value="<?php //echo "here"; ?>"  id="link"/>-->  <textarea class=""  rows="2"  style="resize: none;"  ><?php
$link='http://'.$_SERVER['HTTP_HOST'].'/Gasket/?page_id=19'.'&search_from=url';
if(isset($_GET['text_search'])&& $_GET['text_search']!='' )
{
$link.='&text_search='.$_GET['text_search'];
}
else if( isset($_REQUEST['em_search'])&& $_REQUEST['em_search']!='')
{
$link.='&text_search='.$_REQUEST['em_search'];
}
else if( isset($_COOKIE['em_search'])&& $_COOKIE['em_search']!='' && $_REQUEST['action'] != 'search_events')
{
$link.='&text_search='.$_COOKIE['em_search'];
}

if(isset($_GET['state'])&& $_GET['state']!='' )
{
		
		$link.='&state='.$_GET['state'];
		
}
else if( isset($_REQUEST['state'])&& $_REQUEST['state']!='')
{
$link.='&state='.$_REQUEST['state'];
}
else if( isset($_COOKIE['state'])&& $_COOKIE['state']!='' && $_REQUEST['action'] != 'search_events')
{

	if($_COOKIE['state']=='BLANK' )
	{
	
	}
	else
	{
	$link.='&state='.$_COOKIE['state'];
	}
}

if(isset($_GET['scope_0'])&& $_GET['scope_0']!='' )
{
$link.='&scope_0='.$_REQUEST['scope'][0];
}
else if( isset($_REQUEST['scope'][0])&& $_REQUEST['scope'][0]!='')
{
$link.='&scope_0='.$_REQUEST['scope'][0];
}
else if( isset($_COOKIE['scope_0'])&& $_COOKIE['scope_0']!='' && $_REQUEST['action'] != 'search_events')
{
$link.='&scope_0='.$_COOKIE['scope_0'];
}

if(isset($_GET['scope_1'])&& $_GET['scope_1']!='' )
{
$link.='&scope_1='.$_GET['scope_1'];
}
else if( isset($_REQUEST['scope'][1])&& $_REQUEST['scope'][1]!='')
{
$link.='&scope_1='.$_REQUEST['scope'][1];
}
else if( isset($_COOKIE['scope_1'])&& $_COOKIE['scope_1']!=''&& $_REQUEST['action'] != 'search_events' )
{
$link.='&scope_1='.$_COOKIE['scope_1'];
}

if(isset($_GET['category'])&& $_GET['category']!='' )
{
$link.='&category='.$_GET['category'];
}
else if( isset($_REQUEST['category'])&& $_REQUEST['category']!='' )
{
$l='';
if(isset($_REQUEST['category']))
{
foreach($_REQUEST['category'] as $key=>$value)
{
$l=$l.$value.',';
}
$l = substr($l, 0, -1); 
$link.='&category='.$l;
}


}
else if( isset($_SESSION['my_cookie_arr'])&& $_SESSION['my_cookie_arr']!=''&& $_REQUEST['action'] != 'search_events' && isset($_COOKIE['Category']) )
{
$l='';
foreach($_SESSION['my_cookie_arr'] as $key=>$value)
{
$l=$l.$value.',';
}
$l = substr($l, 0, -1); 
$link.='&category='.$l;
}



 echo $link; ?></textarea></div>