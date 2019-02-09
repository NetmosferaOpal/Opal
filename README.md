[release]: https://img.shields.io/github/release/PHPOpal/Opal.svg
[release-URL]: https://github.com/PHPOpal/Opal/releases
[status]: https://travis-ci.org/PHPOpal/Opal.svg?branch=master
[status-URL]: https://travis-ci.org/PHPOpal/Opal
[coverage]: https://coveralls.io/repos/github/PHPOpal/Opal/badge.svg?branch=master
[coverage-URL]: https://coveralls.io/github/PHPOpal/Opal?branch=master

# Opal

[![][release]][release-URL]
[![][status]][status-URL]
[![][coverage]][coverage-URL]

_Opal_ is a PHP autoloader for _Composer_ projects.

Here's a list of its goals and features:

- **Easy to use**. _Opal_ unintrusively integrates with _Composer_; you can use it while
  your dependencies continue using `Composer`'s own autoloader. _Opal_ can be  easily
  integrated in existing packages, especially if they already use a PSR-4 file system with
  a `VendorName\PackageName` namespace-prefix.
    
- **Low overhead**. Eliminates the need for class maps (i.e. _Composer_'s
  `--optimize-autoloader`) and it does not perform`file_exists()` calls; it simply maps
  PHP names to files. 
  
- **Auto-loading of files that cannot be autoloaded**. `.inc.php` files are automatically
  included when _Opal_ is activated. This makes the maintenance of the entries in
  `composer.json`'s `{ "autoload": { "files": [] } }` automatic, and compensates in part
  for the lack of PHP features such as _function autoloading_.
  
- **No installation during development**. No installation procedures such as _Composer_'s
  `dump-autoload` are needed during development.
  
- **Built-in PHP preprocessor**. PHP code can be preprocessed before it gets executed.
  Preprocessors are modular; they are distributed as _Composer_ packages themselves, and
  they hook into other packages' code before it gets executed. Some useful preprocessors
  are listed [here](./preprocessors.md).
  
- **Ecosystem integration**. All the described features (including the core preprocessors)
  integrate seamlessly with _Composer_, _OPCache_, _CodeCoverage_ and _xDebug_.

## Manual

