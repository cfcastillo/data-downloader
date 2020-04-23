<?php
require_once(dirname(__DIR__, 1) . "/Classes/Business.php");
require_once("uuid.php");
require_once("configs.php");

use CFiniello\DataDownloader\Business;

// The pdo object has been created for you.
require_once("/etc/apache2/capstone-mysql/Secrets.php");
$secrets =  new Secrets("/etc/apache2/capstone-mysql/cohort28/cohort28testing.ini");
$pdo = $secrets->getPdoObject();

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


for ($offset = 0; $offset < 100; $offset = $offset + 20) {

    $ch = curl_init('https://api.yelp.com/v3/businesses/search?term=restaurants&location=NM&offset=' . $offset);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $businesses = json_decode($result)->businesses;

    foreach ($businesses as $business) {
        echo($business->id . "<br>");
        echo($business->name . "<br>");
        echo($business->url . "<br>");
        echo($business->coordinates->latitude . "<br>");
        echo($business->coordinates->longitude . "<br>");
        echo "<br>";

        $bus = new Business(generateUuidV4(), $business->name, $business->url, $business->id, $business->coordinates->latitude, $business->coordinates->longitude);
        $bus->insert($pdo);
    }
}