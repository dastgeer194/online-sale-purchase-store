<?php require_once('header.php'); ?>

<div class="page">
    <div class="container">
        <div class="row">            
            <div class="col-md-12">
                <p>
                    <h3 style="margin-top:20px;"><?php echo LANG_VALUE_121; ?></h3>
                    <?php if(isset($_SESSION['customer'])): ?>
                    <a href="dashboard.php" class="btn btn-success"><?php echo LANG_VALUE_91; ?></a>
                    <?php else: ?>
                    <a href="index.php" class="btn btn-success"><?php echo LANG_VALUE_85; ?></a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
