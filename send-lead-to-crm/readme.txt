Класс отправки лида формы в CRM Bitrix24 через вебхук

По инициализации события
(ПРИМЕР)

class CEvents
{

	public static function bindEvents()
	{
		$eventHandler = EventManager::getInstance();

		LandingB24::bindEvents();

    // отправка лидов для формы
    $eventHandler->addEventHandler("form", "onAfterResultAdd", array(self::class, "sendCrmLead"));
		
  }
  
  // отправка лидов для формы
  public static function sendCrmLead($FORM_ID, $RESULT_ID)
  {
      // временно ограничим только для одной формы (Расскажите о вашем проекте)
      if ($FORM_ID == 1) {
          Lead::AddLead($FORM_ID, $RESULT_ID);
      }
  }
		
}

В init.php

//init Events
CEvents::bindEvents();
