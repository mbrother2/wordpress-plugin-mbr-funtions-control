<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../../../../wp-load.php');
header("Location: /wp-admin/admin.php?page=mbr-functions-control");

global $wpdb;

$function_id = $_POST['function_id'];
$function_title = $_POST['function_title'];
$new_function_content = $_POST['new_function_content'];
$table_name = $wpdb->prefix . 'mbr_function_control';
$old_function_content = $wpdb->get_var( "SELECT content FROM $table_name WHERE ID = $function_id" );

function getStringBetween2($str,$from,$to)
{
    $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
    return substr($sub,0,strpos($sub,$to));
}

if(isset($_POST['sync_db'])){
    $function_file = file_get_contents(get_stylesheet_directory() . '/functions.php');
    $function_next_id = $wpdb->get_var( "SELECT id FROM $table_name WHERE id > $function_id ORDER BY id ASC LIMIT 1" );
    $mbr_title1 = '// ' . $wpdb->get_var( "SELECT title FROM $table_name WHERE ID = $function_id" );
    $mbr_title2 = PHP_EOL . '// ' . $wpdb->get_var( "SELECT title FROM $table_name WHERE ID = $function_next_id" );
    if($function_next_id != NULL){
        $new_function_content = getStringBetween2($function_file, $mbr_title1, $mbr_title2);
    }
    else {
        $arr_function_get_file = explode($mbr_title1, $function_file);
        $new_function_content = $arr_function_get_file[1];
    }
    $new_function_content = trim($new_function_content);
    $wpdb->update(
        $table_name,
        array(
            'content'  => $new_function_content,
            'open'     => 1,
            'same'     => 1
        ),
        array(
            'id' => $function_id
        )
    );
}

if(isset($_POST['update_function'])){
    $mbr_str = implode("",file(get_stylesheet_directory() . '/functions.php'));
    $mbr_fp=fopen(get_stylesheet_directory() . '/functions.php','w');
    // Write information to database
    $new_function_content_db = stripslashes($new_function_content);
    $wpdb->update(
        $table_name,
        array(
            'content'  => $new_function_content_db
        ),
        array(
            'id' => $function_id
        )
    );

    // Write information to file funtions.php
    $old_function_content = preg_replace('/(\r\n|\r|\n)/s',"\n",$old_function_content);
    $new_function_content = $wpdb->get_var( "SELECT content FROM $table_name WHERE ID = $function_id" );
    $new_function_content = preg_replace('/(\r\n|\r|\n)/s',"\n",$new_function_content);
    $mbr_str = str_replace($old_function_content, $new_function_content, $mbr_str);
    fwrite($mbr_fp,$mbr_str,strlen($mbr_str));
    fclose($mbr_fp);
}

if(isset($_POST['delete_function'])){
    $mbr_str = implode("",file(get_stylesheet_directory() . '/functions.php'));
    $mbr_fp=fopen(get_stylesheet_directory() . '/functions.php','w');
    $old_function_content = PHP_EOL . '// ' . $function_title . PHP_EOL . $old_function_content;

    // Write information to database
    $wpdb->delete( $table_name, array( 'id' => $function_id ) );

    // Write information to file funtions.php
    $old_function_content = preg_replace('/(\r\n|\r|\n)/s',"\n",$old_function_content);
    $mbr_str = str_replace($old_function_content, '', $mbr_str);
    $mbr_str = trim(str_replace("\n\n\n", "\n\n", $mbr_str));
    fwrite($mbr_fp,$mbr_str,strlen($mbr_str));
    fclose($mbr_fp);
}

if(isset($_POST['active_all'])){
    $user = $wpdb->get_results( "SELECT * FROM $table_name" );
    foreach ($user as $row){
        $function_id = $row->id;
        $function_same = $row->same;
        // Write information to database
        if($row->status == 0 && $row->same == 1){
            $mbr_str = implode("",file(get_stylesheet_directory() . '/functions.php'));
            $mbr_fp = fopen(get_stylesheet_directory() . '/functions.php','w');
            // Write information to file funtions.php
            $old_content = $wpdb->get_var( "SELECT content FROM $table_name WHERE ID = $function_id" );
            $old_content = preg_replace('/(\r\n|\r|\n)/s',"\n",$old_content);
            $new_content = str_replace(array('**/' . PHP_EOL . '/*', '*/' . PHP_EOL . '/*'), array('/*','*/'), $old_content);
            $new_content = preg_replace(array('/^.+\n/','/\n.+$/'), '', $new_content);
            $wpdb->update(
                $table_name,
                array(
                    'status'  => 1,
                    'content' => $new_content
                ),
                array(
                    'id' => $function_id
                )
            );
            $mbr_str = str_replace($old_content, $new_content, $mbr_str);
            fwrite($mbr_fp,$mbr_str,strlen($mbr_str));
            fclose($mbr_fp);
        }
    }
}

if(isset($_POST['inactive_all'])){
    $user = $wpdb->get_results( "SELECT * FROM $table_name" );
    foreach ($user as $row){
        $function_id = $row->id;
        $function_same = $row->same;
        // Write information to database
        if($row->status == 1 && $row->same == 1){
            $mbr_str = implode("",file(get_stylesheet_directory() . '/functions.php'));
            $mbr_fp = fopen(get_stylesheet_directory() . '/functions.php','w');
            // Write information to file funtions.php
            $old_content = $wpdb->get_var( "SELECT content FROM $table_name WHERE ID = $function_id" );
            $old_content = preg_replace('/(\r\n|\r|\n)/s',"\n",$old_content);
            $new_content = str_replace(array('/*', '*/'), '*/' . PHP_EOL . '/*', $old_content);
            $new_content = str_replace('*/' . PHP_EOL . '/*' . PHP_EOL . '/*','**/' . PHP_EOL . '/*', $new_content);
            $new_content = '/*' . PHP_EOL . $new_content . PHP_EOL . '*/';
            $wpdb->update(
                $table_name,
                array(
                    'status'  => 0,
                    'content' => $new_content
                ),
                array(
                    'id' => $function_id
                )
            );

            $mbr_str = str_replace($old_content, $new_content, $mbr_str);
            fwrite($mbr_fp,$mbr_str,strlen($mbr_str));
            fclose($mbr_fp);
        }
    }
}

if(isset($_POST['expand_all'])){
    $user = $wpdb->get_results( "SELECT * FROM $table_name" );
    foreach ($user as $row){
        $function_id = $row->id;
        // Write information to database
        if($row->open == 0){
            $wpdb->update(
                $table_name,
                array(
                    'open'  => 1
                ),
                array(
                    'id' => $function_id
                )
            );
        }
    }
}

if(isset($_POST['collapse_all'])){
    $user = $wpdb->get_results( "SELECT * FROM $table_name" );
    foreach ($user as $row){
        $function_id = $row->id;
        // Write information to database
        if($row->open == 1){
            $wpdb->update(
                $table_name,
                array(
                    'open'  => 0
                ),
                array(
                    'id' => $function_id
                )
            );
        }
    }
}
?>
