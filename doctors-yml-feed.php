<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

function makeObjData($name, $value, $params = [])
{
    return (object)["name" => $name, "value" => $value, "params" => $params];
}

function generateXML($arr, &$xml)
{
    foreach($arr as $item)
    {
        if(isset($item->value) && isset($item->name))
        {
            $value = $item->value;
            $name = $item->name;
            $child = null;

            if(is_array($value))
            {
                if(!empty($value))
                {
                    $child = $xml->addChild($name);
                    generateXML($value, $child);
                }
            }
            else
            {
                $value = trim($value);
                $child = $xml->addChild($name, htmlspecialchars($value));
            }

            if(!is_null($child) && isset($item->params))
            {
                $params = $item->params;
                if(!empty($params))
                {
                    foreach ($params as $name => $param)
                    {
                        $child->addAttribute($name, $param);
                    }
                }
            }
        }
    }
}

// Цены
$arPrice = [];
$result = CIBlockElement::GetList([], ["IBLOCK_ID" => 5], false, false, ["ID", "PROPERTY_COST"]);
while ($cost = $result->Fetch())
{
    if($cost["PROPERTY_COST_VALUE"])
    {
        $arPrice[$cost["ID"]] = $cost["PROPERTY_COST_VALUE"];
    }
}
$arUniqueSpecId = [];
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "CODE","DETAIL_PICTURE","PROPERTY_*");
$arFilter = Array("IBLOCK_ID" => 3, "ACTIVE"=>"Y");
$arOffers = [];
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement())
{
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();

    // Заполнить массив используемых специализаций
    if(!empty($arProps["SPECIALIZATION"]["VALUE_ENUM_ID"]))
    {
        foreach ($arProps["SPECIALIZATION"]["VALUE_ENUM_ID"] as $spec_ID)
        {
            if(!in_array($spec_ID, $arUniqueSpecId))
            {
                $arUniqueSpecId[] = $spec_ID;
            }
        }
    }

    // Поиск минимальной и максимальной цены
    $price_min = $price_max = null;
    foreach($arProps["PRICE"]["VALUE"] as $price_id)
    {
        if(($price_cur = $arPrice[$price_id]))
        {
            if(is_null($price_min) || ($price_cur < $price_min))
                $price_min = $price_cur;

            if(is_null($price_max) || ($price_cur > $price_max))
                $price_max = $price_cur;
        }
    }

    // Поиск отзывов
    $reviewsCount = 0;
    $reviewsScore = 0;
    $reviewsResult = CIBlockElement::GetList([], ['IBLOCK_ID' => 4, 'ACTIVE'=> 'Y', 'PROPERTY_DOCTORS_REVIEW'=>$arFields['ID']],
        false, false, ['NAME', 'PREVIEW_TEXT', 'ACTIVE_FROM', 'PROPERTY_RATING_REVIEWS']);
    while ($reviews = $reviewsResult->GetNext())
    {
        if(intval($reviews["PROPERTY_RATING_REVIEWS_VALUE"]))
        {
            $reviewsScore += $reviews["PROPERTY_RATING_REVIEWS_VALUE"];
            $reviewsCount++;
        }
    }

    if($reviewsCount > 0)
    {
        $reviewsScore = $reviewsScore / $reviewsCount;
    }
    else
    {
        $reviewsScore = 5;
    }

    $years = preg_replace('/[^0-9]/', '', $arProps["EXPERIENCE"]["VALUE"]);
    $picture = CFile::GetPath($arFields["DETAIL_PICTURE"]);
    $param_vzrosloe = in_array("Взрослое", $arProps["DIRECTION_OF_ACTIVITY"]["VALUE"]) ? "true":"false";
    $param_detskoe = in_array("Детское", $arProps["DIRECTION_OF_ACTIVITY"]["VALUE"]) ? "true":"false";

    if($arProps["DEPARTMENT"]["VALUE"])
    {
        foreach ($arProps["DEPARTMENT"]["VALUE"] as $key_depart => $department)
        {
            $vrachID = "vrach".$arFields["ID"].".".$key_depart;
            $arOfferData = [];
            $arOfferData[] = makeObjData("name", $arFields["NAME"]);
            $arOfferData[] = makeObjData("url", "https://klinika124.ru/doctors/".$arFields["CODE"]."/");
            $arOfferData[] = makeObjData("price", $price_min, ["from" => "true"]);
            $arOfferData[] = makeObjData("oldprice", $price_max);
            $arOfferData[] = makeObjData("currencyId", "RUR");
            if(!empty($arProps["SPECIALIZATION"]["VALUE_ENUM_ID"]))
            {
                $arOfferData[] = makeObjData("set-ids", implode(",", $arProps["SPECIALIZATION"]["VALUE_ENUM_ID"]));
            }
            $arOfferData[] = makeObjData("picture", "https://klinika124.ru".$picture);
            $arOfferData[] = makeObjData("description", $arProps["SPECIALIZACIYA"]["VALUE"]);
            $arOfferData[] = makeObjData("categoryId", "1");
            $arOfferData[] = makeObjData("param", $param_vzrosloe, ["name" => "Взрослый врач"]);
            $arOfferData[] = makeObjData("param", $param_detskoe, ["name" => "Детский врач"]);
            $arOfferData[] = makeObjData("param", intval($years), ["name" => "Годы опыта"]);
            $arOfferData[] = makeObjData("param", "Красноярск", ["name" => "Город"]);
            $arOfferData[] = makeObjData("param", $reviewsScore, ["name" => "Средняя оценка"]);
            $arOfferData[] = makeObjData("param", $reviewsCount, ["name" => "Число отзывов"]);
            $arOfferData[] = makeObjData("param", $department, ["name" => "Адрес клиники"]);
            $arOfferData[] = makeObjData("param", "МКТ, сеть клиник", ["name" => "Название клиники"]);
            $arOffers[] = makeObjData("offer", $arOfferData, ["id" => $vrachID, "group_id" => $arFields["ID"]]);
        }
    }
}
// Специализации
$property_enums = CIBlockPropertyEnum::GetList([], Array("IBLOCK_ID" => 3, "CODE" => "SPECIALIZATION"));
$arSpecs = [];
while($enum_fields = $property_enums->GetNext())
{
    if(in_array($enum_fields["ID"], $arUniqueSpecId))
    {
        $arSpecInfo = [];
        $arSpecInfo[] = makeObjData("name", $enum_fields["VALUE"]);
        $arSpecInfo[] = makeObjData("url", "https://klinika124.ru/doctors/?arrFilter_pf%5BSPECIALIZATION%5D%5B%5D=" . $enum_fields["ID"] . "&set_filter=%D0%A4%D0%B8%D0%BB%D1%8C%D1%82%D1%80&set_filter=Y");
        $arSpecs[] = makeObjData("set", $arSpecInfo, ["id" => $enum_fields["ID"]]);
    }
}

$arData = [];
$arData[] = makeObjData("name", "МКТ, сеть клиник");
$arData[] = makeObjData("url", "https://klinika124.ru/");
$arData[] = makeObjData("email", "klinika_mkt@mail.ru");
$arData[] = makeObjData("picture", "https://klinika124.ru/bitrix/templates/mkt/images/logo_header.png");
$arData[] = makeObjData("description", "Каталог врачей");
$arData[] = makeObjData("currencies", [makeObjData("currency", "", ["id" => "RUR", "rate" => "1"])]);
$arData[] = makeObjData("categories", [makeObjData("category", "Врач", ["id" => "1"])]);
$arData[] = makeObjData("sets", $arSpecs);
$arData[] = makeObjData("offers", $arOffers);

$arData = [makeObjData("shop", $arData)];

//pre($arData);


$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><yml_catalog date="'.date("Y-m-d H:i").'"></yml_catalog>');
generateXML($arData, $xml);
$xml->asXML("doctors.xml");
?>

YML файл сгенерирован. Ссылка на него: <b>https://klinika124.ru/local/dev/conversite/YML/doctors.xml</b>
