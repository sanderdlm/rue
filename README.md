# Rue
A simple PHP wrapper for some Runescape endpoints

####License
MIT

#### Version
1.0.0

for PHP 5.4+

##How to Use

First include and initialize the class.

```php
require_once 'rue.php';
$r = new Rue();
```
If you want to use all of the functionality in the class, you also have to set the following options

```php
require_once 'rue.php';
require_once 'rollingCurl.php';

$r = new Rue();

//Including the RollingCurlX class allows you to use all of the 'multi' methods. 
//These will use multiple parallel cUrl requests to get data on complete clans/groups quickly. 
$rcx = new RollingCurlX();
$r->setMulti($rcx);

//Setting a pug or alt account allows the class to generate session tokens. 
//Adding a token to your requests will yield extra data with the 'details' methods, like online status and world.
$r->setPug("pugemail@gmail.com", "pugpassword", "pugname");
```

Once you're set up, you can start grabbing data. Some examples:
```php
//Get the last 20 activity logs for a player
$player_activity = $r->get_player_activity("Zezima");

//Get a player's skill levels and experience
$player_skills = $r->get_player_skills("Omid");

//Get a list of all clan members, with rank and clan exp per member
$list = $r->get_clan_list("Wrack City");

//Get the last 20 activity logs for everyone in an entire clan
$clan_list = $r->get_clan_list_light("Efficiency Experts");
$demo = $r->get_multi_activity($clan_list);
```
That's what it currently does. All the functions are documented inside the class, so you can take a look there if there is any confusion.

### Issues
If you find any issues please let me know.

### Contact
You can contact me on [Twitter](https://twitter.com/) for all questions.

http://www.github.com/yakcity/rue
