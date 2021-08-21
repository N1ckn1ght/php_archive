<?php
	function validate_date($date, $format = 'Y-m-d')
	{
		$d = DateTime::createFromFormat($format, $date);
		return ($d and $d->format($format) === $date);
	}

	$config = parse_ini_file('./config.ini.php');
	$server = $config['db_server'];
	$user = $config['db_username'];
	$pass = $config['db_password'];
	$dbname = $config['db_name'];
	
	if (isset($_POST['submit']) or isset($_GET['id']))
	{
		try
		{
			$conn = new PDO("mysql:host=$server", $user, $pass);
			$conn->exec('SET NAMES "utf8";');
			$conn->query("use $dbname");
		}
		catch (PDOException $e)
		{
			echo $e->getMessage();
		}
		
		if (isset($_POST['submit']))
		{	
			$year_check = explode('-', $_POST['date'])[0];
			if ($year_check >= 1970 and $year_check < 2038)
			{
				$done = NULL;
				if (isset($_POST['done']))
				{
					$done = date("Y-m-d H:i:s"); 
				}	
				$length = preg_replace('/[^0-9]/', '', $_POST['length']);
				if (strpos($_POST['length'], 'час') !== false)
				{
					$length *= 60;
				}
				$time = $_POST['time'];
				if (strlen($time) < 5)
				{
					$time = '0'.$time;
				}
				$time = $_POST['date'].' '.$time.':00';
				
				if ($_POST['submit'] == 'Добавить')
				{
					try
					{
						$sql = $conn->prepare('INSERT INTO `tasks` (`subject`, `type`, `place`, `datetime`, `length`, `comment`, `done_at`) VALUES (:subject, :type, :place, :datetime, :length, :comment, :done_at)');
						
						$sql->execute([
							':subject' => $_POST['subject'],
							':type' => $_POST['type'],
							':place' => $_POST['place'],
							':datetime' => $time,
							':length' => $length,
							':comment' => $_POST['comment'],
							':done_at' => $done
						]);
						
						echo 'Данные успешно внесены в таблицу!</br>';
						$_POST = array();
					}
					catch (PDOException $e)
					{
						echo $e->getMessage();
					}
				}
				elseif ($_POST['submit'] == 'Обновить')
				{
					try
					{
						$sql = $conn->prepare('UPDATE `tasks` SET `subject` = :subject, `type` = :type, `place` = :place, `datetime` = :datetime, `length` = :length, `comment` = :comment, `done_at` = :done_at WHERE id = :id');
						
						$sql->execute([
							':subject' => $_POST['subject'],
							':type' => $_POST['type'],
							':place' => $_POST['place'],
							':datetime' => $time,
							':length' => $length,
							':comment' => $_POST['comment'],
							':done_at' => $done,
							':id' => $_GET['id']
						]);
						
						echo 'Данные успешно обновлены!</br>';
						$_POST = array();
						unset($_GET['id']);
					}
					catch (PDOException $e)
					{
						echo $e->getMessage();
					}
				}
			}
			else
			{
				echo 'Внимание! Указанный год '.$year_check.' выходит за границы TIMESTAMP в MySQL. Во избежание нулей в дате, данные не будут занесены в таблицу!';
			}
		}
		
		if (isset($_GET['id']))
		{
			$sql = $conn->prepare('SELECT * FROM tasks WHERE id=:id');
			$sql->execute([':id' => $_GET['id']]);
			$task = $sql->fetchAll(PDO::FETCH_ASSOC);
			
			if(empty($task))
			{
				echo 'Задача с id = '.$_GET['id'].' не найдена.';
			}
			else
			{
				$nlength = $task[0]['length'];
				
				if ($nlength >= 60)
				{
					$nlength /= 60;
					$length = $nlength.' час';
					if (($nlength >= 2 and $nlength <= 4) or ($nlength >= 22 and $nlength <= 24))
					{
						$length = $length.'а';
					}
					elseif ($nlength >= 5 and $nlength <= 20)
					{
						$length = $length.'ов';
					}
				}
				else
				{
					$length = $nlength.' минут';
					if (intdiv($nlength, 10) !== 1)
					{
						if ($nlength % 10 === 1)
						{
							$length = $length.'а';
						}
						elseif ($nlength % 10 >= 2 && $nlength % 10 <= 4)
						{
							$length = $length.'ы';
						}
					}
				}
				
				$datetime = explode(' ', $task[0]['datetime'], 2);
				$datetime[1] = substr($datetime[1], 0, -3);
				if (strlen($datetime[1]) === 5 and $datetime[1][0] == '0')
				{
					$datetime[1] = substr($datetime[1], 1);
				}
				
				$_POST['subject'] = $task[0]['subject'];
				$_POST['type'] = $task[0]['type'];
				$_POST['place'] = $task[0]['place'];
				$_POST['length'] = $length;
				$_POST['date'] = $datetime[0];
				$_POST['time'] = $datetime[1];
				$_POST['comment'] = $task[0]['comment'];
				
				if (!is_null($task[0]['done_at']))
				{
					$_POST['done'] = 'on';
				}
				else
				{
					unset($_POST['done']);
				}
			}
		}
		
		$conn = null;
	}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Мой календарь</title>
