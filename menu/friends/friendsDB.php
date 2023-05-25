<?php

session_start();
require_once '../../vendor/connect.php';

class InteractionFriends {
	private $dbh;

	public function __construct ($dbh) {
		$this->dbh = $dbh;
	}

	// === Метод добавления новых друзей ===
	public function addFriend($id, $username, $friendName) {	
		// Проверка на существование пользователя
		$sql = "SELECT * FROM user WHERE username = :username";
		$sth = $this->dbh->prepare($sql);
		$sth->bindValue(':username', $friendName);
		$sth->execute();
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($res)) {

			$friend_id = $res[0]['id']; // id юзера, которому отправлена заявка
			if ($friend_id == $id) die('Is that your username!');

			// Запрос на все таблицы "Друга"
			$res = $this->dbh->query("SELECT * FROM user_friends WHERE user_id = '$friend_id'")->fetch(PDO::FETCH_ASSOC);

			$checkFriends = json_decode($res['friends'], true);
			$checkSent = json_decode($res['sent_app'], true);
			// Таблица входящих заявок
			$incomApp = json_decode($res['incoming_app'], true);

			// Проверка всех колонок юзера
			foreach ($checkFriends as $item) {
				if ($item['id'] == $id) {
					die('This user is already your friend!');
				}
			}

			foreach ($checkSent as $item) {
				if ($item['id'] == $id) {
					die('This user has already sent you a friend request!');
				}
			}

			foreach ($incomApp as $item) {
				if ($item['id'] == $id) {
					die('Your friend request is pending!');
				}
			}

			$sentApp = $this->dbh->query("SELECT sent_app FROM user_friends WHERE user_id = '$id'");

			// Информация отправленной заявки в формате JSON
			$json_sentApp = array(
				'id' => $id,
				'username' => $username,
			);

			$json_incomApp = array(
				'id' => $friend_id,
				'username' => $friendName,
			);

			// Преобразование резултатов в из JSON в массив
			$sentApp = $sentApp->fetch(PDO::FETCH_ASSOC);
			$sentApp = json_decode($sentApp['sent_app'], true);
			array_push($sentApp, $json_incomApp);
			array_push($incomApp, $json_sentApp);
			$sentApp = json_encode($sentApp);
			$incomApp = json_encode($incomApp);

			// Обновляем БД заявок в друзья
			$sql = "UPDATE user_friends SET sent_app = :sentApp WHERE user_id = :id";
			$sth = $this->dbh->prepare($sql);
			$sth->bindValue(':sentApp', $sentApp);
			$sth->bindValue(':id', $id);
			$sth->execute();

			$sql = "UPDATE user_friends SET incoming_app = :incomApp WHERE user_id = :id";
			$sth = $this->dbh->prepare($sql);
			$sth->bindValue(':incomApp', $incomApp);
			$sth->bindValue(':id', $friend_id);
			$sth->execute();

			echo 'Application successfully sent!';

			//test
			//echo $sentApp.'<br>'.$incomApp;
		} else {
			echo 'User with this name not found';
		}
	}

	// === Метод для возвращения списка друзей ===
	public function getFriends($id) {
		$sql = "SELECT friends FROM user_friends WHERE user_id = '$id'";
		$res = $this->dbh->query($sql)->fetch(PDO::FETCH_ASSOC);
		$res = json_decode($res['friends'], true);

		foreach($res as $item) {
			echo '<li class="user_item" id="'.$item["id"].'">'.$item["username"].'</li>';
		}
	}

	// === Метод для возвращения списка отправленных заявок
	public function getSentApp($id) {
		$sql = "SELECT sent_app FROM user_friends WHERE user_id = '$id'";
		$res = $this->dbh->query($sql)->fetch(PDO::FETCH_ASSOC);
		$res = json_decode($res['sent_app'], true);

		foreach($res as $item) {
			echo '<li class="user_item" id="'.$item["id"].'">'.$item["username"].'</li>';
		}
	}

	// === Метод для возвращения списка новых заявок в друзья
	public function getIncomApp($id) {
		$sql = "SELECT incoming_app FROM user_friends WHERE user_id = '$id'";
		$res = $this->dbh->query($sql)->fetch(PDO::FETCH_ASSOC);
		$res = json_decode($res['incoming_app'], true);

		foreach($res as $item) {
			echo '<li class="user_item" id="'.$item["id"].'">'.$item["username"].'</li>';
		}
	}

	// === Метод для отмены отправленной заявки в друзья
	public function cancelApp($id, $friend_id, $sentOrIncom) {
		// Проверка на существование пользователя
		$sql = "SELECT * FROM user WHERE id = :id";
		$sth = $this->dbh->prepare($sql);
		$sth->bindValue(':id', $friend_id);
		$sth->execute();
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($res)) {

			// Запрос на определенную колонку юзеров
			$sentApp = $this->dbh->query("SELECT sent_app FROM user_friends WHERE user_id = '$id'");
			$incomApp = $this->dbh->query("SELECT incoming_app FROM user_friends WHERE user_id = '$friend_id'");

			// Получиение чистого массива
			$sentApp = $sentApp->fetch(PDO::FETCH_ASSOC);
			$sentApp = json_decode($sentApp['sent_app'], true);
			$incomApp = $incomApp->fetch(PDO::FETCH_ASSOC);
			$incomApp = json_decode($incomApp['incoming_app'], true);

			$flag = true;
			// Удаляем из списка запросов запрос
			foreach ($sentApp as $key => $item) {
				if ($item['id'] == $friend_id) {
					unset($sentApp[$key]);
					$flag = false;
					break;
				}
			}
			if ($flag) die('1');

			foreach ($incomApp as $key => $item) {
				if ($item['id'] == $id) {
					unset($incomApp[$key]);
					break;
				}
			}

			// Добавляем обновленный объект запросов юзерам обратно
			$sentApp = json_encode($sentApp);
			$incomApp = json_encode($incomApp);

			// Обновляем БД заявок в друзья
			$sql = "UPDATE user_friends SET sent_app = :sentApp WHERE user_id = :id";
			$sth = $this->dbh->prepare($sql);
			$sth->bindValue(':sentApp', $sentApp);
			$sth->bindValue(':id', $id);
			$sth->execute();

			$sql = "UPDATE user_friends SET incoming_app = :incomApp WHERE user_id = :id";
			$sth = $this->dbh->prepare($sql);
			$sth->bindValue(':incomApp', $incomApp);
			$sth->bindValue(':id', $friend_id);
			$sth->execute();

			// Проверка на отмену заявки от отправителя или получателя
			if ($sentOrIncom) $res = json_decode($sentApp, true);
			else $res = json_decode($incomApp, true);

			// Выводим новый список запросов юзеру
			foreach($res as $item) {
			echo '<li class="user_item" id="'.$item["id"].'">'.$item["username"].'</li>';
			}
		} else die('Error: Not ID');
	}

	// === Метод для принятия заявки в друзья ===
	public function acceptApp($id, $friend_id) {
		// Проверка на существование пользователя
		$sql = "SELECT * FROM user WHERE id = :id";
		$sth = $this->dbh->prepare($sql);
		$sth->bindValue(':id', $friend_id);
		$sth->execute();
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($res)) {

			// Запрос на определенную колонку юзеров
			$friendDB = $this->dbh->query("SELECT * FROM user_friends WHERE user_id = '$friend_id'");
			$myDB = $this->dbh->query("SELECT * FROM user_friends WHERE user_id = '$id'");

			// Получиение чистого массива
			$friendDB = $friendDB->fetch(PDO::FETCH_ASSOC);
			$sentApp = json_decode($friendDB['sent_app'], true);
			$myDB = $myDB->fetch(PDO::FETCH_ASSOC);
			$incomApp = json_decode($myDB['incoming_app'], true);

			$flag = true;
			// Удаляем из списка запросов запрос
			foreach ($sentApp as $key => $item) {
				if ($item['id'] == $id) {

					$friends = json_decode($friendDB['friends'], true);
					array_push($friends, $item);
					$friends = json_encode($friends);

					$sql = "UPDATE user_friends SET friends = :friends WHERE user_id = :id";
					$sth = $this->dbh->prepare($sql);
					$sth->bindValue(':friends', $friends);
					$sth->bindValue(':id', $friend_id);
					$sth->execute();

					unset($sentApp[$key]);
					$flag = false;
					break;
				}
			}
			if ($flag) die('1');

			foreach ($incomApp as $key => $item) {
				if ($item['id'] == $friend_id) {

					$friends = json_decode($myDB['friends'], true);
					array_push($friends, $item);
					$friends = json_encode($friends);

					$sql = "UPDATE user_friends SET friends = :friends WHERE user_id = :id";
					$sth = $this->dbh->prepare($sql);
					$sth->bindValue(':friends', $friends);
					$sth->bindValue(':id', $id);
					$sth->execute();

					unset($incomApp[$key]);
					break;
				}
			}

			// Добавляем обновленный объект запросов юзерам обратно
			$sentApp = json_encode($sentApp);
			$incomApp = json_encode($incomApp);

			// Обновляем БД заявок в друзья
			$sql = "UPDATE user_friends SET sent_app = ? WHERE user_id = ?";
			$sth = $this->dbh->prepare($sql);
			$sth->execute(array($sentApp, $friend_id));

			$sql = "UPDATE user_friends SET incoming_app = ? WHERE user_id = ?";
			$sth = $this->dbh->prepare($sql);
			$sth->execute(array($incomApp, $id));

			// Выводим новый список запросов юзеру
			$incomApp = json_decode($incomApp, true);
			foreach($incomApp as $item) {
			echo '<li class="user_item" id="'.$item["id"].'">'.$item["username"].'</li>';
			}

			// === Создаем 2 чата для юзера и его друга (если их нету)
			$newChats = $this->dbh->query("SELECT * FROM friendly_chat WHERE user_id = '$id' AND friend_id = '$friend_id'");
			$newChats = $newChats->fetchAll(PDO::FETCH_ASSOC);

			if (!count($newChats)) {
				$json = json_encode(array());

				$sql = "INSERT INTO friendly_chat (user_id, friend_id, chat, newMsg) VALUES (?, ?, ?, ?)";
				$sth = $this->dbh->prepare($sql);
				$sth->execute(array($id, $friend_id, $json, $json));
				$sth->execute(array($friend_id, $id, $json, $json));
			}
		} else die('Error: Not ID');
	}

	// Удаление друга из списка друзей
	public function deleteFriend($id, $friend_id) {

		// Информация о списках друзей
		$friendDB = $this->dbh->query("SELECT * FROM user_friends WHERE user_id = '$friend_id'");
		$myDB = $this->dbh->query("SELECT * FROM user_friends WHERE user_id = '$id'");

		// Получиение массива друзей
		$friendDB = $friendDB->fetch(PDO::FETCH_ASSOC);
		$friendDB = json_decode($friendDB['friends'], true);
		$myDB = $myDB->fetch(PDO::FETCH_ASSOC);
		$myDB = json_decode($myDB['friends'], true);

		// Удаляем юзеров из списка друзей
		$flag = true;
		foreach ($friendDB as $key => $item) {
			if ($item['id'] == $id) {
				unset($friendDB[$key]);
				$flag = false;
				break;
			}
		}
		if ($flag) die('1');

		foreach ($myDB as $key => $item) {
			if ($item['id'] == $friend_id) {
				unset($myDB[$key]);
				break;
			}
		}

		// Добавляем обновленный объект запросов юзерам обратно
		$myDB = json_encode($myDB);
		$friendDB = json_encode($friendDB);

		// Обновляем БД заявок в друзья
		$sql = "UPDATE user_friends SET friends = ? WHERE user_id = ?";
		$sth = $this->dbh->prepare($sql);
		$sth->execute(array($myDB, $id));
		$sth->execute(array($friendDB, $friend_id));

		// Выводим новый список друзей юзеру
		$myDB = json_decode($myDB, true);
		foreach($myDB as $item) {
		echo '<li class="user_item" id="'.$item["id"].'">'.$item["username"].'</li>';
		}
	}

	// === Просмотр статистики друга в играх ===
	public function checkStat($id, $username, $friend_id, $friendName) {

		// Запросы на статистику игр друга и личную
		// $friendDB = $this->dbh->query("SELECT * FROM snake JOIN tetris ON snake.ID_snake = tetris.ID_tetris JOIN roulette ON snake.ID_snake = roulette.ID_roulette WHERE snake.ID_snake = $friend_id");
		// $myDB = $this->dbh->query("SELECT * FROM snake JOIN tetris ON snake.ID_snake = tetris.ID_tetris JOIN roulette ON snake.ID_snake = roulette.ID_roulette WHERE snake.ID_snake = $id");

		$games = ['snake', 'tetris', 'roulette'];
		foreach ($games as $row) {
			$sql = 'SELECT * FROM '.$row.' WHERE ID_'.$row.' = '.$friend_id;
			$sth = $this->dbh->query($sql);
			$friendDB = $sth->fetch();

			$sql = 'SELECT * FROM '.$row.' WHERE ID_'.$row.' = '.$id;
			$sth = $this->dbh->query($sql);
			$myDB = $sth->fetch();

			echo '<table border="1">';
			echo '<caption><b>'.$row.'</b></caption>';
			echo '<tr>
				<th>NickName</th>
				<th>Top score</th>
				<th>Games played</th>
			</tr>';
			echo '<tr>
				<td>'.$friendName.'</td>
				<td>'.$friendDB[1].'</td>
				<td>'.$friendDB[2].'</td>
			</tr> 
			<tr>
				<td>'.$username.'</td>
				<td>'.$myDB[1].'</td>
				<td>'.$myDB[2].'</td>
			</tr>';
			echo '</table>';
		}
	}
}

$id = $_SESSION['user_info']['id'];
$username = $_SESSION['user_info']['username'];
$obj = new InteractionFriends($dbh);

if ($_POST['add']) {
	$obj->addFriend($id, $username, $_POST['friendName']);
}
else if ($_POST['friends']) {
	$obj->getFriends($id);
}
else if ($_POST['sent']) {
	$obj->getSentApp($id);
}
else if ($_POST['incoming']) {
	$obj->getIncomApp($id);
}
else if ($_POST['cancel']) {
	$obj->cancelApp($id, $_POST['friend_id'], true);
}
else if ($_POST['accept']) {
	$obj->acceptApp($id, $_POST['friend_id']);
}
else if ($_POST['cancel_incom']) {
	$obj->cancelApp($_POST['friend_id'], $id, false);
}
else if ($_POST['delete_friend']) {
	$obj->deleteFriend($id, $_POST['friend_id']);
}
else if ($_POST['check_stat']) {
	$obj->checkStat($id, $username, $_POST['friend_id'], $_POST['friendName']);
}

$dbh = null;
?>