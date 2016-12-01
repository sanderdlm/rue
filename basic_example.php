<?php
require_once 'rue.php';

$r = new Rue();

$demo = $r->get_player_hiscores("Snow Faker");

echo "<pre>";
print_r($demo);
echo "</pre>";
?>