<?php
function get_ext($pdo,$fname)
{

	$up_filename=$_FILES[$fname]["name"];
	$file_basename = substr($up_filename, 0, strripos($up_filename, '.')); // strip extention
	$file_ext = substr($up_filename, strripos($up_filename, '.')); // strip name
	return $file_ext;
}

function ext_check($pdo,$allowed_ext,$my_ext) 
{

	$arr1 = array();
	$arr1 = explode("|",$allowed_ext);	
	$count_arr1 = count(explode("|",$allowed_ext));	

	for($i=0;$i<$count_arr1;$i++)
	{
		$arr1[$i] = '.'.$arr1[$i];
	}
	

	$str = '';
	$stat = 0;
	for($i=0;$i<$count_arr1;$i++)
	{
		if($my_ext == $arr1[$i])
		{
			$stat = 1;
			break;
		}
	}

	if($stat == 1)
		return true; // file extension match
	else
		return false; // file extension not match
}


function get_ai_id($pdo,$tbl_name) 
{
	$statement = $pdo->prepare("SHOW TABLE STATUS LIKE '$tbl_name'");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row)
	{
		$next_id = $row['Auto_increment'];
	}
	return $next_id;
}

function barqora_get_checkout_customer_defaults()
{
	$customer = array();
	if(isset($_SESSION['customer']) && is_array($_SESSION['customer'])) {
		$customer = $_SESSION['customer'];
	}

	$defaults = array(
		'customer_name' => '',
		'customer_phone' => '',
		'customer_email' => '',
		'customer_address' => '',
		'customer_city' => ''
	);

	$field_map = array(
		'customer_name' => array('cust_s_name', 'cust_b_name', 'cust_name'),
		'customer_phone' => array('cust_s_phone', 'cust_b_phone', 'cust_phone'),
		'customer_email' => array('cust_email'),
		'customer_address' => array('cust_s_address', 'cust_b_address', 'cust_address'),
		'customer_city' => array('cust_s_city', 'cust_b_city', 'cust_city')
	);

	foreach($field_map as $target_field => $source_fields) {
		foreach($source_fields as $source_field) {
			if(isset($customer[$source_field])) {
				$value = trim((string) $customer[$source_field]);
				if($value !== '') {
					$defaults[$target_field] = $value;
					break;
				}
			}
		}
	}

	return $defaults;
}

function barqora_validate_checkout_customer($input)
{
	$data = array(
		'customer_name' => isset($input['customer_name']) ? trim((string) $input['customer_name']) : '',
		'customer_phone' => isset($input['customer_phone']) ? trim((string) $input['customer_phone']) : '',
		'customer_email' => isset($input['customer_email']) ? trim((string) $input['customer_email']) : '',
		'customer_address' => isset($input['customer_address']) ? trim((string) $input['customer_address']) : '',
		'customer_city' => isset($input['customer_city']) ? trim((string) $input['customer_city']) : ''
	);

	$errors = array();

	if($data['customer_name'] === '') {
		$errors[] = 'Name is required.';
	}

	if($data['customer_phone'] === '') {
		$errors[] = 'Phone is required.';
	}

	if($data['customer_address'] === '') {
		$errors[] = 'Address is required.';
	}

	if($data['customer_city'] === '') {
		$errors[] = 'City is required.';
	}

	if($data['customer_email'] !== '' && filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL) === false) {
		$errors[] = 'Please provide a valid email address.';
	}

	return array($data, $errors);
}

function barqora_get_shipping_cost($pdo, $country_id = '')
{
	if($country_id !== '') {
		$statement = $pdo->prepare("SELECT * FROM tbl_shipping_cost WHERE country_id=?");
		$statement->execute(array($country_id));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			return (float) $row['amount'];
		}
	}

	$statement = $pdo->prepare("SELECT * FROM tbl_shipping_cost_all WHERE sca_id=1");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		return (float) $row['amount'];
	}

	return 0;
}

