<?php
/*
 *	Example with authenticated requests
 *
 */

require_once '../rue.php';
$r = new \Rue\rs_api();

//setPug allows you to pass the login credentials of any alt account
//the lib will then use these credentials to simulate a login to runescape.com
//and use the returned session token to get more data
$r->set_pug("yourpugemail@gmail.com", "yourpugpassword", "yourpugname");

//by passing 'true' as the second parameter for get_player_details you will
//get both the 'online' and the 'world' properties in the response object
$demo = $r->get_player_details("Chet Faker", true);

print_r($demo);
?>