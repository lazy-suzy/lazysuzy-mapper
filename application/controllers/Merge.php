<?php

defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);

class Merge extends CI_Controller
{
    private $master_data_table = "master_data";

    /**
     * Search for "to ZIP code" and "for ZIP code" in online_msg col.
     * and remove it.
     * @return void
     */
    public function remove_zip_code_from_online_msg()
    {

        $rows = $this->db->select(['id', 'online_msg'])->from($this->master_data_table)->get()->result();
        $replace_str = "to ZIP code";
        $replace_str2 = "for ZIP code";
        echo "Total Rows in ", $this->master_data_table, " : ", sizeof($rows), "\n";
        $updates_done = 0;
        foreach ($rows as $row) {
            $id = $row->id;
            $online_msg = $row->online_msg;
            if (
                strlen($online_msg) > 0 &&
                (strpos($online_msg, $replace_str) !== false
                    || strpos($online_msg, $replace_str2) !== false)
            ) {
                $online_msg = str_replace([$replace_str, $replace_str2], "", $online_msg);
                if ($this->db->set('online_msg', $online_msg)->where('id', $id)->update($this->master_data_table))
                    $updates_done++;
            }
        }

        echo "Updates done : ", $updates_done, "\n";
    }
}
