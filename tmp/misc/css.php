<?php
// crunches css files

// dir can ontain more than one value, as an array

class CSSCruncher
{
	// directories to process
	protected $dirs;
	// files to process
	protected $files;
	// type to process
	protected $type;

	// top-level folder we must operate within, for security reasons
	protected $styles_folder;
	
	// holds big string of all css to process
	protected $css;
	
	// latest update time of all source files (used for cachebreaking etc)
	protected $timestamp;
	
	public function __construct($dirs, $files = null, $type = null)
	{
		$this->styles_folder = realpath(SITE_ROOT . '../media.host/styles') . '/';
		
		if (empty($dirs))
		{
			$dirs = array();
			$dirs[0] = '_main';
		}
		elseif (!is_array($dirs))
		{
			$dirs = split(',', $dirs);
		}
		$this->dirs = $dirs;
		
		if (is_null($files))
		{
			$files = array();
		}
		elseif (!is_array($files))
		{
			$files = splt(',', $files);
		}
		$this->files = $files;
		
		if ($type == 'all')
		{
			$type = null;
		}
		$this->type = $type;
	}
	
	public function crunchToDisk()
	{
		return $this->process(true);
	}
	
	public function crunchAndReturn()
	{
		return $this->process(false);
	}
	
	protected function process($write_to_disk = false)
	{
		$result = '/* css file generated ' . date('l dS \o\f F Y h:i:s A') . "\n\n" . implode("\n", $this->readFiles()) . "\n*/\n\n";
		
		if ($write_to_disk)
		{
			$this->crunch();
		}
		else
		{
			$this->insertHeadings();
		}
		
		if ($write_to_disk) {
			$result .= "Output files:\n" . implode("\n", $this->writeFiles()) . "\n";
		}
		else
		{
			$result .= $this->getSingleFileOutput();
		}
		
		return $result;
	}
	
	
	/**
	 * Read in css files and collate into one big string of css
	 * 
	 * @return   array of filenames input
	 */
	public function readFiles()
	{
		$this->css = array();
		$return = array();
		
		// read in files
		
		foreach($this->files as $name)
		{
			// we can only open .css files in the current folder
			if (!endsWith(strtolower($name), 'css')) $name .= '.css';
			$name = realpath($this->styles_folder . $name);
		
			if (beginsWith($name, $this->styles_folder)) {
				$key = basename($name);
				$return[] = 'file: ' . $key;
				$this->importFile('files*', $key, $name);				
			}
		}

		$typeext = ( is_null($this->type) || $this->type == 'styles' ) ? '' : '.' . $this->type;
		foreach($this->dirs as $name) {
			
			$name = realpath($this->styles_folder . $name) . '/';
			
			// dirs must be subfolders of the current location for security
			if (beginsWith($name, $this->styles_folder)) {
				
				$dir_name = basename($name);
				
				if (is_dir($name)) {
					// array to store files in this dir
					$df = array();
				
					// Open current directory
					$dp = opendir($name);
					
					// Loop through the directory
					while ($entry = readdir($dp)) {
						$file = $name.$entry;
					
						// If $entry is a file...
						if (is_file($file)) {
							// we must have a file ending .css and not beginning with a shriek
							if ( endsWith(strtolower($file), $typeext . '.css') && !(beginsWith($entry, '!')) && !( ($this->type == 'styles') && (substr_count($entry, '.') > 1)))
							{
								$df[$entry] = $file;
							}
						}
					}
	
					// sort files in dir by file name
					ksort($df);
					
					foreach ($df as $key => $item)
					{
						$return[] = 'dirfile: ' . $key;
						$this->importFile($dir_name, $key, $item);
					}
							
					// Close it again!
					closedir($dp);
				}
			}
			else
			{
				trigger_error('Error: Specified folder . "' . $name . '" not within allowed path</p>');
			}
			
		}
		
		return $return;
	}
	
