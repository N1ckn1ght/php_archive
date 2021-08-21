<?php
	require './resources/arrays.php';
	
	class Form {
		
		private $data;
		private $errors;
		private static $fields = ['name', 'surname', 'email', 'phone', 'subject', 'payment'];
		
		private $subjects = [];
		private $payments = [];
		
		public function __construct($data, $subjects, $payments) 
		{
			unset($data['submit']);
			
			$this->subjects = $subjects;
			$this->payments = $payments;
			$this->data = $data;
		}
		
		public function validate()
		{
			foreach (self::$fields as $field)
			{
				if (!array_key_exists($field, $this->data))
				{
					trigger_error("Поле '$field' не заполнено!");
					return;
				}
			}
			
			$this->validate_field_name();
			$this->validate_field_surname();
			$this->validate_field_email();
			$this->validate_field_phone();
			
			$this->validate_field_subject();
			$this->validate_field_payment();
			$this->validate_field_spam();
			
			return $this->errors;
		}
		
		public function save()
		{
			$config = parse_ini_file('./config.ini.php');
			
			$server = $config['db_server'];
			$user = $config['db_username'];
			$pass = $config['db_password'];
			$dbname = $config['db_name'];
			
			try
			{
				$conn = new PDO("mysql:host=$server", $user, $pass);
				$conn->exec('SET NAMES "utf8";');
				
				$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
				$conn->query("use $dbname");
				
				$conn->exec('
					CREATE TABLE IF NOT EXISTS `participants`(
					`id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
					`name` VARCHAR(127) NOT NULL,
					`surname` VARCHAR(127) NOT NULL,
					`email` VARCHAR(127) NOT NULL,
					`phone` VARCHAR(23) NOT NULL,
					`subject` INT(10) NOT NULL,
					`payment` INT(10) NOT NULL,
					`spam` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
					`deleted_at` TIMESTAMP NULL DEFAULT NULL,
					`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
				)');
				
				$sql = $conn->prepare('INSERT INTO `participants` (`name`, `surname`, `email`, `phone`, `subject`, `payment`, `spam`)
					VALUES (:name, :surname, :email, :phone, :subject, :payment, :spam)');
				
				$subject = array_search($this->data['subject'], $this->subjects);
				$payment = array_search($this->data['payment'], $this->payments);
				
				$sql->execute([
					':name' => $this->data['name'],
					':surname' => $this->data['surname'],
					':email' => $this->data['email'],
					':phone' => $this->data['phone'],
					':subject' => $subject,
					':payment' => $payment,
					':spam' => $this->data['spam']
				]);
			}
			catch (PDOException $e)
			{
				echo $e->getMessage();
			}
			
			$conn = null;
			return;
		}
		
		public function get_data()
		{
			return $this->data;
		}
		
		private function validate_field_name()
		{
			$name = trim($this->data['name']);
			if (strlen($name) === 0)
			{
				$this->push_error('name', 'Пожалуйста, укажите ваше имя!');
			}
			elseif (strlen($name) > 127)
			{
				$this->push_error('name', 'Имя слишком длинное.');
			}
			elseif (!preg_match('/^[a-zA-ZабвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ\- ]*$/', $name))
			{
				$this->push_error('name', 'В имени могут содержаться только буквы русского и английского алфавита, дефис и пробел.');
			}
			
			return;	
		}
		
		private function validate_field_surname()
		{
			$surname = trim($this->data['surname']);
			if (strlen($surname) === 0)
			{
				$this->push_error('surname', 'Пожалуйста, укажите вашу фамилию!');
			}
			elseif (strlen($surname) > 127)
			{
				$this->push_error('surname', 'Фамилия слишком длинная.');
			}
			elseif (!preg_match('/^[a-zA-ZабвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ\- ]*$/', $surname))
			{
				$this->push_error('surname', 'В фамилии могут содержаться только буквы русского и английского алфавита, дефис и пробел.');
			}
			
			return;	
		}
		
		private function validate_field_email()
		{
			$email = trim($this->data['email']);
			if (strlen($email) === 0)
			{
				$this->push_error('email', 'Пожалуйста, укажите вашу электронную почту!');
			}
			elseif (strlen($email) > 127)
			{
				$this->push_error('email', 'Электронная почта слишком длинная.');
			}
			elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$this->push_error('email', 'Пожалуйста, укажите корректный адрес электронной почты.');
			}
			
			return;	
		}
		
		private function validate_field_phone()
		{
			$phone = trim($this->data['phone']);
			if (strlen($phone) === 0)
			{
				$this->push_error('phone', 'Пожалуйста, укажите ваш номер телефона!');
			}
			elseif (strlen($phone) > 23)
			{
				$this->push_error('phone', 'Номер телефона слишком длинный.');
			}
			elseif (!preg_match('/^[0-9x\+]*$/', $phone))
			{
				$this->push_error('phone', 'Пожалуйста, укажите настоящий номер телефона!');
			}
			
			return;	
		}
		
		private function validate_field_subject()
		{
			$subject = $this->data['subject'];
			if (!in_array($subject, $this->subjects))
			{
				$this->push_error('subject', 'Произошла ошибка, указанной темы конференции не существует.');
			}
			
			return;
		}
		
		private function validate_field_payment()
		{
			$payment = $this->data['payment'];
			if (!in_array($payment, $this->payments))
			{
				$this->push_error('payment', 'Произошла ошибка, указанного способа оплаты не существует.');
			}
			
			return;
		}
		
		private function validate_field_spam()
		{
			if (array_key_exists('spam', $this->data))
			{
				$this->data['spam'] = 1;
			}
			else
			{
				$this->data['spam'] = 0;
			}
			
			return;
		}
		
		private function push_error($key, $value)
		{
			$this->errors[$key] = $value;
			
			return;
		}
	}
?>