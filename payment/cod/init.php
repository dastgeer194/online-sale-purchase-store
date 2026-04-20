<?php
ob_start();
session_start();
include("../../admin/inc/config.php");
include("../../admin/inc/functions.php");

if(!isset($_SESSION['cart_p_id'])) {
	header('location: ../../checkout.php');
	exit;
}

list($customer_data, $customer_errors) = barqora_validate_checkout_customer($_POST);

if(!empty($customer_errors)) {
	header('location: ../../checkout.php');
	exit;
}

$payment_date = date('Y-m-d H:i:s');
$payment_id = time();

barqora_create_order_payment($pdo, $customer_data, array(
					'payment_date' => $payment_date,
					'txnid' => '',
					'paid_amount' => $_POST['amount'],
					'card_number' => '',
					'card_cvv' => '',
					'card_month' => '',
					'card_year' => '',
					'bank_transaction_info' => '',
					'payment_method' => 'Cash on Delivery',
					'payment_status' => 'Pending',
					'shipping_status' => 'Pending',
					'payment_id' => $payment_id
				));

barqora_clear_cart();

header('location: ../../payment_success.php');
exit;
?>
