<?php
require('config.php');
include_once('inc/php-kml/kml.php');

ini_set("date.timezone",$defaultTimeZone);
$dateLimt = extractDateParams();

// Selects all the rows before dateEnd and after dateStart
$query = 'SELECT * FROM latitudeRecorder WHERE UNIX_TIMESTAMP(`timestamp`) >=  \''.$dateLimt['start'].'\' AND UNIX_TIMESTAMP(`timestamp`) <=  \''.$dateLimt['end'].'\' ORDER BY timestamp';

$result = getDBResults($server, $username, $password, $database, $query);

$placemarks = array();
$coordinates = array();
$previousPlacemark = null;
while ($row = @mysql_fetch_assoc($result)){
    if($previousPlacemark != null && $previousPlacemark->Geometry->coordinates[0] == $row['longitude'] && $previousPlacemark->Geometry->coordinates[1] == $row['latitude']){
        if(!empty($previousPlacemark->TimePrimitive->when)){
            $timespan = new kml_TimeSpan($previousPlacemark->TimePrimitive->when, date('c', strtotime($row['timestamp'])));
            $placemark->set_TimePrimitive($timespan);
        }else {
            $timespan = new kml_TimeSpan($previousPlacemark->TimePrimitive->begin, date('c', strtotime($row['timestamp'])));
            $placemark->set_TimePrimitive($timespan);
        }
        
    }else {
        $placemark = createPlaceMark($row['id'], $row['reversedLocation'], $row['longitude'], $row['latitude'], $row['accurency'], $row['timestamp']);
        $placemarks[$row['id']] = $placemark;
        $previousPlacemark = $placemark;
        $coordinates[] = array($row['longitude'], $row['latitude']);
    }
}

$d = new kml_Document('Document');
foreach ($placemarks as $placemark) {
    $d->add_Schema($placemark);
}

$d->add_Schema(getRoutePlaceMark($coordinates));

$d->dump();

function getRoutePlaceMark($coordinates){
    $placemark = new kml_Placemark('The route');
    $linestring = new kml_LineString($coordinates);
    $placemark->set_Geometry($linestring);
    
    return $placemark;
}

function extractDateParams(){
    //By default, selecting the current day
    $date['start'] = new DateTime();
    $date['end'] = new DateTime();

    //Reading the dstart and dend from the get params
    //If dend is not present just select for the dsart day
    if(isset($_GET['dstart']) && strtotime($_GET['dstart']) != false){
        $date['start'] = new DateTime(date('Y-m-d', strtotime($_GET['dstart'])));
        if(isset($_GET['dend']) && strtotime($_GET['dend']) != false){
            $date['end'] = new DateTime(date('Y-m-d', strtotime($_GET['dend'])));
        }else{
            $date['end'] = new DateTime(date('Y-m-d', strtotime($_GET['dstart'])));
        }
    }

    //Setting the times for the days
    $date['start']->setTime(0, 0, 0);
    $date['end']->setTime(23, 59, 59);

    
    //Transforming to timestamps (Datetime->gettimestamp comes only with php 5.3)
    $date['start'] = strtotime($date['start']->format("Y-m-d H:i:s"));
    $date['end'] = strtotime($date['end']->format("Y-m-d H:i:s"));
    return $date;
}

function getDBResults($server, $username, $password, $database, $query){
    // Opens a connection to a MySQL server.
    $connection = mysql_connect ($server, $username, $password);

    if (!$connection){
        die('Not connected : ' . mysql_error());
    }
    // Sets the active MySQL database.
    $db_selected = mysql_select_db($database, $connection);

    if (!$db_selected){
        die('Can\'t use db : ' . mysql_error());
    }
    
    $result = mysql_query($query);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    return $result;
}

function createPlaceMark($id, $reversedLocation, $longitude, $latitude, $accurency, $timestamp){
    $placemark = new kml_Placemark(($reversedLocation), new kml_Point($longitude, $latitude));
    $timestamp = new kml_TimeStamp(date('c', strtotime($timestamp)));
    $placemark->set_TimePrimitive($timestamp);
    $placemark->set_id($id);
    $placemark->set_description('accurency: ' . $accurency . 'm');
    return $placemark;
}
?>