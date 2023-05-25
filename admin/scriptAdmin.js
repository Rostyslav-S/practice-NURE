let ajax = new XMLHttpRequest();
let ajaxPush;
let user_id;

$(function() {

	// User ban/unban
	//==========================================================

	$('#user_id').on('input', function() {
		if ($(this).val() !== '') {
			$('#user_nickname').prop('disabled', true);
		} else {
			$('#user_nickname').prop('disabled', false);
		}
	});

	$('#user_nickname').on('input', function() {
		if ($(this).val() !== '') {
			$('#user_id').prop('disabled', true);
		} else {
			$('#user_id').prop('disabled', false);
		}
	});

	// Обработчки нажатия на чекбокс
	$('#checkUnban').change(function() {
		if ($(this).is(':checked')) {
			$('#days').prop('disabled', true);
			$('#cause').prop('disabled', true);
			$('#btnBan').css('display', 'none');
			$('#btnUnban').css('display', 'block');

		} else {
			$('#days').prop('disabled', false);
			$('#cause').prop('disabled', false);
			$('#btnUnban').css('display', 'none');
			$('#btnBan').css('display', 'block');
		}
	});

	$("#btnBan").on("click", function() {
		if ($("#user_id").val() === '' &&
			$("#user_nickname").val() === '') {
				$('#banMsg').html('Enter ID or nickname!');
				$('#banMsg').css('margin-top', '30px');
		}
		else if ($('#days').val().length == 0 || Number($('#days').val()) < '1') {
			$('#banMsg').html('Number of days input error!');
			$('#banMsg').css('margin-top', '30px');
		}
		else {
			$('#banMsg').html('').css('margin-top', '0');
			if (confirm('Ban a user for '+$('#days').val()+' day/days?')) {
				let user_id = $('#user_id').val();
				let user_nickname = $('#user_nickname').val();
				let cause = $('#cause').val();
				let days = $('#days').val();

				ajax.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						$('#banMsg').html(this.responseText);
						$('#banMsg').css('margin-top', '30px');
					}
				};

				ajax.open("POST", "userban.php");
				ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				ajax.send(`user_id=${user_id}&user_nickname=${user_nickname}&cause=${cause}&days=${days}`);
			}
		} 
	});

	$("#btnUnban").on("click", function() {
		if ($("#user_id").val() === '' &&
			$("#user_nickname").val() === '') {
				$('#banMsg').html('Enter ID or nickname!');
				$('#banMsg').css('margin-top', '30px');
		} else {
			$('#banMsg').html('').css('margin-top', '0');
			if (confirm('Unban a user?')) {
				let user_id = $('#user_id').val();
				let user_nickname = $('#user_nickname').val();

				ajax.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						$('#banMsg').html(this.responseText);
						$('#banMsg').css('margin-top', '30px');
					}
				};

				ajax.open("POST", "userunban.php");
				ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				ajax.send(`user_id=${user_id}&user_nickname=${user_nickname}`);
			}
		} 
	});

	// Чат с игроками
	// ===========================================================

	$('#btnSendMsg').on('click', function() {
		if ($('#text-to-send').val() != '') {
			let sendText = $('#text-to-send').val();

			ajax.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						$('.chat-container').append(this.responseText);
					}
				};
			ajax.open("POST", "userschat.php");
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajax.send(`sendText=${sendText}&user_id=${user_id}`);

			$('#text-to-send').val('');
		}
	});

	// Панель для выбора чата с игроком
	// =========================================================

	$('.user_item').on('click', function() {
		clearInterval(ajaxPush);

		$(this).prependTo('.users_list');
		$('.users_info').scrollTop(0);
		$('.user_item').css('background', '#F0F0F0');
		$(this).css('background', '#89E39B');
		$('.chat-container').html('');
		$('#btnSendMsg').prop('disabled', false);
		
		user_id = Number($(this).attr('id'));

		$.post('../admin/loaduserschat.php', {
			user_id: user_id
		}, function(data) {
			$('.chat-container').append(data);
		});

		ajaxPush = setInterval(function() {
		    $.post('../admin/getuserschat.php', {
		    	user_id: user_id
		    }, function(data) {
		        $('.chat-container').append(data);
		    });
		}, 1000);
	});
});
