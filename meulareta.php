<?php
/*
Plugin Name: Meu Lareta
Description: A simple Lareta status lister, microblogging.
Author: Fran diéguez based in MyTwitter
Version: 1.6 beta
Plugin URI: http://www.lareta.net/downloads/
Author URI: http://www.mabishu.com
License:  Creative Commons Attribution-Share Alike 3.0 Unported License
Warranties: None
Last Modified: August 22, 2008
*/

/*
This work is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported License. To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/3.0/ or send a letter to Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.

About: MeuLareta allows users to display their recent Twitter status updates (tweets) on their Wordpress site and update their status through the Options page. Includes customization options including number of recent twitters to display, formatting options, and stylesheets.  It can be called as a function or used as a widget.

Credits: This plugin was inspired by Sarah Isaacson's Twitter Wordpress Sidebar Widget (http://www.velvet.id.au/twitter-wordpress-sidebar-widget/).  MeyLareta 1.02 incorporated a code modification written by Sascha to enable MeuLareta usage as a widget in widget-enabled themes (http://www.switchingtomacosx.org/2007/11/15/meulareta-patch/).  Some code was inspired by Alex King's Twitter Tools 1.0 plugin (http://alexking.org/projects/wordpress/readme?project=twitter-tools).

Requirements: SimplePie (http://simplepie.org/) is required in order for this plugin to function properly.  SimplePie version 1.0.1 is included in the distribution file -- this is the version that has been tested.  The libcurl (http://curl.haxx.se/) library for PHP is required in order to process status updates.  Most hosts who provide PHP include support for libcurl by default.

Installation: Extract the contents of the archive. Upload the meulareta folder to your Wordpress plugins folder (e.g. http://yoursitename.com/wp-content/plugins/).  Set your preferences in the Wordpress Options panel for "MeuLareta" (including username, password, and formatting options).  If it doesn't already exist, create a folder named "cache" in the root directory or your webserver (and give it write permission - chmod to 755) or alternatively edit the $cacheloc variable in the plugin to point to a different location (if you do this you may need to reupload the plugin) -- the cache location will be added to the Options panel in a future version.

Stylesheets: Example CSS code is included in example.css.  To incorporate on your site, copy/edit the code to the stylesheet for your current wordpress theme.  For most themes, this can be done by going to Presentation -> Theme Editor and then select "Stylesheet" from the theme files list.  Included in the code generated by this plugin are following:

classes -- meulareta, meulareta_tweet, meulareta_tweet_time, meulareta_separator -- these are always the same
ids -- meulareta_tweet-1, meulareta_tweet_time-1, meulareta_separator-1 -- the number increases sequentially for each tweet displayed.  If you are displaying 5 tweets, they will be numbered from 1 to 5, (meulareta_tweet-1, meulareta_tweet-2, etc.)

Sample Code for inclusion: 
	<?php if (function_exists('meulareta')) { ?>
	<li><? meulareta();?></li>
	<?php } ?>
	
Warranty: This program is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	
CHANGELOG: 
v. 1.6  -- added CSS class to the widget and widget title, renamed the time_since() function to meulareta_time_since()
v. 1.5
	-- updated parsing for "@" replies (courtesy of @krokodilerian - http://vasil.ludost.net/blog/)
	-- added additional formatting options including ability to style w/ CSS and change order of tweet/time
	-- added meulareta_mostrecent() function to display the most recent update (w/o formatting options) in the Options panel for MeuLareta
v. 1.4
	-- added cache life setting, default is set to 900 seconds (15 minutes)
v. 1.3
	-- changed version numbering to 1.x instead of 1.0x to match the numbering used at the Wordpress Plugin Database (http://wp-plugins.net/)
	-- added the ability to post status updates from the Options page for MeuLareta
v. 1.02
	-- incorporated Sascha's modifications to enable use of MeuLareta as a widget in widget-enabled themes
v. 1.01
	-- modified the time display code to correctly function on servers with PHP version 4
	-- updated the documentation
		
v. 1.00
	-- added the Options panel for MeuLareta in Wordpress (in Wordpress 2.5, the MeuLareta panel is located under "Settings")

v. 1.00beta
	-- working beta version
	
*/
require_once('simplepie.inc');

