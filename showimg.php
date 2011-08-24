<?php
# This script will take any number of query string values, add them to
# an image based on the options below, and optionally record the lines
# used to a MySQL DB.

# File name to build image on
$imageName = "base.png";

# Information for text placement (and other stuff)
$textPlacement = array ("horizontalCenter" => 450, "verticalCenter" => 162, "maxWidth" => 170, "fontAngle" => 5.4, "fontFace" => 'arial.ttf', "fontSize" => 20, "fontColor" => array(0x00, 0x00, 0x00));

# Lines to use if called with no Query String
$defaultLines = array("SERIOUS","BUSINESS");

# Record image impressions to MySql? True is != zero
$logToMySQL = 0;

# Record line used in image impression to MySql if not empty
function recordLine ($line = "") {
    IF (!empty($line)) {
    
        # MySql Connect - connection is $cid
        include('conn.php');
        
        $sql = "INSERT INTO says_lines (words, count, last) VALUES ('$line',1,'" . date(DATE_ATOM) . "') ON DUPLICATE KEY UPDATE count = count + 1, last = '" . date(DATE_ATOM) . "'";
        
        $retid = mysql_db_query($db, $sql, $cid); 
        IF (!$retid) { echo( mysql_error());} 
        
        # MySql Close
        include('close.php');
    }; 
}; 

# Returns width of line as rendered for image
function getLineSize($pSize, $pRotation, $pFont, $pString) {
    $bbox = imagettfbbox($pSize, $pRotation, $pFont, $pString);  
    $lineSize = array("x" => ($bbox[2]-$bbox[6]), "y" => ($bbox[5]-$bbox[1]));
    return $lineSize;
}

# Returns largest size that will fit max width, decending from set font size
function scaleSize($pSize, $pRotation, $pFont, $pString) {
    global $testrun;
    $curw = getLineSize($pSize, $pRotation, $pFont, $pString);
    while ($curw["x"] > 170) {
        $pSize = $pSize - 1;
        $curw = getLineSize($pSize, $pRotation, $pFont, $pString);
    }
    return $pSize;
}

# Add single line of text to image
function printLine ($image, $line, $lineNumber, $numberOfLines) {
    global $textPlacement;
    
    $color = imagecolorallocate($image, $textPlacement["fontColor"][0], $textPlacement["fontColor"][1], $textPlacement["fontColor"][2]);
    $font = $textPlacement["fontFace"];
    $fontSize = $textPlacement["fontSize"];
    $fontRotation = $textPlacement["fontAngle"];
    $str = stripslashes($line);
    
    $fontSize = scaleSize($fontSize, $fontRotation, $font, $str);  
    $lineSize = getLineSize($fontSize, $fontRotation, $font, $str);

    $newX = $textPlacement["horizontalCenter"] - $lineSize["x"] / 2;
    $newY = $textPlacement["verticalCenter"] - $lineSize["y"] / 2;
    
    $printCoord = findOffset($lineNumber, $numberOfLines, $newX, $newY);
    
    ImageTTFText($image, $fontSize, $fontRotation, $printCoord["x"], $printCoord["y"], $color, $font, $line);
}

# Returns coordinates for a line given its line number
function findOffset ($lineNumber, $numberOfLines, $orgX, $orgY) {
    global $textPlacement;
    
    $lineHeight = $textPlacement["fontSize"] * 1.5;
    $heightOffset = (($lineHeight*($numberOfLines-1))/-2)-$lineHeight;
    $lineOffset = $heightOffset + ($lineNumber * $lineHeight);
    $textAngle = $textPlacement["fontAngle"];

    # Needs redoing, noticeable misalignment at larger angles
    $newX = $orgX - intval(($lineOffset) * cos(deg2rad($textAngle + 90)));
    $newY = $orgY + intval(($lineOffset) * sin(deg2rad($textAngle + 90)));
    $newcoord = array("x" => $newX, "y" => $newY);

    return $newcoord;
}

# Returns array of lines to be used on sign
function GetLines () {
    global $defaultLines;
    IF (!empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $signLines);
    } ELSE {
        $signLines = $defaultLines;
    }
    return $signLines;
}

# Creates image and processes lines.
function showItBro ($logIt = 0) {
    global $image;
    $signLines = getLines();
    $numberOfLines = count($signLines);
    $lineNumber = 0;
    $combinedLines = "";
    $preSpace = "";
    foreach($signLines as $currentLine) {
        $lineNumber++;
        printLine ($image, $currentLine, $lineNumber, $numberOfLines);
        $combinedLines .= $preSpace . $currentLine;
        $preSpace = " ";
    }
    IF ($logIt != 0) {
        recordLine(addslashes($combinedLines));
    }
}

# Create image, dispose of resource
$image = ImageCreateFromPNG($imageName);
showItBro($logToMySQL);
header("Content-Type: image/PNG");
ImagePng ($image);
imagedestroy($image);
?>