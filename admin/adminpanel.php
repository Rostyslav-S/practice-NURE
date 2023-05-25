<?php
	session_start();
	require_once '../vendor/connect.php';
	require_once 'connectAdminDB.php';
	if ($_SESSION['user_info']['id'] != 1) header('Location: ../menu.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Admin Panel</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="panelCSS.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
</head>
<body>
	<script type="text/javascript" src="scriptAdmin.js" defer></script>
	<div class="adminPanelcol">
		<div class="adminPanelrow">
			<div class="blockPanel">
				<p class="header_P">Ban/unban a user</p>
				<div class="user_ban">
					<div class="element">
						<p>User id: </p>
						<input type="number" autocomplete="off" id="user_id">
					</div>
					<div class="element">
						<p>User nickname: </p>
						<input type="text" autocomplete="off" id="user_nickname">
					</div>
					<div class="element">
						<p>The reason for the ban: </p>
						<textarea id="cause"></textarea>
					</div>
					<div class="element">
						<p>Amount of days: </p>
						<input type="number" autocomplete="off" id="days">
					</div>
					<input type="button" class="btn" value="Ban" id="btnBan">
					<input type="button" class="btn" value="Unban" id="btnUnban" style="display: none;">
					<div class="checkbox">
						<input type="checkbox" id="checkUnban">
						<label for="checkUnban">Unban user</label>
					</div>
					<div class="res" id="banMsg"></div>
				</div>
			</div>
			<div class="blockPanel">
				<p class="header_P">Block chat a user</p>
				<div class="user_ban">
					<div class="element">
						<p>User id: </p>
						<input type="number" autocomplete="off" id="user_id">
					</div>
					<div class="element">
						<p>User nickname: </p>
						<input type="text" autocomplete="off" id="user_nickname">
					</div>
					<div class="element">
						<p>The reason for the ban: </p>
						<textarea id="cause"></textarea>
					</div>
					<div class="element">
						<p>Amount of days: </p>
						<input type="number" autocomplete="off" id="days">
					</div>
					<input type="button" class="btn" value="Ban" id="btnBan">
					<div class="res" id="banMsg"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="adminPanelcol">
		<div class="adminPanelrow">
			<div class="blockPanelChat">
				<p class="header_P">Chat with users</p>
				<div class="container">
					<div class="users_info">
						<ul class="users_list">
							<?php
								$sql = 'SELECT * FROM users_chat';
								$res = $admindbh->query($sql);

								foreach($res as $item) {
									echo '<li class="user_item" id="'.$item["user_id"].'">'.$item["username"].' (id: '.$item["user_id"].')</li>';
								}
							?>
						</ul>
					</div>
					<div class="field_for_message">
						<div class="chat-container">
						</div>
						<div class="text-and-btn">
							<textarea id="text-to-send"></textarea>
							<input type="button" class="btn" value="Send" id="btnSendMsg" disabled>
						</div>
					</div>
				</div>
				<div class="res" id="msgInfo"></div>
			</div>
		</div>
	</div>
</body>