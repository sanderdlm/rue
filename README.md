# Rue
A PHP wrapper class that lets you easily grab Runescape related data from various endpoints.

####License
MIT

#### Version
1.0.0

for PHP 5.4+

##How to Use

To get access to the basic functionality, simply include and initialize the class.

```php
require_once 'rue.php';
$r = new Rue();
```
That's it.

##Options

If you want to use data like player online status and current world, make a new account **AND** a character on Runescape and enter it's details like this.

```php
require_once 'rue.php';

$r = new Rue();

//Setting a pug or alt account allows the class to generate session tokens. 
$r->setPug("pugemail@gmail.com", "pugpassword", "pugname");
```
##Examples

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
That's what it currently does. All the functions are documented inside the class, so you can take a look there if there is any confusion.

###To do
I'll be adding more stuff like the legacy Hiscore API, Bestiary and any other endpoint I can find in Runemetrics.

### Issues/suggestions
If you have any, please let me know.

### Contact
You can contact me on [Twitter](https://twitter.com/) for all questions.
