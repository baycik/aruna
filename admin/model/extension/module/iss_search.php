<?php

class ModelExtensionModuleIssSearch extends Model {

    public function getModuleId() {
        $sql = " SHOW TABLE STATUS LIKE '" . DB_PREFIX . "module'";
        $query = $this->db->query($sql);
        return $query->rows;
    }

}