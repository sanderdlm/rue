<?php
require_once 'rue.php';

$r = new Rue();

$list = $r->get_clan_list_light("Shuu Zone");
$demo = $r->get_multi_activity($list);

echo "<pre>";
print_r($demo);
echo "</pre>";
?>