<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'setting.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CurlClient.php';

use Goutte\Client;
use tools\CurlClient;
use Jitsu\RegexUtil;
use Simplon\Mysql\Mysql;


for (;;){ 
	$link = connectDb()->fetchRow('SELECT * FROM link WHERE status != :status1 and status != :status2 and type = :type LIMIT 1',[':status1' => 'crawled', ':status2' => 'error-crawled', ':type' => 'product']);
	
	if (empty($link) === false) {
		parsingLink($link);
	}else{
		error('There are not product for crawling');
	}
	
}

function parsingLink($link)
{
	//$link['url'] = 'http://thelabels.ulmart.ru/fashion/goods/16?rootCategory=97375';
	if (empty($link['url'])) {
		error('Url is null');
		return;
	}
	if (empty($link['type'])) {
		error('Type is null');
		return;
	}
	info('Parsing url: '.$link['url']);
	$client = new CurlClient();
	$content = $client->parsePage($link['url']);

	if (empty($content)) {
		error('Content is null');
		changeStatusLink($link,'error-crawled');	
		return;
	}

	if (parse_url($link['url'])['host'] === 'thelabels.ulmart.ru') {
		//collectionLinkThelabels($client, $content, $link['url']);
	}else{
		//$colection = collectionLink($client, $content, $link['url']);
		parsePropertyProduct($client, $content, $link);
	}
	changeStatusLink($link,'crawled');
	
}

function collectionLink($client, $content, $url)
{
	info('The collection of links on the page: '.$url);
	$links_product = $client->parseProperty($content,'link','a.js-gtm-product-click',$url,null);
	if (empty($links_product) === false) {
		info('Found '.count($links_product) .' links for product');
		foreach ($links_product as $link) {
			$parse_product_link = parse_url($link);
			$fix_link = $parse_product_link['scheme'].'://'.$parse_product_link['host'].$parse_product_link['path'];

			if (connectDb()->fetchColumn('SELECT id FROM link WHERE url = :url and type = :product', array(':url' => trim($fix_link),'product' => 'product')) === null)
			{
				$data[0] = [
					'url' => $fix_link,
					'type' => 'product',
					'created_at' => time(),
					'updated_at' => time(),
				];
				insertTable('link',$data);
				info($fix_link. ' is saved');
			}else{
				//info($fix_link. ' is already saving');
			}
		}
		
	}else{
		error('There are not link for product');
	}
	return [
		'product' => $links_product,
	];
	
}

