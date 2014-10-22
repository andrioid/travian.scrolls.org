<?php 

function fromCache($key) {
	if (function_exists('apc_fetch') and ($result = @apc_fetch($key))) {
		return $result;
	}
	return false;
}

function toCache($key, $data, $ttl=-1) {
	global $Config;
	if (!function_exists('apc_store')) { return false; }
	if ($ttl == -1) { $ttl = 60*60*12; }
	if (!@apc_store($key, $data, $ttl)) { return false; }
	return true;
}

function arraytolist ($array) {
	$list = '';
	if (!is_array($array)) { return false; }
	$count = count($array);
	foreach ($array as $i=>$item) {
		$list .= $item;
		if ($i != $count-1) { $list .= ","; }
	}
	return $list;
}

function listtoarray ($list) {
	$strip = array(' ');
	$list = str_replace($strip, '', $list);
	$return = explode(',', $list);
	return $return;
}



// A list parent class that should help with paging and maybe rss/json feeds
class SystemList {
	public $list = array(); // our object list
	public $q = ''; // This is the base query (can be overwritten)
	private $cache = true;
	private $conditions = array();
	private $page = 0; // defaults
	private $perPage = 0; // defaults
	private $order = null; // asc/desc (no defaults, skipped if null)
	private $orderBy = null; // field name (no defaults, skipped if null)
	private $groupBy = null;
	private $fields = array();
	private $fieldTypes = '';
	private $identifier = null; // if null, we'll use the md5 of the sql query (used for caching)
	public $total = 0; // Total result (SQL_COUNT_ROWS)
	public $count = 0; // Current list count
	public $cacheTime = 0; // Cache time in seconds

	public function __construct() {
		global $Config;
		$this->cacheTime = $Config['cacheTime'];
	}
	
