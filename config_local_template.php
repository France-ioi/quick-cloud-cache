<?php           #######################
                ##                   ##
                ##  Editable values  ##
                ##                   ##
                #######################

# The values below can be changed to your needs
# Once you finished editing you can rename this file to config_local.php and
# place it in the same folder as your file weather.php

# The array containing all supported towns to get the temperature from
# If you want to support another town, simply add the name of the town here.
$supportedTowns = array("Paris", "Madrid");

# Time between update
# This time decide when we need to update a value of the database. It is time in
# minutes. For example if the time is equal to 60, then we update the cache if
# the temperature in less recent than 60 minutes.
$timeBetweenUpdate = 60;

# The api key from the website: https://openweathermap.org/api
# Modify it with your own key (a key for quickpi, this one is registered with
# my personal email, we need one which is accessible by everyone)
$apiKey = "your weather api key here";

# The MySql connections parameters
# you must specify the database in which it will be stored also
$servername = "localhost";
$username = "username";
$password = "password";
$databaseName = "quickpiweather";



?>
