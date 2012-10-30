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

namespace DirCommander\Adapters;

use DirCommander\IDirCommanderAdapter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * LocalDirCommanderAdapter
 *
 * Represent adapter for working with local file system
 *
 * @author Alex Kucherenko <kucherenko.email@gmail.com>
 * @since 1.0.0
 * @package DirCommander
*/
class LocalDirCommanderAdapter implements IDirCommanderAdapter {

  /**
   * Copy function.
   *
   * @param string $source Source file to copy.
   * @param string $destination Target path.
   *
   * @return boolean True on success.
   */
  public function copy($source, $destination){

    if(is_file($source)){ return copy($source, $destination); }

    $iterator =  new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST);

    foreach ($iterator as $node) {

      /**
       * @var \SplFileInfo $node
       * @var RecursiveDirectoryIterator $iterator
       */
      if ($node->isDir()) {
        if(!mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName())){ return false; };
      } else {
        if(!copy($node, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName())){ return false; }
      }
    }

    return true;
  }

  /**
   * File_put_contents function.
   *
   * @param string $filename Source file to copy.
   * @param string|array|resource $data Data to write.
   * @param int $flags Flags.
   *
   * @internal param resource $context Local filesystem context.
   *
   * @return integer|boolean Number of bytes written on success, false on fail.
   */
  public function putContents($filename, $data, $flags = 0){
    return file_put_contents($filename, $data, $flags);
  }

  /**
   *
   * @param string $filename Source file to chmod.
   * @param integer $mode Perm to chmod to.
   *
   * @return boolean True on success.
   */
  public function chmod($filename, $mode){
    return chmod($filename, $mode);
  }

  /**
   * Scandir function.
   *
   * @param string $directory Directory to scan.
   * @param integer $sorting_order Sort the contents?.
   *
   * @return array|boolean Array of contents on success, false on failure.
   */
  public function scandir($directory, $sorting_order = 0){
    return scandir($directory, $sorting_order);
  }

  /**
   * Rename function.
   *
   * @param string $oldname Old path to rename.
   * @param string $newname New path.
   *
   * @return boolean True on success.
   */
  public function rename($oldname, $newname){
    return rename($oldname, $newname);
  }

  /**
   * Recursive delete file or directory
   *
   * @param string $path
   *
   * @return boolean True on success.
   */
  public function remove($path){

    if(is_file($path)){ return unlink($path); }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

    /**
     * @var \SplFileInfo $node
     */
    foreach($iterator as $node) {
      if(in_array($node->getBasename(), array('.', '..'))) {
        continue;
      } elseif($node->isDir()) {
        if(!rmdir($node->getPathname())){ return false; }
      } elseif($node->isFile() || $node->isLink()) {
        if(!unlink($node->getPathname())){ return false; }
      }
    }

    return rmdir($path);
  }

  /**
   * Is_writable function.
   *
   * @param string $filename Path to check
   *
   * @return boolean True if is writable False if not.
   */
  public function isWritable($filename){
    return is_writable($filename);
  }

  /**
   * is_readable function.
   *
   * @param string $filename Path to check
   *
   * @return boolean True if is writable False if not.
   */
  public function isReadable($filename){
    return is_readable($filename);
  }

  /**
   * is dir function.
   *
   * @param string $path Path to check
   *
   * @return boolean True if is dir path False if not.
   */
  public function isDir($path){
    return is_dir($path);
  }

  /**
   * is file function.
   *
   * @param string $path Path to check
   *
   * @return boolean True if is file path False if not.
   */
  public function isFile($path){
    return is_file($path);
  }

  /**
   * Checks whether a file or directory exists.
   *
   * @param string $filename Path to the file or directory.
   *
   * @return boolean Returns TRUE if the file or directory specified by filename exists; FALSE otherwise.
   */
  public function isExist($filename){
    return file_exists($filename);
  }

  /**
   * Reads entire file into a string
   *
   * @param string $filename Name of the file to read.
   *
   * @return string|boolean The function returns the read data or FALSE on failure.
   */
  public function getContents($filename){
    return file_get_contents($filename);
  }

  /**
   * Makes directory
   *
   * @param string $pathname The directory path.
   * @param int $mode The mode is 0777 by default, which means the widest possible access. For more information on modes, read the details on the chmod() page.
   * @param bool $recursive Allows the creation of nested directories specified in the pathname.
   *
   * @return boolean Returns TRUE on success or FALSE on failure.
   */
  public function mkdir($pathname, $mode = 0777, $recursive = false){
    return mkdir($pathname, $mode, $recursive);
  }

}
