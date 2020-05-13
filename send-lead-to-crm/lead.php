<?php

namespace CustomClass;

use CFile;
use CForm;
use CFormCrm;
use CFormCrmSender;
use CFormField;
use CFormResult;


class Lead
{

    public static function AddLead($FORM_ID, $RESULT_ID)
    {
        $FORM_ID = intval($FORM_ID);
        $RESULT_ID = intval($RESULT_ID);

        if ($FORM_ID <= 0 || $RESULT_ID <= 0) {
            return false;
        }

        $dbRes = CFormCrm::GetByFormID($FORM_ID);
        $arLink = $dbRes->Fetch();

        if (!$arLink) {
            return false;
        }

        $arResultFields = array();
        $arAnswers = array();

        $arDataResult = CFormResult::GetDataByID($RESULT_ID, array(), $arResultFields, $arAnswers);

        $ob = new CFormCrmSender($arLink['CRM_ID']);
        $arCrmF = $ob->GetFields();
        $arCrmFields = array();
        foreach ($arCrmF as $ar) {
            $arCrmFields[$ar['ID']] = $ar;
        }

        $arLeadFields = array();
        $dbRes = CFormCrm::GetFields($arLink['ID']);
        while ($arRes = $dbRes->Fetch()) {
            if (intval($arRes['FIELD_ID']) > 0) {
                $bFound = false;
                foreach ($arAnswers as $sid => $arAnswer) {
                    foreach ($arAnswer as $answer_id => $arAns) {
                        if ($arAns['FIELD_ID'] == $arRes['FIELD_ID']) {
                            $bFound = true;
                            if ($arCrmFields[$arRes['CRM_FIELD']]) {
                                $value = '';
                                switch ($arCrmFields[$arRes['CRM_FIELD']]['TYPE']) {
                                    case 'enum':
                                        $value = $arAns['ANSWER_TEXT'];
                                        break;
                                    case 'boolean':
                                        $value = 'Y';
                                        break;
                                    default:
                                        $value = (strlen($arAns['USER_TEXT']) > 0
                                            ? $arAns['USER_TEXT']
                                            : (
                                            strlen($arAns['ANSWER_TEXT']) > 0
                                                ? $arAns['ANSWER_TEXT']
                                                : $arAns['VALUE']
                                            )
                                        );
                                        // Если файл прикреплен - спарсим ссылку на него
                                        if ($arAns["VARNAME"] == "FILE_URL" && !empty($arAns['USER_TEXT'])) {
                                            preg_match_all('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.\/\_]+(jpg|png|gif|bmp|jpeg|doc|docx|ppt|pptx|zip|rar|tar|xls|pdf|xlsx)?/', $value, $arrValue);
                                            if (!empty($arrValue[0][0])) {
                                                $value = $arrValue[0][0];
                                            }
                                        }
                                        break;
                                }

                                if ($arCrmFields[$arRes['CRM_FIELD']]['MULTIPLE'] === "true") {
                                    $arLeadFields[$arRes['CRM_FIELD']] .=
                                        (empty($arLeadFields[$arRes['CRM_FIELD']]) ? '' : ',') . $value;
                                } else {
                                    $arLeadFields[$arRes['CRM_FIELD']] = $value;
                                }
                            }
                        }
                    }
                }

                if (!$bFound && $arCrmFields[$arRes['CRM_FIELD']] && $arCrmFields[$arRes['CRM_FIELD']]['TYPE'] == 'boolean') {
                    $arLeadFields[$arRes['CRM_FIELD']] = 'N';
                }
            } elseif (strlen($arRes['FIELD_ALT']) > 0) {
                switch ($arRes['FIELD_ALT']) {
                    case 'RESULT_ID':
                        $arLeadFields[$arRes['CRM_FIELD']] = $arResultFields['ID'];
                        break;
                    case 'FORM_SID':
                        $arLeadFields[$arRes['CRM_FIELD']] = $arResultFields['SID'];
                        break;
                    case 'FORM_NAME':
                        $arLeadFields[$arRes['CRM_FIELD']] = $arResultFields['NAME'];
                        break;
                    case 'SITE_ID':
                        $arLeadFields[$arRes['CRM_FIELD']] = SITE_ID;
                        break;
                    case 'FORM_ALL':
                        $arLeadFields[$arRes['CRM_FIELD']] = self::_getAllFormFields($FORM_ID, $RESULT_ID, $arAnswers);
                        break;
                    case 'FORM_ALL_HTML':
                        $arLeadFields[$arRes['CRM_FIELD']] = self::_getAllFormFieldsHTML($FORM_ID, $RESULT_ID, $arAnswers);
                        break;
                }
            }
        }

        //Подготавливаем данные
        $leadData = [];
        foreach ($arDataResult as $key => $arField) {
            $key = strtoupper($key);
            if (in_array($key, ['UTM_SOURCE', 'UTM_MEDIUM', 'UTM_CAMPAIGN', 'UTM_CONTENT', 'UTM_TERM'])) {
                $leadData[$key] = $arField[0]['USER_TEXT'];
            } elseif (in_array($key, ['PHONE', 'TEL'])) { //crm_multifield
                $leadData['PHONE'][] = ["VALUE" => $arField[0]['USER_TEXT'], "VALUE_TYPE" => "MOBILE"];
            }  elseif ($key == 'EMAIL') { //crm_multifield
                $leadData[$key][] = ["VALUE" => $arField[0]['USER_TEXT'], "VALUE_TYPE" => "HOME"];
            }
        }

        $arLeadFields = array_merge($arLeadFields, $leadData);
        $leadData['TITLE'] = $arForm['NAME'];

        $arLeadFields['ASSIGNED_BY_ID'] = 3568; //Пользователь CRM

        if($arLeadFields['SOURCE_ID']) { //Источник
            $queryData = http_build_query(array(
                'filter' => array(
                    "ENTITY_ID" => "SOURCE",
                    "NAME" => $arLeadFields['SOURCE_ID'],
                )
            ));
            $result = self::sendQuery($queryData, 'crm.status.list');
            $arLeadFields['SOURCE_ID'] = $result['result'][0]['STATUS_ID'];
        }

        $queryData = http_build_query(array(
            'fields' => $arLeadFields,
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        ));

        if(self::sendQuery($queryData, 'crm.lead.add')) {
            //Помечаем как отправлен в CRM
            CFormResult::SetCRMFlag($RESULT_ID, 'Y');
        } else {
            return false;
        }

    }

