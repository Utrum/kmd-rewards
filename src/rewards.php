<?php

$explorer_url = "https://kmdexplorer.io/";
$addr = $_GET['address'];
$url = $explorer_url ."insight-api-komodo/addr/" .$addr ."/utxo?noCache=1";
$timeout = 10;

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
curl_close($ch);

$utxos = json_decode($result);

$KOMODO_ENDOFERA = 7777777;
$LOCKTIME_THRESHOLD = 500000000;

$rewards = 0;
$balance = 0;

foreach ($utxos as $utxo) {
  $amount = $utxo->satoshis;
  $height = $utxo->height;
  $txid = $utxo->txid;
  $balance += $amount;

  if ($amount >= 1000000000) {
    $url = $explorer_url ."insight-api-komodo/tx/" .$txid;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $tx = json_decode($result);
    $tiptime = $tx->time;
    $locktime = $tx->locktime;

    $timestampDiff = gettimeofday()["sec"] - $locktime - 777;
    $hoursPassed = floor($timestampDiff / 3600);
    $minutesPassed = floor(($timestampDiff - ($hoursPassed * 3600)) / 60);
    $secondsPassed = $timestampDiff - ($hoursPassed * 3600) - ($minutesPassed * 60);
    $timestampDiffMinutes = $timestampDiff / 60;

    if ($height < $KOMODO_ENDOFERA && $locktime >= $LOCKTIME_THRESHOLD) {
      if ($timestampDiffMinutes >= 60) {
        if ($height >= 1000000 && $timestampDiffMinutes > (31 * 24 * 60)) {
          $timestampDiffMinutes = 31 * 24 * 60;
        } else {
          if ($timestampDiffMinutes > 365 * 24 * 60) {
            $timestampDiffMinutes = 365 * 24 * 60;
          }
        }
      }
      $timestampDiffMinutes -= 59;
      $rewards += ($amount / 10512000) * $timestampDiffMinutes;
    }
  }
}

$response['address'] = $addr;
$response['balance'] = $balance;
$response['rewards'] = round($rewards, 0);
$response['totalBalance'] = round($balance + $rewards, 0);

echo json_encode($response);

?>
