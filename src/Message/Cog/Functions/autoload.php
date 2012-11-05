<?php



/*****[ FUNCTION FOR AUTO LOADING CLASSES ]**********************************/

spl_autoload_register(function($class) {

	//BUILD A MAP OF CLASSES => PATHS
	static $classes = array();
	if (!$classes && defined('CLASS_AUTOLOAD_PATH') && is_dir(CLASS_AUTOLOAD_PATH)) {
		$dir = new RecursiveIteratorIterator( new RecursiveDirectoryIterator(CLASS_AUTOLOAD_PATH), true );
		foreach ($dir as $file) {
			//SKIP SUBVERSION FILES IF PRESENT
			if (strstr($file, '.svn')) {
				continue;
			}
			//SKIP GIT FILES IF PRESENT
			if (strstr($file, '.git')) {
				continue;
			}
			//PEAR DIRECTORY IS LOADED THROUGH INCLUDE PATH
			if (strstr($file, 'PEAR')) {
				continue;
			}
			//SKIP ABSTRACT VALIDATE CLASS TO AVOID COLLISION WITH SWEETCMS
			if (strstr($file, 'Validate/Validate.php')) {
				continue;
			}
			//SKIP UNNECESSARY CONTROLS FOR SITE AREA
			if (strstr($file, 'Controls/') && !strstr($file, 'Controls/'.AREA.'/')) {
				continue;
			}
			if ($file->isFile()) {
				
				$className = current(explode('.', $file->getFileName()));
				
				// HANDLE NAMESPACED DIRECTORIES
				if(substr($dir->getSubPath(), 0, 1) == '\\') {
					$dirName = preg_replace('/\/(.*)$/', '', substr($dir->getSubPath(), 1));
					$className = $dirName.'\\'.$className;
/* 					$classes[$dir->getSubPath().'\\'.$className] = $file->getPathName(); */
				} 
				
				$classes[$className] = $file->getPathName();
			}
		}
	}

	if(substr($class, 0, 1) == '\\') {
		$class = substr($class, 1);
	}
	
	// CHECK FOR NESTED NAMESPACES
	if(!isset($classes[$class])) {
		if (file_exists($file = CLASS_AUTOLOAD_PATH.'/\\'.str_replace('\\', '/', $class).'.php')) {
			$classes[$class] = $file;
		}
	}
	
	if (isset($classes[$class])) {
		require_once $classes[$class];
	}
});



?>