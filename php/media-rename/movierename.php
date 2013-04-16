<?php
// outputs e.g.  somefile.txt was last modified: December 29 2002 22:16:23.
$root = '/Users/scott/temp/movies';
if ($handle = opendir($root)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != ".DS_Store") {
			$filename = "$root/$file";
			if (file_exists($filename) && is_dir($filename) == false) {
				$timestamp = filemtime($filename);
				$format = 'Y-m-d_His';
				$ext = end(explode('.',$file));
				$ext=strtolower($ext);
				$new_filename = date($format, $timestamp).'.'.$ext;
				if (rename ($filename, "$root/$new_filename")) {
					echo "[*] Renamed $filename to $root/$new_filename\n";
				}
			} 
        }
    }
    closedir($handle);
}
?>
