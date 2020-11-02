<?php
defined('BASEPATH') or exit('No direct script access allowed');

class QR_Outbound_model extends CI_Model
{
    // nama table
    var $table = 'stock_receipt_outbond';

    //field yang ada di table 'stock_receipt_outbond'
    var $column_order = [
        null,
        'id',
        'item_number',
        'stock_item_description',
        'stock_item_spec',
        'oem_model_#',
        'oem_part_#',
        'subinventory',
        'receipt_number',
        'lot_number',
        'receipt_date',
        'po_number'
    ];

    //field yang digunakan untuk pencarian
    var $column_search = [
        'item_number',
        'oem_model_#',
        'oem_part_#',
        'receipt_number',
        'receipt_date',
        'po_number'
    ];

    // default order (urutan)
    var $order = ['id' => 'desc'];

    function __construct()
    {
        // Set table name
        // $this->table = 'stock_receipt_outbond';

        parent::__construct();
        $this->load->database();
    }

    /*
     * Fetch stock-receipt data from the database
     * @param array filter data based on the passed parameters
     */
    public function getRows($params = array())
    {
        $this->db->select('*');
        $this->db->from($this->table);

        if (array_key_exists("where", $params)) {
            foreach ($params['where'] as $key => $val) {
                $this->db->where($key, $val);
            }
        }

        if (array_key_exists("returnType", $params) && $params['returnType'] == 'count') {
            $result = $this->db->count_all_results();
        } else {
            if (array_key_exists("id", $params)) {
                $this->db->where('id', $params['id']);
                $query = $this->db->get();
                $result = $query->row_array();
            } else {
                $this->db->order_by('id', 'desc');
                if (array_key_exists("start", $params) && array_key_exists("limit", $params)) {
                    $this->db->limit($params['limit'], $params['start']);
                } elseif (!array_key_exists("start", $params) && array_key_exists("limit", $params)) {
                    $this->db->limit($params['limit']);
                }

                $query = $this->db->get();
                $result = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            }
        }

        // Return fetched data
        return $result;
    }

    /*
     * Insert stock-receipt data into the database
     * @param $data data to be insert based on the passed parameters
     */
    public function insert($data = array())
    {
        if (!empty($data)) {
            // Add created_by and modified_by if not included
            if (!array_key_exists("created_by", $data)) {
                $data['created_by'] = 'user not found';
            }

            // Add created and modified date if not included
            if (!array_key_exists("created_date", $data)) {
                $data['created_date'] = date("Y-m-d H:i:s");
            }

            // Insert member data
            $insert = $this->db->insert($this->table, $data);

            // Return the status
            return $insert ? $this->db->insert_id() : false;
        }
        return false;
    }

    /*
     * Update stock-receipt data into the database
     * @param $data array to be update based on the passed parameters
     * @param $condition array filter data
     */
    public function update($data, $condition = array())
    {
        if (!empty($data)) {
            // Add modified_by if not included
            if (!array_key_exists("modified_by", $data)) {
                $data['modified_by'] = 'user not found';
            }

            // Add modified date if not included
            if (!array_key_exists("modified_date", $data)) {
                $data['modified_date'] = date("Y-m-d H:i:s");
            }

            // Update member data
            $update = $this->db->update($this->table, $data, $condition);

            // Return the status
            return $update ? true : false;
        }
        return false;
    }

    private function _get_datatables_query()
    {
        $this->db->from($this->table);

        $i = 0;

        foreach ($this->column_search as $item) // looping awal
        {
            if ($_POST['search']['value']) // jika datatable mengirimkan pencarian dengan metode POST
            {
                if ($i === 0) // looping awal
                {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);

        $query = $this->db->get();

        return $query->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();

        return $query->num_rows();
    }

    public function count_all()
    {
        $this->db->from($this->table);

        return $this->db->count_all_results();
    }
}
