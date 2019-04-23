<?php

echo "<h1 style='text-align: center; color: lightslategray'>>>>>>>ТОЛЬКО ДЛЯ ДЕМОНСТРАЦИИ<<<<<<<</h1>";

/**
 *  Сначала смотрим, есть ли Кука ACC_TOKEN . Для нее выставлена жизнь 3600.
 *  Если куки нет - Выводим ссылку АВТОРИЗОВАТЬСЯ ---
 *  По клику АВТОРИЗОВАТЬСЯ - редирект на получение кода доступа для приложения. по условию функ. getAuthCode("УРЛ") ( строка 48-51 )
 *  Далее - возвращается значение "code", содержащий код авторизации.
 *  Проверяем, вернулся ли код авторизации и  (строка 16 )
 *  Делаем CURL- запрос на получение токена.  (строка 32 )
 *  Сам токен запишем в куку, чтобы отслеживать жизнь сессии токена.
 * 
 */

// запрос на получение токена
if (!empty($_REQUEST['code'])) {
    $params = array();
    $params['grant_type'] = 'authorization_code';
    $params['client_id'] = 'spiridonov';
    $params['client_secret'] = 'spiridonov_secret';
    $params['code'] = $_REQUEST['code'];

    $url = 'https://test.exportcenter.ru/oauth2/token/'; 

    $ch = curl_init();
    $header = array ();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS,$params);

    $response = curl_exec( $ch );
    $arResult = (array)json_decode($response);

    //>>>>
    print_r($arResult);
    //>>>>
    
    // установим куку с токеном
    if (empty($arResult['error']) && !empty($arResult['access_token'])) {
        setcookie('ACC_TOKEN',$arResult['access_token'],time() + $arResult['expires_in'], '/');
        header("Location: /api/curl_test/view/");
    }

}

// редирект на получение кода доступа для приложения
if ($_REQUEST['auth'] == "Y") {
    // предполагается, что ссылка на авторизацию приложения выдается клиенту при регистрации приложения
    getAuthCode('https://test.exportcenter.ru/oauth2/auth/?response_type=code&client_id=spiridonov&state=qwerty');
}

// ====================================================================================================
// Если Куки нет - выведем сообщение НЕАВТОРИЗОВАН
if (!$_COOKIE['ACC_TOKEN']) {
    echo "<p>Статус: Не авторизован. Необходимо получение токена.</p>";
    echo "<p><a style='color: red;' href='?auth=Y' >АВТОРИЗОВАТЬСЯ (получить токен)</a></p>";
}
else {

    echo "<p style='color: green;'>АВТОРИЗОВАН</p>";

    if ($_REQUEST['test_user'] == 'Y') {
        $url = 'https://test.exportcenter.ru/api/services/v1/user/';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_GET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_COOKIE['ACC_TOKEN'], 'Content-Type: application/json']);
        $curlData = curl_exec($curl);
        curl_close($curl);
        var_dump(json_decode($curlData, true));
    }

    if ($_REQUEST['test_service'] == '1') {
        $url = 'https://test.exportcenter.ru/api/services/v1/navigator/';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_GET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_COOKIE['ACC_TOKEN'], 'Content-Type: application/json']);
        $curlData = curl_exec($curl);
        curl_close($curl);
        print_r(json_decode($curlData, true));
    }

    if ($_REQUEST['test_service'] == '2') {
        $url = 'https://test.exportcenter.ru/api/services/v1/countryexport/';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_GET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_COOKIE['ACC_TOKEN'], 'Content-Type: application/json']);
        $curlData = curl_exec($curl);
        curl_close($curl);
        print_r(json_decode($curlData, true));
        //print_r($curlData);
    }

    if ($_REQUEST['test_service'] == '3') {
        $url = 'https://test.exportcenter.ru/api/services/v1/countryexport/TN';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_GET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_COOKIE['ACC_TOKEN'], 'Content-Type: application/json']);
        $curlData = curl_exec($curl);
        curl_close($curl);
        print_r(json_decode($curlData, true));
        //print_r($curlData);
    }

}

// редирект на авторизацию прилоения
function GetAuthCode($url) {
    header('Location: ' . $url);
}

?>

<? if ($_COOKIE['ACC_TOKEN']): ?>
    <html >
        <head>
            <meta charset="utf-8">
        </head>
        <body>
            <p>Получить информацию о пользователе</p>
            <a href="?test_user=Y" ><button>Получить</button></a>
            <br>
            <p>Услуга 1. Навигатор по барьерам и требованиям рынков</p>
            <a href="?test_service=1" ><button>Получить услугу 1</button></a>
            <br>
            <br>
            <p>Предзапрос Услуги 2. Запрос списка стран по услуге "Страновой экспортный профил"</p>
            <p>Точка входа: "/api/services/v1/countryexport/"</p>
            <a href="?test_service=2" ><button> Получить список стран </button></a>
            <p>Услуга 2. Страновой экспортный профиль</p>
            <p>Точка входа: "/api/services/v1/countryexport/TN"</p>
            <a href="?test_service=3" ><button>Получить услугу 2 (Страна Тунис TN)</button></a>
        </body>
    </html>
<? endif;?>
