<?php
require_once "Bech32.php";
require_once "Base58.php";
/**
 * Validates a given address.
 *
 * @param string $address
 * @return boolean
 */
function isValid($address, $testnet)
{
    if (isPayToPublicKeyHash($address, $testnet)) {
        return true;
    }

    if (isPayToScriptHash($address, $testnet)) {
        return true;
    }

    if (isBech32($address, $testnet)) {
        return true;
    }

    return false;
}

/**
 * Validates a P2PKH address.
 *
 * @param string $address
 * @return boolean
 */
function isPayToPublicKeyHash($address, $testnet)
{
    $prefix = $testnet ? '1nm' : '1';
    $expr = sprintf('/^[%s][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $prefix);

    if (preg_match($expr, $address) === 1) {
        try {
            return verify58($address);
        } catch (\Throwable $th) {
            return false;
        }
    }
    return false;
}

/**
* Validates a P2SH (segwit) address.
*
* @param string $address
* @param $testnet
* @return boolean
*/
function isPayToScriptHash($address, $testnet)
{
    $prefix = $testnet ? '23' : '3';
    $expr = sprintf('/^[%s][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $prefix);

    if (preg_match($expr, $address) === 1) {
        try {
            return verify58($address);
        } catch (\Throwable $th) {
            return false;
        }
    }
    return false;
}

/**
 * Validates a bech32 (native segwit) address.
 *
 * @param string $address
 * @return boolean
 */
function isBech32($address, $testnet)
{
    $prefix = $testnet ? 'bc|tb' : 'bc';
    $expr = sprintf(
        '/^((%s)(0([ac-hj-np-z02-9]{39}|[ac-hj-np-z02-9]{59})|1[ac-hj-np-z02-9]{8,87}))$/',
        $prefix
    );

    if (preg_match($expr, $address, $match) === 1) {
        try {
            decodeSegwit($match[2], $match[0]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    return false;
}
