<?php

// src/Service/NightscoutAPIClient.php
namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class NightscoutAPIClient
{

  protected $nightscout_url;
  protected $nightscout_secret;
  protected $http_client;

  public function __construct(string $nightscout_url, string $nightscout_secret)
  {
    if(!$nightscout_url || !$nightscout_secret){
      throw new \Exception('You must set your NIGHTSCOUT_URL and NIGHTSCOUT_SECRET in .env or .env.local');
    }

    if(!$this->urlIsValid()){
      throw new \Exception('It appears your Nightscout URL ('.$nightscout_url.') is not valid');
    }

    if(!$this->secretIsValid()){
      throw new \Exception('It appears your Nightscout secret ('.$nightscout_secret.') is not valid');
    }

    $this->nightscout_url     = $nightscout_url;
    $this->nightscout_secret  = $nightscout_secret;
    $this->http_client        = HttpClient::create();
  }

  public function getSGVFromPredictions($count = 1)
  {
    $url = $this->nightscout_url.'/api/v1/devicestatus.json?count='.$count;
    $response = $this->http_client->request('GET', $url);
    $devicestatus_entries = $response->toArray();

    $sgvs = [];
    foreach($devicestatus_entries as $devicestatus){
      if(array_key_exists('loop', $devicestatus) && array_key_exists('predicted', $devicestatus['loop']) ){
        $sgvs[] = [
          'sgv'         => $devicestatus['loop']['predicted']['values'][0],
          'dateString'  => $devicestatus['loop']['predicted']['startDate']
        ];
      }
    }
    return $sgvs;
  }

  public function uploadBGData($dateString, $sgv)
  {
    $date = strtotime($dateString);

    $url = $this->nightscout_url.'/api/v1/entries.json';
    $response = $this->http_client->request('POST', $url, [
      'json' => [
        "sgv"         => $sgv,
        "date"        => $date*1000,
        "dateString"  => date('c', $date),
        "trend"       => 4,
        "direction"   => "Flat",
        "device"      => "Loop Hack",
        "type"        => "sgv",
        "utcOffset"   => 0,
        "sysTime"     => date('c', $date),
        'secret'      => sha1($this->nightscout_secret),
      ],
    ]);
    // TODO: remove print and add actual validation and logging
    echo date('[c]: ')."BG data was posted to NS. Following response returned: ";
    print_r($response->getContent());
    echo "\n";
  }

  public function getSGVForDateTime(string $date_time)
  {
    echo date('[c]: ')."Looking for SGVs around $date_time\n";
    // function will search for SGVs within $minutes_plus_or_minus of the provided $date_time
    // TODO: get rid of hard coded values
    $minutes_plus_or_minus = 4;
    // $start_date = date('c', strtotime($date_time) - $minutes_plus_or_minus * 60);
    // $end_date   = date('c', strtotime($date_time) + $minutes_plus_or_minus * 60);

    $start_date = (strtotime($date_time) - $minutes_plus_or_minus * 60) * 1000;
    $end_date   = (strtotime($date_time) + $minutes_plus_or_minus * 60) * 1000;

    $url  = $this->nightscout_url.'/api/v1/entries.json?';
    $url .= 'find[date][$gte]='.$start_date.'&find[date][$lte]='.$end_date;

    $response = $this->http_client->request('GET', $url);
    $number_of_sgvs_found = count($response->toArray());
    echo date('[c]: ')."Found $number_of_sgvs_found SGV(s) around $date_time\n";

    return $response->toArray();
  }

  public function sgvExistsForDateTime(string $date_time)
  {
    $count = count($this->getSGVForDateTime($date_time));
    return ($count > 0);
  }

  public function secretIsValid()
  {
    // TODO: make sure URL and secret are valid
    return true;
  }

  public function urlIsValid()
  {
    // TODO: make sure URL and secret are valid
    return true;
  }
}