- Setup
  - [Dynamic autoloader setup](#dynamic-autoloader-setup) (are you new? start here!)
  - [Static autoloader setup](#static-autoloader-setup)
- Design information
  - [Autoloader](#autoloader)
  - [Preprocessing](#preprocessing)

## Dynamic autoloader setup

The following steps enable the loader in **dynamic (or development) mode**. In dynamic
mode the loader responds to code changes instantaneously, as code is preprocessed at every
request. This mode reads the original files from the provided directories, preprocesses
them into new files in the cache directory, and finally includes them in PHP.

If you just want to see this in action without following these steps, you may try cloning
the [setup demo repository](https://github.com/PHPOpal/SetupDemo). 

1. Create a standard PHP _Composer_ project;<br>
   leave `{"autoload": {}}` and `{"autoload-dev": {}}` empty for now.
2. Run `composer require netmosfera/opal`.
3. Create an auto-loadable `.php` file (e.g. `src/Armor.php`):
   ```php
   <?php
   
   namespace StarkIndustries\Hulkbuster;

   class Armor
   {
       public function __construct(){
           echo __CLASS__ . "\n";
       }
   }
   ```
4. Create a statically-loadable `.inc.php` file (e.g. `src/enableArmor.inc.php`):
   ```php
   <?php
  
   namespace StarkIndustries\Hulkbuster;

   function enableArmor(Armor $armor){
       echo __FUNCTION__ . "\n";
   }
   ```
5. Create `opal.php` and `opal-dev.php` near `composer.json`.
6. Add the following code to `opal.php` (leave `opal-dev.php` empty for now):
   ```php
   <?php

   use function Netmosfera\Opal\loader;
   loader()->addPackage("StarkIndustries", "Hulkbuster", __DIR__ . "/src");
   //                    ^ Vendor           ^ Package    ^ Path
   ```
7. Add the files to `composer.json` like so:
   ```json
   {
       /* ... */
       
       "autoload": {
           "files": ["opal.php"]
       },
    
       "autoload-dev": {
           "files": ["opal-dev.php"]
       }
   } 
   ```
9. Run `composer dump-autoload`.
10. Create the directory `src-opal` (or any other name, wherever it's desired, but
    preferably near the directory `src`) and make sure it's writable. This is where
    preprocessed files will be saved and executed from. This directory will contain the
    preprocessed code from all the packages that make use of _Opal_, not just the root
    package. For this reason, you may want to add it to `.gitignore` right now.
11. Create your app-index or verify your installation with the following code:
    ```php
    <?php

    use function Netmosfera\Opal\loader;
    use function StarkIndustries\Hulkbuster\enableArmor;
    use StarkIndustries\Hulkbuster\Armor;

    // Init loaders
    require __DIR__ . "/vendor/autoload.php";
    loader()->beginDynamic(__DIR__ . "/src-opal"); // Adjust the path as needed
 
    // Test if the contents in the files are reachable
    $armor = new Armor();
    enableArmor($armor);
    ```

## Static loader setup 

The static loader will include files right from the cache directory directly, and no
attempt will be made to locate or preprocess the files if they are missing; instead, an
error will be thrown. **This is the fast no-overhead mode that should be used in
production**.

Since files will be loaded directly from the cache, we need to make sure that all the
files are actually in the cache. The _installation_ step works exactly like the dynamic
loader, except that it preprocesses the files all at once, rather than one by one
on-demand.
   
1. From the project root run `./vendor/bin/opal-install "./src-opal" 0755 0644`
    or call the installer from PHP or your build tools:
   ```php
   <?php

   use function Netmosfera\Opal\loader;

   require __DIR__ . "/vendor/autoload.php";
   loader()->install(__DIR__ . "/src-opal");
   ``` 
2. Switch to static mode: 
   ```php
   <?php

   use function Netmosfera\Opal\loader;
   use function StarkIndustries\Hulkbuster\enableArmor;
   use StarkIndustries\Hulkbuster\Armor;

   require __DIR__ . "/vendor/autoload.php";
   // loader()->beginDynamic(__DIR__ . "/src-opal");
   loader()->beginStatic(__DIR__ . "/src-opal");
 
   // Test if symbols are callable
   $armor = new Armor();
   enableArmor($armor);
   ```

## Design information

### 1. Autoloader 

Autoloaded files should only include definitions of global symbols, types/traits,
functions or constants (`const ... = ...;`), without executing any code that exists in
other files. This is mandatory because not all files can reach the code in other files at
definition time. For example `class B extends A {}` is capable of autoloading `A`'s file
at definition time, but `define("FOO", bar(123));` might not be able to reach the
definition of `bar()`, that is, when `bar()` will be defined only after `FOO` is
attempted to be defined. On the other hand, `const A = \A\B\C * 10;` will work even if `C`
is not defined yet. Ultimately, it depends on many factors: make sure you test that
everything works correctly.
   
### 1. Preprocessing

Code preprocessing should not be used to distort the original code's intent, but rather
automate tedious tasks that would be otherwise noisy to read, and hard to maintain.

#### 1.1 Preprocess scope 

Preprocessors can be applied on any source code, even of third parties, so long they
also use _Opal_ themselves (they probably should not, however; it's not a good idea
changing other people's code). Preprocessors may have different scopes. Depending on their
goal, they may preprocess only a single file, one or more namespaces, or even all the
files in the project. By default a preprocessor is executed on all files and it's the
preprocessor itself deciding whether to proceed with the transformation on a particular
file or not.

#### 1.2 Preprocess order

On occasion the preprocessing order may be relevant, because some preprocessors depend on
the work of other preprocessors in order to do their work.

The preprocessing order is defined by the order with which the preprocessors are
registered into _Opal_, and it is guaranteed even when multiple preprocessors depend on
the same "parent" preprocessor, because duplicate entries are discarded.

For example, if _A_ and _B_ are added, and another package adds _A_ again and _C_
afterwards, the preprocessing order will result in _ABC_, because the second added _A_ is
discarded; in other words, it is guaranteed that _A_ runs before _B_ and that _A_ runs
before _C_ but not that _A_ runs _immediately_ before _B_ or that _A_ runs immediately
before _C_. In order to depend on another preprocessor's work themselves, preprocessors
just need to register that preprocessor before they register themselves into _Opal_.

