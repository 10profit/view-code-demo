<?
/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var array $arParams
 */
?>

<div class="g-wrap" style="padding-bottom: 50px">
    <?
    global $USER;
    switch ($arParams['ERROR_CODE']) {
        case 1: ?>
            Для отображения истории обращений&nbsp;<a href="javascript:void(0)" data-js-open-layer="auth">авторизуйтесь</a>.
            <? break;

        case 2: ?>
            Доступ запрещён или заявка не существует.
            <? break;

        default:
            if ($USER->IsAdmin() && $this->arParams['ERRORS']) {
               print_r($this->arParams['ERRORS']);
            }
            ?>
            Произошла внутренняя ошибка.
            <? break;
    }
    ?>
</div>
