<?php
class Room{

    private $room_id;
    private $func;

    public function __construct($room_id, $func){
        $this -> room_id = $room_id;
        $this -> func = $func;
    }

    public function response_room_info($user){
        $tods = $this -> get_tods($user);
        $members = $this -> get_members();
        $activities = $this -> get_activities();
        Utils::response(true, $this -> func, "notify from room.php", ['tods' => $tods, 'members' => $members, 'activities' => $activities]
        );
    }

    public function check_todo_status($todo_id){
        $sql = "SELECT status FROM todos WHERE todo_id = $1";
        $params = [$todo_id];
        return DB::row(DB::query_params($sql, $params))['status'];
    }

    public function add_todo($user, $todo){
        $sql = "INSERT INTO todos (todo_id, status, todo, create_time, user_id, room_id) VALUES (DEFAULT, $1, $2, DEFAULT, $3, $4) RETURNING todo_id, create_time";
        $params = [0, $todo, $user -> get_user_id(), $this -> room_id];
        $result = DB::query_params($sql, $params);

        if(!empty($result)){
            $user -> todo += 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Add Todo", $this -> room_id);
            $this -> response_room_info($user);
        }else{Utils::response(false, $this -> func, "Add todo failed");}
    }

    public function delete_todo($user, $todo_id){
        $sql= "DELETE FROM todos WHERE todo_id = $1";
        $params= [$todo_id];
        $result = DB::query_params($sql, $params);

        if(!empty($result)){
            $user -> todo -= 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Delete Todo", $this -> room_id);
            $this -> response_room_info($user);
        }else{Utils::response(false, $this -> func, "Delete todo failed");}
    }

    public function pick_todo($user, $todo_id){
        if($this -> check_todo_status($todo_id) == 1){Utils::response(false, $this -> func, "The todo was picked by others, page will refresh now");}

        $sql = "UPDATE todos SET status = $1, user_id = $2, create_time = DEFAULT WHERE todo_id = $3";
        $params = [1, $user -> get_user_id(), $todo_id];
        $result = DB::query_params($sql, $params);

        if(!empty($result)){
            $user -> ongoing += 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Pick Todo", $this -> room_id);
            $this -> response_room_info($user);
        }else{Utils::response(false, $this -> func, "Pick todo failed");}
    }

    public function done_todo($user, $todo_id){
        if($this -> check_todo_status($todo_id) == 2){Utils::response(false, $this -> func, "The item was picked by others");}
        $sql_done_todo = "UPDATE todos SET status = $1, user_id = $2, create_time = DEFAULT WHERE todo_id = $3";
        $params_done_todo = [2, $user -> get_user_id(), $todo_id];
        $result = DB::query_params($sql_done_todo, $params_done_todo);

        if(!empty($result)){
            $user -> ongoing -= 1;
            $user -> done += 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Done Todo", $this -> room_id);
            $this -> response_room_info($user);
        }else{Utils::response(false, $this -> func, "Done todo failed");}
    }

    public function add_member($user, $data){
        $user_gonna_add = new User($data['form']['add-member-input']);
        $user_gonna_add -> init();
        $sql = "INSERT INTO account_room (user_id, room_id) VALUES ($1, $2)";
        $params = [$user_gonna_add -> get_user_id(), $this -> room_id];
        $result = DB::query_params($sql, $params);

        if(!empty($result)){
            Logger::log($this -> user_id, "Add".$input." to the room", $room_id);
            $this -> response_room_info();
        }else{Utils::response(false, $this -> func, "The user have already in the room");}
    }

    private function get_tods($user){
        $todos = $ongoings = $dones = [];
        $sql = "SELECT account.real_name, todos.todo_id, todos.todo, todos.create_time::timestamptz AT TIME ZONE 'ASIA/SEOUL' FROM account INNER JOIN (SELECT todo_id, todo, create_time, user_id FROM todos WHERE room_id = $1 AND status = $2) AS todos ON (todos.user_id = account.user_id)";
        /*
         *Todo Status    -> 0
         *Ongoing Status -> 1
         *Done Status    -> 2
         */
        foreach(range(0,2) as $status){
            switch($status){
            case 0:
                $params = [$this -> room_id, $status];
                $result = DB::query_params($sql, $params);
                while($row = DB::row($result)){
                    $visibility = "invisible";
                    if($user -> get_real_name() == $row['real_name']) {$visibility = "visible";}
                    $item = ["todo_id" => $row['todo_id'], "todo" => $row['todo'], "create_time" => $row['timezone'], "real_name" => $row['real_name'], 'visibility' => $visibility];
                    $todos[] = $item;
                }
                break;
            case 1:
                $params = [$this -> room_id, $status];
                $result = DB::query_params($sql, $params);
                while($row = DB::row($result)){
                    $visibility = "invisible";
                    if($user -> get_real_name() == $row['real_name']){$visibility = "visible";}
                    $item = ["todo_id" => $row['todo_id'], "todo" => $row['todo'], "create_time" => $row['timezone'], "real_name" => $row['real_name'], 'visibility' => $visibility];
                    $ongoings[] = $item;
                }
                break;
            case 2:
                $params = [$this -> room_id, $status];
                $result = DB::query_params($sql, $params);
                while($row = DB::row($result)){
                    $item = ["todo_id" => $row['todo_id'], "todo" => $row['todo'], "create_time" => $row['timezone'], "real_name" => $row['real_name']];
                    $dones[] = $item;
                }
                break;
            }
        }
        $data = ['todo' => $todos, 'ongoing' => $ongoings, 'done' => $dones];
        return $data;
    }

    private function get_members(){
        $members_id = $members_info = [];
        $sql = "SELECT user_id FROM account_room WHERE room_id = $1";
        $params = [$this -> room_id];
        $result = DB::query_params($sql, $params);
        while($row = DB::row($result)){
            $members_id[] = $row['user_id'];
        }
        foreach($members_id as $value){
            $sql_user_info = "SELECT real_name, todo, ongoing, done FROM account WHERE user_id = $1";
            $params_user_info = [$value];
            $row = DB::row(DB::query_params($sql_user_info, $params_user_info));
            $member_info = ['real_name' => $row['real_name'], 'todo' => $row['todo'], 'ongoing' => $row['ongoing'], 'done' => $row['done']];
            $members_info[] = $member_info;
        }
        return $members_info;
    }

    private function get_activities(){
        $activities = [];
        $sql = "SELECT account.real_name, activity.whendo::timestamptz AT TIME ZONE 'ASIA/SEOUL', activity.whatdo FROM account INNER JOIN activity ON(activity.user_id = account.user_id) WHERE activity.wheredo = $1";
        $params = [$this -> room_id];
        $result = DB::query_params($sql, $params);
        while($row = DB::row($result)){
            $activity_info = ['who' => $row['real_name'], 'when' => $row['timezone'], 'what' => $row['whatdo']];
            $activities[] = $activity_info;
        }
        return $activities;
    }
}
?>
