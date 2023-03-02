<?php

/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
class MySQLIDB
{
    private array $functions = [
        'connect' => 'mysqli_connect',
        'connect_errno' => 'mysqli_connect_errno',
        'query' => 'mysqli_query',
        'fetch_row' => 'mysqli_fetch_row',
        'fetch_array' => 'mysqli_fetch_array',
        'free_result' => 'mysqli_free_result',
        'data_seek' => 'mysqli_data_seek',
        'error' => 'mysqli_error',
        'errno' => 'mysqli_errno',
        'affected_rows' => 'mysqli_affected_rows',
        'num_rows' => 'mysqli_num_rows',
        'num_fields' => 'mysqli_num_fields',
        'field_name' => 'mysqli_fetch_field_direct',
        'insert_id' => 'mysqli_insert_id',
        'real_escape_string' => 'mysqli_real_escape_string',
        'close' => 'mysqli_close',
        'client_encoding' => 'mysqli_character_set_name',
    ];

    private array $fetchtypes = [
        'DBARRAY_NUM' => MYSQLI_NUM,
        'DBARRAY_ASSOC' => MYSQLI_ASSOC,
        'DBARRAY_BOTH' => MYSQLI_BOTH
    ];

    private ?mysqli $connection_master = null;
    private ?mysqli_result $last_query_result = null;
    private ?mysqli_stmt $last_stmt_result = null;
    private string $tbl_prefix = '';
    private string $database = '';
    private string $sql = '';
    private int $querycount = 0;
    private array $parameters = [];
    private array $prepared_types = [];

    public function connect(string $db_name, string $db_server, string $db_user, string $db_passwd, string $db_prefix)
    {
        $this->tbl_prefix = $db_prefix;
        $this->database = $db_name;
        $this->connection_master = $this->db_connect($db_name, $db_server, $db_user, $db_passwd);
    }

    public function testconnect(string $db_name, string $db_server, string $db_user, string $db_passwd): string
    {
        $this->connection_master = @$this->functions['connect']($db_server, $db_user, $db_passwd, $db_name);
        if (!$this->connection_master) {
            return ("<strong>Error MySQLi DB Connection</strong>. Please contact the site administrator.");
        } elseif ($db_name === '') {
            return ("<strong>Error MySQLi DB Connection</strong>. Please contact the site administrator.");
        }
        return '';
    }

    private function db_connect(string $db_name, string $db_server, string $db_user, string $db_passwd): mysqli
    {
        $link = @$this->functions['connect']($db_server, $db_user, $db_passwd, $db_name);
        if ($this->functions['connect_errno']()) {
            die("<br /><br /><strong>Error MySQLi DB Connection</strong><br>Please contact the site administrator.");
        }
        return $link;
    }

    public function close(): bool
    {
        return @$this->functions['close']($this->connection_master);
    }

	public function query(string $sql, bool $buffered = true)
	{
		$this->sql = $sql;
		return $this->execute_query($buffered, $this->connection_master);
	}
	
	private function execute_query(bool $buffered = true, &$link)
	{
		$this->connection_recent = &$link;
		$this->querycount++;
	
		if ($queryresult = mysqli_query($link, $this->sql, $buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT))
		{
			$this->sql = '';
			return $queryresult;
		}
		else
		{
			$this->sql = '';
			return false;
		}
	}
	
	public function fetchRow(string $sql, int $type = DBARRAY_ASSOC)
	{
		$this->sql = $sql;
		$queryresult = $this->execute_query(true, $this->connection_master);
		$returnarray = mysqli_fetch_array($queryresult, $type);
		$this->free_result($queryresult);
		return $returnarray;
	}
	
	public function fetchOne(string $sql)
	{
		$var = $this->fetchRow($sql, MYSQLI_NUM);
		return $var[0];
	}
	
	public function insert(string $tbl, array $dataArray)
	{
		$keys = '';
		$values = '';
		foreach ($dataArray as $k => $v) {
			$keys .= "`" . $this->real_escape_string($k) . "`, ";
			$values .= "'" . $this->real_escape_string($v) . "', ";
		}
	
		$keys = rtrim($keys, ', ');
		$values = rtrim($values, ', ');
		$sql = "INSERT INTO `{$this->tbl_prefix}{$tbl}` ($keys) VALUES ($values)";
		$exeq = $this->query($sql);
		return $exeq;
	}
	
	public function lastInsertId()
	{
		return mysqli_insert_id($this->connection_master);
	}
	
	public function delete(string $tbl, string $data = null)
	{
		$conditional = '';
		if ($data !== null) {
			$conditional = "WHERE {$data}";
		}
		$sql = "DELETE FROM `{$this->tbl_prefix}{$tbl}` {$conditional}";
		$this->query($sql);
	}
	
	public function update(string $tbl, array $dataArray, string $conditional = null)
	{
		$updsql = '';
		foreach ($dataArray as $k => $v) {
			$updsql .= "`" . $this->real_escape_string($k) . "`='" . $this->real_escape_string($v) . "', ";
		}
		$updsql = rtrim($updsql, ', ');
		if ($conditional !== null) {
			$conditional = "WHERE {$conditional}";
		}
		$sql = "UPDATE `{$this->tbl_prefix}{$tbl}` SET {$updsql} {$conditional}";
		$this->query($sql);
	}
	
	public function free_result($queryresult)
	{
		$this->sql = '';
		return mysqli_free_result($queryresult);
	}
	
	public function real_escape_string(string $string): string
    {
        $this->sql = '';
        return $this->connection_master->real_escape_string($string);
    }

}

