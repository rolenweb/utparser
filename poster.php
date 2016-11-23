<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'setting.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CurlClient.php';


use Simplon\Mysql\Mysql;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;





for (;;){ 
	$woocommerce = setClient();

	$product = connectDb()->fetchRow('SELECT * FROM product WHERE status = :status LIMIT 1',[':status' => 'parsed']);
	if (empty($product) === false) {
		info('Try product ID: '.$product['id']);
		$catalog_tree = getCatalogTree($product['catalog_id']);
		
		if (empty($catalog_tree)) {
			error('Tree is null');
			break;
		}
		$pid = null;
		foreach ($catalog_tree as $cid => $cname) {
			$curent_catalog = saveCatalog($woocommerce,$cname,$pid);		
			if (empty($curent_catalog)) {
				changeStatusProduct($product,'error');
				break;
			}
			$pid = $curent_catalog['id'];
			if ($cid == $product['catalog_id']) {
				$current_product = saveProduct($woocommerce,$product,$pid);
				if (empty($current_product) === false) {
					changeStatusProduct($product,'posted');
					info('Product art: '.$product['art'].' is loaded');

				}else{
					changeStatusProduct($product,'error');
					error('Product is not loaded');
				}
			}
		}
	}else{
		error('There are not product for posting');
		die;
	}
	//die;
}

function saveProduct($woocommerce,$product,$pid)
{
	$properties = listProperty($product['id']);
	$data = createData($properties,$product,$pid);
	try {
		return $woocommerce->post('products', $data);
	}catch (HttpClientException $e) {
	    error($e->getMessage()); // Error message.
	    //$e->getRequest(); // Last request data.
	    //$e->getResponse(); // Last response data.
	    return;
	}
	
}

function saveCatalog($woocommerce,$name,$pid = null)
{
	info($name.' checking...');
	try{
		$out = $woocommerce->get('products/categories',[
			'search' => $name,		
			'parent' => (empty($pid) === false) ? $pid : 0,
		]);
		if (empty($out[0]) === false) {
			return $out[0];
		}
	}catch (HttpClientException $e) {
		error($e->getMessage()); // Error message.
	    return;
	}
	
	
	try {
		$out = $woocommerce->post('products/categories', [
			'name' => $name,
			'parent' => $pid,
		]);
		info($out['name'].' is saved');
		return $out;
	}catch (HttpClientException $e) {
	    error($e->getMessage()); // Error message.
	    return;
	}
	
	
}

function getCatalogTree($id)
{
	$out = [];
	$list = connectDb()->fetchRow('
		SELECT catalog.id as cid, catalog.title as ctitle,  c1.id as cid1, c1.title as ctitle1, c2.id as cid2, c2.title as ctitle2, c3.id as cid3, c3.title as ctitle3, c4.id as cid4, c4.title as ctitle4, c5.id as cid5, c5.title as ctitle5, c6.id as cid6, c6.title as ctitle6
		FROM catalog
		LEFT JOIN catalog c1
		ON catalog.parent_id=c1.id
		LEFT JOIN catalog c2
		ON c1.parent_id=c2.id
		LEFT JOIN catalog c3
		ON c2.parent_id=c3.id
		LEFT JOIN catalog c4
		ON c3.parent_id=c4.id
		LEFT JOIN catalog c5
		ON c4.parent_id=c5.id
		LEFT JOIN catalog c6
		ON c5.parent_id=c6.id
		WHERE catalog.id = :id
		',[
			':id' => $id
		]);
	if (empty($list)) {
		error('Catalog tree is null');
		return;
	}	

	$ids = [];
	$titles = [];
	foreach ($list as $key => $item) {
		if (empty($item) === false) {
			switch ($key) {
				case 'cid':
					$ids[] = $item;
					break;
				case 'cid1':
					$ids[] = $item;
					break;
				case 'cid2':
					$ids[] = $item;
					break;
				case 'cid3':
					$ids[] = $item;
					break;
				case 'cid4':
					$ids[] = $item;
					break;
				case 'cid5':
					$ids[] = $item;
					break;
				case 'cid6':
					$ids[] = $item;
					break;

				case 'ctitle':
					$titles[] = $item;
					break;
				case 'ctitle1':
					$titles[] = $item;
					break;
				case 'ctitle2':
					$titles[] = $item;
					break;
				case 'ctitle3':
					$titles[] = $item;
					break;
				case 'ctitle4':
					$titles[] = $item;
					break;
				case 'ctitle5':
					$titles[] = $item;
					break;
				case 'ctitle6':
					$titles[] = $item;
					break;
			}
		}
		
	}
	$out = array_combine($ids,$titles);
	ksort($out);
	return $out;
}

