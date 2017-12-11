<?php
class Logger{
    public static function log($who, $what, $where){
        $sql= "INSERT INTO activity (act_id, user_id, whatdo, whendo, wheredo) VALUES (DEFAULT, $1, $2, DEFAULT, $3)";
        $params= array($who, $what, $where);
        DB::query_params($sql, $params);
    }
}
?>
