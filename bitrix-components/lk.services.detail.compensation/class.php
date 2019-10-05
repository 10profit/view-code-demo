<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die;
}

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Request;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;
use ExportCenter\CRM\Methods\ServiceConfirmationRequest;
use ExportCenter\IBlock\IBlockHelper;
use RecLibrary\AppealHistory;
use RecLibrary\CrmStatus;
use ExportCenter\CRM\Queries\CreateServiceRequest;
use ExportCenter\CRM\Queries\SubsidyRequest;
use Tools\SharePointFileSender;
use ExportCenter\CRM\EntityForSynch;

use Bitrix\Highloadblock as HL;


class LKServicesDetailCompensation extends \CBitrixComponent
{
    /** @var  Request */
    protected $request;

	const HL_BLOCK_QUIZ = 'QuizzesResults';

    private $typesAddress = array(
        "LEGAL" => 899270000, //Юридический
        "ACTUAL" => 899270001 //Фактический
    );

	private $arStreet = array(
		0 => array("CODE" => 899270000, "NAME" => "Аллея", "TYPE_NAME_SHORT" => "аллея", "TYPE_NAME_FULL" => "аллея"),
		1 => array("CODE" => 899270001, "NAME" => "Бульвар", "TYPE_NAME_SHORT" => "б-р", "TYPE_NAME_FULL" => "бульвар"),
		2 => array("CODE" => 899270002, "NAME" => "Линия", "TYPE_NAME_SHORT" => "линия", "TYPE_NAME_FULL" => "линия"),
		3 => array("CODE" => 899270003, "NAME" => "Набережная", "TYPE_NAME_SHORT" => "наб", "TYPE_NAME_FULL" => "набережная"),
		4 => array("CODE" => 899270004, "NAME" => "Переулок", "TYPE_NAME_SHORT" => "пер", "TYPE_NAME_FULL" => "переулок"),
		5 => array("CODE" => 899270005, "NAME" => "Площадь", "TYPE_NAME_SHORT" => "пл", "TYPE_NAME_FULL" => "площадь"),
		6 => array("CODE" => 899270006, "NAME" => "Проезд", "TYPE_NAME_SHORT" => "проезд", "TYPE_NAME_FULL" => "проезд"),
		7 => array("CODE" => 899270007, "NAME" => "Проспект", "TYPE_NAME_SHORT" => "пр-кт", "TYPE_NAME_FULL" => "проспект"),
		8 => array("CODE" => 899270008, "NAME" => "Тупик", "TYPE_NAME_SHORT" => "туп", "TYPE_NAME_FULL" => "тупик"),
		9 => array("CODE" => 899270009, "NAME" => "Улица", "TYPE_NAME_SHORT" => "ул", "TYPE_NAME_FULL" => "улица"),
		10 => array("CODE" => 899270010, "NAME" => "Шоссе", "TYPE_NAME_SHORT" => "ш", "TYPE_NAME_FULL" => "шоссе"),
	);

	private $typeAuthor = array(
		0 => "Аффилированное лицо",
		1 => "Уполномоченное лицо",
		2 => "Поставщик",
		3 => "Производитель"
	);

	private $arIndustry = array(
		0 => "Автомобилестроение",
		1 => "Сельхозмашиностроение",
		2 => "Транспортное машиностроение",
		3 => "Энергетическое машиностроение",
		4 => "Строительно-дорожное/коммунальное машиностроение",
		5 => "Иное"
	);

	private $arAutoIndustry = array(
		0 => "Коммерческий транспорт",
		1 => "Легковой транспорт",
		2 => "Автокомпоненты",
	);

    private $fieldXML = array(
        "SubsidyRequest" => array( // + attributes: "Id"
            "AccountCrmId" => "",
            "ServiceCrmId" => "",
	        "ProductId" => "",
            "FullName" => "FULL_NAME",
            "ShortName" => "WORK_COMPANY", //$arResult['COMPANY']['WORK_COMPANY']
            "Inn" => "UF_OGRN", //$arResult['COMPANY']['UF_OGRN']
            "Ogrn" => "UF_INN", //$arResult['COMPANY']['UF_INN']
            "RegistrationDate" => "DATE_REG",
            "AuthorType" => "TYPE_AUTHOR",
            "IsInCompanyGroup" => "IS_COMPANY_GROUP",
            "CompanyGroupName" => "NAME_COMPANY_GROUP",
            "IsMatchedWithFactAddress" => "IS_MATCHED_ACTUAL_ADDRESS",
            "SendingOriginalDocumentsDetails" => "",
	        "PercentCheck" => "",
            "EfficiencyRatio" => "",
            "ShippedVolume" => "",
            "TotalEstimatedSubsidyAmount" => "TOTAL_ESTIMATED_AMOUNT",
            "Industry" => "INDUSTRY",
            "AutomobileIndustryType" => "AUTOMOBILE_INDUSTRY",
            "Addresses" => array(
                "Address" => array(  // + attributes: "Id"
                    "Type" => "TYPE_ADDRESS",
                    "Index" => "POSTCODE",
                    "CountryId" => "COUNTRY",
                    "RegionId" => "REGION",
                    "City" => "CITY",
                    "Town" => "LOCALITY",
                    "Street" => "STREET",
                    "StreetType" => "TYPE_STREET",
                    "Building" => "HOUSE",
                    "Bulk" => "BUILDING",
                    "Office" => "OFFICE",
                    "Comment" => "COMMENT",
                ),
            ),
            "ContactPersons" => array(
                "ContactPerson" =>  array(  // + attributes: "Id"
                    "LastName" => "LAST_NAME",
                    "FirstName" => "NAME",
                    "MiddleName" => "MIDDLE_NAME",
                    "Position" => "POSITION",
                    "Email" => "EMAIL",
                    "Phone" => "PHONE",
                    "Comment" => "COMMENT",
                    "IsMainContact" => "IS_MAIN_CONTACT",
                ),
            ),
            "RegistryEntries" => array(
            	"RegistryEntry" => array(
		            "CountryId" => "",
		            "Type" => "",
		            "SendingDate" => "",
		            "TransportType" => "",
		            "TransportNumber" => "",
		            "Rur" => "",
		            "Usd" => "",
		            "Eur" => "",
		            "CostInRUR" => "",
		            "AppliedRatio" => "",
		            "TNVEDPercent" => "",
		            "CalculatedSubsidyAmount" => "",
		            "UnitPrice" => "",
		            "UnitCount" => "",
		            "Distance" => "",
		            "ExpensesAmount" => "",
		            "ShippedVolume" => "",
		            "SubsidyAmount" => "",
		            "Documents" => array(
		            	"Document" => array(
				            "Type" => "",
				            "TypeOfProviding" => "",
				            "Number" => "",
				            "Date" => "",
				            "DocumentAmount" => "",
				            "CalcAmount" => "",
				            "SheetsNumber" => "",
				            "CurrencyId" => "",
				            "Verifiers" => array(  // + attributes: "Id"
					            "Verifier" => array(
						            "Position" => "",
						            "LastName" => "",
						            "FirstName" => "",
						            "MiddleName" => "",
						            "Reason" => "",
					            )
				            ),
				            "UploadFileMessageId" => ""
			            )
		            ),
                    "Tnveds" => array(
                    	"Tnved" => ""   // + attributes: "Id"),
                    ),
                    "VinNumbers" => array(
	                    "VinNumber" => array(   // + attributes: "Id"),
		                    "Code" => ""
	                    )
                    ),
	            )
            ),
            "Documents" => array(
                "Document" => array(  // + attributes: "Id"
                    "Type" => "",
                    "TypeOfProviding" => "",
                    "Number" => "",
                    "Date" => "",
                    "DocumentAmount" => "",
                    "CalcAmount" => "",
                    "SheetsNumber" => "",
                    "CurrencyId" => "",
                    "Verifiers" => array(  // + attributes: "Id"
	                    "Verifier" => array(
		                    "Position" => "",
		                    "LastName" => "",
		                    "FirstName" => "",
		                    "MiddleName" => "",
		                    "Reason" => "",
	                    )
                    ),
	                "UploadFileMessageId" => ""
                ),
            ),
        ),
    );

