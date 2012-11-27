<?php

require_once('Template.lib.php');
require_once('Data.lib.php');

define('ITEMS_PER_PAGE', 8);

// генерация мастер-страницы
function GenerateMaster()	{

	$temp = new Template('./templates/master.htm');
	$temp->Load();
	$html = $temp->Html;

	$recs = new Recipes('./data/recipes.data');
	$recs->Parse();

	// >>>>> группы
 	$tmp = '<ul>'; 
 	foreach($recs->Groups as $g)	{
        $tcount = $recs->GroupStat($g);
 		$tmp .= "<li>&rarr;&nbsp;<a href=\"##home##?a=lg&p=" . urlencode($g) . "\" title=\"$g\">" . $g . "</a> ($tcount)</li>";
 	}

 	$tmp .= "</ul>";

 	$html = str_replace('##groups##', $tmp, $html);

 	// >>>>>> категории
 	$tmp = '<ul>';
 	foreach($recs->Cuisines as $g)	{
        $tcount = $recs->CuisineStat($g);
 		$tmp .= "<li>&rarr;&nbsp;<a href=\"##home##?a=lc&p=" . urlencode($g) . "\" title=\"$g\">" . $g . " кухня</a> ($tcount)</li>";
 	}

 	$tmp .= "</ul>";

 	$html = str_replace('##cuisines##', $tmp, $html);

 	// >>>>>>> случайный рецепт
  	$rind = rand(0, sizeof($recs->Data) - 1);
  	$rrec = $recs->Data[$rind];

  	$html = str_replace('##random_title##', $rrec->Title, $html);
  	$html = str_replace('##random_legend##', $rrec->Legend, $html);
  	$html = str_replace('##random_text##', $rrec->Text, $html);

  	// >>>>>>>>>>>> интересный факт
  	$facts = new Facts('./data/facts.data');
  	$facts->Parse();

  	$rfi = rand(0, sizeof($facts->Data) - 1);
  	$rft = $facts->Data[$rfi];

  	$html = str_replace('##fact##', $rft, $html);

	// >>>>>>>>>>>> совет
  	$adv = new Facts('./data/advices.data');
  	$adv->Parse();

  	$rfi = rand(0, sizeof($adv->Data) - 1);
  	$rft = $adv->Data[$rfi];

  	$html = str_replace('##advice##', $rft, $html);

    // >>>>>>>>>>>>> последние добавленные рецепты
    $html = str_replace('##last##', GenerateLastAdded($recs), $html);

	// OK !
	return $html;

}	// /GenerateStart()

// генерация стартовой страницы
function GenerateStart()	{

	$temp = new Template('./templates/start.htm');
	$temp->Load();
	$html = $temp->Body;

	// >>>>>>>>> новости
  	$news = new News('./data/news.data');
  	$news->Parse();

	$tmp = '<table style=\"width: auto;\">';
  	for($i = 0; $i < $news->Size; $i++)	{

		$tmp .= "<tr><td class=\"date\">" . $news->Data[$i]["date"] . 
                ":</td><td class=\"news_text\" style=\"text-align: left; width: auto;\">" .
				$news->Data[$i]["text"] . "</td></tr>";

  	}
  	$tmp .= '</table>';

  	$html = str_replace('##news##', $tmp, $html);

  	// OK !
  	return $html;
}

// генерация страницы группы
function GenerateGroupList($group)	{

	$html = "<h3>$group</h3>";

	$recs = new Recipes('./data/recipes.data');
	$recs->Parse();

	$html .= "<table width=\"100%\" border=\"0px\"><tr>";

	$l1 = '<ul>'; $l2 = '<ul>'; $i = 1;

	foreach($recs->Data as $rec)	{
		if($rec->Group == $group)	{
			$tt = "<li>&rarr;&nbsp;<a href=\"##home##?a=lr&g=" . urlencode($rec->Group) . "&c=" . urlencode($rec->Cuisine) . 
                    "&t=" . urlencode($rec->Title) . "\">" .
					$rec->Title . "</a></li>";

			if($i % 2 == 0) $l2 .= $tt; else $l1 .= $tt;
			$i++;
		}
	}

	$l1 .= '</ul>'; $l2 .= '</ul>';

	$html .= "<td>$l1</td><td>$l2</td></tr></table>";
    
    $i--;   // !!!
    $html .= "<div class=\"fact\" style=\"text-align: right;\">рецептов: $i</div>";

	// OK !
	return $html;

}	//  /GenerateGroupList()

