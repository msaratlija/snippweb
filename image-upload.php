<?php

include dirname(__FILE__)."/image-sql.php";


//#replace path to images folder
$images_dir         = "uploads/";
$target_file_dir    = $images_dir . basename($_FILES["fileToUpload"]["name"]); 
$target_file_tmp    = $_FILES["fileToUpload"]["tmp_name"];                     
$image_unique_name  = null;
$tmp_image          = null;
$image_type         = null;

$uploadOk = 0;
$acceptedTypes      = array("image/png", "image/gif", "image/jpeg", "image/jpg");
$echo_return        = array (
    "image_url"         => "",
    "error_message"     => "",
    "image_type"        => "",
    "image_html"        => "",
    "image_size_msg"    => ""
);

$user_agent         = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "NULL";

$user_image_name    = basename($_FILES["fileToUpload"]["name"]);
$user_image_hash    = "NULL";
$user_agent         = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "NULL";
$user_ip            = getenv('HTTP_CLIENT_IP')?:
                      getenv('HTTP_X_FORWARDED_FOR')?:
                      getenv('HTTP_X_FORWARDED')?:
                      getenv('HTTP_FORWARDED_FOR')?:
                      getenv('HTTP_FORWARDED')?:
                      getenv('REMOTE_ADDR');
$user_browser       = getBrowser();
$user_OS            = getOS();
$user_lang          = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "NULL";
$user_image         = "image";
$user_desktop       = strpos($_FILES["fileToUpload"]["name"], "desk_app");
$user_time          = isset($_POST["time"]) ? $_POST["time"] : "NULL";

$query_image_columns  = "ID, ";
$query_image_columns .= "IMAGE_NAME, ";
$query_image_columns .= "IMAGE_HASH, ";
$query_image_columns .= "USER_IP, ";
$query_image_columns .= "USER_BROWSER, ";
$query_image_columns .= "OS_PLATFORM, ";
$query_image_columns .= "IS_IMAGE, ";
$query_image_columns .= "LANG, ";
$query_image_columns .= "DESKTOP_APP, ";
$query_image_columns .= "TIME";


// Check if image file is defined
if(isset($_FILES['fileToUpload'])) {
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $check = false;
    if(!empty($target_file_tmp)){
        $image_type = finfo_file($finfo, $target_file_tmp);
        $check      = getimagesize($target_file_tmp);
    }


    // Check if image file is a actual image or fake image
    if($check !== false && in_array( $image_type, $acceptedTypes)) {
        $uploadOk = 1;
        $echo_return['image_type'] = $image_type;
    } else {
        $user_image = "NOT an image";
        $echo_return['error_message'] .= "File is not an image. ";
    }
    finfo_close($finfo);
}



//Check image file size
if ($uploadOk == 1 && $_FILES["fileToUpload"]["size"] < 3145728) {
    $echo_return['image_size_msg'] = "File size is under 3 MB.";

    $tmp_image         = imageCreateFromFile($target_file_tmp, $image_type);
    $image_unique_name = generateUniqueName($images_dir);
    $user_image_hash = $image_unique_name;
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
}

echo json_encode($echo_return);

    $query_image_values = 
    "'NULL', '"
    .$user_image_name."', '"
    .$user_image_hash."', '"
    .$user_ip."', '"
    .$user_browser."', '"
    .$user_OS."', '"
    .$user_image."', '"
    .$user_lang."', '"
    .$user_desktop."', '"
    .$user_time. "'";

    $sql_insert_query = "SET SESSION time_zone = '+1:00'";
    $result = db_query($sql_insert_query);
    $sql_insert_query  = "INSERT INTO IMAGE_UPLOAD_DATA (".$query_image_columns.") VALUES (".$query_image_values.");";
    $result = db_query($sql_insert_query);
    
    if($result === false) {
        $error = db_error();
        error_log($error);
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

function getOS() { 

    global $user_agent;

    $os_platform    =   "Unknown OS Platform";

    $os_array       =   array(
                            '/windows nt 10/i'     =>  'Windows 10',
                            '/windows nt 6.3/i'     =>  'Windows 8.1',
                            '/windows nt 6.2/i'     =>  'Windows 8',
                            '/windows nt 6.1/i'     =>  'Windows 7',
                            '/windows nt 6.0/i'     =>  'Windows Vista',
                            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                            '/windows nt 5.1/i'     =>  'Windows XP',
                            '/windows xp/i'         =>  'Windows XP',
                            '/windows nt 5.0/i'     =>  'Windows 2000',
                            '/windows me/i'         =>  'Windows ME',
                            '/win98/i'              =>  'Windows 98',
                            '/win95/i'              =>  'Windows 95',
                            '/win16/i'              =>  'Windows 3.11',
                            '/macintosh|mac os x/i' =>  'Mac OS X',
                            '/mac_powerpc/i'        =>  'Mac OS 9',
                            '/linux/i'              =>  'Linux',
                            '/ubuntu/i'             =>  'Ubuntu',
                            '/iphone/i'             =>  'iPhone',
                            '/ipod/i'               =>  'iPod',
                            '/ipad/i'               =>  'iPad',
                            '/android/i'            =>  'Android',
                            '/blackberry/i'         =>  'BlackBerry',
                            '/webos/i'              =>  'Mobile'
                        );

    foreach ($os_array as $regex => $value) { 
        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }
    }   
    return $os_platform;
}

function getBrowser() {
    global $user_agent;
    $browser        =   "Unknown Browser";
    $browser_array  =   array(
                            '/msie/i'       =>  'Internet Explorer',
                            '/firefox/i'    =>  'Firefox',
                            '/safari/i'     =>  'Safari',
                            '/chrome/i'     =>  'Chrome',
                            '/edge/i'       =>  'Edge',
                            '/opera/i'      =>  'Opera',
                            '/netscape/i'   =>  'Netscape',
                            '/maxthon/i'    =>  'Maxthon',
                            '/konqueror/i'  =>  'Konqueror',
                            '/mobile/i'     =>  'Handheld Browser'
                        );

    foreach ($browser_array as $regex => $value) { 
        if (preg_match($regex, $user_agent)) {
            $browser    =   $value;
        }
    }
    return $browser;
}

?>