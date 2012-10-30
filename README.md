Welcome to Dir Commander README
================================

The DirCommander it is simple lib for fast manipulating file system.

I was created it for code generation purposes, but it good for others tasks.

How to use:
================================

1.  Firs create adapter. Adapter represent functions for working with file system in php.
    Also adapter implement __IDirCommanderAdapter__ interface.

        $adapter = new LocalDirCommanderAdapter();

2. Create __DirCommander__ instance and pass to constructor adapter (It takes     __IDirCommanderAdapter__) and root path.

        $rootPath = __DIR__; // no "/" in the end
        $dc = new DirCommander($adapter, $rootPath);

Now we may use $dc for manipulating file system.

Create new directory in current root
---------------------------------------------------

    $dc->makeDir('newDir');

Remove directory or file
-------------------------------------------------

    $dc->remove('newDir'); // all sub directory and files we be deleted
    $dc->remove('someFile.php')

__WARING: Dirs and files remove recursive!__

Get current directory:
-------------------------------------------------

if rootPath what we pass to constructor for example */home/user*:

    echo $dc->getCurrentPath(); // prints /home/user

Change current directory
-----------------------------------------------

For example we have in */home/user* dir __"alex"__ and current dir is:

    echo $dc->getCurrentPath(); // prints /home/user
    $dc->cd('alex')

It will be change current dir to */home/user/alex*

    echo $dc->getCurrentPath() - prints /home/user/alex

It means that all future operation we will do in alex dir.

Current dir __ALWAYS saves in object__.

If we do:

    $dc->makeDir('documents');

We will be create dir "documents" in __alex__ dir.

The `$dc->makeDir()` not change current dir. Change current dir may only two methods: `$dc->cd()` and `$dc->up()`

Method `$dc->up()` change current dir to upper.
For example:

    echo $dc->getCurrentPath(); // prints /home/user/alex
    $dc->up();
    echo $dc->getCurrentPath(); // prints /home/user

Instead `$dc->up()` you may use `$dc->cd('..')`

Create file
----------------------------------------

    $dc->makeFile('file.php'); // create empty file in current directory
    $dc->makeFile('file.php', 'data'); // create file with text data

Get content of file
---------------------------------------

    $dc->getContent('file.php'); // return string content

Copy and Paste:
---------------------------------------

For example:

    echo $dc->getCurrentPath() - prints /home/user/alex

   Current dir is __alex__, we need create backup copy of it.

     $dc->up(); // now we in /home/user
     $dc->copy('alex');
     $dc->paste('alexBackup'); // it crete /home/user/alexBackup dir

__Copy paste work recursive that mean your sub directories and files was completely copied.__

It work for files too:

    $dc->copy('someFile.php');
    $dc->past('copySomeFile.php', true); // second argument true means, that if file exist it we be overwritten

You also may not past firs argument. For example
dir __alex__ contains file __"someFile.php"__ we need copy it to user directory:

    echo $dc->getCurrentPath(); // prints /home/user/alex
    $dc->copy('someFile.php');
    $dc->up(); // now we in /home/user
    $dc->paste(); // it create copy file "someFile.php" in /home/user dir

Chain of calls
---------------------------------

Only two methods return string: __getContent__ and __getCurrentPath__,
others return self, you may use it for fast manipulate file system.

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

End last one
--------------------------

* More examples se in tests
* __WARING!__ All operation not transactional!