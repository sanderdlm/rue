<?php
require_once 'rue.php';

$r = new Rue();

//optional dependency;enables the use of session tokens
$r->setPug("pugemail@gmail.com", "pugpassword", "pugname");

$demo = $r->get_player_details("Snow Faker", true);

echo "<pre>";
print_r($demo);
echo "</pre>";
?>