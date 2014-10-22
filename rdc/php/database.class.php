<?

// Singleton class for Database access

final class Database {
	static private $instance; // Singleton instance	
	public $mysql;
	
	private function __construct() {
		global $Config;
		// Database
		@$this->mysql = new mysqli($Config['mysql_host'], $Config['mysql_user'], $Config['mysql_password'], $Config['mysql_db']);
		if ($this->mysql->connect_error) {
			$error = sprintf("Database connection failed (%d): %s", $this->mysql->connect_errno, $this->mysql->connect_error);
			$friendlyError = sprintf("Database connection failed");
			throw new SystemException('db_failed', $error);
		}
		$this->mysql->set_charset('utf8'); // Everything should be in UTF8
	}
	
	static public function getInstance() {
		global $Node;
		if (!isset(self::$instance)) {
			self::$instance = new Database();
		}
		return self::$instance;
	}
	static public function isInstance() {
		if (!isset(self::$instance)) {
			return false;
		}
		return true;
	}
}

?>
