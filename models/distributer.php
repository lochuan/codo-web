<?php
$utils_route = ['user_registration', 'user_login', 'id_check', 'user_logout', 'add_member_check'];
$user_route  = ['create_room', 'join_room', 'delete_room', 'get_room_list'];
$room_route  = ['add_todo', 'delete_todo', 'pick_todo', 'done_todo', 'get_room_info', 'add_member'];

$data = file_get_contents('php://input');
session_start();
if(!empty($data)){
    $data = json_decode($data, true); //Parse to assoc arr
    array_walk_recursive($data, function(&$item){$item = trim($item);}); //Trim
    require_once('config/dbconn.php');
    require_once('classes/logger.php');
    require_once('classes/user.php');
    require_once('classes/room.php');
    require_once('classes/utils.php');

    if(in_array($data['func'], $utils_route)){
        switch($data['func']){
        case 'user_registration':
            Utils::register($data['form']['registration-input-id'], $data['form']['registration-input-passwd2'], $data['form']['registration-input-real-name']);
            break;
        case 'user_login':
            Utils::login($data['form']['login-input-id'], $data['form']['login-input-password']);
            break;
        case 'user_logout':
            Utils::logout();
            break;
        case 'id_check':
            Utils::id_check($data['user-id']);
            break;
        case 'add_member_check':
            Utils::add_member_check($data['user-id']);
            break;
        }
    }

    if(in_array($data['func'], $user_route)){
        if(!empty($_SESSION['user_name']));{
            $user = new User($_SESSION['user_name'], $data['func']);
            $user -> init();
            switch($data['func']){
            case 'get_room_list':
                $user -> response_room_list();
                break;
            case 'create_room':
                $user -> create_room($data['form']['create-room-input']);
                break;
            case 'join_room':
                $user -> join_room($data['form']['join-room-input']);
                break;
            case 'delete_room':
                $user -> delete_room($data['form']['delete-room-id-input'], $data['form']['delete-room-name-input']);
                break;

            } 
        }   
    }

    if(in_array($data['func'], $room_route)){
        if(!empty($_SESSION['user_name']) && !empty($data['room-id'])){
            $user = new User($_SESSION['user_name'], $data['func']);
            $user -> init();
            $room = new Room($data['room-id'], $data['func']);
            switch($data['func']){
            case 'get_room_info':
                $room -> response_room_info($user);
                break;
            case 'add_todo':
                $room -> add_todo($user, $data['form']['add-todo-input']);
                break;
            case 'delete_todo':
                $room -> delete_todo($user, $data['todo-id']);
                break;
            case 'pick_todo':
                $room -> pick_todo($user, $data['todo-id']);
                break;
            case 'done_todo':
                $room -> done_todo($user, $data['todo-id']);
                break;
            case 'add_member':
                $room -> add_member($user, $data);
                break;
            } 
        }

    }
}
?>
