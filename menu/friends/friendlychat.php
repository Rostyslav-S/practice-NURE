<?php

session_start();
require_once '../../vendor/connect.php';

class FriendlyChat {
	private $dbh;

	public function __construct ($dbh) {
		$this->dbh = $dbh;
	}

	// === Метод для возвращения переписки с другом
	public function getChat($id, $friend_id) {
		$sql = 'SELECT * FROM friendly_chat WHERE user_id = ? AND friend_id = ?';
		$sth = $this->dbh->prepare($sql);
		$sth->execute(array($id, $friend_id));
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($res)) {
			$json_chat = json_decode($res[0]['chat'], true);
			$newMsg = json_decode($res[0]['newMsg'], true);

			if (count($json_chat) || count($newMsg)) {
				foreach ($json_chat as $item) {
					if ($item['id'] == $id) {
						echo '<div class="message sent-message">';
					} else {
						echo '<div class="message received-message">';
					}
					echo '<p class="message-text">'.$item['msg'].'</p>';
					echo '<span class="message-time">'.$item['time'].'</span></div>';
				}

				if (count($newMsg)) {

					$json_chat = array_merge($json_chat, $newMsg);

					echo '<div class="newMsgInfo">New message</div>';
					foreach ($newMsg as $item) {
						echo '<div class="message received-message">';
						echo '<p class="message-text">'.$item['msg'].'</p>';
						echo '<span class="message-time">'.$item['time'].'</span></div>';
					}

					$sql = 'UPDATE friendly_chat SET chat = ?, newMsg = ? WHERE user_id = ? AND friend_id = ?';
					$sth = $this->dbh->prepare($sql);
					$json_chat = json_encode($json_chat);
					$newMsg = json_encode(array());
					$sth->execute(array($json_chat, $newMsg, $id, $friend_id));
				}

			} else die ('<div class="chat-info">Send a message to a friend to start a chat!</div>');

		} else die('<div class="chat-info">Error: chat not loaded!</div>');
	}

	// === Отправка сообщения 
	public function sendMsg($id, $username, $friend_id, $sendMsg) {

		$sql = 'SELECT * FROM friendly_chat WHERE user_id = ? AND friend_id = ?';
		$sth = $this->dbh->prepare($sql);
		$sth->execute(array($id, $friend_id));
		$myDB = $sth->fetchAll(PDO::FETCH_ASSOC);

		$sth->execute(array($friend_id, $id));
		$friendDB = $sth->fetchAll(PDO::FETCH_ASSOC);

		// Проверка на существование двух чатов
		if (count($myDB) && count($friendDB)) {

			// Формируем сообщение
			$time = date('H:i');
			$data = array(
				'id' => $id,
				'name' => $username,
				'msg' => $sendMsg,
				'time' => $time
			);

			// Переводим полученые результы (чаты) в массив
			$myChat = json_decode($myDB[0]['chat'], true);
			//$friendChat = json_decode($friendDB[0]['chat'], true);
			$friendNewMsg = json_decode($friendDB[0]['newMsg'], true);

			// Записываем новое сообщение
			array_push($myChat, $data);
			//array_push($friendChat, $data);
			array_push($friendNewMsg, $data);

			// Декодируем массив в JSON и сохраняем его в БД
			$myChat = json_encode($myChat);
			//$friendChat = json_encode($friendChat);
			$friendNewMsg = json_encode($friendNewMsg);

			$sql = 'UPDATE friendly_chat SET chat = ? WHERE user_id = ? AND friend_id = ?';
			$sth = $this->dbh->prepare($sql);
			$sth->execute(array($myChat, $id, $friend_id));

			$sql = 'UPDATE friendly_chat SET newMsg = ? WHERE user_id = ? AND friend_id = ?';
			$sth = $this->dbh->prepare($sql);
			$sth->execute(array($friendNewMsg, $friend_id, $id));

			echo '<div class="message sent-message">';
			echo '<p class="message-text">'.$sendMsg.'</p>';
			echo '<span class="message-time">'.$time.'</span></div>';

		} else die('<div class="chat-info">Error: message not sent!</div>');
	}

	// Метод возвращающий новые сообщения
	public function getNewMsg($id, $friend_id) {
		$myDB = $this->dbh->query("SELECT * FROM friendly_chat WHERE user_id = '$id' AND friend_id = '$friend_id'");
		$myDB = $myDB->fetch(PDO::FETCH_ASSOC);
		$newMsg = json_decode($myDB['newMsg'], true);
		$chat = json_decode($myDB['chat'], true);
		
		if (count($newMsg)) {

			foreach ($newMsg as $item) {
				echo '<div class="message received-message">';
				echo '<p class="message-text">'.$item['msg'].'</p>';
				echo '<span class="message-time">'.$item['time'].'</span></div>';
			}

			$chat = array_merge($chat, $newMsg);
			$updateNewMsg = json_encode(array());
			$sql = 'UPDATE friendly_chat SET chat = ?, newMsg = ? WHERE user_id = ? AND friend_id = ?';
			$sth = $this->dbh->prepare($sql);
			$sth->execute(array($chat, $updateNewMsg, $id, $friend_id));
		} else die('1');
	}
}

$id = $_SESSION['user_info']['id'];
$username = $_SESSION['user_info']['username'];
$obj = new FriendlyChat($dbh);

if ($_POST['getChat']) {
	$obj->getChat($id, $_POST['friend_id']);
}
else if ($_POST['sendMsg']) {
	$obj->sendMsg($id, $username, $_POST['friend_id'], $_POST['sendMsg']);
}
else if ($_POST['getNewMsg']) {
	$obj->getNewMsg($id, $_POST['friend_id']);
}

?>