	private $documentTypes = array(
        'Заявления о заключении соглашения и предоставлении субсидии',
        'Выписка ЕГРЮЛ',
        'Письмо от транспортной компании с реестром автотягачей',
        'Паспорт транспортного средства (ПТС)',
        'Письмо от транспортной компании с указанием расстояний по маршрутам транспортировки продукции (для автомобильных перевозок)',
        'Письмо на соответствие условиям предоставления субсидии',
        'Справка налогового органа',
        'Внешнеторговый (экспортный) договор/контракт',
        'Счет/счет-фактура',
        'Инвойс',
        'Договор с транспортной компанией/экспедитором',
        'Товарно-транспортная накладная (ТТН)',
        'Транспортная накладная',
        'Международная товарно-транспортная накладная (CMR)',
        'Ж/Д накладная',
        'Морской коносамент',
        'Договор с водителем',
        'Поручение (договор) с транспортно-экспедиционной компанией на перевозку (организацию перевозки) самоходом',
        'Декларация на товар',
        'Заявление о ввозе товаров и уплате косвенных налогов',
        'Акт выполненных работ',
        'Платежное поручение',
        'Письмо-реестр',
        'Расчет субсидии по форме постановления',
        'Договор с уполномоченным/аффилированным лицом',
        'Письмо МИНПРОМТОРГа (подтверждение производства продукции на территории РФ)',
        'Специальный инвестиционный контракт',
        'Сертификат о происхождении товара',
        'Акт экспертизы Торгово-промышленной палаты РФ',
        'Соглашение о производстве в режиме «промышленной сборки»',
        'Расчет субсидии по форме РЭЦ',
        'Доверенность на подписантов',
        'Статистическая форма',
        'Реестр взаимосвязи платежных документов',
    );

    public function onPrepareComponentParams($arParams)
    {
        $arDefault = [
            'CACHE_TIME' => 3600,
            'CACHE_TYPE' => 'A',
        ];
        $arStatuses = [];
        $resStatus = HLBlockHelper::getHLBlockResultData(
            [],
            $arParams['HL_IBLOCK_TABLE']
        );

        while ($arStatus = $resStatus->fetch()) {
            $arStatus['IS_FINISHED'] = (in_array($arStatus['UF_XML_ID'], $arParams['STATUS_FINISHED']));
            if (null !== $arStatus['UF_CUSTOM_TYPE']) {
                $arStatuses[$arStatus['UF_CUSTOM_TYPE']] = $arStatus;
                $arStatuses[$arStatus['UF_CUSTOM_TYPE']]['CODE'] = $arStatus['UF_CUSTOM_TYPE'];
            } else {
                $arStatuses[$arStatus['UF_XML_ID']] = $arStatus;
                $arStatuses[$arStatus['UF_XML_ID']]['CODE'] = $arStatus['UF_XML_ID'];
            }
        }

        $arDefault['STATUS_LIST'] = $arStatuses;

        return array_replace(
            $arDefault,
            $arParams
        );
    }

    /**
     * Отправка данных в CRM
     */

