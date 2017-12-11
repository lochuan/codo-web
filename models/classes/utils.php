<?php
class Utils{
	public static $response = array('success' => false, 'data' => array(), 'notify' => array());

    public static function id_check($user_name){
        $sql = "SELECT user_name FROM account WHERE user_name = $1";
        $params = array($user_name);
        $result = DB::row(DB::query_params($sql, $params));
        if($result){
            Utils::$response['success'] = false;
            Utils::$response['notify'] = "User existed";
            echo json_encode(Utils::$response);
            exit;
        }else{
            Utils::$response['success'] = true;
            echo json_encode(Utils::$response);
            exit;
        }

    }

    public static function register($user_name, $passwd, $real_name){
        $password_hashed = password_hash($passwd, PASSWORD_DEFAULT);
        $sql = "INSERT INTO account (user_name, password, real_name) VALUES ($1, $2, $3)";
        $params = array($user_name, $password_hashed, $real_name);
        $result = DB::query_params($sql, $params);
        if($result){
            Utils::$response['success'] = true;
            echo json_encode(Utils::$response);
            exit;
        }else{
            Utils::$response['success'] = false;
            echo json_encode(Utils::$response);
            exit;
        }
    }

    public static function login($user_name, $passwd){
        $sql = "SELECT user_name,password FROM account WHERE user_name = $1";
        $params = array($user_name);
        $row = DB::row(DB::query_params($sql, $params));
        if($row){
            if(password_verify($passwd, $row['password'])){
                $_SESSION['login'] = true;
                $_SESSION['user_name'] = $user_name;
                Utils::$response['success'] = true;
                echo json_encode(Utils::$response);
                exit;
            }else{
                Utils::$response['success'] = false;
                Utils::$response['notify'] = "Wrong password";
                echo json_encode(Utils::$response);
                exit;
            }
        }else{
            Utils::$response['success'] = false;
            Utils::$response['notify'] = "Id not found";
            echo json_encode(Utils::$response);
            exit;
        }

    }
}
?>
