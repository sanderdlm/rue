<?php
require_once 'rue.php';
require_once 'rollingCurl.php';

$r = new Rue();

$list = $r->get_clan_list_light("Wrack City");
$demo = $r->get_multi_activity($list);

echo "<pre>";
var_dump($demo);
echo "</pre>";
?>