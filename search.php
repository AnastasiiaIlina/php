<?php
defined('SHOP') or die('Hacking attempt!');
$query = $db->safesql($_POST['query']);
$listsearch = '';
$search = $query ? "AND (title LIKE '%{$query}%' OR productcode LIKE '%{$query}%')" : "AND popular = '1'";
$products = $db->multi_query("SELECT * FROM `products` WHERE status = '0' {$search} ORDER by `sort` ASC", 1);

if($products){
	foreach($products as $row){
		if($row['currency'] == 'EUR'){
			$price =  sprintf("%0.2f", $row['price']*$curency);
			$box_price = $row['price']*$curency*$row['count'];
		} else {
			$price = $row['price'];
			$box_price = $row['price']*$row['count'];
		}

        $ds_row = '';
        if ($discount = $db->multi_query("SELECT * FROM `discount` WHERE product_id = '{$row['id']}' ORDER BY id ASC LIMIT 2", 1)) {
            foreach ($discount as $dis) {
                $ds_row .= '<tr>
                    <td>от ' . $dis['minbox'] . 'ящ</td>
                    <td>' . round($box_price - $box_price * $dis['discount']/100) . 'грн/ящ</td>
                </tr>';
            }
        }

        if($row AND $query){
       		$listsearch .= "<li>
				<div align=\"center\" class=\"search-result-image\">
					<img src=\"https://cups-service.com.ua/uploads/products/thumb_{$row['image']}\" width=\"35\" height=\"35\">
				</div>
				<div class=\"block-title-price\">
					<a href=\"#\" onclick=\"Ajax.page('/review{$row['id']}'); $('#block-search-result').hide();\">{$row['title']}</a>
				</div>
			</li>";
    	}

        $string['discount'] = '
                    <div class="clearfix"></div>
                    <b class="tit">Скидки</b>
                    <table class="table">
                        <tbody>
                            ' . $ds_row . '
                        </tbody>
                    </table>';
           
			 
        $html->set('products', 
            '<div class="a">'array'('{title}' => $row['title'],
            '{image}' => '/uploads/products/'.$row['image'],
            '{id}' => $row['id'],
            '{price}' => $price,
            '{box-price}' => $box_price,
            '{box-count}' => $row['count'],
            '{formula}' => $string['discount'],
            '{productcode}' => ($row['productcode'] ? '<div class="product-code">Код товара: <b>' . $row['productcode'] . '</b></div>' : '<div class="product-code">Код товара отсуцтвует</div>')
		)</div>', 'products');
	}
}

echo json_encode(array(
	'result' => $html->get['products'] ? $html->get['products'] : 'Ничего не найдено!',
	'search' => $listsearch
));
die;
?>