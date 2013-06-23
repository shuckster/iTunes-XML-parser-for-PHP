<?php
/*
  iTunes XML parser for PHP
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

class iTunesXMLParser {

	public $file_name = '';

	public $info = NULL;
	public $tracks = array();
	public $playlists = array();

	public $sort_field = NULL;
	public $sort_direction = 'ascending';

	protected $plist_node = NULL;
	protected $tracks_node = NULL;
	protected $playlists_node = NULL;

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
		$infos_node = NULL;
		$tracks_node = NULL;
		$playlists_node = NULL;

		// First <dict> contains version-info + tracks-node
		foreach ( $plist_node->childNodes as $child ) {
			if ( 'dict' === $child->nodeName ) {
				$infos_node = $child;
				break;
			}
		}

		// <dict> in the version-node contains the tracks, <array> contains the playlists
		$break_out = false;
		foreach ( $infos_node->childNodes as $child ) {

			if ( 'dict' === $child->nodeName ) {
				$tracks_node = $child;
				if ( $break_out ) {
					break;
				}
				$break_out = true;
			}

			if ( 'array' === $child->nodeName ) {
				$playlists_node = $child;
				if ( $break_out ) {
					break;
				}
				$break_out = true;
			}

		}

		// Fell-through: Set public vars to successful parse-data
		$this->file_name = $path;

		$this->plist_node = $plist_node;
		$this->tracks_node = $tracks_node;
		$this->playlists_node = $playlists_node;

		$this->parseInfos();
		$this->parseTracks();
		$this->parsePlaylists();

	}

	protected function parseInfos() {

		$infos = $this->parseDict( $this->plist_node, NULL,  array( 'dict', 'array' ) );

		if ( NULL !== $infos ) {
			$this->info = $infos[ 0 ];
		}

		return $info;

	}

	protected function parseTracks() {

		$tracks = $this->parseDict( $this->tracks_node, 'Track ID' );

		if ( NULL !== $tracks ) {
			$this->tracks = $tracks;
		}

		return $tracks;

	}

	protected function parsePlaylists() {

		$playlists = $this->parseDict( $this->playlists_node, 'Playlist ID' );

		if ( NULL !== $playlists ) {

			// Match playlist-items to actual tracks
			foreach ( $playlists as &$playlist ) {

				if ( isset( $playlist[ 'Playlist Items' ] ) ) {
					$new_items = array();

					foreach ( $playlist[ 'Playlist Items' ] as $item ) {
						$track_id = $item[ 'Track ID' ];
						$new_items[] = $this->tracks[ $track_id ];
					}

					$playlist[ 'Playlist Items' ] = $new_items;
				}

			}

			$this->playlists = $playlists;
		}

		return $playlists;

	}

	protected function parseDict( $baseNode, $primary_key = NULL, $ignore_nodes = array() ) {

		$dicts = array();

		// Loop through the playlists
		foreach ( $baseNode->childNodes as $child ) {

			// all the sub dicts from here on should be songs
			if ( 'dict' === $child->nodeName ) {
				$dict = NULL;

				// Get track properties
				$properties = $child->childNodes;
				for ( $prop_index = 0, $prop_length = $properties->length; $prop_index < $prop_length; $prop_index++ ) {

					$prop = $properties->item( $prop_index );

					// Ordering is important in plist files; key -> value is part of the convention
					if ( 'key' === $prop->nodeName ) {

						// <key> $prop_key </key>
						$prop_key = $prop->textContent;

						// <value> $prop_value </value>
						do {
							$prop = $properties->item( ++$prop_index );
						} while ( '#text' === $prop->nodeName );

						$ignore_node = in_array( $prop->nodeName, $ignore_nodes );

						if ( !$ignore_node ) {

							switch ( $prop->nodeName ) {
								case 'array':
									$prop_value = $this->parseDict( $prop );
								break;

								case 'true':
								case 'false':
									$prop_value = 'true' === $prop->nodeName;
								break;

								case 'integer':
									$prop_value = (int) $prop->textContent;
								break;

								default:
									$prop_value = $prop->textContent;

									if ( preg_match( '/^(Music Folder|Location)$/', $prop_key ) ) {
										$prop_value = urldecode( stripslashes( $prop_value ) );
									}

							}

							$dict[ $prop_key ] = $prop_value;

						}

					}

				}

				// Save the track
				if ( $dict ) {
					if ( NULL !== $primary_key && isset( $dict[ $primary_key ] ) ) {
						$dicts[ $dict[ $primary_key ] ] = $dict;
					}
					else {
						$dicts[] = $dict;
					}
				}

			}

		}

		// Sort the tracks
		if ( $this->sort_field ) {
			uasort( $dicts, array( $this, 'sort' ) );
		}

		return $dicts;

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

}
