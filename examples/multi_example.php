<?php
/*
 *	Multi request usage example
 *
 */

require_once '../rue.php';
$r = new \Rue\rs_api();

//get_clan_list_light returns a name-only list of clan members sorted by rank
$clan_list = $r->get_clan_list_light("Wrack City");

//get_multi_activity returns the last 20 activity logs for all the player's in a list
$demo = $r->get_multi_activity($clan_list);

//this demo prints the last 20 logs for every clan member in 6.4 seconds
print_r($demo);
?>