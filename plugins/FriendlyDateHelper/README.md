FriendlydateHelper.php
=========
FriendlyDateHelper.php is a CakePHP helper to show user friendly dates from SQL DATETIME fields.

Version
--------
1.0

How to use
---------
Clone this repository into your /plugins/ directory.

Then in your controller add:
```
CakePlugin::load('FriendlyDateHelper');
```
and
```
public $helpers = array('FriendlyDateHelper.Friendlydate');
```


In your view you can use the convert method to convert a DATETIME variable into something more user-friendly:

```
$myDatetime = '2010-15-15 14:25:00';
echo $this->Friendlydate->convert($myDatetime);
```
will echo
```
5 months and a half ago
```


Internationalization
---------
If you're using CakePHP's internationalization features, you probably already have something like:

```
Configure::write('Config.language', 'esp');
```
This helper will work correctly for Spanish ('esp'), and will default to English if any other value is encountered.

License
---------
Distributed under the MIT license. For more information see LICENSE.

