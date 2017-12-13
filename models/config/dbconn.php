<?php
class DB {

	private static $instance = NULL;

	private function __construct() {}
	private function __clone() {}

	public static function query_params($sql, $params) {
        if (!isset(self::$instance)) {
            $connection_string = getenv("CODO_DB_CONN");
			self::$instance = pg_pconnect($connection_string, "PGSQL_CONNECT_ASYNC");
		}
		$resource = pg_query_params(self::$instance, $sql, $params);
		return $resource;
	}

	public static function query($sql) {
		if (!isset(self::$instance)) {
            $connection_string = getenv("CODO_DB_CONN");
			self::$instance = pg_pconnect($connection_string, "PGSQL_CONNECT_ASYNC");
		}
		$resource = pg_query(self::$instance, $sql);
		return $resource;
	}

	public static function row($resource) {
		$row = pg_fetch_assoc($resource);
		return $row;
	}

	public static function array($resource) {
		$array = pg_fetch_all($resource);
		return $array;
	}
}
?>
