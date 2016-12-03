<?php
/*
        ---------- Rue 1.0.0 -----------
        An easier way to pull data from the
        Runemetrics & Runescape end-points.

            Copyright (c) 2016 @RSChetFaker
                    License: MIT
        https://github.com/chetfakerrs/Rue
*/  

Class Rue {
    private $_pugName;
    private $_pugLogin;
    private $_pugPassword;
    private $_sessionToken;
    private $_maxConcurrent = 10;
    private $_multi_container;
    private $requests = [];
    private $skill_list = ["Overall", "Attack", "Defence", "Strength", "Hitpoints", "Ranged", "Prayer", "Magic", "Cooking", "Woodcutting", "Fletching", "Fishing", "Firemaking", "Crafting", "Smithing", "Mining", "Herblore", "Agility", "Thieving", "Slayer", "Farming", "Runecrafting", "Hunter", "Construction", "Summoning", "Dungeoneering", "Divination", "Invention", "Bounty Hunter", "B.H. Rogues", "Dominion Tower", "The Crucible", "Castle Wars games", "B.A. Attackers", "B.A. Defenders", "B.A. Collectors", "B.A. Healers", "Duel Tournament", "Mobilising Armies", "Conquest", "Fist of Guthix", "GG: Athletics", "GG: Resource Race", "WE2: Armadyl Lifetime Contribution", "WE2: Bandos Lifetime Contribution", "WE2: Armadyl PvP kills", "WE2: Bandos PvP kills", "Heist Guard Level", "Heist Robber Level", "CFP: 5 game average", "AF15: Cow Tipping", "AF15: Rats killed after the miniquest"];
    public $request_data = [];

    /*
     *  Constructor & dependency injection functions
     */

    function __construct() {
        $this->curl = curl_init();
        $this->request_data['total'] = 0;
        $this->request_data['failed'] = 0;
        $this->request_data['success'] = 0;
        $this->request_data['profile_error'] = 0;
        $this->request_data['success_rate'] = 0;
    }

    public function setPug($email, $password, $username){
        $this->_pugName = $username;
        $this->_pugLogin = $email;
        $this->_pugPassword = $password;
    }

    /*
     *  Helper functions
     */

    private function trim_rm_callback($object){
        $object = substr($object, 21);
        $object = substr($object, 0, -3);
        $object = json_decode($object);
        return $object;
    }

    private function normalize_name($string){
        $string = strtolower($string);
        $string = utf8_encode($string);
        $string = htmlentities($string);
        $string = str_replace(' ', '_', $string);
        return $string;
    }

    private function normalize_clan_name($string){
        $string = strtolower($string);
        $string = utf8_encode($string);
        $string = htmlentities($string);
        $string = str_replace(' ', '+', $string);
        return $string;
    }

    /**
     * Runemetrics end-point wrappers
     * @param string $player_name (optional: boolean $logged_in - with pug account set)
     * @return array
     */

    public function get_player_profile($player_name){
        $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$this->normalize_name($player_name).'&activities=20';
        $result = json_decode($this->get_url($url));
        if(isset($result->error)){
            return $result->error;
        }else{
            return $result;
        } 
    }

    public function get_player_activity($player_name){
        $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$this->normalize_name($player_name).'&activities=20';
        $result = json_decode($this->get_url($url));
        if(isset($result->error)){
            return $result->error;
        }else{
            return $result->activities;
        } 
    }

    public function get_player_skills($player_name){
        $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$this->normalize_name($player_name).'&activities=20';
        $result = json_decode($this->get_url($url));
        if(isset($result->error)){
            return $result->error;
        }else{
            return $result->skillvalues;
        } 
    }

    public function get_player_details($player_name, $logged_in = false){
        $url = 'http://services.runescape.com/m=website-data/playerDetails.ws?membership=true&names=["'.$this->normalize_name($player_name).'"]&callback=angular.callbacks._0';
        $result = $this->get_url($url, $logged_in);
        $result = $this->trim_rm_callback($result)[0];
        return $result;
    }

    public function get_player_quests($player_name){
        $url = 'https://apps.runescape.com/runemetrics/quests?user='.$this->normalize_name($player_name);
        $result = json_decode($this->get_url($url));
        if(isset($result->error)){
            return $result->error;
        }else{
            return $result->quests;
        } 
    }

    public function get_player_avatar($player_name){
        $url = 'http://services.runescape.com/m=avatar-rs/'.$this->normalize_name($player_name).'/chat.png';
        return $url;
    }

    public function get_player_outfit($player_name){
        $url = 'http://services.runescape.com/m=avatar-rs/'.$this->normalize_name($player_name).'/appearance.dat';
        $appearance_string = $this->get_url($url);
        if($appearance_string != false){
            $details_url = 'http://services.runescape.com/m=adventurers-log/avatardetails.json?details='.$appearance_string;
            $result = $this->get_url($details_url);
            return json_decode($result);
        }else{
            return 'NO PROFILE';
        }
    }

    /*
     *  Hiscores (legacy, use Runemetrics)
     */

    /**
     * Grab the legacy Hiscores data for a player
     * @param string $player_name
     * @return array
     */
    public function get_player_hiscores($player_name, $type = false){
        
        if(isset($type) && $type != false){
            if($type == 'IM'){
                $url = 'http://services.runescape.com/m=hiscore_ironman/index_lite.ws?player='.$this->normalize_name($player_name);
            }elseif($type == 'HCIM'){
                $url = 'http://services.runescape.com/m=hiscore_hardcore_ironman/index_lite.ws?player='.$this->normalize_name($player_name);
            }else{
                return 'INVALID TYPE';
            }
        }else{
            $url = 'http://services.runescape.com/m=hiscore/index_lite.ws?player='.$this->normalize_name($player_name);
        }

        $result = $this->get_url($url);
        if($result != false){
            $list = array();
            $result = explode("\n", $result);
            foreach($result as $key => $row){
                if($key < (count($result)-1)){
                    $row_items = explode(",", $row);
                    if($key < 28){
                        $list[] = (object)array(
                            'name' => $this->skill_list[$key],
                            'rank' => $row_items[0],
                            'level' => $row_items[1],
                            'experience' => $row_items[2]
                        );
                    }else{
                        $list[] = (object)array(
                            'name' => $this->skill_list[$key],
                            'rank' => $row_items[0],
                            'value' => $row_items[1]
                        );
                    }
                }
            }
            return $list;
        }else{
            return 'NO PROFILE';
        }
    }

    /*
     *  Clans
     */

    /**
     * Grab a member list for the given clan name with name, rank, exp and kills
     * @param string $clan_name
     * @return array
     */
    public function get_clan_list($clan_name){
        $url = 'http://services.runescape.com/m=clan-hiscores/members_lite.ws?clanName='.$this->normalize_clan_name($clan_name);
        $result = $this->get_url($url);
        if($result != false){
            $clan_list = array();
            $result = explode("\n", $result);
            foreach($result as $key => $row){
                if($key != 0 && $key <= (count($result)-2)){
                    $row_item = explode(",", $row);
                    $name = htmlentities(utf8_encode($row_item[0]));
                    $clan_list[] = (object)array(
                        'name' => str_replace('&nbsp;', ' ', $name),
                        'rank' => $row_item[1],
                        'clan_xp' => $row_item[2],
                        'clan_kills' => $row_item[3]
                    );
                }
            }
            return $clan_list;
        }else{
            return 'CLAN NOT FOUND';
        }
    }

    /**
     * Grab a member list for the given clan name with only names
     * @param string $clan_name
     * @return array
     */
    public function get_clan_list_light($clan_name){
        $url = 'http://services.runescape.com/m=clan-hiscores/members_lite.ws?clanName='.$this->normalize_clan_name($clan_name);
        $result = $this->get_url($url);
        if($result != false){
            $clan_list = array();
            $result = explode("\n", $result);
            foreach($result as $key => $row){
                if($key != 0 && $key <= (count($result)-2)){
                    $row_item = explode(",", $row);
                    $name = htmlentities(utf8_encode($row_item[0]));
                    array_push($clan_list, str_replace('&nbsp;', ' ', $name));
                }
            }
            return $clan_list;
        }else{
            return 'CLAN NOT FOUND';
        }
    }

    /*
     *  Items
     */

    /**
     * Grab a member list for the given clan name with only names
     * @param string $clan_name
     * @return array
     */
    public function get_item_info($item_id){
        $url = 'http://services.runescape.com/m=itemdb_rs/api/catalogue/detail.json?item='.$item_id;
        $result = json_decode($this->get_url($url));
        if($result != false){
            return $result->item;
        }else{
            return 'ITEM NOT FOUND';
        }
    }

    public function items_callback($response, $url, $request_info, $user_data) {

        if($request_info['http_code'] == 200){
            $content = json_decode($response);  
            if($content != false){
                $this->_multi_container[] = $content->item;
            }
        }else{
            $this->_multi_container[] = 'ITEM NOT FOUND';
        }
    }

    public function get_multi_items($item_list){

        $req_count = count($item_list);
        for($i=0;$i<=$req_count-1;$i++){
            $item_id = $item_list[$i];
            $url = 'http://services.runescape.com/m=itemdb_rs/api/catalogue/detail.json?item='.$item_id;
            $post_data = NULL;
            $user_data = [$item_id, $i, $req_count];
            $options = [CURLOPT_SSL_VERIFYPEER => FALSE, CURLOPT_SSL_VERIFYHOST => FALSE];
            $headers = ['Referer: https://apps.runescape.com/runemetrics/'];
            $this->addRequest($url, $post_data, array($this, 'items_callback'), $user_data, $options, $headers);
        }
 
        $this->execute();
        return $this->_multi_container;
    }

    /*
     * Multi functions
     */

    public function activity_callback($response, $url, $request_info, $user_data) {

        if($request_info['http_code'] == 200){
            $content = json_decode($response);  
            if(!isset($content->error)){
                $this->_multi_container[] = (object)array(
                    "name"=>$user_data[0],
                    "activities"=>$content->activities
                    );
            }else{
                $this->request_data['profile_error'] += 1;
            }
        }else{
            $this->_multi_container[] = 'PLAYER NOT FOUND';
        }
    }

    public function skills_callback($response, $url, $request_info, $user_data) {

        if($request_info['http_code'] == 200){
            $content = json_decode($response);   
            if(!isset($content->error)){
                $this->_multi_container[] = (object)array(
                    "name"=>$user_data[0],
                    "skills"=>$content->skillvalues
                    );
            }else{
                $this->request_data['profile_error'] += 1;
            }
        }else{
            $this->_multi_container[] = 'PLAYER NOT FOUND';
        }
    }
    
    /**
     * Grab activity logs for each player in a list
     * @param array $name_list
     * @return array - the provided list with activity logs added per member
     */
    public function get_multi_activity($name_list){

        $req_count = count($name_list);
        for($i=0;$i<=$req_count-1;$i++){
            $name = $name_list[$i];
            $player_name = $this->normalize_name($name);
            $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$player_name.'&activities=20';
            $post_data = NULL;
            $user_data = [$player_name, $i, $req_count];
            $options = [CURLOPT_SSL_VERIFYPEER => FALSE, CURLOPT_SSL_VERIFYHOST => FALSE];
            $headers = ['Referer: https://apps.runescape.com/runemetrics/'];
            $this->addRequest($url, $post_data, array($this, 'activity_callback'), $user_data, $options, $headers);
        }
 
        $this->execute();
        return $this->_multi_container;
    }

    /**
     * Grab skills for each player in a list
     * @param array $name_list
     * @return array - the provided list with skills added per member
     */
    public function get_multi_skills($name_list){

        $req_count = count($name_list);

        for($i=0;$i<=$req_count-1;$i++){
            $name = $name_list[$i];
            $player_name = $this->normalize_name($name);
            $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$player_name.'&activities=20';
            $post_data = NULL;
            $user_data = [$player_name, $i, $req_count];
            $options = [CURLOPT_SSL_VERIFYPEER => FALSE, CURLOPT_SSL_VERIFYHOST => FALSE];
            $headers = ['Referer: https://apps.runescape.com/runemetrics/'];
            $this->addRequest($url, $post_data, array($this, 'skills_callback'), $user_data, $options, $headers);
        }

        $this->execute();
        return $this->_multi_container;
    }

    /**
     * Grab player details for each player in a list
     * @param array $name_list
     * @param boolean $logged_in
     * @return array - the provided list with details added per member
     */
    public function get_multi_details($name_list, $logged_in = false){

        if ((array)$name_list !== $name_list ) { 
            return "INVALID NAME LIST";
        } else { 
            foreach($name_list as &$name){
               $name = $this->normalize_name($name);
            }

            $comb_array = array();
            if(count($name_list) > 99){
                $chunked = array_chunk($name_list, 99, false);
                foreach($chunked as $sub_array){
                    $list = json_encode($sub_array);
                    $playerdetails_url = 'http://services.runescape.com/m=website-data/playerDetails.ws?membership=true&names='.$list.'&callback=angular.callbacks._0';
                    $result = $this->get_url($playerdetails_url, $logged_in);             
                    $result = $this->trim_rm_callback($result);
                    $comb_array = array_merge($comb_array, (array)$result);
                }
            }else{
                $list = json_encode($name_list);
                $playerdetails_url = 'http://services.runescape.com/m=website-data/playerDetails.ws?membership=true&names='.$list.'&callback=angular.callbacks._0';
                $result = $this->get_url($playerdetails_url, $logged_in);
                $result = $this->trim_rm_callback($result);
                $comb_array = $result;
            }
            return $comb_array;
        } 
    }

    /*
     *  cUrl, multi_curl and session token wrappers
     */

    /**
     * Generate a new runescape.com session token
     * @return string - session token, login failed or simply false
     */
    private function get_session_token(){

        $fields_string = "";

        $fields = array(
            'username' => urlencode($this->_pugLogin),
            'password' => urlencode($this->_pugPassword),
            'mod' => urlencode("www"),
            'ssl' => urlencode("0"),
            'dest' => urlencode("community")
        );

        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = substr($fields_string, 0, -1);
        $url = "https://secure.runescape.com/m=weblogin/login.ws";
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($this->curl, CURLOPT_REFERER, "https://secure.runescape.com/m=weblogin/login.ws");
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            "Host: secure.runescape.com",
            "Content-Type: application/x-www-form-urlencoded",
        ));
        $result = curl_exec($this->curl);
        $responseCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if($responseCode == 302 && $result){
            $session_index_start = strpos($result, 'session=') + 8;
            $session_index_end = strpos($result, ';', $session_index_start);
            $session_length = $session_index_end - $session_index_start;
            $session_token = substr($result, $session_index_start, $session_length);
            return $session_token;
        }elseif($responseCode == 200){
            return 'login failed';
        }else{
            return false;
        }
    }

    /**
     * Test a session token's validity by doing a cUrl request and checking if a certain property is in the result.
     * Will make a new session token and store it in the session memory if the current one fails the test.
     * @return string - new token, token still valid or check failed
     */
    private function probe_token(){
        curl_setopt($this->curl, CURLOPT_URL, 'http://services.runescape.com/m=website-data/playerDetails.ws?membership=true&names=["'.$this->_pugName.'"]&callback=angular.callbacks._0');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_REFERER, "https://apps.runescape.com/runemetrics/app/");
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Cookie: loggedIn=true; session=".$this->_sessionToken.";"));
        $result = curl_exec($this->curl);
        $responseCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if($responseCode == 200 && $result){
            $result = $this->trim_rm_callback($result)[0];
            if(!isset($result->online)){
                $this->_sessionToken = $this->get_session_token();
                return $this->_sessionToken;
            }else{
                return 'token still valid';
            }
        }else{
            return 'check failed';
        }
    }

    /**
     * Start a simple curl request to an URL
     * @param string $url
     * @param boolean $logged_in - if true and a pug account is supplied will add a session token
     * @return string|boolean - result as string on success or FALSE on failure
     */
    private function get_url($url, $logged_in = false){

        if($logged_in == true){
            if(!isset($this->_pugLogin)){
                exit('Pug account not set - detail methods are not enabled unless you provide a pug account to generate session tokens with');
            }else{
                $token_check = $this->probe_token();

                if($token_check == 'login failed'){
                    exit('Session token generation failed - wrong pug credentials');
                }else{
                    curl_setopt($this->curl, CURLOPT_REFERER, "https://apps.runescape.com/runemetrics/app/friends");
                    curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Cookie: loggedIn=true; session=".$this->_sessionToken.";"));
                }
            }
        }

        curl_setopt($this->curl, CURLOPT_POST, 0);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_URL, utf8_encode($url));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($this->curl);
        $responseCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if($responseCode == 200 && $result){
            return $result;
        }else{
            return false;
        }
    }

    /**
     * Add a request to the request qeue
     * @param string $clan_name
     * @return int - request index
     */
    private function addRequest($url, $post_data = NULL, callable $callback = NULL, $user_data = NULL, array $options = NULL, array $headers = NULL){
        $this->requests[] = [
            'url' => $url,
            'post_data' => $post_data,
            'callback' => ($callback) ? $callback : $this->_callback,
            'user_data' => ($user_data) ? $user_data : NULL,
            'options' => ($options) ? $options : NULL,
            'headers' => ($headers) ? $headers : NULL
        ];
        return count($this->requests) - 1;
    }

    /**
     * Reset the request qeue
     */
    private function reset() {
        $this->requests = [];
    }

    /**
     * Execute the request qeue
     */
    private function execute() {
        $start = microtime(true);
        $this->request_data['total'] = count($this->requests);
        if(count($this->requests) < $this->_maxConcurrent) {
            $this->_maxConcurrent = count($this->requests);
        }
        //the request map that maps the request queue to request curl handles
        $requests_map = [];
        $multi_handle = curl_multi_init();

        //start processing the initial request queue
        for($i = 0; $i < $this->_maxConcurrent; $i++) {
            $this->init_request($i, $multi_handle, $requests_map);
        }

        do{
            do{
                $mh_status = curl_multi_exec($multi_handle, $active);
            } while($mh_status == CURLM_CALL_MULTI_PERFORM);
            if($mh_status != CURLM_OK) {
                break;
            }

            //a request is just completed, find out which one
            while($completed = curl_multi_info_read($multi_handle)) {
                $this->process_request($completed, $multi_handle, $requests_map);

                //add/start a new request to the request queue
                if($i < count($this->requests) && isset($this->requests[$i])) { //if requests left
                    $this->init_request($i, $multi_handle, $requests_map);
                    $i++;
                }
            }

            usleep(15); //save CPU cycles, prevent continuous checking
        } while ($active || count($requests_map)); //End do-while

        $this->reset();
        $this->request_data['time'] = round(microtime(true) - $start, 1).'s';
        curl_multi_close($multi_handle);
    }

    /**
     * Build individual cURL options for a request
     * @param array $request
     * @return array - options
     */
    private function buildOptions(array $request) {
        $url = $request['url'];
        $post_data = $request['post_data'];
        $individual_opts = $request['options'];
        $individual_headers = $request['headers'];

        $options = $individual_opts;
        $headers = $individual_headers;

        //the below will overide the corresponding default or individual options
        $options[CURLOPT_RETURNTRANSFER] = true;

        $options[CURLOPT_NOSIGNAL] = 1;

        if($url) {
            $options[CURLOPT_URL] = $url;
        }

        if($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        // enable POST method and set POST parameters
        if($post_data) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = is_array($post_data)? http_build_query($post_data) : $post_data;
        }
        return $options;
    }

    /**
     * Initialize the request and add it to the multi handle
     * @param integer|curl_multi_init|array
     */
    private function init_request($request_num, $multi_handle, &$requests_map) {
        $request =& $this->requests[$request_num];

        $ch = curl_init();
        $opts_set = curl_setopt_array($ch, $this->buildOptions($request));
        if(!$opts_set) {
            echo 'options not set';
            exit;
        }
        curl_multi_add_handle($multi_handle, $ch);

        //add curl handle of a new request to the request map
        $ch_hash = (string) $ch;
        $requests_map[$ch_hash] = $request_num;
    }

    /**
     * Read the response data from a completed request
     * @param array|curl_multi_init|array
     */
    private function process_request($completed, $multi_handle, array &$requests_map) {
        $ch = $completed['handle'];
        $ch_hash = (string) $ch;
        $request =& $this->requests[$requests_map[$ch_hash]]; //map handler to request index to get request info

        $request_info = curl_getinfo($ch);
        $request_info['curle'] = $completed['result'];
        $request_info['handle'] = $ch;
        $request_info['url_raw'] = $url = $request['url'];
        $request_info['user_data'] = $user_data = $request['user_data'];

        if(curl_errno($ch) !== 0 || intval($request_info['http_code']) !== 200) { //if server responded with http error
            $response = false;
            $this->request_data['failed'] += 1;
        } else { //sucessful response
            $response = curl_multi_getcontent($ch);
            $this->request_data['success'] += 1;
        }

        //get request info
        $callback = $request['callback'];
        $options = $request['options'];

        if($response && (isset($this->_options[CURLOPT_HEADER]) || isset($options[CURLOPT_HEADER]))) {
            $k = intval($request_info['header_size']);
            $request_info['response_header'] = substr($response, 0, $k);
            $response = substr($response, $k);
        }

        //remove completed request and its curl handle
        unset($requests_map[$ch_hash]);
        curl_multi_remove_handle($multi_handle, $ch);

        //call the callback function and pass request info and user data to it
        if($callback) {
            call_user_func($callback, $response, $url, $request_info, $user_data);
        }
        $request = NULL; //free up memory now just incase response was large
        $this->request_data['success_rate'] = round(($this->request_data['success']/$this->request_data['total'])*100, 2).'%';
    }

    public function __destruct(){
        curl_close($this->curl);
    }
}
?>