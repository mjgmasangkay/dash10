<?php
require_once('classes/Exporter.php');
require_once('classes/Services/StatsService.php');

class Controller {

    public $args;

    public function __construct($args)
    {
        $this->args = $args;
    }

    public function export($type, $format)
    {
        $exporter = new Exporter();
        $statsService = new StatsService();

        $data = $statsService->getStats($this->args, $type, $format);

        if (!$data) {
            exit("Error: No data found!");
        }
        return $exporter->format($data, $format);
    }
}