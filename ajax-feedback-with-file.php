<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application,
    Bitrix\Main\Mail\Event;

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

// Разрешенные расширения файлов.
$allow = array("pdf");

// Запрещенные расширения файлов.
$deny = array(
    'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp',
    'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html',
    'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
);

// Директория куда будут загружаться файлы.
$path = $_SERVER["DOCUMENT_ROOT"] . '/upload/project-feedback/';

if ($request->isPost()) {
    $result = array();
    $postParams = $request->getPostList()->toArray();

    # Сохраняем файлы на сервере
    if (!empty($_FILES["file"])) {
        $files_ids = array();
        $files_cnt = count($_FILES["file"]["name"]);

        for ($i = 0; $i < $files_cnt; $i++) {
            $arIMAGE["name"] = $_FILES['file']['name'][$i];
            $arIMAGE["size"] = $_FILES['file']['size'][$i];
            $arIMAGE["tmp_name"] = $_FILES['file']['tmp_name'][$i];
            $arIMAGE["type"] = $_FILES['file']['type'][$i];
            $arIMAGE["MODULE_ID"] = "iblock";
            $fid = CFile::SaveFile($arIMAGE, "/tmp_projects/");
            $files_ids[] = $fid;
        }
    }


    $el = new CIBlockElement();
    $PROP = array();
    $PROP[49] = $postParams['phone'];
    $PROP[47] = $files_ids;

    $arLoadProductArray = array(
        "NAME" => $postParams["name"],
        "IBLOCK_ID" => "9",
        'PROPERTY_VALUES' => $PROP,
    );

    if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {

        $send_result = Event::sendImmediate(array(
            "EVENT_NAME" => "FEEDBACK_FORM",
            'MESSAGE_ID' => 7,
            "LID" => "s1",
            "C_FIELDS" => array(
                "AUTHOR" => $postParams["name"],
                "PHONE" => $postParams["phone"],
            ),
            "FILE" => $files_ids
        ));

        # Удаляем временные файлы
        CFile::Delete($files_ids);

        $result["success"] = "y";
    } else {
        $result = array(
            "success" => "n",
            "error" => "Ошибка добавления результатов в инфоблок"
        );
    }


    echo json_encode($result);
}
