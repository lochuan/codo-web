var tabContent;
var bird;
/*
 *获取房间列表
 */
function getRoomList(){
    let postData = {
        func: 'get_room_list',
    }
    postJson('/models/distributer.php', postData, (response) => {
        if(response.success){
            let renderingQueue = poorManTmpl(response);
            for(let key in renderingQueue){
                $("#"+key).empty();
                $("#"+key).append(renderingQueue[key]);
            }
        }else{
            $("#room-list").notify(response.notify, {
                autoHideDelay: "3000",
                className: "error",
                position: "right top"
            });
        }
    });
}

function regAddMemberCheck(){
    $("#add-member-check-input").keyup(function(){
        if($("#add-member-check-input").val().length >= 6){
            let url = "models/distributer.php"
            let user_name = $("#add-member-check-input").val();
            let postData = {
                'func': 'add_member_check',
                'user-id': user_name,
            }
            postJson(url, postData, function(response){
                if(response.success){
                    let feedBack = "<li class='list-group-item list-group-item-info d-flex flex-row justify-content-between align-items-center'>"
                        + "<p class='w-40' style='text-align:left;'>"+response.data.real_name+"</p>"
                        + "<span class='badge badge-primary w-20'>Todos:"+response.data.todo+"</span>"
                        + "<span class='badge badge-success w-20'>Ongoging:"+response.data.ongoing+"</span>"
                        + "<span class='badge badge-warning w-20'>Done:"+response.data.done+"</span>"
                        + "</li>"
                    $("#add-member-check-container").empty().append(feedBack);

                }else{
                    let feedBack = "<div class='alert alert-danger' role='alert'>No such user</div>"
                    $("#add-member-check-container").empty().append(feedBack);
                }
            });
        }
    });
}

function logout(event){
    let postData = {
        func: 'user_logout',
    }
    postJson('/models/distributer.php', postData, (response)=>{
        if(response.success){
            $.notify(response.notify, {
                autoHideDelay: "1500",
                className: "success",
                position: "top center"
            });
            setTimeout(()=>{
                window.location.replace('/');
            }, 1800);
        }
    });
}

$(document).ready(() => {
    var $loading = $('#ajax_spinner').hide();
    $(document).ajaxStart(()=>{$loading.show();}).ajaxStop(()=>{$loading.hide();});
    tabContent = $("#tab-content-container").detach();
    bird = $("#bird-template").text();
    $("#outer-card-body").append(bird);
    $("#room-list-container").click((e) => {
        if(!$("#bird").text()){
            tabContent = $("#tab-content-container").detach();
            $("#outer-card-body").append(bird);
        }

        $("#room-list .active").attr("class", "list-group-item list-group-item-action d-flex flex-row justify-content-between align-items-center text-dark");

    });
    $("#room-list").click((e) => {
        e.stopPropagation()
        $(e.target).siblings().attr("class", "list-group-item list-group-item-action d-flex flex-row justify-content-between align-items-center text-dark");
        let room_id = $(e.target).children().first().text();
        room_id = room_id.split(":")[1];
        let postData = {
            'func': 'get_room_info',
            'room-id': room_id,
        }
        if($("#bird").text()){
            $("#bird").remove();
            $("#outer-card-body").append(tabContent);
        }
        postJson('/models/distributer.php', postData, (response)=>{
            if(response.success){
                let renderingQueue = poorManTmpl(response);
                for(let key in renderingQueue){
                    $("#"+key).empty();
                    $("#"+key).append(renderingQueue[key]);
                }
            }else{
                closeAndNotify(response.func);
            }
            $(e.target).attr("class", "list-group-item list-group-item-action d-flex flex-row justify-content-between align-items-center text-light active");
        });   
    });
});
