/*
 *获取房间列表
 */
function getRoomList(){
    let postData = {
        func: 'get_room_list',
    }
    postJson(postData, function(response) {
        if (response.success) {
            $.each(response.data, function(i, item) {
                $("#roomlist").append(
                    '<a class="list-group-item list-group-item-action d-flex flex-row justify-content-between align-items-center">' +
                    response.data[i].teamname+'<span class="badge badge-warning badge-pill text-dark">'+'ID:'+response.data[i].team_id+'</span>'+'</a>');
            });


        }
    });
}
