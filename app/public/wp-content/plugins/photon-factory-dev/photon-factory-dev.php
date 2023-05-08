<?php
/**
 * Plugin Name:       PhotonFactory Dev
 * Plugin URI:        https://photonfactorydev.com
 * Description:       Custom code
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Chris Oberg
 * Author URI:        https://photonfactorydev.com
 * Version:           1.0.0
 * Text Domain:       photon-factory-dev
 * Domain Path:       assets/languages
 *
 */
 
namespace PhotonFactoryDev;

use WeDevs\PM\Task\Controllers\Task_Controller;
use WeDevs\PM\Project\Controllers\Project_Controller;
use WeDevs\PM\Task_List\Transformers\New_Task_List_Transformer;
use WeDevs\PM\Task_List\Controllers\Task_List_Controller;
 
class PhotonFactoryDev {
	 public function __construct(){
		add_action('yith_wapo_before_main_container', [$this, 'init'], 110);
		add_action('ywraq_after_create_order', [$this, 'create_order_from_checkout'], 10, 1);
		add_action( 'wp_login', [$this, 'user_cap_update'], 10, 2 );
		add_action( 'wp_footer', [$this, 'footer'], 10);		 
		add_filter( 'body_class', [$this, 'add_slug_body_class'] );
		add_action('wp_ajax_pfd_create_project', [$this, 'create_project']);
		add_shortcode( 'product_dimensions', [$this, 'display_product_dimensions'] );
		add_action('cpm_admin_menu', [$this, 'admin_menu'], 10, 2);
		add_action( 'admin_enqueue_scripts', [$this, 'jqueryui'] );
		add_action('wp_ajax_pfd_users_list', [$this, 'user_list']);
	 }
	 
	 public function jqueryui()
	 {
		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	 }
	 
