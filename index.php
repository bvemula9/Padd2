<?php

# m h  dom mon dow   command
40 14 * * 5 /usr/bin/php /home/vugman/padd2.ulysses.biz/public_html/xls_to_csv_cron.php
40 6 * * 1 /usr/bin/php /home/vugman/padd2.ulysses.biz/public_html/xls_to_csv_cron.php


    require('php-excel-reader/excel_reader2.php');
    require('SpreadsheetReader.php');
    ini_set('memory_limit', '-1');
    function get_http_response_code($url) {
      $headers = get_headers($url);
      return substr($headers[0], 9, 3);
    }


    function get_spot_price($date, $table){

      $url = 'http://apps.ulysses.biz/get_report?date=20'.$date.'&table='.$table.'&type=csv';

      if(get_http_response_code($url) != "200"){
        return;
      }

      $data = file_get_contents($url);

      $rows = explode("\n",$data);
      $s = array();
      foreach($rows as $row) {
        $s[] = str_getcsv($row, ';');
      }
        
      return explode("|", $s[0][0])[1];
    }

  date_default_timezone_set('America/Chicago');
  $today = getdate();
  $minutes = $today['minutes'];
  $hours = $today['hours'];
  $wday = $today['wday'];

  if($wday == 5 && $hours == 14 && $minutes == 40){

    echo "CFTC\n";

    $destDir = '/home/vugman/padd2.ulysses.biz/public_html';
    $filename = 'tmpfile.zip';
    $theurl = 'http://www.cftc.gov/files/dea/history/fut_disagg_xls_2017.zip';
    $theurl2 = 'http://www.cftc.gov/files/dea/history/com_disagg_xls_2017.zip';

    file_put_contents("tmpfile.zip", fopen($theurl, 'r'));

    $zip = new ZipArchive;
    $res = $zip->open($filename);
    if ($res === TRUE) {
      $zip->extractTo($destDir);
      $zip->close();
    }

    file_put_contents("tmpfile.zip", fopen($theurl2, 'r'));

    $zip = new ZipArchive;
    $res = $zip->open($filename);
    if ($res === TRUE) {
      $zip->extractTo($destDir);
      $zip->close();
    }

    unlink($filename);


    // If you need to parse XLS files, include php-excel-reader
                
    $Reader = new SpreadsheetReader('c_year.xls');

    $fp = fopen('c_year.csv', 'w');
    $fp2 = fopen('cftc_c.csv', 'w');
    $fp3 = fopen('final_cftc_c.csv', 'w');

    foreach ($Reader as $key => $Row) {
        $key = $key - 2;
        if($key == -1){
          fputcsv($fp2, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio'));
          fputcsv($fp3, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio', 'spot_price'));
          fputcsv($fp, array('', $Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17]));
        }else{        
          fputcsv($fp2, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7]));
          fputcsv($fp, array($key, $Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17]));
          if($Row[0] == "CRUDE OIL, LIGHT SWEET - NEW YORK MERCANTILE EXCHANGE" || $Row[0] == "CRUDE OIL, LIGHT SWEET-WTI - ICE FUTURES EUROPE"){
            fputcsv($fp3, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7], get_spot_price($Row[1],'wti')));
          }
        }
    }

    fclose($fp);
    fclose($fp2);
    fclose($fp3);

    $Reader = new SpreadsheetReader('f_year.xls');

    $fp = fopen('f_year.csv', 'w');
    $fp2 = fopen('cftc_f.csv', 'w');
    $fp3 = fopen('final_cftc_f.csv', 'w');

    foreach ($Reader as $key => $Row) {
        $key = $key - 2;
        if($key == -1){
          fputcsv($fp2, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio'));
          fputcsv($fp3, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio', 'spot_price'));
          fputcsv($fp, array('', $Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17]));
        }else{        
          fputcsv($fp2, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7]));
          fputcsv($fp, array($key, $Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17]));
          if($Row[0] == "CRUDE OIL, LIGHT SWEET - NEW YORK MERCANTILE EXCHANGE" || $Row[0] == "CRUDE OIL, LIGHT SWEET-WTI - ICE FUTURES EUROPE"){
            echo $Row[1]."\n";
            fputcsv($fp3, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7], get_spot_price($Row[1], 'wti')));
          }
        }
    }

    fclose($fp);
    fclose($fp2);
    fclose($fp3);

  }elseif($wday == 1 && $hours == 6 && $minutes == 40){

    echo "ICE Data\n";
    
    $url = 'https://www.theice.com/publicdocs/futures/COTHist2017.csv';

    $data = file_get_contents($url);
    $rows = explode("\n",$data);

    $fp = fopen('ice_parsed.csv', 'w');
    $fp2 = fopen('final_ice.csv', 'w');
    $fp3 = fopen('final_brent_ice.csv', 'w');

    $Row = array();
    $i = 0;
    $last = 0;
    foreach($rows as $line) {
        $Row = str_getcsv($line, ',');

        if($i == 0){
          fputcsv($fp, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio'));
          fputcsv($fp2, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio'));
          fputcsv($fp3, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], 'Swap_net', 'Managed_net', 'other_net', 'Prod_net', 'Swap_ratio', 'Managed_ratio', 'other_ratio', 'Prod_ratio', 'spot_price'));
          $i++;
        }elseif(!empty($Row[0])){

          fputcsv($fp, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7]));

          if($Row[0] == "ICE Brent Crude Futures - ICE Futures Europe" || $Row[0] == "ICE Brent Crude Futures and Options - ICE Futures Europe"){
//            echo $Row[1]."\n";
            $a = get_spot_price($Row[1], 'b');
            if(empty($a)){
              $a = $last;
            }else{
              $last = $a;
            }
            fputcsv($fp3, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7], $a));
            fputcsv($fp2, array($Row[0], $Row[1], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[13], $Row[14], $Row[16], $Row[17], $Row[10]-$Row[11], $Row[13]-$Row[14], $Row[16]-$Row[17], $Row[8]-$Row[9], ($Row[10]-$Row[11])/$Row[7], ($Row[13]-$Row[14])/$Row[7], ($Row[16]-$Row[17])/$Row[7], ($Row[8]-$Row[9])/$Row[7]));
          }
        }

    }

    fclose($fp);
    fclose($fp2);
    fclose($fp3);

  }
?>
