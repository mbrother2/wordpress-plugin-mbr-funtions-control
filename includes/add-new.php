<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require('../../../../wp-load.php');
header("Location: /wp-admin/admin.php?page=mbr-functions-control");

global $wpdb;

$input1 = $_POST['input1'];
$input2 = $_POST['input2'];
$function_file = get_stylesheet_directory() . '/functions.php';
// Write information to database
$content_input = stripslashes($input2);
$title_input = stripslashes($input1);
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
$function_content = PHP_EOL . '// ' . $input1 . PHP_EOL . $input2 . PHP_EOL;
$function_content = stripslashes(preg_replace('/(\r\n|\r|\n)/s',"\n",$function_content));
file_put_contents($function_file, $function_content, FILE_APPEND | LOCK_EX);
?>
