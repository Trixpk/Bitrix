<?php

// подключаем prolog bitrix 
require $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php';
// подключаем нужные модули
CModule::IncludeModule("iblock");
CModule::IncludeModule("sales");
 
$el = new CIBlockElement; // Создаем экземпляр класса CIBlockElement для работы с елементами инфоблока
 
$arSelect = array("IBLOCK_ID", "ID", "SORT", "PROPERTY_CML2_ARTICLE", "NAME"); // Перечисляем поля и свойства, которые попадут в выгрузку


function changeSort() // Функция для замены индекса сортировки
{
	global $el, $arSelect;
 
	$arFilter = array("IBLOCK_ID" => 32); // Выбираем инфоблок с ID = 32
	$res = $el->GetList(array(), $arFilter, false, false, $arSelect); // Получаем список элементов инфоблока
 
	while ($r = $res->GetNext()) {
		$article_start = substr($r['PROPERTY_CML2_ARTICLE_VALUE'], 0, 2); // записываем в переменную первые два символа Артикула элемента

		if($article_start == "AU") { // Если артикул начинается с "AU"
			$el->Update($r['ID'], array("SORT" => 1)); // Ставим индекс сортировки = 1
		}
	}
}
 
//changeSort();
