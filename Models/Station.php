<?php

namespace Models;

class Station
{
    public $ds100;
    public $eva;
    public $meta;
    public $name;
    public $db;
    public $creationts;
    public $p;

    public function __construct($xmlStation)
    {
        $this->ds100 = (string)$xmlStation['ds100'];
        $this->eva = (int)$xmlStation['eva'];
        $this->meta = (string)$xmlStation['meta'];
        $this->db = (boolean)$xmlStation['db'];
        $this->name = (string)$xmlStation['name'];
        $this->creationts = (string)$xmlStation['creationts'];
        $this->p = explode('|', (string)$xmlStation['p']);
    }
}

class MultipleStationData
{
    public $stations;

    public function __construct($xmlResponse)
    {
        $this->stations = [];
        if ($xmlResponse !== null) {
            foreach ($xmlResponse->station as $xmlStation) {
                $this->stations[] = new Station($xmlStation);
            }
        }
    }
}