
<?php
// Load WordPress environment
require_once( dirname(__FILE__) . '/wp-load.php' );


$dates = ['crimedata-231222.csv', 'crimedata-140323.csv', 'crimedata-150523.csv', 'crimedata-101122.csv'];

$dateObjects = array_map(function($date) {
    $datePart = substr($date, 5, 6); 
    return DateTime::createFromFormat('dmY', $datePart);
}, $dates);

usort($dateObjects, function($a, $b) {
    return $b->getTimestamp() - $a->getTimestamp();
});

$latestDate = $dateObjects[0];
echo $latestDate;