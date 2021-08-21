<?php
	function translate($phrase, $lang)
	{
		$dict = array(
			'en' => array(
				'hello' => 'Hello!',
				'open' => 'Open',
				'save' => 'Save',
				'close_the_window' => 'Close the window?'
			),
			'ru' => array(
				'hello' => 'Привет!',
				'open' => 'Открыть',
				'save' => 'Сохранить',
				'close_the_window' => 'Закрыть окно?'
			)
		);
		
		if (isset($dict[$lang]))
		{
			if (isset($dict[$lang][$phrase]))
			{
				return $dict[$lang][$phrase];
			}
			return 'Фраза "'.$phrase.'" не найдена в словаре языка "'.$lang.'"';
		}
		return 'Язык "'.$lang.'" не найден в словаре.';
	}
	
	if (isset($_POST['submit']))
	{
		echo translate($_POST['phrase'], $_POST['lang']);
	}
?>

<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Перевод</title>
</head>
<body>
	<form method="POST">
	
		<label>
			Фраза:
			<input required type="text" name="phrase" value="<?php echo htmlspecialchars($_POST['phrase'] ?? '') ?>">
		</label>
		
		<label>
			Язык:
			<input required type="text" name="lang" value="<?php echo htmlspecialchars($_POST['lang'] ?? '') ?>">
		</label>

		<input type="submit" name="submit" value="Перевести">
		
	</form>
</body>