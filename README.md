# Bootstrap Framework for PHP
## Intention
* set up a PHP application to a standard environment with only 3 commands
* add easily library folders for sharing  
* use predefined class autoloaders

## Requirements
PHP5.3 (namespace capability)

## Example index.php
	<?php	//file index.php
	
	use xepl\Bootstrap;
	
	require_once 'Bootstrap.php';
	
	BootStrap::initApp(Bootstrap::useSameDir(__FILE__), 'src', TRUE);
	
	?>

## Installation
The only things to do are:
* put the example _index.php_ and _Bootstrap.php_ located in the 'web' folder to the same folder in your project.
* modify the index.php for own purpose

The other files only needed for demo.	 