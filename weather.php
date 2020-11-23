<?php

// Import the configs (API key, database connections ect...)
require 'config_local.php';

# Make everybody able to access this API, even some scripts which are not part
# of quickpi or france-ioi, be carefull about DDOS attacks or stuff like that
# with this. But I don't think that there is other better ways of doing it
# because the board need to access to the api from anywhere.
header('Access-Control-Allow-Origin: *');

// We need to allow opening of url
ini_set("allow_url_fopen", 1);

// The openweathermap API
$url = "http://api.openweathermap.org/data/2.5/weather";


// This function build the url from a town according to var $url and
// $apiKey.
function getUrlTown($town) {
    global $url, $apiKey;
    return $url . "?q=" . $town . "&appid=" . $apiKey . "&units=metric";
}

// This function update the temperature present in database. Call it when
// isTemperatureUpToDate return FALSE.
function updateTemperatureDB() {
    global $conn, $getLastUpdateStatement, $town;
    $finalUrl = getUrlTown($town);

    $json = file_get_contents($finalUrl);
    $obj = json_decode($json);

    // we use this function to sanitize the input in case someone malicious take
    // control of the weather api
    $newTemp = $obj->main->temp;
    if (!is_numeric($newTemp)) {
        die("The value got from the api is not an int. The API might be compromised.");
    }

    if (!$getLastUpdateStatement->execute())
        die("Unable to execute getLastUpdateStatement");

    $result = $getLastUpdateStatement->fetchAll();

    // if the key does not exists then we insert it
    if ($getLastUpdateStatement->rowCount() === 0) {
        $insertStatement = $conn->prepare("INSERT INTO temperatures (town, temp)
            VALUE (:town, :temp)");

        $insertStatement->bindParam(':town', $town);
        $insertStatement->bindParam(':temp', $newTemp);
        if (!$insertStatement->execute())
            die("Unable to execute insertStatement");
    } else {
        $updateStatement = $conn->prepare("UPDATE temperatures
            SET temp=:temp, last_update=CURRENT_TIMESTAMP
            WHERE town=:town");

        $updateStatement->bindParam(':temp', $newTemp);
        $updateStatement->bindParam(':town', $town);
        if (!$updateStatement->execute())
            die("Unable to execute updateStatement");
    }
}

// This function check the temperature present in the database.
// It return FALSE if there is no temperature in database or if the last update
// of the temperature is lower than the timer we wanted.
function isTemperatureUpToDate() {
    global $getLastUpdateStatement, $timeBetweenUpdate;

    if (!$getLastUpdateStatement->execute())
        die("Unable to execute getLastUpdateStatement)");

    $result = $getLastUpdateStatement->fetch();

    // if the key does not exists
    if ($getLastUpdateStatement->rowCount() === 0)
        return false;

    $time = strtotime($result["last_update"]);

    $currtime = time();

    // if the time is lower than time between update
    return ($currtime - $time) <= $timeBetweenUpdate * 60;
}

function getTemperature() {
    global $getTempStatement;
    if (!isTemperatureUpToDate())
        updateTemperatureDB();

    if (!$getTempStatement->execute())
        die("Unable to execute getTempStatement");
    $result = $getTempStatement->fetch();
    return $result["temp"];
}

try {
    $conn = new PDO("mysql:host=$servername;dbname:$databaseName", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->query("use $databaseName");

    $q = $_GET['q'];
    if (in_array($q, $supportedTowns, TRUE)) {
        // check if table exists else create it
        try {
            $tmp = $conn->query("SELECT 1 FROM temperatures LIMIT 1");
        } catch (Exception $e) {
            $createTable = "CREATE TABLE temperatures (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                town VARCHAR(50) NOT NULL,
                temp FLOAT(6) NOT NULL,
                last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";

            $conn->exec($createTable);
        }

        $town = $q;

        $getTempStatement = $conn->prepare("SELECT temp FROM temperatures WHERE town=:town LIMIT 1");
        $getLastUpdateStatement = $conn->prepare("SELECT last_update FROM temperatures WHERE town=:town LIMIT 1");

        $getTempStatement->bindParam(':town', $town);
        $getLastUpdateStatement->bindParam(':town', $town);

        echo getTemperature();

    } elseif (strcmp($q, "supportedtowns" == 0)) {
        echo json_encode($supportedTowns);
    } else {
        echo "invalid";
    }

} catch(PDOException $e) {
    die("PDO Error: " . $e->getMessage());
}

?>
