<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
/*
 * Plugin Name: MBR functions control
 * Plugin URI: http://buildmce.com
 * Description: Control your funtions in functions.php 
 * Version: 1.0 
 * Author: Root Orchild
 * Author URI: http://buildmce.com
 * License: GPLv2 or later 
 */

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

// Add CSS and JS for this plugin only
function load_custom_wp_admin_style($hook) {
    if($hook == 'toplevel_page_mbr-functions-control' || $hook == 'mbr-functions-control_page_sync-file') {
        wp_enqueue_style( 'mbr_bootstrap_css', plugins_url( '/css/bootstrap.min.css', __FILE__ ));
        wp_enqueue_style( 'mbr_fontawesome_css', plugins_url( '/css/font-awesome.min.css', __FILE__ ));
        wp_enqueue_style( 'mbr_custom_css', plugins_url( '/css/style.css', __FILE__ ));
        wp_enqueue_script( 'mbr_bootstrap_js', plugins_url( '/js/bootstrap.min.js', __FILE__ ));
        wp_enqueue_script( 'mbr_jquery_js', plugins_url( '/js/jquery.min.js', __FILE__ ));
        wp_enqueue_script( 'mbr_popper_js', plugins_url( '/js/popper.min.js', __FILE__ ));
        wp_enqueue_script( 'mbr_custom_js', plugins_url( '/js/mbr.js', __FILE__ ));
    }
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

// Create database table mbr_function_control for plugin
register_activation_hook( __FILE__, 'my_plugin_create_db' );
function my_plugin_create_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . "mbr_function_control"; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    status boolean NOT NULL,
    open boolean NOT NULL,
    same boolean NOT NULL,
    title text NOT NULL,
    content text NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Add plugin to admin menu bar
add_action( 'admin_menu', 'mbr_add_menu_bar' );
if( !function_exists("mbr_add_menu_bar") ){
    function mbr_add_menu_bar(){
        add_menu_page( 'MBR functions control', 'MBR functions control', 'manage_options', 'mbr-functions-control', 'mbr_main_function', plugins_url( '/mbr.png', __FILE__ ), '99' );
        add_submenu_page( 'mbr-functions-control', 'Sync file', 'Sync file funtions.php', 'manage_options', 'sync-file', 'mbr_sync_file');
    }
}
    
function getStringBetween($str,$from,$to)
{
    $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
    return substr($sub,0,strpos($sub,$to));
}

function function_disable($funct_db, $funct_file){
    if($funct_db != $funct_file){
        echo disabled;
    }
    else {
        echo "";
    }
}

// Main Plugin
if( !function_exists("mbr_main_function") )
{
function mbr_main_function(){
    global $wpdb;
    $table_name = $wpdb->prefix . "mbr_function_control";
    $all_functions = $wpdb->get_results( "SELECT * FROM $table_name" );
    $total_function = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
	$wpdb->get_results("SELECT * FROM $table_name WHERE status = '1'");
    $active_function = $wpdb->num_rows;
    $inactive_function = $total_function - $active_function;
?>
    <div class="container">
        <div class="row py-4">
            <div class="col text-center">
                <h3><img src="<?php echo plugins_url( '/mbr3.png', __FILE__ ); ?>"> MBR functions control v1.0</h3>
            </div>
        </div>
		<div class="row mx-0">
			<div class="col-4 alert alert-primary">
				<h5>Tổng: <?php echo $total_function;?></h5>
			</div>
			<div class="col-4 alert alert-success">
				<h5 class="mb-0"><label>Bật: </label><input id="mbr_active_function" type="text" value="<?php echo $active_function;?>" readonly></h5>
			</div>
			<div class="col-4 alert alert-danger">
				<h5 class="mb-0"><label>Tắt: </label><input id="mbr_inactive_function" type="text" value="<?php echo $inactive_function;?>" readonly></h5>
			</div>
		</div>
        <div class="row pb-4">
            <div class="col text-center">
                <form method='post' action='<?php echo plugins_url( 'includes/update.php', __FILE__ ); ?>'>
                    <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#mbr_modal'><i class="fa fa-plus-circle" aria-hidden="true"></i> Thêm mới</button>
					<input type='submit' class='btn btn-success mx-2 fa mbr-fa' name='active_all' value='&#xf058; Bật toàn bộ'>
					<input type='submit' class='btn btn-danger fa mbr-fa' name='inactive_all' value='&#xf057; Tắt toàn bộ' >
					<input type='submit' class='btn btn-info mx-2 fa mbr-fa' name='expand_all' value='&#xf0b2; Mở toàn bộ' >
					<input type='submit' class='btn btn-secondary fa mbr-fa' name='collapse_all' value='&#xf066; Thu toàn bộ' >
                </form>
            </div>
        </div>

        <?php foreach($all_functions as $row){
            $function_id = $row->id;
            $function_status = $row->status;
            $function_open = $row->open;
            $function_title = $row->title;
            $function_content = $row->content;
            $function_file = file_get_contents(get_stylesheet_directory() . '/functions.php');
            $function_next_id = $wpdb->get_var( "SELECT id FROM $table_name WHERE id > $function_id ORDER BY id ASC LIMIT 1" );
            $mbr_title1 = '// ' . $function_title;
            $mbr_title2 = '// ' . $wpdb->get_var( "SELECT title FROM $table_name WHERE ID = $function_next_id" );
            $function_get_db = $row->content;
            $function_get_db = esc_html(preg_replace('/\s*/m','',$function_get_db));
            if($function_next_id != NULL){
                $function_get_file = getStringBetween($function_file, $mbr_title1, $mbr_title2);
                $function_get_file = esc_html(preg_replace('/\s*/m','',$function_get_file));
            }
            else {
                $arr_function_get_file = explode($mbr_title1, $function_file);
                $function_get_file = $arr_function_get_file[1];
                $function_get_file = esc_html(preg_replace('/\s*/m','',$function_get_file));
            }
            if($function_status == 1){
                $show_function_content = $function_content;
            }
            else {
                $show_function_content = str_replace(array('**/' . PHP_EOL . '/*', '*/' . PHP_EOL . '/*'), array('/*','*/'), $function_content);
                $show_function_content = preg_replace(array('/^.+\n/','/\n.+$/'), '', $show_function_content);
            }

        ?>
        <div class="row">
            <div class="col py-4">
                <?php if($function_get_db != $function_get_file){ 
                    $wpdb->update( 
                        $table_name, 
                        array(
                            'same'  => 0
                        ),
                        array(
                            'id' => $function_id
                        )
                    );
                ?>
                <div class="row p-0 m-0">
                    <div class="col-12 py-0 alert alert-danger">
                        <form class="mb-2" method='post' action='<?php echo plugins_url( 'includes/update.php', __FILE__ ); ?>'>
                            <div class="form-group row p-0 m-0">
                                <input type='hidden' name='function_id' value='<?php echo $function_id ?>'>
<!--                                <input type='hidden' name='new_function_content' value='<?php echo stripslashes(esc_html($function_get_file)); ?>'> -->
                                <div class="col-sm-10 mt-3">
                                    <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Dữ liệu trong file functions.php không giống với trong cơ sở dữ liệu!</h5>
                                </div>
                                <div class="col-sm-2 pb-0">
                                    <input type='submit' class='btn btn-warning fa mbr-fa mt-2' name='sync_db' value='&#xf021; Đồng bộ'>
                                </div>
                            </div>
                        </form>
                    </div>                   
                </div>
                <?php } 
                else {
                    $wpdb->update( 
                        $table_name, 
                        array(
                            'same'  => 1
                        ),
                        array(
                            'id' => $function_id
                        )
                    );
                }?>
                <div class="row">
                    <div class="col-2 col-sm-1 pr-0 mr-0">
                        <label class="switch">
                            <input <?php function_disable($function_get_db, $function_get_file);?> id="<?php echo $function_id;?>" type="checkbox" <?php if($function_status == 1){ echo checked;}?> name="colorCheckbox" value="<?php echo $function_id;?>">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="col-10 col-sm-11 pl-0">
                        <div class="accordion w-100 mbr_open_accordion" id="mbr_accordion<?php echo $function_id;?>">
                            <div data-toggle="collapse" data-target="#mbr_collapse<?php echo $function_id;?>">
                                <h4 class="mbr_accordion <?php function_disable($function_get_db, $function_get_file);?>" ><?php echo $function_title ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div id="mbr_collapse<?php echo $function_id;?>" class="collapse <?php if($function_open == 1){?> show <?php } ?> w-100" aria-labelledby="hostvn_heading" data-parent="#mbr_accordion<?php echo $function_id;?>">
                            <?php if($function_status == 1){ ?>
                            <div class="<?php echo $function_id;?>">
                            <?php } else { ?>
                            <div class="<?php echo $function_id;?>" style="display: none;">
                            <?php } ?>
                                <form method='post' action='<?php echo plugins_url( 'includes/update.php', __FILE__ ); ?>'>
                                    <input type='hidden' name='function_title' value='<?php echo $function_title ?>'>
                                    <input type='hidden' name='function_id' value='<?php echo $function_id ?>'>
                                    <textarea <?php function_disable($function_get_db, $function_get_file);?> class="form-control py-3" name="new_function_content" rows="5"><?php echo esc_html($show_function_content); ?></textarea>
                                    <input <?php function_disable($function_get_db, $function_get_file);?> type='submit' class='btn btn-warning fa mbr-fa mt-2' name='update_function' value='&#xf0aa; Cập nhật'>
                                    <button <?php function_disable($function_get_db, $function_get_file);?> type='button' class='btn btn-danger ml-2 mt-2 showmodal' data-toggle='modal' data-target='#ask_delete' data-functioncontent="<?php echo esc_html($function_content); ?>" data-functiontitle="<?php echo esc_html($function_title) ?>" data-functionid="<?php echo $function_id; ?>"><i class="fa fa-times-circle" aria-hidden="true"></i> Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col py-4">
                <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#mbr_modal'><i class="fa fa-plus-circle" aria-hidden="true"></i> Thêm mới</button>
            </div>
        </div>

        <!-- Modal Add new -->
        <div class='modal fade' id='mbr_modal' tabindex='-1' role='dialog' aria-hidden='true'>
            <div class='modal-dialog modal-dialog-centered modal-lg' role='document'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'><i class="fa fa-plus-circle" aria-hidden="true"></i> Thêm vào file functions.php</h5>
                    </div>
                    <div class='modal-body'>
                        <form method='post' action='<?php echo plugins_url( 'includes/add-new.php', __FILE__ ); ?>'>
                            <div class="form-group mx-0 row border rounded">
                                <input type='hidden' name='function_title' value='<?php echo $function_title ?>'>
                                <input type='hidden' name='function_id' value='<?php echo $function_id ?>'>
                                <label class="col-sm-2 col-form-label bg-light border-right border-bottom text-truncate">Tên function</label>
                                <div class="col-sm-10 border-bottom px-2">
                                    <input type="text" class="form-control border-0" name="function_title" id="function_title">
                                </div>
                                <label class="col-sm-2 col-form-label bg-light border-right border-bottom text-truncate">Nội dung</label>
                                <div class="col-sm-10 border-bottom px-2">
                                    <textarea class="form-control border-0" rows="5" name="function_content" id="function_content" aria-label="With textarea"></textarea>
                                </div>
                            </div>
                            <div class='modal-footer mb-0'>
                                <input type='submit' class='btn btn-primary fa mbr-fa' value='&#xf055; Thêm mới'>
                                <button type='button' class='btn btn-danger' data-dismiss='modal'><i class="fa fa-times-circle" aria-hidden="true"></i> Hủy bỏ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ask delete -->
        <div class='modal fade' id='ask_delete' tabindex='-1' role='dialog' aria-hidden='true'>
            <div class='modal-dialog modal-dialog-centered modal-lg' role='document'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'><i class="fa fa-trash" aria-hidden="true"></i> Bạn có chắc chắn muốn xóa funtion này không?</h5>
                    </div>
                    <div class='modal-body'>
                        <form method='post' action='<?php echo plugins_url('includes/update.php', __FILE__ ); ?>' class='form-container'>
                            <input type='hidden' name='function_id'>
							<input type='hidden' name='function_title'>
							<input type='hidden' name='function_content'>
                            <h5 class="pt-1" id='ask_delete_file'></h5>
                            <textarea class="form-control border-0" rows="5" id="ask_delete_file2" readonly></textarea>
                            <div class='modal-footer'>
                                <input type='submit' class='btn btn-danger fa mbr-fa' name='delete_function' value='&#xf057; Xác nhận'>
                                <button type='button' class='btn btn-primary' data-dismiss='modal'><i class="fa fa-minus-circle" aria-hidden="true"></i> Hủy bỏ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
}

// Function sync file
if( !function_exists("mbr_sync_file") ){
    function mbr_sync_file(){
        global $wpdb;
        $table_name = $wpdb->prefix . "mbr_function_control";
        $total_function = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

?>
    <div class="container">
        <div class="row py-4">
            <div class="col text-center">
                <h3>MBR functions control v1.0</h3>
                <h4><i>Sync file to database</i></h4>
            </div>
        </div>
        <div class="row">
            <div class="col alert alert-danger">
                <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> LƯU Ý QUAN TRỌNG:</h5>
                <ul>
                    <li>- Bạn cần tách biệt 2 function bằng 2 dòng trống!</li>
                    <li>- Trong 1 function không được phép cách nhau 2 dòng trống.</li>
                    <li>- File functions.php cũ sẽ được đổi tên thành functions.php.mbr_bak và plugin sẽ tự tạo ra 1 file funtions.php mới chứa các funtion của bạn trong đó.</li>
                </ul>
            </div>
        </div>
        <?php if($total_function > 0){ ?>
        <div class="row">
            <div class="col alert alert-warning">
                <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CẢNH BÁO:</h5>
                <ul>
                    <li>Đã tồn tại dữ liệu trong cơ sở dữ liệu! Nếu bạn nhấn vào nút đồng bộ, plugin sẽ <b>CHỈ</b> xóa dữ liệu trong bảng mbr_function_control và tạo lại cơ sở dữ liệu cho plugin.</li>
                </ul>
            </div>
        </div>      
        <?php }?>
        <div class="row">
            <form method='post' action='<?php echo plugins_url('includes/sync_file.php', __FILE__ ); ?>' class='form-container'>
                <input type='submit' class='btn btn-success fa mbr-fa' name='sync_file' value='&#xf021; Đồng bộ'>                
            </form>
        </div>
    </div>
<?php
    }
}
?>
