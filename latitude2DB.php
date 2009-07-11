<?php
require('config.php');

$url = 'http://www.google.com/latitude/apps/badge/api?user='.$latitudeUserId.'&type=json';

// We get the content
$content = file_get_contents( $url );

// We convert the JSON to an object
$json = json_decode( $content );

$coord = $json->features[0]->geometry->coordinates;
$accurency = $json->features[0]->properties->accuracyInMeters;
$timeStamp = $json->features[0]->properties->timeStamp;
$reverseLocation = $json->features[0]->properties->reverseGeocode;

if ( ! $coord ) 
    exit('This user doesn\'t exist.');

$date = date('Y-m-d H:i:s', $timeStamp );
$lat = $coord[1];
$lon = $coord[0];

$con = mysql_connect($server, $username, $password);
if (!$con){
    die('Could not connect: ' . mysql_error());
}

mysql_select_db($database, $con);

$sqlquery = "INSERT INTO latitudeRecorder (timestamp, latitude, longitude, accurency, reversedLocation)
VALUES ('$date', '$lat', '$lon', '$accurency', '$reverseLocation')";

mysql_query($sqlquery);
mysql_close($con);

echo "I have done this query: " . $sqlquery;