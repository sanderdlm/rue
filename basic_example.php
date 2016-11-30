<?php
require_once 'rue.php';

$r = new Rue();

$demo = $r->get_player_skills("Chet Faker");

echo "<pre>";
var_dump($demo);
echo "</pre>";
?>