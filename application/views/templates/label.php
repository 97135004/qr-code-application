<!DOCTYPE html>
<html>

<head>
    <title>QR Label</title>
    <style>
        @page {
            margin: 0.4cm 0.4cm;
        }

        html,
        body {
            margin: 0.4cm;
        }
    </style>

</head>


<?php foreach ($stockitem as $item) : ?>

    <body>

        <img src="<?= base_url('assets/img/qr/') . $item['id'] . '.png'; ?>" alt="" width="200" height="200">
        <hr>
        <p>
            <div>Receipt Number</div>
            <div><?= $item['receipt_number']; ?></div>
            <div><?= $item['po_number']; ?></div>
        </p>

    </body>
<?php endforeach; ?>