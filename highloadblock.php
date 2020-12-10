<?
  // Подключаем модуль
  \Bitrix\Main\Loader::includeModule('highloadblock');
  // Получаем информацию о Highload блоке
  $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
  // Инициализируем класс сущности
  $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData);
  // Получаем имя таблицы
  $dataClass = $hlEntity->getDataClass();

  // Работаем с методами таблицы
  $rsData = $dataClass::getList(array(
      'select' => array('ID', 'UF_NAME'),
      'order' => array('ID' => 'ASC'),
      'limit' => '50',
  ));
  while ($arItem = $rsData->Fetch())
  {
    pre($arItem);
  }

?>
