<?php
/**
 * @version		$Id: file.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access

include_once('path.php');

/**
 * A File handling class
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
class JFile
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param	string	$file	The file name
	 *
	 * @return	string	The file extension
	 * @since 1.5
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.') + 1;
		return substr($file, $dot);
	}

	/**
	 * Strips the last extension off a file name
	 *
	 * @param	string	$file The file name
	 *
	 * @return	string	The file name without the extension
	 * @since 1.5
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param	string	$file	The name of the file [not full path]
	 *
	 * @return	string	The sanitised string
	 * @since	1.5
	 */
	public static function makeSafe($file)
	{
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
		return preg_replace($regex, '', $file);
	}


	/**
	 * Read the contents of a file
	 *
	 * @param	string	$filename	The full file path
	 * @param	boolean	$incpath	Use include path
	 * @param	int		$amount		Amount of file to read
	 * @param	int		$chunksize	Size of chunks to read
	 * @param	int		$offset		Offset of the file
	 *
	 * @return	mixed	Returns file contents or boolean False if failed
	 * @since	1.5
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
            
		// Initialise variables.
		$data = null;
		if ($amount && $chunksize > $amount) {
			$chunksize = $amount;
		}

		if (false === $fh = fopen($filename, 'r', $incpath)) {
        	echo $filename."<br>";
			return false;
		}

		clearstatcache();

		if ($offset) {
			fseek($fh, $offset);
		}

		if ($fsize = @ filesize($filename)) {
			if ($amount && $fsize > $amount) {
				$data = fread($fh, $amount);
			} else {
				$data = fread($fh, $fsize);
			}
		} else {
			$data = '';
			$x = 0;
			// While its:
			// 1: Not the end of the file AND
			// 2a: No Max Amount set OR
			// 2b: The length of the data is less than the max amount we want
			while (!feof($fh) && (!$amount || strlen($data) < $amount)) {
				$data .= fread($fh, $chunksize);
			}
		}
		fclose($fh);

		return $data;
	}

}
