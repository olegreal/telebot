<?php
class DB {
	
	// Disabling debug by default
	var $defaultDebug = false;
	
	// Starting time
	var $mtStart;
	
	// Queries Count
	// var $nbQueries;
	// Last result
	var $lastResult;
	var $ECHO_MYSQL_QUERIES = false;

	// Connection
	public function __construct(){
		$this->connected = false;
		$this->delayed_enabled = false;
		$this->delayed_cache_dir = sys_get_temp_dir()."/sql_insert_cache";
		/*
		 * if($this->delayed_enabled==true and !is_dir($this->delayed_cache_dir)){
		 * try{mkdir($this->delayed_cache_dir);}
		 * catch (Exception $e){$this->delayed_enabled=false;}
		 * }
		 * if(!is_dir($this->delayed_cache_dir)){$this->delayed_enabled=false;}
		 */
	}

	public function connect(){
		global $CONFIG;
		// $this->mtStart = $this->getMicroTime();
		// $this->nbQueries = 0;
		$this->lastResult = NULL;
		
		// $this->mysqli_connection=@mysqli_connect($CONFIG['DBHost'], $CONFIG['DBUser'], $CONFIG['DBPass'],$CONFIG['DBName'],3306,"/var/lib/mysql.TMPFS/mysql.sock") or die('');
		$this->mysqli_connection = @mysqli_connect($CONFIG['DBHost'],$CONFIG['DBUser'],$CONFIG['DBPass'],$CONFIG['DBName'],$CONFIG['DBPort']);
		// var_dump($this->mysqli_connection);
		
		/* check connection */
		// SECOND ATTEMPT!!
		if (mysqli_connect_errno()) {
			// printf("MYSQLI Connect failed: %s\n", mysqli_connect_error());exit();
			$this->mysqli_connection = @mysqli_connect($CONFIG['DBHost2'],$CONFIG['DBUser'],$CONFIG['DBPass'],$CONFIG['DBName'],$CONFIG['DBPort']);
		}
		
		/* check if server is alive */
		// if (mysqli_ping($this->mysqli_connection)) {
		// printf ("Our connection is ok!\n");
		// } else {
		// printf ("MYSQLI Error: %s\n", mysqli_error($link));
		// }
		
		/* close connection */
		// mysqli_close($link);
		
		// TEST THIS:
		// mysqli_set_charset($dbcon, 'utf8');
		mysqli_query($this->mysqli_connection,"SET sql_mode = ''");
		mysqli_query($this->mysqli_connection,"SET NAMES utf8mb4_bin");
		mysqli_query($this->mysqli_connection,"SET CHARSET utf8mb4");
		mysqli_query($this->mysqli_connection,"SET CHARACTER SET utf8mb4");
		mysqli_query($this->mysqli_connection,"SET SESSION collation_connection = 'utf8mb4_bin'");
		
		$this->connected = true;
	}

	public function delayed_execute($query){
		if ($this->delayed_enabled==true) {
			file_put_contents("{$this->delayed_cache_dir}/{$GLOBALS ['time']}.sql",$query.";\n",FILE_APPEND);
		} else {
			return $this->execute($query);
		}
		return $this;
	}

	public function multiquery(array $querys): array{
		// die ( implode ( ';', $querys ) );
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() start',$querys);
		if (!$this->connected) {
			$this->connect();
		}
		/* execute multi query */
		if (mysqli_multi_query($this->mysqli_connection,implode(';',$querys))) {
			$RESULT = [];
			$i = 0;
			do {
				// while ( mysqli_next_result ( $this->mysqli_connection ) ) {
				/* store first result set */
				if ($result = mysqli_store_result($this->mysqli_connection)) {
					$RESULT[$i] = [];
					while ( $row = mysqli_fetch_assoc($result) ) {
						$RESULT[$i][] = $row;
					}
					mysqli_free_result($result);
					$i++;
				}
				/* print divider */
				// if (mysqli_more_results($link)) {
				// printf("-----------------\n");
				// }
			}
			while ( @mysqli_next_result($this->mysqli_connection) );
			return $RESULT;
		}
	}

	public function query($query, $debug = -1){
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() start',$query);
		if (!$this->connected) {
			$this->connect();
		}
		$this->lastResult = mysqli_query($this->mysqli_connection,$query,MYSQLI_STORE_RESULT);
		// if($this->ECHO_MYSQL_QUERIES){echo $query."<hr>".microtime_diff($GLOBALS['start_mtime'])."<hr>";}
		// $this->debug($debug, $query, $this->lastResult);
		// var_dump($this->lastResult);
		unset($q);
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() finish');
		return $this->lastResult;
	}

