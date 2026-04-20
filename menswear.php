<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_product_category = $row['banner_product_category'];
}
?>



<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_product_category; ?>)">
    <div class="inner">
        <h1><?php echo LANG_VALUE_50; ?> Welcome to Men's Wear</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- Coming Soon Section -->
                <div class="coming-soon" style="text-align: center; padding: 100px 0;">
                    <div class="coming-soon-icon" style="font-size: 80px; margin-bottom: 30px;">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <h2 style="font-size: 36px; margin-bottom: 20px; color: #333;">Coming Soon</h2>
                    <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
                        We're working hard to bring you something amazing. Please check back later!
                    </p>
                    <div class="countdown" style="font-size: 24px; font-weight: bold; color: #e74c3c;">
                        Stay Tuned!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>