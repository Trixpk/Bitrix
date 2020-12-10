<?

  \Bitrix\Main\Loader::includeModule('highloadblock');

  $hlData = \Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
  $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlData);
  $dataClass = $hlEntity->getDataClass();

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
