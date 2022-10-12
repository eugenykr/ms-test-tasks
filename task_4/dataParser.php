<?php
$url= 'https://bills.ru';
//$html = file_get_contents($url);

/**
 * Browser emulation using CURL
 */
$headers = array(
    'cache-control: max-age=0',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
    'sec-fetch-user: ?1',
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
    'x-compress: null',
    'sec-fetch-site: none',
    'sec-fetch-mode: navigate',
    'accept-encoding: deflate, br',
    'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$html = curl_exec($ch);
curl_close($ch);

/**
 * Create DOM model
 */
$dom = new DOMDocument();
$dom->loadHTML($html);
$dom->preserveWhiteSpace = false;
$xpath = new DOMXPath($dom);
$news = $dom->getElementById("bizon_api_news_list");
$insertValuesArr = array();
$monthsArr = array (
    'янв' => 'Jan',
    'фев' => 'Feb',
    'мар' => 'Mar',
    'апр' => 'Apr',
    'мая' => 'May',
    'июн' => 'Jun',
    'июл' => 'Jul',
    'авг' => 'Aug',
    'сен' => 'Sep',
    'окт' => 'Oct',
    'ноя' => 'Nov',
    'дек' => 'Dec',
);

/**
 * Iterating over DOM model nodes
 */
foreach ($news->getElementsByTagName('tr') as $child) {

    $title = $child->childNodes->item(3)->childNodes->item(1)->nodeValue;
    $url = $child->childNodes->item(3)->childNodes->item(1)->getAttribute('href');

    $dateString = trim($child->childNodes->item(1)->nodeValue);
    $date = str_ireplace(
        array_keys($monthsArr),
        array_values($monthsArr),
        $dateString
    );
    $timestamp = strtotime($date . ' ' . date('Y'));

    $dtm = new DateTime();
    $dtm->setTimestamp($timestamp);
    $date = $dtm->format('Y-m-d H:i:s');

    $insertValuesArr[] = "($date, '$title', '$url')";
}

/**
 * Create table
 */

$tableName = 'bills_ru_events';
try {
    $dbh = new PDO("mysql:dbname=test;host=localhost", "root", "test");
}
catch (\PDOException $ex)
{
    echo  $ex->getMessage();
}

$dbh->exec(
    "CREATE TABLE IF NOT EXISTS".$tableName." (
          id int PRIMARY KEY AUTO_INCREMENT,
          date DATETIME,
          title VARCHAR(230),
          url VARCHAR(240),
          CONSTRAINT url UNIQUE (url)
        );"
);

/**
 *  Add data to the database
 */
$query =
    "INSERT INTO '".$tableName."' ('date', 'title', 'url') 
            VALUES ".implode(",", $insertValuesArr);

$this->db->exec($query);
