<?php
$o_switches=array('h', 'r', 'l', 'i', 'v');
$o_strings=array('f', 'c', 'o');

function error($str='') {
   if (!empty($str)) echo "ERROR: $str\n\n";
   global $help;
   echo $help."\n";
   exit(1);
}

function option($opt) {
   global $options;
   return (isset($options[$opt])) ? $options[$opt] : false;
}

if ($argc<2) error();
else {
   $dirnames = array ();
   $options = array ();
   
   // counters
   $found = 0;
   $valid = 0;
   $bad_camera = 0;
   $omitted = 0;
   $renamed = 0;
   $unchanged = 0;
   $dirs_created = 0;
   
   // parsing command-line options
   for ($i=1; $i<$argc; $i++) {
      $val = $argv[$i];
      if ($val == '--help') error();
      else if ($val{0}=='-') // it's an option...
         for ($j=1; $j<strlen($val); $j++) {
            $c = $val{$j};
            if (in_array($c, $o_switches)) $options[$c]=true; // for switches
            else if (in_array($c, $o_strings)) { //for options with an argument
               if ($i+1==$argv) error("option -$c requires an argument");
               $options[$c]=$argv[$i+1];
               $i++;
            }
            else error("Unknown option: -$c");
         }
      else {
         if (is_dir($val)) $dirnames[]=$val;
         else error ("Directory $val does not exist");
      }
   }
   if (empty($dirnames))
      error('No directories specified (use . to process current dir)');
   
   $verbose = option('v');
   $offset = option('o') ? option('o') : 0;
   
   foreach ($dirnames as $dirname) {
      
      $dir = opendir($dirname);
      $renaming=array();
      while ($f = readdir($dir)) {
         if ($f=='.' || $f=='..') continue;
         if (!option('h') && $f{0}=='.') continue;
         if (is_dir("$dirname/$f")) continue; // we're not going recursively
         $found++;
         if (!exif_imagetype("$dirname/$f")) {
            if ($verbose) echo "[*] Omitting file $dirname/$f (no EXIF data)\n";
            $omitted++;
         }
         else { // it is an image with exif data
            $exif = exif_read_data("$dirname/$f");
            if (!$exif) {
               if ($verbose) echo "[!] Reading EXIF from $dirname/$f failed\n";
               continue;
            }
			//print_r($exif);
			//die();
            $timestamp = strtotime($exif['DateTime']);
            $timestamp += $offset;
            if (option('i')) { // info
               $date = date ('d M Y H:i:s', $timestamp);
               echo "[i] $dirname/$f: Taken on $date with {$exif['Model']}\n";
            }
            // checking camera model
            if (option('c') && strcasecmp($exif['Model'], option('c'))) {
               if ($verbose)
                  echo "[*] Camera model in $dirname/$f does not match\n";
               $bad_camera++;
               continue;
            }
            $valid++;
            
            // creating new filename
            $ext = end(explode('.',$f));
            if (option('l')) $ext=strtolower($ext);
            $format = option('f') ? option('f') : 'Y-m-d_His';
            $new_filename = date($format, $timestamp).'.'.$ext;
            $renaming[$f] = $new_filename;
            if (!option('r'))
               echo "[>] $dirname/$f -> $dirname/$new_filename\n";
         }
      }
      closedir($dir);
      
      if (empty($renaming))
         echo "[!] No valid files found in $dirname\n";
      else if (option('r'))
         foreach ($renaming as $k => $v) {
            // creating directories if needed
            $subdir = $dirname.'/'.dirname($v);
            if (!is_dir($subdir)) {
               if (mkdir ($subdir, 0700, true)) {
                  if ($verbose)
                     echo "[*] Created dir $subdir\n";
               }
               else echo "[!] Unable to create directory $subdir\n";
            }
            
            // checking if we can safely rename the file
			$same_count = 1;
            if ($k==$v) {
               $unchanged++;
               if ($verbose)
                  echo "[*] Filename of $dirname/$k isn't changed\n";
            }
            else if (file_exists("$dirname/$v")) {
               echo "[!] File $dirname/$k was not renamed to $dirname/$v "
                   ."because a file with that name already exists\n";
				   $newname = get_newname($v);
				   echo "newfile name = $newname";
				if (rename ("$dirname/$k", "$dirname/$newname")) {
                  $renamed++;
                  echo "[*] Renamed $dirname/$k to $dirname/$v\n";
               }   
            } else {
				$alt_count = 1; // reset
               // renaming
               if (rename ("$dirname/$k", "$dirname/$v")) {
                  $renamed++;
                  echo "[*] Renamed $dirname/$k to $dirname/$v\n";
               }
               else echo "[!] An error occured while renaming "
                        ."$dirname/$k to $dirname/$v\n";
            }
         }
      
   }
   
   // show a summary
   echo "[*] Found $found files, $valid of them were processed.\n";
   if (option('c'))
      echo "[*] Camera model in $bad_camera files did not match.\n";
   
   if (!$found) echo "[!] Didn't find any valid files. Quitting...\n";
   else {
      if ($omitted) echo "[*] $omitted files were omitted.\n";
      if (option('r')) {
         if ($unchanged)
            echo "[*] $unchanged filenames did not need to change.\n";
         if ($dirs_created) echo "[*] $dirs_created directories were created\n";
         echo "[*] Run finished, $renamed files were renamed.\n";
      }
      else echo
         "[*] Run the script with option -r to actually rename these files\n";
   }
}

$alt_count = 1;
$orig_name = "";
function get_newname($name) {
	global $alt_count,$orig_name;
	if ($name != $orig_name) {
		$alt_count = 1;
	}
	$orig_name = $name;
	$new = substr($name,0,strlen($name)-4);
	$new = $new."-$alt_count.jpg";
	$alt_count++;
	return $new;
}

?>