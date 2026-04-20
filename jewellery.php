<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_product_category = $row['banner_product_category'];
}
?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $top[] = $row['tcat_id'];
    $top1[] = $row['tcat_name'];
}

$statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $mid[] = $row['mcat_id'];
    $mid1[] = $row['mcat_name'];
    $mid2[] = $row['tcat_id'];
}

$statement = $pdo->prepare("SELECT * FROM tbl_end_category");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $end[] = $row['ecat_id'];
    $end1[] = $row['ecat_name'];
    $end2[] = $row['mcat_id'];
}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_product_category; ?>)">
    <div class="inner">
        <h1><?php echo LANG_VALUE_50; ?> Welcome to Jewellery</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- Coming Soon Section -->
                <div class="coming-soon" style="text-align: center; padding: 100px 0;">
                    <div class="coming-soon-icon" style="font-size: 80px; margin-bottom: 30px;">
                        <i class="fa fa-diamond"></i>
                    </div>
                    <h2 style="font-size: 36px; margin-bottom: 20px; color: #333;">Coming Soon</h2>
                    <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
                        We're crafting exquisite jewellery pieces for you. Our stunning collection will be available soon!
                    </p>
                    <div class="countdown" style="font-size: 24px; font-weight: bold; color: #e74c3c;">
                        Stay Tuned for Sparkling Deals!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>