<?php
require_once 'rue.php';

$r = new Rue();

$clan_list = $r->get_clan_list_light("Wrack City");
$demo = $r->get_multi_details($clan_list, true);

echo "<pre>";
print_r($demo);
echo "</pre>";
?>