</head>
<body>
	<div>
		<p>Задача:</p>
		<form method="POST">
			<div class="parent">
				<label>Тема:</label>
				<input required type="text" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? '') ?>">
			</div>
			<div class="parent">
				<label>Тип:</label>
				<select required name="type">
					<?php
						if (array_key_exists('type', $_POST))
						{
							echo '<option selected hidden>'.$_POST['type'].'</option>';
						}
					?>
					<option>Встреча</option>
					<option>Звонок</option>
					<option>Совещание</option>
					<option>Дело</option>
				</select>
			</div>
			<div class="parent">
				<label>Место:</label>
				<input required type="text" name="place" value="<?php echo htmlspecialchars($_POST['place'] ?? '') ?>">
			</div>
			<div class="parent">
				<label>Дата и время:</label>
				<div>
					<select required name="time">
						<?php
							if (array_key_exists('time', $_POST))
							{
								echo '<option selected hidden>'.$_POST['time'].'</option>';
							}
							for ($i = 0; $i <= 23; $i++)
							{
								echo '<option>'.$i.':00</option>';
							}
						?>
					</select>
					<input required type="date" name="date" value="<?php echo htmlspecialchars($_POST['date'] ?? '') ?>">
				</div>
			</div>
			<div class="parent">
				<label>Длительность:</label>
				<select required name="length">
					<?php
						if (array_key_exists('length', $_POST))
						{
							echo '<option selected hidden>'.$_POST['length'].'</option>';
						}
					?>
					<option>5 минут</option>
					<option>15 минут</option>
					<option>30 минут</option>
					<option>1 час</option>
					<option>2 часа</option>
					<option>4 часа</option>
				</select>
			</div>
			<div class="parent">
				<label>Комментарий:</label>
				<input type="text" name="comment" value="<?php echo htmlspecialchars($_POST['comment'] ?? '') ?>">
			</div>
			<div class="parent">
				<label>Задача выполнена</label>
				<input type="checkbox" name="done"
					<?php
						if (array_key_exists('done', $_POST))
						{
							echo(' checked');
						}
					?>
				>
			</div>
			<div class="parent">
				<input type="submit" name="submit" value="<?php
					if (isset($_GET['id']))
					{
						echo 'Обновить';
					}
					else
					{
						echo 'Добавить';
					}
				?>">
			</div>
		</form>
	</div>
	<div>
		<p>Список задач:</p>
		<div class="parent">
			<?php
				echo '<label><a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'], 2)[0].'">Текущие</a></label>';
				echo '<label><a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'], 2)[0].'?filter=dead">Просроченные</a></label>';
				echo '<label><a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'], 2)[0].'?filter=done">Выполненные</a></label>';
			?>
		</div>
		<form method="GET">
			<div class="parent">
				<input required type="date" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? '') ?>">
				<input type="submit" name="submit" value="Найти">
			</div>
		</form>
		
		<table>
			<tr>
				<th>Тип</th>
				<th>Задача</th>
				<th>Место</th>
				<th>Дата и время</th>
			</tr>
			<?php
				try
				{
					$conn = new PDO("mysql:host=$server", $user, $pass);
					$conn->exec('SET NAMES "utf8";');
					$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
					$conn->query("use $dbname");
					$conn->exec('
						CREATE TABLE IF NOT EXISTS `tasks`(
						`id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
						`subject` VARCHAR(1027) NOT NULL,
						`type` VARCHAR(1027) NOT NULL,
						`place` VARCHAR(1027) NOT NULL,
						`datetime` DATETIME NOT NULL,
						`length` INT(10) NOT NULL,
						`comment` VARCHAR(1027) NULL,
						`done_at` TIMESTAMP NULL
					)');
					
					$show = true;
					
					if (isset($_GET['filter']))
					{
						if ($_GET['filter'] == 'dead')
						{
							$select = $conn->query('SELECT * FROM `tasks` WHERE `done_at` IS NULL AND `datetime` < CURRENT_TIMESTAMP');
						}
						elseif ($_GET['filter'] == 'done')
						{
							$select = $conn->query('SELECT * FROM `tasks` WHERE `done_at` IS NOT NULL');
						}
					}
					elseif (isset($_GET['search']))
					{
						$date = $_GET['search'];
						if (validate_date($date))
						{
							$select = $conn->query('SELECT * FROM `tasks` WHERE DATE(`datetime`) = \''.$date.'\'');
						}
						else
						{
							echo 'Неверно указана дата.</br>';
							$show = false;
						}
					}
					else
					{
						$select = $conn->query('SELECT * FROM `tasks` WHERE `done_at` IS NULL');
					}
					
					if ($show === true)
					{
						$tasks = $select->fetchAll(PDO::FETCH_ASSOC);
						foreach ($tasks as $t)
						{
							echo '<tr><td>'.$t['type'].'</td>';
							echo '<td><a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'], 2)[0].'?id='.$t['id'].'">'.$t['subject'].'</a></td>';
							echo '<td>'.$t['place'].'</td>';
							echo '<td>'.$t['datetime'].'</td></tr>';
						}
					}
					
					$conn = null;
				}
				catch (PDOException $e)
				{
					echo $e->getMessage();
				}
			?>
		</table>
	</div>
</body>