function barqora_get_cart_data()
{
	$session_keys = array(
		'cart_p_id',
		'cart_size_id',
		'cart_size_name',
		'cart_color_id',
		'cart_color_name',
		'cart_p_qty',
		'cart_p_current_price',
		'cart_p_name',
		'cart_p_featured_photo'
	);

	$cart_data = array();

	foreach($session_keys as $session_key) {
		$cart_data[$session_key] = array();
		if(!isset($_SESSION[$session_key]) || !is_array($_SESSION[$session_key])) {
			continue;
		}

		$i = 0;
		foreach($_SESSION[$session_key] as $value) {
			$i++;
			$cart_data[$session_key][$i] = $value;
		}
	}

	return $cart_data;
}

function barqora_clear_cart()
{
	$session_keys = array(
		'cart_p_id',
		'cart_size_id',
		'cart_size_name',
		'cart_color_id',
		'cart_color_name',
		'cart_p_qty',
		'cart_p_current_price',
		'cart_p_name',
		'cart_p_featured_photo'
	);

	foreach($session_keys as $session_key) {
		unset($_SESSION[$session_key]);
	}
}

function barqora_create_order_payment($pdo, $customer_data, $payment_data)
{
	$payment_id = $payment_data['payment_id'];

	$statement = $pdo->prepare("INSERT INTO tbl_payment (
							customer_id,
							customer_name,
							customer_email,
							customer_phone,
							customer_address,
							customer_city,
							payment_date,
							txnid,
							paid_amount,
							card_number,
							card_cvv,
							card_month,
							card_year,
							bank_transaction_info,
							payment_method,
							payment_status,
							shipping_status,
							payment_id
						) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
	$statement->execute(array(
						isset($_SESSION['customer']['cust_id']) ? (int) $_SESSION['customer']['cust_id'] : 0,
						$customer_data['customer_name'],
						$customer_data['customer_email'],
						$customer_data['customer_phone'],
						$customer_data['customer_address'],
						$customer_data['customer_city'],
						$payment_data['payment_date'],
						$payment_data['txnid'],
						$payment_data['paid_amount'],
						$payment_data['card_number'],
						$payment_data['card_cvv'],
						$payment_data['card_month'],
						$payment_data['card_year'],
						$payment_data['bank_transaction_info'],
						$payment_data['payment_method'],
						$payment_data['payment_status'],
						$payment_data['shipping_status'],
						$payment_id
					));

	$cart_data = barqora_get_cart_data();
	$product_quantities = array();

	$statement = $pdo->prepare("SELECT p_id, p_qty FROM tbl_product");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		$product_quantities[$row['p_id']] = $row['p_qty'];
	}

	$total_items = count($cart_data['cart_p_id']);
	for($i=1; $i<=$total_items; $i++) {
		$statement = $pdo->prepare("INSERT INTO tbl_order (
							product_id,
							product_name,
							size,
							color,
							quantity,
							unit_price,
							payment_id
						) VALUES (?,?,?,?,?,?,?)");
		$statement->execute(array(
						$cart_data['cart_p_id'][$i],
						$cart_data['cart_p_name'][$i],
						$cart_data['cart_size_name'][$i],
						$cart_data['cart_color_name'][$i],
						$cart_data['cart_p_qty'][$i],
						$cart_data['cart_p_current_price'][$i],
						$payment_id
					));

		$current_qty = isset($product_quantities[$cart_data['cart_p_id'][$i]]) ? $product_quantities[$cart_data['cart_p_id'][$i]] : 0;
		$final_quantity = $current_qty - $cart_data['cart_p_qty'][$i];
		$product_quantities[$cart_data['cart_p_id'][$i]] = $final_quantity;

		$statement = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
		$statement->execute(array($final_quantity, $cart_data['cart_p_id'][$i]));
	}

	return $payment_id;
}
