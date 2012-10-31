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

use DirCommander\IDirCommanderAdapter;
use DirCommander\Exceptions\DirCommanderException;

/**
 * DirCommander
 * 
 * @site https://github.com/AlexTiTanium/DirCommander
 *
 * @author Alex Kucherenko <kucherenko.email@gmail.com>
 * @since 1.0.0
 * @package DirCommander
*/

class DirCommander {

  /**
   * @var string directory separator
   */
  const DS = DIRECTORY_SEPARATOR;

  const UNIX_DS = '/';
  const WIN_DS = '\\';

  /**
   * Indicators for buffer
   */
  const TYPE_FILE = 'file';
  const TYPE_DIRECTORY = 'dir';

  /**
   * @var IDirCommanderAdapter
   */
  private $adapter;

  /**
   * @var array - Array of path
   *
   * @example array('dir1', 'dir2', ...) => implode => /dir1/dir2
   */
  private $currentDir;

  /**
   * @var array - Path to file, uses for copy paste operation, and for cut paste
   *              It stored like that: array('path'=>'....', 'type'=>'file or dir')
   *              Type indicate that the path to file or directory
   */
  private $buffer;


  /**
   * @param IDirCommanderAdapter $adapter
   * @param string $rootPath
   */
  public function __construct(IDirCommanderAdapter $adapter, $rootPath){

    $this->adapter = $adapter;
    $this->setCurrentDir($rootPath);
  }

  /**
   * @return string
   */
  public function getCurrentPath(){

    $prefix = '';
    $windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    if(!$windows){ $prefix = '/'; }

    return $prefix.implode(self::DS, $this->currentDir);
  }

  /**
   * @param string $name The name of directory
   *
   * @throws DirCommanderException
   * @return DirCommander
   */
  public function makeDir($name){

    $path = $this->getCurrentPath();

    if(!$this->adapter->isExist($path)){
      throw new DirCommanderException('Directory "'.$path.'" not exist');
    }

    if(!$this->adapter->isWritable($path)){
      throw new DirCommanderException('Directory "'.$path.'" is not writable');
    }

    if(!$this->adapter->mkdir($path.self::DS.$name, 0755)){
      throw new DirCommanderException('Error on creating directory "'.$name.'" in "'.$path.'"');
    }

    return $this;
  }

  /**
   * @param string $name The name of directory
   *
   * @throws DirCommanderException
   * @return DirCommander
   */
  public function remove($name){

    $path = $this->getCurrentPath();

    if(!$this->adapter->isExist($path)){
      throw new DirCommanderException('Directory "'.$path.'" not exist');
    }

    if(!$this->adapter->isExist($path.self::DS.$name)){
      throw new DirCommanderException('Directory "'.$path.'" not exist');
    }

    if(!$this->adapter->remove($path.self::DS.$name)){
      throw new DirCommanderException('Error on creating directory "'.$name.'" in "'.$path.'"');
    }

    return $this;
  }

  /**
   * Change directory
   *
   * @param string $path
   *
   * @throws DirCommanderException
   * @return DirCommander
   */
  public function cd($path){

    $this->addPathToCurrentDir($path);

    $currentPath = $this->getCurrentPath();

    if(!$this->adapter->isExist($currentPath)){
      throw new DirCommanderException('Directory "'.$currentPath.'" not exist');
    }

    return $this;
  }

  /**
   * Up for one directory
   *
   *
   * @throws DirCommanderException
   * @return DirCommander
   * @throws DirCommanderException
   */
  public function up(){

    $this->popCurrentDir();

    $currentPath = $this->getCurrentPath();

    if(!$this->adapter->isExist($currentPath)){
      throw new DirCommanderException('Directory "'.$currentPath.'" not exist');
    }

    return $this;
  }

  /**
   * Create file and if is set data write to file
   *
   * @param string $name
   * @param string $data
   *
   * @throws DirCommanderException
   * @internal param string $path
   *
   * @return DirCommander
   * @throws DirCommanderException
   */
  public function makeFile($name, $data = ''){

    $path = $this->getCurrentPath();

    if(!$this->adapter->isWritable($path)){
      throw new DirCommanderException('Directory "'.$path.'" not writable');
    }

    if($this->adapter->putContents($path.self::DS.$name, $data)===false){
      throw new DirCommanderException('Error creating file "'.$path.self::DS.$name.'"');
    }

    return $this;
  }

  /**
   * Create file and if is set data write to file
   *
   * @param string $name
   *
   * @throws DirCommanderException
   * @return DirCommander
   */
  public function getContent($name){

    $path = $this->getCurrentPath().self::DS.$name;

    if(!$this->adapter->isExist($path)){
      throw new DirCommanderException('File "'.$path.'" not exist');
    }

    if(!$this->adapter->isReadable($path)){
      throw new DirCommanderException('File "'.$path.'" not readable');
    }

    $content = $this->adapter->getContents($path);

    if($content===false){
      throw new DirCommanderException('Error reading file "'.$path.'"');
    }

    return $content;
  }

