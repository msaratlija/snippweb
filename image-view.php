<?php


if( isset($_GET["image"])){
    //#replace with server path to images folder
    $fileDir = 'uploads/';
    $output = "<img src= 'uploads/".$_GET["image"].".png' >";
    echo $output;
}


?>