    public function sendToCRM(){
        global $USER;
        //Отправка запроса на создание услуги
        $filter = ["!UF_XML_ID_CRM" => false, "ID" => $USER->GetID()];
        $by = "id";
        $order = "desc";
        $arUser = CUser::GetList($by, $order, $filter, ["SELECT" => ['ID', 'UF_XML_ID_CRM', 'EMAIL']])->GetNext();

        $arAppeal = \CIBlockElement::GetList(
            [],
            [
                'ID' => $this->arParams['APPEAL_ID'],
                'IBLOCK_ID' => IBlockHelper::getIblockIdByCode('history_appeals'),
                "CREATED_BY" => $arUser['ID']
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                'CREATED_BY',
                'DATE_CREATE',
                'PROPERTY_PRODUCT_ID.PROPERTY_PRODUCT_XML_ID',
                'PROPERTY_PRODUCT_ID.ID',
                'PROPERTY_PRODUCT_ID.PROPERTY_FORM_RESULT_TEXT',
                'PROPERTY_TEXT_XML',
                'PROPERTY_STATUS',
                'PROPERTY_COUNTRIES_XML',
                'PROPERTY_ADDITIONALLY_FILES',
                'REQUEST_NUM',
                'PROPERTY_REQUEST_FORM_DATA',
            ]
        )->GetNext();

        $text = '';
        $text .= !empty($arAppeal['PROPERTY_TEXT_XML_VALUE']['TEXT']) ? $arAppeal['PROPERTY_TEXT_XML_VALUE']['TEXT'] . "\n" : '';
        $text .= !empty($_REQUEST['message_expert']) ? htmlspecialchars($_REQUEST['message_expert']) : '';

        $productCrmId = $arAppeal['PROPERTY_PRODUCT_ID_PROPERTY_PRODUCT_XML_ID_VALUE'];

        $arRequestParams = [
            'SitePersonalAccountId' => !empty($arUser['UF_XML_ID_CRM']) ? $arUser['UF_XML_ID_CRM'] : '',
            'RegistrationDate' => str_replace(' ', 'T', date('Y-m-d H:i:s')),
            'ProductCode' => $productCrmId,
            'Text' => $text,
        ];
        $req = new CreateServiceRequest($arRequestParams);
        $serviceId = "";
        $req->execute(function ($result, $errorText) use (&$serviceId){
            if(is_null($errorText)){
                $serviceId = $result["ServiceId"]; //Id услуги
            }
        });

        //Отправка файлов

        $fileFilter = array();
        foreach($arAppeal["PROPERTY_ADDITIONALLY_FILES_VALUE"] as $fileId){
            $fileFilter[] = $fileId;
        }
        $rsFiles = CFile::GetList(array(), array("@ID" => implode(',', $fileFilter)));
        $uploadDir = COption::GetOptionString("main", "upload_dir", "upload");

        $fileSendResults = array();

        while($arFile = $rsFiles->Fetch()){
            $fPath = $_SERVER["DOCUMENT_ROOT"].'/'.$uploadDir."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
            $fileUploader = new SharePointFileSender(
                $fPath,
                $serviceId,
                "other"
            );

            try {
                $fileUploader->send();
                $fileSendResults[$arFile["ID"]] = $fileUploader->getQueryId(); //Получение идентификатора запроса отправки файлов. Нужен при отправке документов в запросе SubsidyRequest
            } catch (\Bitrix\Main\ObjectException $e) {
                AddMessage2Log(__CLASS__ . $e->getMessage());
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                AddMessage2Log(__CLASS__ . $e->getMessage());
            }
        }

        //Отправка запроса на субсидию
        //на этапе отправки документов получаются идентификатора запроса отправки файлов, где их использовать?
        //UploadFileMessageId
        $arSubsidyRequest = array(
            "AccountCrmId" => $this->arResult['COMPANY']["UF_XML_ID_CRM"],
            "ServiceCrmId" => $serviceId,
            "FullName" => $this->arResult['VALUES']['FULL_NAME'],
            "ShortName" => "-",
            "Inn" => $this->arResult['COMPANY']['UF_INN'],
            "Ogrn" => $this->arResult['COMPANY']['UF_OGRN'],
            "RegistrationDate" => $this->arResult["DATE_REG"], //дата регистрации компании
            "AuthorType" => $this->arResult['VALUES']['TYPE_AUTHOR'],
            "IsInCompanyGroup" => $this->arResult['VALUES']['IS_COMPANY_GROUP'] == "Y" ?: false,
            "CompanyGroupName" => $this->arResult['VALUES']['NAME_COMPANY_GROUP'],
            "ProductId" => $productCrmId,
            "IsMatchedWithFactAddress" => $this->arResult['VALUES']['IS_MATCHED_ACTUAL_ADDRESS'] == "Y" ?: false,
            "SendingOriginalDocumentsDetails" => "-", //номер отправки, курьерской службы, номер доставки
            "PercentCheck" => $this->arResult['registries_info']['percent'],
            "EfficiencyRatio" => $this->arResult['registries_info']["coefficient"],
            "ShippedVolume" => $this->arResult['registries_info']["total_product_price"],
            "TotalEstimatedSubsidyAmount" => $this->arResult['registries_info']["total_subsidy_price"],
            "Addresses" => array(),
            "ContactPersons" => array(),
            "Documents" => array(),
            "RegistryEntries" => array(),
            "Industry" => $this->arResult["INDUSTRY"],
            "AutomobileIndustryType" => $this->arResult["AUTOMOBILE_INDUSTRY"]
        );
        $i = 0;

        foreach($this->arResult['VALUES']['ADDRESS'] as $type => $address){
            $key = "Address##".$i;
            $address = [
                "Type" => $type == "ACTUAL" ? "899270000" : "899270001",
                "Index" => $address["POSTCODE"],
                "CountryId" => EntityForSynch::getByName($address["COUNTRY"], "SynchCountriesRequest")["UF_XML_ID"],
                "RegionId" => EntityForSynch::getByName($address["REGION"], "SynchTerritorySubjectRequest")["UF_XML_ID"],
                "City" => $address["CITY"],
                "Town" => $address["LOCALITY"],
                "Street" => $address["STREET"],
                "StreetType" => $address["TYPE_STREET"],
                "Building" => $address["HOUSE"],
                "Bulk" => $address["BUILDING"],
                "Office" => $address["OFFICE"],
                "Comment" => $address["COMMENT"]
            ];
            $arSubsidyRequest["Addresses"][$key] = $address;
            $i++;
        }

        foreach($this->arResult['CONTACT'] as $type => $person){
            $key = "ContactPerson##".$i;
            $contactPerson = [
                "LastName" => $person["LAST_NAME"],
                "FirstName" => $person["NAME"],
                "MiddleName" => $person["MIDDLE_NAME"],
                "Position" => $person["POSITION"],
                "Email" => $person["EMAIL"],
                "Phone" => $person["PHONE"],
                "Comment" => $person["COMMENT"],
                "IsMainContact" => $person["IS_MAIN_CONTACT"] == "Y" ?: false
            ];
            $arSubsidyRequest["ContactPersons"][$key] = $contactPerson;
            $i++;
        }

        foreach($this->arResult["registries"] as $key => $registry){
            $key = "RegistryEntry##".$key;
            $registryEntry = [
                "CountryId" => EntityForSynch::getByName($registry["country"], "SynchCountriesRequest")["UF_XML_ID"],
                "Type" => $registry["type"],
                "SendingDate" => $registry["date"],
                "TransportType" => $registry["transport_type"],
                "TransportNumber" => $registry["number"],
                "Rur" => $registry["rub_price"],
                "Usd" => $registry["usd_price"],
                "Eur" => $registry["eur_price"],
                "CostInRur" => $registry["document_price"],
                "AppliedRatio" => $registry["coefficient"],
                "TnvedPercent" => $registry["tnveds_percent"],
                "CalculatedSubsidyAmount" => $registry["subsidy_price"],
                "UnitPrice" => $registry["price_for_km"],
                "UnitCount" => $registry["containers_count"],
                "Distance" => $registry["distance"],
                "ExpensesAmount" => $registry['subsidy_limit'],
                "ShippedVolume" => $registry['product_price'],
                "SubsidyAmount" => $registry["accepted_price"],
                "Documents" => [],
                "Tnveds" => [],
                "VinNumbers" => []
            ];

            foreach($registry["tnveds"] as $tnvedKey => $tnved){
                $tnvedKey = "Tnved##".$tnvedKey;
                $tnved = json_decode($tnved, true);
                $registryEntry["Tnveds"][$tnvedKey] = [
                    "@content" => $tnved["value"],
                    "attributes" => [
                        "Id" => $tnved["data"]
                    ]
                ];
            }
            
            $registryVin = explode(',', $registry["vin"]);
            foreach($registryVin as $vinKey => $vin){
                $vinKey = "VinNumber##".$vinKey;
                $registryEntry["VinNumbers"][$vinKey] = [
                    "Code" => $vin
                ];
            }

            foreach($registry["registry_documents"] as $documentKey => $documentNumber){
                $documentKey = "Document##".$documentKey;
                $documentNumber = intval($documentNumber) - 1;
                $document = $this->arResult["documents"][$documentNumber];
                $registryEntry["Documents"][$documentKey] = [
                    "Type" => $document["type"],
                    "TypeOfProviding" => $document["view"],
                    "Number" => $document["number"],
                    "Date" => $document["date"],
                    "DocumentAmount" => $document['doc_price'],
                    "CalcAmount" => $document['price'],
                    "SheetsNumber" => $document["pages"],
                    "CurrencyId" => EntityForSynch::getByShortName($document["currency"], "SynchCurrencyRequest")["UF_XML_ID"],
                    "Verifiers" => [],
                    //"UploadFileMessageId" => ""
                ];

                foreach($document["signers"] as $verKey => $verifier){
                    $verKey = "Verifier##".$verKey;
                    $registryEntry["Documents"][$documentKey][$verKey] = [
                        "Position" => $verifier["position"],
                        "LastName" => $verifier["last_name"],
                        "FirstName" => $verifier["first_name"],
                        "MiddleName" => $verifier["middle_name"],
                        "Reason" => $verifier["reason"]
                    ];
                }
            }

            $arSubsidyRequest["RegistryEntries"][$key] = $registryEntry;
        }

        /**
         * Здесь надо подставлять документы из второго шага
         * При отправке файлов второго шага сохраняется массив fileSendResults, в котором ключ - ID файла в Bitrix, а значение - ID файла с CRM после загрузки
         * Нужно это значение вставлять в ключ UploadFileMessageId массива document
         */

        foreach($this->arResult["documents"] as $documentKey => $document){
            $documentKey = "Document##".$documentKey;
            $document = [
                "Type" => $document["type"],
                "TypeOfProviding" => $document["view"],
                "Number" => $document["number"],
                "Date" => $document["date"],
                "DocumentAmount" => $document['doc_price'],
                "CalcAmount" => $document['price'],
                "SheetsNumber" => $document["pages"],
                "CurrencyId" => EntityForSynch::getByShortName($document["currency"], "SynchCurrencyRequest")["UF_XML_ID"],
                "Verifiers" => [],
                "UploadFileMessageId" => ""
            ];

            foreach($document["signers"] as $verKey => $verifier){
                $verKey = "Verifier##".$verKey;
                $document[$verKey] = [
                    "Position" => $verifier["position"],
                    "LastName" => $verifier["last_name"],
                    "FirstName" => $verifier["first_name"],
                    "MiddleName" => $verifier["middle_name"],
                    "Reason" => $verifier["reason"]
                ];
            }
            $arSubsidyRequest["Documents"][$documentKey] = $document;
        }
        /*
        //Проверка XML
        $builder = new \ExportCenter\CRM\Builder\CrmRequest(
            $arSubsidyRequest,
            "CreateAccelerationDiagnostics"
        );
        \Bitrix\Main\Diag\Debug::dump($builder->asXML());
        */

        $req = new SubsidyRequest($arSubsidyRequest);
        $req->execute(function($result, $errorText){
            // \Bitrix\Main\Diag\Debug::dump($result);
            // \Bitrix\Main\Diag\Debug::dump($errorText);
        });
    }


