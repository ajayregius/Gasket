<?php
/*
 * Default Events List Template
 * This page displays a list of events, called during the em_content() if this is an events list page.
 * You can override the default display settings pages by copying this file to yourthemefolder/plugins/events-manager/templates/ and modifying it however you need.
 * You can display events however you wish, there are a few variables made available to you:
 * 
 * $args - the args passed onto EM_Events::output()
 * 
 */
 //setcookie("Category", "1", time()+604800);
// echo"<pre>";print_r($_COOKIE);//exit;
 //$_REQUEST['category'][0]=11;
if( get_option('dbem_events_page_search') && !defined('DOING_AJAX') ){
	em_locate_template('templates/events-search.php',true);
}

//TODO fine tune ajax searches - we have some pagination issues
if( get_option('dbem_events_page_ajax', false) ) echo "<div class='em-events-search-ajax'>";
//echo "<pre>";print_r($args) ;//exit;
//$args['category']='11';

if(isset($_REQUEST['action']))
{
//echo "done ";exit;
	if($_REQUEST['action'] == 'search_events')
	{
	
	
	//echo "<pre>";print_r($args) ;exit;
//	var_dump($_REQUEST['category']);echo $_REQUEST['category'];exit;
	
	
	}

}
else
{
//echo "<pre>";print_r($args) ;//exit;
//echo "not done ";//exit;

   if($_GET['search_from']!='url') 
   {
  
   
			if(isset($_COOKIE['em_search']) && $_COOKIE['em_search']!='')
			{
			$args['search']=$_COOKIE['em_search'];
			}
			if(isset($_COOKIE['state']) && $_COOKIE['state']!='')
			{
			$args['state']=$_COOKIE['state'];
			}
			if(isset($_COOKIE['CatAll']) && $_COOKIE['CatAll']=='ALL')
			{
			$args['CatAll']=$_COOKIE['CatAll'];
			}
			else
			{
				if(isset($_COOKIE['Category']) && $_COOKIE['Category']!='')
				{
				$args['category']=$_COOKIE['Category'];
				}
			}
			if(isset($_COOKIE['scope_0']) && $_COOKIE['scope_0']!='' && isset($_COOKIE['scope_1']) && $_COOKIE['scope_1']!='')
			{
			$args['scope']=$_COOKIE['scope_0'].','.$_COOKIE['scope_1'];
			}
			if(isset($_COOKIE['state']) )
				{
				$args['state']=$_COOKIE['state'];
				}
				if(isset($_COOKIE['state']) && $_COOKIE['state']=='BLANK')
				{
				$args['state']='';
				}
				
		}
		else
		{
		   $_REQUEST['action'] = 'search_events';
		  
		  if(isset($_GET['state']) && $_GET['state']!='')
		  {
		  $args['state']=$_GET['state'];
		   $_REQUEST['state']=$_GET['state'];
		  }
		  else
		  {
		  $args['state']='';
		  }
		   
		  if(isset($_GET['text_search']) && $_GET['text_search']!='')
		  {
		  $args['search']=$_GET['text_search'];
		  $_REQUEST['em_search']=$_GET['text_search'];
		  }
		  else
		  {
		  //$args['em_search']='';
		  } 
		   
		   
		   if((isset($_GET['scope_0']) && $_GET['scope_0']!='' )  ||(isset($_GET['scope_1']) && $_GET['scope_1']!=''))
		   {
		   $args['scope']='';
				   if(isset($_GET['scope_0']) && $_GET['scope_0']!='')
				  {
				  //$args['search']=$_GET['text_search'];
				  $args['scope']=$_GET['scope_0'].',';
				  $_REQUEST['scope'][0]=$_GET['scope_0'];
				  }
				  else
				  {
				   $args['scope']=',';
				  //$args['em_search']='';
				  } 
				   if(isset($_GET['scope_1']) && $_GET['scope_1']!='')
				  {
				  //$args['search']=$_GET['text_search'];
				  $args['scope']= $args['scope'].$_GET['scope_1'];
				  $_REQUEST['scope'][1]=$_GET['scope_1'];
				  }
				  else
				  {
				   $args['scope']= $args['scope'];
				  //$args['em_search']='';
				  } 
		 
		  }
		  
		  
		  if(isset($_GET['category']) && $_GET['category']!='')
		  {
		  $args['category']=$_GET['category'];
		  $_REQUEST['category']=$_GET['category'];
		  $_REQUEST['category']=explode(',',$_REQUEST['category']);
		  //var_dump($_REQUEST['category']);echo $_REQUEST['category'];exit;
		  }
		  else
		  {
		  //$args['em_search']='';
		  } 
		  
		   
		}		
//$args['search']="dipendu";
//$args['category']='11';

}
//echo "<pre>";print_r($_COOKIE) ;
//echo "<pre>";print_r($_REQUEST) ;
//echo "<pre>";print_r($args) ;

$events_count = EM_Events::count( apply_filters('em_content_events_args', $args) );
$args['limit'] = get_option('dbem_events_default_limit');
$args['page'] = (!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']) )? $_REQUEST['page'] : 1;
if( $events_count > 0 ){
	//If there's a search, let's change the pagination a little here
	if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'search_events'){
		$args['pagination'] = false;
		echo EM_Events::output( $args );
		//do some custom pagination (if needed/requested)
		if( !empty($args['limit']) && $events_count > $args['limit'] ){
			//Show the pagination links (unless there's less than $limit events)
			$search_args = EM_Events::get_post_search() + array('page'=>'%PAGE%','action'=>'search_events');
			$page_link_template = em_add_get_params($_SERVER['REQUEST_URI'], $search_args, false); //don't html encode, so em_paginate does its thing
			echo apply_filters('em_events_output_pagination', em_paginate( $page_link_template, $events_count, $args['limit'], $args['page']), $page_link_template, $events_count, $args['limit'], $args['page']);
		}
	}else{
		echo EM_Events::output( $args );
	}
}else{
	echo get_option ( 'dbem_no_events_message' );
}
if( get_option('dbem_events_page_ajax', false) ) echo "</div>";