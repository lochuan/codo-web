//forms in the home.html
var roomFormList = [
    'create-room-form' ,
    'join-room-form'   ,
    'delete-room-form' ,
    'add-todo-form'    ,
    'edit-todo-form'   ,
    'add-member-form'  ,
];

//forms in the index.html
var indexFormList = [
    'user-login-form',
    'user-registration-form',
];

$(document).ready(() => {
    $.getScript('controllers/utils.js', function(){
        let currentPage = window.location.href.split("/").slice(-1)[0].split(".")[0];
        switch(currentPage){
            default:
                $.getScript('controllers/index_utils.js', () => {
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
                    indexFormHjk.hijack(); //hijack the forms
                    regPasswdCheck(); //register password check event handler
                    regIdCheck(); //register id check event handler
                });
                break;
            case 'home':
                $.getScript('controllers/home_utils.js', () => {
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
