2022 edit: There is a newer library called [Flo](https://github.com/dreadnip/flo/blob/master/src/Flo.php) that uses Symfony HttpClient, which should be more stable than this one. I don't think I ported all of the features though, but you should still use it over this old piece of junk.

# Rue
Rue is a PHP library that attempts to be an easy-to-use wrapper for all of the possible Runescape web service endpoints. It aims to combine the older API, the new Runemetrics URLs and a few other endpoints that can be found on runescape.com. 

The class also has a multi_curl wrapper built in. This makes it easy to take single player functions, like grabbing a player's experience in a certain skill, and apply them to an entire clan. A situation where this might come in handy is automating a clan's skilling competition.

Rue can also simulate web logins to runescape.com and use the generated session token as a cookie to get more data from certain endpoints. By doing this you can get data like player online status and current world.

#### License
MIT

#### Version
1.0.0

for PHP 5.4+

## How to Use

To get access to the basic functionality, simply include and initialize the class.

```php
require_once 'rue.php';
$r = new \Rue\rs_api();
```
That's it.

## Options

If you want to use data like player online status and current world, make a new account **AND** create a character on that account. Once the character exists, enter it's details like this.

```php
require_once 'rue.php';

$r = new \Rue\rs_api();

//Setting a pug or alt account allows the class to generate session tokens. 
$r->set_pug("pugemail@gmail.com", "pugpassword", "pugname");
```
## Examples

Here are a few basic examples. I've also included them as PHP files in the repo.
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

//Get a list of all the online members of your clan
$clan_list = $r->get_clan_list_light("Wrack City");
$demo = $r->get_multi_details($clan_list, true);
```
All the functions are documented inside the class, so you can take a look there if there is any confusion.

### To do

* Add the Bestiary links
* Add more of the old API links like Ironman rankings and HCIM rankings
* idk??

### Issues/suggestions
If you have any, please let me know.
