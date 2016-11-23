<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'setting.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CurlClient.php';

use Goutte\Client;
use tools\CurlClient;
use Jitsu\RegexUtil;
use Simplon\Mysql\Mysql;


for (;;){ 
	$link = connectDb()->fetchRow('SELECT * FROM link WHERE status = :status LIMIT 1',[':status' => 'wating']);
	if (empty($link) === false) {
		parsingLink($link);
	}else{
		error('There are not link for parsing');
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
	$client = new CurlClient();
	$content = $client->parsePage($link['url']);

	if (empty($content)) {
		error('Content is null');
		//return;
	}
	
	if (parse_url($link['url'])['host'] === 'thelabels.ulmart.ru') {
		//collectionLinkThelabels($client, $content, $link['url']);
	}else{
		$colection = collectionLink($client, $content, $link['url']);
		
	}
	$catalog = saveCatalog($client, $content, $link);
	
	

	changeStatusLink($link);
	
	
}

function collectionLink($client, $content, $url)
{
	info('The collection of links on the page: '.$url);
	$links_catalog = $client->parseProperty($content,'link','a.js-gtm-click-menu',$url,null);
	if (empty($links_catalog) === false) {
		info('Found '.count($links_catalog) .' links for catalog');
		foreach ($links_catalog as $link) {
			if (connectDb()->fetchColumn('SELECT id FROM link WHERE url = :url and type = :catalog', array(':url' => trim($link),'catalog' => 'catalog')) === null)
			{
				$data[0] = [
					'url' => $link,
					'type' => 'catalog',
					'created_at' => time(),
					'updated_at' => time(),
				];
				insertTable('link',$data);
				info($link. ' is saved');
			}else{
				//info($link. ' is already saving');
			}
		}
		
	}else{
		error('There are not link for catalog');
	}

	$links_product = $client->parseProperty($content,'link','a.js-gtm-product-click',$url,null);
	var_dump($links_product);
	die;
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
				//info($link. ' is already saving');
			}
		}
		
	}else{
		error('There are not link for product');
	}
	return [
		'catalog' => $links_catalog,
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
				$catalog = connectDb()->fetchColumnMany('SELECT id FROM catalog WHERE title = :title and parent_id is null', array('title' => trim($name)));
			}else{
				$catalog = connectDb()->fetchColumnMany('SELECT id FROM catalog WHERE title = :title and parent_id = :parent', array('title' => trim($name),':parent' => $parent_id));
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
				info($name. ' is already saving');
			}
			$parent_id = (empty($catalog[0]) === false) ? $catalog[0] : null;
		}
		
	}else{
		error('There are not names for catalog');
	}
	//var_dump($name_catalog);
	//die;
}

function changeStatusLink($link)
{
	if ($link['status'] === 'wating') {
		$condr = [
			'id' => $link['id'],		
		];
		$data = [
			'status' => 'parsed',
		];
		updateTable('link', $condr, $data);
		
	}
	info($link['url'].' is parsed');
	
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
	echo "\033[33m".$string."\033[0m".PHP_EOL;
}
?>