function regPasswdCheck(){
    $("#registration-input-passwd2").keyup(function(){
        if($("#registration-input-passwd1").val() === $("#registration-input-passwd2").val()){
            $("#registration-input-passwd2").attr('class', 'form-control is-valid');
        }else{
            $("#registration-input-passwd2").attr('class', 'form-control is-invalid');
        }
    });
}

function regIdCheck(){
    $("#registration-input-id").keyup(function(){
        if($("#registration-input-id").val().length < 6){
            $("#registration-warning").html('The length of the ID is too short');
            $("#registration-input-id").attr('class', 'form-control is-invalid');
        }else{
            let url = "models/distributer.php"
            let user_name = $("#registration-input-id").val();
            let postData = {
                'func': 'id_check',
                'user-id': user_name,
            }
            postJson(url, postData, function(response){
                if(response.success){
                    $("#registration-input-id").attr('class', 'form-control is-valid');
                }else{
                    $("#registration-input-id").notify(response.notify, {autoHideDelay: "1500",className: "error",position: "left middle"});
                    $("#registration-input-id").attr('class', 'form-control is-invalid');				
                }
            });
        }
    });
}
