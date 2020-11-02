<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Qr extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load member model
        $this->load->model('QR_Outbound_model');

        // Load form validation library
        // $this->load->library('form_validation');

        // Load file helper
        // $this->load->helper('file');
    }

    public function index()
    {
        $data['title'] = 'Quick Print QR Label';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('Receipt/quick_print');
        $this->load->view('templates/footer');
    }

    public function get_data_qr_outbound()
    {
        $list = $this->QR_Outbound_model->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $field) {
            $row = array();
            $row[] = $field->id;
            $row[] = $field->item_number;
            $row[] = $field->stock_item_description;
            $row[] = $field->stock_item_spec;
            $row[] = $field->subinventory;
            $row[] = $field->receipt_number;
            $row[] = $field->lot_number;
            $row[] = $field->receipt_date;
            $row[] = $field->po_number;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->QR_Outbound_model->count_all(),
            "recordsFiltered" => $this->QR_Outbound_model->count_filtered(),
            "data" => $data,
        );

        //output dalam format JSON
        echo json_encode($output);
    }

    public function labels()
    {
        $data['title'] = 'Print QR Label';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Get rows
        $data['qrlabels'] = $this->QR_Outbound_model->getRows();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('Receipt/qrlabel', $data);
        $this->load->view('templates/footer');
    }

    public function import()
    {
        $data = array();
        $itemData = array();

        // If import request is submitted
        if ($this->input->post('importSubmit')) {
            // Form field validation rules
            $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');

            // Validate submitted form data
            if ($this->form_validation->run() == true) {
                $insertCount = $updateCount = $rowCount = $notAddCount = 0;

                // If file uploaded
                if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                    // Load CSV reader library
                    $this->load->library('CSVReader');

                    // Parse data from CSV file
                    $csvData = $this->csvreader->parse_csv($_FILES['file']['tmp_name']);

                    // Insert/update CSV data into database
                    if (!empty($csvData)) {
                        foreach ($csvData as $row) {
                            $rowCount++;

                            // Prepare data for DB insertion
                            $itemData = array(
                                'item_number' => $row['item_number'],
                                'stock_item_description' => $row['stock_item_description'],
                                'stock_item_spec' => $row['stock_item_spec'],
                                'oem_model_#' => $row['oem_model_#'],
                                'oem_part_#' => $row['oem_part_#'],
                                'subinventory' => $row['subinventory'],
                                'locator' => $row['locator'],
                                'lot_number' => $row['lot_number'],
                                'receipt_number' => $row['receipt_number'],
                                'receipt_date' => $row['receipt_date'],
                                'po_number' => $row['po_number'],
                                'quantity' => $row['quantity'],
                                'uom' => $row['uom'],
                                'locator_segment_1' => $row['locator_segment_1'],
                                'locator_segment_2' => $row['locator_segment_2'],
                                'locator_segment_3' => $row['locator_segment_3'],
                                'locator_segment_4' => $row['locator_segment_4'],
                                'locator_segment_5' => $row['locator_segment_5'],
                                'locator_segment_6' => $row['locator_segment_6'],
                                'locator_segment_7' => $row['locator_segment_7'],
                                'locator_segment_8' => $row['locator_segment_8'],
                                'created_by' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array()['email'],
                                'created_date' => date("Y-m-d H:i:s")
                            );

                            // Check whether item_number already exists in the database
                            $con = array(
                                'where' => array(
                                    'item_number' => $row['item_number']
                                ),
                                'returnType' => 'count'
                            );
                            $prevCount = $this->QR_Outbound_model->getRows($con);

                            if ($prevCount > 0) {
                                // Update stock-receipt data
                                $condition = array('item_number' => $row['item_number']);
                                $update = $this->QR_Outbound_model->update($itemData, $condition);

                                if ($update) {
                                    $updateCount++;
                                }
                            } else {
                                // Insert stock-receipt data
                                $insert = $this->QR_Outbound_model->insert($itemData);

                                if ($insert) {
                                    $insertCount++;
                                }
                            }
                        }

                        // Status message with imported data count
                        $notAddCount = ($rowCount - ($insertCount + $updateCount));
                        $successMsg = 'Stock-receipt imported successfully. Total Rows (' . $rowCount . ') | Inserted (' . $insertCount . ') | Updated (' . $updateCount . ') | Not Inserted (' . $notAddCount . ')';
                        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">' . $successMsg . '</div>');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Error on file upload, please try again.</div>');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Invalid file, please select only CSV file.</div>');
            }
        }
        redirect('qr/labels');
    }

    /*
     * Callback function to check file value and type during validation
     */
    public function file_check($str)
    {
        $allowed_mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
            $mime = get_mime_by_extension($_FILES['file']['name']);
            $fileAr = explode('.', $_FILES['file']['name']);
            $ext = end($fileAr);
            if (($ext == 'csv') && in_array($mime, $allowed_mime_types)) {
                return true;
            } else {
                $this->form_validation->set_message('file_check', 'Please select only CSV file to upload.');
                return false;
            }
        } else {
            $this->form_validation->set_message('file_check', 'Please select a CSV file to upload.');
            return false;
        }
    }

    public function create($item_id)
    {
        // Check whether item_number already exists in the database
        $condition = array(
            'where' => array(
                'id' => $item_id
            )
        );

        $data = $this->QR_Outbound_model->getRows($condition);

        $this->load->library('ciqrcode'); // memanggil library qr code

        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/img/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
        $config['white']        = array(70, 130, 180); // array, default is array(0,0,0)

        $this->ciqrcode->initialize($config);

        $image_name = $item_id . '.png'; //set image name based on $item_id

        //data yang akan di jadikan QR CODE
        $params['data'] = 'Item # : ' . $data[0]['item_number'] . "\r\n" .
            'desc. : ' . $data[0]['stock_item_description'] . "\r\n" .
            'spec : ' . $data[0]['stock_item_spec'] . "\r\n" .
            'model # : ' . $data[0]['oem_model_#'] . "\r\n" .
            'part # : ' . $data[0]['oem_part_#'] . "\r\n" .
            'subinventory : ' . $data[0]['subinventory'] . "\r\n" .
            'locator : ' . $data[0]['locator'] . "\r\n" .
            'receipt # : ' . $data[0]['receipt_number'] . "\r\n" .
            'lot # : ' . $data[0]['lot_number'] . "\r\n" .
            'receipt date : ' . $data[0]['receipt_date'] . "\r\n" .
            'PO # : ' . $data[0]['po_number'];

        $params['level'] = 'H'; //H=High
        $params['size'] = 10;
        $params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/img/qr/
        $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

        redirect('qr/labels');
    }

    public function printlabel()
    {
        $this->load->library('pdf'); // memanggil library dompdf

        $customPaper = [0, 0, 198, 283];
        $this->pdf->setPaper($customPaper);

        $this->pdf->load_view('mypdf');
        $this->pdf->render();

        $this->pdf->stream('welcome.pdf', array('Attachment' => 0));
    }

    public function get_data_qrlabels()
    {
        $list = $this->QR_Outbound_model->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->user_nama;
            $row[] = $field->user_email;
            $row[] = $field->user_alamat;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->QR_Outbound_model->count_all(),
            "recordsFiltered" => $this->QR_Outbound_model->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    public function printlabel2($item_id)
    {
        // Check whether item_number already exists in the database
        $condition = array(
            'where' => array(
                'id' => $item_id
            )
        );

        $data = $this->QR_Outbound_model->getRows($condition);
        $data['stockitem'] = $this->QR_Outbound_model->getRows($condition);

        $this->load->library('ciqrcode'); // memanggil library qr code

        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/img/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
        $config['white']        = array(70, 130, 180); // array, default is array(0,0,0)

        $this->ciqrcode->initialize($config);

        $image_name = $item_id . '.png'; //set image name based on $item_id

        //data yang akan di jadikan QR CODE
        $params['data'] = 'Item # : ' . $data[0]['item_number'] . "\r\n" .
            'desc. : ' . $data[0]['stock_item_description'] . "\r\n" .
            'spec : ' . $data[0]['stock_item_spec'] . "\r\n" .
            'model # : ' . $data[0]['oem_model_#'] . "\r\n" .
            'part # : ' . $data[0]['oem_part_#'] . "\r\n" .
            'subinventory : ' . $data[0]['subinventory'] . "\r\n" .
            'locator : ' . $data[0]['locator'] . "\r\n" .
            'receipt # : ' . $data[0]['receipt_number'] . "\r\n" .
            'lot # : ' . $data[0]['lot_number'] . "\r\n" .
            'receipt date : ' . $data[0]['receipt_date'] . "\r\n" .
            'PO # : ' . $data[0]['po_number'];

        $params['level'] = 'H'; //H=High
        $params['size'] = 10;
        $params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/img/qr/
        $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

        $this->load->library('pdf'); // memanggil library dompdf

        $customPaper = [0, 0, 198, 283];
        $this->pdf->setPaper($customPaper);
        $this->pdf->set_option('isRemoteEnabled', true);

        $this->pdf->load_view('templates/label', $data);
        $this->pdf->render();

        $this->pdf->stream('LabeltoPrint.pdf', array('Attachment' => 0));
    }
}
