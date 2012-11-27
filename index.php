<?php

@session_start();
//@srand(time());

require_once('./modules/Config.lib.php');
require_once('./modules/Template.lib.php');
require_once('./modules/Data.lib.php');
require_once('./modules/Generate.lib.php');

define('CONF_FILE', 'site.config');
define('MAIN_TEMP', './templates/master.htm');
define('START_TEMP', './templates/start.htm');

define('RECIPES_FILE', './data/recipes.data');

// обработка параметров
$act = 'START';

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0) {	// POST

	if(isset($_POST['addrec_title']))	{	// передана форма добавления рецепта
	
		$act = 'ADDRESULT';
	
	} else if(isset($_POST['search_text']))	{	// передана форма поиска
	
		$act = 'SEARCHRESULT';
	
	} else $act = 'START';


} else	{	// GET


	if(isset($_GET['a']))	{
		switch($_GET['a'])	{
			case 'lc':
				$act = 'CUISINE';
				break;
			case 'lg':
				$act = 'GROUP';
				break;
			case 'lr':
				$act = 'RECIPE';
				break;
			case 'ad':
				$act = 'ADMIN';
				break;
			case 'gl':
				$act = 'GLOSS';
				break;
            case 'nu':
                $act = 'NUTR';
                break;
            case 'vi':
                $act = 'VIT';
                break;
            case 'om':
                $act = 'MEASURE';
                break;
            case 'ar':
                $act = 'ARTICLES';
                break;
		}

	} else $act = 'START';
}

$conf = new Config(CONF_FILE);
$conf->Load();

$temp = new Template();
$temp->Html = GenerateMaster();

// интерпретация параметра
switch($act)	{
	case 'START': default:
		$cont = GenerateStart();
		break;
	case 'GROUP':
		$cont = GenerateGroupList($_GET['p']);
		break;
	case 'CUISINE':
		$cont = GenerateCuisineList($_GET['p']);
		break;
	case 'RECIPE':
		$cont = GenerateRecipe($_GET['g'], $_GET['c'], $_GET['t']);
		break;
	case 'ADMIN':
		$cont = GenerateAddRec($_GET['p']);
		break;
	case 'ADDRESULT':
		$cont = GenerateAddRecResult($_POST);
		break;
	case 'SEARCHRESULT':
		break;
	case 'GLOSS':
		$cont = GenerateDictionary(isset($_GET['p']) ? intval($_GET['p']) : 1);
		break;
    case 'NUTR':
        $cont = GenerateNutrition();
        break;
    case 'VIT':
        $cont = GenerateVitamins();
        break;
    case 'MEASURE':
        $cont = GenerateMeasures();
        break;
    case 'ARTICLES':
        $cont = GenerateArticles(isset($_GET['art']) ? $_GET['art'] : false);
        break;
}

// подстановка переменных
$temp->Subst('content', $cont);
$temp->Subst('home', $conf->home);
$temp->Subst('ads', file_get_contents($conf->ads));
$temp->Subst('ads2', file_get_contents($conf->ads2));
$temp->Subst('ads3', file_get_contents($conf->ads3));
$temp->Subst('ads6', file_get_contents($conf->ads6));
$temp->Subst('ads7', file_get_contents($conf->ads7));
$temp->Subst('ads8', file_get_contents($conf->ads8));

$temp->Subst('counters', file_get_contents($conf->counters));


// вывод
echo $temp->Html;

?>