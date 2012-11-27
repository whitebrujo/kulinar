<?php

#
#	Модель данных для `kilunar3`
#
#

class Recipe	{

	public $Group, $Cuisine, $Title, $Text, $Legend, $Diet, $Fast, $Veg, $Kosher, $Date;
    
    private function ToBool($str)   {
        return strcmp(trim($str), '1') == 0;
    }

	public function __construct($Group, $Cuisine, $Title, $Text, $Legend, $Diet = false, $Fast = false, $Veg = false, $Kosher = false, $Date = false)	{

		$this->Group = $Group;
		$this->Cuisine = $Cuisine;
		$this->Title = $Title;
		$this->Text = $Text;
		$this->Legend = $Legend;
		$this->Diet = is_bool($Diet) ? $Diet : $this->ToBool($Diet);
		$this->Fast = is_bool($Fast) ? $Fast : $this->ToBool($Fast);
		$this->Veg = is_bool($Veg) ? $Veg : $this->ToBool($Veg);
		$this->Kosher = is_bool($Kosher) ? $Kosher : $this->ToBool($Kosher);
        $this->Date = ($Date === false) ? date("j/n/Y") : $Date;
        
		$this->Data = $Date;

	}
    
    
}	// /class Recipe...

class Recipes	{

	protected $file;
	public $Data = array();

	public $Groups = array(), $Cuisines = array();

	public function __construct($FileName)	{

		$this->file = $FileName;

	}
    
    private function ToBool($str)   {
        return strcmp(trim($str), '1') == 0;
    }

	// загрузка и синтаксический разбор файла
	public function Parse()	{

		$fp = fopen($this->file, 'r');
		$recs = intval(fgets($fp));	// кол-во записей

		for($i = 0; $i < $recs; $i++)	{
			$group = trim(fgets($fp));
			$cuisine = trim(fgets($fp));
			$title = trim(fgets($fp));
			$text  = trim(fgets($fp));
			$legend = trim(fgets($fp));
			$diet = $this->ToBool(fgets($fp));
			$fast = $this->ToBool(fgets($fp));
			$veg = $this->ToBool(fgets($fp));
			$kosher = $this->ToBool(fgets($fp));
			$pdate = trim(fgets($fp));

            if($group != '')
                array_push($this->Data, new Recipe($group, $cuisine, $title, $text, $legend, $diet, $fast, $veg, $kosher, $pdate));

			array_push($this->Groups, $group);
			array_push($this->Cuisines, $cuisine);
			
		}
        
        $this->Groups = array_unique($this->Groups);
        $this->Cuisines = array_unique($this->Cuisines);
        
		fclose($fp);

		sort($this->Groups);
		sort($this->Cuisines);

	}	// /Parse()
    
    // статистика по группе (кол-во рецептов)
    public function GroupStat($gr)   {
    
        $res = 0;
        foreach($this->Data as $rec) if(!strcasecmp($rec->Group, $gr)) $res++;
        
        return $res;
    }
    
    // статистика по кухне (кол-во рецептов)
    public function CuisineStat($gr)   {
    
        $res = 0;
        foreach($this->Data as $rec) if(!strcasecmp($rec->Cuisine, $gr)) $res++;
        
        return $res;  
    }
    
    // получить последние $num рецептов
    public function Last($num)  {
    
        if($num > $this->Size) return false;
        
        return array_slice($this->Data, $this->Size - $num, $num, true);
    
    }   // /Last()
	
	// получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()
    
    private function StripCrLf($str)    {
    
        return str_replace("\r\n", "<br />", trim($str));
    
    }
	
	// сохранить данный обратно в файл
	private function Save()	{
	
		if(sizeof($this->Data) == 0) return false;
		
		if(($fp = @fopen($this->file, 'w')) === false) return false;
		
		$txt = strval(sizeof($this->Data)) . "\r\n";	// кол-во записей
		
		foreach($this->Data as $rec)	{
		
			$txt .= $rec->Group . "\r\n";
			$txt .= $rec->Cuisine . "\r\n";
			$txt .= $rec->Title . "\r\n";
			
			$txt .= $this->StripCrLf($rec->Text) . "\r\n";
			$txt .= $this->StripCrLf($rec->Legend) . "\r\n";
			
			$txt .= ($rec->Diet ? "1" : "0") . "\r\n";
			$txt .= ($rec->Fast ? "1" : "0") . "\r\n";
			$txt .= ($rec->Veg ? "1" : "0") . "\r\n";
			$txt .= ($rec->Kosher ? "1" : "0") . "\r\n";
			
			$txt .= $rec->Date . "\r\n";
		}
		
		if(@fwrite($fp, $txt) === false) {
			@fclose($fp);
			return false;
		}
		
		// OK !
		@fclose($fp);
		return true;
	
	}	//  /Save()
	
	// добавление рецепта
	public function Append(Recipe $NewRecipe)	{
	
		array_push($this->Data, $NewRecipe);
		
		// OK !
		return $this->Save();
	
	}	// / Append()

}	// /class Recipes...

