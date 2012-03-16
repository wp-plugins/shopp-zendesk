<?php
/*
Plugin Name: Shopp + Zendesk
Description: Customers who order from your WordPress e-commerce store are added to your Zendesk help desk after checkout.
Version: 1.0.1
Plugin URI: http://optimizemyshopp.com/blog/
Author: Lorenzo Orlando Caum, Enzo12 LLC
Author URI: http://enzo12.com
License: GPLv2
*/
/* (CC BY 3.0) 2011  Lorenzo Orlando Caum  (email : hello@enzo12.com)

	This plugin is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This plugin is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this plugin.  If not, see <http://www.gnu.org/licenses/>. 
*/

require_once('inc/Zendesk.php');

class Shopp_Zendesk {
	public static $account;
	public static $username;
	public static $password;
	public static $organization;

	public function __construct() {
		add_action('shopp_init', array(&$this, 'init'));
		add_action('shopp_order_success', array(&$this, 'add_to_zendesk_list'));
		
		$this->account = get_option("shopp_zendesk_account");
		$this->username = get_option("shopp_zendesk_username");
		$this->password = get_option("shopp_zendesk_password");
		$this->organization = get_option("shopp_zendesk_organization");
	}

	public function init() {
		wp_enqueue_style( 'shopp-zendesk-stylesheet', plugins_url( "css/shopp-zendesk.css", __FILE__ ), array(), '20111128' );
		
		// Actions and filters
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}

	public function admin_menu() {
		global $Shopp;
		$ShoppMenu = $Shopp->Flow->Admin->MainMenu;
		add_submenu_page($ShoppMenu,__('Shopp + Zendesk', 'page title'), __('+ Zendesk','menu title'), defined('SHOPP_USERLEVEL') ? SHOPP_USERLEVEL : 'manage_options', 'shopp-zendesk', array(&$this, 'render_display_settings'));

		add_action( 'admin_print_styles-' . $page, 'admin_styles' );
	}
	
 	public function admin_styles() {
       	wp_enqueue_style( 'shopp-zendesk-stylesheet' );
  	}

	public function add_to_zendesk_list($Purchase) {
		global $wpdb;
		
		$marketing = $wpdb->get_var("SELECT marketing FROM ".$wpdb->prefix."shopp_customer WHERE id = '".$Purchase->customer."'");
		
		$zd = new Zendesk($this->account, $this->username, $this->password);
		
		$firstname = $Purchase->firstname;
		$lastname = $Purchase->lastname;
		$fullname = $firstname.' '.$lastname;
			
		$result = $zd->create(ZENDESK_USERS, array(
    	'details' => array(
        	'email' => $Purchase->email,
       	 	'name' => $fullname,
       	 	'phone' => $Purchase->phone,
       	 	'organization-id' => $this->organization,
       		'roles' => 0,
        	'restriction-id' => 4)
));
			
	}

