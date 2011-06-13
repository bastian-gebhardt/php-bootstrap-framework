<?php

/*
 * Example 'index.php' file to demonstrate the use of the Bootstrap Framework 
 * Please modify this file to your preferences
 * 
 * 
 * @author Bastian Gebhardt
 * @since	12.06.2011
 * @version	0.1
 * 
 */




/*
 * first we want to use the Bootstrap class from the 'xepl' package
 */
use xepl\Bootstrap;

/*
 * next we load the bootstrap framework (it's only a single class file)
 * 
 * the best is to locate the 'Bootstrap.php' file in the same directory like this file
 * another way is, the 'Bootstrap.php' file save to the central include directory, defined in php.ini
 */
require_once 'Bootstrap.php';

/*
 * finaly we are ready to initialise our PHP application:
 * 
 * BootStrap::initApp(<application_root>,<source_folder>,<replace_include_path>)
 * 
 * application_root:		the top folder of the application;	
 * 							if your index.php is located in a special web-folder
 * 							like 'application_root/web/index.php' it is the best to
 * 							define the application root this way: 
 * 							=> 'Bootstrap::useParentDir(__FILE__)'
 *	
 *							if your index.php is directly located in the 'application_root' 
 *					 		folder like 'application_root/index.php' it is the best to
 * 							define the application root this way: 
 * 							=> 'Bootstrap::useSameDir(__FILE__)'
 * 
 * source_folder:			top folder of the classes which defines the application
 * 							use a relative path to application_root for setting the sourcefolder
 *							remember: the defined folder has to exists otherwise an exception will be thrown
 *							=> valid values: '', '.', 'src', 'application'
 *
 * replace_include_path:	set this value either TRUE or FALSE
 * 							TRUE means the defined Includepath will be replaced by the source_folder
 * 							FALSE means the defined Includepath will be appended to the source_folder
 * 							to speed up the application, it is recommended to replace the include path
 */
BootStrap::initApp(Bootstrap::useParentDir(__FILE__), 'src', TRUE);




/* **** some optional useful commands below ************************************** */

/*
 * add some Library folders
 * 
 * use either relative path to 'application_root' or absolute path starting with '/'
 * remember: all folders you want to add have to be exist, otherwise exceptions will be thrown
 * 
 * for destinations inside the 'application_root' use relative paths
 * for all other destinations use absolute paths
 * so you will be able to move your project
 */
BootStrap::addLibDir('lib');
//BootStrap::addLibDir('/usr/php/lib');

/*
 * add autoloaders, so you not need to include class files manually
 * you can add more than one autoloader for mixed situations; 
 * the first you added will be called at first
 * 
 * -> there is an autoloader for classfiles using namespace
 * -> there is an autoloader for classfiles using zendstyle classnames (Zend_Db_Taple.php)
 */
BootStrap::addAutoLoader(BootStrap::AUTOLOADER_NAMESPACE);
BootStrap::addAutoLoader(BootStrap::AUTOLOADER_ZENDSTYLE);

/*
 * it's optional, but finaly add the debug handler for the autoloaders to retrieve
 * informations why autoloading fails
 * 
 * remember: after adding the debug handler, you cannot add further autoloaders
 */
BootStrap::finalyAddDebugAutoloader();

/* **** here ends the initialisation part ******************************************* */




/* **** Some class autoloader tests below ******************************************* */

// test namespace autoloader
use demo\AppDemo;
$demo = new AppDemo();

// test zendstyle autoloader
$zendstyle = new Zendstyle_Demo_AppDemo();

// test autoloading a non existing class
try{
	$fail = new NonExistingAppDemo();
}
catch (Exception $e){
	echo "Error: {$e->getMessage()}<br>";
}
?>