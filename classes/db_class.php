<?php
    require_once(CFG_DIR . "db_access.php");

    define("LOG_FILE_NAME", LOGS_DIR . "sql");
    
    class DB {
        var $server;
        var $dbname;
        var $login;
        var $pass;
        
        var $dbh;
        
        var $log_file_name; 
        
        var $last_errno = 0;
        var $last_error = "";
        
        var $last_query = '';
        
        var $affected_rows = 0;
        
        
        function __construct($server = "", $dbname = "", $login = "", $pass = "") {
            if (empty($server)) { 
                if (defined("Hostname")) {
                    $server = Hostname;
                } else {
                    return false;
                }
            }
            
            if (empty($dbname)) { 
                if (defined("DBName")) {
                    $dbname = DBName;
                } else {
                    return false;
                }
            }
            
            if (empty($login)) { 
                if (defined("Username")) {
                    $login = Username;
                } else {
                    return false;
                }
            }
            
            if (empty($pass)) { 
                if (defined("Password")) {
                    $pass = Password;
                } else {
                    return false;
                }
            }
            
            $this->log_file_name = LOG_FILE_NAME . '-' . $dbname . '.log';

            $this->dbh = mysqli_connect($server, $login, $pass);
            if ($this->NoError()) {
                mysqli_select_db($this->dbh, $dbname);
                if (!$this->NoError()) {
                    unset($this->dbh);
                } else {
                    $this->Execute('set names UTF8');
                }
            }
        } // MyDB
        
        
        function __destruct() {
            mysqli_close($this->dbh);
            unset($this->dbh);
        }


        function SwitchBase($dbname) {
            mysqli_select_db($dbname, $this->dbh);
            $this->log_file_name = LOG_FILE_NAME . '-' . $dbname . '.log';
        }   
             
        
        function LogIt($sql) {
            $log_file = fopen($this->log_file_name, "a+");
            $sql = trim($sql);
            $sql = preg_replace("/\s+/is", " ", $sql);
            //date_default_timezone_set('UTC');
            fwrite($log_file, date("d-m-Y, H:i:s") . " - " . $sql . "\r\n");
            fclose($log_file);
        }
        
        
        function NoError() {
			if (mysqli_errno($this->dbh) != 0) {
				$this->last_errno = mysqli_errno($this->dbh);
				$this->last_error = mysqli_error($this->dbh);

				$error = "MySQL error #" . $this->last_errno . ": " . $this->last_error;
				if (DB_DEBUG) {
					print "<pre>";
					print $error;
					debug_print_backtrace();
					print "</pre>";
				}
				if (DB_LOG > 0) {
					if (DB_LOG == 1) {
						$this->LogIt($this->last_query);
					}
					$this->LogIt($error);
				}

				return false;
			} else {
				return true;
			}
		} // NoError()
        
        
        function GetLastErrNo() {
            return $this->last_errno;
        } // GetLastErrNo()
        

        function GetLastError() {
            return $this->last_error;
        } // GetLastError()
        
        
        function Execute() {
            $args = func_get_args();
            $dbr = call_user_func_array(array($this, "_exec"), $args);
            $this->NoError();
        } // Execute()
        
        
        function Select() {
            $args = func_get_args();
            $dbr = call_user_func_array(array($this, "_exec"), $args);
            
            if ($this->NoError()) {
                $result = array();
                
                while ($row = mysqli_fetch_assoc($dbr)) {
                    $result[] = $row;
                }
                
                mysqli_free_result($dbr);
            
                return $result;
            } else {
                return false;
            }
        } // Select()

		function SelectArrayKey()
		{
			$args = func_get_args();
			$dbr = call_user_func_array(array($this, "_exec"), $args);
			$key = empty($args[1]) ? 'id' : $args[1];

			if ($this->NoError()) {
				$result = array();

				while ($row = mysqli_fetch_assoc($dbr)) {
					$result[$row[$key]] = $row;
				}

				mysqli_free_result($dbr);

				return $result;
			} else {
				return false;
			}
		}
        
		function SelectGroupKey()
		{
			$args = func_get_args();
			$dbr = call_user_func_array(array($this, "_exec"), $args);
			$key = empty($args[1]) ? 'id' : $args[1];

			if ($this->NoError()) {
				$result = array();

				while ($row = mysqli_fetch_assoc($dbr)) {
					$result[$row[$key]][] = $row;
				}

				mysqli_free_result($dbr);

				return $result;
			} else {
				return false;
			}
		}


        function SelectArray() {
            $args = func_get_args();
            $dbr = call_user_func_array(array($this, "_exec"), $args);
            
            if ($this->NoError()) {
                $result = array();
                
                while ($row = mysqli_fetch_array($dbr)) {
                    $result[] = $row[0];
                }
                
                mysqli_free_result($dbr);
            
                return $result;
            } else {
                return false;
            }
        } // Select()
        
        
        function SelectRow() {
            $args = func_get_args();
            $dbr = call_user_func_array(array($this, "_exec"), $args);
            if ($this->NoError()) {
                if ($row = mysqli_fetch_assoc($dbr)) {
                    mysqli_free_result($dbr);
                    return $row;
                } else {
                    mysqli_free_result($dbr);
                    return array();
                }
            } else {
                return false;
            }
        } // SelectRow()
        
        
        function SelectValue() {
            $args = func_get_args();
            $dbr = call_user_func_array(array($this, "_exec"), $args);
            if ($this->NoError()) {
                if ($row = mysqli_fetch_array($dbr)) {
                    mysqli_free_result($dbr);
                    return $row[0];
                } else {
                    mysqli_free_result($dbr);
                    return '';
                }
            } else {
                return false;
            }
        } // SelectValue()
        

        function LastInsertId() {
            return mysqli_insert_id($this->dbh);
        } // LastInsertId()


        function RowsAffected() {
            return $this->affected_rows;
        } // RowsAffected()
        
        
        function __exec() {
            $args = func_get_args();
            $tmp = $args[0];
            $tmp = str_replace(array("%", "?"), array("%%", "%s"), $tmp);
            
            foreach ($args as $idx => $arg) {
                // mysql_escape_string deprecated
                // $args[$idx] = "'" . mysql_escape_string(str_replace('?', chr(7), $arg)) . "'";
                $args[$idx] = "'" . mysqli_real_escape_string($this->dbh, str_replace('?', chr(7), $arg)) . "'";
            }
            
//            print($tmp);
            
//            $args[0] = $tmp;
            
            $query = call_user_func_array("sprintf", $args);
            $query = str_replace(chr(7), '?', $query);
            
            $this->last_query = $query;
            if (DB_LOG == 2) { $this->LogIt($query); }
            
            $result = mysqli_query($this->dbh, $query);
            $this->affected_rows = mysqli_affected_rows($this->dbh);
            
            return $result;
        } // _exec()
        

        function _exec() {
            $args = func_get_args();
            $tmp = $args[0];
//            unset($args[0]);

			$tmp = str_replace("%", "%%", $tmp);
			if (count($args) > 1)
            	$tmp = str_replace("?", "%s", $tmp);
            
            if (!empty($args)) {
                foreach ($args as $idx => $arg) {
                    // mysql_escape_string deprecated
                    // $args[$idx] = "'" . str_replace('?', chr(7), mysql_escape_string($arg)) . "'";
                    $args[$idx] = "'" . str_replace('?', chr(7), mysqli_real_escape_string($this->dbh, $arg.'')) . "'";
                }
            }
            
  //          $marks = array_fill(1, sizeof($args), '?');
//            LogIt(print_r($marks, true)); LogIt(print_r($args, true));
//            $p = 0;
//            
//            foreach ($args as $arg) {
//                $new_pos = strpos($tmp, '?', $p);
//                str_
//            }
            
//            $tmp = str_replace($marks, $args, $tmp);
//            $tmp = str_replace(chr(7), '?', $tmp);
//            }
            $args[0] = $tmp;
            $query = call_user_func_array("sprintf", $args);
            $query = str_replace(chr(7), '?', $query);
//            $query = $tmp;
            
            $this->last_query = $query;
            $time = microtime();
            $result = mysqli_query($this->dbh, $query);
            
            if (DB_LOG == 2) { $this->LogIt( ((microtime()-$time)).'мкс '.$query); }
            $this->affected_rows = mysqli_affected_rows($this->dbh);
            
            return $result;
        } // _exec()
        

        function SimpleExecute($query) {
            $query = str_replace(chr(7), '?', $query);
            $this->last_query = $query;
            if (DB_LOG == 2) { $this->LogIt($query); }
            $result = mysqli_query($this->dbh, $query);
            $this->affected_rows = mysqli_affected_rows($this->dbh);
            
            return $result;
        } // SimpleExecute()
        
        
        function QuoteValue($value) {
            return '"' . mysqli_real_escape_string($this->dbh, str_replace('?', chr(7), $value)) . '"';
        }
		
		function getDbh(){
			return $this->dbh;
		}
        
    } // DB
?>