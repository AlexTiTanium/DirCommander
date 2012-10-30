<?php

/**
* Dir Commander
*
* @author Alex Kucherenko <kucherenko.email@gmail.com>
* @copyright 2012 Alex Kucherenko
* @version 1.0.0
*
* MIT LICENSE
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace DirCommander;

/**
 * IDirCommanderAdapter
 *
 * @author Alex Kucherenko <kucherenko.email@gmail.com>
 * @since 1.0.0
 * @package DirCommander
*/
interface IDirCommanderAdapter {

  /**
   * Copy function.
   *
   * @param string $source Source file to copy.
   * @param string $destination Target path.
   *
   * @return boolean True on success.
   */
  public function copy($source, $destination);

  /**
   * File_put_contents function.
   *
   * @param string $filename Source file to copy.
   * @param string|array|resource $data Data to write.
   * @param int $flags Flags.
   *
   * @return integer|boolean Number of bytes written on success, false on fail.
   */
  public function putContents($filename, $data, $flags = 0);

  /**
   * Reads entire file into a string
   *
   * @param string $filename Name of the file to read.
   *
   * @return string|boolean The function returns the read data or FALSE on failure.
   */
  public function getContents($filename);

  /**
   * Facade for the chmod function.
   *
   * @param string $filename Source file to chmod.
   * @param integer $mode Perm to chmod to.
   *
   * @return boolean True on success.
   */
  public function chmod($filename, $mode);

  /**
   * Scandir function.
   *
   * @param string $directory Directory to scan.
   * @param integer $sorting_order Sort the contents?.
   *
   * @return array|boolean Array of contents on success, false on failure.
   */
  public function scandir($directory, $sorting_order = 0);

  /**
   * Makes directory
   *
   * @param string $pathname The directory path.
   * @param int $mode The mode is 0777 by default, which means the widest possible access. For more information on modes, read the details on the chmod() page.
   * @param bool $recursive Allows the creation of nested directories specified in the pathname.
   *
   * @return boolean Returns TRUE on success or FALSE on failure.
   */
  public function mkdir($pathname, $mode = 0777 , $recursive = false);

  /**
   * Recursive delete file or directory
   *
   * @param string $path
   *
   * @return boolean True on success.
   */
  public function remove($path);

  /**
   * Rename function.
   *
   * @param string $oldname Old path to rename.
   * @param string $newname New path.
   *
   * @return boolean True on success.
   */
  public function rename($oldname, $newname);

  /**
   *
   * @param string $filename Path to check
   *
   * @return boolean True if is writable False if not.
   */
  public function isWritable($filename);

  /**
   * is_readable function.
   *
   * @param string $filename Path to check
   *
   * @return boolean True if is writable False if not.
   */
  public function isReadable($filename);

  /**
   * Checks whether a file or directory exists.
   *
   * @param string $filename Path to the file or directory.
   *
   * @return boolean Returns TRUE if the file or directory specified by filename exists; FALSE otherwise.
   */
  public function isExist($filename);
  /**
   * is dir function.
   *
   * @param string $path Path to check
   *
   * @return boolean True if is dir path False if not.
   */
  public function isDir($path);

  /**
   * is file function.
   *
   * @param string $path Path to check
   *
   * @return boolean True if is file path False if not.
   */
  public function isFile($path);

}
