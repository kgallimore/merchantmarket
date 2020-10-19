<?php

require_once 'btcvalidator/AddressValidator.php';
require_once 'crossSite.php';

function easy_validate($address){
    if (isValid($address, false)){
        return "Valid";
    }
    return "Invalid";
}

function validate($address){
    try {
        $decoded = decodeBase58($address);
    } catch (Exception $e) {
        echo $e;
    }

    $d1 = hash("sha256", substr($decoded,0,21), true);
    $d2 = hash("sha256", $d1, true);

    if(substr_compare($decoded, $d2, 21, 4)){
        throw new \RuntimeException("bad digest");
    }
    return true;
}
function decodeBase58($input) {
    $alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

    $out = array_fill(0, 25, 0);
    for($i=0, $iMax = strlen($input); $i< $iMax; $i++){
        if(($p=strpos($alphabet, $input[$i]))===false){
            throw new \RuntimeException("invalid character found");
        }
        $c = $p;
        for ($j = 25; $j--; ) {
            $c += (int)(58 * $out[$j]);
            $out[$j] = (int)($c % 256);
            $c /= 256;
            $c = (int)$c;
        }
        if($c !== 0){
            throw new \RuntimeException("address too long");
        }
    }

    $result = "";
    foreach($out as $val){
        $result .= chr($val);
    }

    return $result;
}
function validate_address($address, $include_bech){
    if($include_bech){
        if (preg_match("/^(bc1)[a-zA-HJ-NP-Z0-9]{25,69}$/", $address)) {
            $json = file_get_contents("https://chain.api.btc.com/v3/address/" . $address);
            if($json === FALSE){
                return false;
            }
            try {
                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return false;
            }
            if(array_key_exists('err_no', $decoded)){
                return false;
            }
            return true;
        }
        if(preg_match("/^[13][a-km-zA-HJ-NP-Z0-9]{26,33}$/", $address)) {
            if (validate($address)){
                return true;
            }

            return false;
        }

    }
    return preg_match("/^[13][a-km-zA-HJ-NP-Z0-9]{26,33}$/", $address) && validate($address);
}

function get_received_external($address){
    if(easy_validate($address)){
        if (preg_match("/^(bc1)[a-zA-HJ-NP-Z0-9]{25,69}$/", $address)) {
            $json = file_get_contents("https://chain.api.btc.com/v3/address/" . $address);
            if($json === FALSE){
                return "Invalid address";
            }
            try {
                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return $e;
            }
            if(array_key_exists('err_no', $decoded)){
                return "Invalid address";
            }
            return $decoded['data']['received'];
        }
        if (preg_match("/^[13][a-km-zA-HJ-NP-Z0-9]{26,33}$/", $address)){
            $json = file_get_contents("https://blockchain.info/balance?active=" . $address);
            if($json === FALSE){
                return "Invalid address";
            }

            try {
                $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return $e;
            }
            return $decoded[$address]['total_received'];
        }
    }
    return "Invalid address";
}

function get_balance_merchant($merchant){
    return file_get_contents('http://localhost:5000/wallet_balance?merchant_id=' . $merchant);
}

function create_wallet($merchant_id, $secret_key, $output){
    $url = "http://localhost:5000/create_wallet";
    $data = array('merchant_id' => $merchant_id, 'secret_key' => $secret_key, 'output' => $output, 'coin_type' => 'bitcoin');
    return submit_form($url, $data);
}

function create_crypto_payment($merchant_id, $customer_id, $request_id, $coin_type, $amount){
    $url = "http://localhost:5000/create_payment";
    $data = array('merchant_id' => $merchant_id, 'customer_id' => $customer_id, 'request_id' => $request_id, 'coin_type' => $coin_type, 'amount' => $amount);
    return submit_form($url, $data);
}