class News	{

	private $file;

	public $Data = array();

	public function __construct($FileName)	{

		$this->file = $FileName;

	}

	public function Parse()	{

		$fp = fopen($this->file, 'r');

		while(!feof($fp))	{
        
			$td = trim(fgets($fp));
			$tn = trim(fgets($fp));

            if($td != '')
                array_push($this->Data, array('date' => $td, 'text' => $tn));
		}

		// OK !
		@fclose($fp);

	}	// /Parse()
	
	// получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()

}	//	/class News...

class Facts	{

	private $file;

	public $Data = array();

	public function __construct($FileName)	{

		$this->file = $FileName;

	}

	public function Parse()	{

		$fp = fopen($this->file, 'r');
		fgets($fp);	// пропустить число записей

		while(!feof($fp))	{
        
			$tn = trim(fgets($fp));

            if($tn != '')
                array_push($this->Data, $tn);
		}

		// OK !
		@fclose($fp);

	}	// /Parse()
	
	// получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()

}	//	/class Facts...

class Advices	{

	private $file;

	public $Data = array();

	public function __construct($FileName)	{

		$this->file = $FileName;

	}

	public function Parse()	{

		$fp = fopen($this->file, 'r');
		fgets($fp);	// пропустить число записей

		while(!feof($fp))	{
        
			$tn = trim(fgets($fp));
            if($tn != '')
                array_push($this->Data, $tn);
		}

		// OK !
		@fclose($fp);

	}	// /Parse()
	
	// получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()

}	//	/class Advices...


class Dictionary	{

	private $file = '';
	
	public $Data = array();
	
	public function __construct($FileName)	{
	
		$this->file = $FileName;
	
	}
	
	# загрузка и разбивка файла словаря
	public function Parse()	{
	
		$fp = fopen($this->file, 'r');
		fgets($fp);	// пропустить число записей

		while(!feof($fp))	{
        
			$tn = trim(fgets($fp));
            if($tn === '') break;
			$tt = trim(fgets($fp));

			$this->Data[] = array('word' => $tn, 'text' => $tt);
		}

		// OK !
		@fclose($fp);

	
	}	//	/Parse()
	
	// получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()

}	//	/class Dictionary...

// Данные пищевой ценности
class Nutrition {

    private $file = '';
	
	public $Data = array();
	
	public function __construct($FileName)	{
	
		$this->file = $FileName;
	
	}
	
	# загрузка и разбивка файла данных
	public function Parse()	{
	
		$fp = fopen($this->file, 'r');
		fgets($fp);	// пропустить число записей

		while(!feof($fp))	{
            $grp=trim(fgets($fp));	# группа
            if($grp == '') break;
            $prd=trim(fgets($fp));	# продукт
            $prt=trim(fgets($fp));	# белки
            $fat=trim(fgets($fp));	# жиры
            $car=trim(fgets($fp));	# углеводы
            $enr=trim(fgets($fp));	# калории
            
            $this->Data[] = array('group' => $grp, 'product' => $prd, 'proteins' => $prt,
									'fat' => $fat, 'carbohydrates' => $car, 'energy' => $enr);
        }
        
        // OK !
		@fclose($fp);

	}	//	/Parse()
    
    // получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()

}   // class Nutrition...

// Данные витаминов
class Vitamins {

    private $file = '';
	
	public $Data = array();
	
	public function __construct($FileName)	{
	
		$this->file = $FileName;
	
	}
	
	# загрузка и разбивка файла данных
	public function Parse()	{
	
		$fp = fopen($this->file, 'r');
		fgets($fp);	// пропустить число записей

		while(!feof($fp))	{
            $ws=trim(fgets($fp));	# водорастворимость
            if($ws == '') break;
            $name=trim(fgets($fp));	# название
            $title=trim(fgets($fp));	# титул
            $func=trim(fgets($fp));		# действие
            $source=trim(fgets($fp));	# источники
            $dose=trim(fgets($fp));		# дозировка
            $avit=trim(fgets($fp));		# симтомы авитаминоза
            $hyper=trim(fgets($fp));	# симтомы гипертаминоза
            
            $this->Data[] = array('name' => $name, 'title' => $title,
									'function' => $func, 'source' => $source, 'dose' => $dose,
									'avit' => $avit, 'hyper' => $hyper
									);
        }
        
        // OK !
		@fclose($fp);

	}	//	/Parse()
    
    // получение свойств
	public function __get($prop)	{
	
		switch($prop)	{
			case 'Size': case 'Length': case 'Count':
				return sizeof($this->Data);
			default:
				return false;
		}
	
	}	// /__get()

}   // class Vitamins...

class Rating    {

    private $recs, $rfile;

    public function __constuct(Recipes $Recs, string $RFile) {
    
        $this->recs = $Recs; $this->rfile = $RFile;
    }

}   // /class Rating...



?>