    public function executeComponent()
	{
		global $APPLICATION;

		try {

			$this->initialize();

			if ($this->request->get('back-to-draft')) {
				$this->reopenDraft();
			} elseif ($this->request->get('delete-draft') == "Y") {
				$this->DeleteRequestDraft($this->arParams['APPEAL_ID']);
				LocalRedirect($this->arParams['HISTORY_APPEALS_PAGE']);
			}

			$filter = $this->getFilter();
			$this->getResult($filter);

			$this->arResult['STEP'] = $this->request->get('step');

			if ($this->request->isPost()) {
				if (!empty($this->arResult['VALUES'])) {
					$this->arResult['VALUES'] = array_replace_recursive($this->arResult['VALUES'], $this->request->getPostList()->toArray());
				} else {
					$this->arResult['VALUES'] = $this->request->getPostList()->toArray();
				}

				$this->checkIsMatchedActualAddress();
				$this->checkAutomobileIndustry();
			}

			switch ($this->arResult['STEP']) {
				case "2":
					$this->arResult['INCLUDE_FORM'] = "step2";
                    if ($this->request->isPost()) {
                        $this->saveImages();
                        if (empty($this->arResult['errors'])) {
                            $uriString = $this->request->getRequestUri();
                            $uri = new Uri($uriString);
                            LocalRedirect($uri->getPath() . "?step=3");
                        }
                    }
					break;
				case "3":
					if(strlen($this->request->get('registry')) > 0 && strlen($this->request->get('documents') > 0)) {
                        if($this->request->isPost()) {
                            $this->attachDocuments();
                        } else {
                            $this->arResult['INCLUDE_FORM'] = "registry";
                        }
					} elseif(strlen($this->request->get('registry')) > 0) {
                        if($this->request->isPost()) {
                            $this->saveRegistry();
                        } else {
                            if($this->request->get('delete')) {
                                $this->deleteDocumentFromRegistry();
                            }
                            $this->arResult['INCLUDE_FORM'] = "registry";
                        }
                    } else {
                        if($this->request->get('delete')) {
                            $this->deleteRegistry();
                        }
						$this->arResult['INCLUDE_FORM'] = "step3";
					}
					break;
				case "4":
				    if(strlen($this->request->get('registry')) > 0 &&strlen($this->request->get('document')) > 0 && strlen($this->request->get('signer')) > 0) {
                        if($this->request->isPost()) {
                            $this->saveSigner();
                        } else {
                            $this->arResult['INCLUDE_FORM'] = "signer";
                        }
                    } elseif (strlen($this->request->get('registry')) > 0 && strlen($this->request->get('document')) > 0) {
                        if($this->request->isPost()) {
                            $this->saveDocument();
                        } else {
                            if($this->request->get('delete')) {
                                $this->deleteSigner();
                            }
                            $this->arResult['INCLUDE_FORM'] = "document";
                        }
					} else {
                        if($this->request->get('delete')) {
                            $this->deleteDocument();
                        }
						$this->arResult['INCLUDE_FORM'] = "step4";
					}
					break;
				case "5":
					$this->arResult['INCLUDE_FORM'] = "step5";
					break;
				default:
					if ($this->request->get('delete-contact')) {
						$this->deleteContact();
						die();
					}

					if ($this->request->get('save-contact')) {
						$this->saveContact();
					}

					if ($this->request->get('save-address')) {
						$this->saveAddress();
						if($this->request->isAjaxRequest() && $this->request->isPost()) {
							$this->saveRequestData($this->arResult['VALUES']);
							die();
						}
					}

					$this->getAddress();

					if(!empty($this->request->get('edit-address')) && ($this->request->get('edit-address') == "legal" || $this->request->get('edit-address') == "actual")) {
                        $this->arResult['TYPE_ADDRESS'] = strtoupper($this->request->get('edit-address'));
						$this->arResult["SELECT_TYPE_STREET"] = $this->arStreet;
                        $this->arResult['INCLUDE_FORM'] = "address";
                    } elseif(!empty($this->request->get('edit-contact')) && strlen($this->request->get('edit-contact')) > 0) {
	                    $this->arResult['ID_CONTACT'] = $this->getIdContact($this->request->get('edit-contact'));
                        $this->arResult['INCLUDE_FORM'] = "contact";
                    } else {
						$this->arResult["SELECT_TYPE_AUTHOR"] = $this->typeAuthor;
						$this->arResult["SELECT_INDUSTRY"] = $this->arIndustry;
						$this->arResult["SELECT_AUTO_INDUSTRY"] = $this->arAutoIndustry;

                        $this->arResult['INCLUDE_FORM'] = "step1";
                    }
                    break;
            }

			if ($this->request->isPost()) {
				if ($this->request->get("save-draft") == "Y") {
					$this->saveRequestData($this->arResult['VALUES'], true);
					LocalRedirect($this->arParams['HISTORY_APPEALS_PAGE']);
				} else {
					$this->saveRequestData($this->arResult['VALUES']);
                    //Отправляем запрос в CRM
                    $this->sendToCRM();
                }
			}

			if ($this->arParams['SET_TITLE'] == 'Y') {
				$APPLICATION->SetTitle($this->arResult['PRODUCT']);
			}

			$this->arResult['CONFIRM_LINK'] = ServiceConfirmationRequest::getServiceConfirmLink($this->arResult['PROPERTY_CRM_SERVICE_ID_VALUE']);
			$this->arResult['QUIZ_URL'] = $this->getQuizUrl($this->arResult['PROPERTY_CRM_SERVICE_GUID_VALUE']);

			$this->includeComponentTemplate();

		} catch (Exception $e) {
			$this->abortResultCache();
			$this->arParams['ERROR_CODE'] = $e->getCode();
			$this->includeComponentTemplate('auth');
		}
	}

	/**
	 * Метод проверяет установлена ли опция дублирования адреса из поля "Фактический адрес" в поле "Юридический адрес"
	 * Если да, то перетираем значение "Юридический адрес" данными из поля "Фактический адрес"
	 */
	public function checkIsMatchedActualAddress()
	{
		if($this->arResult['VALUES']["IS_MATCHED_ACTUAL_ADDRESS"] == "Y") {
			$this->arResult['VALUES']["ADDRESS"]["LEGAL"] = $this->arResult['VALUES']["ADDRESS"]["ACTUAL"];
		}
	}

	/**
	 * Метод сохранения адреса, если пользовать отредактировал вручную через отдельную форму
	 * Где в последствии на 1-м шаге пользователь видит собранную строку заполненного адреса
	 */
	private function saveAddress()
	{
		$post = $this->request->getPostList()->toArray();

		foreach ($post["ADDRESS"] as $type => $fields) {
			if(!empty($fields["COUNTRY"])
				&& !empty($fields["REGION"])
				&& (!empty($fields["CITY"]) || !empty($fields["LOCALITY"]))
				&& !empty($fields["STREET"])
			) {
				$arDataField = array();

				foreach ($fields as $k => $field) {
					if($k == "TYPE_STREET") {
						foreach ($this->arStreet as $kStreet => $itemStreet) {
							if($field == $kStreet || strtolower($field) == $itemStreet["TYPE_NAME_SHORT"] || strtolower($field) == $itemStreet["TYPE_NAME_FULL"]) {
								$arDataField[$k] = $itemStreet["TYPE_NAME_SHORT"];
							}
						}
					} elseif($k != "COMMENT" && !empty($field)) {
						$arDataField[$k] = $field;
					}
				}

				$this->arResult['VALUES']["STR_".$type."_ADDRESS"] = implode(", ", $fields);
			}
		}
	}

	/**
	 * Метод возвращения адреса ввиде одной строки
	 */
	private function getAddress()
	{
		foreach ($this->arResult['VALUES']["ADDRESS"] as $type => $fields) {
			if(!empty($fields["COUNTRY"])
				&& !empty($fields["REGION"])
				&& (!empty($fields["CITY"]) || !empty($fields["LOCALITY"]))
				&& !empty($fields["STREET"])
			) {
				$arDataField = array();

				foreach ($fields as $k => $field) {
					if($k == "TYPE_STREET") {
						foreach ($this->arStreet as $kStreet => $itemStreet) {
							if($field == $kStreet || strtolower($field) == $itemStreet["TYPE_NAME_SHORT"] || strtolower($field) == $itemStreet["TYPE_NAME_FULL"]) {
								$arDataField[$k] = $itemStreet["TYPE_NAME_SHORT"];
							}
						}
					} elseif($k != "COMMENT" && !empty($field)) {
						$arDataField[$k] = $field;
					}
				}

				$this->arResult['VALUES']["STR_".$type."_ADDRESS"] = implode(", ", $arDataField);
			}
		}
	}

	/**
	 * Метод определеяет добавляется ли новый контакт или редактируется один из имеющихся в списке.
	 * В соответсвии присваиваеет новый идентификатор если добавляется новый контакт.
	 *
	 * @param $idContact
	 * @return int
	 **/
	public function getIdContact($idContact)
	{
		$idContact = intval($idContact);

		if($idContact == 0 || !$this->arResult['VALUES']["CONTACT"][$idContact]) {
			if ((is_array($this->arResult['VALUES']["CONTACT"]) && count($this->arResult['VALUES']["CONTACT"]) > 0)) {
				return count($this->arResult['VALUES']["CONTACT"]) + 1;
			} else {
				return 1;
			}
		}

		return $idContact;
	}

	/**
	 * Проверка контактов.
	 * Если какое-то условие не будет соблюдено, то отдаем false, что не выполнены все условия для дальнейшего прохождения формы
	 * Дополнительно: установка или снятие флага основного контакта
	 * @return bool
	 **/
	public function checkContacts()
	{

        $director = false;
        $accountant = false;
        $communication = false;

        if(empty($this->arResult['VALUES']["CONTACT"]) || !is_array($this->arResult['VALUES']["CONTACT"])) {
            return false;
        }

        $hasCommunication = 0;

        foreach ($this->arResult['VALUES']["CONTACT"] as $contact) {
            if($contact["POSITION"] == 0) {
                $director = true;
            }

            if($contact["POSITION"] == 1) {
                $accountant = true;
            }

            if($contact["POSITION"] == 2) {
                $communication = true;
            }

            if($contact["IS_MAIN_CONTACT"] == "Y") {
                $hasCommunication++;
            }
        }

		if(!$communication || !$director || !$accountant && $hasCommunication !== 1) {
			return false;
		}

		return true;
	}

