<?php

	class Form {
		
		private $data;
		private $errors;
		private static $fields = ['name', 'surname', 'email', 'phone', 'subject', 'payment'];
		
		public function __construct() {}
		
		public function read_post($data)
		{
			unset($data['submit']);
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
			
			return $this->errors;
		}
		
		public function save()
		{
			$date = date('ymdHis');
			$i = 1;
			
			if (!is_dir('./data'))
			{
				mkdir('./data', 0775, true);
			}
			else
			{
				while (is_file('./data/'.$date."_$i"))
				{
					$i++;
				}
			}
			
			$to_save = serialize($this->data);
			$path = './data/'.$date."_$i";
			file_put_contents($path, $to_save);

			return;
		}
		
		public function read_file($file)
		{
			$contents = file_get_contents($file);
			$contents = unserialize($contents);
			$this->data = $contents;
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
		
		private function push_error($key, $value)
		{
			$this->errors[$key] = $value;
			
			return;
		}
	}
?>