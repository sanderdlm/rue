<?php
require_once 'rue.php';

$r = new Rue();
//setting the alt/pug account to authenticate with is required for this example
$r->setPug("yourpugemail@gmail.com", "yourpugpassword", "yourpugname");

//single player
$player_name = "W0OO0O0OO00O";
$player_profile = $r->get_player_profile($player_name);
$player_details = $r->get_player_details($player_name, true);

if($player_profile == 'NOT_A_MEMBER'){
    if($player_details->member == true){
        echo 'banned';
    }
}else{
    echo 'clean';
}

//entire clan
$clan_list = $r->get_clan_list_light("PvM Addicts");
$clan_profiles = $r->get_multi_profile($clan_list);
$clan_details = $r->get_multi_details($clan_list, true);

foreach($clan_profiles as &$clan_member){
	if($clan_member->profile == 'NOT_A_MEMBER'){
		foreach($clan_details as $clan_member_details){
			if($clan_member->name == $clan_member_details->name){
				if($clan_member_details->member == true){
				    $clan_member->ban_status = 'banned';
				}
			}
		}
	}else{
	    $clan_member->ban_status = 'clean';
	}
	
	//remove the profile for easier viewing
	unset($clan_member->profile);
}

echo "<pre>";
print_r($clan_profiles);
?>