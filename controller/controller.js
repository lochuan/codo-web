var roomFormList = [
'create-room-form' ,
'join-room-form'   ,
'delete-room-form' ,
'add-todo-form'    ,
'edit-todo-form'   ,
'add-member-form'  ,
];

var indexFormList = [
'user-login-form',
'user-registration-form',
];

$(document).ready(() => {
    $.getScript('controller/utils.js', function(){
        let currentPage = window.location.href.split("/").slice(-1)[0].split(".")[0];
        switch(currentPage){
            default:
            $.getScript('controller/index_utils.js', () => {
                let indexFormHjk = new formHijacker(indexFormList, (response)=>{
                    if(response.success){
                        switch(response.func){
                            case 'user_registration':
                            closeAndNotify(response.func);
                            $("#user-login-modal").modal("toggle");
                            break;
                            case 'user_login' :
                            closeAndNotify(response.func);
                            setTimeout(()=>{
                                window.location.replace('home.php');
                            }, 1500);
                        }
                    }else{
                        $.notify(response.notify, {
                            autoHideDelay: "3000",
                            className: "error",
                            position: "top center"
                        });
                    }
                });
                indexFormHjk.hijack();
                regPasswdCheck();
                regIdCheck();
            });
            break;
            case 'home':
            $.getScript('controller/home_utils.js', () => {
                let roomFormHjk = new formHijacker(roomFormList, (response)=>{
                    if(response.success){
                        let renderingQueue = poorManTmpl(response);
                        for(let key in renderingQueue){
                            $("#"+key).empty();
                            $("#"+key).append(renderingQueue[key]);
                        }
                        closeAndNotify(response.func);
                    }else{
                        $.notify(response.notify, {
                            autoHideDelay: "3000",
                            className: "error",
                            position: "top center"
                        });
                    }
                });
                roomFormHjk.hijack();
                getRoomList();
                regAddMemberCheck();
            });
            break;
        }
    });
});