  /**
   * Copy current file to buffer
   *
   * @param string $fileOrDirName
   *
   * @throws DirCommanderException
   * @return DirCommander
   * @throws DirCommanderException
   */
  public function copy($fileOrDirName){

    $path = $this->getCurrentPath().self::DS.$fileOrDirName;

    if(!$this->adapter->isExist($path)){
      throw new DirCommanderException('File "'.$path.'" not exist');
    }

    if(!$this->adapter->isReadable($path)){
      throw new DirCommanderException('File "'.$path.'" not readable');
    }

    $this->setBuffer($path);

    return $this;
  }

  /**
   * Copy current file to buffer
   *
   * @param bool|string $newNameFileOrDirectoryName
   * @param bool $overrideIfExist
   *
   * @throws DirCommanderException
   * @return DirCommander
   */
  public function paste($newNameFileOrDirectoryName = false, $overrideIfExist = false){

    $destination = $this->getCurrentPath();
    $destinationDir = $this->getCurrentPath();

    $sourcePath = $this->buffer['path'];
    $sourceType = $this->buffer['type'];

    if($newNameFileOrDirectoryName){
      $destination .= self::DS.$newNameFileOrDirectoryName;
    }

    if(!$newNameFileOrDirectoryName and $sourceType == self::TYPE_FILE){
      $pathInfo = pathinfo($sourcePath);
      $destination .= self::DS.$pathInfo['basename'];
    }

    if(!$newNameFileOrDirectoryName and $sourceType == self::TYPE_DIRECTORY and !$this->adapter->isExist($destination)){
      throw new DirCommanderException('Destination directory  "'.$destination.'" not exist');
    }

    if(!$this->adapter->isWritable($destinationDir)){
      throw new DirCommanderException('Directory "'.$destinationDir.'" not writable');
    }

    if(!$overrideIfExist and $sourceType == self::TYPE_FILE and $this->adapter->isExist($destination)){
      throw new DirCommanderException('File already exist "'.$destination.'"');
    }

    if($newNameFileOrDirectoryName and $sourceType == self::TYPE_DIRECTORY and !$this->adapter->isExist($destination)){
      $this->makeDir($newNameFileOrDirectoryName);
    }

    if(!$this->adapter->copy($sourcePath, $destination)){
      throw new DirCommanderException('Copy "'.$sourcePath.'" to "'.$destination.'" has failed');
    }

    return $this;
  }

/** ----------------------------------------------------------------------------------------
 * Private
 * -----------------------------------------------------------------------------------------
 */

  /**
   * Replace windows directory separator to unix
   *
   * @param string $string
   *
   * @example \dir\dir = > /dir/dir
   * @return string
   */
  private function toUnixDS($string){

    return str_replace(self::WIN_DS, self::UNIX_DS, $string);
  }

  /**
   * Explode path to array by directory separator, also removes empty elements
   *
   * @param string $path
   *
   * @example /dir1/dir2/ => array('dir1', 'dir2')
   * @return array
   */
  private function pathToArray($path){

    $stack = explode(self::UNIX_DS, $this->toUnixDS($path));

    // delete empty elements
    return array_filter($stack, function($value){
        return $value === '' ? false : true;
    });
  }

  /**
   * Set current dir
   *
   * @param string $path
   */
  private function setCurrentDir($path){

    $this->currentDir = $this->pathToArray($path);
  }

  /**
   * Add path to current path
   *
   * @param string $path
   */
  private function addPathToCurrentDir($path){

    $stack = $this->pathToArray($path);

    while ($path = current($stack)) {

      if($path != '..') { next($stack); continue; }

      $key = key($stack);

      if(prev($stack)!==false){
        unset($stack[key($stack)]);
      }else{
        $this->popCurrentDir();
      }

      unset($stack[$key]);
      reset($stack);
    }

    $this->currentDir = array_merge($this->currentDir,  $stack);
  }

  /**
   * Remove last element
   */
  private function popCurrentDir(){

    $lastElement = array_pop($this->currentDir);

    // if last element up symbols we remove it and to up
    if($lastElement == '..'){ $this->popCurrentDir(); }
  }

  /**
   * Detect file type and save buffer structure
   *
   * @param $path
   */
  private function setBuffer($path){

    $type = self::TYPE_FILE;

    if($this->adapter->isDir($path)){ $type = self::TYPE_DIRECTORY; }

    $this->buffer = array(
      'path'=>$path,
      'type'=>$type
    );
  }

}