	public function render_display_settings() {
		wp_nonce_field('shopp-zendesk');	
		if(!empty($_POST['submit'])){
			$this->account = stripslashes($_POST['account']);
			$this->username = stripslashes($_POST['username']);
			$this->password = stripslashes($_POST['password']);
			$this->organization = stripslashes($_POST['organization']);
			
			update_option("shopp_zendesk_account", $this->account);
			update_option("shopp_zendesk_username", $this->username);
			update_option("shopp_zendesk_password", $this->password);
			update_option("shopp_zendesk_organization", $this->organization);
		}
?>
<div class="wrap">
	<h2>Shopp + Zendesk</h2>
	<div class="postbox-container" style="width:65%;">
		<div class="metabox-holder">	

			<div id="shopp-zendesk-introduction" class="postbox">
				<h3 class="hndle"><span>Introduction</span></h3>
				<div class="inside">
					<p>This plugin integrates <a href="http://optimizemyshopp.com/go/shopp/" title="Learn more about Shopp">Shopp</a> with <a href="http://optimizemyshopp.com/go/zendesk/" title="Learn more about Zendesk">Zendesk</a>.</p> 
					<p>After checkout, a customers email, name, and phone number will be added to your Zendesk.</p>
					<p>This process occurs in the background without needing intervention from the user.</p> 
					<p>*If a user has never used your Zendesk, then they will be asked to set a password for their account. <em>This may or may not be applicable as it depends on the settings in your Zendesk.</em></p>
					<strong>Acknowledgements</strong>
					<p>Credit to Adam Sewell who wrote the original code that allowed data to be transferred from Shopp to MailChimp. This code has been adapted for several other services. <a href="http://optimizemyshopp.com/go/adamsewell/" title="Learn about Shopp Toolbox">View Adam's latest project</a></p>
					<p>Credit to Brian Hartvigsen who wrote the a PHP API Wrapper for the Zendesk API. <a href="https://support.zendesk.com/entries/30891-php-api-library" title="Learn about the PHP API Wrapper for Zendesk">Learn more about the Zendesk API</a></p>
				</div>
			</div>

			<div id="shopp-zendesk-general-settings" class="postbox">
				<h3 class="hndle"><span>Set Up Tutorial</span></h3>
				<div class="inside">
				<p>Pro-tip: After starting a video, click on the fullscreen button which appears to the right of the HD logo.</p>
				<p><strong>A Walkthrough of the Integration</strong><br /><br /><iframe src="http://player.vimeo.com/video/32981420?title=0&amp;byline=0&amp;portrait=0" width="600" height="300" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe></p>
				<br />
				<p><strong>How to Find Your Information from Zendesk</strong><br /><br /><iframe src="http://player.vimeo.com/video/32981669?title=0&amp;byline=0&amp;portrait=0" width="600" height="300" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe></p>
				</div>
			</div>
			
			<div id="shopp-zendesk-support-feedback" class="postbox">
				<h3 class="hndle"><span>Support & Feedback</span></h3>
				<div class="inside">
				<p>This is a 3rd-party integration.</p> 
				<p>This plugin is <strong>actively supported</strong>. Support is provided as a courtesy by Lorenzo Orlando Caum, Enzo12 LLC. If you have any questions or concerns, please open a <a href="http://optimizemyshopp.com/support/" title="Open a new support ticket with Optimize My Shopp">new support ticket</a> via our Help Desk.</p>
				<p>You can share feedback via this a <a href="http://optimizemyshopp.com/go/shopp-extension-feedback/" title="Take a super short survey">short survey</a>. Takes less 3 minutes -- we promise!</p>
				<p>Feeling generous? Please consider <a href="http://optimizemyshopp.com/go/donate-shopp-zendesk/">buying me a Redbull</a> or tipping me through the <a href="http://optimizemyshopp.com/go/tip-shopp-help-desk/">Shopp Help Desk</a>.</p>				
				</div>
			</div>
			
			<div id="shopp-zendesk-settings" class="postbox">
				<h3 class="hndle"><span>Zendesk Settings</span></h3>
				<div class="inside">
				<p><strong>Current Limitation</strong></p>
				<p>Zendesk has a feature called "Regular SSL". When enabled it secures your Help Desk via https. If you are using this feature you'll need to make an edit for the Shopp + Zendesk plugin  to work. <a href="http://optimizemyshopp.com/blog/how-to-use-shopp-zendesk-with-zendesks-regular-ssl/ ">Learn how to make this necessary edit</a></p>
					<p><form action="" method="post">
					<table>
						<th>Account URL:</th>
						<td><input type="text" name="account" size="35" value="<?php echo $this->account; ?>" /></td>
					</tr>
					<tr>
						<th>Username [Email]:</th>
						<td><input type="text" name="username"  size="35" value="<?php echo $this->username; ?>" /></td>
					</tr>
					<tr>
						<th>Password:</th>
						<td><input type="password" name="password"  size="35" value="<?php echo $this->password; ?>" /></td>
					</tr>
					<tr>
						<th>Organization (Optional)</th>
						<td><input type="text" name="organization"  size="35" value="<?php echo $this->organization; ?>" /></td>
					</tr>
					</table>
					<input type="submit" class="button-primary" value="Save Settings" name="submit" />
					</form></p>					
				</div>
			</div>
			
			<div id="shopp-zendesk-about-the-author" class="postbox">
				<h3 class="hndle"><span>About the Author</span></h3>
				<div class="inside">
					<table border="0" width="100%">
   					 	<tr>
     						<td width="70%"><div><img style="padding: 5px 10px 0px 0px; float:left" src="http://cdn.lorenzocaum.com/wp-content/uploads/2011/01/lorenzo-orlando-caum.jpg" border="0" alt="Founder of Enzo12 LLC" width="115" height="160"><p><a href="http://twitter.com/lorenzocaum" >@lorenzocaum</a> is an entrepreneur and a marketer. <br><br>Lorenzo is the founder of Enzo12 LLC, a <a href="http://enzo12.com" title="Enzo12 LLC">web engineering firm</a> <br />in Tampa, FL. He is a graduate from the College of Business at the <br />University of South Florida. <br><br>Lorenzo contributes to the <a href="http://optimizemyshopp.com/go/shopp/" title="Learn more about Shopp">Shopp</a> project as a customer support rep.<br><br>He also has a <a href="http://lorenzocaum.com" title="Read Lorenzo's blog">business, marketing, and technology blog</a>.</p></div></td>
    					  <td width="30%"><div id="optin">
						<Big>Get Free Email Updates</Big>
						<form action="http://optimizemyshopp.us2.list-manage1.com/subscribe/post?u=5991854e8288cad7823e23d2e&amp;id=0719c3f096" method="post" target="_blank">
						<p>
						<input type="text" name="EMAIL" class="email" value="Enter your email" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;" />
						<input name="submit" type="submit" value="Get Started Today!" /></p>
						</form>
						</div></td>
   						</tr>
					</table>		
				</div>
			</div>
			
		</div>
	</div>
	
	<div class="postbox-container" style="width:25%;">
		<div class="metabox-holder">	
			
			<div id="shopp-zendesk-donate" class="postbox">
				<h3 class="hndle"><span><strong>Make a Donation!</strong></span></h3>
				<div class="inside">
					<p>Hi friend!</p><p>If this plugin is helpful to you, then please <a href="http://optimizemyshopp.com/go/donate-shopp-zendesk/">buy me a Redbull</a>.</p> 
					<p>You can also tip me through the <a href="http://optimizemyshopp.com/go/tip-shopp-help-desk/">Shopp Help Desk</a>.</p>
					<p>Your kindness is appreciated and will go towards <em>continued development</em> of the Shopp + Zendesk plugin.</p>
				</div>
			</div>

			<div id="shopp-zendesk-subscribe" class="postbox">
				<h3 class="hndle"><span>Free Email Updates</span></h3>
				<div class="inside">
					<p>Get infrequent email updates delivered right to your inbox.</p>
					<p><div id="optin">
					<form action="http://optimizemyshopp.us2.list-manage1.com/subscribe/post?u=5991854e8288cad7823e23d2e&amp;id=0719c3f096" method="post" target="_blank">
					<p>
					<input type="text" name="EMAIL" class="email" value="Enter your email" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;" />
					<input name="submit" type="submit" value="Get Started!" /></p></form></div></p>				
				</div>
			</div>
					
			<div id="shopp-zendesk-have-a-question" class="postbox">
				<h3 class="hndle"><span>Have a Question?</span></h3>
				<div class="inside">
					<p>Open a <a href="http://optimizemyshopp.com/support/" title="Open a new support ticket with Optimize My Shopp">new support ticket</a> for Shopp + Zendesk<br /><br />Learn about <a href="http://optimizemyshopp.com/resources/" title="Learn about extra Shopp resources">additional Shopp resources</a><br /><br />View my post on <a title="How to Get Awesome Support on the Shopp Help Desk" href="http://optimizemyshopp.com/blog/how-to-get-awesome-support-from-the-shopp-help-desk/">getting awesome support</a> through the Shopp Help Desk</p>
				</div>
			</div>
			
			<div id="shopp-zendesk-enjoy-this-plugin" class="postbox">
				<h3 class="hndle"><span>Enjoy this Plugin?</span></h3>
				<div class="inside">
					<p><ol><li><strong>Rate it </strong><a href="http://wordpress.org/extend/plugins/shopp-zendesk/">5 stars on WordPress.org</li></a><li><strong>Spread social joy</strong> ;)<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://optimizemyshopp.com/blog/" data-text="Shopp + Zendesk for my #WordPress #ecommerce store" data-count="none" data-via="enzo12llc" data-related="lorenzocaum:entrepreneur">Tweet</a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script><br /><br /><div id="fb-root"></div>
					<script>(function(d, s, id) {
 					var js, fjs = d.getElementsByTagName(s)[0];
  					if (d.getElementById(id)) {return;}
  					js = d.createElement(s); js.id = id;
  					js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  					fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));</script>

