<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"><?= $title; ?></h1>

    <!-- Content -->
    <table id="table" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>id</th>
                <th>item_number</th>
                <th>stock_item_description</th>
                <th>stock_item_spec</th>
            </tr>
        </thead>
        <tbody>
        </tbody>

        <tfoot>
            <tr>
                <th>id</th>
                <th>item_number</th>
                <th>stock_item_description</th>
                <th>stock_item_spec</th>
            </tr>
        </tfoot>
    </table>

    <script src="<?= base_url('assets/'); ?>vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?= base_url('assets/'); ?>vendor/datatables/datatables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.css">

    <script type="text/javascript">
        var table;
        $(document).ready(function() {
            //datatables
            table = $('#table').DataTable({

                "processing": true,
                "serverSide": true,
                "order": [],

                "ajax": {
                    "url": "<?php echo site_url('qr/get_data_qr_outbound') ?>",
                    "type": "POST"
                },


                "columnDefs": [{
                    "targets": [0],
                    "orderable": false,
                }, ],

            });

        });
    </script>
    <!-- End of Content -->

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->