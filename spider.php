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
//	die;
}

function parsingLink($link)
{
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
	if ($link['type'] === 'product') {
		
	}
	$colection = collectionLink($client, $content, $link['url']);

	changeStatusLink($link);
	
}

function collectionLink($client, $content, $url)
{
	info('The collection of links on the page: '.$url);
	$links_catalog = $client->parseProperty($content,'link','a.js-gtm-click-menu',$url,null);
	if (empty($links_catalog) === false) {
		info('Found '.count($links_catalog) .' links for catalog');
		foreach ($links_catalog as $link) {
			if (connectDb()->fetchColumn('SELECT id FROM link WHERE url = :url and type = :catalog', array('url' => trim($link),'catalog' => 'catalog')) === null)
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
				info($link. ' is already saving');
			}
		}
		
	}else{
		error('There are not link for catalog');
	}

	$links_product = $client->parseProperty($content,'link','a.js-gtm-product-click',$url,null);
	if (empty($links_product) === false) {
		info('Found '.count($links_product) .' links for product');
		foreach ($links_product as $link) {
			if (connectDb()->fetchColumn('SELECT id FROM link WHERE url = :url and type = :product', array('url' => trim($link),'product' => 'product')) === null)
			{
				$data[0] = [
					'url' => $link,
					'type' => 'product',
					'created_at' => time(),
					'updated_at' => time(),
				];
				insertTable('link',$data);
				info($link. ' is saved');
			}else{
				info($link. ' is already saving');
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

function changeStatusLink($link)
{
	$condr = [
		'id' => $link['id'],		
	];
	$data = [
		'status' => 'parsed',
	];
	updateTable('link', $condr, $data);
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