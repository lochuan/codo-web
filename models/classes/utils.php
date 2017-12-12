<?php
class Utils{
	public static $response = ['success' => false, 'data' => [], 'notify' => [], 'func' => []];

    public static function id_check($user_name){
        $sql = "SELECT user_name FROM account WHERE user_name = $1";
        $params = [$user_name];
        $result = DB::row(DB::query_params($sql, $params));
        if($result){
            Utils::$response['success'] = false;
            Utils::$response['notify'] = "User existed";
            Utils::$response['func'] = "id_check";
            echo json_encode(Utils::$response);
            exit;
        }else{
            Utils::$response['success'] = true;
            Utils::$response['func'] = "id_check";
            echo json_encode(Utils::$response);
            exit;
        }

    }

    public static function add_member_check($user_name){
        $sql = "SELECT real_name, todo, ongoing, done FROM account WHERE user_name = $1";
        $params = [$user_name];
        $row = DB::row(DB::query_params($sql, $params));
        if($row){
            $member_info = ['real_name' => [], 'todo' => [], 'ongoing' => [], 'done' => []];
            $member_info['real_name'] = $row['real_name'];
            $member_info['todo']      = $row['todo'];
            $member_info['ongoing']   = $row['ongoing'];
            $member_info['done']     = $row['done'];
            Utils::$response['success'] = true;
            Utils::$response['func'] = "add_member_check";
            Utils::$response['data'] = $member_info;
            echo json_encode(Utils::$response);
            exit;
        }else{
            Utils::$response['success'] = false;
            Utils::$response['func'] = "add_member_check";
            echo json_encode(Utils::$response);
            exit;
        }

    }

    public static function register($user_name, $passwd, $real_name){
        $password_hashed = password_hash($passwd, PASSWORD_DEFAULT);
        $sql = "INSERT INTO account (user_name, password, real_name) VALUES ($1, $2, $3)";
        $params = [$user_name, $password_hashed, $real_name];
        $result = DB::query_params($sql, $params);
        if($result){
            Utils::$response['success'] = true;
            Utils::$response['func'] = "user_registration";
            echo json_encode(Utils::$response);
            exit;
        }else{
            Utils::$response['success'] = false;
            Utils::$response['func'] = "user_registration";
            Utils::$response['notify'] = "Registration failed, please try again";
            echo json_encode(Utils::$response);
            exit;
        }
    }

    public static function login($user_name, $passwd){
        $sql = "SELECT user_name,password FROM account WHERE user_name = $1";
        $params = [$user_name];
        $row = DB::row(DB::query_params($sql, $params));
        if($row){
            if(password_verify($passwd, $row['password'])){
                $_SESSION['login'] = true;
                $_SESSION['user_name'] = $user_name;
                Utils::$response['success'] = true;
                Utils::$response['func'] = "user_login";
                echo json_encode(Utils::$response);
                exit;
            }else{
                Utils::$response['success'] = false;
                Utils::$response['notify'] = "Wrong password";
                Utils::$response['func'] = "login";
                echo json_encode(Utils::$response);
                exit;
            }
        }else{
            Utils::$response['success'] = false;
            Utils::$response['notify'] = "Id not found";
            Utils::$response['func'] = "user_login";
            echo json_encode(Utils::$response);
            exit;
        }

    }

    public static function logout(){
        session_unset();
        Utils::$response['success'] = true;
        Utils::$response['func'] = "logout";
        Utils::$response['notify'] = "LOGOUT SUCCESS";
        echo json_encode(Utils::$response);
    }
}
?>
