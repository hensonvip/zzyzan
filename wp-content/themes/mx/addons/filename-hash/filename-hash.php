<?php
/**
 * Filter {@see sanitize_file_name()} and return an MD5 hash.
 *
 * @param string $filename
 * @return string
 */
function make_filename_hash($filename) {
    $filename = strtolower($filename);
	$ext = strrchr($filename,'.');
    return md5(basename($filename,$ext)) . $ext;
}
add_filter('sanitize_file_name', 'make_filename_hash', 10);