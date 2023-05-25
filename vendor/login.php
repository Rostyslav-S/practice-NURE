<?php

session_start();
require_once 'connect.php';
require_once '../admin/connectAdminDB.php';

$username = $_POST['username'];
$password = md5($_POST['password']);

$sql = "SELECT * FROM user WHERE username = :username AND password = :password";
$sth = $dbh->prepare($sql);
$sth->bindValue(':username', $username);
$sth->bindValue(':password', $password);
$sth->execute();
$res = $sth->fetchAll(PDO::FETCH_ASSOC);

if (count($res) > 0) {

	$sqlBan = 'SELECT * FROM users_ban WHERE user_id = '.$res[0]['id'];
	$sth = $admindbh->query($sqlBan);
	$checkBan = $sth->fetchAll(PDO::FETCH_ASSOC);

	if (count($checkBan) > 0) {
		$currentDateTime = new DateTime();

		foreach ($checkBan as $row) {
			// Преобразование даты и времени из базы данных в объект DateTime
			$end_date = DateTime::createFromFormat('Y-m-d H:i:s', $row['end_date']);

			if ($currentDateTime < $end_date) {
				$cause = $row['cause'];
				$_SESSION['msgError'] = 'Ваш аккаун заблокирован до<br>'.$end_date->format('Y-m-d H:i:s').'<br>Причина: '.$cause;
				header('Location: ../index.php');
				die();
			}
		}
	}

	$games = ['snake', 'tetris', 'roulette'];

	foreach ($games as $row) {
		$sql = 'SELECT * FROM '.$row.' WHERE ID_'.$row.' = '.$res[0]['id'];
		$sth = $dbh->query($sql);
		$res[$row] = $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	// Создание ряда с данным об игре в случае ее отсутсвия
	// *** цикл по коллекции $res с выявлением пустого элемента ***

	$_SESSION['user_info'] = [
		"id" => $res[0]['id'],
		"username" => $res[0]['username'],
		"snake_topScore" => $res["snake"][0]["topScore"],
		"snake_numGames" => $res["snake"][0]["num_of_games"],
		"tetris_topScore" => $res["tetris"][0]["topScore"],
		"tetris_numGames" => $res["tetris"][0]["num_of_games"],
		"roulette_deposit" => $res["roulette"][0]["deposit"],
		"roulette_numGames" => $res["roulette"][0]["num_of_games"]
	];

	header('Location: ../menu.php');

} else {
	$_SESSION['msgError'] = 'Неверный логин или пароль!';
	header('Location: ../index.php');
}

?>