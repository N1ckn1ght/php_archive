<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Классы и объекты</title>
</head>
<body>
	<?php
		class Number
		{
			protected $n;
			protected $smth = 0;
			private $data = 'this is a private data';
			
			public function __construct($value)
			{
				$this->n = $value;
			}
			
			public function get_value()
			{
				return $this->n;
			}
			
			public function next_generation()
			{
				if ($this->n % 2)
				{
					$this->n = $this->up($this->n);
				}
				else
				{
					$this->n = $this->down($this->n);
				}
				
				return;
			}
			
			public function new_data($data)
			{
				$this->data = $data;
				
				return;
			}
			
			public function get_data()
			{
				return $this->data;
			}
			
			public function get_smth()
			{
				return $this->smth;
			}
			
			public function private_test()
			{
				return $this->test();
			}
			
			protected function up($n)
			{
				return $n * 3 + 1;
			}
			
			protected function down($n)
			{
				return $n / 2;
			}
			
			private function test()
			{
				return 'A';
			}
		}
		
		class AdditionalNumber extends Number
		{
			protected $smth = 1;
			private $data = 'this is an alternative private data';
			
			public function add_digit()
			{
				$this->n *= 10;
				
				return;
			}
			
			private function test()
			{
				return 'B';
			}
		}
		
		$num = new Number(13);
		echo $num->get_value().'</br>';
		
		$num->next_generation();
		echo $num->get_value().'</br>';
		$num->next_generation();
		echo $num->get_value().'</br>';
		
		echo $num->get_data().'</br>';
		$num->new_data('this is a new private data');
		echo $num->get_data().'</br>';
		
		echo $num->get_smth().'</br></br>';
		
		
		$anum = new AdditionalNumber(7);
		echo $anum->get_value().'</br>';
		
		$anum->next_generation();
		echo $anum->get_value().'</br>';
		
		$anum->add_digit();
		echo $anum->get_value().'</br>';
		
		echo $anum->get_data().'</br>';
		$anum->new_data('this is a new private data');
		echo $anum->get_data().'</br>';
		
		echo $anum->get_smth().'</br></br>';
		
		
		echo $num->private_test().'</br>';
		echo $anum->private_test().'</br></br>';
	?>
</body>