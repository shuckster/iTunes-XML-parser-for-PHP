<?php
/*
  iTunes XML PhP Parser for PHP 5
  Copyright (C) 2013 Conan Theobald [http://github.com/shuckster]
  version: 1.3
  	Changes:
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

/*
  See "example.php" for usage.

  Example output:

	Array (
		[0] => Array
			(
				[Track ID] => 34
				[Name] => depeche mode gameboy megamix!
				[Artist] => nullsleep
				[Composer] => nullsleep
				[Album] => www.nullsleep.com
				[Genre] => Electronic
				[Kind] => MPEG audio file
				[Size] => 13901952
				[Total Time] => 868780
				[Year] => 2002
				[Date Modified] => 2002-12-17T10:24:18Z
				[Date Added] => 2003-11-11T06:38:24Z
				[Bit Rate] => 128
				[Sample Rate] => 44100
				[Comments] => www.8bitpeoples.com 1. enjoy the silence 2. photographic 3. new life 4. everything counts
				[Play Count] => 3
				[Play Date] => -1142117754
				[Play Date UTC] => 2003-11-28T15:32:22Z
				[Rating] => 80
				[Normalization] => 976
				[Location] => file://localhost/C:/media/nullsleep/www.nullsleep.com/depeche%20mode%20gameboy%20megamix!.mp3/
				[File Folder Count] => 4
				[Library Folder Count] => 1
			)
	    [1] => Array
			(
				[Track ID] => 65
				[Name] => Daftendirekt
				[Artist] => Daft Punk
				[Album] => Homework
				[Genre] => Electronic
				[Kind] => MPEG audio file
				[Size] => 4098756
				[Total Time] => 164649
				[Track Number] => 1
				[Date Modified] => 2003-11-28T19:45:05Z
				[Date Added] => 2003-11-11T06:38:25Z
				[Bit Rate] => 192
				[Sample Rate] => 44100
				[Play Count] => 5
				[Play Date] => -1142119790
				[Play Date UTC] => 2003-11-28T14:58:26Z
				[Rating] => 80
				[Normalization] => 1414
				[Location] => file://localhost/C:/media/Daft%20Punk/Homework/01%20Daftendirekt.MP3/
				[File Folder Count] => 4
				[Library Folder Count] => 1
			)
	}
*/

class iTunesXMLParser {

	public $file_name = '';
	public $tracks = array();

	public $sort_field = NULL;
	public $sort_direction = 'ascending';

	public function open( $path ) {

		if ( !file_exists( $path ) ) {
			die( 'iTunes XML file not found: ' . $path );
		}

		// Open the XML document in the DOM
		$dom = new DomDocument();
		if ( !$dom->load( $path ) ) {
			die( 'Could not parse iTunes XML file: ' . $path );
		}

		// Get the root element <plist>
		$plist_node = $dom->documentElement;
		$version_node = NULL;
		$tracks_node = array();

		// First <dict> contains version-info + tracks-node
		foreach ( $plist_node->childNodes as $child ) {
			if ( 'dict' === $child->nodeName ) {
				$version_node = $child;
				break;
			}
		}

		// <dict> in the version-node contains tracks
		foreach ( $version_node->childNodes as $child ) {
			if ( 'dict' === $child->nodeName ) {
				$tracks_node = $child;
				break;
			}
		}

		// Loop through the tracks
		foreach ( $tracks_node->childNodes as $child ) {

			// all the sub dicts from here on should be songs
			if ( 'dict' === $child->nodeName ) {
				$track = NULL;

				// Get track properties
				$properties = $child->childNodes;
				for ( $prop_index = 0, $prop_length = $properties->length; $prop_index < $prop_length; $prop_index++ ) {

					$prop = $properties->item( $prop_index );

					// Ordering is important in plist files; key -> value is part of the convention

					if ( 'key' === $prop->nodeName ) {

						// <key> $prop_key </key>
						$prop_key = $prop->textContent;

						// <value> $prop_value </value>
						$prop = $properties->item( ++$prop_index );

						switch ( $prop->nodeName ) {
							case 'true':
							case 'false':
								$prop_value = 'true' === $prop->nodeName;
							break;

							case 'integer':
								$prop_value = (int) $prop->textContent;
							break;

							default:
								$prop_value = $prop->textContent;
						}

						$track[ $prop_key ] = $prop_value;
					}
				}

				// Save the track
				if ( $track ) {
					$tracks[] = $track;
				}

			}

		}

		// Sort the tracks
		if ( $this->sort_field ) {
			uasort( $tracks, array( $this, 'sort' ) );
		}

		// Fell-through: Set public vars to successful parse-data
		$this->file_name = $path;
		$this->tracks = $tracks;

		return $tracks;

	}

	// To be used with the uasort() array function
	protected function sort( $left, $right ) {

		$field = $this->sort_field;
		$direction = $this->sort_direction;

		// Return the strcmp() of the two fields
		if ( isset( $left[ $field ] ) && isset( $right[ $field ] ) ) {

			$left = $left[ $field ];
			$right = $right[ $field ];

			switch ( gettype( $left ) ) {

				case 'boolean':
					$left = (int) $left;
					$right = (int) $right;

				case 'integer':
				case 'double':
					if ( 'descending' === $direction ) {
						return $left === $right ? 0 : ( $left > $right ? -1 : 1 );
					}
					else {
						return $left === $right ? 0 : ( $right > $left ? -1 : 1 );
					}
				break;

				default:

					$rx_date = '/^\d{4}\-\d{2}\-\d{2}T\d{2}\:\d{2}\:\d{2}(Z|\+\d{2}\:\d{2})$/';

					// Do a date-comparison
					if ( preg_match( $rx_date, $left ) && preg_match( $rx_date, $right ) ) {

						$left = strtotime( $left );
						$right = strtotime( $right );

						if ( 'descending' === $direction ) {
							return $left === $right ? 0 : ( $left > $right ? -1 : 1 );
						}
						else {
							return $left === $right ? 0 : ( $right > $left ? -1 : 1 );
						}

					}

					// Default to a string comparison
					else {
						if ( 'descending' === $direction ) {
							return strcasecmp( $left, $right );
						}
						else {
							return strcasecmp( $right, $left );
						}
					}

			}

		}
		elseif ( isset( $left[ $field ] ) ) {
			return -1;
		}
		else {
			return 1;
		}

	}

}