//define("CACHELOC", $_SERVER['DOCUMENT_ROOT'] . "/cache");
define("CACHELOC", dirname(__FILE__) . "/cache");

/* Leave this part alone unless you know what you're doing */
function meulareta() {

/* Grabs the setting from the MeuLareta Options page or uses the default if options aren't available */
	$count = (get_option("meulareta_count") == null)? 1 : get_option("meulareta_count");
	$tuser = (get_option("meulareta_user") == null)? "MyTwitt3r" : stripslashes(get_option("meulareta_user"));
	$title = (get_option("meulareta_title") == null)? "lareta.net/".$tuser : stripslashes(get_option("meulareta_title"));
	
	$cache_life = (get_option("meulareta_cache_life") == null) ? 900 : get_option("meulareta_cache_life");
	
	$orderfirst = (get_option("meulareta_order") == null) ? "putfirst_twitter" : get_option("meulareta_order");
	
	$separator  = (get_option("meulareta_separator") == null)? "" : stripslashes(get_option("meulareta_separator"));
	$beforeall  = (get_option("meulareta_beforeall") == null)? '<ul class="meulareta">' : stripslashes(get_option("meulareta_beforeall"));
	$afterall   = (get_option("meulareta_afterall") == null)? "</ul>" : stripslashes(get_option("meulareta_afterall"));
	$beforeitem = (get_option("meulareta_beforeitem") == null)? '<li class="meulareta">' : stripslashes(get_option("meulareta_beforeitem"));
	$afteritem  = (get_option("meulareta_afteritem") == null)? "</li>" : stripslashes(get_option("meulareta_afteritem"));
	
	// PREPARE THE OUTPUT -- example feed address: http://lareta.net/statuses/user_timeline/MyTwitt3r.rss?count=5
	$twitter_url = 'http://lareta.net/';
	$my_twitter_feed_url = $twitter_url . '/api/statuses/user_timeline/' . $tuser . '.rss?count=' . $count; // gets the full RSS feed address
	$my_twitter = $twitter_url . $tuser; // the twitter address for $tuser

	$feed = new SimplePie();
	$feed->set_feed_url($my_twitter_feed_url);
	$feed->set_cache_location(CACHELOC); // creates the SimplePie feed object
	$feed->set_cache_duration($cache_life);
	$feed->init();
	
	echo "<h2 class=\"widgettitle\"><style type=\"text/css\" media=\"screen\">".file_get_contents(dirname(__FILE__)."/example.css")."</style>\n<a href='" . $my_twitter . "' title='olla os meus chíos'>" . $title . "</a></h2>\n"; //display the title and links to the twitter page
	echo $beforeall . "\n"; // display before all tweets
		
	if ($feed->error())
	{
		?>
		<b>Erro:</b> non se puido acceder ó teu usuario de lareta <i><?php echo $tuser;?></i>.
		<?php
	}
	else {

		
		//Note: this currently functions on the understanding that the feed returned by Twitter contains the correct item count, if fewer items are available it will display as many as available.
		// Twitter seems to have a limit of 20 max items in the feeds being used by this plugin.
		$i=1;
		foreach ($feed->get_items() as $item) { //goes through the SimplePie object for each tweet
			$title = ereg_replace($tuser.': ', '', $item->get_title()); //removes username from text (e.g. strips out "yourusername: " from the tweet
			$title = myTwitterFormatter($title);
			$timesince = meulareta_time_since($item->get_date(U));
			$when  = ($timesince != "December 31st, 1969") ? $timesince : $item->get_date(); // if $timesince ok display it, otherwise display it raw
			
			//figure out the order
			if ($orderfirst == "putfirst_time") {
					echo '  ' . $beforeitem . '<span class="meulareta_tweet_time" id="meulareta_tweet_time-' . $i . '">' . $when . '</span><span class="meulareta_separator" id="meulareta_separator-' . $i . '">' . $separator . '</span><span class="meulareta_tweet" id="meulareta_tweet-' . $i . '">' . $title . '</span>' . $afteritem . "\n"; // displays the tweet
			}
			else {
				echo '  ' . $beforeitem . '<span class="meulareta_tweet" id="meulareta_tweet-' . $i . '">' . $title . '</span><span class="meulareta_separator" id="meulareta_separator-' . $i . '">' . $separator . '</span><span class="meulareta_tweet_time" id="meulareta_tweet_time-' . $i . '">' . $when . '</span>' . $afteritem . "\n"; // displays the tweet
			}
			
			$i++;
		}
	
	}
	
	echo $afterall . "\n"; // displays after all tweets

}

