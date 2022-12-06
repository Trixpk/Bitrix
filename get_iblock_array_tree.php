<?php
  require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
  Bitrix\Main\Loader::includeModule("iblock");

  // Инициализируем переменные для разделов и элементов
  $arResult['ROOT'] = [];
  $sectionLinc = [];
  $elements = [];
  $sectionLinc[0] = &$arResult['ROOT'];

  // Получаем разделы инфоблока
  $arFilter = [
      'ACTIVE' => 'Y',
      'IBLOCK_ID' => '5',
      'GLOBAL_ACTIVE'=>'Y'
  ];

  $arSelect = [
      'IBLOCK_ID',
      'ID',
      'NAME',
      'DEPTH_LEVEL',
      'IBLOCK_SECTION_ID'
  ];

  $arOrder = [
      'DEPTH_LEVEL'=>'ASC',
      'SORT'=>'ASC'
  ];

  $rsSections = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);

  while($arSection = $rsSections->GetNext()) {
      $sectionLinc[intval($arSection['IBLOCK_SECTION_ID'])]['SUB_SECTION'][$arSection['ID']] = $arSection;
      $sectionLinc[$arSection['ID']] = &$sectionLinc[intval($arSection['IBLOCK_SECTION_ID'])]['SUB_SECTION'][$arSection['ID']];
  }

  $rsElements = CIBlockElement::GetList(
      ['SORT' => 'ASC'],
      $arFilter,
      false,
      false,
      ['PREVIEW_PICTURE', 'PROPERTY_YOUTUBE', 'ID', 'IBLOCK_SECTION_ID']
  );

  while ($arElement = $rsElements->GetNext(true, false)) {
      $elements[] = $arElement;
  }

  foreach($sectionLinc[0]['SUB_SECTION'] as $key => $arSection) {
      foreach($arSection['SUB_SECTION'] as $subSection) {
          foreach($elements as $arElement) {
              if($subSection['ID'] == $arElement['IBLOCK_SECTION_ID']) {
                  $sectionLinc[0]['SUB_SECTION'][$key]['SUB_SECTION'][$subSection['ID']]['ELEMENTS'][] = $arElement;
              }
          }
      }
  }

  $arResult['GALLERY_TREE'] = $sectionLinc[0]['SUB_SECTION'];
  unset($sectionLinc);
  unset($elements);