	/**
	 * Do the same as query() but do not return nor store result.\n
	 * Should be used for INSERT, UPDATE, DELETE...
	 * 
	 * @param $query The
	 *        	query.
	 * @param $debug If
	 *        	true, it output the query and the resulting table.
	 */
	public function execute($query, $debug = -1){
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() start',$query);
		if (!$this->connected) {
			$this->connect();
		}
		// $this->nbQueries++;
		$this->lastResult = mysqli_query($this->mysqli_connection,$query);
		// if($this->ECHO_MYSQL_QUERIES){echo $query."<hr>";}
		// $this->debug($debug, $query);
		unset($query);
		// mysqli_affected_rows
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() finish');
		return $this;
	}

	/**
	 * Convenient method for mysql_fetch_object().
	 * 
	 * @param $result The
	 *        	ressource returned by query(). If NULL, the last result returned by query() will be used.
	 * @return An object representing a data row.
	 */
	public function next_record($result = NULL){
		if ($result==NULL) {
			$result = $this->lastResult;
		}
		return (($result==NULL||mysqli_num_rows($result)<1)?NULL:mysqli_fetch_assoc($result));
	}

	/**
	 * Get the number of rows of a query.
	 * 
	 * @param $result The
	 *        	ressource returned by query(). If NULL, the last result returned by query() will be used.
	 * @return The number of rows of the query (0 or more).
	 */
	public function num_rows($result = NULL){
		if ($result==NULL) {
			if ($this->lastResult) {
				return mysqli_num_rows($this->lastResult);
			} else {
				return 0;
			}
		} else {
			return mysqli_num_rows($result);
		}
	}

	/**
	 * Internal function to debug when MySQL encountered an error,
	 * even if debug is set to Off.
	 * 
	 * @param $query The
	 *        	SQL query to echo before diying.
	 */
	public function debugAndDie($query){
		$this->debugQuery($query,"Error");
		// die("<p style=\"margin: 2px;\">"."DATABASE ERROR"."</p></div>");
		die("<p style=\"margin: 2px;\">".mysqli_error($this->mysqli_connection)."</p></div>");
	}

	/**
	 * Internal function to debug a MySQL query.\n
	 * Show the query and output the resulting table if not NULL.
	 * 
	 * @param $debug The
	 *        	parameter passed to query() functions. Can be boolean or -1 (default).
	 * @param $query The
	 *        	SQL query to debug.
	 * @param $result The
	 *        	resulting table of the query, if available.
	 */
	public function count_affected_rows(){
		return $this->LAST_AFFECTED_ROWS = mysqli_affected_rows($this->mysqli_connection);
	}

	public function debug($debug, $query, $result = NULL){
		if ($debug===-1&&$this->defaultDebug===false) return;
		if ($debug===false) return;
		
		$reason = ($debug===-1?"Default Debug":"Debug");
		$this->debugQuery($query,$reason);
		if ($result==NULL) {
			
			echo "<p style=\"margin: 2px;\">Number of affected rows: ".$this->count_affected_rows()."</p></div>";
		} else {
			$this->debugResult($result);
		}
	}

	/**
	 * Internal function to output a query for debug purpose.\n
	 * Should be followed by a call to debugResult() or an echo of "</div>".
	 * 
	 * @param $query The
	 *        	SQL query to debug.
	 * @param $reason The
	 *        	reason why this function is called: "Default Debug", "Debug" or "Error".
	 */
	public function debugQuery($query, $reason = "Debug"){
		$color = ($reason=="Error"?"red":"orange");
		echo "<div style=\"border: solid $color 1px; margin: 2px;\">"."<p style=\"margin: 0 0 2px 0; padding: 0; background-color: #DDF;\">"."<strong style=\"padding: 0 3px; background-color: $color; color: white;\">$reason:</strong> "."<span style=\"font-family: monospace;\">".htmlentities($query,HTML_ENTITIES,'UTF-8')."</span></p>";
	}

