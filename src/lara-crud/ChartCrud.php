<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LaraCrud;

/**
 * Description of ChartCrud
 *
 * @author Tuhin
 */
class ChartCrud
{
    /**
     * Chart Generator JavaScript Library.E.g. ChartJs, Google . This are defined in config file.
     * Currenly support only chartjs
     * @var string
     */
    protected $library;

    /**
     * Type of the Chart. E.g. Bar, Column,Pie
     * Options are bar, line, radar, polarArea, pie, doughnut, bubble
     * @var enum
     */
    protected $type;

    /**
     * Whether it is a complete dashboard or just a partial chart
     * @var boolean
     */
    protected $dashboard;

    /**
     * Name of the file where to save.
     * 
     * @var string File Name
     */
    protected $name;

    /**
     * Data Provider class
     * @var Chart\DataBank
     */
    protected $dataBank;

    public function __construct($table, $type, $dashboard, $name)
    {
        parent::__construct();
        if (!empty($table)) {
            if (is_array($table)) {
                $this->tables = $table;
            } else {
                $this->tables[] = $table;
            }
        } else {
            $this->getTableList();
        }
        $this->fileName  = $name;
        $this->type      = $type;
        $this->dashboard = $dashboard;

        $this->loadDetails();
        $this->findPivotTables();
        $this->prepareRelation();

        if (!file_exists(base_path($this->getConfig("chartPath")))) {
            mkdir(base_path($this->getConfig("chartPath")));
        }
    }

    

    protected function create($table)
    {
        
    }

    public function make()
    {
        
    }
}