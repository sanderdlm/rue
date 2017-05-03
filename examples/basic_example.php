<?php
/*
 *	Basic usage example
 *
 */

require_once '../rue.php';
$r = new \Rue\rs_api();

$demo = $r->get_player_profile("Chet Faker");

print_r($demo);
?>