<?php

include dirname(__FILE__)."/image-sql.php";

//file data
//#replace path to images folder
$images_dir = "uploads/";
$target_file_dir = $images_dir . basename($_FILES["fileToUpload"]["name"]); // dir/image_name.ext
$target_file_tmp = $_FILES["fileToUpload"]["tmp_name"];                     //temp filename of file 
$tmp_image;
$image_type;
$image_name = basename($_FILES["fileToUpload"]["name"]);
$image_unique_name;            
//$imageFileType = pathinfo($target_file_dir,PATHINFO_EXTENSION);


$uploadOk = 0;
$acceptedTypes = array("image/png", "image/gif", "image/jpeg", "image/jpg");
$echo_return = array (
    "image_url"         => "",
    "error_message"     => "",
    "image_type"        => "",
    "image_html"        => "",
    "image_size_msg"    => ""
);



// Check if image file is defined
if(isset($_FILES['fileToUpload'])) {

    /*
    $result = db_query("INSERT INTO test_table (NAME, TEST1, TEST2) VALUES ('name textX', 'test1 textX', 'test2 textX');");
    if($result === false) {
    $error = db_error();
    
    // Send the error to an administrator, log to a file, etc.
    }
    */

    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $image_type = finfo_file($finfo, $target_file_tmp);
    $check = getimagesize($target_file_tmp);

    // Check if image file is a actual image or fake image
    if($check !== false && in_array( $image_type, $acceptedTypes)) {
        $uploadOk = 1;
        $echo_return['image_type'] = $image_type;
    } else {
        $echo_return['error_message'] .= "File is not an image. ";
    }
    finfo_close($finfo);
}



//Check image file size
if ($uploadOk == 1 && $_FILES["fileToUpload"]["size"] < 3145728) {
    $echo_return['image_size_msg'] = "File size is under 3 MB.";

    $tmp_image = imageCreateFromFile($target_file_tmp, $image_type);
    $image_unique_name = generateUniqueName($images_dir);
    $tmp_dir_to_save = $images_dir . $image_unique_name . ".png";
        
    if (imagepng($tmp_image, $tmp_dir_to_save)) {
        $echo_return['uploadOk'] = $uploadOk;
        //#replace dirname($_SERVER['PHP_SELF']) for server hosting -> ex.com/123
        $echo_return['image_url'] = "http://".$_SERVER['HTTP_HOST']. "/".dirname($_SERVER['PHP_SELF']).'/'.$image_unique_name;
        $echo_return ['image_html'] = "<img src='". $tmp_dir_to_save . "' class='preview'>";
    } else {
        $echo_return['error_message'] .= "Sorry, there was an error uploading your file."; 
    }
    imagedestroy($tmp_image);
    echo json_encode($echo_return);
}

function generateUniqueName($img_dir){
    do{
        $img_u_n = uniqid(mt_rand());
    }while(file_exists($img_dir . "png"));
    return substr($img_u_n, -8);
}

function imageCreateFromFile($tmp_file, $file_type) {
    if (!file_exists($tmp_file)) {
        throw new InvalidArgumentException('File "'.$filename.'" not found.');
    }
    switch ($file_type) {
        case 'image/jpg':
        case 'image/jpeg':
            return imagecreatefromjpeg($tmp_file);
        break;

        case 'image/png':
            return imagecreatefrompng($tmp_file);
        break;

        case 'image/gif':
            return imagecreatefromgif($tmp_file);
        break;

        default:
            //throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
        break;
    }
}

?>