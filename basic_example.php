<?php
require_once 'rue.php';

$r = new Rue();

$demo = $r->get_player_profile("Chet Faker");

echo "<pre>";
print_r($demo);
?>