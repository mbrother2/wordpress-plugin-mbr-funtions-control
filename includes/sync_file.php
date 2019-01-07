<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../../../../wp-load.php');
$url = "/wp-admin/admin.php?page=mbr-functions-control";

global $wpdb;
$file_name = get_stylesheet_directory() . '/functions.php';
$function_file = file_get_contents($file_name);
$new_file = $file_name . '.mbr_bak';
$table_name = $wpdb->prefix . 'mbr_function_control';

// Remove all rows of mbr_function_control table if exist
$total_function = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
if($total_function > 0){
    $wpdb->query("TRUNCATE TABLE $table_name");
}
rename($file_name, $new_file);
file_put_contents($file_name, '<?php');
$arr_function_file = preg_split( '/\n\n\n/', $function_file );
foreach($arr_function_file as $a_function){
    $a_function = trim($a_function);
    $arr_a_function = preg_split( '/\n/', $a_function );
    if(current($arr_a_function) == "<?php"){
        unset($arr_a_function[0]);
        $function_title = $arr_a_function[1];
    }
    else {
        $function_title = $arr_a_function[0];
    }
    if(end($arr_a_function) == "?>"){
        unset($arr_a_function[count($arr_a_function)-1]);
    }
    $function_title = trim(substr($function_title,2));
    $function_content = implode("\n",(array_slice($arr_a_function,1)));
    $wpdb->insert(
        $table_name,
        array(
            'status'  => 1,
            'open'    => 1,
            'same'    => 1,
            'title'   => $function_title,
            'content' => $function_content,
        )
    );
    file_put_contents($file_name, PHP_EOL . '// ' . $function_title . PHP_EOL, FILE_APPEND | LOCK_EX);
    file_put_contents($file_name, $function_content . PHP_EOL, FILE_APPEND | LOCK_EX);
}
wp_redirect($url);
exit;