	protected function importFile($css_key, $file_key, $file_path)
	{
		$type = 'styles';
		$p2 = strrpos($file_key, '.');
		if ($p2 !== false)
		{
			$p1 = strpos($file_key, '.');
			if ( ($p1 !== false) && ($p2 > $p1) )
			{
				$type = substr($file_key, $p1 + 1, $p2 - $p1 - 1);
			}
		}
		if (!isset($this->css[$css_key]))
		{
			$this->css[$css_key] = array();
		}
		if (!isset($this->css[$css_key][$type]))
		{
			$this->css[$css_key][$type] = '';
		}
		$this->css[$css_key][$type] .= "\n\n/**" . trimOffEnd('.css', $file_key) . "*/\n\n" . file_get_contents($file_path);
		$this->timestamp = max($this->timestamp, filemtime($file_path));
	}

	
	public function crunch()
	{
		// optimise it
		foreach($this->css as $css_key => $types) {
			foreach($types as $file_key => $value)
			{
				// remove /*  */ comments - as long as they have at least 4 chars inside, cuz of /**/ hacks
				// ya know, i think this is actually wrong now i come to look at it again - but it seems to work???? so leave it for now...
				$value = preg_replace("/\/\*[^*](.|\n)*?\*\//",'', $value);
				
				// some of these below could use str_replace instead - must be more efficient???
				 
				// take note of { and } alongside whitespace - cuz othewise can be used in hacks etc.
				$value = preg_replace("/}\s/",'}¬', $value);
				$value = preg_replace("/\s{/",'¬{', $value);
				
				// replace spaces
				$value = preg_replace("/ +/",'¬', $value);
				
				// strip whitespace
				$value = preg_replace("/(\s)*/",'', $value);
				
				// replace ¬'s inserted above
				$value = preg_replace("/}¬/","}\n", $value);
				$value = preg_replace("/¬*{¬*/",'{', $value);
				
				// take out spaces not needed
				$value = preg_replace("/:¬/",':', $value);
				$value = preg_replace("/;¬/",';', $value);
				
				// put spaces back in
				$value = preg_replace("/¬/",' ', $value);
				
				// automatically inserted file marker comments
				$this->css[$css_key][$file_key] = preg_replace('/\/\*\*([^\*\/]*)\*\//',"\n\n/* \${1} */\n\n", $value);
			}
		}
	}
	
	protected function insertHeadings()
	{
		foreach($this->css as $css_key => $types) {
			foreach($types as $file_key => $value)
			{
				// automatically inserted file marker comments
				$this->css[$css_key][$file_key] = preg_replace('/\/\*\*([^\*\/]*)\*\//',"\n\n/**** \${1} ****/\n\n", $value);
			}
		}
	}
		
	public function writeFiles()
	{
		$timestamp = '/* css file generated ' . date('l dS \o\f F Y h:i:s A') . "*/\n\n";

		$return = array();
		
		foreach($this->css as $dir_name => $types) {
			if ($dir_name == '_main') {
				$dir_name = '';
			}
			if (substr($dir_name, 0, 1) == '_') {
				$dir_name = substr($dir_name, 1);
			}
			foreach($types as $key => $value) {
				$file = $dir_name . '/' . $key . '.css';
				$f = fopen($this->styles_folder . $file, 'w');
				fwrite($f, $timestamp . preg_replace('/\/\*\*([^\*\/]*)\*\//',"\n\n/**** \${1} ****/\n\n", $value));
				fclose($f);
				$return[] = $file;
			}
		}
		
		return $return;
	}
	
	public function getSingleFileOutput() 
	{
		$retval = '';
		foreach($this->css as $type)
		{
			foreach ($type as $value)
			{
				$retval .= $value . "\n";
			}
		}
		return $retval;	
	}
	
	// latest filemtime - used for cachebreakers etc
	public function getTimestamp()
	{
		return $this->timestamp;
	}
}


?>