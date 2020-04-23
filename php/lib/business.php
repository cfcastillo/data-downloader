<?php
//require_once(dirname(__DIR__, 1) . "/Classes/Business.php");
//require_once(dirname(__DIR__, 1) . "uuid.php");
require_once("configs.php");

// The pdo object has been created for you.
//require_once("/etc/apache2/capstone-mysql/Secrets.php");
//$secrets =  new Secrets("/etc/apache2/capstone-mysql/cohort28/cohort28testing.ini");
//$pdo = $secrets->getPdoObject();

//use CFiniello\DataDownloader\Business;

//this simulates the front end of a web site. This would actually come from HTML, Javascript, etc.
//$businessId = generateUuidV4();
$businessName = null;
$businessYelpUrl = null;
$businessYelpId = null;
$businessLat = 0;
$businessLong = 0;

//cURL - https://www.php.net/manual/en/function.curl-setopt.php
$authorization = "Authorization: Bearer " . $yelpToken;
$ch = curl_init('https://api.yelp.com/v3/businesses/search?term=restaurants&location=NM');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
var_dump(json_decode($result));

