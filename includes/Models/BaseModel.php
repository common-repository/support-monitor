<?php

namespace SupportMonitor\App\Models;

require_once __DIR__ . './../../vendor/autoload.php';

use WeDevs\ORM\Eloquent\Model;

class BaseModel extends Model {

    /**
     * @return string
     */
    public function getTable() {         // In this example, it's set, but this is better in an abstract class
        if ( isset( $this->table ) ) {
            $prefix = $this->getConnection()->db->prefix;

            return $prefix . $this->table;
        }

        return parent::getTable();
    }

}
