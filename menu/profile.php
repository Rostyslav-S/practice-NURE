<?php
	session_start();
	require_once '../vendor/connect.php';
	require_once '../admin/connectAdminDB.php';
	if (!$_SESSION['user_info']) header('Location: ../index.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Menu</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="stylesProfile.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
</head>
<body>
	<script src="profileChange.js" defer></script>
	<div class="profile">
		<div class="menuLog">
			<div class="header">
				<h3>Profile</h3>
				<input id="admin_chat" type="checkbox">
				<label for="admin_chat">Admin chat
					<div class="check-newMsg">!</div>
				</label>
			</div>
			<?php
				echo '<div class="info">ID: <p class="item_info" id="myID">'.$_SESSION["user_info"]["id"].'</p></div>';
				echo '<div class="info">Name: <p class="item_info">'.$_SESSION["user_info"]["username"].'</p></div>';
			?>
			<div class="profileChange">
				<input id="nameChange" class="btn" type="button" value="Name change">
				<input id="passwordChange" class="btn" type="button" value="Password change">
				<input id="delAcc" class="btn" type="button" value="Delete account">
			</div>
			<div id="msg"></div>
			<div class="accordion">
				<div class="accordion_item">
					<input id="snake_info" name="accordion_head" type="checkbox">
					<label for="snake_info">Snake progress</label>
					<div class="info_body">
						<?php
							echo '<div class="info">Top score: <p class="item_info">'.$_SESSION["user_info"]["snake_topScore"].'</p></div>';
							echo '<div class="info">Num of games: <p class="item_info">'.$_SESSION["user_info"]["snake_numGames"].'</p></div>';
						?>
					</div>
				</div>
				<div class="accordion_item">
					<input id="tetris_info" name="accordion_head" type="checkbox">
					<label for="tetris_info">Tetris progress</label>
					<div class="info_body">
						<?php
							echo '<div class="info">Top score: <p class="item_info">'.$_SESSION["user_info"]["tetris_topScore"].'</p></div>';
							echo '<div class="info">Num of games: <p class="item_info">'.$_SESSION["user_info"]["tetris_numGames"].'</p></div>';
						?>
					</div>
				</div>
				<div class="accordion_item">
					<input id="roulette_info" name="accordion_head" type="checkbox">
					<label for="roulette_info">Roulette progress</label>
					<div class="info_body">
						<?php
							echo '<div class="info">Deposit: <p class="item_info">'.$_SESSION["user_info"]["roulette_deposit"].' $</p></div>';
							echo '<div class="info">Num of games: <p class="item_info">'.$_SESSION["user_info"]["roulette_numGames"].'</p></div>';
						?>
					</div>
				</div>
				<form action="../menu.php">
					<input type="submit" value="Menu" name="btnMenu" class="btn">
				</form>
			</div>
		</div>
		<div class="admin_chat">
			<div class="field_for_message">
				<div class="chat-container">
				</div>
				<div class="text-and-btn">
					<textarea id="text-to-send"></textarea>
					<input type="button" class="btn" value="Send" id="btnSendMsg">
				</div>
			</div>
		</div>
	</div>
</body>
</html>