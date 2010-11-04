<?php
/*--------------------------------------------------------------------------
 * function  wordwrap2
 *--------------------------------------------------------------------------
 * purpose   wrap a text at specified width. ommit any html tag when
 *           specifying where to wrap. you may want to help the function
 *           by inserting <wbr> tags into your text where a long word may
 *           by hyphenated.
 *
 * input     string $str        text to wrap, may contain html
 *           int    $width      width, default 75
 *           string $break      linebreak, default to a blank
 *           string $separator  word seperator, default to a blank
 *           string $outprefix  prefix for every line, may contain html
 *
 * output    string word-wrapped text 
 *
 * changes   2002-02-19 [mschatz@ib-rahn.de] first public release
 *--------------------------------------------------------------------------
 * Copyright (C) 2002 by Michael Schatz, mschatz@ib-rahn.de
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the GNU General Public License for more details at
 * http://www.gnu.org/copyleft/gpl.html
 *--------------------------------------------------------------------------*/
function wordwrap2($str="", $width=75, $break=" ", $separator=" ", $outprefix="") {
    $wbr = "<wbr>";  // word break string;
	// adjust output width
    $width = $width - strlen(strip_tags($outprefix)); 
	// erase trailing blanks
    $str = trim($str);
    if ($str=="") {
		// is there nothing ?
        return $str; // Y:return input
    }
	// explode $str to array, deleting trailing spaces
    $strarray = explode($separator,$str);
    foreach ($strarray as $stritem) $stritem = trim($stritem);              // delete trailing spaces
    $outarray = array();                                                    // prepare array
    $ln = 0; $wd = 0;                                                       // initialize counters
    $outarray[$ln] = $outprefix;                                            // output prefix
    while ($wd < sizeof($strarray)) {                                       // loop for any word
        if (strlen(strip_tags($outarray[$ln]." ".$strarray[$wd]))>$width) { //   without tags greater than width ?
            $rlen = $width-1-strlen(strip_tags($outarray[$ln]." "));        //   Y:get remaining line length
            if (strpos($strarray[$wd],$wbr)>1) {                            //   | is there a wbr sign ?
                $tarray = explode($wbr,$strarray[$wd]);                     //   | Y:explode word to wordpieces
                $i = 0;                                                     //   | | initialize counter
                $tstr = "";                                                 //   | | result is empty
                while (($i<sizeof($tarray)) and (strlen($tstr.$tarray[$i])<$rlen)){
                                                                            //   | | loop for any array entry until rem length exceeded
                    $tstr .= $tarray[$i];                                   //   | |   add next word piece  
                    $i++;                                                   //   | |   inc counter
                }                                                           //   | | loop end
                if ($i > 0) {                                               //   | | have there been word pieces fitting on line ?
                    $outarray[$ln] .= $tstr."-".$break;                     //   | | Y:add word pieces to out line, adding '-'
                    $ln++;                                                  //   | | | next line
                    $outarray[$ln] = $outprefix;                            //   | | | output prefix
                    $strarray[$wd] = "";
                    for ($i=$i; $i<sizeof($tarray); $i++) {                 //   | | | loop for remainder of word
                        $strarray[$wd] .= $tarray[$i].$wbr;                 //   | | |   remainder of word next word
                    }                                                       //   | | | loop end
                } else {                                                    //   | |
                    $outarray[$ln] .= $break;                               //   | | N:add new line
                    $ln++;                                                  //   | | | new line
                    $outarray[$ln] = $outprefix;                            //   | | | output prefix
                }                                                           //   | |
            } else {                                                        //   |  
                $outarray[$ln] .= $break;                                   //   | N:insert break
                $ln++;                                                      //   | | next line
                while (strlen($strarray[$wd])>$width) {                     //   | | loop while word greater width
                    $outarray[$ln] = $outprefix.substr($strarray[$wd],0,$width).$break;
                                                                            //   | |   output width
                    $ln++;                                                  //   | |   next line
                    $strarray[$wd] = substr($strarray[$wd],$width);         //   | |   set word remainder
                }                                                           //   | | loop end
                $outarray[$ln] = $outprefix.$strarray[$wd]." ";             //   | | insert text
                $wd++;                                                      //   | | next word
            }                                                               //
        } else {                                                            //   N:
            $outarray[$ln] .= $strarray[$wd]." ";                           //   | append
            $wd++;                                                          //   | next word
        }                                                                   //
    }                                                                       //   end of loop
    return implode("",$outarray);                                           // return the array as one string
} // end of wordwrap2
?>
