<?php foreach ($errors as $value): ?>
    <div class="alert alert-danger">
        <p><?php echo $value ?></p>
    </div>
<?php endforeach; ?>
<?php foreach ($warnings as $value): ?>
    <div class="alert alert-warning">
        <p><?php echo $value ?></p>
    </div>
<?php endforeach; ?>
<?php foreach ($infos as $value): ?>
    <div class="alert alert-info">
        <p><?php echo $value ?></p>
    </div>
<?php endforeach; ?>
<?php foreach ($success as $value): ?>
    <div class="alert alert-success">
        <p><?php echo $value ?></p>
    </div>
<?php endforeach; ?>