<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://app.surveyfunnel.com
 * @since      2.0.0
 *
 * @package    Survey_Funnel
 * @subpackage Survey_Funnel/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.0
 * @package    Survey_Funnel
 * @subpackage Survey_Funnel/includes
 * @author     WPEka Club <support@wpeka.com>
 */
class Survey_Funnel {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'survey-funnel';
		$this->version = '2.0.0';

		define( 'SFP_REMOTE_URL' , 'http://app.surveyfunnel.com' );
		//define( 'SFP_REMOTE_URL' , 'http://localhost:8080/survey-app' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {

			$this->define_admin_hooks();
			$this->define_public_hooks();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		add_action('admin_menu', array($this,'survey_funnel_admin_menu'));
		add_action('init', array($this,'survey_funnel_save_credentials'));
		add_action('admin_enqueue_scripts', array($this,'survey_funnel_admin_enqueue'));

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		add_action('wp_footer', array($this,'survey_funnel_public_script'));

	}

	public function survey_funnel_admin_menu(){

			add_menu_page( 'Survey Funnel', 'Survey Funnel', 'manage_options', 'sf-app', array($this, 'survey_funnel_dashboard') , plugins_url('../admin/images/survey.png', __FILE__) );

	}

	public function survey_funnel_save_credentials(){

		if( isset($_POST['sf-activate-client-credentials'])){

				$client_email = $_POST['sf-client-email'];
				$client_pass = $_POST['sf-client-pass'];

				$servey_url = SFP_REMOTE_URL."/wp-json/sf/v2/client-validate";

				/*	$args = array('body' => array('sf_client_email' => $client_email, 'sf_client_pass' => $client_pass ) );
					$response = wp_remote_post( SFP_REMOTE_URL.'/client-validation-page' , $args ); */

			/*	$args = array('body' => array('sf_client_email' => $client_email,
																			'sf_client_pass' => $client_pass ) );
					$response = wp_remote_get( $servey_url,$args ); */

					$args = array(
												'request' => 'validate-client',
												'plugin_name' => $this->plugin_name,
												'version' => $this->version,
												'sf_client_email' => $client_email,
												'sf_client_pass' => $client_pass,
												'url' => esc_url( home_url( '/' ) )
											);

				$response = wp_remote_post( $servey_url , array(
																		'timeout' => 45,
																		'redirection' => 5,
																		'httpversion' => '1.0',
																		'headers' => array( 'user-agent' => 'surveyfunnel/' . $this->version ),
																		'body' => $args,
																		'sslverify' => false
																		) );

				$response = wp_remote_retrieve_body( $response );

				if( !is_wp_error($response) ){

					//$result = json_decode( $response['body'] );
					$result = json_decode($response);

					if( !empty($result->status) && $result->status == 1 ){
						update_option( 'sf_client_credentials', array('sf_client_email' => $client_email, 'sf_client_pass' => $client_pass ) );
					}
					else{
						echo '<script> alert("Entered credentials is not valid. Please try again"); </script>';
					}

				}
				else
				{
					echo '<script> alert("Network issue. Please check your internet."); </script>';
				}

		}

			if( isset($_POST['sf-deactivate_client_credentials'])){

					delete_option( 'sf_client_credentials' );

			}

}

public function survey_funnel_dashboard() {

$key = get_option('sf_client_credentials');

	if( empty($key) ){
	?>

	<h1 class="sf-head">Survey Funnel </h1>

	<div class="sf-login-form" aligin="center">

			<div class="sf-login-header">
				Survey Funnel - Configuration
			</div>
			<div class="sf-login-body">

				<div class="sf-login-user">
				 Log in with your SurveyFunnel account:
				</div>

				<form id="sf-login-form" action="" method="post">

						<div class="sf-login-control">
							<div class="sf-login-label">
								SurveyFunnel Email ID
							</div>
							<div class="sf-login-field">
								<input type="email" class="sf-login-input-control" name="sf-client-email" id="sf-client-email">
							</div>
						</div>

						<div class="sf-login-control">
							<div class="sf-login-label">
								SurveyFunnel Password
							</div>
							<div class="sf-login-field">
								<input type="password" class="sf-login-input-control" name="sf-client-pass" id="sf-client-pass">
							</div>
						</div>

						<div class="sf-login-footer" align="center">
							<input type="submit" class="sf-login-submit" name="sf-activate-client-credentials" id="sf-activate-client-credentials" value="Login">
							<span class="sf-span-data"> Don't have a SurveyFunnel account? <a target="_blank" href="<?php echo SFP_REMOTE_URL."/pricing/" ?>">Sign-up now.</a> </span>
						</div>

				</form>
			</div>

	</div>



	<?php
	}

	else{
		?>

		<div class="sf-login-form" aligin="center">

				<div class="sf-login-header">
					Survey Funnel Dashboard
				</div>
				<div class="sf-login-body">
					<?php $client_credentials = get_option( 'sf_client_credentials'); ?>
					 <div class="sf-login-user">
						 Current Account - <b><?php echo $client_credentials['sf_client_email']; ?></b>
					 </div>

					 <div class="sf-login-user">
						To start using Survey Funnel, launch our dashboard for access to all features, including survey customization!
					 </div>

					 <div class="sf-login-user">
							<a class="sf-login-submit" href="<?php echo SFP_REMOTE_URL.'/manage/'; ?>" target="_blank"> Launch Surveys </a>
							<span>(This will open up a new browser tab.)</span>
					 </div>

					<form id="deactivate_credentials" action="" method="post">

							<div class="sf-login-footer" align="right">
								<input type="submit" class="sf-login-submit" name="sf-deactivate_client_credentials" id="sf-deactivate_client_credentials" value="Deactivate">
							</div>

					</form>
				</div>

		</div>

	<?php
	}

}

public function survey_funnel_admin_enqueue() {

	if(isset($_GET['page']) && $_GET['page'] == 'sf-plus')

		wp_enqueue_script('sf_plus_script' , SFP_REMOTE_URL.'/api/js/dashboard-js.php');
		wp_enqueue_script('sf_plus_client_script' , plugins_url('../admin/js/survey-funnel-admin.js',__FILE__));
		wp_enqueue_style('sf_plus_style' , plugins_url('../admin/css/survey-funnel-admin.css',__FILE__));
}

function survey_funnel_public_script(){

	$apiKey = get_option('sfp_client_api_key');

	if( !empty($apiKey) ){

		$web_url = get_permalink();
		$args = array('body' => array('survey_api_key' => $apiKey,'action' => 'get_survey_key','web_page_url' => $web_url));
		$url = SFP_REMOTE_URL.'/validation-page/';
		$server_response = wp_remote_post($url, $args);

		if( !is_wp_error($server_response) ){

			$result = json_decode($server_response['body']);

			if( !empty($result->survey_key) ){

				update_option('sfp_client_survey_key',$result->survey_key);
			}
			else{
				update_option('sfp_client_survey_key',0);
			}
		}
	}

	$survey_key = get_option('sfp_client_survey_key');

	if( !empty($survey_key) )
		echo '	<div id="survey" style="position: relative;">
				<script src="'.SFP_REMOTE_URL.'/api/js"></script>
				<script>var survey = new Survey("survey" ,"'.$survey_key.'");</script></div>';
}



}
