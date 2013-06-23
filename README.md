iTunes XML parser for PHP
==========================

#### A class to extract track-information from an iTunes XML file.

Original code copyright (c) 2003 by [Robert A. Wallis](http://codetriangle.com/). Dicked around with in 2005 by [Peter Minarik](http://www.wirsindecht.org/), and in 2013 by [Conan Theobald](mailto:me[at]conans[dot]co[dot]uk).

LGPL licensed: See [LICENSE](LICENSE)

## About

A simple PHP class that will read an iTunes XML file and convert the tracks
contained within into an array of objects.

Supports sorting fields by string, number, and date.

## Instructions

See [example.php](example.php) for a basic implementation.

```php
require_once 'iTunesXMLparser.class.php';

$xml_path = 'iTunes playlist export.xml';

$itunes = new iTunesXMLParser();
$itunes->sort_field = 'Track ID';
$itunes->sort_direction = 'ascending';
$itunes->open( $xml_path );

/*
These variables are now available. print_r to see what's in 'em:

$itunes->info;
$itunes->tracks;
$itunes->playlists;
*/
```

## Credits

Based on work by:

*   http://codetriangle.com/
*   http://www.wirsindecht.org/

The original author, Robert A. Wallis, can be found here:

*   https://plus.google.com/100955196044884930812/about
