<?php
/*
Plugin Name: ZD YouTube FLV Player
Plugin URI: http://www.proloy.me/projects/wordpress-plugins/zd-youtube-flv-player/
Description: Display FLV and YouTube Video in you blog in a Custom Player. Usage: [zdvideo width="x" height="x"]url[/zdvideo]. 'width' and 'height' are optional. Default 'width' = 425 and default 'height' = 349.
Author: Proloy Chakroborty
Version: 1.2.6
Author URI: http://www.proloy.me/
*/

 
/*Copyright (c) 2008, Proloy Chakroborty
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of Proloy Chakroborty nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY Proloy Chakroborty ''AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Proloy Chakroborty BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.*/

////////////////////////////////////////////////////////////////////////////////////////////////////
// Filename: zd-youtube-flv-player.php
// Creation date: 27 November 2008
//
// Version history:
//	1.0.0 - 27 November 2008: Initial release
//	1.2.3 - 02 December 2008: Border Show on/off, fullscreen button show on/off and 5 themes
//  1.2.4 - 03 December 2008: Fixed Attributes Case Problem and add PDF documentation
//  1.2.5 - 31 January 2009: Bug fixed and autoplay 0n/off feature added.
//  1.2.6 - 13 April 2009: Bug fixed youtube.
// Usage:
//	[zdvideo]url[/zdvideo]
// Attributes:
//  width - Default is "425". This is the width of the player including border.
//  height - Default is "349". This is the height of the player including border.
//  align - Alignment of the Flash Player. Default is "center". Available Options: "left"/"right"/"center"
//  border - Show/Hide Flash Player Border. Default "yes". Available Options: "yes"/"no"
//  fullscreen - Show/Hide full screen button. Default "yes". Available Options: "yes"/"no"
//  theme - Name of the theme you want to use for the Flash Player. Default "dark".
////////////////////////////////////////////////////////////////////////////////////////////////////

