<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"><?= $title; ?></h1>

    <!-- Content -->
    <div class="row">
        <div class="col-lg-6">
            <?= $this->session->flashdata('message'); ?>

            <h2>Stock Items</h2>

            <div class="row">
                <!-- Import link -->
                <div class="col-md-12 head">
                    <div class="float-right">
                        <a href="javascript:void(0);" class="btn btn-success" onclick="formToggle('importFrm');"><i class="plus"></i> Import</a>
                    </div>
                </div>

                <!-- File upload form -->
                <div class="col-md-12" id="importFrm" style="display: none;">
                    <form action="<?= base_url('qr/import'); ?>" method="post" enctype="multipart/form-data">
                        <input type="file" name="file" />
                        <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT">
                    </form>
                </div>

                <!-- Data list table -->
                <div class="container-xl table-responsive overflow-auto">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Action</th>
                                <th>#ID</th>
                                <th>Item Number</th>
                                <th>Stock Item Description</th>
                                <th>Stock Item Specification</th>
                                <th>OEM Model</th>
                                <th>Parts Number</th>
                                <th>Subinventory/Locator</th>
                                <th>Receipt Number</th>
                                <th>Lot Number</th>
                                <th>Receipt Date</th>
                                <th>PO Information</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($qrlabels)) {
                                foreach ($qrlabels as $row) { ?>
                                    <tr>
                                        <td>
                                            <a class='badge badge-danger' href="<?= base_url('qr/create/') . $row['id']; ?>">Create QR Code</a>
                                            <a class='badge badge-success' href="<?= base_url('qr/printlabel/'); ?>" target="blank">Cetak</a>
                                            <a class='badge badge-warning' href="<?= base_url('qr/printlabel2/') . $row['id']; ?>" target="blank">Print</a>
                                        </td>
                                        <td><?= $row['id']; ?></td>
                                        <td><?= $row['item_number']; ?></td>
                                        <td><?= $row['stock_item_description']; ?></td>
                                        <td><?= $row['stock_item_spec']; ?></td>
                                        <td><?= $row['oem_model_#']; ?></td>
                                        <td><?= $row['oem_part_#']; ?></td>
                                        <td><?= $row['subinventory'] . '/' . $row['locator']; ?></td>
                                        <td><?= $row['receipt_number']; ?></td>
                                        <td><?= $row['lot_number']; ?></td>
                                        <td><?= $row['receipt_date']; ?></td>
                                        <td><?= $row['po_number']; ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="11">No item(s) found...</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formToggle(ID) {
            var element = document.getElementById(ID);
            if (element.style.display === "none") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }
    </script>
    <!-- End of Content -->

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->