	/**
	 * Добавление нового контакта на 1-м шаге
	 */
	private function saveContact()
	{
		$post = $this->request->getPostList()->toArray();

		if(empty($this->arResult['VALUES']["CONTACT"]) || !is_array($this->arResult['VALUES']["CONTACT"])) {
			$idNextContact = 1;
		} else {
			$idNextContact = count($this->arResult['VALUES']["CONTACT"]) + 1;
		}

		foreach ($post["CONTACT"] as $kContact => $contact) {
			if($this->arResult['VALUES']["CONTACT"][$kContact]) {
				$this->arResult['VALUES']["CONTACT"][$kContact] = $contact;
			} else {
				$this->arResult['VALUES']["CONTACT"][$idNextContact] = $contact;
			}
		}
	}

	/**
	 * Метод удаления контакта из 1-го шага. И перестраиваем идентификаторы контактов
	 */
	private function deleteContact()
	{
		if ($this->request->isPost()) {
			$postData = $this->request->getPostList()->toArray();

			if(intval($postData["contact_id"]) > 0 && !empty($this->arResult['VALUES']["CONTACT"]) && is_array($this->arResult['VALUES']["CONTACT"])) {
				unset($this->arResult['VALUES']["CONTACT"][$postData["contact_id"]]);

				$arContacts = array();
				$i = 1;
				foreach ($this->arResult['VALUES']["CONTACT"] as $kContact => $contact) {
					$arContacts[$i] = $contact;
					$i++;
				}
				$this->arResult['VALUES']["CONTACT"] = $arContacts;

				$this->saveRequestData($this->arResult['VALUES']);
			}
 		}
	}

	/**
	 * Если в поле отрасли было выбрано не автомобилестроение, то значение "Вид автопрома" должен быть пустым
	 */
	private function checkAutomobileIndustry()
	{
		if(!empty($this->arResult['VALUES']["INDUSTRY"]) && $this->arResult['VALUES']["INDUSTRY"] != 0 ) {
			$this->arResult['VALUES']["AUTOMOBILE_INDUSTRY"] = "";
		}
	}

    protected function attachDocuments()
    {
        $registryId = (integer)$this->request->get('registry');
        $request = $this->request->getPostList()->toArray();
        if($registryId > 0){
            $index = $registryId - 1;
            $registry = $this->arResult['VALUES']['registries'][$index];
            $registryDocuments = $registry['documents'];
            foreach ($request['documents'] as $item){
                $key = array_search((string)$item, $registryDocuments);
                if($key !== false) {
                    $registryDocuments[$key] = (string)$item;
                } else {
                    $registryDocuments[] = (string)$item;
                }
            }
            $registry['documents'] = $registryDocuments;
            $this->arResult['VALUES']['registries'][$index] = array_merge($this->arResult['VALUES']['registries'][$index], $registry);

            $this->saveRequestData($this->arResult['VALUES']);
            $this->calculateRegistry($index);
            $this->calculateRegistries();
        }

        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        $uri->deleteParams(['step', 'registry', 'documents']);
        $uri->addParams(['step' => '3', 'registry' => $registryId]);
        LocalRedirect($uri->getUri());
    }

    protected function deleteDocumentFromRegistry()
    {
        $registryId = (integer)$this->request->get('registry');
        $forDelete = (integer)$this->request->get('delete');

        if($registryId > 0){
            $index = $registryId - 1;
            $registry = $this->arResult['VALUES']['registries'][$index];
            $registryDocuments = $registry['documents'];
            $key = array_search((string)$forDelete, $registryDocuments);
            if($key !== false) {
                $registryDocuments[$key] = (string)$forDelete;
                unset($registryDocuments[$key]);
            }
            $registry['documents'] = $registryDocuments;
            $this->arResult['VALUES']['registries'][$index] = $registry;

            $this->saveRequestData($this->arResult['VALUES']);
            $this->calculateRegistry($index);
            $this->calculateRegistries();
        }

        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        $uri->deleteParams(['step', 'delete', 'registry']);
        $uri->addParams(['step' => '3', 'registry'=> $registryId]);

        LocalRedirect($uri->getUri());
    }

    protected function calculateRegistries()
    {
        $paymentGroups = array(20);

        $registries = $this->arResult['VALUES']['registries'];
        $totalProductPrice = 0;
        $totalSubsidyPrice = 0;

        foreach ($registries as $registry) {
            $totalProductPrice += (float)$registry['product_price'];
            $totalSubsidyPrice += (float)$registry['accepted_price'];
        }

        $this->arResult['VALUES']['registries_info'] = array(
            'percent' => ($totalProductPrice > 0) ? ($totalSubsidyPrice / $totalProductPrice * 100) : 0,
            'coefficient' => ($totalSubsidyPrice > 0) ? $totalProductPrice / $totalSubsidyPrice : 0,
            'total_product_price' => $totalProductPrice,
            'total_subsidy_price' => $totalSubsidyPrice,
        );

        $this->saveRequestData($this->arResult['VALUES']);
    }

    protected function calculateRegistry($registryId)
    {
        $registry = $this->arResult['VALUES']['registries'][$registryId];

        if(!empty($registry)){
            $registry['rub_price'] = 0;
            $registry['eur_price'] = 0;
            $registry['usd_price'] = 0;
            $registry['document_price'] = 0;
            $registry['coefficient'] = 0;
            $registry['subsidy_price'] = 0;
            $registry['subsidy_limit'] = 0;
            $registry['accepted_limit'] = 0;
            $registry['price_for_km'] = 1;



            $documents = array();
            foreach ($registry['documents'] as $documentId){
                $documents[(integer)$documentId] = $this->arResult['VALUES']['documents'][(integer)$documentId - 1];
            }

            if($this->arResult['VALUES']['INDUSTRY'] == '0'){
                if($this->arResult['VALUES']['AUTOMOBILE_INDUSTRY'] == '0'){
                    $hasSpik = false;
                    $hasTpp = false;
                    foreach ($documents as $document){
                        if($document['type'] == '26'){
                            $hasSpik = true;
                        }
                        if($document['type'] == '27'){
                            $hasTpp = true;
                        }
                    }
                    if($hasSpik && $hasTpp) {
                        $registry['coefficient'] = 0.8;
                    }
                }
                if($this->arResult['VALUES']['AUTOMOBILE_INDUSTRY'] == '1'){
                    $hasSpik = false;
                    $hasTpp = false;
                    $hasProm = false;
                    foreach ($documents as $document){
                        if($document['type'] == '26'){
                            $hasSpik = true;
                        }
                        if($document['type'] == '27'){
                            $hasTpp = true;
                        }
                        if($document['type'] == '28'){
                            $hasProm = true;
                        }
                    }
                    if($hasSpik && $hasTpp && $hasProm) {
                        $registry['coefficient'] = 0.8;
                    }
                }
                if($this->arResult['VALUES']['AUTOMOBILE_INDUSTRY'] == '2'){
                    $hasSpik = false;
                    $hasTpp = false;
                    $hasProm = false;
                    foreach ($documents as $document){
                        if($document['type'] == '26'){
                            $hasSpik = true;
                        }
                        if($document['type'] == '27'){
                            $hasTpp = true;
                        }
                        if($document['type'] == '28'){
                            $hasProm = true;
                        }
                    }
                    if($hasSpik && $hasProm) {
                        $registry['coefficient'] = 0.8;
                    } elseif($hasProm && $hasTpp) {
                        $registry['coefficient'] = 0.6;
                    }
                }
            } else {
                foreach ($documents as $document){
                    if(in_array($document['type'], array('26', '25'))){
                        $registry['coefficient'] = 0.8;
                        break;
                    }
                }
            }

            $paymentDocuments = array();
            foreach ($documents as $key => $document) {
                if(!empty($document)){
                    if(in_array($document['type'], array('20'))){
                        $paymentDocuments[] = $document;
                    }
                }
            }

            foreach ($paymentDocuments as $paymentDocument){
                $key = strtolower($paymentDocument['currency']) . '_price';
                $registry[$key] += (float)$paymentDocument['price'];
                $registry['document_price'] += (float)$paymentDocument['total_price'];
            }
            $registry['subsidy_price'] = (float)$registry['document_price'] * (float)$registry['coefficient'] / 100;

            $registry['subsidy_limit'] = (float)$registry['price_for_km'] * (float)$registry['containers_count'] * (float)$registry['distance'];

            $registry['accepted_price'] = ((float)$registry['product_price'] < (float)$registry['subsidy_price']) ? $registry['product_price'] : $registry['subsidy_price'];
            $this->arResult['VALUES']['registries'][$registryId] = array_merge($this->arResult['VALUES']['registries'][$registryId], $registry);

            $this->saveRequestData($this->arResult['VALUES']);
        }
    }

