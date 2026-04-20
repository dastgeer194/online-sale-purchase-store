<?php require_once('header.php'); ?>

<?php
$error_message = '';
if(isset($_POST['form1'])) {
    $valid = 1;
    $cust_email = '';

    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'Subject can not be empty\n';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'Message can not be empty\n';
    }
    $subject_text = strip_tags($_POST['subject_text']);
    $message_text = strip_tags($_POST['message_text']);

    $payment_row = array();
    $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=?");
    $statement->execute(array($_POST['payment_id']));
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        $payment_row = $row;
    }

    if(empty($payment_row)) {
        $valid = 0;
        $error_message .= 'Order can not be found\n';
    } else {
        $cust_email = $payment_row['customer_email'];
        if($cust_email == '') {
            $valid = 0;
            $error_message .= 'This order has no email address\n';
        }
    }

    if($valid == 1) {
        // Getting Admin Email Address
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $admin_email = $row['contact_email'];
        }

        $order_detail = '';
        if($payment_row['payment_method'] == 'PayPal'):
            $payment_details = '
Transaction Id: '.$payment_row['txnid'].'<br>
            ';
        elseif($payment_row['payment_method'] == 'Stripe'):
            $payment_details = '
Transaction Id: '.$payment_row['txnid'].'<br>
Card number: '.$payment_row['card_number'].'<br>
Card CVV: '.$payment_row['card_cvv'].'<br>
Card Month: '.$payment_row['card_month'].'<br>
Card Year: '.$payment_row['card_year'].'<br>
            ';
        elseif($payment_row['payment_method'] == 'Bank Deposit'):
            $payment_details = '
Transaction Details: <br>'.$payment_row['bank_transaction_info'];
        elseif($payment_row['payment_method'] == 'Cash on Delivery'):
            $payment_details = '
Customer will pay in cash on delivery. The parcel may be opened and inspected before payment.
            ';
        endif;

        $customer_name = htmlspecialchars($payment_row['customer_name'], ENT_QUOTES, 'UTF-8');
        $customer_phone = htmlspecialchars($payment_row['customer_phone'], ENT_QUOTES, 'UTF-8');
        $customer_email_safe = htmlspecialchars($payment_row['customer_email'], ENT_QUOTES, 'UTF-8');
        $customer_address = nl2br(htmlspecialchars($payment_row['customer_address'], ENT_QUOTES, 'UTF-8'));
        $customer_city = htmlspecialchars($payment_row['customer_city'], ENT_QUOTES, 'UTF-8');

        $order_detail .= '
Customer Name: '.$customer_name.'<br>
Customer Phone: '.$customer_phone.'<br>
Customer Email: '.$customer_email_safe.'<br>
Customer Address: '.$customer_address.'<br>
Customer City: '.$customer_city.'<br>
Payment Method: '.$payment_row['payment_method'].'<br>
Payment Date: '.$payment_row['payment_date'].'<br>
Payment Details: <br>'.$payment_details.'<br>
Paid Amount: '.$payment_row['paid_amount'].'<br>
Payment Status: '.$payment_row['payment_status'].'<br>
Shipping Status: '.$payment_row['shipping_status'].'<br>
Payment Id: '.$payment_row['payment_id'].'<br>
        ';

        $i=0;
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $i++;
            $order_detail .= '
<br><b><u>Product Item '.$i.'</u></b><br>
Product Name: '.$row['product_name'].'<br>
Size: '.$row['size'].'<br>
Color: '.$row['color'].'<br>
Quantity: '.$row['quantity'].'<br>
Unit Price: '.$row['unit_price'].'<br>
            ';
        }

        if((int) $_POST['cust_id'] > 0) {
            $statement = $pdo->prepare("INSERT INTO tbl_customer_message (subject,message,order_detail,cust_id) VALUES (?,?,?,?)");
            $statement->execute(array($subject_text,$message_text,$order_detail,$_POST['cust_id']));
        }

        // sending email
        $to_customer = $cust_email;
        $message = '
