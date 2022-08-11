function getRates()
{
    global $APPLICATION;


// Инициализируем переменную для хранения текущего курса базовой валюты USD
    $usdAmount = 0;

// Получаем список доступных валют из Битрикс
    $arCurrencies = array();
    $by = 'name';
    $order = 'asc';
    $lcur = CCurrency::GetList($by, $order, LANGUAGE_ID);

    while ($lcurRes = $lcur->Fetch()) {
        // Заполняем массив доступных валют
        $arCurrencies[] = $lcurRes["CURRENCY"];
    }

// Получаем данные по валютам с сайта cbr.ru
    $ch = curl_init('https://www.cbr.ru/scripts/XML_daily.asp');

    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $xml = curl_exec($ch);
    curl_close($ch);

    $xmlResult = new SimpleXMLElement($xml);

// Преобразовываем объект Simplexml к массиву
    $xmlResult = xml2array($xmlResult);

// Добавляем в массив валюту RUB так как она изначально отсутствует на сайте cbr.ru
    $xmlResult['Valute'][] = (object)['CharCode' => 'RUB'];

// Получаем курс базовой валюты USD
// Относительно этого курса будем конвертировать остальные

    foreach ($xmlResult['Valute'] as $valute) {
        switch ($valute->CharCode) {
            case 'USD':
                $usdAmount = $valute->Value;
                break 2;
        }
    }

// Проходимся по всем полученным валютам с сайта cbr
// Если валюта есть в списке валют нашего сайта обрабатываем
    foreach ($xmlResult['Valute'] as $valute) {
        // Проверяем что бы валюта от cbr находилась в списке доступных нам валют 1С-Битрикс
        // Рубль обрабатываем отдельно так как от cbr RUB не приходит
        if (in_array($valute->CharCode, $arCurrencies)) {

            // Пропускаем доллар так как он является базовой валютой в системе
            if ($valute->CharCode == 'USD') {
                continue;
            }

            // Обновляем курс валюты
            $resCurUpdate = CCurrency::Update($valute->CharCode, array(
                'CURRENCY' => $valute->CharCode,
                'AMOUNT' => ($valute->CharCode == 'RUB') ? round(1 / $usdAmount, 3) : round($usdAmount / $valute->Value, 3)
            ));

            if ($resCurUpdate) {
                echo 'Валюта с кодом ' . $valute->CharCode . ' изменена<br>';
            }
            else {
                if ($ex = $APPLICATION->GetException()) {
                    echo $ex->GetString();
                }
            }

        }
    }

    return "getRates();";
}
