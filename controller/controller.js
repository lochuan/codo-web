$(document).ready(() => {
    $.getScript('controller/utils.js', function(){
        let currentPage = window.location.href.split("/").slice(-1)[0].split(".")[0];
        switch(currentPage){
            case '':
                $.getScript('controller/index_utils.js', () => {
                    let indexFormList = [
                        'user-login-form',
                        'user-registration-form',
                    ];
                    let indexFormHjk = new formHijacker(indexFormList, ()=>{console.log("I")});
                    indexFormHjk.hijack();
                    regPasswdCheck();
                    regIdCheck();
                });
                break;
            case 'home':
                $.getScript('controller/home_utils.js', () => {
                    let roomFormList = [
                        'create-room-form' ,
                        'join-room-form'   ,
                        'delete-room-form' ,
                        'add-todo-form'    ,
                        'edit-todo-form'   ,
                        'add-member-form'  ,
                    ];
                    let roomFormHjk = new formHijacker(roomFormList, ()=>{console.log("H")});
                    roomFormHjk.hijack();
                });
                break;
        }
    });
});