	 public function init()
	 {
		?>
		<div id="yith-wapo-container">
			<div id="yith-wapo-block-x" class="yith-wapo-block">
				<div class="yith-wapo-addon yith-wapo-addon-type-text  default-closed" data-min="" data-max="" data-exa="" data-addon-type="text" style="background-color: #ffffff; padding: 0px 0px 0px 0px;">
					<h3 class="wapo-addon-title toggle-closed">Project</h3>
						<div class="options default-closed per-row-1">
							<div id="yith-wapo-option-16-0" class="yith-wapo-option">
								<?php
									$projects = $this->get_projects();
									if($projects):?>
									<div id="project_list">
								<select name="product_list" id="project_list">
									<option value=""> - Select Project - </option>
									<?php
										foreach($projects as $project):?>										
										<option value="<?php echo $project->id?>"><?php echo $project->title?></option>
									<?php endforeach;?>
								</select><label class="btn_new_project"> add new project</label>
									</div>
									<div id="new_project_wrapper" style="display: none">
								</div>
								<?php
									else:?>
									<input type="text" id="yith-wapo-16-4" class="yith-wapo-option-value" name="yith_wapo[][16-4]" value="" data-price="0" data-price-type="fixed" data-price-method="free" data-first-free-enabled="no" data-first-free-options="0" data-addon-id="16" required="" style="width: 100%;" placeholder="">
									<?php
									endif;?>
								
							</div>
						</div>
				</div>
			</div>
		</div>
		<style>
			#yith-wapo-addon-25 {
				display: none;
			}
			#ui-datepicker-div {
			    width: 13em;
			    padding: 4px;
			}
			.ui-widget {
				font-size: .7em;
			}
			.ui-datepicker-calendar td a {
				line-height: 20px;
				font-size: small;
			}
			.woocommerce form select,.woocommerce form .select2 {
				font-size:13.333px!important;
			}
		</style>
		<script>
			jQuery(document).ready(function(){
				
				<?php if(!$projects):?>
						jQuery('#yith-wapo-25-0').val('New').trigger('change');
				<?php else:?>
						jQuery('#yith-wapo-option-19-0').after('<div id="yith-wapo-option-19-0" class="yith-wapo-option btn_cancel_project"><label>cancel</label><label>');
				<?php endif?>
				jQuery('.btn_new_project').click(function(){
					jQuery('#yith-wapo-25-0').val('New').trigger('change');
					jQuery('#project_list').hide();
					jQuery('#new_project_wrapper').show();
					jQuery('#new_project').val('');
					jQuery('#project_list').find("option:selected").prop("selected", false);
					
				});
				jQuery('.btn_cancel_project').click(function(){
					jQuery('#project_list').show();
					jQuery('#new_project_wrapper').hide();
					jQuery('#new_project').val('');
					jQuery('#yith-wapo-25-0').val('').trigger('change');
					
				});
				jQuery('#project_list').on( 'change', function(){
					var txt = jQuery('option:selected', this).text();
					jQuery('#yith-wapo-19-0').val(txt == ' - Select Project - ' ? '' : txt); //name
					jQuery('#yith-wapo-25-0').val(jQuery('option:selected', this).val()).trigger('change'); //id
					
				}).trigger('change');
				
				jQuery('#yith-wapo-25-0').on( 'change', function(){
					if( jQuery(this).val() == 'New' ) {
						jQuery('#project_list').attr({'required' : false}); 
						jQuery('#yith-wapo-19-0').attr({'required' : true}); 
						jQuery('#yith-wapo-23-0').attr({'required' : true}); 
						jQuery('#yith-wapo-23-1').attr({'required' : true}); 
						jQuery('#yith-wapo-23-2').attr({'required' : true});  
						jQuery('#yith-wapo-24-0').attr({'required' : true}); 
						
						jQuery('#yith-wapo-block-x').parent().hide();
						jQuery('#yith-wapo-block-2').show();
					} else {
						jQuery('#project_list').attr({'required' : true});  
						jQuery('#yith-wapo-19-0').attr({'required' : false}); 
						jQuery('#yith-wapo-23-0').attr({'required' : false});
						jQuery('#yith-wapo-23-1').attr({'required' : false});
						jQuery('#yith-wapo-23-2').attr({'required' : false});
						jQuery('#yith-wapo-24-0').attr({'required' : false});
						jQuery('#yith-wapo-block-x').parent().show();
						jQuery('#yith-wapo-block-2').hide();
					}

				}).trigger('change');
			});
		</script>
		
		<?php
	 }
	 
	public function get_projects()
	{
		$user_id = get_current_user_id();
		return $GLOBALS['wpdb']->get_results( "SELECT `id`, `title` FROM `wp_pm_projects` where exists (
			SELECT `wp_pm_role_user`.`project_id` FROM `wp_pm_role_user` WHERE `wp_pm_projects`.`id` = `wp_pm_role_user`.`project_id` AND `wp_pm_role_user`.`user_id`=".$user_id."
		) ORDER BY created_at DESC", OBJECT );

	}
	
	public function create_order_from_checkout($order_id)
	{

		$orders = wc_get_order($order_id);
		$user_id = get_current_user_id();
		$project_id = 0;
		
		foreach($orders->get_items() as $order) {
			
			$data = '';
			$project_name = '';
			$project_data = '';
			$product = $order->get_product();
			
			foreach($order->get_meta_data('_raq_request') as $item) {
				$skip = false;
				$itemdata = $item->get_data();
				if($itemdata['key'] == '_ywraq_wc_ywapo') { continue; }


				if($itemdata['key'] == 'ID') {
					if( strpos( $itemdata['value'], 'Id: ') !== false){
						$project_id = str_replace( 'Id: ', '', $itemdata['value']);
					}
					$skip = true;
				}elseif($itemdata['key'] == 'Project') {
					if( strpos( $itemdata['value'], 'Id: ') !== false){
						$project_id = str_replace( 'Id: ', '', $itemdata['value']);
					}elseif( strpos( $itemdata['value'], 'Name: ') !== false){
						$project_name = str_replace( 'Name: ', '', $itemdata['value']);
					}
					$skip = true;
				}elseif($itemdata['key'] == 'Address') {
					$project_data .= urldecode($itemdata['value']) . "\r\n";
					$skip = true;
				}elseif($itemdata['key'] == 'Delivery') {
					if( strpos( $itemdata['value'], 'Date: ') !== false){
						$project_data .= str_replace( 'Date: ', 'Delivery Date: ', urldecode($itemdata['value'])) . "\r\n";
					}
					$skip = true;
				}

				if(!$skip) {
					$data .= $itemdata['key'] . ': ' . $itemdata['value'] . '<br/>';
				}
			}

			if( $project_id == 'New' ) {
				
                $project_data = array( 
                	"title" => urldecode($project_name), 
                	"description" => $project_data,
                    "notify_users" => true, 
                    "status" => "incomplete"
                );
                $project_controller = new Project_Controller();
                $response = $project_controller->create_project( $project_data );
                $project_id = $response['data']['id'];
			}
			
			$task_data = array(
	            'title' => $order->get_name(),
	            'project_id' => $project_id,
	            'board_id'   => 0,
	            'assignees'  => array( $user_id ),
	            'privacy'    => true,
	            'created_by' => $user_id,
	            'description' => $data,
	            'updated_by' => $user_id
	        );
	
	        Task_Controller::create_task( $task_data );
	        	
		}
	}
	
	public function user_cap_update( $user_login, $user ) {

		$user_id = $user->ID;
		if ( user_can( $user_id, 'manage_options' ) ) {
            return;
        }

        update_user_meta( $user_id, 'pm_capability', '' );
        $user = get_user_by( 'id', $user_id );

        foreach ( pm_access_capabilities() as $meta_key => $label ) {
            $user->remove_cap( $meta_key );
        }
	}

	 public function footer()
	 {
		 if(!is_page('projects')) {
			return;
		 }
		 
		 $user_ID = get_current_user_id(); 
		 
		$address_1 = get_user_meta( $user_ID, 'billing_address_1', true ); 

		$city = get_user_meta( $user_ID, 'billing_city', true );
		$postcode = get_user_meta( $user_ID, 'billing_postcode', true );

		 ?>
		<script>
			jQuery(document).ready(function(){
					
				function check_trigger (){
					console.log(jQuery("#pfd-create-project").length);
					if(jQuery('#wedevs-project-manager > div:nth-child(3) > div.pm-header > div > div > div.pm-header-left > h2').length > 0) {
						if(jQuery("#pfd-create-project").length == 0) {
						jQuery('#wedevs-project-manager > div:nth-child(3) > div.pm-header > div > div > div.pm-header-left > h2').after('<a href="#" id="pfd-create-project" class="pm-btn pm-btn-primary pm-btn-uppercase"><i aria-hidden="true" class="pm-icon flaticon-plus"></i>&nbsp;&nbsp;New Project</a><div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front pm-ui-dialog ui-draggable ui-resizable pfd-project-dialog" aria-describedby="pfd-project-dialog" style="position: absolute; height: auto; width: 485px;display:none" aria-labelledby="ui-id-3"><div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle"><span id="ui-id-3" class="ui-dialog-title">Start a new project</span><button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close"><span class="ui-button-icon pfd-close ui-icon ui-icon-closethick"></span><span class="ui-button-icon-space"> </span>Close</button></div><div id="pm-project-dialog" style="width: auto; min-height: 73.0156px; max-height: none; height: auto;" class="ui-dialog-content ui-widget-content"><div><form action="" method="post" class="pm-form pfd-project-form"><div class="item project-name"><input type="text" id="project_name" name="name" placeholder="Name of the project" size="45" required></div><div class="item street"><input type="text" id="street" name="street" value="<?php echo $address_1?>" placeholder="Street Address" size="45" required></div><div class="item project-name"><input type="text" id="city" name="city" value="<?php echo $city?>" placeholder="City" size="45" required></div><div class="item zip"><input type="text" id="zip" placeholder="Zip/Postal Code" value="<?php echo $postcode?>" size="45" required></div><div class="item project-name"><input type="text" id="delivery" name="delivery" placeholder="Delivery Date" size="45" required></div><!----><div class="submit"><!----> <input type="submit" name="add_project" id="add_project" class="pm-button pm-primary" value="Add New Project"> <a href="#" class="pm-button pm-secondary pfd-close">Close</a> <span class="pm-loading" style="display: none;"></span></div></form> </div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90; display: block;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90; display: block;"></div></div>');
						jQuery("#pm-create-project").hide();
						
						
				
					jQuery('#pfd-create-project').on("click", function(e){
						e.preventDefault();
						jQuery('.pfd-project-dialog').show();
						jQuery( "#delivery" ).datepicker({ minDate: 0});
						
					});
					jQuery('.pfd-close').on('click', function(){
						jQuery('.pfd-project-dialog').hide();
						
						
					});
					jQuery('.pfd-project-form').on('submit', function(e){
						e.preventDefault();
						console.log( jQuery(this).serialize() );
									
				      jQuery.ajax({
				         type : "post",
				         dataType : "json",
				         url : "<?php echo admin_url( 'admin-ajax.php' );?>",
				         data : {
					        action: "pfd_create_project", 
					        name : jQuery('.pfd-project-form #project_name').val(), 
					        street : jQuery('.pfd-project-form #street').val(), 
					        city : jQuery('.pfd-project-form #city').val(), 
					        zip : jQuery('.pfd-project-form #zip').val(), 
					        delivery : jQuery('.pfd-project-form #delivery').val(), 
					        nonce: "<?php echo wp_create_nonce("project_nonce")?>"},
					         success: function(response) {
					            if(typeof response.data.id != 'undefined') {
					               pm.Toastr.success(response.message);
					               window.location.reload();
					            }
					            else {
					               pm.Toastr.error(response.message);
					            }
					         }
					      })   
						
						return false;
					});
					
						}
					}
					

				}
				setInterval (check_trigger, 300);
			});
		</script>
		<style>
			#ui-datepicker-div {
				z-index: 10000!important;
			}
			
			#ui-datepicker-div {
			    width: 12em;
			    padding: 5px;
			}
			.ui-widget {
				font-size: .7em!important;
			}
		</style>
		 <?php
		 
	 }
	 
	public function create_project()
	{
		if ( !wp_verify_nonce( $_REQUEST['nonce'], "project_nonce")) {
			exit("No naughty business please");
		} 
		
		$project_name = wp_filter_kses($_POST['name']);
		$street = wp_filter_kses($_POST['street']);
		$city = wp_filter_kses($_POST['city']);
		$zip = wp_filter_kses($_POST['zip']);
		$delivery_date = wp_filter_kses($_POST['delivery']);
		
		$desc = sprintf("Street Address: %s\r\nCity: %s\r\nZip/Postal Code: %s\r\nDelivery Date: %s", $street, $city, $zip, $delivery_date);
        $project_data = array( 
        	"title" => urldecode($project_name), 
        	"description" => $desc,
            "notify_users" => true, 
            "status" => "incomplete"
        );
        $project_controller = new Project_Controller();
        $response = $project_controller->create_project( $project_data );
        
        wp_send_json($response);
	}
	
	function add_slug_body_class( $classes ) {
		global $post;
		if ( isset( $post ) ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
		}
		return $classes;
	}
	
	function display_product_dimensions( $atts ){
	    // Extract shortcode attributes
	    extract( shortcode_atts( array(
	        'id' => '',
	    ), $atts, 'product_dimensions' ) );
	
	    if( $id == '' ) {
	        global $product;
	
	        if( ! is_a($product, 'WC_Product') ) {
	            $product = wc_get_product( get_the_id() );
	        }
	    }
	
		if(!method_exists( $product, 'get_dimensions' )) {
			return 'N/A';
		}
		
	    if(! $product->has_dimensions() ) {
			return 'N/A';
		}

		$return = '<div class="pdt-dimensions"><em>';
		$return .= '<b>Lenght:</b> ' . $product->get_length() . ' ' . get_option( 'woocommerce_dimension_unit' );
		$return .= '<br><b>Height:</b> ' . $product->get_height() . ' ' . get_option( 'woocommerce_dimension_unit' );
		$return .= '<br><b>Width:</b> ' . $product->get_width() . ' ' . get_option( 'woocommerce_dimension_unit' );
		$return .= '</em></div>';
		return $return;
	}
	
	public function admin_menu($caps, $home)
	{
		$page = add_submenu_page( 
			'pm_projects',
			'Project Report',
			'Project Report',
			$caps,
			'project-report',
			[$this, 'admin_page'],
		);

        add_action('load-' . $page, [$this, 'preload']);
	}
	
	public function admin_page()
	{	
		return pfd_render('view-project-report');
	}
	
	public function preload() {
		
		if(!isset($_POST['download_reports'])) {
			return;
		}
		
		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], "download_reports_nonce")) {
			exit("No naughty business please");
		} 
		
        global $wpdb;
        
        add_filter('pm_task_where', [$this, 'get_task_where_created'], 100, 2);
     
        $results = pm_get_tasks(
        	[
        		'with' => 'project',
        		'per_page' => '-1',
            	'orderby' => 'created_at:desc'
            ]
        );
        
        $output = $this->output();

        foreach($results["data"] as $task) {
			$creators = array_column($task["assignees"]["data"], "display_name");
			$d_creators = is_array($creators) ? implode(', ',$creators): '';
			fputcsv( $output, 
				[ 
					$task["project_title"], 
					preg_replace('/<br\s?\/?>/i', "\n", urldecode($task["project"]["data"]["description"]["content"])), 
					$task["title"], 
					preg_replace('/<br\s?\/?>/i', "\n", urldecode($task["description"]["content"])), 
					$d_creators,	
					$task["status"],
					$task["created_at"]["date"]
				]
			);

        }
		fclose($output);
		exit;
        
	}
	
	public function get_task_where_created($where, $user_id)
	{
		global $wpdb;

		$tb_tasks    = pm_tb_prefix() . 'pm_tasks';
		$tb_assignees   = pm_tb_prefix() . 'pm_assignees';
		$tb_projects   = pm_tb_prefix() . 'pm_projects';
		$tb_role_user   = pm_tb_prefix() . 'pm_role_user';
		
        $from = $_REQUEST['from'];
        $to = $_REQUEST['to'];

        $start = empty( $from )
            ? date( 'Y-m-d', strtotime( '-30 days' ) )
            : date( 'Y-m-d', strtotime( $from ) );
        $end = empty( $to )
            ? date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) )
            : date( 'Y-m-d', strtotime( $to ) );
            
		$where .= $wpdb->prepare( " AND (DATE({$tb_tasks}.created_at) BETWEEN %s AND %s) ", $start, $end );
		
		if(isset($_REQUEST['user']) && !empty($_REQUEST['user'])) {
			$where .= $wpdb->prepare( " AND ( {$tb_tasks}.project_id IN (
					SELECT {$tb_role_user}.project_id FROM {$tb_role_user} 
						WHERE {$tb_tasks}.project_id = {$tb_role_user}.project_id AND {$tb_role_user}.user_id=%d)
					) ", wp_filter_kses($_REQUEST['user'])
			);
		}
		
		return $where;
	}
	
	public function output($save = true) {
		if (!$save) {
			return;
		}
		header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=tasks-'. date('YMdhis') .'.csv');
        $output = fopen("php://output", "wb");
        fputcsv(
	        $output, 
			['Project', 'Project Description', 'Task', 'Task Description', 'Assignees', 'Status', 'Date Created']
		);
		return $output;
	}
	
	public function user_list()
	{
		
        $wp_user_query = new \WP_User_Query([
            'search' => sanitize_text_field($_POST['q']),
            'number' => 50
        ]);
        $users = $wp_user_query->get_results();
        $result = [
	        ['id' => '', 'text' => 'Any User']
        ];
        foreach ($users as $user) {
            $result[] = ['id' => $user->ID, 'text' => $user->display_name];
        }

        wp_send_json([
            'results' => $result,
            'pagination' => ['more' => false]
        ]);
        wp_die();
	}
	
	
 }


if (!function_exists('pfd_render')) {
    function pfd_render($file, $args = [])
    {
        extract($args);
        
        $path = path_join(plugin_dir_path( __FILE__ ), $file . '.php');
        
        
        if (!file_exists($path)) {
            return false;
        }

        if (!$args) {
            require_once $path;
            return true;
        }

        ob_start();
        require $path;
        
        
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}

new PhotonFactoryDev();


function dd($str, $exit = true) {
	echo '<pre>';
	var_dump($str);
	echo '</pre>';
	
	if($exit) {
		exit;
	}
	
}