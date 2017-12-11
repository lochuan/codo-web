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

function pick_active_room(){
        let roomId = $("#roomlist a.active span").text();
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
                let inputVal = [];
                let valid    = true;
                $("#"+elem+" div.input").each((key, obj) => {inputVal.push(obj.value)});
                for(let val of inputVal){
                    if(val === ""){
                        valid = false;
                    }
                }
                if(valid){
                    let url      = './models/distributer.php';
                    let funcName = elem.split("-");
                    let postData = {
                        'func': funcName[0]+ "_" + funcName[1], 
                        'form': $("#"+elem),
                        'room-id': pick_active_room(),
                    }
                    //房间和todo的form用不同的callback来刷新页面
                    postJson(url, postData, callback);
                }
            });
        })
    }
}

function eventHandler(event){
    let url     = './models/distributer.php';
    let roomId  = pick_active_room();
    let todoId  = $(event.target).closest("li").prop("id");
    let btnType = $(event.target).attr("type");
    if(roomId && todoId){
        let funcName = btnType.split("-");
        let postData = {
            'func'    : funcName[0]+ "_" + funcName[1], 
            'room-id' : roomId,
            'todo-id' : todoId,
        }
        postJson(url, postData, (response)=>{console.log(response)});
    }
}

function 