					<div class="fb-like" data-href="http://facebook.com/enzo12llc" data-send="false" data-layout="button_count" data-width="5" data-show-faces="false" data-font="lucida grande">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><br /><br /> <!-- Place this tag where you want the +1 button to render -->
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div class="g-plusone" data-annotation="inline" data-width="120" data-href="http://optimizemyshopp.com/blog/"></div>

					<!-- Place this render call where appropriate -->
					<script type="text/javascript">
  					(function() {
   		 			var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    				po.src = 'https://apis.google.com/js/plusone.js';
    				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  					})();
					</script></li><li><strong>Express your kindness</strong> with a <a href="http://optimizemyshopp.com/go/donate-shopp-zendesk/">donation</a></li></p>		 
				</div>
			</div>

			<div id="shopp-zendesk-news-from-oms" class="postbox">
				<h3 class="hndle"><span>News from Optimize My Shopp</span></h3>
				<div class="inside"><p>Free eBook<br /> <a href="http://optimizemyshopp.com/the-list/" title="Receive your free eBook delivered instantly to your inbox">10 Steps to a More Secure WordPress</a></p>
				<p>White Papers<br /> <a href="http://optimizemyshopp.com/resources/white-papers/" title="Get your free white paper on creating a fast Shopp website">Speeding up your Shopp Ecommerce Website</a><br /><a href="http://optimizemyshopp.com/resources/white-papers/" title="Get your free white paper on using Shopp with caching plugins">Shopp + Caching Plugins</a></p>
				<?php _e('Recent posts from the blog:'); ?>
				<?php // Get RSS Feed(s)
				include_once(ABSPATH . WPINC . '/feed.php');

