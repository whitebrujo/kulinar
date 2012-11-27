<?php

#
#	����� `Template`
#
#	������ � �������� ��������
#
#	������ 1.0, 29/07/2011
#

define('TEMPLATE', 1);

class Template	{

	protected $file, $text;

	const VarSpan = '##';

	# �����������
	public function __construct($FileName = '')	{

		$this->file = $FileName;

	}

	# ��������
	#
	public function Load()	{

		if($this->file == '') return false;
		if(($this->text = file_get_contents($this->file)) === false) return false;

		# OK !
		return true;
	}

	# ���������� ����������� ���� <body>
	private function ExtractBody()	{

		$body_pos = stripos($this->text, '<body>');
		if($body_pos == -1) return '';
        
        $body_pos += 6; // + ����� ������ ����

		$ebody_pos = strpos($this->text, '</body>');
		$ebody_pos = $ebody_pos == 0 ? strlen($this->text) - 1 : $ebody_pos;

		return substr($this->text, $body_pos, $ebody_pos - $body_pos);

	}

	# ������� ����������������
	#
	#	$Var	-	��� ����������������
	#	$Value	-	��������
	public function Subst($Var, $Value)	{

		return $this->text = str_replace(self::VarSpan . $Var . self::VarSpan, $Value, $this->text);
	}

	# ��������� �������
	public function __get($prop)	{

		switch($prop)	{
			case 'Text': case 'Html':
				return $this->text;
			case 'File': case 'FileName':
				return $this->file;
			case 'Body':
				return $this->ExtractBody();
			default:
				return false;
		}
	}

	# ��������� �������
	public function __set($prop, $val)	{

		switch($prop)	{
			case 'Text': case 'Html':
				$this->text = $val;
				return true;
			default:
				return false;
		}

	}
}

?>