<?php

$rss_tags = array(
    'id',
    'price',
    'availability',
    'custom_label_0'
);
$rss_item_tag = 'item';
$rss_url = 'https://www.fricosmos.com/google-merchant/google_merchant_es.xml';
$rss_csv = 'fricosmos.csv';

$columns_names = array(
    'REF1',
    'PRECIO',
    'STOCK',
    'NIVELSTOCK',
    'REF2',
);

$current_date_time = date('h:i:sa') . ' ' . date('Y/m/d');

function parser_stock_level($val)
{
    switch ($val) {
        case "Sin stock":
            return 0;
        case "Nivel de stock: escaso":
            return 1;
        case "Nivel de stock: moderado":
            return 3;
        case "Nivel de stock: amplio":
            return 4;
        default:
            return 0;
    }
}

function rss_to_array($tag, $array, $url)
{
    $doc = new DOMdocument();
    $doc->load($url);
    $rss_array = array();
    $items = array();
    foreach ($doc->getElementsByTagName($tag) as $node) {
        foreach ($array as $key => $value) {
            $val = $node->getElementsByTagName($value)->item(0)->nodeValue;
            if ($value == 'availability') {
                $items[$value] = $val == 'in stock' ? 1 : 0;
            } elseif ($value == 'price') {
                $newval = str_replace(' EUR', '', $val);
                $items[$value] = number_format($newval, 2, ',', '');
            } elseif ($value == 'custom_label_0') {
                $items[$value] = parser_stock_level($val);
            } else {
                $items[$value] = $val;
            }
        }
        array_push($rss_array, $items);
    }
    return $rss_array;
}

echo 'Fricosmos import XML has started at ' . $current_date_time . PHP_EOL;

$rssfeed = rss_to_array($rss_item_tag, $rss_tags, $rss_url);

foreach ($rssfeed as &$val) $val['idtag'] = "FRI" . $val['id'];

$fp = fopen($rss_csv, 'w');

fputcsv($fp, $columns_names, ';');

foreach ($rssfeed as $fields) {
    fputcsv($fp, $fields, ';');
}

fclose($fp);

echo 'Fricosmos import XML has finished at ' . $current_date_time . PHP_EOL . 'Please check the above log for more information.' . PHP_EOL;
