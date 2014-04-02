RobsCMS
=========

RobsCMS is a simple [photo]blogging platform created by Roberto Chavarria. Check out a live version on http://www.robchava.com

Some cool features:

  - Post photos as well as normal text posts.
  - Any photo can be used as the site's background image.
  - Users can log in via Google or Facebook, and can comment on posts.
  - Permission system lets you assign users to groups, and limit which groups can view which posts, and which posts are public.
  - Want to share a specific post with someone, but not give him access to anything else? Each post has a key that can allow this, and be reset at any moment.
  - Photos can be assigned a geolocation, and you can view all photo locations on a Google Map.
  - Site is responsive! Looks great on all devices.
  - Open source via GPL license.

Version
-----------
1.0

To-do list
-----------
 - Infinite scrolling
 - Labels

Thanks to
-----------
RobsCMS is built upon a number of open source projects:
* [CakePHP] - PHP web application framework
* [Twitter Bootstrap] - UI boilerplate for modern web apps
* [jQuery] - Javascript Library
* [Dillinger] - Online Markdown editor
* [FriendlyDateHelper] - Friendly date helper for CakePHP

PHP, MySQL, LESS, and others.

Installation
-----------
The first step to create a new install of RobsCMS is to create the database:

```
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `robscms` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `robscms`;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `text` varchar(10000) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_ibfk_2` (`user_id`),
  KEY `comments_ibfk_1` (`post_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `me` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `about_loggedin` varchar(10000) NOT NULL,
  `about_loggedout` varchar(10000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(2500) NOT NULL,
  `text` varchar(50000) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `isBg` tinyint(4) NOT NULL DEFAULT '0',
  `isQuote` tinyint(4) NOT NULL DEFAULT '0',
  `isStatus` tinyint(4) NOT NULL DEFAULT '0',
  `exif` varchar(1000) NOT NULL,
  `lat` varchar(50) NOT NULL,
  `long` varchar(50) NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '50',
  `key` varchar(40) NOT NULL,
  `isDeleted` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `prefs` (
  `user_id` int(11) NOT NULL,
  `emailfreq` tinyint(4) NOT NULL DEFAULT '0',
  `lang` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `googleid` varchar(100) NOT NULL,
  `facebookid` varchar(50) NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `prefs`
  ADD CONSTRAINT `prefs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


INSERT INTO `me` (`id`, `about_loggedin`, `about_loggedout`) VALUES (1, ' :) ', ' :) ');
```




Next, modify preset values in /Config/bootstrap.php to your own preferences, and Config/database.php to your database's URL and user/password combination.

You must create folders for your thumbnails. By default these folders are located in /webroot/img/ruperts/ and should be original/, 200/, 400/, 600/, 800/, 1200/.

If you want to allow users to login using Facebook, you will have to create a Facebook app and enter the app ID and app secret in /Config/bootstrap.php

Log in to the site. Then via the MySQL shell or phpMyAdmin, set your own user's 'permissions' field to 5, to give yourself admin permissions. You will be able to update other user's permissions via web interface.

The current robots.txt file is set to block all bots from accessing the site. Change this if you would like your site indexed by Google.



License
-----------
    RobsCMS - [Photo]Blogging Platform
    Copyright (C) 2014 Roberto Chavarria

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.



[CakePHP]:http://cakephp.org/
[Twitter Bootstrap]:http://twitter.github.com/bootstrap/
[jQuery]:http://jquery.com
[Dillinger]:http://dillinger.io
[FriendlyDateHelper]:http://github.com/rbchv/friendlydatehelper

