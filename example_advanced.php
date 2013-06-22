<?php
/*
  Copyright (C) 2003 Robert A. Wallis http://codetriangle.com

  This software is provided 'as-is', without any express or implied
  warranty.  In no event will the authors be held liable for any damages
  arising from the use of this software.

  Permission is granted to anyone to use this software for any purpose,
  including commercial applications, and to alter it and redistribute it
  freely, subject to the following restrictions:

  1. The origin of this software must not be misrepresented; you must not
     claim that you wrote the original software. If you use this software
     in a product, an acknowledgment in the product documentation would be
     appreciated but is not required.
  2. Altered source versions must be plainly marked as such, and must not be
     misrepresented as being the original software.
  3. This notice may not be removed or altered from any source distribution.
*/

include "itunes_xml_parser.php";

// filename is the xml file, cat prefix is something different for each time you call the function
// ie. DrawPlaylist("techno_playlist.xml", "techno-");
function DrawPlaylist($filename, $cat_prefix)
{
	$output = NULL;
	
	// get "techno-f" and "tehcno-d" vars if they exsist
	// f stands for field, and d stants for direction
	$field = isset($_REQUEST[$cat_prefix."f"])?$_REQUEST[$cat_prefix."f"]:NULL;
	$direction = isset($_REQUEST[$cat_prefix."d"])?$_REQUEST[$cat_prefix."d"]:NULL;
	
	// load up the list of songs and sort
	$songs = iTunesXmlParser($filename, $field, $direction);
	
	// if the load was successful
	if ($songs)
	{
		// create an anchor so when the list is sorted, it comes back to where you are now
		$output .= '<a name="'.$cat_prefix.'"></a>';
		
		// start a table
		$output .= '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
		$output .= '<tr>';
		// for each field, create a link that will toggle up or down sorting direction
		// also add the anchor to the end of the link so when the page refreshes these
		// fields stay in the viewing area
		$output .= '<td><font size="1"><a href="example_advanced.php?'.$cat_prefix.'f=Name&'.$cat_prefix.'d='.(($field=="Name")?(($direction=="up")?"down":"up"):"down").'#'.$cat_prefix.'">name</a></font></td>';
		$output .= '<td><font size="1"><a href="example_advanced.php?'.$cat_prefix.'f=Artist&'.$cat_prefix.'d='.(($field=="Artist")?(($direction=="up")?"down":"up"):"down").'#'.$cat_prefix.'">artist</a></font></td>';
		$output .= '<td><font size="1"><a href="example_advanced.php?'.$cat_prefix.'f=Album&'.$cat_prefix.'d='.(($field=="Album")?(($direction=="up")?"down":"up"):"down").'#'.$cat_prefix.'">album</a></font></td>';
		$output .= '<td><font size="1"><a href="example_advanced.php?'.$cat_prefix.'f=Rating&'.$cat_prefix.'d='.(($field=="Rating")?(($direction=="up")?"down":"up"):"down").'#'.$cat_prefix.'">rating</a></font></td>';
		$output .= '</tr>';
		
		// now actually get the songs
		foreach ($songs as $song)
		{
			// display the field if the field is set
			$output .= '<tr>';
			$output .= '<td><font size="2">'.(isset($song["Name"])?$song["Name"]:NULL).'</font></td>';
			$output .= '<td><font size="2">'.(isset($song["Artist"])?$song["Artist"]:NULL).'</font></td>';
			$output .= '<td><font size="2">'.(isset($song["Album"])?$song["Album"]:NULL).'</font></td>';
			$output .= '<td><font size="2">'.(isset($song["Rating"])?$song["Rating"]:NULL).'</font></td>';
			$output .= '</tr>';
		}
		
		// end the table
		$output .= '</table>';
	}
	
	// show our results
	print ($output);
}


// *****************
// now for each playlist you can have a separate table

DrawPlaylist("techno_playlist.xml", "tech-");
