<?php

set_time_limit(0);

if (!isset($argv[1])) {
die("Usage: add-subtitles file.mp4");
}

$argv[1] = str_ireplace('\\', '/', $argv[1]);
$file = pathinfo($argv[1]);
$dirname = $file['dirname'];
$basename = $file['basename'];
$extension = $file['extension'];
$name = $file['filename'];
unset($file);   
copy('MP4Box.exe', $dirname.'/MP4Box.exe');
copy('libgpac.dll', $dirname.'/libgpac.dll');
$output = array();
$new_path = chdir($dirname);           
print "MP4Box.exe -ttxt \"$name.srt\" \r\n";
$s = exec("MP4Box.exe -ttxt \"$name.srt\" ", $output);
if (stristr($output[1], 'Conversion done') === false) {
    print_r($output);
}
print "Converted .srt to .ttxt.\r\n";
print "MP4Box.exe -add \"$name.ttxt\":lang=en \"$basename\"\r\n";
$s = exec("MP4Box.exe -add \"$name.ttxt\":lang=en \"$basename\"", $output);
print_r($output);
print "Added subtitles to video file.\r\n";
unlink("$name.ttxt");

$f = fopen("$basename", 'rb+');
$last = '';
$current = ''; 
while (!feof($f)) {
    $position = ftell($f) - 16384; # Beginning of $last 
    $current  = fread($f, 16384); # 16KB of file.
    $string = $last.$current; # 32KB of file.
    $find = (stristr($string, 'text') !== false) ? true : false; 
    if ($find) {
        $strpos = strpos($string, 'text');        
        $string = str_ireplace('text', 'sbtl', $string);
        fseek($f, $position);
        fwrite($f, $string);
        print "Replaced text for sbtl.\r\n";    	
    	  break;
    }
    $last = $current;
}

rename("$dirname/$basename", "$dirname/$name.m4v");
print "Changed file extension to .m4v.\r\n";
unlink('MP4Box.exe');
unlink('libgpac.dll');
print "Subtitles added successfully.\r\n";