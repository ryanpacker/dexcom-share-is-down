<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Service\NightscoutAPIClient;

class UploadSGVFromPredictionCommand extends Command
{
  protected static $defaultName = 'go';
  protected $api_client;

  public function __construct(NightscoutAPIClient $client)
  {
    $this->api_client = $client;
    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->setDescription('Fill in SVG entries in Nightscout based on Loop prediction values')
      ->setHelp('Looks for valid Loop predictions in Nightscout devicestatus collection and uses this data to assume SGV')
      ->addOption(
        'devicestatus-count',
        'd',
        InputOption::VALUE_REQUIRED,
        'Number of devicestatus entries to check. Use a large number to backfill data.',
        2
      )
    ;
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    echo date('[c]: ')."UploadSGVFromPredictionCommand started\n";
    // TODO: add option to command line to determine how many devicestatus entries to go get
    $sgvs = $this->api_client->getSGVFromPredictions($input->getOption('devicestatus-count'));
    foreach ($sgvs as $sgv) {
      echo date('[c]: ')."Found valid prediction with bg data. DateTime: ".$sgv['dateString']." | sgv: ".$sgv['sgv']."\n";
      if(!$this->api_client->sgvExistsForDateTime($sgv['dateString'])){
        echo  date('[c]: ')."uploading sgv: ".$sgv['sgv']." and date: ".$sgv['dateString']."\n";
        $this->api_client->uploadBGData($sgv['dateString'], $sgv['sgv']);
      }
    }
  }
}