				// Get a SimplePie feed object from the specified feed source.
				$rss = fetch_feed('http://feeds.feedburner.com/optimizemyshopp');
				if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
    			// Figure out how many total items there are, but limit it to 5. 
    			$maxitems = $rss->get_item_quantity(7); 

    			// Build an array of all the items, starting with element 0 (first element).
    			$rss_items = $rss->get_items(0, $maxitems); 
				endif;
				?>

				<ul>
    			<?php if ($maxitems == 0) echo '<li>No items.</li>';
    			else
    			// Loop through each feed item and display each item as a hyperlink.
    			foreach ( $rss_items as $item ) : ?>
    			<li>
        		<a href='<?php echo esc_url( $item->get_permalink() ); ?>'
        		title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
        		<?php echo esc_html( $item->get_title() ); ?></a>
    			</li>
    			<?php endforeach; ?>
				</ul>
				</div>
			</div>		
			
			<div id="shopp-zendesk-recommendations" class="postbox">
				<h3 class="hndle"><span>Recommended</span></h3>
				<div class="inside">
					<p>...Easy and automatic WordPress backups with BackupBuddy</p>
					<p><a href="http://optimizemyshopp.com/go/backupbuddy/"><img src="http://ithemes.com/graphics/backupbuddy_sidebarad.png" border=0 alt="Backup WordPress Easily" ></a></p>	
				</div>
			</div>

		</div>
		<br><br><br>
	</div>
</div>
<?php	
	}
}

new Shopp_Zendesk();

?>