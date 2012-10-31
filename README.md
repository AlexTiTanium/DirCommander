The DirCommander is a simple lib for fast manipulating of the file system.

I created it for code generation purposes, but it's good for others tasks.

How to use:
================================

1.  First create an adapter. Adapter represents functions for working with the file system in PHP.
    Also adapter implements __IDirCommanderAdapter__ interface.

        $adapter = new LocalDirCommanderAdapter();

2. Create __DirCommander__ instance, passing adapter as it's first argument and root path.

        $rootPath = __DIR__; // no "/" in the end
        $dc = new DirCommander($adapter, $rootPath);

Now we may use $dc for manipulating file system.

Create a new directory in current root
---------------------------------------------------

    $dc->makeDir('newDir');

Remove a directory or file
-------------------------------------------------

    $dc->remove('newDir'); // all sub directories and files will be deleted
    $dc->remove('someFile.php')

__WARING: Removing directories and files is recursive!__

Get current directory
-------------------------------------------------

For example, if we pass */home/user* to constructor:

    echo $dc->getCurrentPath(); // prints /home/user

Change current directory
-----------------------------------------------

For example, we have __"alex"__ folder in */home/user* dir and current directory is:

    echo $dc->getCurrentPath(); // prints /home/user
    $dc->cd('alex')

It will change current directory to */home/user/alex*

    echo $dc->getCurrentPath() - prints /home/user/alex

It means that all future operations we will do in __alex__ directory.

Current directory __ALWAYS saved in object__.

If we do:

    $dc->makeDir('documents');

We will create directory "documents" in __alex__ directory.

The `$dc->makeDir()` does not change the current directory. Only two methods may change current directory: `$dc->cd()` and `$dc->up()`

Method `$dc->up()` change current dir to upper.
For example:

    echo $dc->getCurrentPath(); // prints /home/user/alex
    $dc->up();
    echo $dc->getCurrentPath(); // prints /home/user

Instead `$dc->up()` you may use `$dc->cd('..')`

Create file
----------------------------------------

    $dc->makeFile('file.php'); // create an empty file in the current directory
    $dc->makeFile('file.php', 'data'); // create file with text data

Get content of the file
---------------------------------------

    $dc->getContent('file.php'); // return string content

Copy and Paste:
---------------------------------------

For example:

    echo $dc->getCurrentPath() - prints /home/user/alex

   Current directory is __alex__, we need create a backup copy of it.

     $dc->up(); // now we in /home/user
     $dc->copy('alex');
     $dc->paste('alexBackup'); // it create /home/user/alexBackup directory

__Copy paste works recursive that mean your subdirectories and files was completely copy.__

It works for files too:

    $dc->copy('someFile.php');
    $dc->past('copySomeFile.php', true); // second argument true means, that if file exist it we be overwritten

You also may not past firs argument. For example:
dir __alex__ contains file __"someFile.php"__ we need copy it to user directories:

    echo $dc->getCurrentPath(); // prints /home/user/alex
    $dc->copy('someFile.php');
    $dc->up(); // now we in /home/user
    $dc->paste(); // it create copy of file "someFile.php" in /home/user dir

Chain of calls
---------------------------------

Only two methods return a string: __getContent__ and __getCurrentPath__.
Others return self, you may use it for fast manipulate file system.

Something like that:

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
        ->copy('d1')
        ->makeDir('copyD1')
          ->cd('copyD1')
          ->paste();

Error handling
----------------------------

All methods throws __DirCommanderException__ use try catch.

In the End
--------------------------

* More examples see in tests
* __WARNING!__ All operations not transactional!