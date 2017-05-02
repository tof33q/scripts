<?php
class dbcheckup {

	public $logfile = "";
	public $newfile = "";

	private $_patterns = [
			'/eval\s?\(\s?(base64|eval|\$_|\$\$|\$[A-Za-z_0-9\s?\{\s?]*\s?(\(|\{|\[))/i'
		];

	
	public function __construct() {
		
	}


	public function check($file) {
		$basedir = dirname($file);

		$this->logfile = fopen($basedir . "/log_dbcheck.txt", 'w');
		$this->newfile = fopen($basedir . "/newsql.sql", 'w');

		$handle = fopen($file, 'r');
		while (!feof($handle)) {
			$sql = stream_get_line($handle, null, ";");
			$this->_test($sql);
		}
		fclose($handle);
	}

	private function _test(&$sql) {
		foreach ($this->_patterns as $pattern) {
			if (preg_match($pattern, $sql)) {
				fwrite($this->logfile, $sql);
				echo "malware found \n";
			} else {
				fwrite($this->newfile, $sql);
			}
		}
	}
}

if (isset($argv[1])) {
	set_time_limit(0);
	ini_set('memory_limit', '-1');

	$file = $argv[1];
	$dbcheck = new dbcheckup();
	$dbcheck->check($file); 
}