function meulareta_mostrecent() {
	$tuser = (get_option("meulareta_user") == null)? "MyTwitt3r" : stripslashes(get_option("meulareta_user"));
	$cache_life = (get_option("meulareta_cache_life") == null) ? 900 : get_option("meulareta_cache_life");
	
	// PREPARE THE OUTPUT
	$twitter_url = 'http://lareta.net/';
	$my_twitter_feed_url = $twitter_url . '/apistatuses/user_timeline/' . $tuser . '.rss?count=1'; // gets the full RSS feed address
	$my_twitter = $twitter_url . $tuser . ''; // the twitter address for $tuser

	$feed = new SimplePie();
	$feed->set_feed_url($my_twitter_feed_url);
	$feed->set_cache_location(CACHELOC); // creates the SimplePie feed object
	$feed->set_cache_duration(0); // default to one minute
	$feed->init();
	
	if ($feed->error())
	{
		?>
		<b>Error:</b> unable to access Twitter feed for user <i><?php echo $tuser;?></i>.
		<?php
	}
	else {
		$item = $feed->get_item(0);
		$title = ereg_replace($tuser.': ', '', $item->get_title()); //removes username from text (e.g. strips out "yourusername: " from the tweet
		$title = myTwitterFormatter($title);
		$timesince = meulareta_time_since($item->get_date(U));
		$when  = ($timesince != "December 31st, 1969") ? $timesince : $item->get_date(); // if $timesince ok display it, otherwise display it raw
		
		echo $title . " (" . $when . ")";
	}

}

// Widget stuff
function widget_meulareta_register() { 
	function widget_meulareta($args) {
			echo $before_widget; 
			 ?>	<li id="meulareta_widget" class="widget widget_meulareta"><? meulareta();?></li><?php
			 echo $after_widget; 
	}	 
	register_sidebar_widget('MeuLareta', 'widget_meulareta', null, 'meulareta'); 
}

add_action('init', 'widget_meulareta_register');

function myTwitterPoster($username,$password,$status) {
	$url = "http://lareta.net/api/statuses/update.xml";
	
	$session = curl_init();
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_USERPWD, $username . ":" . $password);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_POST, 1);
	curl_setopt($session, CURLOPT_POSTFIELDS,"status=" . $status . "&source=meulareta");
	
	$result = curl_exec($session);
	$resultArray = curl_getinfo($session);
	
	curl_close($session);

	return $resultArray['http_code'];
}

function myTwitterFormatter($tweet) {
	$tweet = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<i><a href=\"\\0\">link</a></i>",$tweet); // turn any URL's into links
	$tweet = ereg_replace("@([a-zA-Z0-9]+)([^a-zA-Z0-9])",'<a href="http://lareta.net/\\1">@\\1</a>\\2',$tweet); // add "@username" links
	return $tweet;
}

/* Used to generate the statement for the ammount of time elapsed since the Twitter item was posted (e.g. 11 hours ago) */
function meulareta_time_since($original) {	//function source: http://snippets.dzone.com/posts/show/3044
	    $chunks = array(			// array of time period chunks
        array(60 * 60 * 24 * 365 , 'ano'),
        array(60 * 60 * 24 * 30 , 'mes'),
        array(60 * 60 * 24 * 7, 'semana'),
        array(60 * 60 * 24 , 'día'),
        array(60 * 60 , 'hora'),
        array(60 , 'minuto'),
    );
    
    $since = time() - $original;
	
	if($since > 604800) {
		$print = date("M jS", $original);
		if($since > 31536000) {$print .= ", " . date("Y", $original);}
		return $print;
	}
    
    // $j saves performing the count function each time around the loop
    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {break;} // finding the biggest chunk
    }
    $print = " fai ".($count == 1) ? '1 '.$name : "$count {$name}s";
		return $print;

}

function meulareta_pages(){
	add_options_page('Opcións de MeuLareta', 'MeuLareta', 8, __FILE__, 'meulareta_options');
}
function meulareta_options(){
	include('meulareta_admin.php');
}
add_action('admin_menu', 'meulareta_pages');

?>
