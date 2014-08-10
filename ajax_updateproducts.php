<?php

$arrayitems = "";
if (isset($_GET['arrayitems'])) {

    // header('Content-type: application/json');
    require_once(dirname(__FILE__) . '../../../config/config.inc.php');
    require_once(dirname(__FILE__) . '../../../init.php');

    // recuperation des produits en nouveautes

    $data = $_GET['arrayitems'];

    // Definition d une date de depart pour la MAJ des produits

    $date_update = new DateTime();
    date_modify($date_update, '-1 hour');

    // MAJ de la date de MAJ des produits

    $db = Db::getInstance();

    foreach ($data as $idProduct) {

            Db::getInstance()->update(
                'product',
                array(
                    'date_upd' => pSQL($date_update->format('Y-m-d H:i:s')),
                ),
                $where = '`id_product` = ' . (int)$idProduct,
                $limit = 0,
                $null_values = false,
                $use_cache = false,
                $add_prefix = true
            );

            Db::getInstance()->update(
                'product_shop',
                array(
                    'date_upd' => pSQL($date_update->format('Y-m-d H:i:s')),
                ),
                $where = '`id_product` = ' . (int)$idProduct,
                $limit = 0,
                $null_values = false,
                $use_cache = false,
                $add_prefix = true
            );

            date_modify($date_update, '+1 second');


    }

    // Envoi des donnees au format JSON

    echo json_encode('ok');

} else {

    echo json_encode('error');

}