// генерация страницы кухни
function GenerateCuisineList($cuisine)	{

	$recs = new Recipes('./data/recipes.data');
	$recs->Parse();
    
    $html = "<h3>$cuisine кухня</h3>";

	$html .= "<table width=\"100%\" border=\"0px\"><tr>";

	$l1 = '<ul>'; $l2 = '<ul>'; $i = 1;

	foreach($recs->Data as $rec)	{
		if($rec->Cuisine == $cuisine)	{
			$tt = "<li>&rarr;&nbsp;<a href=\"##home##?a=lr&g=" . urlencode($rec->Group) . "&c=" . urlencode($rec->Cuisine) . "&t=" . urlencode($rec->Title) . "\">" .
					$rec->Title . "</a></li>\r\n";

			if($i % 2 == 0) $l2 .= $tt; else $l1 .= $tt;
			$i++;
		}
	}

	$l1 .= '</ul>'; $l2 .= '</ul>';

	$html .= "<td>$l1</td><td>$l2</td></tr></table>";

    $i--;   // !!!
    $html .= "<div class=\"fact\" style=\"text-align: right;\">рецептов: $i</div>";

	// OK !
	return $html;

}	//  /GenerateCuisineList()

// генерация страницы рецепта
function GenerateRecipe($group, $cuisine, $title)	{

    //$group = urldecode($group);
    //$cuisine = urldecode($cuisine);
    $title = str_replace("\\", "", $title);
    
	$recs = new Recipes('./data/recipes.data');
	$recs->Parse();

	$nr = false;

	foreach($recs->Data as $rec)	{

		if(strcmp($rec->Group, $group) == 0 && strcmp($rec->Cuisine, $cuisine) == 0 && strcmp($rec->Title, $title) == 0)	{
			$nr = $rec;
			break;
		}
	}

	if($nr === false) return GenerateStart();	// не нашли

	$temp = new Template('./templates/recipe.htm');
	$temp->Load();

	$temp->Subst('title', $nr->Title . " (" . $nr->Group . "; " . $nr->Cuisine . " кухня)");
	$temp->Subst('recipe_legend', $nr->Legend);
	$temp->Subst('recipe_text', $nr->Text);

	// OK !
	return $temp->Body;

}	//  /GenerateRecipe()

// генерация страницы добавления рецепта
function GenerateAddRec($pass)	{

	if($pass !== 'tecciztecatl') return GenerateStart();

	$temp = new Template('./templates/addrec.htm');
	$temp->Load();

	return $temp->Body;

}	// /GenerateAddRec()

// нормализовать POST переменную для элемента checkbox
function NormalizeCheckboxVar($postvars, $varkey) {

    if(!isset($postvars[$varkey])) return '0'; else { if(strcasecmp($postvars[$varkey], 'on') == 0) return '1'; else return '0'; }
}

// генерация страницы результата добавления рецепта
function GenerateAddRecResult($postvars)	{

	$html = '';
    
    // нормализация
    if(trim($postvars['addrec_group']) == '' || trim($postvars['addrec_cuisine']) == '' || trim($postvars['addrec_title']) == '' || 
            trim($postvars['addrec_text']) == '') return GenerateStart();
            
            
    // добавление
	$rec = new Recipe(trim($postvars['addrec_group']), trim($postvars['addrec_cuisine']), trim($postvars['addrec_title']), 
                        trim($postvars['addrec_text']), trim($postvars['addrec_legend']),
                        NormalizeCheckboxVar($postvars, 'addrec_diet'), 
                        NormalizeCheckboxVar($postvars, 'addrec_fast'),
                        NormalizeCheckboxVar($postvars, 'addrec_veg'), 
                        NormalizeCheckboxVar($postvars, 'addrec_kosh'));
                        
   
	$recs = new Recipes('./data/recipes.data');
	$recs->Parse();

	if($recs->Append($rec))
		$html = '<h2>Рецепт добавлен. Рецептов в базе: ' . $recs->Size . '</h2>';
	else
		$html = '<h2>Ошибка добавления !!!</h2>';


	// OK !
	return $html;

}	//  /GenerateAddRecResult()

// генерация страницы советов
function GenerateAdvices()	{

}	// 	/GenerateAddRecResult()

// генерация страницы словаря
function GenerateDictionary($page)	{

	$dict = new Dictionary('./data/glossary.data');
	$dict->Parse();
    
    $temp = new Template('./templates/gloss.htm');
	$temp->Load();
    
    $dhtml = '';    // код для ##dict##
    $phtml = '';    // код для ##pager##
    
    // генерим пункты словаря для страницы
    $starti = ($page - 1) * ITEMS_PER_PAGE;
    $endi = ($starti + ITEMS_PER_PAGE >= $dict->Size) ? $dict->Size - 1 : $starti + ITEMS_PER_PAGE - 1;
    
    $dhtml .= "<ul>";
    
    for($i = $starti; $i <= $endi; $i++)    {
        $dhtml .= "<li style=\"padding-bottom: 7px;\"><span style=\"color: Green; font-weight: bold;\">" . 
                $dict->Data[$i]["word"] . "</span>: " . $dict->Data[$i]["text"] . "</li>\r\n";
    }
    
    $dhtml .= "</ul>";
    
    // генерим пейджер
    $phtml .= GeneratePager($dict->Size, $page, ITEMS_PER_PAGE, "##home##?a=gl");

	// OK !
    $temp->Subst('dict', $dhtml);
    $temp->Subst('pager', $phtml);
	return $temp->Body;

}	// 	GenerateDictionary()

