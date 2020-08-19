<?php

function getAllCurrencies()
{
    include_once "modules/configuration/objects/currency/currency_model.php";
    $objModel = currency_model::getInstance();
    $arrCurrencies = $objModel->getCurrencies();
    return $arrCurrencies;
}