    protected static function sendQuery($queryData, $method)
    {
        $queryUrl = 'https://24.YOUR_SITE.ru/rest/3568/z10fm0q3wt6mvko1/'.$method.'.json';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);
        if (array_key_exists('error', $result)) {
//          echo "Ошибка при сохранении лида: " . $result['error_description'] . "";
            return false;
        } else {
            return $result;
        }

    }



    protected static function _getAllFormFields($WEB_FORM_ID, $RESULT_ID, $arAnswers)
    {
        global $APPLICATION;

        $strResult = "";

        $w = CFormField::GetList($WEB_FORM_ID, "ALL", $by, $order, array("ACTIVE" => "Y"), $is_filtered);
        while ($wr = $w->Fetch()) {
            $answer = "";
            $answer_raw = '';
            if (is_array($arAnswers[$wr["SID"]])) {
                $bHasDiffTypes = false;
                $lastType = '';
                foreach ($arAnswers[$wr['SID']] as $arrA) {
                    if ($lastType == '') {
                        $lastType = $arrA['FIELD_TYPE'];
                    } elseif ($arrA['FIELD_TYPE'] != $lastType) {
                        $bHasDiffTypes = true;
                        break;
                    }
                }

                foreach ($arAnswers[$wr["SID"]] as $arrA) {
                    if ($wr['ADDITIONAL'] == 'Y') {
                        $arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];
                    }

                    $USER_TEXT_EXIST = (strlen(trim($arrA["USER_TEXT"])) > 0);
                    $ANSWER_TEXT_EXIST = (strlen(trim($arrA["ANSWER_TEXT"])) > 0);
                    $ANSWER_VALUE_EXIST = (strlen(trim($arrA["ANSWER_VALUE"])) > 0);
                    $USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"]) > 0);

                    if (
                        $bHasDiffTypes
                        && !$USER_TEXT_EXIST
                        && (
                            $arrA['FIELD_TYPE'] == 'text'
                            ||
                            $arrA['FIELD_TYPE'] == 'textarea'
                        )
                    ) {
                        continue;
                    }

                    if (strlen(trim($answer)) > 0) {
                        $answer .= "\n";
                    }
                    if (strlen(trim($answer_raw)) > 0) {
                        $answer_raw .= ",";
                    }

                    if ($ANSWER_TEXT_EXIST) {
                        $answer .= $arrA["ANSWER_TEXT"] . ': ';
                    }

                    switch ($arrA['FIELD_TYPE']) {
                        case 'text':
                        case 'textarea':
                        case 'email':
                        case 'url':
                        case 'hidden':
                        case 'date':
                        case 'password':

                            if ($USER_TEXT_EXIST) {
                                $answer .= trim($arrA["USER_TEXT"]);
                                $answer_raw .= trim($arrA["USER_TEXT"]);
                            }

                            break;

                        case 'checkbox':
                        case 'multiselect':
                        case 'radio':
                        case 'dropdown':

                            if ($ANSWER_TEXT_EXIST) {
                                $answer = substr($answer, 0, -2) . ' ';
                                $answer_raw .= $arrA['ANSWER_TEXT'];
                            }

                            if ($ANSWER_VALUE_EXIST) {
                                $answer .= '(' . $arrA['ANSWER_VALUE'] . ') ';
                                if (!$ANSWER_TEXT_EXIST) {
                                    $answer_raw .= $arrA['ANSWER_VALUE'];
                                }
                            }

                            if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST) {
                                $answer_raw .= $arrA['ANSWER_ID'];
                            }

                            $answer .= '[' . $arrA['ANSWER_ID'] . ']';

                            break;

                        case 'file':
                        case 'image':

                            if ($USER_FILE_EXIST) {
                                $f = CFile::GetByID($arrA["USER_FILE_ID"]);
                                if ($fr = $f->Fetch()) {
                                    $file_size = CFile::FormatSize($fr["FILE_SIZE"]);
                                    $url = ($APPLICATION->IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/bitrix/tools/form_show_file.php?rid=" . $RESULT_ID . "&hash=" . $arrA["USER_FILE_HASH"] . "&action=download&lang=" . LANGUAGE_ID;

                                    if ($arrA["USER_FILE_IS_IMAGE"] == "Y") {
                                        $answer .= $arrA["USER_FILE_NAME"] . " [" . $fr["WIDTH"] . " x " . $fr["HEIGHT"] . "] (" . $file_size . ")\n" . $url;
                                    } else {
                                        $answer .= $arrA["USER_FILE_NAME"] . " (" . $file_size . ")\n" . $url . "&action=download";
                                    }
                                }

                                $answer_raw .= $arrA['USER_FILE_NAME'];
                            }

                            break;
                    }
                }
            }

            $strResult .= $wr["TITLE"] . ":\r\n" . (strlen($answer) <= 0 ? " " : $answer) . "\r\n\r\n";
        }

        return $strResult;
    }

    protected static function _getAllFormFieldsHTML($WEB_FORM_ID, $RESULT_ID, $arAnswers)
    {
        global $APPLICATION;

        $strResult = "";

        $w = CFormField::GetList($WEB_FORM_ID, "ALL", $by, $order, array("ACTIVE" => "Y"), $is_filtered);
        while ($wr = $w->Fetch()) {
            $answer = "";
            $answer_raw = '';
            if (is_array($arAnswers[$wr["SID"]])) {
                $bHasDiffTypes = false;
                $lastType = '';
                foreach ($arAnswers[$wr['SID']] as $arrA) {
                    if ($lastType == '') {
                        $lastType = $arrA['FIELD_TYPE'];
                    } elseif ($arrA['FIELD_TYPE'] != $lastType) {
                        $bHasDiffTypes = true;
                        break;
                    }
                }

                foreach ($arAnswers[$wr["SID"]] as $arrA) {
                    if ($wr['ADDITIONAL'] == 'Y') {
                        $arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];
                    }

                    $USER_TEXT_EXIST = (strlen(trim($arrA["USER_TEXT"])) > 0);
                    $ANSWER_TEXT_EXIST = (strlen(trim($arrA["ANSWER_TEXT"])) > 0);
                    $ANSWER_VALUE_EXIST = (strlen(trim($arrA["ANSWER_VALUE"])) > 0);
                    $USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"]) > 0);

                    if (
                        $bHasDiffTypes
                        &&
                        !$USER_TEXT_EXIST
                        &&
                        (
                            $arrA['FIELD_TYPE'] == 'text'
                            ||
                            $arrA['FIELD_TYPE'] == 'textarea'
                        )
                    ) {
                        continue;
                    }

                    if (strlen(trim($answer)) > 0) {
                        $answer .= "<br />";
                    }
                    if (strlen(trim($answer_raw)) > 0) {
                        $answer_raw .= ",";
                    }

                    if ($ANSWER_TEXT_EXIST) {
                        $answer .= $arrA["ANSWER_TEXT"] . ': ';
                    }

                    switch ($arrA['FIELD_TYPE']) {
                        case 'text':
                        case 'textarea':
                        case 'hidden':
                        case 'date':
                        case 'password':

                            if ($USER_TEXT_EXIST) {
                                $answer .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
                                $answer_raw .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
                            }

                            break;

                        case 'email':
                        case 'url':

                            if ($USER_TEXT_EXIST) {
                               // $answer .= '<a href="' . ($arrA['FIELD_TYPE'] == 'email' ? 'mailto:' : '') . trim($arrA["USER_TEXT"]) . '">' . htmlspecialcharsbx(trim($arrA["USER_TEXT"])) . '</a>';
                                $answer_raw .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
                            }

                            break;

                        case 'checkbox':
                        case 'multiselect':
                        case 'radio':
                        case 'dropdown':

                            if ($ANSWER_TEXT_EXIST) {
                                $answer = htmlspecialcharsbx(substr($answer, 0, -2) . ' ');
                                $answer_raw .= htmlspecialcharsbx($arrA['ANSWER_TEXT']);
                            }

                            if ($ANSWER_VALUE_EXIST) {
                                $answer .= '(' . htmlspecialcharsbx($arrA['ANSWER_VALUE']) . ') ';
                                if (!$ANSWER_TEXT_EXIST) {
                                    $answer_raw .= htmlspecialcharsbx($arrA['ANSWER_VALUE']);
                                }
                            }

                            if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST) {
                                $answer_raw .= $arrA['ANSWER_ID'];
                            }

                            $answer .= '[' . $arrA['ANSWER_ID'] . ']';

                            break;

                        case 'file':
                        case 'image':

                            if ($USER_FILE_EXIST) {
                                $f = CFile::GetByID($arrA["USER_FILE_ID"]);
                                if ($fr = $f->Fetch()) {
                                    $file_size = CFile::FormatSize($fr["FILE_SIZE"]);
                                    $url = ($APPLICATION->IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/bitrix/tools/form_show_file.php?rid=" . $RESULT_ID . "&hash=" . $arrA["USER_FILE_HASH"] . "&lang=" . LANGUAGE_ID;

                                    if ($arrA["USER_FILE_IS_IMAGE"] == "Y") {
                                        $answer .= "<a href=\"$url\">" . htmlspecialcharsbx($arrA["USER_FILE_NAME"]) . "</a> [" . $fr["WIDTH"] . " x " . $fr["HEIGHT"] . "] (" . $file_size . ")";
                                    } else {
                                        $answer .= "<a href=\"$url&action=download\">" . htmlspecialcharsbx($arrA["USER_FILE_NAME"]) . "</a> (" . $file_size . ")";
                                    }

                                    $answer_raw .= htmlspecialcharsbx($arrA['USER_FILE_NAME']);
                                }
                            }

                            break;
                    }
                }
            }

            $strResult .= $wr["TITLE"] . ":<br />" . (strlen($answer) <= 0 ? " " : $answer) . "<br /><br />";
        }

        return $strResult;
    }

}
