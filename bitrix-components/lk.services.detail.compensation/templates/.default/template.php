<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}
/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var array $arParams
 */
\Bitrix\Main\Page\Asset::getInstance()->addCss($templateFolder . '/css/spacing.css');
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder . "/js/jquery.autocomplete.min.js");

?>
<!-- layer cancel -->
<div class="layer layer-bg_auth layer-close_app" data-js-layer data-id="draft-remove">
	<div class="layer__wrapper _700">

		<div class="layer__header">
			<div class="layer__title _m32">Удалить черновик</div>
			<div class="layer__close"></div>
		</div>

		<div class="layer__body _f16 cancel_form">
			<div class="layer__body-wrapper">
				<div class="cancel-wrapper">
					<div class="app-layer__text">
						<p>
							Вы уверены что хотите удалить этот черновик?
						</p>
					</div>
					<form action="<?= $APPLICATION->GetCurPage() ?>" method="post" enctype="multipart/form-data"
					      data-name="<?=$arResult["PROPERTY_PRODUCT_ID_NAME"]?>"
					      data-id="<?=$arResult['ID']?>"
					      data-variant="<?=$arResult['VARIANT']?>"
					      data-category="<?php if(!empty($arResult['CHAIN_GROUPS_NAME'])) { echo implode("/", $arResult['CHAIN_GROUPS_NAME']); }?>"
					>
						<button type="submit" name="delete-draft" class="btn bk-button" value="Y">
							Удалить черновик
						</button>
						<div class="btn btn_light" js-close-layer>Отмена</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- layer posted -->
<div class="layer layer-bg_auth layer-close_app" data-js-layer data-id="draft-posted">
	<div class="layer__wrapper _700">
		<div class="layer__header">
			<div class="layer__title _m32">Ваша заявка отправлена!</div>
		</div>
		<form action="" method="post" enctype="multipart/form-data">
			<div class="btn bk-button" js-close-layer>Закрыть</div>
		</form>
	</div>
</div>

<? if ($arResult['IS_ACTIVE']): ?>
	<!-- layer cancel -->
	<div class="layer layer-bg_auth layer-close_app" data-js-layer data-id="cancel">
		<div class="layer__wrapper _700">

			<div class="layer__header">
				<div class="layer__title _m32">Отозвать заявку</div>
				<div class="layer__close"></div>
			</div>

			<div class="layer__body _f16 cancel_form">
				<div class="layer__body-wrapper">
					<div class="cancel-wrapper">
						<div class="app-layer__text">
							<p>
								Обращаем внимание, что если вы отзываете заявку, то все работы по ней прекращаются. Если
								вы
								решите возобновить получение услуги, то вам необходимо будет подавать заявку
								заново.
							</p>
							<p>
								Укажите,
								пожалуйста, причину, по которой вы отзываете заявку
							</p>
						</div>

						<form class="app-message__form">
							<textarea class="app-message__txt"></textarea>

							<button class="btn bk-button" disabled>Отозвать заявку</button>
							<div class="btn btn_light" js-close-layer>Отмена</div>
						</form>
					</div>
				</div>
			</div>

			<div class="layer__body _f16 cancel_success" style="display: none">
				<div class="layer__body-wrapper">
					<div class="app-layer__success">
						<div class="app-layer__text">
							<p>Ваша заявка аннулирована</p>
							<p>Вы аннулировали заявку на оказание услуги
								«<?= $arResult['PROPERTY_PRODUCT_ID_NAME'] ?>
								».</p>
							<p>В случае, если вам в последующем потребуется воспользоваться данной услугой, то вы
								можете
								найти и
								заказать ее в каталоге услуг на сайте РЭЦ.</p>
						</div>
						<div class="btn bk-button" js-close-layer>Закрыть окно</div>
					</div>
				</div>
			</div>

		</div>
	</div>
<? endif; ?>