	/**
	 * Internal function to output a table representing the result of a query, for debug purpose.\n
	 * Should be preceded by a call to debugQuery().
	 * 
	 * @param $result The
	 *        	resulting table of the query.
	 */
	public function debugResult($result){
		echo "<table border=\"1\" style=\"margin: 2px;\">"."<thead style=\"font-size: 80%\">";
		
		$numFields = mysqli_num_fields($this->mysqli_connection,$result);
		
		// BEGIN HEADER
		$tables = array();
		$nbTables = -1;
		$lastTable = "";
		$fields = array();
		$nbFields = -1;
		while ( $column = mysqli_fetch_field($this->connection,$result) ) {
			if ($column->table!=$lastTable) {
				$nbTables++;
				$tables[$nbTables] = array(
					"name" => $column->table,
					"count" => 1
				);
			} else
				$tables[$nbTables]["count"]++;
			$lastTable = $column->table;
			$nbFields++;
			$fields[$nbFields] = $column->name;
		}
		
		for($i = 0; $i<=$nbTables; $i++)
			echo "<th colspan=".$tables[$i]["count"].">".$tables[$i]["name"]."</th>";
		echo "</thead>";
		echo "<thead style=\"font-size: 80%\">";
		for($i = 0; $i<=$nbFields; $i++)
			echo "<th>".$fields[$i]."</th>";
		echo "</thead>";
		// END HEADER
		while ( $row = mysqli_fetch_array($this->mysqli_connection,$result) ) {
			echo "<tr>";
			for($i = 0; $i<$numFields; $i++)
				echo "<td>".htmlentities($row[$i],HTML_ENTITIES,'UTF-8')."</td>";
			echo "</tr>";
		}
		
		echo "</table></div>";
		$this->resetFetch($result);
	}

	/**
	 * Get how many time the script took from the begin of this object.
	 * 
	 * @return The script execution time in seconds since the
	 *         creation of this object.
	 */
	public function getExecTime(){
		return round(($this->getMicroTime()-$this->mtStart)*1000)/1000;
	}

	/**
	 * Get the number of queries executed from the begin of this object.
	 * 
	 * @return The number of queries executed on the database server since the
	 *         creation of this object.
	 */
	// public function getQueriesCount()
	// {
	// #return $this->nbQueries;
	// }
	/**
	 * Go back to the first element of the result line.
	 * 
	 * @param $result The
	 *        	resssource returned by a query() function.
	 */
	public function resetFetch($result){
		if (mysqli_num_rows($this->mysqli_connection,$result)>0) {
			mysqli_data_seek($this->connection,$result,0);
		}
	}

	/**
	 * Get the id of the very last inserted row.
	 * 
	 * @return The id of the very last inserted row (in any table).
	 */
	public function lastInsertedId(){
		return mysqli_insert_id($this->mysqli_connection);
	}

	/**
	 * Close the connexion with the database server.\n
	 * It's usually unneeded since PHP do it automatically at script end.
	 */
	public function close(){
		mysqli_close($this->mysqli_connection);
	}

	/**
	 * Internal method to get the current time.
	 * 
	 * @return The current time in seconds with microseconds (in float format).
	 */
	public function getMicroTime(){
		list($msec,$sec) = explode(' ',microtime());
		return floor($sec/1000)+$msec;
	}

	public function upsert($tablename, $idfieldname, $array){
		if (!empty($tablename) and !empty($idfieldname) and $idfieldname!="*") {
			if (is_object($array)) {
				$array = get_object_vars($array);
			}
			if (is_array($array)) {
				$this->query("SELECT $idfieldname FROM $tablename WHERE `$idfieldname` ='".$array[$idfieldname]."'");
				if ($this->num_rows()==0) {
					$this->insert($tablename,$array,false);
				} else {
					$this->update($tablename,$idfieldname,$array,array(),false);
				}
			}
		}
		return $this;
	}

	public function stringify($fieldvalue){
		if (is_array($fieldvalue)) {
			return json_encode($fieldvalue);
		} elseif (is_object($fieldvalue)) {
			return json_encode(get_object_vars($fieldvalue));
		} elseif ($fieldvalue===true) {
			return 'true';
		} elseif ($fieldvalue===false) {
			return 'false';
		} else {
			return $fieldvalue;
		}
	}

	// function returns ARRAY of rows in select
	public function select($q, string $key = '', string $groupbykey = '', string $onlyfield = ''){
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() start',$q);
		$this->query($q);
		unset($q);
		$r = [];
		if (empty($onlyfield)) {
			if (empty($key)) {
				while ( $b = $this->next_record() ) {
					$r[] = $b;
				}
			} else {
				if (empty($groupbykey)) {
					while ( $b = $this->next_record() ) {
						$r[$b[$key]] = $b;
					}
				} else {
					while ( $b = $this->next_record() ) {
						$r[$b[$groupbykey]][$b[$key]] = $b;
					}
				}
			}
		} else {
			// select fieldvalues array
			while ( $b = $this->next_record() ) {
				$r[] = $b[$onlyfield];
			}
		}
		// mysqli_free_result($this->lastResult); #try that ?
		$this->lastResult = NULL;
		// DEBUG_SNAPSHOT(__CLASS__.'->'.__FUNCTION__.'() finish');
		return $r;
	}

