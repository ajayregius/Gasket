<?php

if(!class_exists('Kmn_Func')) : // declare Kmn_Func class only if class has not been defined (working with Komoona Ads and Komoona AdSense plugin)

    class Kmn_Func {

        const KOMOONA_ADS = 'komoona_ads';
        const KOMOONA_CPM = 'komoona_cpm';
        const KOMOONA_ADSENSE = 'komoona_adsense';

        static public function komoona_plugin_type($basename) {
            return strtolower(str_replace('.php', '', $basename));
        }

        static public function komoona_ads_activate() {
            Kmn_Func::komoona_activate(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_ads_uninstall() {
            Kmn_Func::komoona_uninstall(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_ads_settings_link($links) {
            return Kmn_Func::komoona_settings_link(Kmn_Func::KOMOONA_ADS, $links);
        }

        static public function komoona_ads_plugin_init() {
            Kmn_Func::komoona_plugin_init(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_ads_options_engugue() {
            Kmn_Func::komoona_options_engugue(Kmn_Func::KOMOONA_ADS);
        }

        static public function register_komoona_ads_settings() {
            Kmn_Func::register_komoona_settings(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_ads_enqueue_script() {
            Kmn_Func::komoona_enqueue_script(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_ads_register_widget() {
            Kmn_Func::komoona_register_widget(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_ads_plugin_options() {
            Kmn_Func::komoona_plugin_options(Kmn_Func::KOMOONA_ADS);
        }

        static public function komoona_adsense_activate() {
            Kmn_Func::komoona_activate(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function komoona_adsense_uninstall() {
            Kmn_Func::komoona_uninstall(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function komoona_adsense_settings_link($links) {
            return Kmn_Func::komoona_settings_link(Kmn_Func::KOMOONA_ADSENSE, $links);
        }

        static public function komoona_adsense_plugin_init() {
            Kmn_Func::komoona_plugin_init(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function komoona_adsense_options_engugue() {
            Kmn_Func::komoona_options_engugue(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function register_komoona_adsense_settings() {
            Kmn_Func::register_komoona_settings(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function komoona_adsense_enqueue_script() {
            Kmn_Func::komoona_enqueue_script(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function komoona_adsense_register_widget() {
            Kmn_Func::komoona_register_widget(Kmn_Func::KOMOONA_ADSENSE);
        }

        static public function komoona_adsense_plugin_options() {
            Kmn_Func::komoona_plugin_options(Kmn_Func::KOMOONA_ADSENSE);
        }
		
        static public function komoona_cpm_activate() {
            Kmn_Func::komoona_activate(Kmn_Func::KOMOONA_CPM);
        }

        static public function komoona_cpm_uninstall() {
            Kmn_Func::komoona_uninstall(Kmn_Func::KOMOONA_CPM);
        }

        static public function komoona_cpm_settings_link($links) {
            return Kmn_Func::komoona_settings_link(Kmn_Func::KOMOONA_CPM, $links);
        }

        static public function komoona_cpm_plugin_init() {
            Kmn_Func::komoona_plugin_init(Kmn_Func::KOMOONA_CPM);
        }

        static public function komoona_cpm_options_engugue() {
            Kmn_Func::komoona_options_engugue(Kmn_Func::KOMOONA_CPM);
        }

        static public function register_komoona_cpm_settings() {
            Kmn_Func::register_komoona_settings(Kmn_Func::KOMOONA_CPM);
        }

        static public function komoona_cpm_enqueue_script() {
            Kmn_Func::komoona_enqueue_script(Kmn_Func::KOMOONA_CPM);
        }

        static public function komoona_cpm_register_widget() {
            Kmn_Func::komoona_register_widget(Kmn_Func::KOMOONA_CPM);
        }

        static public function komoona_cpm_plugin_options() {
            Kmn_Func::komoona_plugin_options(Kmn_Func::KOMOONA_CPM);
        }

        /**
         * Get cURL extension version (if installed)
         */
        static public function get_curl_version() {
            $curl_version = NULL;

            $extensions = get_loaded_extensions();
            if($extensions) {
                foreach($extensions as $extension) {
                    if($extension === 'curl' && function_exists('curl_version')) {
                        $curl_version = curl_version();
                    }
                }
            }

            return $curl_version;
        }
        
        /**
         * Get the collection of installed Komoona plugins
         * @return <array> installed Komoona plugins
         */
        static public function komoona_installed_plugins()
        {
            $installed = array();
            
            $plugins = array(self::KOMOONA_ADS, self::KOMOONA_ADSENSE, self::KOMOONA_CPM);
            foreach($plugins as $plugin) {
                if(strlen(get_option($plugin . '_username')) != 0 || strlen(get_option($plugin . '_widget_layout_id')) != 0) {
                    $installed[$plugin] = $plugin;
                }
            }
            
            return $installed;
        }


        /**
         * Get the plugin name based on its type
         */
        static public function komoona_plugin_name($type) {
            $name = '';
            
            switch ($type) {
                case Kmn_Func::KOMOONA_ADS:
                    $name = 'Komoona Ads';
                    break;
                case Kmn_Func::KOMOONA_ADSENSE;
                    $name = 'Komoona AdSense Companion';
                    break;
                case Kmn_Func::KOMOONA_CPM:
                    $name = 'Komoona CPM Ads';
                    break;
                default:
                    $name = 'Komoona Ads';
                    break;
            }
            
            return $name;
        }
        
        /**
         * Register Komoona plugin default options to the data store. This function is called when the
         * plug in is activate
         */
        static private function komoona_activate($type) {
            // add default options to the data store
            add_option($type . '_widget_layout_name', Kmn_Func::komoona_get_option('komoona_widget_layout_name', ''));
            add_option($type . '_widget_layout_id', Kmn_Func::komoona_get_option('komoona_widget_layout_id', ''));
            add_option($type . '_script_url' , Kmn_Func::komoona_get_option('komoona_script_url', ''));
            add_option($type . '_script_footer', Kmn_Func::komoona_get_option('komoona_script_footer', TRUE));
            add_option($type . '_username', Kmn_Func::komoona_get_option('komoona_username', ''));
        }

        /**
         * Remove Komoona plugin options from the data store. This function is called when the
         * plug in is deactivate
         */
        static private function komoona_uninstall($type) {
            // remove plugin optioins from the data store
            delete_option($type . '_widget_layout_name');
            delete_option($type . '_widget_layout_id');
            delete_option($type . '_script_url');
            delete_option($type . '_script_footer');
            delete_option($type . '_username');
            delete_option($type . '_installed'); // added if installed
        
            // remove prev version if installed
            $type = 'komoona';
            delete_option($type . '_widget_layout_name');
            delete_option($type . '_widget_layout_id');
            delete_option($type . '_script_url');
            delete_option($type . '_username');
            delete_option($type . '_installed'); // added if installed
        }

        /**
         * Add settings link on plugin page
         */
        static private function komoona_settings_link($type, $links) {
            $settings_link = sprintf('<a href="options-general.php?page=%s_options">Settings</a>', $type);
            array_unshift($links, $settings_link);
            return $links;
        }

        /**
         * Init the Komoona plugin settings page
         */
        static private function komoona_plugin_init($type) {

            //call register settings function
            add_action('admin_init', 'Kmn_Func::register_' . $type . '_settings');

            $page_title = self::komoona_plugin_name($type);
            
            $menu_title = '';
            switch ($type) {
                case Kmn_Func::KOMOONA_ADS:
                    $menu_title = 'Komoona Ads';
                    break;
                case Kmn_Func::KOMOONA_ADSENSE;
                    $menu_title = 'Komoona AdSense';
                    break;
                case Kmn_Func::KOMOONA_CPM;
                    $menu_title = 'Komoona CPM Ads';
                    break;
                default:
                    $menu_title = 'Komoona Ads';
                    break;
            }

            $page = add_options_page($title, $menu_title, 'manage_options', $type . '_options', 'Kmn_Func::' . $type . '_plugin_options');
            add_action('admin_print_styles-' . $page, 'Kmn_Func::' . $type . '_options_engugue');
            
           // load the language file
            load_plugin_textdomain('komoona', false, dirname(plugin_basename( __FILE__ ) ) . '/languages');
        }

        /**
         * Enqueue css and scripts from the Komoona options page
         */
        static private function komoona_options_engugue($type) {
            wp_register_style('komoona.min.css', KOMOONA_PLUGIN_URL . '/resources/komoona.min.css');
            wp_enqueue_style('komoona.min.css');
            wp_register_script('jquery.bt.js', KOMOONA_PLUGIN_URL . '/resources/jquery.bt.js', array ('jquery'));
            wp_enqueue_script('jquery.bt.js');
            wp_register_script('komoona.min.js', KOMOONA_PLUGIN_URL . '/resources/komoona.min.js', array ('jquery'));
            wp_enqueue_script('komoona.min.js');
        }

        /**
         * Register default pluign setting and its sanitization callback
         */
        static private function register_komoona_settings($type) {
            register_setting('komoona-settings-group', $type . '_widget_layout_id');
            register_setting('komoona-settings-group', $type . '_script_url');
            register_setting('komoona-settings-group', $type . '_script_footer');
        }

        /**
         * Enqueue the komoona script to the page footer if komoona is enabled
         */
        static private function komoona_enqueue_script($type) {
            // get komoona script url.
            $script_url = get_option($type  . '_script_url');

            // should the script be placed on footer
            $footer = get_option($type  . '_script_footer');
			
            // add script to page
            wp_enqueue_script($type . '_script_url', $script_url, array(), false, $footer);
        }

        /**
         * @param type $type 
         */
        static private function komoona_register_widget($type) {
            $widget = '';

            switch ($type) {
                case Kmn_Func::KOMOONA_ADS:
                    $widget = 'Komoona_Ads_Widget';
                    break;
                case Kmn_Func::KOMOONA_ADSENSE;
                    $widget = 'Komoona_AdSense_Widget';
                    break;
                case Kmn_Func::KOMOONA_CPM;
                    $widget = 'Komoona_Cpm_Widget';
                    break;
                default:
                    $widget = 'Komoona_Ads_Widget';
                    break;
            }

            return register_widget($widget);
        }

        /**
         * A safe way of getting values for a named option from the options database table.
         * @param string $key: the value to get from the database
         * @param string $default: default value if key has no value
         */
        static private function komoona_get_option($key, $default) {
            $db_value = get_option($key, '');

            if(strlen($db_value) == 0) {
                $db_value = $default;
            }

            return $db_value;
        }
        
        /**
         * Get the collection of supported languages
         * @param type $language default selected
         */
        static private function komoona_get_languages($language) {
            ?>
            <option <?php echo $language === 'en-US' ? 'selected="selected"' : ''; ?> value="en-US"><?php _e('English', 'komoona'); ?></option>
            <option <?php echo $language === 'de-DE' ? 'selected="selected"' : ''; ?> value="de-DE"><?php _e('Deutsch', 'komoona'); ?></option>
            <option <?php echo $language === 'fr-FR' ? 'selected="selected"' : ''; ?> value="fr-FR"><?php _e('Française', 'komoona'); ?></option>
            <option <?php echo $language === 'pt-BR' ? 'selected="selected"' : ''; ?> value="pt-BR"><?php _e('Português', 'komoona'); ?></option>
            <option <?php echo $language === 'es-SP' ? 'selected="selected"' : ''; ?> value="es-SP"><?php _e('Español', 'komoona'); ?></option>
            <option <?php echo $language === 'ja-JP' ? 'selected="selected"' : ''; ?> value="ja-JP"><?php _e('日本', 'komoona'); ?></option>
            <option <?php echo $language === 'pl-PL' ? 'selected="selected"' : ''; ?> value="pl-PL"><?php _e('Polski', 'komoona'); ?></option>
            <option <?php echo $language === 'tr-TR' ? 'selected="selected"' : ''; ?> value="tr-TR"><?php _e('Türkçe', 'komoona'); ?></option>
            <option <?php echo $language === 'nl-NL' ? 'selected="selected"' : ''; ?> value="nl-NL"><?php _e('Nederlands', 'komoona'); ?></option>
            <option <?php echo $language === 'el-GR' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('ελληνικά', 'komoona'); ?></option>
            <option <?php echo $language === 'ru-RU' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('Pусский', 'komoona'); ?></option>
            <option <?php echo $language === 'mk-MK' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('Mакедонски', 'komoona'); ?></option>
            <option <?php echo $language === 'fi-FI' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('Suomi', 'komoona'); ?></option>
            <option <?php echo $language === 'uk-UA' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('Українська', 'komoona'); ?></option>
            <option <?php echo $language === 'da-DK' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('Dansk', 'komoona'); ?></option>
            <option <?php echo $language === 'nb-NO' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('Norsk', 'komoona'); ?></option>
            <option <?php echo $language === 'he-IL' ? 'selected="selected"' : ''; ?> value="el-GR"><?php _e('עברית', 'komoona'); ?></option>
            <?php
        }
        
        /**
         * Get the collection of supported currencies
         * @param type $currency default selected
         */
        static private function komoona_get_currencies($currency) {
            ?>
            <option <?php echo $currency === 'USD' ? 'selected="selected"' : ''; ?> value="USD"><?php _e('USD', 'komoona'); ?></option>
            <option <?php echo $currency === 'AUD' ? 'selected="selected"' : ''; ?> value="AUD"><?php _e('AUD', 'komoona'); ?></option>
            <option <?php echo $currency === 'CAD' ? 'selected="selected"' : ''; ?> value="CAD"><?php _e('CAD', 'komoona'); ?></option>
            <option <?php echo $currency === 'EUR' ? 'selected="selected"' : ''; ?> value="EUR"><?php _e('EUR', 'komoona'); ?></option>
            <option <?php echo $currency === 'JPY' ? 'selected="selected"' : ''; ?> value="JPY"><?php _e('JPY', 'komoona'); ?></option>
            <option <?php echo $currency === 'GBP' ? 'selected="selected"' : ''; ?> value="GBP"><?php _e('GBP', 'komoona'); ?></option>
            <?php
        }
        
        /**
         * Get the list of supported ad units
		 * @param string $type plugin type
         * @param string $adsize default selected
         */
        static private function komoona_get_ad_size($type, $adsize) {
            
            if($type === Kmn_Func::KOMOONA_ADS): ?>	
                <optgroup label="Banner">
                    <option <?php echo $adsize === '728x90' ? 'selected="selected"' : ''; ?> value="728x90"><?php _e('728x90', 'komoona'); ?></option>
                    <option <?php echo $adsize === '468x60' ? 'selected="selected"' : ''; ?> value="468x60"><?php _e('468x60', 'komoona'); ?></option>
                </optgroup>
                <optgroup label="Skyscrape">
                    <option <?php echo $adsize === '160x600' ? 'selected="selected"' : ''; ?> value="160x600"><?php _e('160x600', 'komoona'); ?></option>
                    <option <?php echo $adsize === '120x600' ? 'selected="selected"' : ''; ?> value="160x600"><?php _e('120x600', 'komoona'); ?></option>
                </optgroup>
                <optgroup label="Square">
                    <option <?php echo $adsize === '125x125' ? 'selected="selected"' : ''; ?> value="125x125"><?php _e('125x125', 'komoona'); ?></option>
                    <option <?php echo $adsize === '200x200' ? 'selected="selected"' : ''; ?> value="200x200"><?php _e('200x200', 'komoona'); ?></option>
                    <option <?php echo $adsize === '250x250' ? 'selected="selected"' : ''; ?> value="250x250"><?php _e('250x250', 'komoona'); ?></option>
                </optgroup>
                <optgroup label="Rectangle">
                    <option <?php echo $adsize === '300x250' ? 'selected="selected"' : ''; ?> value="300x250"><?php _e('300x250', 'komoona'); ?></option>
                    <option <?php echo $adsize === '180x150' ? 'selected="selected"' : ''; ?> value="180x150"><?php _e('180x150', 'komoona'); ?></option>
                    <option <?php echo $adsize === '175x250' ? 'selected="selected"' : ''; ?> value="175x250"><?php _e('175x250', 'komoona'); ?></option>
                    <option <?php echo $adsize === '300x100' ? 'selected="selected"' : ''; ?> value="300x100"><?php _e('300x100', 'komoona'); ?></option>
                </optgroup>
                <optgroup label="Large Rectangle">
                    <option <?php echo $adsize === '36x280' ? 'selected="selected"' : ''; ?> value="336x280"><?php _e('336x280', 'komoona'); ?></option>
                </optgroup>
                <optgroup label="Half Banner">
                    <option <?php echo $adsize === '434x30' ? 'selected="selected"' : ''; ?> value="434x30"><?php _e('434x30', 'komoona'); ?></option>
                </optgroup>
                <optgroup label="Vertical Banner">
                    <option <?php echo $adsize === '120x240' ? 'selected="selected"' : ''; ?> value="120x240"><?php _e('120x240', 'komoona'); ?></option>
                </optgroup>
            <?php else : ?>
                    <option <?php echo $adsize === '728x90' ? 'selected="selected"' : ''; ?> value="728x90"><?php _e('728x90', 'komoona'); ?></option>
                    <option <?php echo $adsize === '160x600' ? 'selected="selected"' : ''; ?> value="160x600"><?php _e('160x600', 'komoona'); ?></option>
                    <option <?php echo $adsize === '300x250' ? 'selected="selected"' : ''; ?> value="300x250"><?php _e('300x250', 'komoona'); ?></option>
            <?php endif;
        }

        /**
         * Create the Komoona options page
         */
        static private function komoona_plugin_options($type) {
            if(!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            ?>
            <div class="wrap">
            <?php
                // check if this plugin (or any Komoona plugin) are already installed in this WordPress instance
                $installed = self::komoona_installed_plugins();
                                
                // indicate the one of Komoona plugins is installed
                $komoona_installed = FALSE;
                
                // loop over all komoona plugins to find if already installed
                $plugins = array(self::KOMOONA_ADS, self::KOMOONA_ADSENSE, self::KOMOONA_CPM);
                foreach($plugins as $plugin) {
                    if($plugin !== $type) {
                        $komoona_installed = $komoona_installed || isset ($installed[$plugin]);
                    }
                }
                
                $plugin_installed = isset ($installed[$type]);
                 
                if(!$plugin_installed && !$komoona_installed) : // Komoona not installed 
            ?>
                <div id="kmn-create-account">
                    <h2><?php _e('Create Komoona Account', 'komoona'); ?></h2>
                    <h4>Hi there - Welcome to <?php echo self::komoona_plugin_name($type); ?>!<br/><?php _e('Please provide your email and choose a password so we can notify you when advertisers want to directly buy your ad space', 'komoona'); ?>:</h4>
                    <?php if($type === Kmn_Func::KOMOONA_CPM): ?>
                        <p style="width:720px;border:1px solid green;background-color:#ffd;color:black;padding:3px;"><?php _e('CPM service is subject to approval by Komoona (usually within 48 hours). Your site cannot not contain any adult, gambling or illegal content.', 'komoona');?></p>
                    <?php endif; ?>
                    <?php 
                        $curl = Kmn_Func::get_curl_version();
                        if(isset($curl)) : 
                    ?>
                        <a style="font-size: 100%;" onclick="return false;" href="javascript:void(0);" rel="kmn-account-exists"><?php _e('Already Have Komoona Account?', 'komoona'); ?></a>
                    <?php endif; ?>
                    <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" id="create-kmn-account">
                        <?php 
                            settings_fields('komoona-registration-group');
                            $post = $_SERVER['REQUEST_METHOD'] === 'POST'; 
                        ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php _e('Your Email', 'komoona'); ?></th>
                                <?php
                                    if($post) {
                                        $username = $_POST['kmn_username'];
                                    }
                                    else {
                                        $current_user = wp_get_current_user();
                                        $username = $current_user->user_email;
                                    }
                                ?>
                                <td>
                                    <input type="text" id="kmn-username" name="kmn_username" style="width:250px;" tabindex="1" value="<?php echo $username; ?>" />
                                    <a onclick="return false;" href="javascript:void(0);" rel="help-tip" title="<?php _e('Your email will also serve as your Komoona username', 'komoona'); ?>">
                                        <img width="12px" height="12px" alt="" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>">
                                    </a>
                                </td>
                            </tr>
                            <?php
                                $curl = Kmn_Func::get_curl_version();
                                if($curl) :
                            ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Password'); ?></th>
                                <td>
                                    <input type="password" id="kmn-password" name="kmn_password" style="width:250px" tabindex="2" value="" autocomplete="off" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $pos_name = self::komoona_plugin_name($type); ?>
                                <th scope="row"><?php echo sprintf('Where on your blog do you plan to display %s?', $pos_name); ?></th>
                                <td>
                                    <div id="kmn-pos-div" style="padding:5px;width:350px;">
                                        <input id="kmn-sidebar" type="checkbox" style="float: left;" value="sidebar" name="kmn_sidebar" />
                                        <label for="kmn_sidebar" style="padding:0 5px;font-weight:bold;"><?php echo sprintf("Drag and drop %s ('Widgets' area)", $pos_name); ?></label>
                                        <br/>
                                        <input id="kmn-other" type="checkbox" style="float: left;" value="sidebar" name="kmn_other" />
                                        <label for="kmn_other" style="padding:0 5px;font-weight:bold;"><?php _e('Edit my theme (advanced users)', 'komoona'); ?></label>
                                        <div id="kmn-sidebar-div" class="kmn-position" style="display: none;">
                                            Please make sure your site's sidebar has sufficient space to accommodate your <?php echo $pos_name; ?> placement
                                        </div>
                                        <div id="kmn-other-div" class="kmn-position" style="display: none;">
                                            After completing this stage please open the 'Advanced Settings' section and copy the 'Komoona Snippet Code' in to your site's template according to where you want the ads to display
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if($type !== Kmn_Func::KOMOONA_ADSENSE) : ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Single Ad Unit Size', 'komoona'); ?></th>
                                <td>
                                <?php $adsize = $post ? $_POST['kmn_adsize'] : '300x250'; ?>
                                    <select id="kmn-adsize" name="kmn_adsize" tabindex="3" style="height:23px;width:250px;">
                                        <?php self::komoona_get_ad_size($type, $adsize); ?>
                                </td>
                            </tr>
                        </table>
                        <?php if($type === Kmn_Func::KOMOONA_ADS): ?>
                        <table class="form-table">
                            <tr>
                                <td>
                                    <div style="width:580px;">
                                        <input id="kmn-cpm" type="checkbox" checked="true" style="float: left;" value="cpm" name="kmn_cpm" />
                                        <label for="kmn_cpm" style="padding:0 5px;"><?php _e('Enable CPM ads', 'komoona'); ?></label>
                                        <a rel="cpm-tip" href="javascript:void(0);" onclick="return false;" title="<?php echo '<strong>Start earning immediately!</strong></br>Your placement will be filled by Komoona with paid ads until you sell directly to your readers. Available for 300x250,728x90 or 160x600 only'; ?>" style="padding-left: 10px;padding-top: 3px;"><img src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>" alt="" width="12px" height="12px" /></a>
                                        <p id="kmn-cpm-notice" style="font-size: 14px;display:none;border:1px solid green;background-color:#ffd;color:black;padding:5px"><?php _e('Pleae note: this size is not eligible for Komoona’s CPM ads, to join the CPM service choose either 300x250, 729x90 or 160x600', 'komoona');?></p>
                                        <p id="kmn-cpm-warning" style="font-size: 12px;font-style: italic;color: black;"><?php _e('* My site does not contain any adult, gambling or illegal content. CPM service is subject to approval by Komoona (might take up to 24 hours).', 'komoona');?></p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <?php endif; ?>
                        <table class="form-table">
                            <?php else : ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Paste Your Entire Adsense Code', 'komoona'); ?></th>
                                <td>
                                    <textarea id="kmn-adsense" name="kmn_adsense" style="float:left;width:500px;height:170px;" tabindex="3" class="code" cols="50" rows="10"></textarea>
                                    <a style="clear:left;float:left;font-size: 70%;" onclick="return false;" href="javascript:void(0);" rel="adsense-tip" title=""><?php _e('Where do I get the AdSense code?', 'komoona'); ?></a>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Single Ad Unit Price', 'komoona'); ?></th>
                                <td>
                                <?php
                                    $price = $post ? $_POST['kmn_price'] : '';
                                    $currency = $post ? $_POST['kmn_currency'] : 'USD';
                                ?>
                                    <input type="text" id="kmn-price" name="kmn_price" style="width:100px;" tabindex="4" autocomplete="off" value="<?php echo $price; ?>" />
                                    <select id="kmn_currency" name="kmn_currency" tabindex="5" style="height:23px;width:140px;margin-left:5px;">
                                        <?php self::komoona_get_currencies($currency); ?>
                                    </select>
                                    <a onclick="return false;" href="javascript:void(0);" rel="help-tip" title="<?php _e('By default, you set the price for 7 day ad. You can later change this and define price for additional periods (i.e. price for 30 day ad) from the control panel.', 'komoona'); ?>">
                                        <img width="12px" height="12px" alt="" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>">
                                    </a>					
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Ad Wizard Language', 'komoona'); ?></th>
                                <td>
                                <?php $language = $post ? $_POST['kmn_language'] : 'USD'; ?>
                                <select id="kmn-language" name="kmn_language" tabindex="5" style="height:23px;width:250px;">
                                    <?php echo self::komoona_get_languages($language); ?>
                                </select>
                                <a onclick="return false;" href="javascript:void(0);" rel="help-tip" title="<?php _e('You can choose which language the Komoona Ad Wizard will be displayed in.', 'komoona'); ?>">
                                    <img width="12px" height="12px" alt="" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>">
                                </a>
                            </td>
                        </tr>
                    </table>
                    <?php
                    if($post) {
                        $billing = $_POST['kmn_billing'] === 'komoona' ? 'komoona' : 'self';
                    }
                    else {
                        $billing = 'komoona';
                    }
                    ?>
                    <div id="kmn-billing-div" style="margin-top:10px;padding-top:10px;<?php echo $billing !== 'komoona' ? 'display:none;' : ''; ?>">
                        <input id="kmn-billing" name="kmn_billing" type="checkbox" style="float: left;" <?php echo $billing === 'komoona' ? 'checked="true"' : ''; ?> value="komoona" />
                        <label for="kmn_billing" style="padding:0 5px;"><?php _e('Use Komoona Billing via PayPal'); ?></label>
                        <div class="free-mium">
                            <ul>
                                <li><?php _e('Komoona will bill and invoice your advertisers using PayPal', 'komoona'); ?></li>
                                <li><?php _e('Advertisers can pay by credit card without leaving your site', 'komoona'); ?> <font style="font-size: 10px;"><?php _e('(verified sites only)'); ?></font></li>
                                <li><?php _e('No need for a PayPal Merchant account', 'komoona'); ?></li>
                            </ul>
                            <p style="font-size: 12px;"><?php _e('a standard 9.62% billing charge applies', 'komoona'); ?></p>
                        </div>
                    </div>
                    <div id="kmn-self-div" style="margin-top:10px;padding-top:10px;<?php echo $billing === 'komoona' ? 'display:none;' : ''; ?>">
                        <input id="kmn-self" name="kmn_self" type="checkbox" style="float: left;" <?php echo $billing !== 'komoona' ? 'checked="true"' : ''; ?> value="self" />
                        <label for="kmn_self" style="padding:0 5px;"><?php _e('Use Your Own Paypal Merchant Account For Billing', 'komoona'); ?></label>
                        <div class="free" style="border: 1px solid red;clear:both;width: 700px;">
                            <ul>
                                <li><strong><?php _e('You must have a PayPal Merchant Account (set to \'authorize\')', 'komoona'); ?></strong></li>
                                <li><?php _e('Advertisers will not be able to pay by credit card', 'komoona'); ?></li>
                                <li><?php _e('Advertisers will leave your site and will be re directed to PayPal in order to complete payment', 'komoona'); ?></li>
                                <li><?php _e('You will need to authorize the payment in your PayPal account, and authorize the new ad in your Komoona Control Panel separately', 'komoona'); ?></li>
                            </ul>
                        </div>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Your PayPal <strong>Merchant</strong> Account</th>
                                <td>
                                    <input type="text" id="kmn-paypal" name="kmn_paypal" style="width:250px;" value="<?php echo $post ? $_POST['kmn_paypal'] : ''; ?>" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="width:300px;;margin-top:10px;">
                        <input id="kmn-toc" type="checkbox" style="float: left;" value="toc" name="kmn_toc" />
                        <label for="kmn_toc" style="padding:0 5px;"><?php _e('I have read and agree to the', 'komoona'); ?></label>
                        <a target="_blank" href="https://www.komoona.com/komoona-tos"><?php _e('Terms of Service', 'komoona'); ?></a>
                    </div>
                    <div style="padding-top:5px;">
                        <input id="kmn-submit" type="submit" class="button-primary" value="<?php _e('Create', 'komoona') ?>" />
                        <div class="kmn-error"></div>
                    </div>
                <input type="hidden" name="<?php echo $type; ?>_create" value="Y" />
            </form>
            <!-- animation gif for ajax calls  -->
            <div id="wait-div"  style="position:absolute;left:25%;top:20%;display:none;">
                <img src="<?php echo KOMOONA_PLUGIN_URL . 'resources/ajax-loader.gif'; ?>" alt="" width="22px" height="22px" style="padding-left:90px;" />
                <p style="font-weight:bold;"><?php _e('Please wait while we create your account', 'komoona'); ?></p>
            </div>
        </div> <!-- komoona create account -->
        <?php Kmn_Func::komoona_add_site_form($type, $komoona_installed); ?>        
        <?php else : // komoona is installed but the plug in is not installed    ?>
        <?php if(!$plugin_installed && $komoona_installed) : ?>
            <?php self::komoona_add_placement($type); ?>
        <?php else: // The plugin is installed: show the options page ?>
        <h2><?php echo self::komoona_plugin_name($type) . ' Options'; ?></h2>
            <?php
                $installed = get_option($type . '_installed');
                if($installed == true) {
                    // this code should be executed only once
                    delete_option('komoona_installed');
                    echo '<h3>Your account was created, please follow the following steps:</h3>';
                } 
            ?>
            <div class="kmn-message">
                <ul>
                    <li>
                        <?php 
                            if($type === Kmn_Func::KOMOONA_ADSENSE) {
                                echo sprintf('When adding %s to your sidebar, make sure your sidebar is large enough for the AdSense unit you want to display. If needed, you can create smaller AdSense unit in your AdSense dashboard', self::komoona_plugin_name($type));
                            }
                            else {
                                echo sprintf('When adding %s to your sidebar, make sure your sidebar is large enough for the Komoona Ad unit you want to display. If needed, you can create smaller ad units in your Komoona control panel', self::komoona_plugin_name($type));
                            }
                        ?>
                    </li>
                    <li>
                        Open the <a href="widgets.php">Widget</a> page and <strong>drag and drop '<?php echo self::komoona_plugin_name($type); ?>' widget</strong> from the 'Available Widget' list to your 'Sidebar'.
                    </li>
                    <li>
                        <?php _e("If you want to display the Komoona ads in different section of your site or if you would like to display multiple ad units on your page, you'll need to edit your WordPress theme's HTML source code - please refer to the 'Advances Setting' section below.", 'komoona'); ?>
                    </li>
                    <li>
                        In order to add new placements or make changes to the existing ones, please log in to your <a href="https://www.komoona.com/users/login/" target="_blank"><?php _e('Komoona Control Panel', 'komoona'); ?></a>
                    </li>
                </ul>
                <p style="font-size: 11px;padding-top:10px;">Still having trouble? Contact our <a href="mailto:support@komoona.com">support team</a>, we'll be glad to help you.</p>
            </div>
            <div class="kmn-advanced">
                <h3 class="kmn-advanced-header">Advanced Settings</h3>
                <a style="float:right;top:5px;position:relative;" rel="kmn-expand-div" href="javascript:void(0);">
                    <img title="expand" alt="collapse" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/'; ?>expand.png">
                </a>
                <div id="kmn-advanced-settings" style="display:none;clear:both;">
                    <p><?php _e("Copy the 'Komoona Snippet Code' (below) in to your site's template code according to where you want the ads to display – you can also<br/>read our", 'komoona'); ?> <a target="_blank" href="https://www.komoona.com/support/how-to-implement-komoona">manual installation guide</a></p>
                    <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                        <?php settings_fields('komoona-settings-group'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php _e('Komoona Snippet Code', 'komoona'); ?></th>
                                <td>
                                    <input type="text" style="width:600px;" name="komoona_widget_layout_id" value="<?php echo htmlentities(get_option($type . '_widget_layout_id'), ENT_QUOTES); ?>" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Komoona Script URL', 'komoona'); ?></th>
                                <td>
                                    <input type="text" style="width:600px;" name="komoona_script_url" value="<?php echo get_option($type . '_script_url'); ?>" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Place Script on Footer', 'komoona'); ?>
                                    <br/>
                                    <span style="font-style:italic;font-size:85%;"><?php _e('if un-checked, script will be rendered to page header', 'komoona'); ?></span>
                                </th>								
                                <td>
                                    <input name="komoona_script_footer" type="checkbox" style="float: left;" <?php echo (get_option($type . '_script_footer') ? 'checked="true"' : ''); ?>" value="script_footer" />
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="<?php echo $type; ?>_hidden" value="Y" />
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'komoona') ?>" />
                        </p>
                    </form>
                </div>
                <br style="clear:both;"/>
            </div>
            <?php endif; //add new placement ?>
            <?php endif; // komoona is already installed ?>
            <?php if($type === Kmn_Func::KOMOONA_ADSENSE) : ?>
                <div id="adsense-help-text" style="display:none;width:480px;">
                    <img id="help-text-close" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/'; ?>modal_close.png" width="28px" height="28px" alt="close" style=" cursor:pointer;position: absolute;top:-2%;right: 0" onclick="jQuery('a[rel=adsense-tip]').btOff();" />
                    <div style="font-size: 85%;padding-bottom: 10px;width:470px;">
                        <p style="color: black;"><?php _e('To get your Google AdSense code, sign into your Google AdSense account and create new ad unit or edit an existing one.<br/><br/>The code should look similar to the image below:', 'komoona'); ?></p>
                    </div>
                    <img src="<?php echo KOMOONA_PLUGIN_URL . 'resources/' ?>sense-code.jpg" width="470px" height="240px" alt="adsense code" class="" />
                    <div style="font-size: 70%;">
                        <a href="https://www.google.com/adsense/support" target="_blank"><?php _e('AdSense Help', 'komoona'); ?></a>
                    </div>
                </div>
            <?php endif; // adsense help    ?>
        </div>
        <?php
        } // end of komoona_plugin_options

        static private function komoona_add_site_form($type) {
                ?>
            <div id="kmn-add-site"  style="display:none;">
                <h2><?php _e('Add Komoona Site To Your Account', 'komoona'); ?></h2>
                <h4>
                    <?php _e('Before you can start using the Komoona plugin, you need to define your site. Please provide the following details:', 'komoona'); ?>
                </h4>
                <?php if($type === Kmn_Func::KOMOONA_CPM): ?>
                    <p style="width:720px;border:1px solid green;background-color:#ffd;color:black;padding:3px;"><?php _e('CPM service is subject to approval by Komoona (up to 24 hours). Your site cannot not contain any adult, gambling or illegal content.', 'komoona');?></p>
                <?php endif; ?>
				<a style="font-size: 100%;" onclick="return false;" href="javascript:void(0);" rel="kmn-create-account"><?php _e('Don\'t Have Account on Komoona?'); ?></a>
                <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" id="add-kmn-site">
                    <?php settings_fields('komoona-registration-group'); ?>
                    <?php $post = $_SERVER['REQUEST_METHOD'] === 'POST'; ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Komoona Username'); ?></th>
                            <?php
                            if($post) {
                                $username = $_POST['kmn_username'];
                            }
                            else {
                                $current_user = wp_get_current_user();
                                $username = $current_user->user_email;
                            }
                            ?>
                            <td>
                                <input type="text" id="kmn-a-username" name="kmn_a_username" style="width:250px;" tabindex="1" value="<?php echo $username; ?>" />
                                <a onclick="return false;" href="javascript:void(0);" rel="help-tip" title="<?php _e("Insert your existing Komoona username and password", 'komoona'); ?>">
                                    <img width="12px" height="12px" alt="" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>">
                                </a>
                            </td>
                        </tr>
                        <?php
                        $curl = Kmn_Func::get_curl_version();
                        if($curl) :
                            ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Password', 'komoona'); ?></th>
                                <td>
                                    <input type="password" id="kmn-a-password" name="kmn_a_password" style="width:250px" tabindex="2" value="" autocomplete="off" />
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr valign="top">
                            <?php $pos_name = self::komoona_plugin_name($type); ?>
                            <th scope="row"><?php echo sprintf('Where on your blog do you plan to display %s?', $pos_name); ?></th>
                            <td>
                                <div id="kmn-a-pos-div" style="padding:5px;width:350px;">
                                    <input id="kmn-a-sidebar" type="checkbox" style="float: left;" value="sidebar" name="kmn_sidebar" />
                                    <label for="kmn_sidebar" style="padding:0 5px;font-weight:bold;"><?php echo sprintf('Drag %s widget to the sidebar', $pos_name); ?></label>
                                    <br/>
                                    <input id="kmn-a-other" type="checkbox" style="float: left;" value="sidebar" name="kmn_other" />
                                    <label for="kmn_other" style="padding:0 5px;font-weight:bold;"><?php _e('Edit my theme (advanced users)'); ?></label>
                                    <div id="kmn-a-sidebar-div" class="kmn-position" style="display: none;">
                                        Please make sure your site's sidebar has sufficient space to accommodate your <?php echo $pos_name; ?> placement
                                    </div>
                                    <div id="kmn-a-other-div" class="kmn-position" style="display: none;">
                                        <?php _e("After completing this stage please open the 'Advanced Settings' section and copy the 'Komoona Snippet Code' in to your site's template according to where you want the ads to display", 'komoona'); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php if($type !== Kmn_Func::KOMOONA_ADSENSE) : ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Single Ad Unit Size', 'komoona'); ?></th>
                                <td>
                                    <?php $adsize = $post ? $_POST['kmn_adsize'] : '300x250'; ?>
                                    <select id="kmn-a-adsize" name="kmn_a_adsize" tabindex="3" style="height:23px;width:250px;">
                                        <?php self::komoona_get_ad_size($type, $adsize); ?>
                                    </select>
                                </td>
                            </tr>
                    </table>
                    <?php if($type === Kmn_Func::KOMOONA_ADS): ?>
                    <table class="form-table">
                        <tr>
                            <td>
                                <div style="width:580px;">
                                    <input id="kmn-a-cpm" type="checkbox" checked="true" style="float: left;" value="cpm" name="kmn_a_cpm" />
                                    <label for="kmn_a_cpm" style="padding:0 5px;"><?php _e('Enable CPM ads', 'komoona'); ?></label>
                                    <a rel="cpm-tip" href="javascript:void(0);" onclick="return false;" title="<?php echo '<strong>Start earning immediately!</strong></br>Your placement will be filled by Komoona with paid ads until you sell directly to your readers. Available for 300x250,728x90 or 160x600 only'; ?>" style="padding-left: 10px;padding-top: 3px;"><img src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>" alt="" width="12px" height="12px" /></a>
                                    <p id="kmn-a-cpm-notice" style="font-size: 14px;display:none;border: 1px solid green;background-color:#ffd;color:black;padding:5px"><?php _e('Pleae note: this size is not eligible for Komoona’s CPM ads, to join the CPM service choose either 300x250, 729x90 or 160x600', 'komoona');?></p>
                                    <p id="kmn-a-cpm-warning" style="font-size: 12px;font-style: italic;color: black;"><?php _e('* My site does not contain any adult, gambling or illegal content. CPM service is subject to approval by Komoona (usually within 48 hours).', 'komoona');?></p>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <?php endif; ?>
                    <table class="form-table">
                        <?php else : ?>
                            <tr valign="top">
                                <th scope="row"><?php _e('Paste Your Entire Adsense Code', 'komoona'); ?></th>
                                <td>
                                    <textarea id="kmn-a-adsense" name="kmn_a_adsense" style="float:left;width:500px;height:170px;" tabindex="3" class="code" cols="50" rows="10"></textarea>
                                    <a style="clear:left;float:left;font-size: 70%;" onclick="return false;" href="javascript:void(0);" rel="adsense-tip" title=""><?php _e('Where do I get the AdSense code?'); ?></a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr valign="top">
                            <th scope="row"><?php _e('Single Ad Unit Price', 'komoona'); ?></th>
                            <td>
                                <?php
                                $price = $post ? $_POST['kmn_price'] : '';
                                $currency = $post ? $_POST['kmn_currency'] : 'USD';
                                ?>
                                <input type="text" id="kmn-a-price" name="kmn_a_price" style="width:100px;" tabindex="4" autocomplete="off" value="<?php echo $price; ?>" />
                                <select id="kmn_currency" name="kmn_currency" tabindex="5" style="height:23px;width:140px;margin-left:5px;">
                                    <?php self::komoona_get_currencies($currency); ?>
                                </select>
                                <a onclick="return false;" href="javascript:void(0);" rel="help-tip" title="By default, you set the price for 7 day ad. You can later change this and define price for additional periods (i.e. price for 30 day ad) from the control panel.">
                                    <img width="12px" height="12px" alt="" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>">
                                </a>					
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Ad Wizard Language', 'komoona'); ?></th>
                            <td>
                                <?php $language = $post ? $_POST['kmn_language'] : 'USD'; ?>
                                <select id="kmn-a-language" name="kmn_a_language" tabindex="5" style="height:23px;width:250px;">
                                    <?php echo self::komoona_get_languages($language); ?>
                                </select>
                                <a onclick="return false;" href="javascript:void(0);" rel="help-tip" title="You can choose which language the Komoona Ad Wizard will be displayed in.">
                                    <img width="12px" height="12px" alt="" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/question.jpg'; ?>">
                                </a>
                            </td>
                        </tr>
                    </table>
                    <p style="padding-top:5px;">
                        <input id="kmn-a-submit" type="submit" class="button-primary" value="<?php _e('Create Site', 'komoona') ?>" />
                    <div class="kmn-error"></div>
                    </p>
                    <input type="hidden" name="<?php echo $type; ?>_add_site" value="Y" />
                </form>
                <!-- animation gif for ajax calls  -->
                <div id="wait-div-a"  style="position:absolute;left:25%;top:20%;display:none;">
                    <img src="<?php echo KOMOONA_PLUGIN_URL . 'resources/ajax-loader.gif'; ?>" alt="" width="22px" height="22px" style="padding-left:90px;" />
                    <p style="font-weight:bold;"><?php _e('Please wait while we validate your account', 'komoona'); ?></p>
                </div>
            </div> <!-- kmn-add-site -->
            <?php
        } // end of komoona_add_site function
        
        static private function komoona_add_placement($type) {
            ?>
            <div id="kmn-add-placement">
                <h2><?php echo sprintf('%s Placement', self::komoona_plugin_name($type)); ?></h2>
                <div class="kmn-message">
                    <p style="font-size:110%;">
                        <?php _e('It seems that Komoona is already installed in your site. To add new placement please log in to Komoona control panel, create new ad placement and follow the installation instructions', 'komoona'); ?>
                    </p>
                    <p><a href="https://www.komoona.com/ads" target="_blank">Komoona Control Panel</a></p>
                </div> 
            </div> <!-- kmn-add-placement -->
            <div class="kmn-advanced">
                <h3 class="kmn-advanced-header">Advanced Settings</h3>
                <a style="float:right;top:5px;position:relative;" rel="kmn-expand-div" href="javascript:void(0);">
                    <img title="expand" alt="collapse" src="<?php echo KOMOONA_PLUGIN_URL . 'resources/'; ?>expand.png">
                </a>
                <div id="kmn-advanced-settings" style="display:none;clear:both;">
                    <p><?php _e("Copy the 'Komoona Snippet Code' (below) in to your site's template code according to where you want the ads to display – you can also<br/>read our"); ?> <a target="_blank" href="https://www.komoona.com/support/how-to-implement-komoona">manual installation guide</a></p>
                    <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                        <?php settings_fields('komoona-settings-group'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php _e('Komoona Snippet Code', 'komoona'); ?></th>
                                <td>
                                    <input type="text" style="width:600px;" name="komoona_widget_layout_id" value="<?php echo htmlentities(get_option($type . '_widget_layout_id'), ENT_QUOTES); ?>" />
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="<?php echo $type; ?>_placement" value="Y" />
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'komoona') ?>" />
                        </p>
                    </form>
                </div>
                <br style="clear:both;"/>
            </div>
        <?php
        } // end of komoona_add_placement function
        
    } // end of Kmn_Func class

endif; // Kmn_Func exists
// kmn func class
?>