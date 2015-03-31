<?php

require_once('WebPicasaPhotoGrabber.php');

$userId = '107035540163974889412';
$grebber = new WebPicasaPhotoGrabber();
$grebber->saveAllByUserId($userId);
