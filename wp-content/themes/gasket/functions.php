<?php

add_filter( 'em_events_build_sql_conditions', 'my_em_multi_category_search',1,2);
function my_em_multi_category_search($conditions, $args){
	//print_r($conditions);
	/**
	if( !empty($args['category']) && $args['category']=='today-tomorrow' ){
		$start_date = date('Y-m-d',current_time('timestamp'));
		$end_date = date('Y-m-d',strtotime("+1 day", current_time('timestamp')));
		$conditions['scope'] = " (event_start_date BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE)) OR (event_end_date BETWEEN CAST('$end_date' AS DATE) AND CAST('$start_date' AS DATE))";
	}
	 */
	return $conditions;
}

	
function AllCatsOn(){
	alert("hi");
	//$QryForm.category.checked == true;
	//document.getElementById('categoryChkBox').checked=false;
}	

?>