    protected function saveRegistry()
    {
        $registryId = (integer)$this->request->get('registry');
        $registry = $this->request->getPostList()->toArray();
        $needNewDocument = (bool)$registry['new_document'];
        unset($registry['new_document']);
        if($registryId > 0){
            $index = $registryId - 1;
            $this->arResult['VALUES']['registries'][$index] = array_merge($this->arResult['VALUES']['registries'][$index], $registry);
        } else {
            $this->arResult['VALUES']['registries'][] = $registry;
            $index = key( end($this->arResult['VALUES']['registries']) );
        }

        $this->saveRequestData($this->arResult['VALUES']);
        $this->calculateRegistry($index);
        $this->calculateRegistries();

        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        if($needNewDocument){
            end($this->arResult['VALUES']['registries']);
            $uri->deleteParams(['step', 'document']);
            $uri->addParams(['step' => '4', 'registry' => key($this->arResult['VALUES']['registries'])]);
        } else {
            $uri->deleteParams(['step', 'registry']);
            $uri->addParams(['step' => '3']);
        }
        LocalRedirect($uri->getUri());
    }

    protected function deleteRegistry()
    {
        $forDelete = (integer)$this->request->get('delete');
        unset($this->arResult['VALUES']['registries'][(integer)$forDelete - 1]);
        $this->saveRequestData($this->arResult['VALUES']);
        $this->calculateRegistries();
        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        $uri->deleteParams(['step', 'delete']);
        $uri->addParams(['step' => '3']);

        LocalRedirect($uri->getUri());
    }

    protected function saveSigner()
    {
        $registryId = (integer)$this->request->get('registry');
        $documentId = (integer)$this->request->get('document');
        $signerId = (integer)$this->request->get('signer');

        $signer = $this->request->getPostList()->toArray();
        if($documentId > 0){
            $documentIndex = $documentId - 1;
            if($signerId > 0){
                $this->arResult['VALUES']['documents'][$documentIndex]['signers'][$signerId - 1] = $signer;
            } else {
                $this->arResult['VALUES']['documents'][$documentIndex]['signers'][] = $signer;
            }
        }

        $this->saveRequestData($this->arResult['VALUES']);
        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        $uri->deleteParams(['step', 'document', 'signer', 'registry']);
        $uri->addParams(['step' => '4', 'registry' => $registryId, 'document' => $documentId]);

        LocalRedirect($uri->getUri());
    }

    protected function deleteSigner()
    {
        $document = (integer)$this->request->get('document');
        $forDelete = (integer)$this->request->get('delete');
        if($document > 0 && $forDelete > 0){
            $documentIndex = $document  - 1;
            $index = (integer)$forDelete - 1;
            unset($this->arResult['VALUES']['documents'][$documentIndex]['signers'][$index]);
        }
        $this->saveRequestData($this->arResult['VALUES']);

        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        $uri->deleteParams(['step', 'document', 'delete']);
        $uri->addParams(['step' => '4', 'document' => $document]);

        LocalRedirect($uri->getUri());
    }

    protected function saveDocument()
    {
        $registryId = (integer)$this->request->get('registry');
        $documentId = (integer)$this->request->get('document');
        $document = $this->request->getPostList()->toArray();

        $document['total_price'] = $this->getDocumentPrice($document['currency'], $document['price'], $document['date']);
        $needNewSigner = (bool)$document['new_signer'];
        unset($document['new_signer']);
        $document['type_name'] = $this->documentTypes[$document['type']];
        if($documentId > 0){
            $index = $documentId - 1;
            $this->arResult['VALUES']['documents'][$index] = array_merge($this->arResult['VALUES']['documents'][$index], $document);
        } else {
            $this->arResult['VALUES']['documents'][] = $document;
        }

        $registryIndex = $registryId - 1;

        $this->saveRequestData($this->arResult['VALUES']);
        $this->calculateRegistry($registryIndex);
        $this->calculateRegistries();

        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        if($needNewSigner){
            end($this->arResult['VALUES']['documents']);
            $uri->deleteParams(['step', 'document', 'registry']);
            $uri->addParams(['step' => '4', 'registry' => $registryId,'document' => key($this->arResult['VALUES']['documents']), 'signer' => 0]);
        } else {
            $uri->deleteParams(['step', 'document', 'registry']);
            $uri->addParams(['step' => '4', 'registry' => $registryId]);
        }
        LocalRedirect($uri->getUri());
    }

    protected function getDocumentPrice($currency, $price, $date)
    {
        if($currency == 'USD') {
            $hldata = array_pop(HL\HighloadBlockTable::getList(array('filter' => array('NAME' => 'UsdCourse')))->fetchAll());
            $entityClass = HL\HighloadBlockTable::compileEntity($hldata)->getDataClass();
            $res = $entityClass::getList(array('select' => array('*'), 'order' => array('ID' => 'ASC'), 'filter' => array(
                'UF_DATE' => $date
            )))->fetch();

            if(!empty($res['UF_COURSE'])){
                return (float)$price * (float)$res['UF_COURSE'];
            }
            return (float)$price * 69.4706;
        }
        if($currency == 'EUR') {
            $hldata = array_pop(HL\HighloadBlockTable::getList(array('filter' => array('NAME' => 'EurCourse')))->fetchAll());
            $entityClass = HL\HighloadBlockTable::compileEntity($hldata)->getDataClass();
            $res = $entityClass::getList(array('select' => array('*'), 'order' => array('ID' => 'ASC'), 'filter' => array(
                'UF_DATE' => $date
            )))->fetch();

            if(!empty($res['UF_COURSE'])){
                return (float)$price * (float)$res['UF_COURSE'];
            }
            return (float)$price * 70.294;
        }

        return (float)$price;
    }

    protected function deleteDocument()
    {
        $registryId = (integer)$this->request->get('registry');
        $forDelete = $this->request->get('delete');
        if($forDelete == 'all'){
            foreach ($this->arResult['VALUES']['documents'] as $key => $document){
                $this->arResult['VALUES']['documents'][$key]['disabled'] = true;
            }
        } else {
            $index = (integer)$forDelete - 1;
            $this->arResult['VALUES']['documents'][$index]['disabled'] = true;
        }

        $this->saveRequestData($this->arResult['VALUES']);

        $uriString = $this->request->getRequestUri();
        $uri = new Uri($uriString);
        $uri->deleteParams(['step', 'delete', 'registry']);
        $uri->addParams(['step' => '4', 'registry'=> $registryId]);

        LocalRedirect($uri->getUri());
    }

