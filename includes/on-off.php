<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../../../../wp-load.php');

// Write information to database
global $wpdb;
$table_name = $wpdb->prefix . 'mbr_function_control';
if(isset($_POST['id'])){
    $input1 = $_POST['id'];
    $input2 = $_POST['value'];

    $wpdb->update(
        $table_name,
        array(
            'status'  => $_POST['value']
        ),
        array(
            'id' => $input1
        )
    );

    // Write information to file funtions.php
    $old_content = $wpdb->get_var( "SELECT content FROM $table_name WHERE ID = $input1" );
    $old_content = preg_replace('/(\r\n|\r|\n)/s',"\n",$old_content);
    if ($input2 == 1) {
        $new_content = str_replace(array('**/' . PHP_EOL . '/*', '*/' . PHP_EOL . '/*'), array('/*','*/'), $old_content);
        $new_content = preg_replace(array('/^.+\n/','/\n.+$/'), '', $new_content);
    }
    else {
        $new_content = str_replace(array('/*', '*/'), '*/' . PHP_EOL . '/*', $old_content);
        $new_content = str_replace('*/' . PHP_EOL . '/*' . PHP_EOL . '/*','**/' . PHP_EOL . '/*', $new_content);
        $new_content = '/*' . PHP_EOL . $new_content . PHP_EOL . '*/';
    }
    $wpdb->update(
        $table_name,
        array(
            'content'  => $new_content
        ),
        array(
            'id' => $input1
        )
    );
    $mbr_str = implode("",file(get_stylesheet_directory() . '/functions.php'));
    $mbr_fp=fopen(get_stylesheet_directory() . '/functions.php','w');
    $mbr_str = str_replace($old_content, $new_content, $mbr_str);
    fwrite($mbr_fp,$mbr_str,strlen($mbr_str));
    fclose($mbr_fp);
}

if(isset($_POST['functionid'])){
    $function_id = $_POST['functionid'];
    $function_status = $wpdb->get_var( "SELECT status FROM $table_name WHERE ID = $function_id" );
    $function_open = $wpdb->get_var( "SELECT open FROM $table_name WHERE ID = $function_id" );
    if ($function_status == 1){
        if ($function_open == 1){
            $function_open = 0;
        }
        else {
            $function_open = 1;
        }
    }
    $wpdb->update(
        $table_name,
        array(
            'open'  => $function_open
        ),
        array(
            'id' => $function_id
        )
    );
}
