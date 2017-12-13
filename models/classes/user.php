<?php
class User{

    private $user_id;
    private $user_name;
    private $real_name;
    public  $todo;
    public  $ongoing;
    public  $done;
    private $func;

    public function __construct($user_name, $func){
        $this -> user_name = $user_name;
        $this -> func = $func;
    }

    public function init(){
        $sql = "SELECT user_id, real_name, todo, ongoing, done FROM account WHERE user_name = $1";
        $params = [$this -> user_name];
        $row = DB::row(DB::query_params($sql, $params));
        $this -> user_id = $row['user_id'];
        $this -> real_name = $row['real_name'];
        $this -> todo = $row['todo'];
        $this -> ongoing = $row['ongoing'];
        $this -> done = $row['done'];
    }

    public function get_real_name(){
        return $this -> real_name;
    }

    public function get_user_id(){
        return $this -> user_id;
    }

    public function update_user(){
        $sql = "UPDATE account SET todo = $1, ongoing = $2, done = $3 WHERE user_id = $4";
        $params = [$this -> todo, $this -> ongoing, $this -> done,$this -> user_id];
        DB::query_params($sql, $params);
    }

    public function response_room_list(){
        $room_list = [];
        $sql= "SELECT room.room_id, room.room_name FROM room INNER JOIN (SELECT account_room.room_id FROM account_room WHERE account_room.user_id = $1) AS account_room_id ON (room.room_id = account_room_id.room_id)";
        $params= [$this -> user_id];
        $result= DB::query_params($sql, $params);
        while($row = DB::row($result)){
            $room = ["room_id" => $row['room_id'], "room_name" => $row['room_name']];
            $room_list[] = $room;
        }
        if(count($room_list) !== 0){Utils::response(true, $this -> func, "notify from user.php", $room_list);}
        else{Utils::response(false, $this -> func, "You have no room, Create or Join one!");}
    }

    public function create_room($room_name){
        $sql_create_room = "INSERT INTO room (room_id, room_name) VALUES (DEFAULT, $1) RETURNING room_id";
        $params_create_room = [$room_name];
        $row_create_room = DB::row(DB::query_params($sql_create_room, $params_create_room));

        $sql_insert_user_room = "INSERT INTO account_room (user_id, room_id) VALUES ($1, $2)";
        $params_insert_user_room = [$this -> user_id, $row_create_room['room_id']];
        $result_insert_user_room = DB::query_params($sql_insert_user_room, $params_insert_user_room);

        if($row_create_room && $result_insert_user_room){
            Logger::log($this -> user_id, "Create Room", $row_create_room['room_id']);
            $this -> response_room_list();
        }else{Utils::response(false, $this -> func, "Create room failed");}
    }

    public function join_room($room_id){
        $sql_room_name = "SELECT room_name FROM room WHERE room_id = $1";
        $params_room_name = [$room_id];
        $row_room_name = DB::row(DB::query_params($sql_room_name, $params_room_name));
        if(!empty($row_room_name)){
            $sql_insert_user_room = "INSERT INTO account_room (user_id, room_id) VALUES ($1, $2)";
            $params_insert_user_room = [$this -> user_id, $room_id];
            $result_insert_user_room = DB::query_params($sql_insert_user_room, $params_insert_user_room);
            if(!empty($result_insert_user_room)){
                Logger::log($this -> user_id, "Join Room", $room_id);
                $this -> response_room_list();
            }else{Utils::response(false, $this -> func, "You have already in the room");}

        }else{Utils::response(false, $this -> func, "Room ID not found");}
    }

    public function delete_room($room_id, $room_name){
        $sql_room_name_id = "SELECT room_id, room_name FROM room WHERE room_id = $1";
        $params_room_name_id = [$room_id];
        $row_room_name_id = DB::row(DB::query_params($sql_room_name_id, $params_room_name_id));
        if(!empty($row_room_name_id)){
            if($row_room_name_id['room_name'] == $room_name){
                $sql_delete = "DELETE FROM room WHERE room_id = $1";
                $params_delete = [$room_id];
                $delete_result = DB::query_params($sql_delete, $params_delete);
                if(!empty($delete_result)){
                    Logger::log($this -> user_id, "Delete Room", $room_id);
                    $this -> response_room_list();
                }else{Utils::response(false, $this -> func, "Delete room failed");}
            }else{Utils::response(false, $this -> func, "Room ID and Room name don't match");}
        }else{Utils::response(false, $this -> func, "Room ID not found");}
    }
}
?>
