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
    private $_rcx;
    private $_multi_container;

    /*
     *  Constructor & dependency injection functions
     */

    function __construct() {
        $this->curl = curl_init();
    }

    public function setMulti(RollingCurlX $rcx){
        $this->_rcx = $rcx;
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

    private function multi_check(){
        if(!isset($this->_rcx)){
            exit('RollingCurlX class not found - multi methods are not enabled unless you inject this class - https://github.com/marcushat/RollingCurlX');
        }
    }

    /**
     * Runemetrics end-point wrappers
     * @param string $player_name (optional: boolean $logged_in - with pug account set)
     * @return array
     */

    public function get_player_profile($player_name){
        $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$this->normalize_name($player_name).'&activities=20';
        $result = json_decode($this->get_url($url));
        return $result;
    }

    public function get_player_activity($player_name){
        $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$this->normalize_name($player_name).'&activities=20';
        $result = json_decode($this->get_url($url));
        return $result->activities;
    }

    public function get_player_skills($player_name){
        $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$this->normalize_name($player_name).'&activities=20';
        $result = json_decode($this->get_url($url));
        return $result->skillvalues;
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
        return $result->quests;
    }

    public function get_player_avatar($player_name){
        $url = 'http://services.runescape.com/m=avatar-rs/'.$this->normalize_name($player_name).'/chat.png';
        return $url;
    }

    /*
     *  Hiscores (legacy, use Runemetrics)
     */

    /**
     * Grab the legacy Hiscores data for a player
     * @param string $player_name
     * @return array
     */
    public function get_player_hiscores($player_name){
        $url = 'http://services.runescape.com/m=hiscore/index_lite.ws?player='.$this->normalize_name($player_name);
        $result = str_getcsv($this->get_url($url));
        return (object)$result;
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
    }

    /**
     * Grab a member list for the given clan name with only names
     * @param string $clan_name
     * @return array
     */
    public function get_clan_list_light($clan_name){
        $url = 'http://services.runescape.com/m=clan-hiscores/members_lite.ws?clanName='.$this->normalize_clan_name($clan_name);
        $result = $this->get_url($url);
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
    }

    /*
     * Multi functions
     */

    public function activity_callback($response, $url, $request_info, $user_data, $time) {

        if($request_info['http_code'] == 200){
            $content = json_decode($response);  
            if(!isset($content->error)){
                $this->_multi_container[] = (object)array(
                    "name"=>$user_data[0],
                    "activities"=>$content->activities
                    );
            }
        }
    }

    public function skills_callback($response, $url, $request_info, $user_data, $time) {

        if($request_info['http_code'] == 200){
            $content = json_decode($response);   
            if(!isset($content->error)){
                $this->_multi_container[] = (object)array(
                    "name"=>$user_data[0],
                    "skills"=>$content->skillvalues
                    );
            }
        }
    }
    
    /**
     * Grab activity logs for each player in a list
     * @param array $name_list
     * @return array - the provided list with activity logs added per member
     */
    public function get_multi_activity($name_list){
        $this->multi_check();

        $req_count = count($name_list);

        for($i=0;$i<=$req_count-1;$i++){
            $name = $name_list[$i];
            $player_name = $this->normalize_name($name);
            $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$player_name.'&activities=20';
            $post_data = NULL;
            $user_data = [$player_name, $i, $req_count];
            $options = [CURLOPT_SSL_VERIFYPEER => FALSE, CURLOPT_SSL_VERIFYHOST => FALSE];
            $headers = ['Referer: https://apps.runescape.com/runemetrics/'];
            $this->_rcx->addRequest($url, $post_data, array($this, 'activity_callback'), $user_data, $options, $headers);
        }

        $this->_rcx->execute();
        return $this->_multi_container;
    }

    /**
     * Grab skills for each player in a list
     * @param array $name_list
     * @return array - the provided list with skills added per member
     */
    public function get_multi_skills($name_list){
        $this->multi_check();

        $req_count = count($name_list);

        for($i=0;$i<=$req_count-1;$i++){
            $name = $name_list[$i];
            $player_name = $this->normalize_name($name);
            $url = 'https://apps.runescape.com/runemetrics/profile/profile?user='.$player_name.'&activities=20';
            $post_data = NULL;
            $user_data = [$player_name, $i, $req_count];
            $options = [CURLOPT_SSL_VERIFYPEER => FALSE, CURLOPT_SSL_VERIFYHOST => FALSE];
            $headers = ['Referer: https://apps.runescape.com/runemetrics/'];
            $this->_rcx->addRequest($url, $post_data, array($this, 'skills_callback'), $user_data, $options, $headers);
        }

        $this->_rcx->execute();
        return $this->_multi_container;
    }

    /**
     * Grab player details for each player in a list
     * @param array $name_list
     * @param boolean $logged_in
     * @return array - the provided list with details added per member
     */
    public function get_multi_details($name_list, $logged_in = false){
        $this->multi_check();

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

    public function __destruct(){
        curl_close($this->curl);
    }
}
?>