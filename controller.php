<?php
include 'db_api.php';

$db_api = new db_api();

try {
    $multipleStations = $db_api->getStation('Magdeburg HBF');
    echo 'Number of stations: ' . count($multipleStations->stations) . '<br>';

    for ($i = 0; $i < count($multipleStations->stations); $i++) {
        echo 'Name      : ' . $multipleStations->stations[$i]->name . '<br>';
        echo 'DS100     : ' . $multipleStations->stations[$i]->ds100 . '<br>';
        echo 'EVA       : ' . $multipleStations->stations[$i]->eva . '<br>';
        echo 'Meta      : ' . $multipleStations->stations[$i]->meta . '<br>';
        echo 'DB        : ' . $multipleStations->stations[$i]->db . '<br>';
        echo 'Creationts: ' . $multipleStations->stations[$i]->creationts . '<br>';
    }

} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

//API:
switch ($_POST['action']) {
    case 'getStation':
        if (!isset($_POST['station'])) {
            echo 'Station name is required';
            die();
        }
        try {
            $station = $db_api->getStation($_POST['station']);
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
        echo json_encode($station);
        break;
    default:
        echo 'Invalid action';
        break;
}