	// We will handle WHERE and LIMIT for the query here
	public function populate () {
		$where = '';
		$order = '';
		$paging = '';
		$paraType = '';
		$query = '';
		$this->list = array(); // We must clear it
		$para = array();
		if (empty($this->q)) { throw new Exception("Cannot populate a list without a query"); }
		
		
		// Conditions into "where"
		$where = $this->processConditions();
		
		// Paging
		if ($this->page > 0) {
			$from = (int)($this->page-1) * (int)$this->perPage;
		} else {
			$from = 0;
		}
		if ($this->perPage > 0) { // Skip limit if not defined 
			$paging = sprintf("LIMIT %d,%d", $from, $this->perPage);
		}
		
		// Order
		if ($this->orderBy != null) {
			$order = sprintf("ORDER BY %s", $this->orderBy);
			if ($this->order != null) {
				$order .= " ".$this->order;
			}
		}
		
		// Group By
		if ($this->groupBy != null) {
			$groupby = sprintf("GROUP BY %s", $this->groupBy);
		} else { $groupby = ''; }

		// Create the new query
		$query = sprintf("%s %s %s %s %s", $this->q, $where, $groupby, $order, $paging);
		//printf("Query: %s<br>", htmlspecialchars($query));

		// Caching
		if ($this->identifier != null) { 
			$cacheName = $this->identifier; 
		} else { 
			$cacheName = md5($query.$this->fieldTypes.arraytolist($this->fields));
		}
		
		if ($this->cache == true) {
			list($this->list, $this->count, $this->total) = fromCache($cacheName);
			if (is_array($this->list) and count($this->list) > 0) {
				return true;
			}
		}
		//printf("Using database for: %s <br>", $this->q);
		$db = Database::getInstance();


		// Prepare the query
		if (!$stmt = $db->mysql->prepare($query)) { 
			throw new Exception("Could not prepare statement: ".$db->mysql->error);
		}

		// Do some binding to the statement
		// - This is some voodoo shit, we have to dynamically handle all parameters and output variables
		// - Read comments on: http://www.php.net/manual/en/mysqli-stmt.bind-result.php
		if (strlen($this->fieldTypes) > 0) {
			call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, &$this->fieldTypes), $this->fields));
		}
		//echo "<pre>"; print_r($stmt); echo "</pre>";
		if (!$stmt->execute()) { throw new Exception("Failed to execute: ".$stmt->error); }
		
		$result = mysqli_stmt_result_metadata($stmt); 
		$data = array(); // Store the results in
		$args = array();

		while ($field = mysqli_fetch_field($result)) {
			$name = $field->name; 
			$args[$name] =& $data[$field->name]; 
        } 
        if (!@call_user_func_array(array(&$stmt, "bind_result"), $args)) {
			printf("SystemList: Name conflict in query, please fix.");
			exit(0);
		}

		
		while ($stmt->fetch()) {
			$this->processRow($args);
		}
		// Update count
		$this->count = count($this->list);
		// Update total count
		if (!$result = $db->mysql->query("SELECT FOUND_ROWS()")) { throw new SystemException("List failed to update total"); }
		list ($this->total) = $result->fetch_row();
		
		if (!toCache($cacheName,array($this->list, $this->count, $this->total),$this->cacheTime)) {
			throw new Exception("Failed to store cache.");
		}

		if ($this->list == null) { // In case the processing method does something weird
			$this->list = array(); 
		}

		
		$stmt->close();
		
	}
	protected function processRow() {
		// This should be replaced with your own object method
		// - this prototype simply converts to stdclass and adds to list
		/* Important note:
		 * - Each row is stored in $data as a reference. When the next row is called, those values change.
		 * - We need to copy the values before exiting this function
		 */
		$args = func_get_args();
		$out = array();
		$numarg = count($args);
		// de-referencing the array (damn this is ugly)
		foreach ($args[0] as $key=>$value) {
			$out[$key] = $value;
		}
		
		$this->list[] = (object)$out;
		//echo "<pre>"; print_r($this->list); echo "</pre>";
		return true;
	}


	 public function setCache($cache) {
		 if (is_bool($cache)) { 
			 $this->cache = $cache;
			 return true;
		 }
		 if (is_numeric($cache)) { 
			if ($cache == 0) {
				$this->cache = false;
				return true;
			} else if ($cache == 1) {
				$this->cache = true;
				return true;
			}
		 }
		return false;
	 }

	// Offer lists to name their caching value (so they can clear it)
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}
	 
	 public function addCondition ($field, $value, $sign='=', $op='AND', $opjoin=false, $internal=false) {
		 // Example: ('username', 'moocow', '=', 'AND', false)
		 // - opjoin dictates if the value should be put into previous parenthesis or left seperate.
		 // - the $op is ignored for the first value (as it doesn't matter)
		 if (is_array($value)) {
			 foreach ($value as $v) {
				 $c = new stdClass();
				 $c->field = $field;
				 $c->value = $v;
				 $c->sign = $sign;
				 $c->op = $op;
				 $c->opjoin = $opjoin;
				 $c->internal = $internal;
				 $this->conditions[] = $c;
				 //printf("Array value %s: %s<br>\n", $field, $v);
			 }
		 } else {
			$c = new stdClass();
			$c->field = $field;
			$c->value = $value;
			$c->sign = $sign;
			$c->op = $op;
			$c->opjoin = $opjoin;
			$c->internal = $internal;
			$this->conditions[] = $c;
		}
	 }
	 
	 public function processConditions() {
		 $p =& $this->conditions;
		 $i = 0;
		 $num = count($p);
		 $where = '';
		 $this->fieldTypes = ''; // We must reinitialize
		 $this->fields = array(); // We must reinitialize
		 if ($num > 0) { $where = 'WHERE '; }
		 foreach ($p as $key=>$v) {
			if ($i != 0 && $v->opjoin == false) { $where .= ') '; } // Close any lingering parenthesis
			if ($i != 0) { $where .= sprintf(" %s ", strtoupper($v->op)); }
			if ($i == 0 || $v->opjoin == false) { $where .= '('; } // Initial condition, open with parenthesis
			if ($v->internal == false) {
				$where .= sprintf("%s %s ?", $v->field, $v->sign);
				// Fix binding
				if (is_double($v->value)) {
					$this->fieldTypes .= 'd';
				} elseif (is_int($v->value)) {
					$this->fieldTypes .= 'i';
				} else {
					$this->fieldTypes .= 's';
				}
				$this->fields[] = &$v->value;				
			} else {
				$where .= sprintf("%s %s %s", $v->field, $v->sign, $v->value);
			}
			if ($i+1 >= $num) { $where .= ")"; } // Last line, close anything remaining
			
			// Increment
			$i++;
		 }
		 
		// Debug
		//printf("<h3>Debug: %s</h3>\n", $this->fieldTypes);
		//echo "<pre>"; print_r($this->fields); echo "</pre>";

		return $where;
	 }
	 
	 public function setPaging ($page, $perPage=20) {
		 $this->page = (int)$page;
		 $this->perPage = (int)$perPage;
		 return true;
	 }
	 
	 public function setOrder ($orderBy, $first='asc') {
		 if (strtolower($first) == 'asc') {
			 $this->order = 'ASC';
		 } else {
			 $this->order = 'DESC';
		 }
		 $this->orderBy = $orderBy;
		 return true;
	 }
	 
	 public function setQuery ($query) {
		 $query = preg_replace('/^SELECT/i', 'SELECT SQL_CALC_FOUND_ROWS', $query);
		 $this->q = $query;
	 }
	 
	 public function setNavigation ($nav) { // Allows us to set paging and order with the nav variable
		 $this->page = (int)$nav['page'];
		 $this->perPage = (int)$nav['number'];
		 $this->orderBy = $nav['sort'];
		 $this->order = $nav['order'];
	 }
	 
	 public function random($num=null) {
		 shuffle($this->list);
		 $count = count($this->list);
		 if ($num != null) {
			if ($count<$num) { $num=$count; }
			$return = array();
			for ($i=0; $i<$num; $i++) {
				$return[] = $this->list[$i];
			}
			$this->list = $return;
		}
	 }
	 
	 public function showConditions() {
		 echo "<pre>"; print_r($this->conditions); echo "</pre>";
	 }
	 
	 public function setGroupBy ($gb) {
		 $this->groupBy = $gb;
	 }

	 public function setCacheTime ($time) {
		$this->cacheTime =$time;
	 }
}
?>
