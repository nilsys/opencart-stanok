<?php
class ModelCatalogSuboption extends Model {

    public function setSuboptions($option_id, $data) {

        if (!empty($data['suboptions'])) {
            foreach ($data['suboptions'] as $option_value_id => $suboptions) {
                foreach ($suboptions as $suboption_id => $suboption_name) {
                    $suboptionRow = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_suboptions WHERE suboption_id = '" . $suboption_id . "' AND option_value_id = '" . $option_value_id . "' AND option_id = '" . $option_id . "'")->row;
                    $toDelCheck[$suboption_id] = $suboptionRow;
                    if ($suboptionRow)
                        {
                            $this->db->query("UPDATE " . DB_PREFIX . "custom_suboptions SET suboption_name = '" . $suboption_name . "' WHERE option_id = '" . $option_id . "' AND suboption_id = '" . $suboption_id . "' AND option_value_id = '" . (int)$option_value_id . "'");
                            $this->db->query("UPDATE " . DB_PREFIX . "custom_product_suboptions SET suboption_name = '" . $suboption_name . "' WHERE option_id = '" . $option_id . "' AND suboption_id = '" . $suboption_id . "' AND option_value_id = '" . (int)$option_value_id . "'");
                        }
                    else
                        $this->db->query("INSERT INTO " . DB_PREFIX . "custom_suboptions SET suboption_id = '" . $suboption_id . "', option_value_id = '" . (int)$option_value_id . "', suboption_name = '" . $suboption_name . "', option_id = '" . $option_id . "'");
                }
            }
        }
        // to delete block
        $dbAr = $this->db->query("SELECT suboption_id FROM " . DB_PREFIX . "custom_suboptions WHERE option_id = '" . $option_id . "'")->rows;
        foreach ($dbAr as $row) {
            if (!array_key_exists($row['suboption_id'], $toDelCheck)) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "custom_suboptions WHERE option_id=" . $option_id . " AND suboption_id=" . $row['suboption_id']);
                $this->db->query("DELETE FROM " . DB_PREFIX . "custom_product_suboptions WHERE option_id=" . $option_id . " AND suboption_id=" . $row['suboption_id']);
            }
        }
        //

    }

    public function setProductSuboptions($product_id, $data)
    {
        if (array_key_exists('prod_suboption', $data)) {
            foreach ($data['prod_suboption'] as $key => $suboption_properties) {
                $suboptionRow = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_product_suboptions WHERE product_id = '" . $product_id . "' AND option_id = '" . $suboption_properties['option_id'] . "' AND option_value_id = '" . $suboption_properties['option_value_id'] . "' AND suboption_id = '" . $suboption_properties['suboption_id'] . "'")->row;
                $toDelCheck[$suboption_properties['option_id'] . '_' . $suboption_properties['suboption_id']] = $suboptionRow;
                if ($suboptionRow) {
                    if (array_key_exists('status', $suboption_properties)) {
                        $this->db->query("UPDATE " . DB_PREFIX . "custom_product_suboptions SET status = '" . $suboption_properties['status'] . "', suboption_price = '" . $suboption_properties['prod_suboption_price'] . "' WHERE product_id = '" . $product_id . "' AND option_id = '" . $suboption_properties['option_id'] . "' AND option_value_id = '" . $suboption_properties['option_value_id'] . "' AND suboption_id = '" . $suboption_properties['suboption_id'] . "'");
                    } else {
                        $this->db->query("UPDATE " . DB_PREFIX . "custom_product_suboptions SET status = 0, suboption_price = '" . $suboption_properties['prod_suboption_price'] . "' WHERE product_id = '" . $product_id . "' AND option_id = '" . $suboption_properties['option_id'] . "' AND option_value_id = '" . $suboption_properties['option_value_id'] . "' AND suboption_id = '" . $suboption_properties['suboption_id'] . "'");
                    }
                } else {
                    if (array_key_exists('status', $suboption_properties)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "custom_product_suboptions SET product_id = '" . $product_id . "', option_value_id = '" . $suboption_properties['option_value_id'] . "', option_id = '" . $suboption_properties['option_id'] . "', suboption_name = '" . $suboption_properties['suboption_name'] . "', suboption_id = '" . $suboption_properties['suboption_id'] . "', status = '" . $suboption_properties['status'] . "', suboption_price = '" . $suboption_properties['prod_suboption_price'] . "'");
                    } else {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "custom_product_suboptions SET product_id = '" . $product_id . "', option_value_id = '" . $suboption_properties['option_value_id'] . "', option_id = '" . $suboption_properties['option_id'] . "', suboption_name = '" . $suboption_properties['suboption_name'] . "', suboption_id = '" . $suboption_properties['suboption_id'] . "', status = 0, suboption_price = '" . $suboption_properties['prod_suboption_price'] . "'");
                    }
                }
            }

            // to delete block
            $suboptions_for_product = $this->db->query("SELECT option_id, suboption_id, option_value_id FROM " . DB_PREFIX . "custom_product_suboptions WHERE product_id = '" . $product_id . "'")->rows;
            foreach ($suboptions_for_product as $suboption) {
                $toDelAr[$suboption['option_id'] . '_' . $suboption['suboption_id']] = $suboption;
            }
            foreach ($toDelAr as $key => $row) {
                if (!array_key_exists($key, $toDelCheck)) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "custom_product_suboptions WHERE product_id=" . $product_id . " AND option_value_id=" . $row['option_value_id'] . " AND option_id=" . $row['option_id']);
                }
            }
            //
        } else {
            //если в POST-е не пришло ни одной сабопции для данного товара - то удалить у этого товара все сабопции
            $this->db->query("DELETE FROM " . DB_PREFIX . "custom_product_suboptions WHERE product_id=" . $product_id);
        }


    }

    public function getSuboption($suboption_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_suboptions WHERE suboption_id = '" . (int)$suboption_id . "'");

        return $query->row;
    }

    public function getSuboptions($data)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "custom_suboptions WHERE option_value_id = '" . $data['option_value_id'] ."' AND option_id = '" . $data['option_id'] . "'";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProductSuboptions($product_id, $option_group_id, $data)
    {
        $optionSuboptions = $this->getSuboptions(['option_value_id' => $data['option_value_id'], 'option_id' => $option_group_id ]);
        foreach ($optionSuboptions as $suboption) {
            $OScheck[$product_id.'_'.$data['option_value_id'].'_'.$option_group_id][$suboption['suboption_id']] = $suboption;
        }

        $productSuboptions = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_product_suboptions WHERE product_id = '" . intval($product_id) . "' AND option_value_id = '" . intval($data['option_value_id']) ."' AND option_id = '" . intval($option_group_id) ."'")->rows;
        foreach ($productSuboptions as $suboption) {
            $PScheck[$product_id.'_'.$data['option_value_id'].'_'.$option_group_id][$suboption['suboption_id']] = $suboption;
        }
        if (!empty($productSuboptions)) {
            foreach ($OScheck[$product_id . '_' . $data['option_value_id'] . '_' . $option_group_id] as $OSsuboption_id => $OSsuboption) {
                if (!array_key_exists($OSsuboption_id, $PScheck[$product_id . '_' . $data['option_value_id'] . '_' . $option_group_id])) {
                    $PScheck[$product_id . '_' . $data['option_value_id'] . '_' . $option_group_id][$OSsuboption_id] = [
                        'product_id' => $product_id,
                        'option_value_id' => $OSsuboption['option_value_id'],
                        'option_id' => $OSsuboption['option_id'],
                        'suboption_name' => $OSsuboption['suboption_name'],
                        'suboption_id' => $OSsuboption['suboption_id'],
                        'suboption_price' => 0.0000,
                        'status' => 0
                    ];
                }
            }
            $productSuboptions = $PScheck[$product_id . '_' . $data['option_value_id'] . '_' . $option_group_id];
        }

        return $productSuboptions;
    }

    public function writeLog()
    {
        $logFileName = realpath($_SERVER["DOCUMENT_ROOT"] . "/../..") . "/logs/stanok.log";
        $backtrace = debug_backtrace();
        $backtracePath = array();
        foreach($backtrace as $k => $bt)
        {
            if($k > 15)
                break;
            $backtracePath[] = substr($bt['file'], strlen($_SERVER['DOCUMENT_ROOT'])) . ':' . $bt['line'];
        }

        $data = func_get_args();
        if(count($data) == 0)
            return;
        elseif(count($data) == 1)
            $data = current($data);

        if(!is_string($data) && !is_numeric($data))
            $data = var_export($data, 1);
        $fp = fopen($logFileName, 'at+');
        fwrite($fp, "\n--------------------------" . date('Y-m-d H:i:s ') . microtime() . "-----------------------\n Backtrace: " . implode(' > ', $backtracePath) . "\n" . $data);
        fflush($fp);
        fclose($fp);
    }

}