<?if($_GET['sended']):?>
	<!-- layer crm-success -->
	<div class="layer layer-bg_auth layer-close_app" data-js-layer data-id="crm-success">
		<div class="layer__wrapper _700">
			<div class="layer__header">
				<div class="layer__title _m32">Ваша заявка отправлена</div>
				<div class="layer__close"></div>
			</div>

			<div class="layer__body _f16 assess_form">
				<div class="layer__body-wrapper">
					<div class="app-layer__text">
						<p>Уважаемый экспортёр!</p>
						<p>
							Спасибо за обращение в АО «Российский экспортный центр». <br>
							Ваша заявка отправлена в работу.
						</p>
					</div>

					<form class="app-message__form">
						<div class="btn bk-button" js-close-layer>Закрыть</div>
					</form>
				</div>
			</div>
		</div>
	</div>
<?endif;?>

<? if ($arResult['IS_RATE_FORM']) { ?>
	<!-- layer cancel -->
	<div class="layer layer-bg_auth layer-close_app" data-js-layer data-id="assess01">
		<div class="layer__wrapper _700">
			<div class="layer__header">
				<div class="layer__title _m32">Оценить работу</div>
				<div class="layer__close"></div>
			</div>

			<div class="layer__body _f16 assess_form">
				<div class="layer__body-wrapper">
					<div class="app-layer__text">
						<p>Уважаемый экспортёр!</p>
						<p>
							Повышение качества обслуживания – одна из главных целей деятельности АО «Российский
							экспортный центр». Чтобы создать максимально комфортные условия взаимодействия, нам очень
							важно знать ваше мнение.
						</p>
						<p>
							Пожалуйста оцените качество нашей работы по вашему обращению по десятибальной шкале и
							оставьте небольшой комментарий.<br/>Спасибо
						</p>
					</div>

					<form class="app-message__form">
						<div class="app-layer__estimate">
							<div class="estimate-list js-estimate-list" data-score=""></div>
						</div>

						<span>Комментарий</span>
						<textarea class="app-layer__txt"></textarea>

						<button class="btn bk-button" disabled>Отправить</button>
						<div class="btn btn_light" js-close-layer>Отмена</div>
					</form>
				</div>
			</div>

			<div class="layer__body _f16 assess_success">
				<div class="layer__body-wrapper">
					<div class="app-layer__success">
						<div class="app-layer__text">
							<p>
								Спасибо за вашу оценку. В случае необходимости наши сотрудники отдела качества свяжутся
								с
								вами для уточнения деталей.
							</p>
						</div>
						<div class="btn bk-button" js-close-layer>Закрыть окно</div>
					</div>
				</div>
			</div>

		</div>
	</div>
<? } ?>

