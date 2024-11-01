<?php
/*
Plugin Name: Survey Funnel Plus
Version: 1.0
Plugin URI: http://www.surveyfunnel.com
Description: Enables you to talk with your customers through Surveys
Author: Survey Funnel
Author URI: http://app.surveyfunnel.com
*/

define( 'SFP_REMOTE_URL' , 'http://app.surveyfunnel.com' );

add_action( 'admin_menu' , 'sfPlus_admin_menu' );

function sfPlus_admin_menu(){
	add_menu_page( 'Survey Funnel Plus', 'Survey Funnel +', 'manage_options', 'sf-plus', 'sf_dashboard' , plugins_url('img/surveyfunnel_icon.png', __FILE__) );
}

/*
*	Gets Survey Key from USER FORM, validates the key from remote server
*	and saves it to options table.
*/

add_action( 'init', 'sfp_save_survey_key' );

function sfp_save_survey_key(){

	if( isset($_GET['page']) && $_GET['page'] == 'sf-plus' ){

		if( isset($_POST['sfp_api_key']) && !empty($_POST['sfp_api_key']) ){

			$args = array('body' => array('sfp_api_key' => sanitize_key($_POST['sfp_api_key']) ) );
			$response = wp_remote_post( SFP_REMOTE_URL.'/validation-page' , $args );

			if( !is_wp_error($response) ){

				$result = json_decode( $response['body'] );

				if( !empty($result->status) ){

					update_option( 'sfp_client_api_key', sanitize_key($_POST['sfp_api_key']) );
				}
				else{
					echo '<script> alert("Entered Key is not valid. Please try again"); </script>';
				}
			}
		}
	}
}

function sf_dashboard() {

$key = get_option('sfp_client_api_key');

	if( empty($key) ){
	?>
		<form id="sfp_client_form" action="" method="post">
			<input type="password" id="sfp_api_field" name="sfp_api_key" placeholder="Your Survey Funnel APP key.."/>
			<br>
			<a class="button sfp_btn" href = "<?php echo SFP_REMOTE_URL."/pricing/" ?>" target="_blank"> Get Your Key !</a>
			<button class="button sfp_validate_btn" style="display:none"> Validate </button>
		</form>
	<?php
	}

	else{
		?>
		<div class="activation-popup">
        	<div class="activation-msg">
            	<p><strong>Get Started ! Just <a class="button sfp_btn" href="<?php echo SFP_REMOTE_URL.'/manage/'; ?>" target="_blank">  Goto your Dashboard  </a> to manage your Surveys on the Survey Funnel Apps Account.</p>
        	</div>
        </div>
		<div id="dashboard-area"></div>
	<?php
	}

}

add_action('admin_enqueue_scripts' , 'sfplus_enqueue_scripts'); 


function sfplus_enqueue_scripts() {

	if(isset($_GET['page']) && $_GET['page'] == 'sf-plus')

		wp_enqueue_script('sf_plus_script' , SFP_REMOTE_URL.'/api/js/dashboard-js.php');
		wp_enqueue_script('sf_plus_client_script' , plugins_url('js/script.js',__FILE__));
		wp_enqueue_style('sf_plus_style' , plugins_url('css/style.css',__FILE__));
}


/*	
*	Request remote server for api key based on current page url
*/

add_action('wp_footer', 'sfp_script');

function sfp_script(){

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
?>