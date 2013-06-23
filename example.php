<?php
/*
  Example for using iTunes XML parser for PHP
  Copyright (C) 2013 Conan Theobald [http://github.com/shuckster]
  version: 1.4
    Changes:
      * 1.4: Parse info and playlists
  		* 1.3: New example, delete old/deprecated stuff
  		* 1.2: Now a class, improved sort-method
  		* 1.1: Type-cast integers and booleans

  based on:

  Copyright (C) 2005 Peter Minarik [http://www.wirsindecht.org]
  version: 1.00
  based on:

  iTunes XML PhP Parser
  Copyright (C) 2003 Robert A. Wallis [http://codetriangle.com/]
  version: 1.00

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE
  as published by the Free Software Foundation; either version 2.1
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  Library General Public License for more details.

  You should have received a copy of the GNU Library General Public
  License along with this library; if not, write to the Free
  Software Foundation, Inc., 59 Temple Place - Suite 330, Boston,
  MA 02111-1307, USA

  iTunes is a product by Apple Computer, Inc.
  http://www.apple.com/

*/

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

// Find only videos (kind of useless, since the example XML only has video in it ;)
$video = array();
foreach ( $itunes->tracks as $track ) {
	if ( isset( $track[ 'Has Video' ] ) && $track[ 'Has Video' ] && isset( $track[ 'Location' ] ) ) {
		$video[] = $track;
	}
}

print_r( $video );

?>
