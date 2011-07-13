<?php

class Model_Core_Address_Db
{

    /**
     * Cache
     * @var Object
    */
    protected $_cache;

    /**
     * DB
     * @var Object
    */
    protected $_db;

    /**
     * Constructor: Creates the DB conection and sets up Caching Options    
     * @global Object The database object
     * @access Public
     * @return void
    */
    public function __construct()
    {
        $this->_db = Zend_Registry::get('Zend_Db');
    }


    public function getGeoZones()
    {
        $sql = "SELECT gz.geo_zone_name, gz.geo_zone_id,
                        c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3,
                        z.zone_code, z.zone_name,z.zone_id
                    FROM geo_zones gz
                    INNER JOIN zones_to_geo_zones ztg ON gz.geo_zone_id = gz.geo_zone_id
                    INNER JOIN countries c ON ztg.zone_country_id = c.countries_id
                    INNER JOIN zones z ON ztg.zone_id = z.zone_id
                    GROUP BY z.zone_id 
                ";

        $result = $this->_db->fetchAll($sql);

        return $result;

    }

}
