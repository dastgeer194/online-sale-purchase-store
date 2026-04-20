<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_checkout = $row['banner_checkout'];
}
?>

<?php
if(!isset($_SESSION['cart_p_id'])) {
    header('location: cart.php');
    exit;
}

$cart_data = barqora_get_cart_data();
$checkout_customer = barqora_get_checkout_customer_defaults();
$shipping_country = '';
if(isset($_SESSION['customer']['cust_country'])) {
    $shipping_country = $_SESSION['customer']['cust_country'];
}
$shipping_cost = barqora_get_shipping_cost($pdo, $shipping_country);
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_checkout; ?>)">
    <div class="overlay"></div>
    <div class="page-banner-inner">
        <h1><?php echo LANG_VALUE_22; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 class="special"><?php echo LANG_VALUE_26; ?></h3>
                <div class="cart">
                    <table class="table table-responsive">
                        <tr>
                            <th><?php echo LANG_VALUE_7; ?></th>
                            <th><?php echo LANG_VALUE_8; ?></th>
                            <th><?php echo LANG_VALUE_47; ?></th>
                            <th><?php echo LANG_VALUE_157; ?></th>
                            <th><?php echo LANG_VALUE_158; ?></th>
                            <th><?php echo LANG_VALUE_159; ?></th>
                            <th><?php echo LANG_VALUE_55; ?></th>
                            <th class="text-right"><?php echo LANG_VALUE_82; ?></th>
                        </tr>
                        <?php
                        $table_total_price = 0;
                        ?>
                        <?php for($i=1;$i<=count($cart_data['cart_p_id']);$i++): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>
                                <img src="assets/uploads/<?php echo $cart_data['cart_p_featured_photo'][$i]; ?>" alt="">
                            </td>
                            <td><?php echo $cart_data['cart_p_name'][$i]; ?></td>
                            <td><?php echo $cart_data['cart_size_name'][$i]; ?></td>
                            <td><?php echo $cart_data['cart_color_name'][$i]; ?></td>
                            <td><?php echo LANG_VALUE_1; ?><?php echo $cart_data['cart_p_current_price'][$i]; ?></td>
                            <td><?php echo $cart_data['cart_p_qty'][$i]; ?></td>
                            <td class="text-right">
                                <?php
                                $row_total_price = $cart_data['cart_p_current_price'][$i]*$cart_data['cart_p_qty'][$i];
                                $table_total_price = $table_total_price + $row_total_price;
                                ?>
                                <?php echo LANG_VALUE_1; ?><?php echo $row_total_price; ?>
                            </td>
                        </tr>
                        <?php endfor; ?>           
                        <tr>
                            <th colspan="7" class="total-text"><?php echo LANG_VALUE_81; ?></th>
                            <th class="total-amount"><?php echo LANG_VALUE_1; ?><?php echo $table_total_price; ?></th>
                        </tr>
                        <tr>
                            <td colspan="7" class="total-text"><?php echo LANG_VALUE_84; ?></td>
                            <td class="total-amount"><?php echo LANG_VALUE_1; ?><?php echo $shipping_cost; ?></td>
                        </tr>
                        <tr>
                            <th colspan="7" class="total-text"><?php echo LANG_VALUE_82; ?></th>
                            <th class="total-amount">
                                <?php
                                $final_total = $table_total_price+$shipping_cost;
                                ?>
                                <?php echo LANG_VALUE_1; ?><?php echo $final_total; ?>
                            </th>
                        </tr>
                    </table> 
                </div>

                <div class="billing-address">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 class="special">Customer Details</h3>
                            <p style="margin-bottom:20px;">
                                Place your order as a guest. Name, phone, address and city are required. Email is optional.
                            </p>
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label for="checkout_customer_name">Name *</label>
                                    <input type="text" class="form-control" id="checkout_customer_name" value="<?php echo htmlspecialchars($checkout_customer['customer_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label for="checkout_customer_phone">Phone *</label>
                                    <input type="text" class="form-control" id="checkout_customer_phone" value="<?php echo htmlspecialchars($checkout_customer['customer_phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label for="checkout_customer_email">Email</label>
                                    <input type="email" class="form-control" id="checkout_customer_email" value="<?php echo htmlspecialchars($checkout_customer['customer_email'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label for="checkout_customer_city">City *</label>
                                    <input type="text" class="form-control" id="checkout_customer_city" value="<?php echo htmlspecialchars($checkout_customer['customer_city'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-sm-12 form-group">
                                    <label for="checkout_customer_address">Address *</label>
                                    <textarea class="form-control" id="checkout_customer_address" rows="5"><?php echo htmlspecialchars($checkout_customer['customer_address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h3 class="special"><?php echo LANG_VALUE_33; ?></h3>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for=""><?php echo LANG_VALUE_34; ?> *</label>
                                    <select name="payment_method" class="form-control select2" id="advFieldsStatus">
                                        <option value=""><?php echo LANG_VALUE_35; ?></option>
                                        <option value="PayPal"><?php echo LANG_VALUE_36; ?></option>
                                        <option value="Bank Deposit"><?php echo LANG_VALUE_38; ?></option>
                                        <option value="Cash on Delivery">Cash on Delivery</option>
                                    </select>
                                </div>

                                <form class="paypal" action="<?php echo BASE_URL; ?>payment/paypal/payment_process.php" method="post" id="paypal_form" target="_blank">
                                    <input type="hidden" name="cmd" value="_xclick" />
                                    <input type="hidden" name="no_note" value="1" />
                                    <input type="hidden" name="lc" value="UK" />
                                    <input type="hidden" name="currency_code" value="USD" />
                                    <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
                                    <input type="hidden" name="final_total" value="<?php echo $final_total; ?>">
                                    <input type="hidden" name="customer_name" value="">
                                    <input type="hidden" name="customer_phone" value="">
                                    <input type="hidden" name="customer_email" value="">
                                    <input type="hidden" name="customer_address" value="">
                                    <input type="hidden" name="customer_city" value="">
                                    <div class="col-md-12 form-group">
                                        <input type="submit" class="btn btn-primary" value="<?php echo LANG_VALUE_46; ?>" name="form1">
                                    </div>
                                </form>

                                <form action="payment/bank/init.php" method="post" id="bank_form">
                                    <input type="hidden" name="amount" value="<?php echo $final_total; ?>">
                                    <input type="hidden" name="customer_name" value="">
                                    <input type="hidden" name="customer_phone" value="">
                                    <input type="hidden" name="customer_email" value="">
                                    <input type="hidden" name="customer_address" value="">
                                    <input type="hidden" name="customer_city" value="">
                                    <div class="col-md-12 form-group">
                                        <label for=""><?php echo LANG_VALUE_43; ?></label><br>
                                        <?php
                                        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
                                        $statement->execute();
                                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($result as $row) {
                                            echo nl2br($row['bank_detail']);
                                        }
                                        ?>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <label for=""><?php echo LANG_VALUE_44; ?> <br><span style="font-size:12px;font-weight:normal;">(<?php echo LANG_VALUE_45; ?>)</span></label>
                                        <textarea name="transaction_info" class="form-control" cols="30" rows="10"></textarea>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <input type="submit" class="btn btn-primary" value="<?php echo LANG_VALUE_46; ?>" name="form3">
                                    </div>
                                </form>

                                <form action="payment/cod/init.php" method="post" id="cod_form">
                                    <input type="hidden" name="amount" value="<?php echo $final_total; ?>">
                                    <input type="hidden" name="customer_name" value="">
                                    <input type="hidden" name="customer_phone" value="">
                                    <input type="hidden" name="customer_email" value="">
                                    <input type="hidden" name="customer_address" value="">
                                    <input type="hidden" name="customer_city" value="">
                                    <div class="col-md-12 form-group">
                                        <div class="alert alert-info" style="margin-bottom:15px;">
                                            For Cash on Delivery orders, you may open and inspect the parcel before making payment to the rider.
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <input type="submit" class="btn btn-primary" value="Place Cash on Delivery Order" name="form_cod">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cart-buttons">
                    <ul>
                        <li><a href="cart.php" class="btn btn-primary"><?php echo LANG_VALUE_21; ?></a></li>
                    </ul>
                </div>

				<div class="clear"></div>

            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>
