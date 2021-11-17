<?php

if (empty($_POST)) return;

$file = fopen($_FILES['csv']['tmp_name'], 'r');
fgets($file); // skip first line

$map = [
  'integrator_name' => 1,
  'integrator_version' => 2,
  'solution_name' => 3,
  'solution_version' => 4,
  'sdk_type' => 5,
  'sdk_version' => 6,
  'mid' => 7,
  'request' => 8,
  'anzahl' => 9,
  'jahr' => 10,
  'monat' => 11,
];

$stats = [];
while (($statsLine = fgetcsv($file, 1000)) !== false) {
  if ($statsLine[$map['jahr']] == '2021') {
    $stats[] = new Stat(
      $statsLine[$map['integrator_name']],
      $statsLine[$map['integrator_version']],
      $statsLine[$map['solution_name']],
      $statsLine[$map['solution_version']],
      $statsLine[$map['sdk_type']],
      $statsLine[$map['sdk_version']],
      $statsLine[$map['mid']],
      $statsLine[$map['request']],
      $statsLine[$map['anzahl']],
      $statsLine[$map['jahr']] . '-' . $statsLine[$map['monat']] . '-01',
    );
  }
}
fclose($file);

$dsn = 'mysql:host=mysql;dbname=statistik;charset=utf8;port=3306';
$pdo = new PDO($dsn, 'root', 'root');
foreach ($stats as $stat) {
  $statement = $pdo->prepare("INSERT INTO integrator (integrator_name, integrator_version, solution_name, solution_version, sdk_type, sdk_version, mid, request, count, month) VALUES (:integrator_name, :integrator_version, :solution_name, :solution_version, :sdk_type, :sdk_version, :mid, :request, :count, :month)");
  $statement->execute([
    'integrator_name' => $stat->integrator_name,
    'integrator_version' => $stat->integrator_version,
    'solution_name' => $stat->solution_name,
    'solution_version' => $stat->solution_version,
    'sdk_type' => $stat->sdk_type,
    'sdk_version' => $stat->sdk_version,
    'mid' => $stat->mid,
    'request' => $stat->request,
    'count' => $stat->count,
    'month' => $stat->month
  ]);
}

echo 'Submitted.';


// --------- Helpers ---------------

class Stat {
  public $integrator_name;
  public $integrator_version;
  public $solution_name;
  public $solution_version;
  public $sdk_type;
  public $sdk_version;
  public $mid;
  public $request;
  public $count;
  public $month;
  
  public function __construct(
    $integrator_name,
    $integrator_version,
    $solution_name,
    $solution_version,
    $sdk_type,
    $sdk_version,
    $mid,
    $request,
    $count,
    $month
  ) {
    $this->integrator_name = $integrator_name;
    $this->integrator_version = $integrator_version;
    $this->solution_name = $solution_name;
    $this->solution_version = $solution_version;
    $this->sdk_type = empty($sdk_type) ? null : $sdk_type;
    $this->sdk_version = empty($sdk_version) ? null : $sdk_version;
    $this->mid = $mid;
    $this->request = $request;
    $this->count = $count;
    $this->month = $month;
    
    if ($this->integrator_name === 'oxid') {
      // get first digit of Oxid shop version. Then decide is it Oxid5 or Oxid6
      $firstDigit = substr(explode('.', $this->integrator_version)[0], -1);

      if ($firstDigit === '4' || $firstDigit === '5') {
        $integrator_name = $this->integrator_name . '5';
      }
      if ($firstDigit === '6') {
        $integrator_name = $this->integrator_name . '6';
      }
      
      $this->integrator_name = $integrator_name;
    }
  }
}