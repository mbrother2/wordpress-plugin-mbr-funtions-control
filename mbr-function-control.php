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

// Add CSS and JS
wp_enqueue_style( 'mbr_bootstrap_css', '//maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css' );
wp_enqueue_style( 'mbr_fontawesome_css', '//stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
wp_enqueue_style( 'mbr_custom_css', plugins_url( '/css/style.css', __FILE__ ));
wp_enqueue_script( 'mbr_bootstrap_js', '//maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js' );
wp_enqueue_script( 'mbr_jquery_js', '//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js' );
wp_enqueue_script( 'mbr_popper_js', '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' );
wp_enqueue_script( 'mbr_custom_js', plugins_url( '/js/mbr.js', __FILE__ ));
wp_enqueue_script( 'mbr_ace_js', plugins_url( '/plugin/ace/src/ace.js', __FILE__ ));

// Create database table mbr_function_control for plugin
register_activation_hook( __FILE__, 'my_plugin_create_db' );
function my_plugin_create_db() {
global $wpdb;
    $table_name = $wpdb->prefix . "mbr_function_control"; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    status boolean NOT NULL,
    title text NOT NULL,
    content text NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Add plugin to admin menu bar
add_action( 'admin_menu', 'extra_post_info_menu' );
if( !function_exists("extra_post_info_menu") ){
    function extra_post_info_menu(){
        $page_title = 'MBR functions control';
        $menu_title = 'MBR functions control';
        $capability = 'manage_options';
        $menu_slug  = 'extra-post-info';
        $function   = 'extra_post_info_page';
        $icon_url   = 'dashicons-media-code';
        $position   = 99;

        add_menu_page( $page_title,
                       $menu_title, 
                       $capability, 
                       $menu_slug, 
                       $function, 
                       $icon_url, 
                       $position );
    }
}
    
// Main Plugin
function getStringBetween($str,$from,$to)
{
    $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
    return substr($sub,0,strpos($sub,$to));
}

if( !function_exists("extra_post_info_page") )
{
function extra_post_info_page(){
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
                <h3>MBR functions control v1.0</h3>
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
            $function_file = file_get_contents(get_stylesheet_directory() . '/functions.php');
            $function_next_id = $wpdb->get_var( "SELECT id FROM $table_name WHERE id > $row->id ORDER BY id ASC LIMIT 1" );
            $mbr_title1 = '// ' . $wpdb->get_var( "SELECT title FROM $table_name WHERE ID = $row->id" );
            $mbr_title2 = '// ' . $wpdb->get_var( "SELECT title FROM $table_name WHERE ID = $function_next_id" );
            $function_get_db = $wpdb->get_var( "SELECT content FROM $table_name WHERE ID = $row->id" );
            $function_get_db2 = esc_html(preg_replace('/\s*/m','',$function_get_db));
            if($function_next_id != NULL){
                $function_get_file = getStringBetween($function_file, $mbr_title1, $mbr_title2);
                $function_get_file2 = esc_html(preg_replace('/\s*/m','',$function_get_file));
            }
            else {
                $arr_function_get_file = explode($mbr_title1, $function_file);
                $function_get_file = $arr_function_get_file[1];
                $function_get_file2 = esc_html(preg_replace('/\s*/m','',$function_get_file));
            }
        ?>
        <div class="row">
            <div class="col py-4">
                <?php if($function_get_db2 != $function_get_file2){ 
                    $wpdb->update( 
                        $table_name, 
                        array(
                            'same'  => 0
                        ),
                        array(
                            'id' => $row->id
                        )
                    );
                ?>
                <div class="row p-0 m-0">
                    <div class="col-12 py-0 alert alert-danger">
                        <form class="mb-2" method='post' action='<?php echo plugins_url( 'includes/update.php', __FILE__ ); ?>'>
                            <div class="form-group row p-0 m-0">
                                <input type='hidden' name='function_id' value='<?php echo $row->id ?>'>
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
                <?php } ?>
                <div class="row">
                    <div class="col-2 col-sm-1 pr-0 mr-0">
                        <label class="switch">
                            <input <?php if($function_get_db2 != $function_get_file2){ echo disabled; }?> id="<?php echo $row->id;?>" type="checkbox" <?php if($row->status == 1){ echo checked;}?> name="colorCheckbox" value="<?php echo $row->id;?>">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="col-10 col-sm-11 pl-0">
                        <div class="accordion w-100 mbr_open_accordion" id="mbr_accordion<?php echo $row->id;?>">
                            <div data-toggle="collapse" data-target="#mbr_collapse<?php echo $row->id;?>">
                                <h4 class="mbr_accordion <?php if($function_get_db2 != $function_get_file2){ echo disabled; }?>" ><?php echo $row->title ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div id="mbr_collapse<?php echo $row->id;?>" class="collapse <?php if($row->open == 1){?> show <?php } ?> w-100" aria-labelledby="hostvn_heading" data-parent="#mbr_accordion<?php echo $row->id;?>">
                            <?php if($row->status == 1){ ?>
                            <div class="<?php echo $row->id;?>">
                            <?php } else { ?>
                            <div class="<?php echo $row->id;?>" style="display: none;">
                            <?php } ?>
                                <form method='post' action='<?php echo plugins_url( 'includes/update.php', __FILE__ ); ?>'>
                                    <input type='hidden' name='function_title' value='<?php echo $row->title ?>'>
                                    <input type='hidden' name='function_id' value='<?php echo $row->id ?>'>
                                    <textarea <?php if($function_get_db2 != $function_get_file2){ echo disabled; }?> class="form-control py-3" name="new_function_content" rows="5"><?php echo esc_html($row->content); ?></textarea>
                                    <input <?php if($function_get_db2 != $function_get_file2){ echo disabled; }?> type='submit' class='btn btn-warning fa mbr-fa mt-2' name='update_function' value='&#xf0aa; Cập nhật'>
                                    <button <?php if($function_get_db2 != $function_get_file2){ echo disabled; }?> type='button' class='btn btn-danger ml-2 mt-2 showmodal' data-toggle='modal' data-target='#ask_delete' data-functioncontent="<?php echo esc_html($row->content); ?>" data-functiontitle="<?php echo esc_html($row->title) ?>" data-functionid="<?php echo $row->id; ?>"><i class="fa fa-times-circle" aria-hidden="true"></i> Xóa</button>
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
                                <input type='hidden' name='function_title' value='<?php echo $row->title ?>'>
                                <input type='hidden' name='function_id' value='<?php echo $row->id ?>'>
                                <label class="col-sm-2 col-form-label bg-light border-right border-bottom text-truncate">Tên function</label>
                                <div class="col-sm-10 border-bottom px-2">
                                    <input type="text" class="form-control border-0" name="input1" id="input1">
                                </div>
                                <label class="col-sm-2 col-form-label bg-light border-right border-bottom text-truncate">Nội dung</label>
                                <div class="col-sm-10 border-bottom px-2">
                                    <textarea class="form-control border-0" rows="5" name="input2" id="input2" aria-label="With textarea"></textarea>
                                </div>
                            </div>
                            <div class='modal-footer mb-0'>
                                <input type='submit' class='btn btn-success' value='Thêm mới'>
                                <button type='button' class='btn btn-danger' data-dismiss='modal'>Hủy bỏ</button>
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
?>
