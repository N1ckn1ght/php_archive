<?php
	require './resources/form.php';
	
	if (isset($_POST['submit']))
	{
		$form = new Form($_POST, $subjects, $payments);
		$errors = $form->validate();
		
		if (empty($errors))
		{
			$form->save();
			echo 'Ваша заявка принята, '.$_POST['name'].'!';
			$_POST = array();
		}
	}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Регистрация</title>
</head>
<body>
	<form method="POST">

		<label>
			Имя:
			<input required type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '') ?>">
		</label>
		<div class="error">
			<?php echo $errors['name'] ?? ''?>
		</div>
		
		<label>
			Фамилия:
			<input required type="text" name="surname" value="<?php echo htmlspecialchars($_POST['surname'] ?? '') ?>">
		</label>
		<div class="error">
			<?php echo $errors['surname'] ?? ''?>
		</div>
		
		<label>
			Электронная почта:
			<input required type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
		</label>
		<div class="error">
			<?php echo $errors['email'] ?? ''?>
		</div>
		
		<label>
			Номер телефона:
			<input required type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? '') ?>">
		</label>
		<div class="error">
			<?php echo $errors['phone'] ?? ''?>
		</div>
		
		<label>
			Тема:
			<select required name="subject">
				<?php
					if (array_key_exists('subject', $_POST))
					{
						echo '<option selected hidden>'.$_POST['subject'].'</option>';
					}
					foreach ($subjects as $subject)
					{
						echo '<option>'.$subject.'</option>';
					}
				?>
			</select>
		</label>
		<div class="error">
			<?php echo $errors['subject'] ?? ''?>
		</div>
		
		<label>
			Способ оплаты:
			<select required name="payment">
				<?php
					if (array_key_exists('payment', $_POST))
					{
						echo '<option selected hidden>'.$_POST['payment'].'</option>';
					}
					foreach ($payments as $payment)
					{
						echo '<option>'.$payment.'</option>';
					}
				?>
			</select>
		</label>
		<div class="error">
			<?php echo $errors['payment'] ?? ''?>
		</div>
		
		<label>
			<input type="checkbox" name="spam"
				<?php
					if (array_key_exists('spam', $_POST))
					{
						echo(' checked');
					}
				?>
			>
			Получать рассылку о конференции
		</label>
		
		<input type="submit" name="submit" value="Зарегистрироваться">
		
	</form>
</body>