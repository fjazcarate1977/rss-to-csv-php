<?php

    $rss_tags = array(
        'id',
        'price',
        'availability',
    );
    $rss_item_tag = 'item';
    $rss_url = 'https://www.fricosmos.com/google-merchant/google_merchant_es.xml';
    $rss_csv = 'fricosmos.csv';

    $current_date_time = date('h:i:sa') . ' ' . date('Y/m/d');

    function rss_to_array($tag, $array, $url) {
        $doc = new DOMdocument();
        $doc->load($url);
        $rss_array = array();
        $items = array();
        foreach($doc->getElementsByTagName($tag) AS $node) {    
            foreach($array AS $key => $value) {
                $val = $node->getElementsByTagName($value)->item(0)->nodeValue;
                if($value == 'availability') {
                    $items[$value] = $val == 'in stock' ? 0 : 1;
                } elseif ($value == 'price') {
                    $newval= str_replace(' EUR', '', $val);
                    $items[$value] = number_format($newval, 2, ',', '.');
                } else {
                    $items[$value] = $val;
                }    
   
            }
            array_push($rss_array, $items);
        }
        return $rss_array;
    }

    echo 'Fricosmos import XML has started at ' . $current_date_time . PHP_EOL ;

    $rssfeed = rss_to_array($rss_item_tag,$rss_tags,$rss_url);

    foreach ($rssfeed as &$val) $val['idtag'] = "FRI" . $val['id'];

    $fp = fopen($rss_csv, 'w');
    foreach ($rssfeed as $fields) {
        fputcsv($fp, $fields, ';');
    }
  
    fclose($fp);

    echo 'Fricosmos import XML has finished at ' . $current_date_time . PHP_EOL . 'Please check the above log for more information.' . PHP_EOL;

?>
