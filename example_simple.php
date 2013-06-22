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

// must do this, include itunes_xml_parser_php5.php if your server is php v5
include "itunes_xml_parser.php";

// get songs from the xml file
$songs = iTunesXmlParser("techno_playlist.xml");

// if it worked
if ($songs)
{
	// create a table
	$output = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
	
	// fill the first row with headings
	$output .= '<tr>';
	$output .= '<td><font size="1">name</font></td>';
	$output .= '<td><font size="1">artist</font></td>';
	$output .= '<td><font size="1">album</font></td>';
	$output .= '<td><font size="1">rating</font></td>';
	$output .= '</tr>';
		
	// loop through the songs in the array and get 4 fields that I want to see
	foreach ($songs as $song)
	{
		$output .= '<tr>';
		$output .= '<td><font size="2">'.$song["Name"].'</font></td>';
		$output .= '<td><font size="2">'.$song["Artist"].'</font></td>';
		$output .= '<td><font size="2">'.$song["Album"].'</font></td>';
		$output .= '<td><font size="2">'.$song["Rating"].'</font></td>';
		$output .= '</tr>';
	}
	
	// end the table
	$output .= '</table>';
	
	// show my new table
	print ($output);
}
