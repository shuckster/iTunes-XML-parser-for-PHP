<?php
/*
  iTunes XML parser for PHP
  Copyright (C) 2013 Conan Theobald [http://github.com/shuckster]
  version: 1.5
  	Changes:
  		* 1.5: Simplify parseDict, API changes
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

class iTunesXMLParser {

	public $file_name = '';
	public $data = NULL;

	public $sort_field = NULL;
	public $sort_direction = 'ascending';

	protected function openFileOrSource( $file = NULL, $source = NULL) {

		// Open the XML document in the DOM
		$dom = new DomDocument();

		if ( NULL !== $file ) {
			if ( !file_exists( $file ) ) {
				die( 'iTunes XML file not found: ' . $file );
			}
			if ( !$dom->load( $file ) ) {
				die( 'Could not parse iTunes XML file: ' . $file );
			}
		}
		else if ( NULL !== $source ) {
			if ( !$dom->loadXML( $source ) ) {
				die( 'Could not parse XML source: ' . $source );
			}
		}

		// Get the root element <plist>
		$plist_node = $dom->documentElement;
		$first_dict_node = NULL;

		// First <dict> contains version-info + tracks-node
		foreach ( $plist_node->childNodes as $child ) {
			if ( 'dict' === $child->nodeName ) {
				$first_dict_node = $child;
				break;
			}
		}

		// Fell-through: Parse
		$this->file_name = $file;
		$this->data = $this->parseDict( $first_dict_node, NULL );

	}

	public function parse( $source ) {
		return $this->openFileOrSource( NULL, $source );
	}

	public function open( $file ) {
		return $this->openFileOrSource( $file );
	}

	public function processPlaylists() {

		if ( NULL !== $this->data && isset( $this->data[ 'Playlists' ] ) ) {
			$tracks = (array) $this->data[ 'Tracks' ];

			foreach( $this->data[ 'Playlists' ] as &$playlist ) {
				$new_items = array();

				foreach ( $playlist->{ 'Playlist Items' } as $item ) {
					$track_id = $item->{ 'Track ID' };
					$new_items[] = $tracks[ $track_id ];
				}

				$playlist->{ 'Playlist Items' } = $new_items;
			}
		}
		else {
			die( 'No data to work with' );
		}

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

					// Detect dates (ISO8601 based), convert to timestamps for comparison
					$rx_date = '/^\d{4}\-\d{2}\-\d{2}T\d{2}\:\d{2}\:\d{2}(Z|\+\d{2}\:\d{2})$/';
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

	protected function parseDict( $baseNode ) {

		$dicts = array();
		$current_key = NULL;
		$current_value = NULL;

		foreach ( $baseNode->childNodes as $child ) {

			$dict = NULL;

			switch ( $child->nodeName ) {

				case '#text':
				break;

				case 'key':
					$current_key = $child->textContent;
					$current_value = NULL;
				break;

				case 'array':
					$current_value = $this->parseDict( $child );
				break;

				case 'dict':
					$current_value = (object) $this->parseDict( $child );
				break;

				case 'true':
				case 'false':
					$current_value = 'true' === $child->nodeName;
				break;

				case 'integer':
					$current_value = (int) $child->textContent;
				break;

				default:
					$current_value = $child->textContent;

					if ( preg_match( '/^(Music Folder|Location)$/', $current_key ) ) {
						$current_value = urldecode( stripslashes( $current_value ) );
					}

			}

			if ( NULL !== $current_value ) {

				if ( 'array' === $baseNode->nodeName ) {
					$dicts[] = $current_value;
				}
				else if ( NULL !== $current_key ) {
					$dicts[ $current_key ] = $current_value;
					$current_key = NULL;
				}

				$current_value = NULL;

			}

		}

		// Sort the tracks
		if ( $this->sort_field ) {
			uasort( $dicts, array( $this, 'sort' ) );
		}

		return $dicts;

	}

}
