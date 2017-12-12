function getFormData(form){
	var unindexed_array = form.serializeArray();
	var indexed_array = {};

	$.map(unindexed_array, function(n, i){
		indexed_array[n['name']] = n['value'];
	});
	return indexed_array;
}

function postJson(url, data, callback){
	if (url !== undefined && data.func !== undefined) {
		if (data.form !== undefined) {data['form'] = getFormData(data.form)}
          $.ajax({
             url: url,
             method: 'POST',
             data: JSON.stringify(data),
             dataType: 'JSON',
         }).done(callback);
  }
}

function pickActiveRoom(){
    let roomId = $("#room-list a.active span").text();
    roomId = roomId.split(":")[1];
    return roomId;
}
/*
 *Constructor Function用来构建一个formHijacker对象
 */
 function formHijacker(formArr, callback){
    this.formArr = formArr;
    this.hijack = () => {
        this.formArr.map((elem, i, array) => {
            $("#"+elem).submit((event) => {
                event.preventDefault();
                let roomId = pickActiveRoom();
                let inputVal = {};
                let valid    = true;
                $("#"+elem+" div input").each((key, obj) => {
                    inputVal[obj.name] = obj.value;
                    if($(obj).hasClass("is-invalid")){valid = false}
                });
                for(let key in inputVal){
                    if(inputVal[key] === ""){
                        valid = false
                    }
                }
                if(valid){
                    let url      = '/models/distributer.php';
                    let funcName = elem.split("-");
                    let postData = {
                        'func': funcName[0]+ "_" + funcName[1], 
                        'form': $("#"+elem),
                        'room-id': roomId,
                    }
                    postJson(url, postData, callback);
                }else{
                    $("#"+elem).notify("Check your input", {autoHideDelay: "2000",className: "error",position: "top center"});
                    $("#"+elem+" .is-invalid").removeClass("is-invalid");
                }
            });
        })
    }
}

function btnHijacker(event){
    let url     = '/models/distributer.php';
    let roomId  = pickActiveRoom();
    let todoId  = $(event.target).closest("li").prop("id");
    let btnType = $(event.target).attr("type");
    if(roomId && todoId){
        let funcName = btnType.split("-");
        let postData = {
            'func'    : funcName[0]+ "_" + funcName[1], 
            'room-id' : roomId,
            'todo-id' : todoId,
        }
        postJson(url, postData, (response)=>{
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
    }
}

function poorManTmpl(response){
    let roomListFunc = ['create_room', 'join_room', 'delete_room', 'get_room_list'];
    let inRoomFunc = ['add_todo', 'delete_todo', 'pick_todo', 'done_todo', 'get_room_info', 'add_member'];
    let renderingQueue = {};

    if(roomListFunc.includes(response.func)){
        let tmpl = $("#room-template").text();
        let rendering = "";
        for(let item of response.data){
            rendering += tmpl
            .replace("{{room_name}}", item.room_name)
            .replace("{{room_id}}", item.room_id);
        }
        renderingQueue['room-list'] = rendering;
        return renderingQueue;
    }

    if(inRoomFunc.includes(response.func)){
        let todoTmpl = $("#todo-template").text();
        let ongoingTmpl = $("#ongoing-template").text();
        let doneTmpl = $("#done-template").text();
        let memberTmpl = $("#member-template").text();
        let todoRendering = "";
        let ongoingRendering = "";
        let doneRendering = "";
        let memberRendering = "";
        for(let item of response.data.tods.todo){
            todoRendering += todoTmpl
            .replace("{{todo_id}}", item.todo_id)
            .replace("{{real_name}}", item.real_name)
            .replace("{{todo}}", item.todo)
            .replace("{{visibility}}", item.visibility)
            .replace("{{create_time}}", item.create_time);
        }
        renderingQueue['todo-list'] = todoRendering;

        for(let item of response.data.tods.ongoing){
            ongoingRendering += ongoingTmpl
            .replace("{{todo_id}}", item.todo_id)
            .replace("{{real_name}}", item.real_name)
            .replace("{{todo}}", item.todo)
            .replace("{{visibility}}", item.visibility)
            .replace("{{create_time}}", item.create_time);
        }
        renderingQueue['ongoing-list'] = ongoingRendering;

        for(let item of response.data.tods.done){
            doneRendering += doneTmpl
            .replace("{{todo_id}}", item.todo_id)
            .replace("{{real_name}}", item.real_name)
            .replace("{{todo}}", item.todo)
            .replace("{{create_time}}", item.create_time);
        }
        renderingQueue['done-list'] = doneRendering;

        for(let item of response.data.members){
            memberRendering += memberTmpl
            .replace("{{real_name}}", item.real_name)
            .replace("{{todo}}", item.todo)
            .replace("{{ongoing}}", item.ongoing)
            .replace("{{done}}", item.done);
        }
        renderingQueue['member-list'] = memberRendering;

        return renderingQueue;
    }
}

function closeAndNotify(elem){
    let strSplit = elem.split("_");
    let notify = strSplit[0].toUpperCase()+" "+strSplit[1].toUpperCase();
    let close = strSplit[0]+"-"+strSplit[1]+"-"+"modal";
    $("#"+close).modal("toggle");
    setTimeout(()=>{
        $.notify(notify + " SUCCESS", {
            autoHideDelay: "3000",
            className: "success",
            position: "top right"
        });
    }, 600);
}