	public function table_has_column($table, $column){}

	public function insert($tablename, $array, $delayed = false, $only_return_generated_query = false){
		if (is_object($array)) {
			$array = get_object_vars($array);
		}
		if (!empty($tablename) and is_array($array)) {
			$SQL_FIELDS_ARRAY = $SQL_VALUES_ARRAY = [];
			foreach ( $array as $fieldname => $fieldvalue ) {
				$SQL_FIELDS_ARRAY[] = "`".addslashes($fieldname)."`";
				$SQL_VALUES_ARRAY[] = "'".addslashes($this->stringify($fieldvalue))."'";
			}
			$QUERY = "INSERT INTO `$tablename` (".implode(',',$SQL_FIELDS_ARRAY).") VALUES (".implode(',',$SQL_VALUES_ARRAY).")";
			if ($only_return_generated_query==true) {
				return $QUERY;
			} else {
				if ($delayed==false) {
					$this->execute($QUERY);
					return $this->lastInsertedId();
				} else {
					$this->delayed_execute($QUERY);
					return 0;
				}
			}
		} else {
			return 0;
		}
	}

	public function insert_multi($tablename, $array2, $delayed = false){
		if (!empty($tablename) and is_array($array2)) {
			$fieldnames = $GLOBALS['GLOBALCLASS']->get_valid_fieldnames($tablename);
			$SQL_FIELDS_ARRAY = array_map(function ($v){
				return '`'.$v.'`';
			},array_keys($array2[0]));
			$SQL_VALUES_ARRAY2 = [];
			foreach ( $array2 as $fieldname2 => $array ) {
				if (is_object($array)) {
					$array = get_object_vars($array);
				}
				$SQL_VALUES_ARRAY = [];
				foreach ( $array as $fieldname => $fieldvalue ) {
					if (in_array($fieldname,$fieldnames)) {
						$SQL_VALUES_ARRAY[] = "'".addslashes($this->stringify($fieldvalue))."'";
					}
				}
				$SQL_VALUES_ARRAY2[] = "(".implode(',',$SQL_VALUES_ARRAY).")";
			}
			$QUERY = "INSERT INTO `$tablename` (".implode(',',$SQL_FIELDS_ARRAY).") VALUES ".implode(',',$SQL_VALUES_ARRAY2);
			if (!empty($SQL_VALUES_ARRAY2)) {
				if ($delayed==false) {
					$this->execute($QUERY);
					return $this->lastInsertedId();
				} else {
					$this->delayed_execute($QUERY);
					return 0;
				}
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	public function update($tablename, $idfieldname, $array, $raw_fields = array(), $delayed = false, $only_return_generated_query = false){
		if (is_object($array)) {
			$array = get_object_vars($array);
		}
		if (is_array($array) and !empty($tablename) and !empty($idfieldname) and $idfieldname!="*") {
			$SQL_QUERY = "UPDATE $tablename SET ";
			$SQL_DATA_ARRAY = [];
			foreach ( $array as $fieldname => $fieldvalue ) {
				if ($fieldname!=$idfieldname) {
					if (in_array($fieldname,$raw_fields) or $fieldvalue==NULL) {
						if ($fieldvalue==NULL) {
							$fieldvalue = 'NULL';
						}
						$pushval = "`".addslashes($fieldname)."`=$fieldvalue";
					} else {
						$pushval = "`".addslashes($fieldname)."`='".addslashes($this->stringify($fieldvalue))."'";
					}
					$SQL_DATA_ARRAY[] = $pushval;
				}
			}
			if (!empty($SQL_DATA_ARRAY)) {
				$SQL_QUERY .= implode(',',$SQL_DATA_ARRAY)." WHERE `$idfieldname`='".$array[$idfieldname]."'";
				if ($only_return_generated_query==true) {
					return $SQL_QUERY;
				} else {
					if ($delayed==true) {
						$this->delayed_execute($SQL_QUERY);
					} else {
						$this->execute($SQL_QUERY);
					}
				}
			}
		}
		return $this;
	}
}
$GLOBALS['db'] = new DB();