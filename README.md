iTunes XML parser for PHP
==========================

#### A class to extract info, tracks and playlists from an iTunes XML file.

Original code copyright (c) 2003 by [Robert A. Wallis](https://github.com/robert-wallis).
Dicked around with in 2005 by [Peter Minarik](https://peterminarik.com/),
and in 2013-2021 by [Conan Theobald](https://github.com/shuckster).

LGPL2.1 licensed: See [LICENSE](LICENSE)

## About

A simple PHP class that will read an iTunes XML file and convert the info,
tracks and playlists contained within into an array of objects.

Tracks can be matched to playlist-items by running the `#processPlaylists()`
method after opening your XML file.

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
  "$itunes->data" is now available. print_r to see what's inside:
    print_r( $itunes->data );
*/

```

For conversion to JSON, I recommend using the `jsbeautifier.org`
[PHP port](https://github.com/einars/js-beautify/tree/attic-php/php).

EDIT - No need for that [now](https://www.php.net/manual/en/function.json-encode.php). :)

## 2021 update

Before releasing 1.6 I discovered a fork by @dajoho that has a `composer.json`:

*   https://github.com/dajoho/itunes-php

This version had also already fixed the [sort() bug](https://github.com/shuckster/iTunes-XML-parser-for-PHP/commit/1d02fb4c0b93309712f8bcf1733d2746cdb90737) [2 years ago](https://github.com/dajoho/itunes-php/commit/eb28d8a3873607dc57aa412f6cffcafa58173f57)! :)

## Contributors

*   [@nickbe](https://github.com/nickbe) - Bug report
*   [@dajoho](https://github.com/dajoho) - Composer compatible fork
*   [@SidRoberts](https://github.com/SidRoberts) - Code improvement


## Credits

Based on work by:

*   Robert Wallis
*   http://www.wirsindecht.org/
