<?php
ob_start();
session_start();
include("../../admin/inc/config.php");
include("../../admin/inc/functions.php");
// Getting all language variables into array as global variable
$i=1;
$statement = $pdo->prepare("SELECT * FROM tbl_language");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	define('LANG_VALUE_'.$i,$row['lang_value']);
	$i++;
}
?>
<?php
if( !isset($_REQUEST['msg']) ) {
	list($customer_data, $customer_errors) = barqora_validate_checkout_customer($_POST);

	if(empty($_POST['transaction_info'])) {
		header('location: ../../checkout.php');
		exit;
	} elseif(!empty($customer_errors) || !isset($_SESSION['cart_p_id'])) {
		header('location: ../../checkout.php');
		exit;
	} else {
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
	    						'bank_transaction_info' => $_POST['transaction_info'],
	    						'payment_method' => 'Bank Deposit',
	    						'payment_status' => 'Pending',
	    						'shipping_status' => 'Pending',
	    						'payment_id' => $payment_id
	    					));

	    barqora_clear_cart();

	    header('location: ../../payment_success.php');
	    exit;
	}
}
?>
