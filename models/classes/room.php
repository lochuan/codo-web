<?php
class Room{
    private $room_id;
    private $todo = [];
    private $ongoing = [];
    private $done = [];
    private $members = [];
    private $func;
    private $response = ['success' => false, 'notify' => [], 'data' => [], 'func' => []];

    public function __construct($room_id, $func){
        $this -> room_id = $room_id;
        $this -> func = $func;
    }

    public function response_room_info(){
        $tods = $this -> get_tods();
        $members = $this -> get_members();
        $this -> response['success'] = true;
        $this -> response['func'] = $this -> func;
        $this -> response['data'] = ['tods' => $tods, 'members' => $members];
        echo json_encode($this -> response);
        exit;
    }

    public function check_todo_status(){
        $sql = "SELECT status FROM todos WHERE todo_id = $1";
        $params = [$todo_id];
        return DB::row(DB::query_params($sql, $params))['todo_id'];
    }
    public function add_todo($user, $todo){
        $sql = "INSERT INTO todos (todo_id, status, todo, create_time, user_id, room_id) VALUES (DEFAULT, $1, $2, DEFAULT, $3, $4) RETURNING todo_id, create_time";
        $params = [0, $todo, $user -> get_user_id(), $this -> room_id];
        $result = DB::query_params($sql, $params);

        if(!empty($result)){
            $user -> todo += 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Add Todo", $this -> room_id);
            $this -> response_room_info();
        }else{
            $this -> response['success'] = false;
            $this -> response['func'] = $this -> func;
            $this -> response['notify'] = "Add todo failed";
            echo json_encode($this -> response);
            exit;
        }
    }

    public function delete_todo($user, $todo_id){
        $sql= "DELETE FROM todos WHERE todo_id = $1";
        $params= [$todo_id];
        $result = DB::query_params($sql, $params);

        if(!empty($result)){
            $user -> todo -= 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Delete Todo", $this -> room_id);
            $this -> response_room_info();
        }else{
            $this -> response['success'] = false;
            $this -> response['notify'] = "Delete todo failed";
            $this -> response['func'] = $this -> func;
            echo json_encode($this -> response);
            exit;
        }
    }

    public function pick_todo($user, $todo_id){
        if(check_todo_status($todo_id) == 1){
            $this -> response['success'] = false;
            $this -> response['func'] = $this -> func;
            $this -> response['notify'] = "The todo was picked by others";
            echo json_encode($this -> response);
            exit;
        }
        $sql = "UPDATE todos SET status = $1, user_id = $2, create_time = DEFAULT WHERE todo_id = $3";
        $params = [1, $user -> get_user_id(), $todo_id];
        $result = DB::query_params($sql_pick_todo, $params_pick_todo);

        if(!empty($result)){
            $user -> ongoing += 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Pick Todo", $this -> room_id);
            $this -> response_room_info();
        }else{
            $this -> response['success'] = false;
            $this -> response['func'] = $this -> func;
            $this -> response['notify'] = "Pick todo failed";
            echo json_encode($this -> response);
            exit;
        }
    }

    public function done_todo($user, $todo_id){
        $sql_done_todo = "UPDATE todos SET status = $1, user_id = $2, create_time = DEFAULT WHERE todo_id = $3";
        $params_done_todo = [2, $user -> get_user_id(), $todo_id];
        $result = DB::query_params($sql_done_todo, $params_done_todo);

        if(!empty($result)){
            $user -> done += 1;
            $user -> update_user();
            Logger::log($user -> get_user_id(), "Done Todo", $this -> room_id);
            $this -> response_room_info();
        }else{
            $this -> response['success'] = false;
            $this -> response['func'] = $this -> func;
            $this -> response['notify'] = "Done todo failed";
            echo json_encode($this -> response);
            exit;
        }
    }

    private function get_tods(){
        $todos    = [];
        $ongoings = [];
        $dones    = [];
        $data    = ['todo' => [], 'ongoing' => [], 'done' => []];

        $sql = "SELECT account.real_name, todos.todo_id, todos.todo, todos.create_time FROM account INNER JOIN (SELECT todo_id, todo, create_time, user_id FROM todos WHERE room_id = $1 AND status = $2) AS todos ON (todos.user_id = account.user_id)";
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
                    $item = ["todo_id" => $row['todo_id'], "todo" => $row['todo'], "create_time" => $row['create_time'], "real_name" => $row['real_name']];
                    array_push($todos, $item);
                }
                break;
            case 1:
                $params = [$this -> room_id, $status];
                $result = DB::query_params($sql, $params);
                while($row = DB::row($result)){
                    $item = ["todo_id" => $row['todo_id'], "todo" => $row['todo'], "create_time" => $row['create_time'], "real_name" => $row['real_name']];
                    array_push($ongoings, $item);
                }
                break;
            case 2:
                $params = [$this -> room_id, $status];
                $result = DB::query_params($sql, $params);
                while($row = DB::row($result)){
                    $item = ["todo_id" => $row['todo_id'], "todo" => $row['todo'], "create_time" => $row['create_time'], "real_name" => $row['real_name']];
                    array_push($ongoings, $item);
                }
                break;

            }
        }
        $data['todo'] = $todos;
        $data['ongoing'] = $ongoings;
        $data['done'] = $dones;
        return $data;
    }

    private function get_members(){
        $members = [];
        $sql = "SELECT user_id FROM account_room WHERE room_id = $1";
        $params = [$this -> room_id];
        $result = DB::query_params($sql, $params);
        while($row = DB::row($result)){
            array_push($members, $row['user_id']);
        }
        foreach($members as $value){
            $member_info = ['real_name' => [], 'todo' => [], 'ongoing' => [], 'done' => []];
            $sql_user_info = "SELECT real_name, todo, ongoing, done FROM account WHERE user_id = $1";
            $params_user_info = [$value];
            $row = DB::row(DB::query_params($sql_user_info, $params_user_info));
            $member_info['real_name'] = $row['real_name'];
            $member_info['todo']      = $row['todo'];
            $member_info['ongoing']   = $row['ongoing'];
            $member_infoj['done']     = $row['done'];
            array_push($members, $member_info);
        }
        return $members;
    }
}
?>
