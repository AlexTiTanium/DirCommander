<?php

use DirCommander\DirCommander;
use DirCommander\Adapters\LocalDirCommanderAdapter;

/**
 * Created by JetBrains PhpStorm.
 * User: Alexander
 * Date: 02.10.12
 * Time: 17:40
 * To change this template use File | Settings | File Templates.
 */

class FileSystemLocalTest extends PHPUnit_Framework_TestCase {

  const DS = DIRECTORY_SEPARATOR;

  /**
   * @var DirCommander
   */
  private $dc;

  /**
   * @var string
   */
  private $testDir;

  /**
   * @var string
   */
  private $assertDir;

  /**
   *
   */
  protected function setUp(){

    $this->testDir = time();
    $this->assertDir = __DIR__ . self::DS . 'assert' . self::DS . $this->testDir;

    if(!mkdir($this->assertDir)) {
      new Exception('Can`t create assert dir');
    }

    $this->dc = new DirCommander(new LocalDirCommanderAdapter(), $this->assertDir);
  }

  /**
   *
   */
  protected function tearDown(){

    if(!$this->delete($this->assertDir)) {
      new Exception('Can`t remove assert dir');
    }

  }

  /**
   * Recursive file delete
   *
   * @param $path
   *
   * @return bool
   */
  private function delete($path){
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

    /**
     * @var SplFileInfo $file
     */
    foreach($it as $file) {
      if(in_array($file->getBasename(), array('.', '..'))) {
        continue;
      } elseif($file->isDir()) {
        rmdir($file->getPathname());
      } elseif($file->isFile() || $file->isLink()) {
        unlink($file->getPathname());
      }
    }

    return rmdir($path);
  }

  /**
   * -------------------------------------------------------------------------------------------------
   */

