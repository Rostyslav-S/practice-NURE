let ajax = new XMLHttpRequest();
let ajaxPush;
let user_id;
let username;

function defaultProfile() {
	clearInterval(ajaxPush);
	$('#username').html("UserName: ");
	$('#ID').html(`ID: `);
	$('.functions-btns').html('');
	$('.chat-container').html('');
	$('.field_for_message').html('');
	$('.field_for_message').css('display', 'none');
	$('.friend-profile').css('display', 'none');
}

function showProfile() {
	$('.field_for_message').css('display', 'block');
	$('.friend-profile').css('display', 'flex');
	$('#username').html("UserName: <b>" + username + '</b>');
	$('#ID').html(`ID: <b>${user_id}</b>`);
}

$(function() {

	// Проверка на новые заявки в друзья
	$.post('friendsDB.php', {
		incoming: true,
	}, function(data) {
		if (data !== '') $('.check-newMsg').css('display', 'block');
	});

	// Панель для выбора друга
	$('.friends_list').on('click', '.user_item', function() {

		$(this).prependTo('.friends_list');
		$('.friends_info').scrollTop(0);
		$('.user_item').css('background', '#F0F0F0');
		$(this).css('background', '#89E39B');
		$('.chat-container').html('');

		user_id = $(this).attr('id');
		username = $(this).text();

		// === My friends ===
		if ($('#option1').is(':checked')) {
			showProfile();
			clearInterval(ajaxPush);
			$('.functions-btns').html('<input type="radio" name="chat-or-stat" id="radio-chat" checked="true">' +
				'<label for="radio-chat" class="btn" id="chat">Chat</label>' +
				'<input type="radio" name="chat-or-stat" id="radio-stat">' +
				'<label for="radio-stat" class="btn" id="stat">Statistics</label>' +
				'<input type="button" class="btn" id="delete-friend" value="Delete a friend">');
			$('.field_for_message').html('<div class="chat-container"></div>' +
				'<div class="text-and-btn">' +
				'<textarea id="text-to-send"></textarea>' +
				'<input type="button" class="btn" value="Send" id="btnSendMsg"></div>');

			// Загрузка чата с другом
			$.post('friendlychat.php', {
				getChat: true,
				friend_id: user_id
			}, function(data) {
				$('.chat-container').html(data);
			});

			// Запрос на получения новых сообщений
			ajaxPush = setInterval(function() {
		    	$.post('friendlychat.php',{
		    		getNewMsg: true,
		    		friend_id: user_id
		    	}, function(data) {
		        	if (data != '1') {
						$('.chat-container').append(data);
    				}
		    	});
			}, 1000);

			// === Отображение чата с дургом
			$('#chat').on('click', function() {
				$('#chat').css('background', 'linear-gradient(#49B681, #298533)');
				$('#stat').css('background', 'linear-gradient(#49708f, #293f50)');

				$('.field_for_message').html('<div class="chat-container"></div>' +
					'<div class="text-and-btn">' +
					'<textarea id="text-to-send"></textarea>' +
					'<input type="button" class="btn" value="Send" id="btnSendMsg"></div>');

				// Загрузка чата с другом
				$.post('friendlychat.php', {
					getChat: true,
    				friend_id: user_id
    			}, function(data) {
					$('.chat-container').html(data);
    			});
			});


			// === Отправка сообщения
			$('.field_for_message').on('click', '#btnSendMsg', function() {
				$.post('friendlychat.php', {
					sendMsg: $('#text-to-send').val(),
    				friend_id: user_id
    			}, function(data) {
    				if ($('.chat-info').val() != '') {
						$('.chat-container').append(data);
    				} else {
    					$('.chat-container').html(data);
    				}
    			});
				$('#text-to-send').val('');
			});

			// === Отображение статистики друга
			$('#stat').on('click', function() {
				$('#stat').css('background', 'linear-gradient(#49B681, #298533)');
				$('#chat').css('background', 'linear-gradient(#49708f, #293f50)');
				
				$.post('friendsDB.php', {
					check_stat: true,
					friendName: username,
    				friend_id: user_id
    			}, function(data) {
					$('.field_for_message').html(data);
    			});
			});

			// === Удалить друга из друзей
			$('#delete-friend').on('click', function() {
				if (confirm('Are you sure you want to delete this friend?')) {
					$.post('friendsDB.php', {
						delete_friend: true,
	    				friend_id: user_id
	    			}, function(data) {
	    				if (data == "1") {
	    					alert('Error: cancel application impossible!');
	    				} else {
		    				$('.friends_list').html(data);
	    				}
	    			});
	    			defaultProfile();
				}
			});
		}

		// === Sent app ===
		else if ($('#option2').is(':checked')) {
			showProfile();
			$('.functions-btns').html('<input type="button" class="btn" id="cancel-app" value="Cancel the app">');

			$('#cancel-app').on('click', function() {
				if (confirm('Are you sure you want to cancel the application?')) {
					$.post('friendsDB.php', {
						cancel: true,
	    				friend_id: user_id
	    			}, function(data) {
	    				if (data == "1") {
	    					alert('Error: cancel application impossible!');
	    				} else {
		    				$('.friends_list').html(data);
	    				}
	    			});
	    			defaultProfile();
				}
			});
		}

		// === Incoming app ===
		else if ($('#option3').is(':checked')) {
			showProfile();
			$('.functions-btns').html('<input type="button" class="btn" id="accept-app" value="Accept app">');
			$('.functions-btns').append('<input type="button" class="btn" id="cancel-app" value="Cancel the app">');

			$('#accept-app').on('click', function() {
				if (confirm('Do you want to accept friend request?')) {
					$.post('friendsDB.php', {
						accept: true,
	    				friend_id: user_id
	    			}, function(data) {
	    				if (data === '') $('.check-newMsg').css('display', 'none');
	    				if (data == "1") {
	    					alert('Error: accept application impossible!');
	    				} else {
		    				$('.friends_list').html(data);
	    				}
	    			});
	    			defaultProfile();
				}
			});

			$('#cancel-app').on('click', function() {
				if (confirm('Are you sure you want to cancel the application?')) {
					$.post('friendsDB.php', {
						cancel_incom: true,
	    				friend_id: user_id
	    			}, function(data) {
	    				if (data === '') $('.check-newMsg').css('display', 'none');
	    				if (data == "1") {
	    					alert('Error: cancel application impossible!');
	    				} else {
		    				$('.friends_list').html(data);
	    				}
	    			});
	    			defaultProfile();
				}
			});
		}
	}); 

	// Добавление друга
	$('#friend-add').on('click', function() {
		if ($('#friend-name').val() != '') {
			let friendName = $('#friend-name').val();

			$.post('friendsDB.php', {
				add: true,
    			friendName: friendName
    		}, function(data) {
    			$('#test').html(data);
    		});

    		$('#friend-name').val('');
		}
	});

	// Мои друзья
	$('#my-friends').on('click', function() {
		defaultProfile();
		$('#my-friends').css('background', 'linear-gradient(#49B681, #298533)');
		$('#sent-app').css('background', 'linear-gradient(#49708f, #293f50)');
		$('#incoming-app').css('background', 'linear-gradient(#49708f, #293f50)');

		$.post('friendsDB.php', {
			friends: true,
    	}, function(data) {
    		$('.friends_list').html(data);
    	});
	}); 

	// Отправленные заявки
	$('#sent-app').on('click', function() {
		defaultProfile();
		$('#sent-app').css('background', 'linear-gradient(#49B681, #298533)');
		$('#my-friends').css('background', 'linear-gradient(#49708f, #293f50)');
		$('#incoming-app').css('background', 'linear-gradient(#49708f, #293f50)');

		$.post('friendsDB.php', {
			sent: true,
    	}, function(data) {
    		$('.friends_list').html(data);
    	});
	}); 

	// Полученные заявки
	$('#incoming-app').on('click', function() {
		defaultProfile();
		$('#incoming-app').css('background', 'linear-gradient(#49B681, #298533)');
		$('#my-friends').css('background', 'linear-gradient(#49708f, #293f50)');
		$('#sent-app').css('background', 'linear-gradient(#49708f, #293f50)');


		$.post('friendsDB.php', {
			incoming: true,
    	}, function(data) {
    		$('.friends_list').html(data);
    	});
	});
});