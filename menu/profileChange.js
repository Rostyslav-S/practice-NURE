let ajax = new XMLHttpRequest();
let ajaxPush;
let user_id = $('#myID').html();

if (localStorage.getItem(`${user_id} newMsg`) == 1) {
	$('.check-newMsg').css('display', 'block');
}

$(function() {

	// Изменение никнейма
	$('#nameChange').on('click', function() {
		$('#msg').html('New name: <input id="TextNewName" type="text" autocomplete="off"></input>' + 
			'<input id="newName" class="update" value="Change" type="button"></input>');
		
		$('#newName').on('click', function() {
			if ($('#TextNewName').val().length < 4) {
				alert('This name is too short!');
			}
			else if ($('#TextNewName').val().length > 30) {
				alert('This name is too long!')
			}
			else if (confirm('Do you really want to change your name?')) {

				ajax.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						$('#msg').html(this.responseText);	
					}
				};

				ajax.open("POST", "profileChange/updatename.php");
				ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				ajax.send(`newName=${$('#TextNewName').val()}`);
			}
		});
	});

	// Изменение пароля
	$('#passwordChange').on('click', function() {
		$('#msg').html('<div class="rowText">Old password: <input id="TextOldPassword" type="password"></input></div>' +
			'<div class="rowText">New password: <input id="TextNewPassword" type="password"></input></div>' + 
			'<div class="rowText">Repeat password: <input id="TextRepeatPassword" type="password"></input></div>' +
			'<input id="newPassword" class="update" value="Change" type="button"></input>');

		$('#newPassword').on('click', function() {
			if ($('#TextOldPassword').val().length == 0 ||
				$('#TextNewPassword').val().length == 0 ||
				$('#TextRepeatPassword').val().length == 0) {
				alert('The field is empty!');
			} else {

				let password = $('#TextOldPassword').val();
				let newPassword = $('#TextNewPassword').val();
				let repeatPassword = $('#TextRepeatPassword').val();

				ajax.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						$('#msg').html(this.responseText);	
					}
				};
				ajax.open("POST", "profileChange/updatepassword.php");
				ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				ajax.send(`password=${password}&newPassword=${newPassword}&repeatPassword=${repeatPassword}`);
			}
		});
	});

	// Удаление аккаунта
	$('#delAcc').on('click', function() {
		$('#msg').html('<p style="color: #EB4C42;">When you delete your account, ' +
		'you will permanently lose access to it!</p>' + 
		'<div class="rowText">Enter password: <input id="EnterPassword" type="password"></input></div>' +
		'<input style="background:linear-gradient(#EA685F, #DA372C)" id="deleteAcc" class="update" value="DELETE ACCOUNT" type="button"></input>');
		
		$('#deleteAcc').on('click', function() {
			let password = $('#EnterPassword').val();

			ajax.onreadystatechange = function() {
				if (ajax.readyState == 4 && ajax.status == 200) {

					if (!this.responseText) location.href = '../index.php';
					else $('#msg').html(this.responseText);
				}
			};
			ajax.open("POST", "profileChange/deleteaccount.php");
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajax.send(`password=${password}`);
		});
	});

	// Чат с администратором

	// Обработчки нажатия на чекбокс
	$('#admin_chat').change(function() {
		if ($(this).is(':checked')) {
			$('.field_for_message').css('display', 'block');
			
			let user_id = $('#myID').html();

			// Загружаем чат с администратором
			$.post('../admin/loaduserschat.php',{
		    	user_id: user_id
		    }, function(data) {
		       	$('.chat-container').append(data);
		    });
		} else {
			$('.field_for_message').css('display', 'none');
			//clearInterval(ajaxPush);
		}
	});

	// Отправка сообщения
	$('#btnSendMsg').on('click', function() {

		if ($('#text-to-send').val() != '') {
			let sendText = $('#text-to-send').val();

			ajax.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						$('.chat-container').append(this.responseText);
					}
				};
			ajax.open("POST", "../admin/userschat.php");
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajax.send(`sendText=${sendText}&user_id=${user_id}`);

			$('#text-to-send').val('');
		}
	});

	ajaxPush = setInterval(function() {
    	$.post('../admin/getuserschat.php',{
    		user_id: user_id
    	}, function(data) {
        	$('.chat-container').append(data);

        	if (data && !$('#admin_chat').is(':checked')) {
        		localStorage.setItem(`${user_id} newMsg`, 1);
			}
			else if ($('#admin_chat').is(':checked')) {
				localStorage.setItem(`${user_id} newMsg`, 0);
			}

			if (localStorage.getItem(`${user_id} newMsg`) == 1) {
				$('.check-newMsg').css('display', 'block');
			} else {
				$('.check-newMsg').css('display', 'none');
			}
			//console.log(getItem(`${user_id} newMsg`);
    	});
	}, 1000);
});