<html><body>
<h3>Message: </h3>
'.$message_text.'
<h3>Order Details: </h3>
'.$order_detail.'
</body></html>
';
        $headers = 'From: ' . $admin_email . "\r\n" .
                   'Reply-To: ' . $admin_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=ISO-8859-1\r\n";

        // Sending email to admin                  
        mail($to_customer, $subject_text, $message, $headers);
        
        $success_message = 'Your email to customer is sent successfully.';

    }
}
?>
<?php
if($error_message != '') {
    echo "<script>alert('".$error_message."')</script>";
}
if($success_message != '') {
    echo "<script>alert('".$success_message."')</script>";
}

$order_filter_definitions = array(
    'completed-orders' => array(
        'label' => 'Completed Orders',
        'query' => "SELECT * FROM tbl_payment WHERE payment_status=? ORDER BY id DESC",
        'params' => array('Completed')
    ),
    'completed-shipping' => array(
        'label' => 'Completed Shipping',
        'query' => "SELECT * FROM tbl_payment WHERE shipping_status=? ORDER BY id DESC",
        'params' => array('Completed')
    ),
    'pending-orders' => array(
        'label' => 'Pending Orders',
        'query' => "SELECT * FROM tbl_payment WHERE payment_status=? ORDER BY id DESC",
        'params' => array('Pending')
    ),
    'pending-shipping' => array(
        'label' => 'Pending Shipping (Order Completed)',
        'query' => "SELECT * FROM tbl_payment WHERE payment_status=? AND shipping_status=? ORDER BY id DESC",
        'params' => array('Completed', 'Pending')
    )
);

$active_order_filter = '';
$active_order_filter_label = 'All Orders';
$order_query = "SELECT * FROM tbl_payment ORDER BY id DESC";
$order_query_params = array();

