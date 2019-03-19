<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
//pre($arOrder);
use Bitrix\Main\Loader;
Loader::includeModule('iblock');
Loader::includeModule('catalog');
Loader::includeModule('sale');
class Courier
{
    private static $instance;
    public static function handler($arOrder)
    {
        $instance = self::getInstance();
        // �������� ������ (��� �������)
        $arProducts = $instance->getListProducts($arOrder['ID']);
        //pre($arOrder);
        // ���������� � ���������
        $arIblock = $instance->getIblockInfo($arProducts[0]['PRODUCT_ID']);
        $arResult = $instance->resultCreate($arIblock, $arProducts, $arOrder);
        return $arResult;
    }
    private static function getInstance()
    {
        $instance = self::$instance ? self::$instance : new self();
        return $instance;
    }
    //���������� ������ ������� �� ������
    private  function getListProducts($orderId)
    {
        $items = null;
        if( $dbBasket = \CSaleBasket::GetList(Array("ID"=>"ASC"), Array("ORDER_ID"=>$orderId), false, false) )
        {
            $key = 0;
            while ($arItems = $dbBasket->Fetch())
            {
            $items[$key] = $arItems;
            $key++;
            }
        return $items;
        }
    else
        {
            throw new \Exception('�� ������� �������� ������ ��������� �� ID ������. ��� �� ����.');
        }
    }
    // ���������� ID ��� � ��� ��������� � ��������
    private function getIblockInfo($ElementID)
    {
        $items = array();
        $Res = CIBlockElement::GetByID($ElementID);
        if ($arItem = $Res->GetNext())
        {
            $items['IBLOCK_ID'] = $arItem['IBLOCK_ID'];
            $items['IBLOCK_CODE'] = $arItem['IBLOCK_CODE'];
            $items['IBLOCK_TYPE_ID'] = $arItem['IBLOCK_TYPE_ID'];
        }

        return $items;
    }
    //���������� ������ ������� ��� ������
    private function getElementProps($IBlockCode, $ElementID)
    {
        $prop = array();
        $filter = ['IBLOCK_CODE' => $IBlockCode, 'ID' => $ElementID];
        $select = ['ID','IBLOCK_ID'];
        $rs = \CIBlockElement::GetList(false, $filter, false, false, $select);
        while($ob = $rs->getNextElement())
        {
            $prop[] = $ob->getProperties();
        }
        return $prop;
    }
    // ���������� ������ ���������������� ������� ������
    private function getOrderProps($orderID)
    {
        //pre($orderID);
        $arValue = array();
        $db_vals = \CSaleOrderPropsValue::GetList(
        array("SORT" => "ASC"),
            array(
                "ORDER_ID" => $orderID,
                //"CODE" => "*"
            )
        );
        while ($arVals = $db_vals->Fetch())
        {
            $arValue[$arVals['CODE']] = $arVals;
        }
        //pre($arValue);
        return $arValue;
    }
    // ���������� ��������� ����
    private function getOrderFields($orderID)
    {
        $arField = array();
        $db_vals = CSaleOrder::GetList(
            array("SORT" => "ASC"),
            array(
                "ID" => $orderID

            )
        );
        while ($arVals = $db_vals->Fetch())
        {
            $arField[] = $arVals;
        }
        return $arField;
    }


    private function getOrderPay($orderID)
    {
        $arPay = array();
        $db_vals = CSalePaySystemAction::GetList(
            array("SORT" => "ASC"),
            array(
                "PAY_SYSTEM_ID" => $orderID

            )
        );
        while ($arVals = $db_vals->Fetch())
        {
            $arPay[] = $arVals;
        }
        return $arPay;
    }

    // ����������� ������ ���� � ����� �� ��� ID
    private function getUserInfo($userID)
    {
        $order = array('sort' => 'asc');
        $tmp = 'sort'; // �������� ��������������� �������, �� ������ ����
        $rsUser = \CUser::GetList($order, $tmp, array("ID"=>$userID), array("UF_*"));
        $arUser = $rsUser->Fetch();
        return $arUser;
    }

    private function getOrderStatus($statusID)
    {
            $arStatus = array(
            "N" => "������. ��������",
            "M" => "������ �� ��������",
            "D" => "�����",
            "E" => "�������",
            "F" => "��������",
            );
            $status = array_search($statusID, array_flip($arStatus));
            return $status;
    }
        // global array - �������� ������ ��� ������ � ������
    private function resultCreate($arIblock, $arProducts, $arOrder)
    {
        $result = array();
        $result['IBLOCK_INFO'] = $arIblock;
        foreach ($arProducts as $key => $product)
        {
        $product['PROPERTIES'] = $this->getElementProps($arIblock['IBLOCK_CODE'], $product['PRODUCT_ID']);
        $arItem[] = $product;
        }
        if (!empty($arOrder)) {
        $result['USER_INFO'] = $this->getUserInfo($arOrder['USER_ID']);
        $result['ORDER_PROPERTIES'] = $this->getOrderProps($arOrder['ID']);
        $result['FIELDS'] = $this->getOrderFields($arOrder['ID']);
        $result['PAYSYSTEM'] = $this->getOrderPay($arOrder['PAY_SYSTEM_ID']);
        $result['ORDER_STATUS'] = $this->getOrderStatus($arOrder['STATUS_ID']);
        }
        if (!empty($arItem)) {
        $result['PRODUCTS'] = $arItem;
        }
        return $result;
    }
}
?>