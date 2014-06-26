#PHP-DEV
This repository is used to bootstrap a php project. It helps to monitor changes in files and run phpunit tests.

##Requirement
For this to works, you must have few dependency installed;

1. PHP (of course)
2. Composer
3. NodeJS NPM
4. Grunt-cli

##Installation
1. You can download this repository through the "Download ZIP" button on the right hand side of this page.
2. Unzip it and rename the folder to your project name.
3. `cd` into your project folder
4. Update `composer.json` with your autoload namespace if applicable
5. `composer install`.
6. After you have installed composer dependency, you can install grunt by `npm install`.
7. After you have installed grunt, you can start grunt with `grunt` and the watch process will begin.
8. Start developing in the `src` folder or the `tests` folder. Grunt should auto detect file changes and invoke phpunit.

##Feedback
If you have any question or suggestion, feel free to open a "New Issue" in the issue tab.