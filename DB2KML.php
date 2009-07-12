<?php
require('config.php');

ini_set("date.timezone",$defaultTimeZone);

//By default, selecting the current day
$dateStart = new DateTime();
$dateEnd = new DateTime();

//Reading the dstart and dend from the get params
//If dend is not present just select for the dsart day
if(isset($_GET['dstart']) && strtotime($_GET['dstart']) != false){
    $dateStart = new DateTime(date('Y-m-d', strtotime($_GET['dstart'])));
    if(isset($_GET['dend']) && strtotime($_GET['dend']) != false){
        $dateEnd = new DateTime(date('Y-m-d', strtotime($_GET['dend'])));
    }else{
        $dateEnd = new DateTime(date('Y-m-d', strtotime($_GET['dstart'])));
    }
}

//Setting the times for the days
$dateStart->setTime(0, 0, 0);
$dateEnd->setTime(23, 59, 59);

//Transforming to timestamps (Datetime->gettimestamp comes only with php 5.3)
$dateStart = strtotime($dateStart->format("Y-m-d H:i:s"));
$dateEnd = strtotime($dateEnd->format("Y-m-d H:i:s"));

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

// Selects all the rows before dateEnd and after dateStart
$query = 'SELECT * FROM latitudeRecorder WHERE UNIX_TIMESTAMP(`timestamp`) >=  \''.$dateStart.'\' AND UNIX_TIMESTAMP(`timestamp`) <=  \''.$dateEnd.'\'';
$result = mysql_query($query);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

// Creates the Document.
$dom = new DOMDocument('1.0', 'UTF-8');

// Creates the root KML element and appends it to the root document.
$node = $dom->createElementNS('http://earth.google.com/kml/2.1', 'kml');
$parNode = $dom->appendChild($node);

// Creates a KML Document element and append it to the KML element.
$dnode = $dom->createElement('Document');
$docNode = $parNode->appendChild($dnode);

// Creates a for the user, and append the elements to the Document element.
$userStyleNode = $dom->createElement('Style');
$userStyleNode->setAttribute('id', $latitudeUserId);
$userIconstyleNode = $dom->createElement('IconStyle');
$userIconNode = $dom->createElement('Icon');
$userHref = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon63.png');
$userIconNode->appendChild($userHref);
$userIconstyleNode->appendChild($userIconNode);
$userStyleNode->appendChild($userIconstyleNode);
$docNode->appendChild($userStyleNode);

// Iterates through the MySQL results, creating one Placemark for each row.
while ($row = @mysql_fetch_assoc($result)){
    // Creates a Placemark and append it to the Document.
    
    $node = $dom->createElement('Placemark');
    $placeNode = $docNode->appendChild($node);
    
    // Creates an id attribute and assign it the value of id column.
    $placeNode->setAttribute('id', 'placemark' . $row['id']);
    
    // Create name, and description elements and assigns them the values of the name and address columns from the results.
    $nameNode = $dom->createElement('name',htmlspecialchars_decode($row['reversedLocation']));
    $placeNode->appendChild($nameNode);
    $descNode = $dom->createElement('description', 'accurency: ' . $row['accurency'] . 'm');
    $placeNode->appendChild($descNode);
    $styleUrl = $dom->createElement('styleUrl', '#' . $latitudeUserId . 'Style');
    $placeNode->appendChild($styleUrl);
    
    $timestampNode = $dom->createElement('TimeStamp');
    $whenNode = $dom->createElement('when', date('c', strtotime($row['timestamp'])));
    $timestampNode->appendChild($whenNode);
    $placeNode->appendChild($timestampNode);
    
    // Creates a Point element.
    $pointNode = $dom->createElement('Point');
    $placeNode->appendChild($pointNode);
    
    // Creates a coordinates element and gives it the value of the lng and lat columns from the results.
    $coorStr = $row['longitude'] . ','  . $row['latitude'];
    $coorNode = $dom->createElement('coordinates', $coorStr);
    $pointNode->appendChild($coorNode);
}

$kmlOutput = $dom->saveXML();
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>