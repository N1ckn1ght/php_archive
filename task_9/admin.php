<?php
	require './resources/arrays.php';
	
	$config = parse_ini_file('./config.ini.php');
			
	$server = $config['db_server'];
	$user = $config['db_username'];
	$pass = $config['db_password'];
	$dbname = $config['db_name'];

	if (isset($_POST['submit']))
	{
		array_pop($_POST);
		
		if (!empty($_POST))
		{
			$conn = new PDO("mysql:host=$server", $user, $pass);
			$conn->exec('SET NAMES "utf8";');
			
			try
			{
				$conn->query("use $dbname");
				$sql = $conn->prepare('UPDATE `participants` SET `deleted_at`=CURRENT_TIMESTAMP WHERE `id`=:id');
				
				foreach($_POST as $value)
				{
					$sql->execute([':id' => $value]);
				}
				
				echo 'Удалено заявок: '.count($_POST);
			}
			catch (PDOException $e)
			{
				echo $e->getMessage();
			}
					
			$conn = null;
		}
		else
		{
			echo 'Нет отмеченных заявок для удаления.';
		}
		
		$_POST = array();
	}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<!-- link rel="stylesheet" type="text/css" href="style.css" -->
	<title>Панель администратора</title>
</head>
<body>
	<form method="POST">
		
		<table>
			<tr>
				<th>Имя</th>
				<th>Фамилия</th>
				<th>Электронная почта</th>
				<th>Номер телефона</th>
				<th>Тема</th>
				<th>Способ оплаты</th>
				<th>Рассылка</th>
				<th></th>
			</tr>
			<?php
				$conn = new PDO("mysql:host=$server", $user, $pass);
				$conn->exec('SET NAMES "utf8";');
				
				try
				{
					$conn->query("use $dbname");
					$select = $conn->query('SELECT * FROM `participants`');
					$participants = $select->fetchAll(PDO::FETCH_ASSOC);
					
					foreach ($participants as $p)
					{
						if (is_null($p['deleted_at']))
						{
							echo '<tr><td>'.$p['name'].'</td>';
							echo '<td>'.$p['surname'].'</td>';
							echo '<td>'.$p['email'].'</td>';
							echo '<td>'.$p['phone'].'</td>';
							echo '<td>'.$subjects[$p['subject']].'</td>';
							echo '<td>'.$payments[$p['payment']].'</td>';
							if ($p['spam'] == 1)
							{
								echo '<td>Подписан</td>'; 
							}
							else
							{
								echo '<td></td>';
							}
							echo '<td><input type="checkbox" name="delete" value='.$p['id'].'></td></tr>';
						}
					}
				}
				catch (PDOException $e)
				{
					echo $e->getMessage();
				}
				
				$conn = null;
			?>
		</table>
		
		<input type="submit" name="submit" value="Удалить">
		
	</form>
</body>