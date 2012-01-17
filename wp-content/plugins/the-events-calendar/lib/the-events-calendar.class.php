<?php
/**
* Central Tribe Events Calendar class.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists( 'TribeEvents' ) ) {

	class TribeEvents {
		const EVENTSERROROPT = '_tribe_events_errors';
		const OPTIONNAME = 'tribe_events_calendar_options';
		const TAXONOMY = 'tribe_events_cat';
		const POSTTYPE = 'tribe_events';
		const VENUE_POST_TYPE = 'tribe_venue';
		const ORGANIZER_POST_TYPE = 'tribe_organizer';
		const PLUGIN_DOMAIN = 'tribe-events-calendar';
		const VERSION = '2.0.3';
		const FEED_URL = 'http://tri.be/category/products/feed/';

		protected $postTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug' => 'event', 'with_front' => false),
			'menu_position' => 6,
			'supports' => array('title','editor','excerpt','author','thumbnail'),
			//'capability_type' => array('tribe_event', 'tribe_events'),
			//'map_meta_cap' => TRUE,
		);
		protected $postVenueTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug'=>'venue', 'with_front' => false),
			'show_ui' => true,
			'show_in_menu' => 0,
			'supports' => array('title', 'editor'),
			//'capability_type' => array('tribe_venue', 'tribe_venues'),
			//'map_meta_cap' => TRUE,
			'exclude_from_search' => true
		);
		protected $postOrganizerTypeArgs = array(
			'public' => true,
			'rewrite' => false,
			'show_ui' => true,
			'show_in_menu' => 0,
			'menu_position' => 6,
			'supports' => array(''),
			//'capability_type' => array('tribe_organizer', 'tribe_organizers'),
			//'map_meta_cap' => TRUE,
			'exclude_from_search' => true
		);
		protected $taxonomyLabels;

		public static $tribeUrl = 'http://tri.be/';
		public static $supportPath = 'support/';

		protected static $instance;
		protected $rewriteSlug = 'events';
		protected $rewriteSlugSingular = 'event';
		protected $taxRewriteSlug = 'event/category';
		protected $monthSlug = 'month';
		protected $pastSlug = 'past';
		protected $upcomingSlug = 'upcoming';
		protected $postExceptionThrown = false;
		protected $optionsExceptionThrown = false;
		protected static $options;
		public $displaying;
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginName;
		public $date;
		protected $tabIndexStart = 2000;

		public $metaTags = array(
			'_EventAllDay',
			'_EventStartDate',
			'_EventEndDate',
			'_EventVenueID',
			'_EventShowMapLink',
			'_EventShowMap',
			'_EventCost',
			'_EventOrganizerID',
			'_EventPhone',
			'_EventHideFromUpcoming',
			self::EVENTSERROROPT
		);

		public $venueTags = array(
			'_VenueVenue',
			'_VenueCountry',
			'_VenueAddress',
			'_VenueCity',
			'_VenueStateProvince',
			'_VenueState',
			'_VenueProvince',
			'_VenueZip',
			'_VenuePhone'
		);

		public $organizerTags = array(
			'_OrganizerOrganizer',
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone'
		);

		public $states = array();
		public $currentPostTimestamp;
		public $daysOfWeekShort;
		public $daysOfWeek;
		public $daysOfWeekMin;
		public $monthsFull;
		public $monthsShort;

		/* Static Singleton Factory Method */
		public static function instance() {
			if (!isset(self::$instance)) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}		

		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		protected function __construct( ) {
			$this->pluginPath = trailingslashit( dirname( dirname(__FILE__) ) );
			$this->pluginDir = trailingslashit( basename( $this->pluginPath ) );
			$this->pluginUrl = plugins_url().'/'.$this->pluginDir;
			if (self::supportedVersion('wordpress') && self::supportedVersion('php')) {
				register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );
				$this->addFilters();
				$this->addActions();
				$this->loadLibraries();
			} else {
				// Either PHP or WordPress version is inadequate so we simply return an error.
				add_action('init', array($this,'loadTextDomain'));
				add_action('admin_head', array($this,'notSupportedError'));
			}
		}

		/**
		 *Load all the required library files.
		 **/
		protected function loadLibraries() {
			require_once( 'tribe-event-exception.class.php' );
			// Load Template Tags
			require_once( $this->pluginPath.'public/template-tags/general.php' );
			require_once( $this->pluginPath.'public/template-tags/calendar.php' );
			require_once( $this->pluginPath.'public/template-tags/loop.php' );
			require_once( $this->pluginPath.'public/template-tags/google-map.php' );
			require_once( $this->pluginPath.'public/template-tags/organizer.php' );
			require_once( $this->pluginPath.'public/template-tags/venue.php' );
			require_once( $this->pluginPath.'public/template-tags/date.php' );
			require_once( $this->pluginPath.'public/template-tags/link.php' );
			// Load Advanced Functions
			require_once( $this->pluginPath.'public/advanced-functions/event.php' );
			require_once( $this->pluginPath.'public/advanced-functions/venue.php' );
			require_once( $this->pluginPath.'public/advanced-functions/organizer.php' );
			// Load Deprecated Template Tags
			require_once( 'template-tags-deprecated.php' );
			require_once( 'widget-list.class.php' );
			require_once( 'tribe-admin-events-list.class.php' );
			require_once( 'tribe-date-utils.class.php' );
			require_once( 'tribe-templates.class.php' );
			require_once( 'tribe-event-api.class.php' );
			require_once( 'tribe-event-query.class.php' );
			require_once( 'tribe-the-events-calendar-import.class.php' );
			require_once( 'tribe-view-helpers.class.php' );
			require_once( 'tribe-debug-bar.class.php' );
		}

		protected function addFilters() {
			add_filter( 'post_class', array( $this, 'post_class') );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'query_vars',		array( $this, 'eventQueryVars' ) );
			add_filter( 'admin_body_class', array($this, 'admin_body_class') );
			//add_filter( 'the_content', array($this, 'emptyEventContent' ), 1 );
			add_filter( 'wp_title', array($this, 'maybeAddEventTitle' ), 10, 2 );
			add_filter( 'bloginfo_rss',	array($this, 'add_space_to_rss' ) );
			add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
			add_filter( 'post_updated_messages', array($this, 'updatePostMessage') );
	
			/* Add nav menu item - thanks to http://wordpress.org/extend/plugins/cpt-archives-in-nav-menus/ */
			add_filter( 'nav_menu_items_' . TribeEvents::POSTTYPE, array( $this, 'add_events_checkbox_to_menu' ), null, 3 );
			add_filter( 'wp_nav_menu_objects', array( $this, 'add_current_menu_item_class_to_events'), null, 2);

			// a fix for Twenty Eleven specifically
			if (function_exists('twentyeleven_body_classes')) {
				remove_filter( 'body_class', 'twentyeleven_body_classes' );
				add_filter( 'body_class', array( $this, 'twentyeleven_body_classes' ) );
			}

			add_filter( 'generate_rewrite_rules', array( $this, 'filterRewriteRules' ) );
		}

		protected function addActions() {
			add_action( 'init', array( $this, 'init'), 10 );
			add_action( 'template_redirect', array( $this, 'loadStyle' ) );
			add_action( 'admin_menu', array( $this, 'addOptionsPage' ) );
			add_action( 'admin_init', array( $this, 'saveSettings' ) );
			add_action( 'admin_menu', array( $this, 'addEventBox' ) );
			add_action( 'save_post', array( $this, 'addEventMeta' ), 15, 2 );
			add_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			add_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );
			add_action( 'pre_get_posts', array( $this, 'setDate' ));
			add_action( 'pre_get_posts', array( $this, 'setDisplay' ));
			add_action( 'tribe_events_post_errors', array( 'TribeEventsPostException', 'displayMessage' ) );
			add_action( 'tribe_events_options_top', array( 'TribeEventsOptionsException', 'displayMessage') );
			add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScriptsAndStyles' ) );
			add_action( 'plugins_loaded', array( $this, 'accessibleMonthForm'), -10 );
			add_action( 'the_post', array( $this, 'setReccuringEventDates' ) );			
			add_action( "trash_" . TribeEvents::VENUE_POST_TYPE, array($this, 'cleanupPostVenues'));
			add_action( "trash_" . TribeEvents::ORGANIZER_POST_TYPE, array($this, 'cleanupPostOrganizers'));
			add_action( "wp_ajax_tribe_event_validation", array($this,'ajax_form_validate') );
			add_action( 'tribe_debug', array( $this, 'renderDebug' ), 10, 2 );
			// noindex grid view
			add_action('wp_head', array( $this, 'noindex_months' ) );
			add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
			// organizer and venue
			add_action( 'tribe_venue_table_top', array($this, 'displayEventVenueInput') );
			add_action( 'tribe_organizer_table_top', array($this, 'displayEventOrganizerInput') );
			if( !defined('TRIBE_HIDE_UPSELL') || !TRIBE_HIDE_UPSELL ) {
				add_action( 'wp_dashboard_setup', array( $this, 'dashboardWidget' ) );
				add_action( 'tribe_events_cost_table', array($this, 'maybeShowMetaUpsell'));
				add_action( 'tribe_events_options_top', array($this, 'maybeShowSettingsUpsell'));
			}
		}

		/**
		 * Add code to tell search engines not to index the grid view of the 
		 * calendar.  Users were seeing 100s of months being indexed.
		 */
		function noindex_months() {
			if (get_query_var('eventDisplay') == 'month') {
				echo " <meta name=\"robots\" content=\"noindex, follow\"/>\n";
			} 
		}

		/**
		 * Run on applied action init
		 */
		public function init() {
			$this->loadTextDomain();
			$this->pluginName = __( 'The Events Calendar', 'tribe-events-calendar' );
			$this->rewriteSlug = $this->getOption('eventsSlug', 'events');
			$this->rewriteSlugSingular = $this->getOption('singleEventSlug', 'event');
			$this->taxRewriteSlug = $this->rewriteSlug . '/' . __( 'category', 'tribe-events-calendar' );
			$this->monthSlug = __('month', 'tribe-events-calendar');
			$this->upcomingSlug = __('upcoming', 'tribe-events-calendar');
			$this->pastSlug = __('past', 'tribe-events-calendar');
			$this->postTypeArgs['rewrite']['slug'] = $this->rewriteSlugSingular;
			$this->postVenueTypeArgs['rewrite']['slug'] = __( 'venue', 'tribe-events-calendar' );
			$this->currentDay = '';
			$this->errors = '';
			TribeEventsQuery::init();
			$this->registerPostType();

			//If the custom post type's rewrite rules have not been generated yet, flush them. (This can happen on reactivations.)
			if(is_array(get_option('rewrite_rules')) && !array_key_exists($this->rewriteSlugSingular.'/[^/]+/([^/]+)/?$',get_option('rewrite_rules'))) {
				$this->flushRewriteRules();
			}
			self::debug(sprintf(__('Initializing Tribe Events on %s','tribe-events-calendar'),date('M, jS \a\t h:m:s a')));
			$this->maybeMigrateDatabase();
			$this->maybeSetTECVersion();
		}

		public function maybeMigrateDatabase( ) {
			// future migrations should actually check the db_version
			if( !get_option('tribe_events_db_version') ) {
				global $wpdb; 
				// rename option
				update_option(self::OPTIONNAME, get_option('sp_events_calendar_options'));
				delete_option('sp_events_calendar_options');

				// update post type names
				$wpdb->update($wpdb->posts, array( 'post_type' => self::POSTTYPE ), array( 'post_type' => 'sp_events') );
				$wpdb->update($wpdb->posts, array( 'post_type' => self::VENUE_POST_TYPE ), array( 'post_type' => 'sp_venue') );
				$wpdb->update($wpdb->posts, array( 'post_type' => self::ORGANIZER_POST_TYPE ), array( 'post_type' => 'sp_organizer') );

				// update taxonomy names
				$wpdb->update($wpdb->term_taxonomy, array( 'taxonomy' => self::TAXONOMY ), array( 'taxonomy' => 'sp_events_cat') );
				update_option('tribe_events_db_version', '2.0.3');
			}
		}

		public function maybeSetTECVersion() {
			if(!$this->getOption('latest_ecp_version') ) {
				if ( version_compare($this->getOption('latest_ecp_version'), self::VERSION, '<') )
					$this->setOption('latest_ecp_version', self::VERSION);
			}
		}

		/**
		 * Test PHP and WordPress versions for compatibility
		 *
		 * @param string $system - system to be tested such as 'php' or 'wordpress'
		 * @return boolean - is the existing version of the system supported?
		 */
		public function supportedVersion($system) {
			if ($supported = wp_cache_get($system,'tribe_version_test')) {
				return $supported;
			} else {
				switch (strtolower($system)) {
					case 'wordpress' :
						$supported = version_compare(get_bloginfo('version'), '3.0', '>=');
						break;
					case 'php' :
						$supported = version_compare( phpversion(), '5.2', '>=');
						break;
				}
				$supported = apply_filters('tribe_events_supported_version',$supported,$system);
				wp_cache_set($system,$supported,'tribe_version_test');
				return $supported;
			}
		}

		public function notSupportedError() {
			if ( !self::supportedVersion('wordpress') ) {
				echo '<div class="error"><p>'.sprintf(__('Sorry, The Events Calendar requires WordPress %s or higher. Please upgrade your WordPress install.', 'tribe-events-calendar'),'3.0').'</p></div>';
			}
			if ( !self::supportedVersion('php') ) {
				echo '<div class="error"><p>'.sprintf(__('Sorry, The Events Calendar requires PHP %s or higher. Talk to your Web host about moving you to a newer version of PHP.', 'tribe-events-calendar'),'5.2').'</p></div>';
			}
		}

		public function add_current_menu_item_class_to_events( $items, $args ) {
			foreach($items as $item) {
				if($item->url == $this->getLink() ) {
					if ( (is_singular() && get_post_type() == TribeEvents::POSTTYPE)
						|| is_singular( TribeEvents::VENUE_POST_TYPE ) 
						|| is_tax(TribeEvents::TAXONOMY) 
						|| ( ( tribe_is_upcoming() 
							|| tribe_is_past() 
							|| tribe_is_month() ) 
						&& isset($wp_query->query_vars['eventDisplay']) ) ) {
						$item->classes[] = 'current-menu-item current_page_item';
					}
					break;
				}
			}

			return $items;
		}

		public function add_events_checkbox_to_menu( $posts, $args, $post_type ) {
			global $_nav_menu_placeholder, $wp_rewrite;
			$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
			$archive_slug = $this->getLink();

			array_unshift( $posts, (object) array(
				'ID' => 0,
				'object_id' => $_nav_menu_placeholder,
				'post_content' => '',
				'post_excerpt' => '',
				'post_title' => $post_type['args']->labels->all_items,
				'post_type' => 'nav_menu_item',
				'type' => 'custom',
				'url' => $archive_slug,
			) );

			return $posts;			
		}

		/**
		 * Tribe debug function. usage: TribeEvents::debug('Message',$data,'log');
		 *
		 * @param string $title - message to display in log
		 * @param string $data - optional data to display
		 * @param string $format - optional format (log|warning|error|notice)
		 * @return void
		 * @author Peter Chester
		 */
		public static function debug($title,$data=false,$format='log') {
			do_action('tribe_debug',$title,$data,$format);
		}
		
		/**
		 * Render the debug logging to the php error log. This can be over-ridden by removing the filter.
		 *
		 * @param string $title - message to display in log
		 * @param string $data - optional data to display
		 * @param string $format - optional format (log|warning|error|notice)
		 * @return void
		 * @author Peter Chester
		 */
		public function renderDebug($title,$data=false,$format='log') {
			$format = ucfirst($format);
			if ($this->getOption('debugEvents')) {
				error_log($this->pluginName." $format: $title");
				if ($data && $data!='') {
					error_log($this->pluginName." $format: ".print_r($data,true));
				}
			}
		}

		public function get_event_taxonomy() {
			return self::TAXONOMY;
		}

		public function add_space_to_rss($title) {
			global $wp_query;
			if(get_query_var('eventDisplay') == 'upcoming' && get_query_var('post_type') == TribeEvents::POSTTYPE) {
				return $title . ' ';
			}

			return $title;
		}

		public function addDateToRecurringEvents($permalink, $post) {
			if( function_exists('tribe_is_recurring_event') && $post->post_type == self::POSTTYPE && tribe_is_recurring_event($post->ID) ) {
				if( is_admin() && (!isset($post->EventStartDate) || !$post->EventStartDate) ) {
					if( isset($_REQUEST['eventDate'] ) ) {
						$post->EventStartDate = $_REQUEST['eventDate'];
					} else	{
						$post->EventStartDate = TribeEvents::getRealStartDate( $post->ID );
					}
				}
		
				if( '' == get_option('permalink_structure') ) {
					return add_query_arg('eventDate', TribeDateUtils::dateOnly( $post->EventStartDate ), $permalink ); 					
				} else {
					return trailingslashit($permalink) . TribeDateUtils::dateOnly( isset($post->EventStartDate) ? $post->EventStartDate : null );
				}
			}
	
			return $permalink;
		}

		// sorts the meta to ensure we are getting the real start date
		public static function getRealStartDate( $postId ) {
			$start_dates = get_post_meta( $postId, '_EventStartDate' );

			if( is_array( $start_dates ) && sizeof( $start_dates ) > 0 ) {
				sort($start_dates);
				return $start_dates[0];
			}

			return null;
		}		

		public function maybeAddEventTitle($title, $sep = null){
			if(get_query_var('eventDisplay') == 'upcoming'){
				$new_title = __("Upcoming Events", 'tribe-events-calendar'). ' '.$sep . ' ' . $title;
			}elseif(get_query_var('eventDisplay') == 'past'){
					$new_title = __("Past Events", 'tribe-events-calendar') . ' '. $sep . ' ' . $title;

			}elseif(get_query_var('eventDisplay') == 'month'){
				if(get_query_var('eventDate')){
					$new_title = sprintf(__("Events for %s", 'tribe-events-calendar'),date("F, Y",strtotime(get_query_var('eventDate')))) . ' '. $sep . ' ' . $title;
				}else{
					$new_title = sprintf(__("Events this month", 'tribe-events-calendar'),get_query_var('eventDate')) . ' '. $sep . ' ' . $title;
				}

			} elseif(get_query_var('eventDisplay') == 'day') {
				$new_title = sprintf(__("Events for %s", 'tribe-events-calendar'),date("F d, Y",strtotime(get_query_var('eventDate')))) . ' '. $sep . ' ' . $title;
         } elseif(get_query_var('post_type') == self::POSTTYPE && is_single() && $this->getOption('spEventsTemplate') != '' ) {
				global $post;
				$new_title = $post->post_title . ' '. $sep . ' ' . $title;
			} elseif(get_query_var('post_type') == self::VENUE_POST_TYPE && $this->getOption('spEventsTemplate') != '' ) {
				global $post;
				$new_title = sprintf(__("Events at %s", 'tribe-events-calendar'), $post->post_title) . ' '. $sep . ' ' . $title;
			} else {
				return $title;
			}

			return $new_title;

		}

		public function emptyEventContent( $content ) {
			global $post;
			if ( '' == $content && isset($post->post_type) && $post->post_type == self::POSTTYPE ) {
				$content = __('No description has been entered for this event.', 'tribe-events-calendar');
			}
			return $content;
		}

		public function accessibleMonthForm() {
			if ( isset($_GET['EventJumpToMonth']) && isset($_GET['EventJumpToYear'] )) {
				$_GET['eventDisplay'] = 'month';
				$_GET['eventDate'] = $_GET['EventJumpToYear'] . '-' . $_GET['EventJumpToMonth'];
			}
		}

		public function body_class( $c ) {
			if ( get_query_var('post_type') == self::POSTTYPE ) {
				if ( ! is_single() || tribe_is_showing_all() ) {
					$c[] = 'events-archive';
				}
				else {
					$c[] = 'events-single';
				}
			}
			return $c;
		}


		/**
		 * this funciton provides compatibility for twenty eleven body classes
		 * without this slight rewrite of the twentyeleven_body_classes, there is a persistent php notice
		 * on some event pages
		 */
		public function twentyeleven_body_classes( $c ) {
			if ( function_exists( 'is_multi_author' ) && ! is_multi_author() )
				$c[] = 'single-author';

			if ( is_singular() && get_post_type() != self::POSTTYPE && ! is_home() && ! is_page_template( 'showcase.php' ) && ! is_page_template( 'sidebar-page.php' ) )
			$c[] = 'singular';

			return $c;
		}

		public function post_class( $c ) {
			global $post;
			if ( is_object($post) && isset($post->post_type) && $post->post_type == self::POSTTYPE && $terms = get_the_terms( $post->ID , self::TAXONOMY ) ) {
				foreach ($terms as $term) {
					$c[] = 'cat_' . sanitize_html_class($term->slug, $term->term_taxonomy_id);
				}
			}
			return $c;
		}

		public function registerPostType() {
			$this->generatePostTypeLabels();
			register_post_type(self::POSTTYPE, $this->postTypeArgs);
			register_post_type(self::VENUE_POST_TYPE, $this->postVenueTypeArgs);
			register_post_type(self::ORGANIZER_POST_TYPE, $this->postOrganizerTypeArgs);

         $role = get_role( 'administrator' );
         $role->add_cap( 'edit_tribe_event' );
         $role->add_cap( 'read_tribe_event' );
         $role->add_cap( 'delete_tribe_event' );
         $role->add_cap( 'edit_tribe_events' );
         $role->add_cap( 'edit_others_tribe_events' );
         $role->add_cap( 'publish_tribe_events' );
         $role->add_cap( 'read_private_tribe_events' );

         $role->add_cap( 'edit_tribe_venue' );
         $role->add_cap( 'read_tribe_venue' );
         $role->add_cap( 'delete_tribe_venue' );
         $role->add_cap( 'edit_tribe_venues' );
         $role->add_cap( 'edit_others_tribe_venues' );
         $role->add_cap( 'publish_tribe_venues' );
         $role->add_cap( 'read_private_tribe_venues' );

         $role->add_cap( 'edit_tribe_organizer' );
         $role->add_cap( 'read_tribe_organizer' );
         $role->add_cap( 'delete_tribe_organizer' );
         $role->add_cap( 'edit_tribe_organizers' );
         $role->add_cap( 'edit_others_tribe_organizers' );
         $role->add_cap( 'publish_tribe_organizers' );
         $role->add_cap( 'read_private_tribe_organizers' );

			register_taxonomy( self::TAXONOMY, self::POSTTYPE, array(
				'hierarchical' => true,
				'update_count_callback' => '',
				'rewrite' => array('slug'=> $this->taxRewriteSlug),
				'public' => true,
				'show_ui' => true,
				'labels' => $this->taxonomyLabels
			));
	
			if( $this->getOption('showComments','no') == 'yes' ) {
				add_post_type_support( self::POSTTYPE, 'comments');
			}
	
		}

		public function getVenuePostTypeArgs() {
			return $this->postVenueTypeArgs;
		}

		public function getOrganizerPostTypeArgs() {
			return $this->postOrganizerTypeArgs;
		}

		protected function generatePostTypeLabels() {
			$this->postTypeArgs['labels'] = array(
				'name' => __('Events', 'tribe-events-calendar'),
				'singular_name' => __('Event', 'tribe-events-calendar'),
				'add_new' => __('Add New', 'tribe-events-calendar'),
				'add_new_item' => __('Add New Event', 'tribe-events-calendar'),
				'edit_item' => __('Edit Event', 'tribe-events-calendar'),
				'new_item' => __('New Event', 'tribe-events-calendar'),
				'view_item' => __('View Event', 'tribe-events-calendar'),
				'search_items' => __('Search Events', 'tribe-events-calendar'),
				'not_found' => __('No events found', 'tribe-events-calendar'),
				'not_found_in_trash' => __('No events found in Trash', 'tribe-events-calendar')
			);
	
			$this->postVenueTypeArgs['labels'] = array(
				'name' => __('Venues', 'tribe-events-calendar'),
				'singular_name' => __('Venue', 'tribe-events-calendar'),
				'add_new' => __('Add New', 'tribe-events-calendar'),
				'add_new_item' => __('Add New Venue', 'tribe-events-calendar'),
				'edit_item' => __('Edit Venue', 'tribe-events-calendar'),
				'new_item' => __('New Venue', 'tribe-events-calendar'),
				'view_item' => __('View Venue', 'tribe-events-calendar'),
				'search_items' => __('Search Venues', 'tribe-events-calendar'),
				'not_found' => __('No venue found', 'tribe-events-calendar'),
				'not_found_in_trash' => __('No venues found in Trash', 'tribe-events-calendar')
			);
	
			$this->postOrganizerTypeArgs['labels'] = array(
				'name' => __('Organizers', 'tribe-events-calendar'),
				'singular_name' => __('Organizer', 'tribe-events-calendar'),
				'add_new' => __('Add New', 'tribe-events-calendar'),
				'add_new_item' => __('Add New Organizer', 'tribe-events-calendar'),
				'edit_item' => __('Edit Organizer', 'tribe-events-calendar'),
				'new_item' => __('New Organizer', 'tribe-events-calendar'),
				'view_item' => __('View Venue', 'tribe-events-calendar'),
				'search_items' => __('Search Organizers', 'tribe-events-calendar'),
				'not_found' => __('No organizer found', 'tribe-events-calendar'),
				'not_found_in_trash' => __('No organizers found in Trash', 'tribe-events-calendar')
			);
	
			$this->taxonomyLabels = array(
				'name' =>	__( 'Event Categories', 'tribe-events-calendar' ),
				'singular_name' =>	__( 'Event Category', 'tribe-events-calendar' ),
				'search_items' =>	__( 'Search Event Categories', 'tribe-events-calendar' ),
				'all_items' => __( 'All Event Categories', 'tribe-events-calendar' ),
				'parent_item' =>	__( 'Parent Event Category', 'tribe-events-calendar' ),
				'parent_item_colon' =>	__( 'Parent Event Category:', 'tribe-events-calendar' ),
				'edit_item' =>	__( 'Edit Event Category', 'tribe-events-calendar' ),
				'update_item' =>	__( 'Update Event Category', 'tribe-events-calendar' ),
				'add_new_item' =>	__( 'Add New Event Category', 'tribe-events-calendar' ),
				'new_item_name' =>	__( 'New Event Category Name', 'tribe-events-calendar' )
			);
	
		}

		public function updatePostMessage( $messages ) {
			global $post, $post_ID;

			$messages[self::POSTTYPE] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Event updated. <a href="%s">View event</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom field updated.', 'tribe-events-calendar'),
				3 => __('Custom field deleted.', 'tribe-events-calendar'),
				4 => __('Event updated.', 'tribe-events-calendar'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s', 'tribe-events-calendar'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Event published. <a href="%s">View event</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Event saved.', 'tribe-events-calendar'),
				8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'tribe-events-calendar'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' , 'tribe-events-calendar'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			);

			$messages[self::VENUE_POST_TYPE] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Venue updated. <a href="%s">View venue</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom field updated.', 'tribe-events-calendar'),
				3 => __('Custom field deleted.', 'tribe-events-calendar'),
				4 => __('Venue updated.', 'tribe-events-calendar'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Venue restored to revision from %s', 'tribe-events-calendar'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Venue published. <a href="%s">View venue</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Venue saved.'),
				8 => sprintf( __('Venue submitted. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Venue scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview venue</a>', 'tribe-events-calendar'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' , 'tribe-events-calendar'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Venue draft updated. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				);

				$messages[self::ORGANIZER_POST_TYPE] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Organizer updated. <a href="%s">View organizer</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom field updated.', 'tribe-events-calendar'),
				3 => __('Custom field deleted.', 'tribe-events-calendar'),
				4 => __('Organizer updated.', 'tribe-events-calendar'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Organizer restored to revision from %s', 'tribe-events-calendar'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Organizer published. <a href="%s">View organizer</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Organizer saved.'),
				8 => sprintf( __('Organizer submitted. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Organizer scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview organizer</a>', 'tribe-events-calendar'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' , 'tribe-events-calendar'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Organizer draft updated. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			);			

			return $messages;
		}

		public function admin_body_class( $classes ) {
			global $current_screen;			
			if ( isset($current_screen->post_type) &&
					($current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_the-events-calendar.class')
			) {
				$classes .= ' events-cal ';
			}
			return $classes;
		}

		public function addAdminScriptsAndStyles() {
			// always load style. need for icon in nav.
			wp_enqueue_style( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.css' );		
			
			global $current_screen;
			if ( isset($current_screen->post_type) ) {
				if ( $current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_the-events-calendar.class' ) {
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin-ui.css' );		
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );					
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );					
					wp_enqueue_script( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );
					// calling our own localization because wp_localize_scripts doesn't support arrays or objects for values, which we need.
					add_action('admin_footer', array($this, 'printLocalizedAdmin') );
				}elseif( $current_screen->post_type == self::VENUE_POST_TYPE){
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );					
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );					
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin-ui.css' );					
					wp_enqueue_script( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
					wp_enqueue_style( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/hide-visibility.css' );
				}elseif( $current_screen->post_type == self::ORGANIZER_POST_TYPE){
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );					
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );					
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin.css' );					
					wp_enqueue_script( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
					wp_enqueue_style( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/hide-visibility.css' );
				}
			}
		}

		public function localizeAdmin() {	
			$bits = array(
				'dayNames' => $this->daysOfWeek,
				'dayNamesShort' => $this->daysOfWeekShort,
				'dayNamesMin' => $this->daysOfWeekMin,
				'monthNames' => array_values( $this->monthNames() ),
				'monthNamesShort' => array_values( $this->monthNames( true ) ),
				'nextText' => __( 'Next', 'tribe-events-calendar' ),
				'prevText' => __( 'Prev', 'tribe-events-calendar' ),
				'currentText' => __( 'Today', 'tribe-events-calendar' ),
				'closeText' => __( 'Done', 'tribe-events-calendar' )
			);
			return $bits;
		}

		public function printLocalizedAdmin() {
			$object_name = 'TEC';
			$vars = $this->localizeAdmin();
	
			$data = "var $object_name = {\n";
			$eol = '';
			foreach ( $vars as $var => $val ) {
		
				if ( gettype($val) == 'array' || gettype($val) == 'object' ) {
					$val = json_encode($val);
				}
				else {
					$val = '"' . esc_js( $val ) . '"';
				} 
		
				$data .= "$eol\t$var: $val";
				$eol = ",\n";
			}
			$data .= "\n};\n";
	
			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo $data;
			echo "/* ]]> */\n";
			echo "</script>\n";
	
		}

		public function addOptionsPage() {
			add_options_page($this->pluginName, $this->pluginName, 'administrator', 'tribe-events-calendar', array($this,'optionsPageView'));
		}

		public function optionsPageView() {
			include( $this->pluginPath . 'admin-views/events-options.php' );
			// every visit to ECP Settings = flush rules.
			$this->flushRewriteRules();
		}

		/**
		 * Process settings form submissions and save settings if appropriate
		 */
		public function saveSettings() {
	
			if ( isset($_POST['saveEventsCalendarOptions']) && check_admin_referer('saveEventsCalendarOptions') ) {
				$options = self::getOptions();
				$options['viewOption'] = $_POST['viewOption'];
				if(isset($_POST['defaultCountry']) && $_POST['defaultCountry']) {
					$countries = TribeEventsViewHelpers::constructCountries();
					$defaultCountryKey = array_search( $_POST['defaultCountry'], $countries );
					$options['defaultCountry'] = array( $defaultCountryKey, $_POST['defaultCountry'] );
				}

				if( $_POST['embedGoogleMapsHeight'] ) {
					$options['embedGoogleMapsHeight'] = $_POST['embedGoogleMapsHeight'];
					$options['embedGoogleMapsWidth'] = $_POST['embedGoogleMapsWidth'];
				}
		
				// single event cannot be same as plural. Or empty.
				if( isset($_POST['singleEventSlug']) && isset($_POST['eventsSlug']) ){
					if ( $_POST['singleEventSlug'] === $_POST['eventsSlug'] ) {
						$_POST['singleEventSlug'] = 'event';
					}
				}

				if( empty($_POST['singleEventSlug']) ){
					$_POST['singleEventSlug'] = 'event';
				}
		
				// Events slug can't be empty
				if ( empty( $_POST['eventsSlug'] ) ) {
					$_POST['eventsSlug'] = 'events';
				}
		
				$boolean_opts = array(
					'embedGoogleMaps',
					'showComments',
					'displayEventsOnHomepage',
					'debugEvents',
					'defaultValueReplace',
				);
				foreach ($boolean_opts as $opt) {					
					$options[$opt] = (isset($_POST[$opt])) ? true : false;
				}
				
				$opts = array( 
					'resetEventPostDate',
					'eventsSlug',
					'singleEventSlug',
					'spEventsAfterHTML',
					'spEventsBeforeHTML',
					'spEventsCountries',
					'eventsDefaultVenueID',
					'eventsDefaultOrganizerID',
					'eventsDefaultState',
					'eventsDefaultProvince',
					'eventsDefaultAddress',
					'eventsDefaultCity',
					'eventsDefaultZip',
					'eventsDefaultPhone',
					'multiDayCutoff',
					'spEventsTemplate'
				);
				foreach ($opts as $opt) {
					if(isset($_POST[$opt]))
						$options[$opt] = $_POST[$opt];
				}

				$options['spEventsCountries'] = (isset($options['spEventsCountries'])) ? stripslashes($options['spEventsCountries']) : null;
		
				// events slug happiness
				$slug = $options['eventsSlug'];
				$slug = sanitize_title_with_dashes($slug);
				$slug = str_replace('/',' ',$slug);
				$options['eventsSlug'] = $slug;
				$this->rewriteSlug = $slug;

				$this->setOptions($options);
			} // end if
		}

		/**
		 * Get all options for the Events Calendar
		 *
		 * @return array of options
		 */
		public static function getOptions() {
			if ( !isset( self::$options ) ) {
				$options = get_option( TribeEvents::OPTIONNAME, array() );
				self::$options = apply_filters( 'tribe_get_options', $options );
			}
			return self::$options;
		}

		/**
		 * Get value for a specific option
		 *
		 * @param string $optionName name of option
		 * @param string $default default value
		 * @return mixed results of option query
		 */
		public function getOption($optionName, $default = '') {
			if( !$optionName )
				return null;

			if( !isset( self::$options ) ) 
				self::getOptions();
		
			if ( isset( self::$options[$optionName] ) ) {
				$option = self::$options[$optionName];
			} else {
				$option = $default;
			}
	
			return apply_filters( 'tribe_get_single_option', $option, $default );	
		}

		/**
		 * Saves the options for the plugin
		 *
		 * @param array $options formatted the same as from getOptions()
		 * @return void
		 */
		public function setOptions($options, $apply_filters=true) {
			if (!is_array($options)) {
				return;
			}
			if ( $apply_filters == true ) {
				$options = apply_filters( 'tribe-events-save-options', $options );
			}
			if ( update_option( TribeEvents::OPTIONNAME, $options ) ) {
				self::$options = apply_filters( 'tribe_get_options', $options );
				if ( isset(self::$options['eventsSlug']) && self::$options['eventsSlug'] != '' ) {
					$this->flushRewriteRules();
				}
			} else {
				self::$options = self::getOptions();
			}
		}

		public function setOption($name, $value) {
			$newOption = array();
			$newOption[$name] = $value;
			$options = self::getOptions();
			$this->setOptions( wp_parse_args( $newOption, $options ) );
		}

		// clean up trashed venues
		public function cleanupPostVenues($postId) {
			$this->removeDeletedPostTypeAssociation('_EventVenueID', $postId);
		}

		// clean up trashed organizers
		public function cleanupPostOrganizers($postId) {
			$this->removeDeletedPostTypeAssociation('_EventOrganizerID', $postId);
		}		

		// do clean up for trashed venues or organizers
		protected function removeDeletedPostTypeAssociation($key, $postId) {
			$the_query = new WP_Query(array('meta_key'=>$key, 'meta_value'=>$postId, 'post_type'=> TribeEvents::POSTTYPE ));

			while ( $the_query->have_posts() ): $the_query->the_post();
				delete_post_meta(get_the_ID(), $key);
			endwhile;

			wp_reset_postdata();
		}

		public function truncate($text, $excerpt_length = 44) {

			$text = strip_shortcodes( $text );

			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);

			$words = explode(' ', $text, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = rtrim($text);
				$text .= '&hellip;';
				}

			return $text;
		}

		public function loadTextDomain() {
			load_plugin_textdomain( 'tribe-events-calendar', false, $this->pluginDir . 'lang/');
			$this->constructDaysOfWeek();
			$this->initMonthNames();
		}

		public function loadStyle() {
	
			$eventsURL = trailingslashit( $this->pluginUrl ) . 'resources/';
			wp_enqueue_script('sp-events-pjax', $eventsURL.'jquery.pjax.js', array('jquery') );			
			wp_enqueue_script('sp-events-calendar-script', $eventsURL.'events.js', array('jquery', 'sp-events-pjax') );
			// is there an events.css file in the theme?
			if ( $user_style = locate_template(array('events/events.css')) ) {
				$styleUrl = str_replace( get_theme_root(), get_theme_root_uri(), $user_style );
			}
			else {
				$styleUrl = $eventsURL.'events.css';
			}
			$styleUrl = apply_filters( 'tribe_events_stylesheet_url', $styleUrl );
	
			if ( $styleUrl )
				wp_enqueue_style('sp-events-calendar-style', $styleUrl);
		}


		public function setDate($query) {
			if ( $query->get('eventDisplay') == 'month' ) {
				$this->date = $query->get('eventDate') . "-01";
			} else if ( $query->get('eventDate') ) {
				$this->date = $query->get('eventDate');
			} else if ( $query->get('eventDisplay') == 'month' ) {
				$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
				$this->date = substr_replace( $date, '01', -2 );
			} else if (is_singular() && $query->get('eventDate') ) {
				$this->date = $query->get('eventDate');
			} else if (!is_singular()) { // don't set date for single event unless recurring
				$this->date = date(TribeDateUtils::DBDATETIMEFORMAT);
			}
		}

		public function setDisplay() {
			if (is_admin()) {
				$this->displaying = 'admin';
			} else {
				global $wp_query;
				$this->displaying = isset( $wp_query->query_vars['eventDisplay'] ) ? $wp_query->query_vars['eventDisplay'] : 'upcoming';
			}
		}

		public function setReccuringEventDates( $post ) {	
			if( function_exists('tribe_is_recurring_event') && 
				is_singular() &&
				get_post_type() == self::POSTTYPE &&
				tribe_is_recurring_event() && 
				!tribe_is_showing_all() && 
				!tribe_is_upcoming() && 
				!tribe_is_past() && 
				!tribe_is_month() && 
				!tribe_is_by_date() ) {

				$startTime = get_post_meta($post->ID, '_EventStartDate', true);
				$startTime = TribeDateUtils::timeOnly($startTime);
		
				$post->EventStartDate = TribeDateUtils::addTimeToDate($this->date, $startTime);
				$post->EventEndDate = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($post->EventStartDate) + get_post_meta($post->ID, '_EventDuration', true) );
			}
		}		

		/**
		 * Helper method to return an array of 1-12 for months
		 */
		public function months( ) {
			$months = array();
			foreach( range( 1, 12 ) as $month ) {
				$months[ $month ] = $month;
			}
			return $months;
		}

		protected function initMonthNames() {
			global $wp_locale;
			$this->monthsFull = array( 
				'January' => $wp_locale->get_month('01'), 
				'February' => $wp_locale->get_month('02'), 
				'March' => $wp_locale->get_month('03'), 
				'April' => $wp_locale->get_month('04'), 
				'May' => $wp_locale->get_month('05'), 
				'June' => $wp_locale->get_month('06'), 
				'July' => $wp_locale->get_month('07'), 
				'August' => $wp_locale->get_month('08'), 
				'September' => $wp_locale->get_month('09'), 
				'October' => $wp_locale->get_month('10'), 
				'November' => $wp_locale->get_month('11'), 
				'December' => $wp_locale->get_month('12') 
			);
			// yes, it's awkward. easier this way than changing logic elsewhere.
			$this->monthsShort = $months = array( 
				'Jan' => $wp_locale->get_month_abbrev( $wp_locale->get_month('01') ), 
				'Feb' => $wp_locale->get_month_abbrev( $wp_locale->get_month('02') ), 
				'Mar' => $wp_locale->get_month_abbrev( $wp_locale->get_month('03') ), 
				'Apr' => $wp_locale->get_month_abbrev( $wp_locale->get_month('04') ), 
				'May' => $wp_locale->get_month_abbrev( $wp_locale->get_month('05') ), 
				'Jun' => $wp_locale->get_month_abbrev( $wp_locale->get_month('06') ), 
				'Jul' => $wp_locale->get_month_abbrev( $wp_locale->get_month('07') ), 
				'Aug' => $wp_locale->get_month_abbrev( $wp_locale->get_month('08') ), 
				'Sep' => $wp_locale->get_month_abbrev( $wp_locale->get_month('09') ), 
				'Oct' => $wp_locale->get_month_abbrev( $wp_locale->get_month('10') ), 
				'Nov' => $wp_locale->get_month_abbrev( $wp_locale->get_month('11') ), 
				'Dec' => $wp_locale->get_month_abbrev( $wp_locale->get_month('12') )
			); 
		}

		/**
		 * Helper method to return an array of translated month names or short month names
		 * @return Array translated month names
		 */
		public function monthNames( $short = false ) {
			if ($short)
				return $this->monthsShort;
			return $this->monthsFull;
		}

		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public function flushRewriteRules() {
			global $wp_rewrite; 
			$wp_rewrite->flush_rules();
			// in case this was called too early, let's get it in the end.
			add_action('shutdown', array($this, 'flushRewriteRules'));
		}		
		/**
		 * Adds the event specific query vars to Wordpress
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 * @return mixed array of query variables that this plugin understands
		 */
		public function eventQueryVars( $qvars ) {
			$qvars[] = 'eventDisplay';
			$qvars[] = 'eventDate';
			$qvars[] = 'ical';
			$qvars[] = 'start_date';
			$qvars[] = 'end_date';
			return $qvars;			
		}
		/**
		 * Adds Event specific rewrite rules.
		 *
		 *	events/				=>	/?post_type=tribe_events
		 *	events/month		=>	/?post_type=tribe_events&eventDisplay=month
		 *	events/upcoming		=>	/?post_type=tribe_events&eventDisplay=upcoming
		 *	events/past			=>	/?post_type=tribe_events&eventDisplay=past
		 *	events/2008-01/#15	=>	/?post_type=tribe_events&eventDisplay=bydate&eventDate=2008-01-01
		 * events/category/some-events-category => /?post_type=tribe_events&tribe_event_cat=some-events-category
		 *
		 * @return void
		 */
		public function filterRewriteRules( $wp_rewrite ) {
			if ( '' == get_option('permalink_structure') ) {
		
			}

			$base = trailingslashit( $this->rewriteSlug );
			$baseSingle = trailingslashit( $this->rewriteSlugSingular );
			$baseTax = trailingslashit( $this->taxRewriteSlug );
			$baseTax = "(.*)" . $baseTax;
	
			$month = $this->monthSlug;
			$upcoming = $this->upcomingSlug;
			$past = $this->pastSlug;
			$newRules = array();
	
			// single event
			$newRules[$baseSingle . '([^/]+)/(\d{4}-\d{2}-\d{2})/?$'] = 'index.php?' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDate=" . $wp_rewrite->preg_index(2);
			$newRules[$baseSingle . '([^/]+)/(\d{4}-\d{2}-\d{2})/ical/?$'] = 'index.php?ical=1&' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDate=" . $wp_rewrite->preg_index(2);
			$newRules[$baseSingle . '([^/]+)/all/?$'] = 'index.php?' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDisplay=all";			
	
			$newRules[$base . 'page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'ical'] = 'index.php?post_type=' . self::POSTTYPE . '&ical=1';
			$newRules[$base . '(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&feed=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $month] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$base . $upcoming . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $upcoming] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$base . $past . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $past] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$base . '(\d{4}-\d{2})$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(1);
			$newRules[$base . '(\d{4}-\d{2}-\d{2})$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=day' .'&eventDate=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'feed/?$'] = 'index.php?eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$base . '?$']						= 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');

			// single ical
			$newRules[$baseSingle . '([^/]+)/ical/?$' ] = 'index.php?post_type=' . self::POSTTYPE . '&name=' . $wp_rewrite->preg_index(1) . '&ical=1';

			// taxonomy rules.
			$newRules[$baseTax . '([^/]+)/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $month] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$baseTax . '([^/]+)/' . $upcoming . '/page/(\d+)'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $upcoming] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$baseTax . '([^/]+)/' . $past . '/page/(\d+)'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $past] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$baseTax . '([^/]+)/(\d{4}-\d{2})$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/feed/?$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$baseTax . '([^/]+)/?$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');
			$newRules[$baseTax . '([^/]+)/ical/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&ical=1';
			$newRules[$baseTax . '([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&feed=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&tribe_events_cat=' . $wp_rewrite->preg_index(2);
	
			$wp_rewrite->rules = $newRules + $wp_rewrite->rules; 
		}

		/**
		 * returns various internal events-related URLs
		 * @param string $type type of link. See switch statement for types.
		 * @param string $secondary for $type = month, pass a YYYY-MM string for a specific month's URL
		 */

		public function getLink( $type = 'home', $secondary = false, $term = null ) {
			// if permalinks are off or user doesn't want them: ugly.
			if( '' == get_option('permalink_structure') ) {
				return esc_url($this->uglyLink($type, $secondary));
			}

         // account for semi-pretty permalinks
         if( strpos(get_option('permalink_structure'),"index.php") !== FALSE ) {
            $eventUrl = trailingslashit( home_url() . '/index.php/' . $this->rewriteSlug );
         } else {
            $eventUrl = trailingslashit( home_url() . '/' . $this->rewriteSlug );
         }
	
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = trailingslashit( get_term_link( get_query_var('term'), self::TAXONOMY ) );
			} else if ( $term ) {
				$eventUrl = trailingslashit( get_term_link( $term, self::TAXONOMY ) );
			}
	
			switch( $type ) {
		
				case 'home':
					return esc_url($eventUrl);
				case 'month':
					if ( $secondary ) {
						return esc_url($eventUrl . $secondary);
					}
					return esc_url($eventUrl . $this->monthSlug . '/');
				case 'upcoming':
					return esc_url($eventUrl . $this->upcomingSlug . '/');
				case 'past':
					return esc_url($eventUrl . $this->pastSlug . '/');
				case 'dropdown':
					return esc_url($eventUrl);
				case 'ical':
					if ( $secondary == 'single' )
						$eventUrl = trailingslashit(get_permalink());
					return esc_url($eventUrl . 'ical/');
				case 'single':
				global $post;
					$p = $secondary ? $secondary : $post;
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$link = trailingslashit(get_permalink($p));
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );										
				return esc_url($link);
			case 'day':
				$date = strtotime($secondary);
				$secondary = date('Y-m-d', $date);
				return esc_url($eventUrl . $secondary);
			case 'all':
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = trailingslashit(get_permalink());
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );										
					return esc_url($eventUrl . 'all/');
				default:
					return esc_url($eventUrl);
			}
	
		}

		protected function uglyLink( $type = 'home', $secondary = false ) {
	
			$eventUrl = add_query_arg('post_type', self::POSTTYPE, home_url() );
	
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = add_query_arg( self::TAXONOMY, get_query_var('term'), $eventUrl );
			}
	
			switch( $type ) {
		
				case 'home':
					return $eventUrl;
				case 'month':
					$month = add_query_arg( array( 'eventDisplay' => 'month'), $eventUrl );
					if ( $secondary )
						$month = add_query_arg( array( 'eventDate' => $secondary ), $month );
					return $month;
				case 'upcoming':
					return add_query_arg( array( 'eventDisplay' => 'upcoming'), $eventUrl );
				case 'past':
					return add_query_arg( array( 'eventDisplay' => 'past'), $eventUrl );
				case 'dropdown':
					$dropdown = add_query_arg( array( 'eventDisplay' => 'month', 'eventDate' => ' '), $eventUrl );
					return rtrim($dropdown); // tricksy
				case 'ical':
					if ( $secondary == 'single' ) {
						return add_query_arg('ical', '1', get_permalink() );
					}
					return home_url() . '/?ical';
				case 'single':
					global $post;
					$post = $secondary ? $secondary : $post;
					$link = trailingslashit(get_permalink($post));
					return $link;
				case 'all':
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = add_query_arg('eventDisplay', 'all', get_permalink() );
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );															
					return $eventUrl;
				default:
					return $eventUrl;
			}
		}

		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function get_google_maps_args() {

			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			$languageCode = substr( get_bloginfo( 'language' ), 0, 2 );
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('tribe_get_'.$val);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return 'f=q&amp;source=embed&amp;hl=' . $languageCode . '&amp;geocode=&amp;q='. urlencode( trim( $toUrlEncode ) );
			return "";
	
		}

		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function googleMapLink( $postId = null ) {
			if ( $postId === null || !is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
	
			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('tribe_get_'.$val, $postId);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return "http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=" . urlencode( trim( $toUrlEncode ) );
			return "";
		}
		
		/**
		 *  Returns the full address of an event along with HTML markup.  It 
		 *  loads the full-address template to generate the HTML
		 */  
		public function fullAddress( $postId=null, $includeVenueName=false ) {
			ob_start();
			load_template( TribeEventsTemplates::getTemplateHierarchy( 'full-address' ), false );
			$address = ob_get_contents();
			ob_end_clean();
			return $address;
		}

		/**
		 *  Returns a string version of the full address of an event
		 */
		public function fullAddressString( $postId=null ) {
			$address = '';
			if( tribe_get_address( $postId ) ) { 
				$address .= tribe_get_address( $postId );
			} 

			if( tribe_get_city( $postId ) ) {
				if($address != '') $address .= ", ";
				$address .= tribe_get_city( $postId );
			}

			if( tribe_get_region( $postId ) ) {
				if($address != '') $address .= ", ";
				$address .= tribe_get_region( $postId );
			}

			if( tribe_get_zip( $postId ) ) { 
				if($address != '') $address .= ", ";
				$address .= tribe_get_zip( $postId );
			} 

			if( tribe_get_country( $postId ) ) {
				if($address != '') $address .= ", ";
				$address .= tribe_get_country( $postId );
			}

			return $address;
		}

		/**
		 * This plugin does not have any deactivation functionality. Any events, categories, options and metadata are
		 * left behind.
		 * 
		 * @return void
		 */
		public function on_deactivate( ) { 
			$this->flushRewriteRules();
		}

		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 */
		public function dateToTimeStamp( $date, $hour, $minute, $meridian ) {
			if ( preg_match( '/(PM|pm)/', $meridian ) && $hour < 12 ) $hour += "12";
			if ( preg_match( '/(AM|am)/', $meridian ) && $hour == 12 ) $hour = "00";
			$date = $this->dateHelper($date);
			return "$date $hour:$minute:00";
		}
		public function getTimeFormat( $dateFormat = TribeDateUtils::DATEONLYFORMAT ) {
			return $dateFormat . ' ' . get_option( 'time_format', TribeDateUtils::TIMEFORMAT );
		}

/*
		 * Ensures date follows proper YYYY-MM-DD format
		 * converts /, - and space chars to -
		 */
		protected function dateHelper( $date ) {

			if($date == '')
				return date(TribeDateUtils::DBDATEFORMAT);

			$date = str_replace( array('-','/',' ',':',chr(150),chr(151),chr(45)), '-', $date );
			// ensure no extra bits are added
			list($year, $month, $day) = explode('-', $date);
	
			if ( ! checkdate($month, $day, $year) )
				$date = date(TribeDateUtils::DBDATEFORMAT); // today's date if error
			else
				$date = $year . '-' . $month . '-' . $day;

			return $date;
		}

		/**
		 * Adds an alias for get_post_meta so we can do extra stuff to the plugin values.
		 * If you need the raw unfiltered data, use get_post_meta directly. 
		 * This is mainly for templates.
		 */
		public function getEventMeta( $id, $meta, $single = true ){
			$use_def_if_empty = tribe_get_option('defaultValueReplace');
			if($use_def_if_empty){
				$cleaned_tag = str_replace('_Event','',$meta);
				$default = tribe_get_option('eventsDefault'.$cleaned_tag);
				$default = apply_filters('filter_eventsDefault'.$cleaned_tag,$default);
				return (get_post_meta( $id, $meta, $single )) ? get_post_meta( $id, $meta, $single ) : $default;
			}else{
				return get_post_meta( $id, $meta, $single );
			}

		}
		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param string $postId 
		 * @return void
		 */
		public function addEventMeta( $postId, $post ) {
			// only continue if it's an event post
			if ( $post->post_type != self::POSTTYPE || defined('DOING_AJAX') ) {
				return;
			}
			// don't do anything on autosave or auto-draft either or massupdates
			if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') ) {
				return;
			}
	
			// remove these actions even if nonce is not set
			// note: we're removing these because these actions are actually for PRO,
			// these functions are used when editing an existing venue or organizer
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );			
			
			if( !isset($_POST['ecp_nonce']) )
				return;
				
			if ( !wp_verify_nonce( $_POST['ecp_nonce'], TribeEvents::POSTTYPE ) )
				return;
	
			if ( !current_user_can( 'publish_posts' ) )
				return;

			$_POST['Organizer'] = stripslashes_deep($_POST['organizer']);
			$_POST['Venue'] = stripslashes_deep($_POST['venue']);


			/**
			 * When using pro and we have a VenueID/OrganizerID, we just save the ID, because we're not
			 * editing the venue/organizer from within the event.
			 */
			if( isset($_POST['Venue']['VenueID']) && !empty($_POST['Venue']['VenueID']) && class_exists('TribeEventsPro') )
				$_POST['Venue'] = array('VenueID' => $_POST['Venue']['VenueID']);

			if( isset($_POST['Organizer']['OrganizerID']) && !empty($_POST['Organizer']['OrganizerID']) && class_exists('TribeEventsPro') )
				$_POST['Organizer'] = array('OrganizerID' => $_POST['Organizer']['OrganizerID']);


			TribeEventsAPI::saveEventMeta($postId, $_POST, $post);
		}


		//** If you are saving a new venu separate from an event
		public function save_venue_data( $postID = null, $post=null ) {
			global $_POST;

			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a venue Or
			// finally, called from the save_post action, but on save_posts that
			// are not venue posts
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
						isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ||
						(isset($_POST['venue']) && !$_POST['venue']) ||
						($post->post_type != self::VENUE_POST_TYPE && $postID)) {
				return;
			}
			
			if ( !current_user_can( 'publish_posts' ) )
				return;					

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );

			if( !isset($_POST['post_title']) || !$_POST['post_title'] ) { $_POST['post_title'] = "Unnamed Venue"; }
			$_POST['venue']['Venue'] = $_POST['post_title'];
			$data = stripslashes_deep($_POST['venue']);
			$venue_id = TribeEventsAPI::updateVenue($postID, $data);

			return $venue_id;
		}

		function get_venue_info($p = null){
			$r = new WP_Query(array('post_type' => self::VENUE_POST_TYPE, 'nopaging' => 1, 'post_status' => 'publish', 'ignore_sticky_posts ' => 1,'orderby'=>'title', 'order'=>'ASC','p' => $p));
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		//** If you are saving a new organizer along with the event, we will do this:
		public function save_organizer_data( $postID = null, $post=null ) {
			global $_POST;

			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a organizer Or
			// finally, called from the save_post action, but on save_posts that
			// are not organizer posts
			
			if( !isset($_POST['organizer']) ) $_POST['organizer'] = null;
			
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
						isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ||
						!$_POST['organizer'] ||
						($post->post_type != self::ORGANIZER_POST_TYPE && $postID)) {
				return;
			}
			
			if ( !current_user_can( 'publish_posts' ) )
				return;										

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			$data = stripslashes_deep($_POST['organizer']);

			$organizer_id = TribeEventsAPI::updateOrganizer($postID, $data);

			/**
			 * Put our hook back
			 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/save_post#Avoiding_infinite_loops
			 */
			add_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			return $organizer_id;
		}

		// abstracted for EventBrite
		public function add_new_organizer($data, $post=null) {
			if($data['OrganizerID'])
				return $data['OrganizerID'];

			if ( $post->post_type == self::ORGANIZER_POST_TYPE && $post->ID) {
				$data['OrganizerID'] = $post->ID;
			}

			//google map checkboxes
			$postdata = array(
				'post_title' => $data['Organizer'],
				'post_type' => self::ORGANIZER_POST_TYPE,
				'post_status' => 'publish',
				'ID' => $data['OrganizerID']
			);

			if( isset($data['OrganizerID']) && $data['OrganizerID'] != "0" ) {
				$organizer_id = $data['OrganizerID'];
				wp_update_post( array('post_title' => $data['Organizer'], 'ID'=>$data['OrganizerID'] ));
			} else {
				$organizer_id = wp_insert_post($postdata, true);
			}

			if( !is_wp_error($organizer_id) ) {
				foreach ($data as $key => $var) {
					update_post_meta($organizer_id, '_Organizer'.$key, $var);
				}

				return $organizer_id;
			}
		}

		function get_organizer_info($p = null){
			$r = new WP_Query(array('post_type' => self::ORGANIZER_POST_TYPE, 'nopaging' => 1, 'post_status' => 'publish', 'ignore_sticky_posts ' => 1,'orderby'=>'title', 'order'=>'ASC', 'p' => $p));
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function EventsChooserBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			foreach ( $this->metaTags as $tag ) {
				if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
					// Sort the meta to make sure it is correct for recurring events
					$meta = get_post_meta($postId,$tag); 
					sort($meta);
					if (isset($meta[0])) { $$tag = $meta[0]; }
				} else {
					$cleaned_tag = str_replace('_Event','',$tag);
					$$tag = class_exists('TribeEventsPro') ? tribe_get_option('eventsDefault'.$cleaned_tag) : "";
				}
			}
			
			if( isset($_EventOrganizerID) && $_EventOrganizerID && tribe_get_option('defaultValueReplace') ) {
				foreach($this->organizerTags as $tag) {
					$$tag = get_post_meta($_EventOrganizerID, $tag, true );
				}
			}

			if( isset($_EventVenueID) && $_EventVenueID && tribe_get_option('defaultValueReplace') ){
				foreach($this->venueTags as $tag) {
					$$tag = get_post_meta($_EventVenueID, $tag, true );
				}

			}elseif ( tribe_get_option('defaultValueReplace') ){
				$defaults = $this->venueTags;
				$defaults[] = '_VenueState';
				$defaults[] = '_VenueProvince';

				foreach ( $defaults as $tag ) {
					if ( !$postId || !isset($_GET['post']) ) { //if there is a post AND the post has been saved at least once.
						$cleaned_tag = str_replace('_Venue','',$tag);

						if($cleaned_tag == 'Cost')
							continue;

						${'_Venue'.$cleaned_tag} = class_exists('TribeEventsPro') ? tribe_get_option('eventsDefault'.$cleaned_tag) : "";
					}
				}
				if ( isset($_VenueState) ) {
					$_VenueStateProvince = $_VenueState; // we want to use default values here
				} else {
					$_VenueStateProvince = $_VenueProvince;
				}
			}
			
			$_EventStartDate = (isset($_EventStartDate)) ? $_EventStartDate : null;
			$_EventEndDate = (isset($_EventEndDate)) ? $_EventEndDate : null;
			$_EventAllDay = isset($_EventAllDay) ? $_EventAllDay : false;
			$isEventAllDay = ( $_EventAllDay == 'yes' || ! TribeDateUtils::dateOnly( $_EventStartDate ) ) ? 'checked="checked"' : ''; // default is all day for new posts
			$startMonthOptions 		= TribeEventsViewHelpers::getMonthOptions( $_EventStartDate );
			$endMonthOptions 			= TribeEventsViewHelpers::getMonthOptions( $_EventEndDate );
			$startYearOptions 		= TribeEventsViewHelpers::getYearOptions( $_EventStartDate );
			$endYearOptions			= TribeEventsViewHelpers::getYearOptions( $_EventEndDate );
			$startMinuteOptions 		= TribeEventsViewHelpers::getMinuteOptions( $_EventStartDate );
			$endMinuteOptions		= TribeEventsViewHelpers::getMinuteOptions( $_EventEndDate );
			$startHourOptions				= TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventStartDate, true );
			$endHourOptions			= TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventEndDate );
			$startMeridianOptions = TribeEventsViewHelpers::getMeridianOptions( $_EventStartDate, true );
			$endMeridianOptions		= TribeEventsViewHelpers::getMeridianOptions( $_EventEndDate );
	
			if( $_EventStartDate )
				$start = TribeDateUtils::dateOnly($_EventStartDate);

			$EventStartDate = ( isset($start) && $start ) ? $start : date('Y-m-d');
	
			if ( !empty($_REQUEST['eventDate']) )
				$EventStartDate = $_REQUEST['eventDate'];
	
			if( $_EventEndDate )
				$end = TribeDateUtils::dateOnly($_EventEndDate);

			$EventEndDate = ( isset($end) && $end ) ? $end : date('Y-m-d');
			$recStart = isset($_REQUEST['event_start']) ? $_REQUEST['event_start'] : null;
			$recPost = isset($_REQUEST['post']) ? $_REQUEST['post'] : null;
	
			if ( !empty($_REQUEST['eventDate']) ) {
				$duration = get_post_meta( $postId, '_EventDuration', true );
				$EventEndDate = TribeDateUtils::dateOnly( strtotime($EventStartDate) + $duration, true );
			}

			include( $this->pluginPath . 'admin-views/events-meta-box.php' );
		}

		public function displayEventVenueInput($postId) {
			$VenueID = get_post_meta( $postId, '_EventVenueID', true);
			?><input type='hidden' name='venue[VenueID]' value='<?php echo esc_attr($VenueID) ?>'/><?php
		}

		public function displayEventOrganizerInput($postId) {
			$OrganizerID = get_post_meta( $postId, '_EventOrganizerID', true);
			?><input type='hidden' name='organizer[OrganizerID]' value='<?php echo esc_attr($OrganizerID) ?>'/><?php
		}
		

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function VenueMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::VENUE_POST_TYPE){
			
				foreach ( $this->venueTags as $tag ) {
					if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$$tag = esc_html(get_post_meta( $postId, $tag, true ));
					} else {
						$cleaned_tag = str_replace('_Venue','',$tag);
						$$tag = tribe_get_option('eventsDefault'.$cleaned_tag);
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="VenueInfo">
					<?php
					include( $this->pluginPath . 'admin-views/venue-meta-box.php' );
					?>
					</table>
				</div>
			<?php
		}		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function OrganizerMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::ORGANIZER_POST_TYPE){
			
				foreach ( $this->organizerTags as $tag ) {
					if ( $postId && isset($_GET['post']) && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$$tag = get_post_meta( $postId, $tag, true );
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="OrganizerInfo">
					<?php
					include( $this->pluginPath . 'admin-views/organizer-meta-box.php' );
					?>
					</table>
				</div>
			<?php
		}

		/**
		 * Handle ajax requests from admin form
		 */
		public function ajax_form_validate() {
			if ($_REQUEST['name'] && $_REQUEST['nonce'] && wp_verify_nonce($_REQUEST['nonce'], 'tribe-validation-nonce')) {
				if($_REQUEST['type'] == 'venue'){
					echo $this->verify_unique_name($_REQUEST['name'],'venue');
					exit;
				} elseif ($_REQUEST['type'] == 'organizer'){
					echo $this->verify_unique_name($_REQUEST['name'],'organizer');
					exit;
				}
			}
		}

		/**
		 * Verify that a venue or organizer is unique
		 *
		 * @param string $name - name of venue or organizer
		 * @param string $type - post type (venue or organizer) 
		 * @return boolean
		 */
		public function verify_unique_name($name, $type){
			global $wpdb;
			$name = stripslashes($name);
			if ('' == $name) { return 1; }
			if ($type == 'venue') {
				$post_type = self::VENUE_POST_TYPE;
			} elseif($type == 'organizer') {
				$post_type = self::ORGANIZER_POST_TYPE;
			}
			$results = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type = %s && post_title = %s && post_status = 'publish'",$post_type,$name));
			return ($results) ? 0 : 1;
		}

		/**
		 * Given a date (YYYY-MM-DD), returns the first of the next month
		 *
		 * @param date
		 * @return date
		 */
		public function nextMonth( $date ) {
			$dateParts = split( '-', $date );
			if ( $dateParts[1] == 12 ) {
				$dateParts[0]++;
				$dateParts[1] = "01";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]++;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 && strlen( $dateParts[1] ) == 1 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =	$dateParts[0] . '-' . $dateParts[1];
			return $return;
		}
		/**
		 * Given a date (YYYY-MM-DD), return the first of the previous month
		 *
		 * @param date
		 * @return date
		 */
		public function previousMonth( $date ) {
			$dateParts = split( '-', $date );

			if ( $dateParts[1] == 1 ) {
				$dateParts[0]--;
				$dateParts[1] = "12";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]--;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =	$dateParts[0] . '-' . $dateParts[1];

			return $return;
		}

		/**
		 * Callback for adding the Meta box to the admin page
		 * @return void
		 */
		public function addEventBox( ) {
			add_meta_box( 'Event Details', $this->pluginName, array( $this, 'EventsChooserBox' ), self::POSTTYPE, 'normal', 'high' );
			add_meta_box( 'Event Options', __('Event Options', 'tribe-events-calendar'), array( $this, 'eventMetaBox' ), self::POSTTYPE, 'side', 'default' );
	
			add_meta_box( 'Venue Details', __('Venue Information', 'tribe-events-calendar'), array( $this, 'VenueMetaBox' ), self::VENUE_POST_TYPE, 'normal', 'high' );
			add_meta_box( 'Organizer Details', __('Organizer Information', 'tribe-events-calendar'), array( $this, 'OrganizerMetaBox' ), self::ORGANIZER_POST_TYPE, 'normal', 'high' );
		}
		public function eventMetaBox() {
			include( $this->pluginPath . 'admin-views/event-sidebar-options.php' );
		}

		public function getDateString( $date ) {
			$monthNames = $this->monthNames();
			$dateParts = split( '-', $date );
			$timestamp = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );
			return $monthNames[date( "F", $timestamp )] . " " . $dateParts[0];
		}
		/**
		 * echo the next tab index
		 * @return void
		 */
		public function tabIndex() {
			echo $this->tabIndexStart;
			$this->tabIndexStart++;
		}

		public function getEvents( $args = '' ) {
			$tribe_ecp = TribeEvents::instance();
			$defaults = array(
				'posts_per_page' => get_option( 'posts_per_page', 10 ),
				'post_type' => TribeEvents::POSTTYPE,
				'orderby' => 'event_date',
				'order' => 'ASC'
			);			

			$args = wp_parse_args( $args, $defaults);
			return TribeEventsQuery::getEvents($args);
		}

		public function isEvent( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::POSTTYPE ) {
				return true;
			}
			return false;
		}

		public function isVenue( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::VENUE_POST_TYPE ) {
				return true;
			}
			return false;
		}

		public function isOrganizer( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::ORGANIZER_POST_TYPE ) {
				return true;
			}
			return false;
		}

		/**
	 ** Get a "previous/next post" link for events. Ordered by start date instead of ID.
	 **/

		public function get_event_link($post, $mode = 'next',$anchor = false){
			global $wpdb;

			if($mode == 'previous'){
				$order = 'DESC';
				$sign = '<';
			}else{
				$order = 'ASC';
				$sign = '>';
			}
	
			$date = $post->EventStartDate;
			$id = $post->ID;
	
			$eventsQuery = "
				SELECT $wpdb->posts.*, d1.meta_value as EventStartDate
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
				WHERE $wpdb->posts.post_type = '".self::POSTTYPE."'
				AND d1.meta_key = '_EventStartDate'
				AND ((d1.meta_value = '" .$date . "' AND ID $sign ".$id.") OR
					d1.meta_value $sign '" .$date . "')
				AND $wpdb->posts.post_status = 'publish'
				AND ($wpdb->posts.ID != $id OR d1.meta_value != '$date')
				ORDER BY TIMESTAMP(d1.meta_value) $order, ID $order
				LIMIT 1";

			$results = $wpdb->get_row($eventsQuery, OBJECT);
			if(is_object($results)) {
				if ( !$anchor ) {
					$anchor = $results->post_title;
            } elseif ( strpos( $anchor, '%title%' ) !== false ) {
					$anchor = preg_replace( '|%title%|', $results->post_title, $anchor );
				}

				echo '<a href='.tribe_get_event_link($results).'>'.$anchor.'</a>';
		
			}
		}

		public function addMetaLinks( $links, $file ) {
			if ( $file == $this->pluginDir . 'the-events-calendar.php' ) {
				$anchor = __( 'Support', 'tribe-events-calendar' );
				$links []= '<a href="http://wordpress.org/tags/the-events-calendar?forum_id=10">' . $anchor . '</a>';

				$anchor = __( 'View All Add-Ons', 'tribe-events-calendar' ); 
				$links []= '<a href="'.self::$tribeUrl.'events-calendar/features/add-ons/?ref=tec-plugin">' . $anchor . '</a>';
			}
			return $links;
		}

		public function dashboardWidget() {
			wp_add_dashboard_widget( 'tribe_dashboard_widget', __( 'News from Modern Tribe' ), array( $this, 'outputDashboardWidget' ) );
		}

		public function outputDashboardWidget() {
			echo '<div class="rss-widget">';
			wp_widget_rss_output( self::FEED_URL, array( 'items' => 10 ) );
			echo "</div>";
		}

		protected function constructDaysOfWeek() {
			global $wp_locale;
			for ($i = 0; $i <= 6; $i++) {
				$day = $wp_locale->get_weekday($i);
				$this->daysOfWeek[$i] = $day;
				$this->daysOfWeekShort[$i] = $wp_locale->get_weekday_abbrev($day);
				$this->daysOfWeekMin[$i] = $wp_locale->get_weekday_initial($day);
			}
		}

		public function setPostExceptionThrown( $thrown ) {
			$this->postExceptionThrown = $thrown;
		}
		public function getPostExceptionThrown() {
			return $this->postExceptionThrown;
		}

		public function do_action($name, $event_id = null, $showMessage = false, $extra_args = null) {
			try {
				do_action( $name, $event_id, $extra_args );
				if( !$this->getPostExceptionThrown() && $event_id ) delete_post_meta( $event_id, TribeEvents::EVENTSERROROPT );
			} catch ( TribeEventsPostException $e ) {
				$this->setPostExceptionThrown(true);
				if ($event_id) {
					update_post_meta( $event_id, self::EVENTSERROROPT, trim( $e->getMessage() ) );
				}

				if( $showMessage ) {
					$e->displayMessage($showMessage);
				}
			}
		}

		public function maybeShowMetaUpsell($postId) {
			?><tr class="eventBritePluginPlug">
				<td colspan="2" class="tribe_sectionheader">
					<h4><?php _e('Additional Functionality', 'tribe-events-calendar'); ?></h4>	
				</td>
			</tr>
			<tr class="eventBritePluginPlug">
				<td colspan="2">
					<p><?php _e('Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ) ?> <?php printf( __('Check out the <a href="%s">available Add-Ons</a>.', 'tribe-events-calendar' ), TribeEvents::$tribeUrl.'shop/?ref=tec-event' ); ?></p>
				</td>
			</tr><?php 
		}

		public function maybeShowSettingsUpsell($postId) {
			?><p><?php _e('Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ); ?></p>
			<p><?php printf(__('Check out the <a href="%s">available Add-Ons</a>.', 'tribe-events-calendar' ), self::$tribeUrl.'?ref=tec-options') ?></p> <?php 
		}
		
		/**
		 * Helper function for getting Post Id. Accepts null or a post id.
		 *
		 * @param int $postId (optional)
		 * @return int post ID
		 */
		public static function postIdHelper( $postId = null ) {
			if ( $postId != null && is_numeric( $postId ) > 0 ) {
				return (int) $postId;
			} else {
				return get_the_ID();
			}
		}

	} // end TribeEvents class

} // end if !class_exists TribeEvents
?>