function createData($properties,$product,$cid)
{
	$out = [
		'name' => $product['title'],
		'sku' => $product['art'],
		'categories' => [
	        [
	            'id' => $cid
	        ],
	        
	    ],
	];

	if (empty($properties['data'])) {
		error('Properties is null');
		return;
	}
	
	foreach ($properties['data'] as $item) {
		switch ($item['title']) {
			case 'description':
				$out['description'] = trim($item['value']);
				break;
			case 'price':
				$out['regular_price'] = trim($item['value']);
				break;
			case 'bigimage':
				$out['images'] = [
					[
			            'src' => trim($item['value']),
			            'position' => 0
			        ],
				];
				break;
		}
	}
	return $out;
}

function listProperty($pid)
{
	$out = [];
	$product_properties = productPropery($pid);	
	if (empty($product_properties)) {
		error('Product properties are null');
		return;
	}
	$out['data'] = $product_properties;
	
	$full_property = fullProperty($pid);

	if (empty($full_property) === false) {
		$out['property'] = $full_property;
	}else{
		$short_property = shortProperty($pid);
		if (empty($short_property) === false) {
			$out['property'] = $short_property;
		}
	}
	return $out;
}

function productPropery($id)
{
	return connectDb()->fetchRowMany('
			SELECT property.value as value, property_setting.title as title
			FROM property
			LEFT JOIN property_setting
			ON property.property_id=property_setting.id
			
			WHERE property.object_id = :id and (property_setting.title = :title1 or property_setting.title = :title2 or property_setting.title = :title3 or property_setting.title = :title4 or property_setting.title = :title5 or property_setting.title = :title6 or property_setting.title = :title7)
			',[
				':id' => $id,
				':title1' => 'title',
				':title2' => 'description',
				':title3' => 'art',
				':title4' => 'price',
				':title5' => 'currency',
				':title6' => 'bigimage',
				':title7' => 'smallimage',

		]);
}

function shortProperty($id)
    {
        $out = [];

        $list_name = connectDb()->fetchRowMany('
			SELECT property.value as value
			FROM property
			LEFT JOIN property_setting
			ON property.property_id=property_setting.id
			
			WHERE property.object_id = :id and property_setting.title = :property_title
			',[
				':id' => $id,
				':property_title' => 'property_title'
		]);

		$list_value = connectDb()->fetchRowMany('
			SELECT property.value as value
			FROM property
			LEFT JOIN property_setting
			ON property.property_id=property_setting.id
			
			WHERE property.object_id = :id and property_setting.title = :property_value
			',[
				':id' => $id,
				':property_value' => 'property_value'
		]);
        

        if (empty($list_name)) {
            return $out;
        }
        foreach ($list_name as $n_name => $name) {
            $out[] = [
                'name' => $name['value'],
                'value' => (empty($list_value[$n_name]['value']) === false) ? trim($list_value[$n_name]['value']) : null,
                
            ];
        }
        return $out;
}

function fullProperty($id)
    {
        $out = [];

        $list_name = connectDb()->fetchRowMany('
			SELECT property.value as value
			FROM property
			LEFT JOIN property_setting
			ON property.property_id=property_setting.id
			
			WHERE property.object_id = :id and property_setting.title = :property_title
			',[
				':id' => $id,
				':property_title' => 'property_full_title'
		]);

		$list_value = connectDb()->fetchRowMany('
			SELECT property.value as value
			FROM property
			LEFT JOIN property_setting
			ON property.property_id=property_setting.id
			
			WHERE property.object_id = :id and property_setting.title = :property_value
			',[
				':id' => $id,
				':property_value' => 'property_full_value'
		]);
        

        if (empty($list_name)) {
            return $out;
        }
        foreach ($list_name as $n_name => $name) {
            $out[] = [
                'name' => $name['value'],
                'value' => (empty($list_value[$n_name]['value']) === false) ? trim($list_value[$n_name]['value']) : null,
                
            ];
        }
        return $out;
}

function setClient()
{
	return new Client(
	    WOOCOMMERCE_URl, 
	    WOOCOMMERCE_KEY, 
	    WOOCOMMERCE_SECRET,
	     [
	        'wp_api' => true,
	        'version' => 'wc/v1',
	    ]
	);
}

function changeStatusProduct($product,$status)
{
	$condr = [
		'id' => $product['id'],		
	];
	$data = [
		'status' => $status,
	];
	updateTable('product', $condr, $data);
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