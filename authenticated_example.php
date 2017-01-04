<?php
require_once 'rue.php';

$r = new Rue();

//optional dependency;enables the use of session tokens
$r->setPug("yourpugemail@gmail.com", "yourpugpassword", "yourpugname");

$demo = $r->get_player_details("Chet Faker", true);

echo "<pre>";
print_r($demo);
?>