<?php
require_once("../../../wp-load.php");

$check_given = $_GET['c'];
if(!$check_given) die();
$post_id = $_GET['p'];
if ( !is_numeric($post_id) ) die();
$image_ids_string = $_GET['is'];
$image_ids = explode(',',$image_ids_string);
$image_file_paths = array();
foreach ($image_ids as $id) {
  if ( !is_numeric($id) ) die();
  $file_path = get_attached_file($id);
  if ($file_path) {
    $image_file_paths[] = $file_path;
  } else {
    die();
  }
}

// Security check
$post = get_post($post_id);
$check = sha1(
  implode(',',$image_ids)
  .implode(',',$image_file_paths)
  .'1L1Dqdhw0w0zha-VqjaaaiCLnJim1YLJrU3fKZf61uR'
  .get_the_title($post).$post->ID.$post->post_modified.$post->post_name
);
if ($check !== $check_given) die();


$uploads_dir_array = wp_upload_dir('');
$uploads_dir_basedir = $uploads_dir_array['basedir'];
$root_name = 'lqu-'.sanitize_file_name($post->post_name);
$zip_root_name = $root_name.'-images-'.date("Y-m-d-H-i-s");
$zip_file_name = $zip_root_name.'.zip';
$zip = new ZipArchive;
$zip->open($zip_file_name, ZipArchive::CREATE);
$i = 1;
$len = count($image_file_paths);
$digits = strlen((string)$len);
if ($digits < 2) $digits = 2;
foreach ($image_file_paths as $file) {
  //OLD:
  //$new_file_name = sprintf('%0'.$digits.'d',$i).'-'.basename($file);
  //NEW:
  $extention = $filename = substr( strrchr(basename($file),"."), 1);
  $new_file_name = $root_name.'-'.sprintf('%0'.$digits.'d',$i).'.'.$extention;
  //ADD FILE:
  $zip->addFile( $file, $zip_root_name.'/'.sanitize_file_name($new_file_name) );
  // if folders are wanted, use str_replace($uploads_dir_basedir,'',$file);
  $i++;
}
$zip->close();
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zip_file_name);
header('Content-Length: ' . filesize($zip_file_name));


// these may not be needed
header("Pragma: no-cache");
header("Expires: 0");
ob_clean();
flush();

readfile($zip_file_name);

// delete file when done
ignore_user_abort(true);
unlink($zip_file_name);

exit();

?>
