
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
			$("#registration-warning").html('The length of the ID can not be less than 6');
			$("#registration-input-id").attr('class', 'form-control is-invalid');
		}else{
			let url = "models/distributer.php"
			let user_name = $("#registration-input-id").val();
			let data = {
				'func': 'id_check',
				'user-id': user_name,
			}
			postJson(url, data, function(response){
				if(response.success){
					$("#registration-input-id").attr('class', 'form-control is-valid');
				}else{
					$("#registration-warning").html('This is a registered ID');
					$("#user_name").attr('class', 'form-control is-invalid');					
				}
			});
		}
	});
}
