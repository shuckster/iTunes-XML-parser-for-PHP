<?php
/*
  Example for using iTunes XML parser for PHP.
  Copyright (C) 2013-2021 Conan Theobald [http://github.com/shuckster]
  Source repository:
  - https://github.com/shuckster/iTunes-XML-parser-for-PHP

  based on:

    Copyright (C) 2005 Peter Minarik [http://www.wirsindecht.org]
    version: 1.00
    based on:

    iTunes XML PhP Parser
    Copyright (C) 2003 Robert A. Wallis [https://github.com/robert-wallis]
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
  "$itunes->data" is now available. print_r to see what's inside:
    print_r( $itunes->data );
*/

// Find only videos (kind of useless, since the example XML only
// has video in it ;)
$video = array();
$tracks = (array) $itunes->data[ 'Tracks' ];
foreach ( $tracks as $track ) {
	if ( isset( $track->{ 'Has Video' } ) && $track->{ 'Has Video' } && isset( $track->{ 'Location' } ) ) {
		$video[] = $track;
	}
}

print_r( $video );
exit;

/*
 * Old JSON output example using JSBeautify.
 * These days we can use json_encode()
 */

// JSBeautify and options
// Download from: https://github.com/einars/js-beautify/tree/attic-php/php

require_once 'jsbeautifier.php';

$jsb = new JSBeautifier();
$jsb_opts = new BeautifierOptions();
$jsb_opts->indent_size = 1;
$jsb_opts->indent_with_tabs = true;

header( 'Content-type: application/json; charset=utf-8' );
echo $jsb->beautify( json_encode( $video ), $jsb_opts );
exit;

?>