// Entry point from WordPress
add_action('wp_head', 'UpdateHeaderTag');
add_shortcode('zdvideo', 'main');
//--------------------------------------------------------------------------------------------------
// Plugin Variables
define('WP_ZDYTFP_URL', WP_PLUGIN_URL.'/zd-youtube-flv-player');
define('WP_ZDYTFP_PLAYER_URL', WP_ZDYTFP_URL.'/flash');
define('WP_ZDYTFP_YTPROXY_URL', WP_ZDYTFP_URL.'/fl_youTubeProxy.php');
define('WP_ZDYTFP_SWFJS_URL', WP_ZDYTFP_URL.'/js/swfobject.js');
//--------------------------------------------------------------------------------------------------
// Function: main
//    Main entry point for the script. Does most of the work.
//
// Parameters
//    None.
//
// Returns:
//    None. 
//--------------------------------------------------------------------------------------------------
function main($atts, $content=null) {
	// Themes
	$player['dark'] = WP_ZDYTFP_PLAYER_URL.'/zdytflv-player-dark.swf';
	$player['glossy'] = WP_ZDYTFP_PLAYER_URL.'/zdytflv-player-glossy.swf';
	$player['gray'] = WP_ZDYTFP_PLAYER_URL.'/zdytflv-player-gray.swf';
	$player['simple1'] = WP_ZDYTFP_PLAYER_URL.'/zdytflv-player-simple1.swf';
	$player['simple2'] = WP_ZDYTFP_PLAYER_URL.'/zdytflv-player-simple2.swf';
	
	// Make sure we're running an up-to-date version of PHP
	$phpVersion = phpversion();
	$verArray = explode('.', $phpVersion);
	if( (int)$verArray[0] < 5 ) {
		$error = "'ZD YouTube FLV Player' requires PHP version 5 or newer.<br>\n";
		$error .= "Your server is running version $phpVersion<br>\n";
		return $error;
		exit;
	}
	
	// Make sure content is not Null
	if($content == null) {
		$error = "<em>'ZD YouTube FLV Player' requires either a youtube video URL or FLV URL.</em><br>\n";
		$error .= '<em>Usage: [zdvideo width="x" height="x"]url[/zdvideo]</em><br>'."\n";
		return $error;
		exit;
	}
	
	// Make sure content is an uri
	$find_http = "http://";
	$pos_http = strpos($content, $find_http);
	if($pos_http === false) {
		$error = "<em>'ZD YouTube FLV Player' requires either a youtube video URL or FLV URL.</em><br>\n";
		$error .= '<em>Usage: [zdvideo]url[/zdvideo]</em><br>'."\n";
		return $error;
		exit;
	}
	
	// Extract WordPress parameters
	extract(shortcode_atts(array('align' => 'center', 'width' => '425', 'height' => '349', 'autoplay' => 'no', 'border' => 'yes', 'fullscreen' => 'yes', 'theme' => 'dark'), $atts));
	
	$border = formatAtts($border);
	$fullscreen = formatAtts($fullscreen);
	$autoplay = formatAtts($autoplay);
	$align = formatAtts($align);
	$theme = formatAtts($theme);
	
	//Make sure 'align' has correct value
	if($align != 'center' and $align != 'left' and $align != 'right') {
		$error = "<em>'".$align."' is not a valid value for 'align' attribute.</em><br>\n";
		$error .= "<em>Correct Values: left, right and center</em><br>"."\n";
		return $error;
		exit;
	}
	
	$wp_zdytfp_player_id = PlayerId();
	
	$wp_zdytfp_player = '<div id="'.$wp_zdytfp_player_id.'" style="width:100%; height:'.$height.'px; text-align:'.$align.'; margin:auto;">'."\n";
   	$wp_zdytfp_player .= '<div id="v_'.$wp_zdytfp_player_id.'" style="width:100%; height:100%;">ZD YouTube FLV Player</div>'."\n";
	$wp_zdytfp_player .= '</div>'."\n";
	$wp_zdytfp_player .= '<script type="text/javascript">'."\n";
	$wp_zdytfp_player .= 'var flashvars = {'."\n";
	$wp_zdytfp_player .= 'vurl: "'.$content.'",'."\n";
	if($border == "no") {
		$wp_zdytfp_player .= 'bdr: "'.$border.'",'."\n";
	}
	if($fullscreen == "no") {
		$wp_zdytfp_player .= 'fullBtn: "'.$fullscreen.'",'."\n";
	}
	if($autoplay == "yes") {
		$wp_zdytfp_player .= 'autoplay: "'.$autoplay.'",'."\n";
	}
	$wp_zdytfp_player .= 'yturl: "'.WP_ZDYTFP_YTPROXY_URL.'"'."\n";
	$wp_zdytfp_player .= '};'."\n";
	$wp_zdytfp_player .= 'var params = {'."\n";
	$wp_zdytfp_player .= 'wmode: "transparent",'."\n";
	$wp_zdytfp_player .= 'allowFullScreen: "true"'."\n";
	$wp_zdytfp_player .= '};'."\n";
	$wp_zdytfp_player .= 'var attributes = {'."\n";
	$wp_zdytfp_player .= 'id: "my_'.$wp_zdytfp_player_id.'",'."\n";
	$wp_zdytfp_player .= 'name: "my_'.$wp_zdytfp_player_id.'"'."\n";
	$wp_zdytfp_player .= '};'."\n";
	$wp_zdytfp_player .= 'swfobject.embedSWF("'.$player[$theme].'", "v_'.$wp_zdytfp_player_id.'", "'.$width.'", "'.$height.'", "9.0.0", false, flashvars, params, attributes);'."\n";
	$wp_zdytfp_player .= '</script>'."\n";
	return $wp_zdytfp_player;
} // END main()

//--------------------------------------------------------------------------------------------------
// Function: UpdateHeaderTag
//    Includes the jauascript file in header tag.
//
// Parameters
//    None.
//
// Returns:
//    None.
//--------------------------------------------------------------------------------------------------
function UpdateHeaderTag() {
	echo '<script type="text/javascript" src="'.WP_ZDYTFP_SWFJS_URL.'"></script>'."\n";
} // END UpdateHeaderTag()

//--------------------------------------------------------------------------------------------------
// Function: PlayerId()
//    Generates player DOM id.
//
// Parameters
//    None.
//
// Returns:
//    $player_id: The Player Id used for the player in DOM
//--------------------------------------------------------------------------------------------------
function PlayerId() {
	global $wp_query;
	$the_post_id = $wp_query->post->ID;
	$player_id = "wp_zdytfp_container_".$the_post_id;
	return $player_id;
} // END PlayerId()

//--------------------------------------------------------------------------------------------------
// Function: formatAtts
//    Changes attributes to lower case and removes spaces.
//
// Parameters
//    $atts: attribute value
//
// Returns:
//    $atts: formated attribute value
//--------------------------------------------------------------------------------------------------
function formatAtts($atts) {
	$atts = strtolower($atts);
	$atts = str_replace (" ", "", $atts);
	return $atts;
} // END formatAtts()
?>