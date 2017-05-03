<?php
/*
 *	Advanced example (both authenticated & multi requests)
 *	
 *	This demo will check an entire clan for banned members.
 *	You can vrify this by asking both Runemetrics & the details endpoint for the
 *	player's membership status. RM will return NOT_A_MEMBER for banned players,
 *	but the details endpoint still has member = true set for that player.
 *
 */

require_once '../rue.php';
$r = new \Rue\rs_api();

//setting the alt account to authenticate your requests with is required for this example to work
$r->set_pug("yourpugemail@gmail.com", "yourpugpassword", "yourpugname");

//set the clan name
$clan_name = "Raccoons";
//grab the list of all the clan's members
$clan_list = $r->get_clan_list_light($clan_name);
//get the RM profile for every member (if public)
$clan_profiles = $r->get_multi_profile($clan_list);
//get the player details data for every member
$clan_details = $r->get_multi_details($clan_list, true);

//prepare the output array with statistics
$output = array();
$output['clan_name'] = $clan_name;
$output['members_count'] = count($clan_list);
$output['banned_members_count'] = 0;
$output['banned_members'] = array();

//start looping over every player profile
foreach($clan_profiles as &$clan_member){
	//check if the player shows up as F2P on Runemetrics
	if($clan_member->profile == 'NOT_A_MEMBER'){
		//if he does, grab that player's detail data from the array
		foreach($clan_details as $clan_member_details){
			if($clan_member->name == $clan_member_details->name){
				//if his detail data still lists his membership as active
				if($clan_member_details->member == true){
					//rip ur account
				    array_push($output['banned_members'], $clan_member->name);
				    //count up
				    $output['banned_members_count']++;
				}
			}
		}
	}
}

print_r($output);
?>