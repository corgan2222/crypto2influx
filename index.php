<?php 
/**
 *  Fork by Stefan Knaak
 *  - add Worker Hasrate
 *  - fix btc error
 * 
 * */


/**
* 
* Coin Dashboard by Christian Haschek
* https://blog.haschek.at
*
* Donations always welcome
*    BTC: 1ChrisHMgr4DvEVXzAv1vamkviZNLPS7yx
*    ETH: 0x1337C2F18e54d72d696005d030B8eF168a4C0d95
* 
* Read more at
*    https://blog.haschek.at/2017/making-an-awesome-dashboard-for-your-crypto.html
*/

//settings
define('CURRENCY','EUR'); // Can be: EUR/USD/GBP
define('INFLUX_IP','127.0.0.1'); // The IP address of your InfluxDB instance
define('INFLUX_PORT',8072); // The UDP (!) port of your InfluxDB instance


//Wallets
// Enter your ETH and BTC wallet addresses below
// If you only use ETH for example, then delete the btcwallets line
// If you have multiple addresses add them like this
//  $ethwallets[] = '0x69ea6b31ef305d6b99bb2d4c9d99456fa108b02a';
//  $ethwallets[] = '0xce6265f9e675b0a6ed99bd146c2758eef5d08727';
//  $ethwallets[] = '0xc525b4dd2dc308599fc9c76a837a25b6f72d3318';
//------------- Code starts here ------------------------//

// Ethereum stuff

$worthsum = 0;

if(is_array($ethwallets) && count($ethwallets) >= 1)
{
    $ethsellprice = getETHSellPriceKraken();
    sendToDB('ethereumsellprice value='.$ethsellprice);

    $ethminingearnings = 0;
    $hashratesum = 0;

    if($ethsellprice && is_array($ethwallets))
        foreach($ethwallets as $ethwallet)
        {
            $eth = wei2eth(getETHBalance($ethwallet));
            if(!$eth || $eth=='0' || !$ethsellprice) return;
            $worth = ($eth*$ethsellprice);
            $worthsum+=$worth;

            echo "[ETH] $ethwallet: Got: ".money_format('%n', $worth)." ".CURRENCY."\n";
            sendToDB('ethereumworth,addr='.$ethwallet.' value='.$worth);
            sendToDB('ethereumcoins,addr='.$ethwallet.' value='.$eth);
            $hashratesum += getHashRateNanopool($ethwallet);

            #worker Data
            $workerData = getWorkerHashrates($ethwallet);
            if(is_array($workerData))
            {
                foreach($workerData as $wo)
                {
                    $wo_hashrate=floatval($wo["hashrate"]);
                    $h1=floatval($wo["h1"]);
                    $h3=floatval($wo["h3"]);
                    $h6=floatval($wo["h6"]);
                    $h12=floatval($wo["h12"]);
                    $h24=floatval($wo["h24"]);
                    $rating=intval($wo["rating"]);
                    $wo=$wo["id"];
                    echo "[ETH] Worker $wo: $wo_hashrate MH/s\n";
                    sendToDB('ethereumworker,addr='.$ethwallet.',worker='.$wo.'  rating='.$rating.',hashrate='.$wo_hashrate.',h1='.$h1.',h3='.$h3.',h6='.$h6.',h12='.$h12.',h24='.$h24);
                }
            }    

        }

    $ethminingearnings=calculateETHMiningEarnings($hashratesum,'bitcoins','month');
    if(!$ethminingearnings) $ethminingearnings = 0;

    echo "[ETH] Hashrate: $hashratesum MH/s\n";
    sendToDB('ethereumhashrate value='.$hashratesum);

    if($hashratesum >= 1)
    {
        sendToDB('ethereumapproxearnings value='.$ethminingearnings);
        echo "[ETH] Approx mining earnings: ".money_format('%n', $ethminingearnings)." ".CURRENCY."\n";
    }

    
}

// Bitcoin stuff starts here
if(isset($btcwallets)){
    
    if(is_array($btcwallets) && count($btcwallets) >= 1)
    {
        $btcsellprice = getBTCSellPriceKraken();
        sendToDB('btcsellprice value='.$btcsellprice);

        if($btcsellprice && is_array($btcwallets))
            foreach($btcwallets as $btcwallet)
            {
                $btc = satoshi2btc(getBTCBalance($btcwallet));
                if(!$btc || $btc=='0' || !$btcsellprice) return;
                $worth = ($btc*$btcsellprice);
                $worthsum+=$worth;

                echo "[BTC] $btcwallet: Got: ".money_format('%n', $worth)." ".CURRENCY."\n";
                sendToDB('btcworth,addr='.$btcwallet.' value='.$worth);
                sendToDB('btccoins,addr='.$btcwallet.' value='.$btc);
            }
    }
}

sendToDB('combinedworth value='.$worthsum);
echo "[i] Worth of all wallets combined: ".money_format('%n', $worthsum)." ".CURRENCY."\n";



function getETHBalance($address)
{
    $json = json_decode(file_get_contents('https://api.etherscan.io/api?module=account&action=balance&address='.$address.'&tag=latest'),true);
    return $json['result'];
}

function getWorkerHashrates($address)
{
    $json = json_decode(file_get_contents('https://api.nanopool.org/v1/eth/user/'.$address),true);
    #var_dump($json["data"]["workers"]);
    if (is_array($json["data"]["workers"]))
    {
        return $json["data"]["workers"];
    }else
    {
        return false;
    }
        
}




function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, bchexdec($remain)), hexdec($last));
        }
    }

function wei2eth($wei)
{
    return bcdiv($wei,1000000000000000000,18);
}

function getHashRateNanopool($addr)
{
    $json = json_decode(file_get_contents('https://api.nanopool.org/v1/eth/hashrate/'.$addr),true);
    return $json['data'];
}

function calculateETHMiningEarnings($mhs,$currency='dollars',$timespan='month')
{
    $json = json_decode(file_get_contents('https://api.nanopool.org/v1/eth/approximated_earnings/'.$mhs),true);
    return $json['data'][$timespan][$currency];
}

function getETHSellPriceKraken()
{
    $json = json_decode(file_get_contents('https://api.kraken.com/0/public/Ticker?pair=ETH'.CURRENCY),true);
    return $json['result']['XETHZ'.CURRENCY]['c'][0];
}

function sendToDB($data)
{
	$socket = stream_socket_client("udp://".INFLUX_IP.":".INFLUX_PORT."");
	stream_socket_sendto($socket, $data);
	stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
}

function satoshi2btc($satoshi)
{
    return bcdiv($satoshi,100000000,8);
}

function getBTCBalance($addr)
{
    return file_get_contents('https://blockchain.info/q/addressbalance/'.$addr);
}

function getBTCSellPriceKraken()
{
    //if(CURRENCY=='USD')
    //    $json = json_decode(file_get_contents('https://api.kraken.com/0/public/Ticker?pair=XBTUSD'),true);
    //else
    $json = json_decode(file_get_contents('https://api.kraken.com/0/public/Ticker?pair=XBT'.CURRENCY),true);
    return $json['result']['XXBTZ'.CURRENCY]['c'][0];
}