<section class="g-section service-detail">
	<div class="g-wrap">
		<aside class="_left _back">
			<a href="<?= $arResult['BACK_LINK'] ?: $arParams['HISTORY_APPEALS_PAGE'] ?>" class="">
				К списку заявок на услуги
			</a>
			<br>
			<br>
			<? if (!empty($arResult['DRAFT_LINK']) && !$arResult['REQUEST_NUM']): ?>
				<a href="<?= $arResult['DRAFT_LINK'] ?>" class="">
					К редактированию заявки
				</a>
			<? endif; ?>
			<nav class="form_block_menu" data-event-block-menu>
				<a class="item-menu-form<? if ($arResult['STEP'] == 1 || empty($arResult['STEP'])) { ?> active<? } ?>" href="?step=1">
					<span>Информация о заявителе</span>
				</a>
				<a class="item-menu-form<? if ($arResult['STEP'] == 2) { ?> active<? } ?>" href="?step=2">
					<span>Оценка соответствия организации условиям предоставления субсидии</span>
				</a>
				<a class="item-menu-form<? if ($arResult['STEP'] == 3) { ?> active<? } ?>" href="?step=3">
					<span>Реестры предоставляемых документов по логистической субсидии</span>
				</a>
				<a class="item-menu-form<? if ($arResult['STEP'] == 4) { ?> active<? } ?>" href="?step=4">
					<span>Доступные документы</span>
				</a>
				<a class="item-menu-form<? if ($arResult['STEP'] == 5) { ?> active<? } ?>" href="?step=5">
					<span>Отправка обращения</span>
				</a>
			</nav>
		</aside>
		<main class="_900" id="order_wrap" data-id="<?= $arResult['ID'] ?>">
			<h1 class="app-row__title">Заявка на оказание услуги:</h1>
			<div class="app-row__preview order_description <?=(!empty($arResult['TEXT_FORM']) && $arResult['IS_DRAFT'] ? 'app-row__no_brd' : '');?>">
				<b><?= $arResult['PRODUCT'] ?></b>
			</div>
			<? if (!$arResult['IS_DRAFT']): ?>
				<div class="app-row app-row_brd js-app-row">
					<!-- Application Info -->
					<div class="app-info js-app-info">
						<ul class="list-clean">
							<li>
								<div class="app-info__name">Номер:</div>
								<div class="app-info__val">
									<?= $arResult['SERVICE_NUM'] ?: 'Черновик' ?>
								</div>
							</li>
							<li>
								<div class="app-info__name">Создана:</div>
								<div class="app-info__val">
									<?= $arResult['DATE']['DATE'] ?>
									<span class="app-info__time">
										<?= $arResult['DATE']['TIME'] ?>
									</span>
								</div>
							</li>
							<li>
								<div class="app-info__name">Изменена:</div>
								<div class="app-info__val">
									<?= $arResult['LAST_UPDATE']['DATE'] ?>
									<span class="app-info__time">
										<?= $arResult['LAST_UPDATE']['TIME'] ?>
									</span>
								</div>
							</li>
							<li>
								<div class="app-info__name">Статус:</div>
								<div class="app-info__val js-app-status">
									<?= $arResult['STATUS']['UF_SITE_STATUS'] ?: 'Черновик' ?>
								</div>
								<? if ($arResult['IS_RATE_FORM']) { ?>
									<a href="#" class="app-info__layer open_rate_windowapp-info__layer open_rate_window" data-js-open-layer="assess01">Оценить работу</a>
								<? } ?>
							</li>
							<? if ($arResult['EXP_FILE']) { ?>
								<li>
									<div class="app-info__name">Результат услуги:</div>
									<div class="app-info__val">
										<a class="i-link doc doc_pdf" target="_blank" href="<?=$arResult['EXP_FILE']['PATH']?>">
											<?=$arResult['EXP_FILE']['FILE_NAME']?>
											<span class="desc">pdf, <?=round($arResult['EXP_FILE']['FILE_SIZE'] / 1024)?> kb</span>
										</a>
									</div>
								</li>
							<? } ?>
						</ul>
						<? if ($arResult['IS_ACTIVE']): ?>
							<a href="#" class="cancel_window" data-js-open-layer="cancel">Отозвать заявку</a>
						<? endif; ?>
						<? if ($arResult['CONFIRM_LINK']): ?>
							<a class="btn bk-button" href="<?=$arResult['CONFIRM_LINK']?>" target="_blank">Подтвердить работы</a>
						<? endif; ?>
						<? if ($arResult['QUIZ_URL']): ?>
							<a class="btn bk-button" href="<?=$arResult['QUIZ_URL']?>" target="_blank">Оценить качество услуги</a>
						<? endif; ?>
					</div>
				</div>
			<? endif; ?>
			<? if ($arResult['IS_DRAFT'] && null !== $arResult['TEXT_FORM_STEP'][$arResult['STEP']]) : ?>
				<div class="app-row__preview text_form">
					<?= $arResult['TEXT_FORM_STEP'][$arResult['STEP']]; ?>
				</div>
			<? endif; ?>

			<div class="app-row__row">
				<? if (!$arResult['PROPERTY_REQUEST_NUM_VALUE'] && $arResult['IS_DRAFT']) { ?>
					<div class="draft-container">
						<form action="<?= $APPLICATION->GetCurPage() ?>" method="post" id="<?= $arParams['COMPONENT_ID'] ?>" enctype="multipart/form-data">
							<a href="#" class="app-info__layer delete-draft-link" data-js-open-layer="draft-remove">
								<span class="delete"></span>
								Удалить черновик
							</a>
						</form>
					</div>
				<? } ?>
				<div class="event_request_form">
					<? if(!empty($arResult['INCLUDE_FORM']) && @file_exists(dirname(__FILE__)."/include/".$arResult['INCLUDE_FORM'].".php")) {
						include_once("include/".$arResult['INCLUDE_FORM'].".php");
					} ?>
				</div>
			</div>
		</main>
	</div>
</section>
