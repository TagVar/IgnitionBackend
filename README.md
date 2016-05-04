# IgnitionBackend
A modular, portable, AJAX compatible back-end for web applications with a custom front-end.

SYSTEM REQUIREMENTS: 

IgnitionBackend has been tested on a Linux machine running Debian Jessie (05/05/2016), Apache (05/05/2016), MySQL (05/05/2016), and PHP 5. Ignition has not been tested under any other conditions, but should function regardless of Linux distribution, or Apache distribution. PHP must be at least version 5, and later versions have not been tested for compatibility. TagVar made an initial test of IgnitionBackend on a WAMP server on Windows 7 whcih yielded compatibility issues, mostly due to pathing.

The root directory of your server must not contain a directory named "nodes".

BASE INSTALLATION INSTRUCTIONS:

The following instructions will teach you how to install the package manager of IgnitionBackend. This application reads Ignition packages, allows for the creation of new Ignition users, and allows pre-existing users with appropriate permissions to edit the permissions of other users. 

1) Donload the source files for IgnitionBacend either through cloning the repository at https://github.com/TagVar/IgnitionBackend.git, or downloading a ZIP compressed version at https://github.com/TagVar/IgnitionBackend/archive/master.zip.
2) Navigate into the "Package Manager" directory. Move into the "config" directory. Edit the "main_config.php" file with a plain text editor.
3) Add a root username and password the the "main_config.php" file. If you wish to, define a title for your administration panel. Finally, define the MySQL host, username, password, and database for your IgnitionBackend installation. Save the changes to the file. Please use an empty MySQL database to install IgnitionBackend. 

"main_config.php" Example: 

<?php

  $username = "MyUsername";
  $password = password_hash('MyPassword', PASSWORD_DEFAULT);

  $title = "My Title";

  $sql_host = "localhost";
  $sql_username = "SQLUsername";
  $sql_password = "SQLPassword";
  $sql_database = "IgnitionBackendDatabase";

  $connection = new mysqli($sql_host, $sql_username, $sql_password, $sql_database);

?>


4) Navigate to the root of your http server. Create a directory anywhere in the public folder of your webserver. Copy the contents of the "Package Manager" directory into the directory you just created. IgnitionBackend is extrmely portable and will function regardless of path as long as the containing directory is within your server's public directory.


PACKAGE INSTALLATION INSTRUCTIONS:

Packages are where the actual functionality of IgnitionBackend lies. TagVar has released the Blog, Content Management System (CMS), Newsletter, and Bookkepping packages. The Ecommerce package has also been included in this repository. The Ecommerce package is largely finished, but does require further work to be fully functional. Furthermore, the Bookkeeping package is completely functional, but in order to be useful requires its sister package, Bookkeeping Management, which has yet to be completed. 

Note that the Settings package is pre-installed in Ignition. This package is required for the package manager application to function properly, and must not be removed.

1) Navigate to the directory where the IgnitionBackend package manager is installed. 
2) Enter the "packages" directory.
3) Copy the directory (NOT the contents) of a package into the "packages" directory. 
4) Login to IgnitionBackend with a user that has Root permissions.
5) Follow the graphical prompts to install the package. 


ON NODES:

Some Ignition packages are intended to be interfaced with the front-end. When this is the case, Ignition will create a node for the aforementioned package. The first time a package requires a node, Ignition will create a directory named "nodes" in the root directory of your server. Certain dependency and configuration files will be installed into the "nodes" directory. These files must not be deleted or tampered with, or the packages may function incorrectly. 

Packaged that create node files will create a single API file within the directory, usually with a name similar to "package_name.php." Some packages may create other resources, or create other types of API files in subdirectories of the "nodes" directory. These files are package speciic. Please refer to the documentation for the installed packages to learn about the exact node format for the package. 

These node files contain simple functions for rendering data from the backend, usually with the use of templates and data deliminations. All nodes provided by TagVar are AJAX compatible, and can thus provide an easy way to create Single Page Applications that require a backend.

Project Creator: Allen Hundley

Email: Allen@TagVar.com