function collectionLinkThelabels($client, $content, $url)
{
	var_dump($url);
	$token = $client->parseProperty($content,'attribute','meta[name = "_csrf"]',$url,'content');
	if (empty($token[0])) {
		error('The token is not found');
		return;
	}
	var_dump($token[0]);
	$parse_url = parse_url($url);
	var_dump($parse_url);
	$request_url = 'http://thelabels.ulmart.ru/fashion/goodscontent/16';//$parse_url['scheme'].'://'.$parse_url['host'].str_replace('goods', 'goodscontent', $parse_url['path']);
	var_dump($request_url);
	parse_str($parse_url['query'],$parse_query);
	var_dump($parse_query['rootCategory']);
	$refer = 'http://thelabels.ulmart.ru/fashion/goods/16?rootCategory=97375&availability=1&pageNum=1&pageSize=60&offset=0&viewType=WM&listType=Sm&sort=NEW&action=ALL&goodIds=&superPrice=&costMin=110&costMax=25150&brands=&colors=&sizes=&seasons=&shadows=&plantings=&colorWashes=&shapes=&sleeves=&styles=&ages=&tops=&linings=&insulations=&heels=&soles=&sports=&lengths='; //$parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'].'?rootCategory='.$parse_query['rootCategory'].'&availability=1&pageNum=1&pageSize=60&offset=0&viewType=WM&listType=Sm&sort=NEW&action=ALL&goodIds=&superPrice=&costMin=110&costMax=25150&brands=&colors=&sizes=&seasons=&shadows=&plantings=&colorWashes=&shapes=&sleeves=&styles=&ages=&tops=&linings=&insulations=&heels=&soles=&sports=&lengths=';
	var_dump($refer);
	//die;

	$ckfile = tempnam("cookiefile", "CURLCOOKIE");
    $useragent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/5.0.342.3 Safari/533.2';

    $f = fopen('cookiefile/log.txt', 'w'); // file to write request 
    $f2 = fopen('cookiefile/log2.txt', 'w'); // file to write request 

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    //curl_setopt($ch, CURLOPT_COOKIE, $ckfile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
    curl_setopt($ch, CURLOPT_POST, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, 'rootCategory='.$parse_query['rootCategory'].'&availability=1&pageNum=1&pageSize=60&offset=0&viewType=WM&listType=Sm&sort=NEW&action=ALL&goodIds=&superPrice=&costMin=110&costMax=25150&brands=&colors=&sizes=&seasons=&shadows=&plantings=&colorWashes=&shapes=&sleeves=&styles=&ages=&tops=&linings=&insulations=&heels=&soles=&sports=&lengths=');
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'rootCategory=97375&availability=1&pageNum=1&pageSize=60&offset=0&viewType=WM&listType=Sm&sort=NEW&action=ALL&goodIds=&superPrice=&costMin=110&costMax=25150&brands=&colors=&sizes=&seasons=&shadows=&plantings=&colorWashes=&shapes=&sleeves=&styles=&ages=&tops=&linings=&insulations=&heels=&soles=&sports=&lengths=');
    curl_setopt($ch, CURLOPT_VERBOSE,true);
    curl_setopt($ch, CURLOPT_STDERR ,$f);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	//"Host: thelabels.ulmart.ru",
    	//"Connection: keep-alive",
    	//"Content-Length: 310",
    	"Origin: http://thelabels.ulmart.ru",
    	"X-CSRF-TOKEN: ".$token[0],
    	//"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
    	//"Accept: */*",
    	"X-Requested-With: XMLHttpRequest",
    	//"Accept-Encoding:gzip, deflate",
    	//"Accept-Language: en-US,en;q=0.8",
    	
    	));
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_REFERER, $refer);
    
    

    $webpage = curl_exec($ch);
    //var_dump(curl_getinfo($ch,CURLINFO_COOKIELIST));        
    var_dump($webpage);
    curl_close($ch);



}

function saveCatalog($client, $content, $link)
{
	$names_catalog = $client->parseProperty($content,'string','a.b-crumbs__link span',null,null);
	$links_catalog = $client->parseProperty($content,'link','a.b-crumbs__link',$link['url'],null);

	if (empty($names_catalog) === false) {
		info('Found '.count($names_catalog) .' name of catalog');
		
		foreach ($names_catalog as $n => $name) {
			if (parse_url($link['url'])['host'] === 'thelabels.ulmart.ru') {
				if ($n === 0) {
					$name = 'Одежда и обувь';
				}
			}
			if ($n === 0) {
				$catalog = connectDb()->fetchColumnMany('SELECT id FROM catalog WHERE title = :title and parent_id is null', array(':title' => trim($name)));
			}else{
				$catalog = connectDb()->fetchColumnMany('SELECT id FROM catalog WHERE title = :title and parent_id = :parent', array(':title' => trim($name),':parent' => $parent_id));
			}
			
			if ($catalog === null)
			{
				$data[0] = [
					'title' => trim($name),
					'parent_id' => ($n === 0) ? null : $parent_id,
					'url' => (empty($links_catalog[$n]) === false) ? $links_catalog[$n] : null,
					'created_at' => time(),
					'updated_at' => time(),
				];
				$catalog = insertTable('catalog',$data);
				info($name. ' is saved');
			}else{
				//info($name. ' is already saving');
			}
			$parent_id = (empty($catalog[0]) === false) ? $catalog[0] : null;
		}
		return $catalog;
	}else{
		error('There are not names for catalog');
	}
	//var_dump($name_catalog);
	//die;
}

