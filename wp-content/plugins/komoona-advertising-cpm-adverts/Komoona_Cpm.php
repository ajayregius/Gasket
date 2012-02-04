<?php
/*
  Plugin Name: Komoona Advertising - CPM Adverts
  Plugin URI: https://www.komoona.com/users/registration/platform/wordpress
  Description: <strong>To finish the installation</strong> click 'Widgets' under the 'Appearance' menu in the dashboard and <strong>Drag and drop the 'Komoona CPM Ads' widget</strong> from the 'Available Widget' area to the 'Widget' area.
  Tags: ad, ads, advert, adverts, banner, banners, advertise, advertising, wordpress advertising, blog advertising, site advertising, display advertising, CPM advertising, adverts, make money, earn money, money, monetize, monetization
  Version: 2.0
  Author: The Komoona Team
  Author URI: http://www.komoona.com
 */

/*
  Copyright 2011 Komoona (email : Support@Komoona.com)

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('KOMOONA_SERVER_URL', 'https://www.komoona.com/api/');
define('KOMOONA_PLUGIN_URL', plugin_dir_url(__FILE__));

// komoona functions
include_once dirname(__FILE__) . '/Komoona_Func.php';

// get plug in type (ads\adsense)
$plugin_type = Kmn_Func::komoona_plugin_type(basename(__FILE__));

// define plugin version
define(strtoupper($plugin_type) . '_VERSION', '1.2');

// Update Komoona settings: post from the settings page
if($_POST[$plugin_type . '_hidden'] === 'Y') {

    // widget ad unit snippet
    if (get_magic_quotes_gpc()) {
        $widget_code = stripslashes($_POST['komoona_widget_layout_id']);
        $script_code = stripslashes($_POST['komoona_script_url']);
        $script_footer = stripslashes($_POST['komoona_script_footer']);
    }
    else {
        $widget_code = $_POST['komoona_widget_layout_id'];
        $script_code = $_POST['komoona_script_url'];
        $script_footer = $_POST['komoona_script_footer'];
    }
	
    $script_footer = ($script_footer === 'script_footer' ? TRUE : FALSE);
   
    // update options
    update_option($plugin_type . '_widget_layout_id', $widget_code);
    update_option($plugin_type . '_script_url', $script_code);
    update_option($plugin_type . '_script_footer', $script_footer);
}

// Update Komoona snippet code: post from the settings page
if($_POST[$plugin_type . '_placement'] === 'Y') {

    // widget ad unit snippet
    $widget_code = $_POST['komoona_widget_layout_id'];
    update_option($plugin_type . '_widget_layout_id', $widget_code);
    
    // also the plugin script url based on the already installed plugin script url
    if(method_exists("Kmn_Func", "komoona_installed_plugins")) {
        $komoona_plugins = Kmn_Func::komoona_installed_plugins();
        if(isset ($komoona_plugins) && count($komoona_plugins)) {
            foreach($komoona_plugins as $komoona_plugin) {
                if($komoona_plugin !== $plugin_type) {
                    $script_url = get_option($komoona_plugin . '_script_url');
                    update_option($plugin_type . '_script_url', $script_url);
                }
            }
        }
    } 
    else {
        // also update second plugin settings (script url)
        $op_type = ($plugin_type === Kmn_Func::KOMOONA_ADS) ? Kmn_Func::KOMOONA_ADSENSE : Kmn_Func::KOMOONA_ADS;
        $script_url = get_option($op_type . '_script_url');
        update_option($plugin_type . '_script_url', $script_url);
    }
}

// Create new Komoona account
if($_POST[$plugin_type . '_create'] === 'Y') {

    // komoona rest client
    include_once dirname(__FILE__) . '/Komoona_Rest.php';

    $magic_quotes = get_magic_quotes_gpc();
    
    // new account parameters
    if ($magic_quotes) {
        $params = array (
            'username' => stripslashes($_POST['kmn_username']), 
            'toc'      => stripslashes($_POST['kmn_toc']),
            'siteurl'  => site_url(),
            'price'    => stripslashes($_POST['kmn_price']),
            'currency' => stripslashes($_POST['kmn_currency']),
            'language' => stripslashes($_POST['kmn_language']),
            'type'     => $plugin_type, 
            'version'  => constant(strtoupper($plugin_type) . '_VERSION')
        );
    }
    else {
        $params = array (
            'username' => $_POST['kmn_username'], 
            'toc'      => $_POST['kmn_toc'],
            'siteurl'  => site_url(),
            'price'    => $_POST['kmn_price'],
            'currency' => $_POST['kmn_currency'],
            'language' => $_POST['kmn_language'],
            'type'     => $plugin_type, 
            'version'  => constant(strtoupper($plugin_type) . '_VERSION')
        );
    }

    switch($plugin_type) {
        case Kmn_Func::KOMOONA_ADSENSE:
            $params['adsense'] = $magic_quotes ? stripslashes($_POST['kmn_adsense']) : $_POST['kmn_adsense'];
            break;
        case Kmn_Func::KOMOONA_ADS:
            $params['adsize'] = $magic_quotes ? stripslashes($_POST['kmn_adsize']) : $_POST['kmn_adsize'];
            $params['cpm'] = $magic_quotes ? stripslashes($_POST['kmn_cpm']) : $_POST['kmn_cpm'];
            break;
        default:
            $params['adsize'] = $magic_quotes ? stripslashes($_POST['kmn_adsize']) : $_POST['kmn_adsize'];
            $params['cpm'] = 'cpm';
            break;
    }

    // paypal account
    if(isset($_POST['kmn_self'])) {
        $params['paypal'] = $magic_quotes ? stripslashes($_POST['kmn_paypal']) : $_POST['kmn_paypal'];
    }

    // call Komoona server - if curl supported, use ssl. else standard HTTP call
    $curl = Kmn_Func::get_curl_version();
    if(isset($curl)) {

        // server rest API destination
        $komoona_srv = parse_url(KOMOONA_SERVER_URL);

        // new account password
        $params['password'] = $magic_quotes ? stripslashes($_POST['kmn_password']) : $_POST['kmn_password'];

        try {
            if(isset($komoona_srv['port'])) {
                $port = $komoona_srv['port'];
            }
            else {
                $port = $komoona_srv['scheme'] === 'https' ? 443 : 80;
            }

            $rest = Kmn_Rest::connect($komoona_srv['host'], $port, $komoona_srv['scheme'] === 'https' ? Kmn_Rest::HTTPS : Kmn_Rest::HTTP);

            $method = $komoona_srv['path'] . 'swp_signup';
            $pos = strpos($method, '/');
            if($pos === 0) {
                $method = substr_replace($method, '', $pos, 1);
            }

            // get result from Komoona server
            $result = $rest->post($method, $params);

            // validate results
            $r = json_decode($result);
            if($r->status === 'success') {
                $script_code = $r->script_url;
                $widget_code = $r->snippet;

                // update the plugin options
                update_option($plugin_type . '_widget_layout_id', $widget_code);
                update_option($plugin_type . '_script_url', $script_code);
                update_option($plugin_type . '_username', $params['username']);

                // indicate that plugin was installed now
                add_option($plugin_type . '_installed', true);
            }

            // echo result to page
            echo $result;
        }
        catch(Kmn_Rest_Exception $e) {
            // show error 
            echo json_encode(array ('error' => $e->__toString()));
        }

        die(); // this is required to return a proper result from the ajax call
    }
    else {

        try {
            // direct call to server (random password will be set on server)
            $server_url = str_replace('https://', 'http://', KOMOONA_SERVER_URL);
            $result = Kmn_Rest::http_post($server_url . 'wp_signup', $params);

            // validate results
            $r = json_decode($result);
            if($r->status === 'success') {
                $script_code = $r->script_url;
                $widget_code = $r->snippet;

                // update the plugin options
                update_option($plugin_type . '_widget_layout_id', $widget_code);
                update_option($plugin_type . '_script_url', $script_code);
                update_option($plugin_type . '_username', $params['username']);

                // indicate that plugin was installed now
                add_option($plugin_type . '_installed', true);
            }

            // echo result to page
            echo $result;
        }
        catch(Kmn_Rest_Exception $e) {
            // show error 
            echo json_encode(array ('error' => $e->__toString()));
        }

        die(); // this is required to return a proper result from the ajax call
    }
}

// Create new Komoona site
if($_POST[$plugin_type . '_add_site'] === 'Y') {

    // komoona rest client
    include_once dirname(__FILE__) . '/Komoona_Rest.php';

    $magic_quotes = get_magic_quotes_gpc();
    
    // new account parameters
    if($magic_quotes) {
        $params = array (
            'username' => stripslashes($_POST['kmn_a_username']), 
            'siteurl'  => site_url(), 
            'price'    => stripslashes($_POST['kmn_a_price']), 
            'currency' => stripslashes($_POST['kmn_a_currency']), 
            'language' => stripslashes($_POST['kmn_a_language']), 
            'type'     => $plugin_type,
            'version'  => constant(strtoupper($plugin_type) . '_VERSION')
        );
    }
    else {
        $params = array (
            'username' => $_POST['kmn_a_username'], 
            'siteurl'  => site_url(), 
            'price'    => $_POST['kmn_a_price'], 
            'currency' => $_POST['kmn_a_currency'], 
            'language' => $_POST['kmn_a_language'], 
            'type'     => $plugin_type,
            'version'  => constant(strtoupper($plugin_type) . '_VERSION')
        );
    }

    switch($plugin_type) {
        case Kmn_Func::KOMOONA_ADSENSE:
            $params['adsense'] = $magic_quotes ? stripslashes($_POST['kmn_a_adsense']) : $_POST['kmn_a_adsense'];
            break;
        case Kmn_Func::KOMOONA_ADS:
            $params['adsize'] = $magic_quotes ? stripslashes($_POST['kmn_a_adsize']) : $_POST['kmn_a_adsize'];
            $params['cpm'] = $magic_quotes ? stripslashes($_POST['kmn_cpm']) : $_POST['kmn_a_cpm'];
            break;
        default:
            $params['adsize'] = $magic_quotes ? stripslashes($_POST['kmn_a_adsize']) : $_POST['kmn_a_adsize'];
            $params['cpm'] = 'cpm';
            break;
    }

    // call Komoona server - if curl supported, use ssl. else standard HTTP call
    $curl = Kmn_Func::get_curl_version();
    if(isset($curl)) {

        // server rest API destination
        $komoona_srv = parse_url(KOMOONA_SERVER_URL);

        // new account password
        $params['password'] = $magic_quotes ? stripslashes($_POST['kmn_a_password']) : $_POST['kmn_a_password'];

        try {
            if(isset($komoona_srv['port'])) {
                $port = $komoona_srv['port'];
            }
            else {
                $port = $komoona_srv['scheme'] === 'https' ? 443 : 80;
            }

            $rest = Kmn_Rest::connect($komoona_srv['host'], $port, $komoona_srv['scheme'] === 'https' ? Kmn_Rest::HTTPS : Kmn_Rest::HTTP);

            $method = $komoona_srv['path'] . 'swp_add_site';
            $pos = strpos($method, '/');
            if($pos === 0) {
                $method = substr_replace($method, '', $pos, 1);
            }

            // get result from Komoona server
            $result = $rest->post($method, $params);

            // validate results
            $r = json_decode($result);
            if($r->status === 'success') {
                $script_code = $r->script_url;
                $widget_code = $r->snippet;

                // update the plugin options
                update_option($plugin_type . '_widget_layout_id', $widget_code);
                update_option($plugin_type . '_script_url', $script_code);
                update_option($plugin_type . '_username', $params['username']);

                // indicate that plugin was installed now
                add_option($plugin_type . '_installed', true);
            }

            // echo result to page
            echo $result;
        }
        catch(Kmn_Rest_Exception $e) {
            // show error 
            echo json_encode(array ('error' => $e->__toString()));
        }

        die(); // this is required to return a proper result from the ajax call
    }
    else {

        echo json_encode(array ('error' => 'Your server is missing cURL extension. This extension must be install before you can access the Komoona server.'));
        die(); // this is required to return a proper result from the ajax call
    }
} // end of create new site post request

// Render plugin script
if(!is_admin()) {
    add_action('wp_print_scripts', 'Kmn_Func::' . $plugin_type . '_enqueue_script');
}

// Manage plug in from admin panel
if(is_admin()) {

    // create plugin configuration menu
    add_action('admin_menu', 'Kmn_Func::' . $plugin_type . '_plugin_init');

    $plugin = plugin_basename(__FILE__);

    // add a “Settings” link directly on the plugins page
    add_filter("plugin_action_links_$plugin", 'Kmn_Func::' . $plugin_type . '_settings_link');

    // 'install' section: register default options
    register_activation_hook(__FILE__, 'Kmn_Func::' . $plugin_type . '_activate');

    // 'un-install' section: remove plugin options from the data store
    register_uninstall_hook(__FILE__, 'Kmn_Func::' . $plugin_type . '_uninstall');
} // admin

// Komoona widget
include_once dirname(__FILE__) . '/Komoona_Widget.php';
add_action('widgets_init', 'Kmn_Func::' . $plugin_type . '_register_widget');

//end of file ?>