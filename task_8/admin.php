<?php
	require './resources/form.php';

	if (isset($_POST['submit']))
	{
		array_pop($_POST);
		
		foreach($_POST as $file)
		{
			unlink($file);
		}
		
		echo 'Выбранные заявки удалены!';
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
				if (is_dir('./data'))
				{
					$folder = scandir('./data');
					foreach($folder as $file)
					{
						if ($file != '.' && $file != '..')
						{
							echo '<tr>';
							
							$form = new Form();
							$form->read_file('./data/'.$file);
							$data = $form->get_data();
							
							foreach ($data as $row)
							{
								echo '<td>'.$row.'</td>';
							}
							if (!in_array('on', $data))
							{
								echo '<td></td>';
							}
							
							echo '<td><input type="checkbox" name="delete" value=./data/'.$file.'></td></tr>';
						}
					}
				}
			?>
		</table>
		
		<input type="submit" name="submit" value="Удалить">
		
	</form>
</body>