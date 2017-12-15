<?php
class Utils{

    public static function id_check($user_name){
        $sql = "SELECT user_name FROM account WHERE user_name = $1";
        $params = [$user_name];
        $result = DB::row(DB::query_params($sql, $params));
        if($result){self::response(false, "id_check", "ID existed");}
        else{self::response(true, "id_check");}
    }

    public static function response($success, $func, $notify = null, $data = null) {
        $response = ['success' => $success, 'data' => $data, 'notify' => $notify, 'func' => $func];
        echo json_encode($response);
        exit;
    }
    public static function add_member_check($user_name){
        $sql = "SELECT real_name, todo, ongoing, done FROM account WHERE user_name = $1";
        $params = [$user_name];
        $row = DB::row(DB::query_params($sql, $params));
        if($row){
            $member_info = ['real_name' => $row['real_name'], 'todo' => $row['todo'], 'ongoing' => $row['ongoing'], 'done' => $row['done']];
            self::response(true, "add_member_check");
        }else{self::response(false, "add_member_check");}
    }

    public static function register($user_name, $passwd, $real_name){
        $password_hashed = password_hash($passwd, PASSWORD_DEFAULT);
        $sql = "INSERT INTO account (user_name, password, real_name) VALUES ($1, $2, $3)";
        $params = [$user_name, $password_hashed, $real_name];
        $result = DB::query_params($sql, $params);
        if($result){self::response(true, "user_registration");}
        else{self::response(false, "user_registration", "Registration failed, try again");}
    }

    public static function login($user_name, $passwd){
        $sql = "SELECT user_name,password FROM account WHERE user_name = $1";
        $params = [$user_name];
        $row = DB::row(DB::query_params($sql, $params));
        if($row){
            if(password_verify($passwd, $row['password'])){
                $_SESSION['login'] = true;
                $_SESSION['user_name'] = $user_name;
                self::response(true, "user_login");
            }else{self::response(false, "user_login", "Wrong password");}
        }else{self::response(false, "user_login", "ID not found");}
    }

    public static function logout(){
        session_unset();
        self::response(true, "logout", "LOGOUT SUCCESS");
    }
}
?>
