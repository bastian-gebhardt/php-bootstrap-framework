<?php

namespace xepl;

use Exception;


/**
 * This class provides some static methods to bootstrap a php application
 * Bootstrapping means:
 * - defining the ROOT directory of the app
 * - defining source folder where are typicaly located the class files of the app
 * - defining a class autoloader
 * 
 * Additional with this class you can
 * - define one or more 'library' dirs, which will be add to the include Path
 * 
 * Other definitions should be set by the app itself using config files
 * 
 * 
 * @author Bastian Gebhardt
 * @since	05.06.2011
 * @version	0.1
 * 
 * @license LGPL
 * 
 * @package xepl (stands for e(X)t(E)nded(P)hp(L)ibrary similar to 'SPL')
 *
 */
class Bootstrap{
	
	const AUTOLOADER_NAMESPACE = 'namespaceAutoLoader';
	const AUTOLOADER_ZENDSTYLE = 'zendstyleAutoLoader';
	
	const INCLUDE_PATH_ADD_MODE_PREPEND = 'prepend';
	const INCLUDE_PATH_ADD_MODE_REPLACE = 'replace';
	const INCLUDE_PATH_ADD_MODE_APPEND = 'append';

	/**
	 * Path to the rootdir of the app (with trailing '/' )
	 * @var string
	 */
	private static $app_root_path = '';
	
	/**
	 * saves, if the 'debugautoloader' has been added or not
	 * @var boolean
	 */
	private static $is_debug_autoloader = FALSE;
	
	/**
	 * 
	 * useful for caching purpose; not implemented yet
	 * @var string
	 */
	private static $application_id;
	
	
	/**
	 * Initialises the application by defining its root path and sourcefolder
	 * 
	 * @param string $application_root - absolute path to the application root dir
	 * @param string $src_folder - root folder of the classfiles defining the app 
	 * @param boolean $replace_include_path - true: previous settings and settings by php.ini will be overwritten to zero
	 *
	 * @throws Exception
	 */
	public static function initApp($application_root,$src_folder,$replace_include_path){

		$add_include_path_mode = ($replace_include_path) ? self::INCLUDE_PATH_ADD_MODE_REPLACE : self::INCLUDE_PATH_ADD_MODE_PREPEND;

		if(is_dir($application_root)){
			//path allways ends with '/'
			self::$app_root_path = rtrim($application_root,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			self::$application_id = md5(self::$app_root_path);
			self::addIncludePath($src_folder, $add_include_path_mode);
		}
		else{
			throw new Exception("Given application root is not a dir", 1);
		}
	}
	
	/**
	 * Adds the given directory to the 'php include path'
	 * 
	 * @param string $dir - absolute (begins with '/') or relative (to approot) path specification are allowed
	 */
	public static function addLibDir($dir){
		self::addIncludePath($dir,self::INCLUDE_PATH_ADD_MODE_APPEND);
	}
	
	/**
	 * Returns the Path to the root folder; Path ends with '/'
	 *
	 * @return string - Path to the root folder of this application
	 */
	public static function getAppRootPath(){
		return self::$app_root_path;
	}
	
	/**
	 * @param string $file
	 * 
	 * @return string - absolute path to the directory where $file is located
	 */
	public static function useSameDir($file){
		return dirname($file);
	}

	/**
	 * @param string $file
	 * 
	 * @return string - absolute path to parent directory of the $file directory
	 */
	public static function useParentDir($file){
		return dirname(dirname($file));
	}	
	
	/**
	 * Adds one of the predefined Autoloaders to the autoloaderqueue;
	 * It is impossible to add further autoloaders if you have set the debugautoloader
	 * 
	 * Implemented Autoloaders are:
	 * - AUTOLOADER_NAMESPACE for classes with namespaces
	 * - AUTOLOADER_ZENDSTYLE for classes with names like Zend_Db_Taple.php
	 * @param const $autoloader_type - only use AUTOLOADER_NAMESPACE or AUTOLOADER_ZENDSTYLE
	 * @throws Exception
	 */
	public static function addAutoLoader($autoloader_type){
		if(self::$is_debug_autoloader){
			throw new Exception("DebugAutoloader was allready set, no further Autoloader can be added; Please set DebugAutoloader after all desired Autoloader has been added;", 1);
		}
		$test_type = (
			$autoloader_type == self::AUTOLOADER_NAMESPACE || 
			$autoloader_type == self::AUTOLOADER_ZENDSTYLE
		);
		if($test_type){
			spl_autoload_register(array(get_class(), $autoloader_type), TRUE, FALSE);
		}
		else{
			throw new Exception("unknown autoloader; avaiable autoloader are 'AUTOLOADER_NAMESPACE' or 'AUTOLOADER_ZENDSTYLE'", 1);
		}
	}
	
	/**
	 * Adds a debug output handler at the end of the autoloader queue; 
	 * Only with this handler the errormessage will contain the classname for which autoloading fails 
	 * Please remember: after using this command it is impossible to add further autoloaders using addAutoLoader();
	 */
	public static function finalyAddDebugAutoloader(){
		//TODO: Logging, if this method is used twice
		self::$is_debug_autoloader = TRUE;
		spl_autoload_register(array(get_class(), 'debugAutoLoader'), TRUE, FALSE);
	}
	
	private static function addIncludePath($dir,$add_mode){
		//TODO: test to increase the performance, by using only one include path?
		//TODO: extends this to use additionally phar:// 
		
		if($dir[0]!='/' && $dir!='.'){
			$dir = self::$app_root_path . $dir;
		}
		else if($dir=='.'){
			$dir = self::$app_root_path;
		}
		
		$dir = rtrim($dir,DIRECTORY_SEPARATOR);
		if(!is_dir($dir)){
			throw new Exception("Given path is not a dir", 2);
		}
		
		if($add_mode==self::INCLUDE_PATH_ADD_MODE_REPLACE){
			set_include_path($dir);
		}
		else if($add_mode==self::INCLUDE_PATH_ADD_MODE_PREPEND){
			set_include_path($dir.PATH_SEPARATOR.get_include_path());
		}
		else if($add_mode==self::INCLUDE_PATH_ADD_MODE_APPEND){
			set_include_path(get_include_path().PATH_SEPARATOR.$dir);
		}
	}
	
	/**
	 * transforms a namespace 'path' into a real path + filename without extension
	 * 
	 * @param string $classname 
	 */
	private static function namespaceAutoLoader($classname){
		$package = str_replace('\\', '/', $classname);
		self::loadClassFile($package);
	}
	
	/**
	 * transforms a 'Zend' like classname into a real path + filename without extension
	 *  
	 * @param string $classname
	 */	
	private static function zendstyleAutoLoader($classname){
		$package = str_replace('_', '/', $classname);
		self::loadClassFile($package);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $class - path + name of classfile to include (path/to/ClassFile) whithout the fileextension
	 */
	private static function loadClassFile($class){
		$file = $class.'.php';
		
		//'include' should not cause an error in this context, but should be executed faster than 'include_once'
		//error messages are bothering 
		return @include $file;
	}
	
	/**
	 * Debugginghandler, wich will be called, after all autoloader in the queue fails
	 * @param string $classname
	 * @throws Exception with classname, which could not be included
	 */
	private static function debugAutoLoader($classname){
		throw new Exception("Autoloading failed for class: '{$classname}'; No classdefinitionfile was found", 1);
	}
	
	/**
	 * This will not be running;
	 * Call this Class only in the static way 'Bootstrap::desiredMethod()'
	 */
	private function __construct(){
		//prevent instanciating this as an object
	}
}

?>