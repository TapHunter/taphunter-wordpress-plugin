<?php
/*
Plugin Name: taphunter
Plugin URI:
Description: A plug-in to show a location's tap, bottle, spirit, or cocktail list as provided on TapHunter.com
Version: 2.0.0
Author: TapHunter
Author URI: http://www.taphunter.com/
*/
/*  Copyright 2015  Jeff Gordon  (email : flash@taphunter.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('TAPHUNTER_PLUGIN_VERSION', '2.0.0');

function bookwormr_conf() {

?>

<div class="wrap">
<h2><?php _e('TapHunter Configuration'); ?></h2>

</div>
<?php }

function taphunter_get($url) {
    if (function_exists('wp_remote_get')) {
        // Function was added in WP2.7
        $response = wp_remote_get($url, array( 'timeout' => 15 ));
        if(!is_wp_error($response)) {
            return $response['body'];
        } else {
            //echo 'Failed to load taphunter widget via wp_remote_get';
        }
    }
    if (function_exists('curl_init')) {
        $curl_session = curl_init($url);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($curl_session, CURLOPT_TIMEOUT, 8);
        $data = curl_exec($curl_session);
        if ($data === false) {
            //echo 'Failed to load taphunter widget via curl';
        }
        curl_close($curl_session);
    } else {
        $f = fopen($url, 'r');
        if ($f) {
            while (!feof($f)) {
                $data .= fread($f, 4096);
            }
            fclose($f);
        } else {
            //echo 'Failed to load taphunter widget via fopen';
        }
    }

    return $data;
}


function taphunter_widgeturl_v1($settings) {
    // Fix parameters that are uppercase as these are incorrectly
    // Lowercased by the shortcode
    $mapping = array(
        'stylesheet' => 'styleSheet',
        'onlybody' => 'onlyBody',
        'backgroundcolor' => 'backgroundColor',
    );
    foreach($mapping as $key => $value) {
        $settings[$value] = $settings[$key];
    }
    $url = sprintf('https://thetaphunter.appspot.com/widgets/location/id/%s/?%s', $settings['location'], http_build_query($settings));
    return $url;
}


// [taphunter location=1234567]
// [taphunter location=1234567 type="taps(default)|bottles|ondeck|tapsandbottles|allbeer|spirits|cocktails"]
// [taphunter location=1234567 stylesheet="<url>"]
// [taphunter location=1234567 order="name|tapnumber|style|category"]
// [taphunter location=1234567 title="<string>"]
// [taphunter widgeturl="<url>"]            // Deprecated but maintained for backwards compatibility
function taphunter_shortcode($atts) {
    // Shortcode attributes are ALWAYS lowercase
    $settings = shortcode_atts(array(
        'version' => '1',
        'location' => '',
        'type' => '',
        'title' => '',
        'stylesheet' => '',
        'order' => '',

        // Deprecated
        'width' => '',
        'locationname' => '',
        'locationdescription' => '',
        'header' => '',
        'ondeck' => '',
        'orderby' => '',
        'format' => '',
        'style' => '',
        'breweryname' => '',
        'brewerylocation' => '',
        'onlybody' => '',
        'updatedate' => '',
        'backgroundcolor' => '',
        'servingsize' => '',
        'servingprice' => '',
        'servingtype' => '',
        'autoscroll' => '',
        'widgeturl' => '',
    ), $atts);

    if ($settings['location'] !== '') {
        if ($settings['version'] === '1') {
            return taphunter_get(taphunter_widgeturl_v1($settings));
        } else {
            return 'Please upgrade your TapHunter WordPress plugin. The current version is ' . TAPHUNTER_PLUGIN_VERSION;
        }
    } else if ($settings['widgeturl'] !== '') {
        return taphunter_get($settings['widgeturl']);
    } else {
        return 'Unrecognized TapHunter shortcode';
    }
}
add_shortcode('taphunter', 'taphunter_shortcode');

?>