if(isset($_GET['filter']) && isset($order_filter_definitions[$_GET['filter']])) {
    $active_order_filter = $_GET['filter'];
    $active_order_filter_label = $order_filter_definitions[$active_order_filter]['label'];
    $order_query = $order_filter_definitions[$active_order_filter]['query'];
    $order_query_params = $order_filter_definitions[$active_order_filter]['params'];
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1><?php echo $active_order_filter == '' ? 'View Orders' : $active_order_filter_label; ?></h1>
	</div>
    <?php if($active_order_filter != ''): ?>
    <div class="content-header-right">
        <a href="order.php" class="btn btn-primary btn-sm">View All Orders</a>
    </div>
    <?php endif; ?>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-striped">
			<thead>
			    <tr>
			        <th>SL</th>
                    <th>Customer Details</th>
			        <th>Product Details</th>
                    <th>
                    	Payment Information
                    </th>
                    <th>Paid Amount</th>
                    <th>Payment Status</th>
                    <th>Shipping Status</th>
			        <th>Action</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare($order_query);
            	$statement->execute($order_query_params);
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
            		?>
					<tr class="<?php if($row['payment_status']=='Pending'){echo 'bg-r';}else{echo 'bg-g';} ?>">
	                    <td><?php echo $i; ?></td>
	                    <td>
                            <b>Id:</b> <?php echo $row['customer_id'] ? $row['customer_id'] : 'Guest'; ?><br>
                            <b>Name:</b><br> <?php echo htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8'); ?><br>
                            <b>Phone:</b><br> <?php echo htmlspecialchars($row['customer_phone'], ENT_QUOTES, 'UTF-8'); ?><br>
                            <b>Email:</b><br> <?php echo $row['customer_email'] != '' ? htmlspecialchars($row['customer_email'], ENT_QUOTES, 'UTF-8') : 'Not provided'; ?><br>
                            <b>City:</b><br> <?php echo htmlspecialchars($row['customer_city'], ENT_QUOTES, 'UTF-8'); ?><br>
                            <b>Address:</b><br> <?php echo nl2br(htmlspecialchars($row['customer_address'], ENT_QUOTES, 'UTF-8')); ?><br><br>
                            <?php if($row['customer_email'] != ''): ?>
                            <a href="#" data-toggle="modal" data-target="#model-<?php echo $i; ?>"class="btn btn-warning btn-xs" style="width:100%;margin-bottom:4px;">Send Message</a>
                            <?php else: ?>
                            <span class="btn btn-default btn-xs" style="width:100%;margin-bottom:4px;cursor:default;">No Email Provided</span>
                            <?php endif; ?>
                            <div id="model-<?php echo $i; ?>" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
											<h4 class="modal-title" style="font-weight: bold;">Send Message</h4>
										</div>
										<div class="modal-body" style="font-size: 14px">
											<form action="" method="post">
                                                <input type="hidden" name="cust_id" value="<?php echo $row['customer_id']; ?>">
                                                <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
												<table class="table table-bordered">
													<tr>
														<td>Subject</td>
														<td>
                                                            <input type="text" name="subject_text" class="form-control" style="width: 100%;">
														</td>
													</tr>
                                                    <tr>
                                                        <td>Message</td>
                                                        <td>
                                                            <textarea name="message_text" class="form-control" cols="30" rows="10" style="width:100%;height: 200px;"></textarea>
                                                        </td>
                                                    </tr>
													<tr>
														<td></td>
														<td><input type="submit" value="Send Message" name="form1"></td>
													</tr>
												</table>
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										</div>
									</div>
								</div>
							</div>
                        </td>
                        <td>
                           <?php
                           $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
                           $statement1->execute(array($row['payment_id']));
                           $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                           foreach ($result1 as $row1) {
                                echo '<b>Product Name:</b> '.$row1['product_name'];
                                echo '<br>(<b>Size:</b> '.$row1['size'];
                                echo ', <b>Color:</b> '.$row1['color'].')';
                                echo '<br>(<b>Quantity:</b> '.$row1['quantity'];
                                echo ', <b>Unit Price:</b> '.$row1['unit_price'].')';
                                echo '<br><br>';
                           }
                           ?>
                        </td>
                        <td>
                        	<?php if($row['payment_method'] == 'PayPal'): ?>
                        		<b>Payment Method:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>Payment Id:</b> <?php echo $row['payment_id']; ?><br>
                        		<b>Date:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>Transaction Id:</b> <?php echo $row['txnid']; ?><br>
                        	<?php elseif($row['payment_method'] == 'Stripe'): ?>
                        		<b>Payment Method:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>Payment Id:</b> <?php echo $row['payment_id']; ?><br>
								<b>Date:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>Transaction Id:</b> <?php echo $row['txnid']; ?><br>
                        		<b>Card Number:</b> <?php echo $row['card_number']; ?><br>
                        		<b>Card CVV:</b> <?php echo $row['card_cvv']; ?><br>
                        		<b>Expire Month:</b> <?php echo $row['card_month']; ?><br>
                        		<b>Expire Year:</b> <?php echo $row['card_year']; ?><br>
                        	<?php elseif($row['payment_method'] == 'Bank Deposit'): ?>
                        		<b>Payment Method:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>Payment Id:</b> <?php echo $row['payment_id']; ?><br>
								<b>Date:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>Transaction Information:</b> <br><?php echo $row['bank_transaction_info']; ?><br>
                        	<?php elseif($row['payment_method'] == 'Cash on Delivery'): ?>
                        		<b>Payment Method:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>Payment Id:</b> <?php echo $row['payment_id']; ?><br>
								<b>Date:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>Delivery Note:</b> The parcel may be opened and inspected before payment.<br>
                        	<?php endif; ?>
                        </td>
                        <td><?php echo $row['paid_amount']; ?></td>
                        <td>
                            <?php echo $row['payment_status']; ?>
                            <br><br>
                            <?php
                                if($row['payment_status']=='Pending'){
                                    ?>
                                    <a href="order-change-status.php?id=<?php echo $row['id']; ?>&task=Completed" class="btn btn-warning btn-xs" style="width:100%;margin-bottom:4px;">Make Completed</a>
                                    <?php
                                }
                            ?>
                        </td>
                        <td>
                            <?php echo $row['shipping_status']; ?>
                            <br><br>
                            <?php
                            if($row['payment_status']=='Completed') {
                                if($row['shipping_status']=='Pending'){
                                    ?>
                                    <a href="shipping-change-status.php?id=<?php echo $row['id']; ?>&task=Completed" class="btn btn-warning btn-xs" style="width:100%;margin-bottom:4px;">Make Completed</a>
                                    <?php
                                }
                            }
                            ?>
                        </td>
	                    <td>
                            <a href="#" class="btn btn-danger btn-xs" data-href="order-delete.php?id=<?php echo $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete" style="width:100%;">Delete</a>
	                    </td>
	                </tr>
            		<?php
            	}
            	?>
            </tbody>
          </table>
        </div>
      </div>
  

</section>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                Are you sure want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>
