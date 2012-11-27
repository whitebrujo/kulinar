<?php

#
#	класс `Config`
#
#	конфигурация
#
#	версия: 2.0 29/09/2011
#

define('CONFIG', 1);

class Config	{

	protected $file, $data;

	# конструктор
	public function __construct($FileName)	{

		$this->file = $FileName;

	}

	# загрузка файла конфигурации
	public function Load($FileName = '')	{

		if($FileName === '' && $this->file === '') return false;
		elseif($FileName !== '') $this->file = $FileName;

		try {
			$this->data = parse_ini_file($this->file);
		}

		catch(Exception $ex)	{ return false; }

		# OK !
		return true;

	}

	# сохранить конфигурацию
	public function Save($FileName = '')	{

		if($FileName === '' && $this->file === '') return false;
		elseif($FileName !== '') $this->file = $FileName;

		$txt = '';
		foreach($this->data as $key => $val)	{
			if(is_string($val)) $val = "\"$val\"";
			$txt .=	"$key=$val\r\n";
		}

		try	{
			file_put_contents($this->file, $txt);
		}

		catch(Exception $ex)	{ return false; }

		# OK !
		return true;
	}

	# получение значения переменной
	public function __get($Var)	{

		if(array_key_exists($Var, $this->data)) return  $this->data[$Var];
		else return null;

	}

	# установка значения переменной
	public function __set($Var, $Val)	{

		$this->data[$Var] = $Val;

	}

}

?>