	/**
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function initialize()
	{
		Loader::includeModule('iblock');
		Loader::includeModule('highloadblock');
		Loader::includeModule('rec.library');
		Loader::includeModule('exportcenter.main');
		Loader::includeModule('rec.oauth2');

		$this->request = Context::getCurrent()->getRequest();

		$this->isOwner();
	}

    /**
     * Проверка существования заявки
     *
     * @throws Exception
     */
    private function isOwner()
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            throw new \Exception('Требуется авторизация', 1);
        }

        $resOrder = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array(
                'IBLOCK_ID' => HISTORY_APPEALS_IBLOCK_ID,
                'ID' => intval($this->arParams['APPEAL_ID']),
                'CREATED_BY' => $USER->GetID(),
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_CRM_SERVICE_GUID')
        )->Fetch();

        if (!$resOrder && !$USER->IsAdmin()) {
            Bitrix\Iblock\Component\Tools::process404(
                "Заявка не найдена"
                ,true
                ,"Y"
                ,"N"
            );
            throw new \Exception('Заявка не найдена', 2);
        }
    }

	/**
	 * Сохраняем форму заявки на услугу, если надо переключаем статус черновика в готовый к отправке.
	 *
	 * @param array $values
	 * @param bool $is_draft
	 * @return void
	 */
	public function saveRequestData($values = [], $is_draft = false)
	{

		//Предварительная обработка данных
		$this->prepareSaveData($values);

		// сделаем Update иначе Дата изменения не обновится у черновика
		global $USER;
		$obElement = new CIBlockElement();
		$arLoadProductArray = Array(
			"MODIFIED_BY" => $USER->GetID()
		);
		$res = $obElement->Update($this->arResult["ID"], $arLoadProductArray);

		$obElement->SetPropertyValueCode($this->arResult["ID"], 'REQUEST_FORM_DATA', [
			"VALUE" => [
				"TEXT" => json_encode($values),
				"TYPE" => "html",
			],
		]);

		if ($is_draft) {
			//Статусы черновика
			$ObDraftStatus = \CIBlockPropertyEnum::GetList(["SORT" => "ASC"], [
				'IBLOCK_ID' => $this->arResult['IBLOCK_ID'],
				'PROPERTY_ID' => 'DRAFT_STATUS',
			]);
			$DraftStatus = [];
			while ($ob = $ObDraftStatus->Fetch()) {
				$DraftStatus[$ob['XML_ID']] = $ob;
			}

			//Сохранение и перевод в статус завершенного черновика
			$obElement->SetPropertyValueCode(
				$this->arResult["ID"],
				'DRAFT_STATUS',
				['VALUE' => $DraftStatus['complete']['ID']]
			);
		}
	}

	/**
	 * Метод подготовки чистки данных перед сохранением
	 * Напрмер, метод избавляется от не нужных к хранению в БД значений
	 * @param $value
	 */
	private function prepareSaveData(&$value)
	{
		//todo: в этот же метод можно добавить, что-то типа htmlspecialcharsbx(). Но только учесть, что $value - многомерный массив

		$arDeleteValue = array(
			"save-address",
			"edit-address",
			"save-contact",
			"edit-contact",
			"delete-contact",
			"submit",
		);

		foreach ($arDeleteValue as $val) {
			if($value[$val]) {
				unset($value[$val]);
			}
		}
	}

	/**
	 * Собираем всю информацию по заявке
	 *
	 * @param array $filter
	 * @param array $sort
	 * @throws \Exception
	 * @throws \Bitrix\Main\SystemException
	 */
    protected function getResult($filter = [], $sort = ['TIMESTAMP_X' => 'DESC'])
    {
        global $DB, $USER;

        $format = "DD.MM.YYYY HH:MI:SS";
        $new_format = "DD.MM.YYYY##HH:MI";

        $res = CIBlockElement::GetList(
            $sort,
            $filter,
            false,
            ['nTopCount' => 1],
            [
                'ID',
                'IBLOCK_ID',
                'PROPERTY_PRODUCT_ID',
                'NAME',
                'CREATED_BY',
                'TIMESTAMP_X',
                'DATE_CREATE',
                'PROPERTY_STATUS',
                'PROPERTY_PRODUCT_ID.ID',
                'PROPERTY_PRODUCT_ID.NAME',
                'PROPERTY_PRODUCT_ID.CODE',
                'PROPERTY_REQUEST_NUM',
                'PROPERTY_CRM_SERVICE_ID',
                'PROPERTY_REC_USER',
                'PROPERTY_VIEWED_BY_USER',
                'PROPERTY_PRODUCT_ID.PREVIEW_TEXT',
                'PROPERTY_PRODUCT_ID.PROPERTY_TEXT_FORM',
                'PROPERTY_PRODUCT_ID.PROPERTY_TEXT_FORM_STEP_1',
                'PROPERTY_PRODUCT_ID.PROPERTY_TEXT_FORM_STEP_2',
                'PROPERTY_PRODUCT_ID.PROPERTY_TEXT_FORM_STEP_3',
                'PROPERTY_DRAFT_STATUS',
                'PROPERTY_REQUEST_FORM_DATA',
                'PROPERTY_CRM_SERVICE_GUID',
                'PROPERTY_EXPORTSTAT_REPORT',
            ]
        );

        if ($arItem = $res->Fetch()) {
            $this->arResult = $arItem;
            $d_create = explode('##', $DB->FormatDate($arItem['DATE_CREATE'], $format, $new_format));
            $d_update = explode('##', $DB->FormatDate($arItem['TIMESTAMP_X'], $format, $new_format));
            $this->arResult['DATE']['DATE'] = $d_create[0];
            $this->arResult['DATE']['TIME'] = $d_create[1];
            $this->arResult['LAST_UPDATE']['DATE'] = $d_update[0];
            $this->arResult['LAST_UPDATE']['TIME'] = $d_update[1];
            $this->arResult['STATUS'] = $this->arParams['STATUS_LIST'][$arItem['PROPERTY_STATUS_VALUE']];
            $this->arResult['PRODUCT'] = $arItem['PROPERTY_PRODUCT_ID_NAME'];
            $this->arResult['VARIANT'] = $this->getTypeProduct($arItem['PROPERTY_PRODUCT_ID_VALUE']);
	        $this->arResult['COMPANY'] = $this->GetUserCompany($USER->GetID());
            $this->arResult['CHAIN_GROUPS_NAME'] = $this->getChainProduct($arItem['PROPERTY_PRODUCT_ID_VALUE']);
            $this->arResult['PRODUCT_ID'] = $arItem['PROPERTY_PRODUCT_ID_ID'];
            $this->arResult['DRAFT_STATUS'] = CIBlockPropertyEnum::GetByID($arItem['PROPERTY_DRAFT_STATUS_ENUM_ID']);
            if (!empty($this->arResult['DRAFT_STATUS'])) {
                $this->arResult['DRAFT_STATUS'] = $this->arResult['DRAFT_STATUS']['XML_ID'];
            }

            // ВСЯ JSON-ина котторая хранится в Истории, выводим ее в результирующий массив.
            $REQUEST_FORM_DATA = [];
            if (is_array($arItem['PROPERTY_REQUEST_FORM_DATA_VALUE'])) {
                $REQUEST_FORM_DATA = array_shift($arItem['PROPERTY_REQUEST_FORM_DATA_VALUE']);
            }
	        if (strlen($REQUEST_FORM_DATA) > 0) {
		        try {
			        $this->arResult['VALUES'] = json_decode($REQUEST_FORM_DATA, true);
		        } catch (\Exception $e) {
			        $this->arResult['VALUES'] = [];
		        }
	        } else {
		        $this->arResult['VALUES'] = [];
	        }

            $this->arResult['REQUEST_NUM'] = $arItem['PROPERTY_REQUEST_NUM_VALUE'];
            $this->arResult['SERVICE_NUM'] = $arItem['PROPERTY_CRM_SERVICE_ID_VALUE'];
            $this->arResult['PREVIEW_TEXT'] = $arItem['PROPERTY_PRODUCT_ID_PREVIEW_TEXT'];
            $this->arResult['VIEWED_BY_USER'] = ($arItem['PROPERTY_VIEWED_BY_USER_VALUE'] == 'Y');

            // фиксим сериализованный текст
            if ($arText = unserialize($arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_VALUE'])) {
                $this->arResult['TEXT_FORM'] = $arText['TEXT'];
            } else {
                if (is_array($arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_VALUE'])) {
                    $this->arResult['TEXT_FORM'] = $arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_VALUE']['TEXT'];
                } else {
                    $this->arResult['TEXT_FORM'] = $arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_VALUE'];
                }
            }
            // Текст алгоритма по шагам
            $this->arResult['TEXT_FORM_STEP'] = [
                1 => $arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_STEP_1_VALUE']['TEXT'],
                2 => $arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_STEP_2_VALUE']['TEXT'],
                3 => $arItem['PROPERTY_PRODUCT_ID_PROPERTY_TEXT_FORM_STEP_3_VALUE']['TEXT'],
            ];

	        // флаг вывода формы "Оценить услугу"
	        $isRateForm = false;
	        if ($this->arResult['STATUS']['IS_FINISHED']) {
		        $resRate = CIBlockElement::GetList(
			        array('sort' => 'asc'),
			        array('IBLOCK_ID' => SERVICES_RATE_IBLOCK_ID, 'PROPERTY_ORDER_ID' => $arItem['ID'])
		        );
		        if (!($arRate = $resRate->Fetch())) {
			        $isRateForm = true;
		        }
	        }
	        $this->arResult['IS_RATE_FORM'] = $isRateForm;

            if (in_array($arItem['PROPERTY_STATUS_VALUE'], $this->arParams['STATUS_ACTIVE'])) {
                $this->arResult['IS_ACTIVE'] = true;
            }
            $this->arResult['IS_DRAFT'] = false;
            if ($this->arResult['PROPERTY_STATUS_VALUE'] == CrmStatus::DRAFT_STATUS || $this->arResult['PROPERTY_STATUS_VALUE'] == '') {
                $this->arResult['IS_DRAFT'] = true;
            }

            $this->arResult['STATUS_CHANGELOG'] = AppealHistory::getAppealStatusChangelog($this->arResult['ID']);

            if (!$this->arResult['VIEWED_BY_USER'] && $this->arResult['CREATED_BY'] == $USER->GetID()) {
                $arProps = CIBlockPropertyEnum::GetList(
                    [],
                    [
                        'CODE' => 'VIEWED_BY_USER',
                        'IBLOCK_ID' => $this->arResult['IBLOCK_ID'],
                        'VALUE' => 'Y'
                    ]
                )->Fetch();
                CIBlockElement::SetPropertyValuesEx($this->arResult['ID'], $this->arResult['IBLOCK_ID'],
                    ['VIEWED_BY_USER' => $arProps['ID'], 'VIEWED_BY_USER_CNT' => 0]);
            }

            if ($expFileId = $arItem['PROPERTY_EXPORTSTAT_REPORT_VALUE']) {
                $this->arResult['EXP_FILE'] = \CFile::GetByID($expFileId)->Fetch();
                $this->arResult['EXP_FILE']['PATH'] = \CFile::GetPath($expFileId);
            }

        } else {
            throw new \Exception('Заявка не найдена', 2);
        }

        $this->arResult['BACK_LINK'] = $this->getBackLink();

        if ($this->arResult['DRAFT_STATUS'] == 'complete') {
            $request = Application::getInstance()->getContext()->getRequest();
            $uriString = $request->getRequestUri();
            $uri = new Uri($uriString);
            $uri->addParams(array("back-to-draft" => "Y"));
            $uri->deleteParams(array('step'));
            $uri->addParams(array("step" => "1"));
            $this->arResult['DRAFT_LINK'] =$uri->getUri();
        }
    }

	/**
	 * @param $id
	 * @return mixed
	 * @throws \Bitrix\Main\LoaderException
	 */
    private function getTypeProduct($id)
    {
        $arrFilter = array(
            "ID" => $id,
            "IBLOCK_ID" => IBlockHelper::getIblockIdByCode('non_financial_products', 'services'),
        );
        $elements = CIBlockElement::GetList(array(), $arrFilter, false, false, array("ID", "NAME", "PROPERTY_PRODUCT_TYPE"));
        while($arElements = $elements->Fetch()){
            return $arElements['PROPERTY_PRODUCT_TYPE_VALUE'];
        }
    }

	/**
	 * Получаем информацию о компании клиента
	 *
	 * @param $user_id
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function GetUserCompany($user_id)
	{
		if (intval($user_id) <= 0) {
			$this->error = __CLASS__ . ": Empty \$user_id argument";
			return [];
		}

		$by = "timestamp_x";
		$order = "desc";
		$obUser = CUser::GetList($by, $order, [
			'ID' => $user_id,
		], [
			"FIELDS" => ['ID', 'WORK_COMPANY'],
			"SELECT" => ['UF_OGRN', 'UF_INN', 'UF_ORGANIZATION'],
		]);

		$arUser = $obUser->Fetch();

		if (!isset($arUser['ID']) || intval($arUser['ID']) <= 0) {
			$this->error = __CLASS__ . ": Can`t find user";
			return [];
		}

		return $arUser;
	}

	/**
	 * @param $id
	 * @return mixed
	 */
    private function getChainProduct($id)
    {
        $result = CIBlockElement::GetElementGroups($id, true);
        $chain = array();
        while ($item = $result->Fetch()) {
            $chain['GROUP'][] = $item;
        }
        $result = array();
        foreach ($chain['GROUP'] as $section){
            $ibsTreeResource = CIBlockSection::GetNavChain( false, $section['ID'], array( "ID", "NAME",) );
            while($sectionItem = $ibsTreeResource->Fetch()){
                $chain['CHAIN_GROUPS'][] = $sectionItem['NAME'];
            }
        }
        return $chain['CHAIN_GROUPS'];
    }

	/**
	 * @return bool
	 */
    private function getBackLink()
    {
        return isset($_SESSION['USER_SERVICE_STATE']) ? $_SESSION['USER_SERVICE_STATE'] : false;
    }

	/**
	 * @return array
	 */
    protected function getFilter()
    {
        global $USER;

        $filter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
        ];

        // по прямой ссылке админ открывает любую заявку
        if (!$USER->IsAdmin()) {
            $filter['CREATED_BY'] = $USER->GetID();
        }

        if (!empty($appeal_id = $this->arParams['APPEAL_ID'])) {
            $filter['ID'] = (int)trim($appeal_id);
        }

        return $filter;
    }

    /**
     * Удаление черновика заявки
     *
     * @param $request_id
     */
    public function DeleteRequestDraft($request_id)
    {
        global $DB;
        $DB->StartTransaction();
        if (!CIBlockElement::Delete($request_id)) {
            $DB->Rollback();
        } else {
            $DB->Commit();
        }
    }

	/**
	 * Возврат к редактированию заявки
	 */
	private function reopenDraft()
	{
		if ($id = $this->arParams['APPEAL_ID']) {
			//Статусы черновика
			$ObDraftStatus = \CIBlockPropertyEnum::GetList(["SORT" => "ASC"], [
				'IBLOCK_ID' => $this->arResult['IBLOCK_ID'],
				'PROPERTY_ID' => 'DRAFT_STATUS',
			]);
			$DraftStatus = [];
			while ($ob = $ObDraftStatus->Fetch()) {
				$DraftStatus[$ob['XML_ID']] = $ob;
			}

			$obElement = new CIBlockElement();

			$obElement->SetPropertyValueCode($id, 'DRAFT_STATUS',
				['VALUE' => $DraftStatus['init']['ID']]);


			$uriString = $this->request->getRequestUri();
			$uri = new Uri($uriString);
			$uri->deleteParams(["back-to-draft", 'step']);
			$uri->addParams(['step' => '1']);

			LocalRedirect($uri->getUri());
		}
	}

	/** возвращает ссылку на текущий активный и не пройденный опрос для пользователя по услуге
	 * @param $serviceId - GUID услуги
	 * @return bool|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function getQuizUrl($serviceId)
	{
		if ($serviceId) {
			$className = Highloadblock::getInstance(self::HL_BLOCK_QUIZ)->getDataClass();

			if ($qRow = $className::getList([
				'filter' => [
					'=UF_SERVICE_ID' => $serviceId,
					'=UF_QUIZ_RESULT_DATA' => null,
					'>UF_QUIZ_FINISH_DATE' => new DateTime(),
				],
				'limit' => 1
			])->fetch()) {
				return '/quality_control/?h=' . $qRow['UF_SECRET'] . '&channel=425430000';
			}
		}

		return false;
	}

	public function saveImages()
    {
        $files = [];

        $formFieldsName = [
            'EGRUL__2__FILE',
            'SPRAVKA__2__FILE',
            'SUBSIDII__2__FILE',
            'REESTR__2__FILE',
            'SOSTAV__2__FILE',
            'ZAYAVLENIE__2__FILE',
            'PISMO__2__FILE'
        ];

        foreach ($formFieldsName as $name) {
            if ($_FILES[$name]['error'] === 0) {
                $files[$name] = $_FILES[$name];
            }
        }

        if (!empty($files)) {
            $cBlock = new CIBlockElement();

            foreach ($files as $key => $file) {
                $cBlock->Update(
                    $this->arResult['ID'],
                    [
                        'PROPERTY_VALUES' =>
                            [
                                'ADDITIONALLY_FILES' => $file,
                                'PRODUCT_ID' => $this->arResult['PROPERTY_PRODUCT_ID_VALUE']
                            ]
                    ]
                );

                if($cBlock->LAST_ERROR) {
                    $this->arResult['errors'][$key] = $cBlock->LAST_ERROR;
                } else {
                    $this->arResult['success'][$key] = "(" . $file['name'] . ") успешно загружен.";
                }
            }
        }
    }
}