  /**
   * @group Core
   * @group DirCommander
   * @covers getCurrentPath::getCurrentDir
   */
  public function testGetCurrentDir(){

    $this->assertEquals($this->dc->getCurrentPath(), $this->assertDir);
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers getCurrentPath::getCurrentDir
   */
  public function testGetCurrentDirWin(){

    $dir = '\dir1\\dir2\dir3/dir4/dir5';
    $dc = new DirCommander(new LocalDirCommanderAdapter(), $dir);

    $this->assertEquals($dc->getCurrentPath(), self::DS.'dir1'.self::DS.'dir2'.self::DS.'dir3'.self::DS.'dir4'.self::DS.'dir5');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers getCurrentPath::getCurrentDir
   */
  public function testGetCurrentDirBadEnd(){

    $dir = '/dir/dir2/dir3/';
    $dc = new DirCommander(new LocalDirCommanderAdapter(), $dir);

    $this->assertEquals($dc->getCurrentPath(), self::DS.'dir'.self::DS.'dir2'.self::DS.'dir3');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers getCurrentPath::getCurrentDir
   */
  public function testGetCurrentDirBadFirst(){

    $dir = 'dir/dir2/dir3';
    $dc = new DirCommander(new LocalDirCommanderAdapter(), $dir);

    $this->assertEquals($dc->getCurrentPath(), self::DS.'dir'.self::DS.'dir2'.self::DS.'dir3');
  }

  /**
   * ONLY FOR WINDOWS
   *
   * @group Core
   * @group DirCommander
   * @covers getCurrentPath::getCurrentDir
   */
  public function testGetCurrentDirWindows(){

    $windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    if(!$windows) {
      return;
    }

    $dir = 'C:' . self::DS . 'dir' . self::DS . 'dir2' . self::DS . 'dir3';
    $dc = new DirCommander(new LocalDirCommanderAdapter(), $dir);

    $this->assertEquals($dc->getCurrentPath(), $dir, $dc->getCurrentPath());
  }


  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::makeDir
   */
  public function testMakeDir(){

    $dc = $this->dc;

    $dc->makeDir('new');
    $this->assertFileExists($this->assertDir . self::DS . 'new');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::remove
   */
  public function testRemoveDir(){

    $dc = $this->dc;

    $dc
      ->makeDir('forRemove')
      ->remove('forRemove');

    $this->assertFileNotExists($this->assertDir . self::DS . 'forRemove');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::cd
   */
  public function testCd(){

    $dc = $this->dc;

    $dc
      ->makeDir('checkCd')
      ->cd('checkCd');

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir . self::DS . 'checkCd');

    // we now in /checkCd
    // up simulation
    $dc
      ->makeDir('dep1')
      ->cd('dep1')
      ->cd('..');

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir . self::DS . 'checkCd', $dc->getCurrentPath());

    // we now in /checkCd
    $dc
      ->cd('dep1')
      ->makeDir('dep2')
      ->cd('dep2/..')
      ->cd('..');

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir . self::DS . 'checkCd', $dc->getCurrentPath());

    // we now in /checkCd
    $dc
      ->cd('dep1/dep2')
      ->cd('../..');

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir . self::DS . 'checkCd', $dc->getCurrentPath());

    // we now in /checkCd
    $dc
      ->cd('dep1/dep2/../..');

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir . self::DS . 'checkCd', $dc->getCurrentPath());

    // we now in /checkCd
    $dc
      ->cd('dep1/dep2/../../dep1/dep2/../..');

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir . self::DS . 'checkCd', $dc->getCurrentPath());

  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::up
   */
  public function testUp(){

    $dc = $this->dc;

    $dc
      ->makeDir('upTest')
      ->cd('upTest')
      ->up();

    $this->assertEquals($dc->getCurrentPath(), $this->assertDir, $dc->getCurrentPath());

  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::makeFile
   */
  public function testMakeFile(){

    $dc = $this->dc;

    $dc
      ->makeDir('files')
        ->cd('files')
        ->makeFile('f1.php')
        ->makeFile('f2.php', 'hello')
      ->up()
      ->makeDir('files2')
        ->cd('files2')
        ->makeFile('f3.php');

    $this->assertFileExists($this->assertDir.self::DS.'files'.self::DS.'f1.php');
    $this->assertFileExists($this->assertDir.self::DS.'files'.self::DS.'f2.php');
    $this->assertFileExists($this->assertDir.self::DS.'files2'.self::DS.'f3.php');

    $fileContent = file_get_contents($this->assertDir.self::DS.'files'.self::DS.'f2.php');

    $this->assertEquals($fileContent, 'hello');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::getContent
   */
  public function testGetContent(){

    $dc = $this->dc;

    $content = $dc
      ->makeDir('getContent')
        ->cd('getContent')
        ->makeFile('f2.php', 'hello')
        ->getContent('f2.php');

    $this->assertEquals($content, 'hello');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::copy
   * @covers FileSystem::paste
   */
  public function testCopyPaste(){

    $dc = $this->dc;

    $dc
      ->makeDir('copyPaste')
        ->cd('copyPaste')
        ->makeDir('d1')
          ->cd('d1')
          ->makeFile('f3.php')
          ->makeDir('d11')
            ->cd('d11')
            ->makeFile('f4.php')
          ->up()
        ->up()
        ->makeDir('d2')
          ->cd('d2')
          ->makeFile('f2.php')
        ->up();

    $dc
      ->copy('d1')
      ->paste('copyD1');

    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'f3.php');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'d11');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'d11'.self::DS.'f4.php');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::copy
   * @covers FileSystem::paste
   */
  public function testCopyPaste2(){

    $dc = $this->dc;

    $dc
      ->makeDir('copyPaste')
        ->cd('copyPaste')
        ->makeDir('d1')
          ->cd('d1')
          ->makeFile('f3.php')
          ->makeDir('d11')
            ->cd('d11')
            ->makeFile('f4.php')
          ->up()
        ->up()
        ->makeDir('d2')
          ->cd('d2')
          ->makeFile('f2.php')
        ->up();

    $dc
      ->copy('d1')
      ->makeDir('copyD1')
      ->paste('copyD1');

    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'f3.php');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'d11');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'d11'.self::DS.'f4.php');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::copy
   * @covers FileSystem::paste
   */
  public function testCopyPaste3(){

    $dc = $this->dc;

    $dc
      ->makeDir('copyPaste')
        ->cd('copyPaste')
        ->makeDir('d1')
          ->cd('d1')
          ->makeFile('f3.php')
          ->makeDir('d11')
            ->cd('d11')
            ->makeFile('f4.php')
          ->up()
        ->up()
        ->makeDir('d2')
          ->cd('d2')
          ->makeFile('f2.php')
        ->up();

    $dc
      ->copy('d1')
      ->makeDir('copyD1')
        ->cd('copyD1')
        ->paste();

    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'f3.php');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'d11');
    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'copyD1'.self::DS.'d11'.self::DS.'f4.php');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::copy
   * @covers FileSystem::paste
   */
  public function testCopyPasteFile(){

    $dc = $this->dc;

    $dc
      ->makeDir('copyPaste')
        ->cd('copyPaste')
        ->makeDir('d1')
          ->cd('d1')
          ->makeFile('f3.php')
          ->makeDir('d11')
            ->cd('d11')
            ->makeFile('f4.php')
          ->up()
        ->up()
        ->makeDir('d2')
          ->cd('d2')
          ->makeFile('f2.php')
        ->up();

    $dc
        ->cd('d1')
        ->copy('f3.php')
      ->up()
      ->cd('d2')
        ->paste();

    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'d2'.self::DS.'f3.php');
  }

  /**
   * @group Core
   * @group DirCommander
   * @covers FileSystem::copy
   * @covers FileSystem::paste
   */
  public function testCopyPasteFile2(){

    $dc = $this->dc;

    $dc
      ->makeDir('copyPaste')
        ->cd('copyPaste')
        ->makeDir('d1')
          ->cd('d1')
          ->makeFile('f3.php')
          ->makeDir('d11')
            ->cd('d11')
            ->makeFile('f4.php')
          ->up()
        ->up()
        ->makeDir('d2')
          ->cd('d2')
          ->makeFile('f2.php')
        ->up();

    $dc
        ->cd('d1')
        ->copy('f3.php')
      ->up()
      ->cd('d2')
        ->paste('new.php');

    $this->assertFileExists($this->assertDir.self::DS.'copyPaste'.self::DS.'d2'.self::DS.'new.php');
  }
}