// генерация страницы пищевой ценности
function GenerateNutrition()    {

    $nutr = new Nutrition('./data/nutrition.data');
	$nutr->Parse();
    
    $temp = new Template('./templates/nutr.htm');
	$temp->Load();
    
    $html = '';
    
    for($i = 0; $i < $nutr->Size; $i++) {
    
        $html .= "<tr>";
        $html .= "<td class=\"table_body\" style=\"text-align: left;\">" . $nutr->Data[$i]["product"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: center;\">" . $nutr->Data[$i]["proteins"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: center;\">>" . $nutr->Data[$i]["fat"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: center;\">>" . $nutr->Data[$i]["carbohydrates"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: center;\">>" . $nutr->Data[$i]["energy"] . "</td>";
        $html .= "</tr>";
    
    }
    
    // OK !
    $temp->Subst('nutr', $html);
	return $temp->Body;

}   // GenerateNutrition()

// генерация страницы витаминов
function GenerateVitamins() {

    $vit = new Vitamins('./data/vitamins.data');
	$vit->Parse();
    
    $temp = new Template('./templates/vitamins.htm');
	$temp->Load();
    
    $html = '';
    
    for($i = 0; $i < $vit->Size; $i++) {
    
        $html .= "<tr>";
        $html .= "<td class=\"table_body\" style=\"text-align: center;\"><b>" . $vit->Data[$i]["name"] . "</b></td>";
        $html .= "<td class=\"table_body\" style=\"text-align: left; vertical-align: top;\">" . $vit->Data[$i]["function"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: left; vertical-align: top;\">" . $vit->Data[$i]["source"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: center; verticat-align: middle;\">" . $vit->Data[$i]["dose"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: left; vertical-align: top;\">" . $vit->Data[$i]["avit"] . "</td>";
        $html .= "<td class=\"table_body\" style=\"text-align: left; vertical-align: top;\">" . $vit->Data[$i]["hyper"] . "</td>";
        $html .= "</tr>";
        
    }
    
    // OK !
    $temp->Subst('vit', $html);
	return $temp->Body;

}   // GenerateVitamins()

// генерация пейджера
function GeneratePager($set_size, $currpage, $items_per_page, $link)	{
	$html = '<table><tr>';

	$total_items = $set_size;
	$total_pages = $total_items % $items_per_page == 0 ? intval($total_items / $items_per_page) : intval($total_items / $items_per_page) + 1;
    
    for($i = 1; $i <= $total_pages; $i++)  {
    
        $clink = $link . "&p=$i";
    
        if($i == $currpage)
            $html .= "<td><a href=\"$clink\" style=\"color: Red;\">&nbsp;$i&nbsp;</a></td>";
        else
            $html .= "<td><a href=\"$clink\">&nbsp;$i&nbsp;</a></td>";
    
    }

    $html .= '</tr></table>';

	// OK !
	return $html;

}	// /GeneratePager()

// генерация страницы старых мер
function GenerateMeasures() {

    $temp = new Template('./templates/measures.htm');
	$temp->Load();
    
    return $temp->Body;
    
}   // /GenerateMeasures()

// генерация страницы статей
function GenerateArticles($afile) {

    if($afile === false) $afile = './templates/articles.htm'; 
    else $afile = 'templates/articles/' . $afile;
     
    if(!file_exists($afile)) return GenerateStart();
     
    $temp = new Template($afile);
	$temp->Load();
    
    return $temp->Body;

}   // /GenerateArticles()

// генерация тайла последних добавленных рецептов
function GenerateLastAdded($recs)    {

    $la = $recs->Last(5);
    
    $html = '<div><h2>Последнее</h2><ul>';
    
    foreach($la as $rec)  {
        $html .= "<li><a href=\"##home##?a=lr&g=" . urlencode($rec->Group) . "&c=" . urlencode($rec->Cuisine) . 
                    "&t=" . urlencode($rec->Title) . "\">" .
					$rec->Title . "</a></li>";
    }
    
    $html .= '</ul></div>';
    // OK !
    return $html;

}   //  /GenerateLastAdded()

?>