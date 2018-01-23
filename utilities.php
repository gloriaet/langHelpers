<?php

// outputs a document heading tag and 
// stylesheet link, and a title tag
//  $stylesheet - name of stylesheet relative to this page
//  $title - title of page 
function printDocHeading($stylesheet, $title)
{
  print
    '<!DOCTYPE html>'. "\n" .
    '<html lang="en">' . "\n" .
    '<head> ' ."\n" .
    '<meta charset="utf-8" />'. "\n".
    '<meta name="viewport" content="width=device-width, initial-scale=1.0">' ."\n".
    '<meta name="author" content="">'. "\n".
    '<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />' . "\n". 
    '<link rel="icon" href="../favicon.ico" type="image/x-icon"  />' . "\n". 
    '<link rel="STYLESHEET" href="' . $stylesheet . '"  type="text/css" />'."\n" .
    '<title>' . "\n" .$title . "\n" .'</title> ' ."\n" .
    ' </head> '. "\n" ;
}

//creates database connection for LangHelpers database
function connectToDB()
    {
        $serverName = 'gtemple1.create.stedwards.edu';
        $username = 'gtemplec_lngUser';
        $password = 'tester';
        $dbName = 'gtemplec_LangHelpers';
        $conn = new mysqli($serverName, $username, $password, $dbName);
        if ($conn->connect_error)
        {
            die("Connection failed: " . $conn->connect_error);
        }
        //echo "Connected successfully";

        return $conn;
    }
?>