function parsePropertyProduct($client, $content, $link)
{

	$catalog = saveCatalog($client, $content, $link);
	if (CRAWLERMODE === 'full') {
		collectionLink($client, $content, $link['url']);
	}
	

	if (empty($catalog[0])) {
		error('Catalog id is not found');
		return;
	}

	$properties =  connectDb()->fetchRowMany('SELECT * FROM property_setting WHERE type = :type', array(':type' => 'product'));

	if (empty($properties)) {
		error('The property is null');
	}

	$title = $client->parseProperty($content,'string',$properties[0]['value'],null,null);

	$art = $client->parseProperty($content,'string',$properties[1]['value'],null,null);

	if (empty(trim($art[0]))) {
		error('The art is not found');
		return;
	}
	
	if (connectDb()->fetchColumn('SELECT id FROM product WHERE art = :art', array(':art' => trim($art[0]))) === null)
	{

		$data[0] = [
			'url' => $link['url'],
			'art' => trim($art[0]),
			'title' => trim($title[0]),
			'catalog_id' => $catalog[0],
			'status' => 'parsed',
			'created_at' => time(),
			'updated_at' => time(),
		];
		$product = insertTable('product',$data);
		info($art[0]. ' is saved');
		if (empty($product[0]) === false) {
			foreach ($properties as $property) {
				switch ($property['title']) {
					
					case 'description':
						$description = $client->parseProperty($content,'string',$property['value'],null,null);
						saveProperty($description,$product[0],$property['id']);
						
						break;

					case 'price':
						$price = $client->parseProperty($content,'attribute',$property['value'],null,'content');
						saveProperty($price,$product[0],$property['id']);
						break;

					case 'currency':
						$currency = $client->parseProperty($content,'attribute',$property['value'],null,'content');
						saveProperty($currency,$product[0],$property['id']);
						break;

					
					case 'bigimage':
						$bigimage = $client->parseProperty($content,'attribute',$property['value'],null,'src');
						saveProperty($bigimage,$product[0],$property['id']);
						break;

					case 'otherimage':
						$otherimage = $client->parseProperty($content,'link',$property['value'],$link['url'],null);
						saveProperty($otherimage,$product[0],$property['id']);
						break;

					case 'smallimage':
						$smallimage = $client->parseProperty($content,'attribute',$property['value'],null,'src');
						saveProperty($smallimage,$product[0],$property['id']);
						break;

					case 'property_title':
						$property_title = $client->parseProperty($content,'string',$property['value'],null,null);
						saveProperty($property_title,$product[0],$property['id']);
						break;

					case 'property_value':
						$property_value = $client->parseProperty($content,'string',$property['value'],null,null);
						saveProperty($property_value,$product[0],$property['id']);
						break;

					case 'property_full_title':
						$property_full_title = $client->parseProperty($content,'string',$property['value'],null,null);
						saveProperty($property_full_title,$product[0],$property['id']);
						break;

					case 'property_full_value':
						$property_full_value = $client->parseProperty($content,'string',$property['value'],null,null);
						saveProperty($property_full_value,$product[0],$property['id']);
						break;
					
					default:
						# code...
						break;
				}
			}
		}

	}else{
		info($art[0]. ' is already parsed');
	}

	
	
}

function saveProperty($properties,$pid,$propid)
{
	if (empty($properties) === false) {
		foreach ($properties as $property) {
			$data[0] = [
				'object_id' => $pid,
				'property_id' => $propid,
				'value' => rtrim($property),
				'created_at' => time(),
				'updated_at' => time(),
			];
			insertTable('property',$data);
			//info($property.' is saved');
		}
	}else{
		error('Properties are null');
	}
}

function changeStatusLink($link,$status)
{
	$condr = [
		'id' => $link['id'],		
	];
	$data = [
		'status' => $status,
	];
	updateTable('link', $condr, $data);
	info($link['url'].' is crawled');
}

//database
function connectDb()
{
	require 'config/db.php';
	
	return new Mysql(
	    $config['host'],
	    $config['user'],
	    $config['password'],
	    $config['database']
	);
}

function insertTable($table,$data)
{
	return connectDb()->insertMany($table, $data);	
}
function updateTable($table, $condr, $data)
{
	connectDb()->update($table, $condr, $data);	
}
//database

function error($string)
{
	echo "\033[31m".$string."\033[0m".PHP_EOL;
}

function success($string)
{
	echo "\033[32m".$string."\033[0m".PHP_EOL;
}

function info($string)
{
	if (MODE === 'test') {
		echo "\033[33m".$string."\033[0m".PHP_EOL;
	}
}
?>