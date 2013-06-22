<?php
/* 
  iTunes XML PhP Parser for PHP 5
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

// pass the $filename of the xml file you want to parse
// [optional] $sort_field = can be set to "Album", "Rating", etc...  left alone will not sort the list
// [optional] $sort_direction = "up" or "down"
//
// the function will return an array of songs
// each song is an array of variables that makes sense
/* example output
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
function iTunesXmlParser($filename, $sort_field=NULL, $sort_direction="up")
{
	// save the input in global variables for the sort function
	global $g_ITX_field, $g_ITX_direction;
	$g_ITX_field = $sort_field;
	$g_ITX_direction = $sort_direction;
	
	// init main variables
	$songs = array();
	$xml = NULL; // parsed XML
	
	// read the file into $xml first
	/*ob_start();
		readfile($filename);
		$xml = ob_get_contents();
	ob_end_clean();*/
	
	// open the xml document in the DOM
	$dom = new DomDocument();
	if (!$dom->load($filename))
		die("Could not parse iTunes XML file: ".$filename);
	
	// get the root element
	$root = $dom->documentElement;
	
	// yeah "dict" means everything, playlist, and song that makes sense... NOT
	// find the first "dict"
	$children = $root->childNodes;
	foreach ($children as $child)
	{
		if ($child->nodeName=="dict")
		{
			$root = $child;
			break;
		}
	}

	// do that again, and find the second inner dict
	$children = $root->childNodes;
	foreach ($children as $child)
	{
		if ($child->nodeName=="dict")
		{
			$root = $child;
			break;
		}
	}
		
	// now go through all the child elements
	$children = $root->childNodes;
	foreach ($children as $child)
	{
		// all the sub dicts from here on should be songs
		if ($child->nodeName=="dict")
		{
			$song = NULL;
			
			// get all the elements
			$elements = $child->childNodes;
			for ($i = 0; $i < $elements->length; $i++)
			{
				// alright whomever wrote this xml file was smoking something serious
				// in normal XML documents we would do:
				//  <artist>Daft Punk</artist>
				// but in Apple iTunes bong land we do:
				//  <key>Artist</key><string>Daft Punk</string>
				
				if ($elements->item($i)->nodeName=="key")
				{
					// so I'm just going to expect that i++ (<string>, <int>, etc...) is always going to be there,
					//  if the key's name is <key>
					//  instead of doing some error checking here to make sure there are matching values to keys
					$key = $elements->item($i)->textContent;
					$i++;
					$value = $elements->item($i)->textContent;
					$song[$key]=$value;
				}
			}
			
			// save the song
			if ($song)
				$songs[] = $song;
		}
	}
	
	// now sort the songs
	// $sort_field=NULL, $sort_direction="up"
	if ($sort_field)
	{
		uasort($songs, "iTunesXmlSongSort");
	}

	return $songs;
}

$g_ITX_field = NULL;
$g_ITX_direction = NULL;
// to be used with the uasort() array function in PHP
function iTunesXmlSongSort($left, $right)
{
	global $g_ITX_field, $g_ITX_direction;
	
	// return the strcmp() of the two fields
	if (isset($left[$g_ITX_field])&&isset($right[$g_ITX_field]))
	{
		if (strcasecmp($g_ITX_direction, "up"))
			return strcasecmp($left[$g_ITX_field],$right[$g_ITX_field]);
		else
			return strcasecmp($right[$g_ITX_field],$left[$g_ITX_field]);	
	}
	elseif (isset($left[$g_ITX_field]))
		return -1;
	else
		return 1;
}
