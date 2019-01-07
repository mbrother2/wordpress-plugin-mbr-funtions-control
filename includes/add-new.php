<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require('../../../../wp-load.php');
$url = "/wp-admin/admin.php?page=mbr-functions-control";
global $wpdb;

$function_title = $_POST['function_title'];
$function_content = $_POST['function_content'];
$function_file = get_stylesheet_directory() . '/functions.php';
// Write information to database
$content_input = stripslashes($function_content);
$title_input = stripslashes($function_title);
$table_name = $wpdb->prefix . 'mbr_function_control';

$wpdb->insert(
    $table_name,
    array(
        'status'  => 1,
        'open'    => 1,
        'same'    => 1,
        'title'   => $title_input,
        'content' => $content_input,
    )
);

// Write information to file funtions.php
$function_content = PHP_EOL . '// ' . $function_title . PHP_EOL . $function_content . PHP_EOL;
$function_content = stripslashes(preg_replace('/(\r\n|\r|\n)/s',"\n",$function_content));
file_put_contents($function_file, $function_content, FILE_APPEND | LOCK_EX);
wp_redirect($url);
exit;
?>