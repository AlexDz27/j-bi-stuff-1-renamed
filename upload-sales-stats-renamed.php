<?php

if (empty($_POST)) return;

$file = fopen($_FILES['csv']['tmp_name'], 'r');

fgets($file); // skip first line

$map = [
  'mid' => 1,
  'accountname' => 2,
  'tx' => 3,
  'products' => 4,
  'clearing' => 5,
  'umsatzTx' => 6,
  'umsatzDis' => 7,
  'costs' => 8,
  'marge' => 9,
  'storno' => 10,
  'stornoVol' => 11,
  'fix' => 12,
  'monat' => 13,
  'jahr' => 14,
  'systems' => 15
];

$statsOrders = [];
while (($statsOrderLine = fgetcsv($file, 1000)) !== false) {
  if ($statsOrderLine[$map['jahr']] == '2021') { // take only stats from year 2021
    if ($statsOrderLine[$map['products']] === '0') { // skip stats order lines that have no products (i.e. all zeros in the line)
      continue;
    }
    
    $statsOrders[] = new Order(
      $statsOrderLine[$map['accountname']],
      $statsOrderLine[$map['mid']],
      '2010-01-01',
      $statsOrderLine[$map['tx']],
      '-',
      $statsOrderLine[$map['products']],
      $statsOrderLine[$map['clearing']],
      $statsOrderLine[$map['umsatzTx']],
      $statsOrderLine[$map['umsatzDis']],
      $statsOrderLine[$map['costs']],
      $statsOrderLine[$map['marge']],
      $statsOrderLine[$map['storno']],
      $statsOrderLine[$map['stornoVol']],
      $statsOrderLine[$map['fix']],
      $statsOrderLine[$map['jahr']] . '-' . $statsOrderLine[$map['monat']] . '-01',
    );
  }
}
fclose($file);

$dsn = 'mysql:host=mysql;dbname=statistik;charset=utf8;port=3306';
$pdo = new PDO($dsn, 'root', 'root');

$statement = $pdo->prepare(
  "INSERT INTO orders (company_name, merchant_id, start_date, transactions, dial_in, products, clearing_volume, sales_taxes,
                    sales_discount, costs, db0, cancellations, cancellation_clear_volume, fixed_fees, date)
         VALUES (:company_name, :merchant_id, :start_date, :transactions, :dial_in, :products, :clearing_volume, :sales_taxes,
                 :sales_discount, :costs, :db0, :cancellations, :cancellation_clear_volume, :fixed_fees, :date)");

foreach ($statsOrders as $order) {
  $statement->execute([
    'company_name' => $order->companyName,
    'merchant_id' => $order->merchantId,
    'start_date' => $order->startDate,
    'transactions' => $order->transactions,
    'dial_in' => $order->dialIn,
    'products' => $order->products,
    'clearing_volume' => $order->clearingVolume,
    'sales_taxes' => $order->salesTaxes,
    'sales_discount' => $order->salesDiscount,
    'costs' => $order->costs,
    'db0' => $order->DB0,
    'cancellations' => $order->cancellations,
    'cancellation_clear_volume' => $order->cancellationClearVolume,
    'fixed_fees' => $order->fixedFees,
    'date' => $order->date
  ]);
}

echo 'Submitted.';


// --------- Helpers ---------------

class Order {
  public $companyName;
  public $merchantId;
  public $startDate;
  public $transactions;
  public $dialIn;
  public $products;
  public $clearingVolume;
  public $salesTaxes;
  public $salesDiscount;
  public $costs;
  public $DB0;
  public $cancellations;
  public $cancellationClearVolume;
  public $fixedFees;
  public $date;

  public function __construct(
    $companyName,
    $merchantId,
    $startDate,
    $transactions,
    $dialIn,
    $products,
    $clearingVolume,
    $salesTaxes,
    $salesDiscount,
    $costs,
    $DB0,
    $cancellations,
    $cancellationClearVolume,
    $fixedFees,
    $date
  ) {
    $this->companyName = $companyName;
    $this->merchantId = $merchantId;
    $this->startDate = $startDate;
    $this->transactions = $transactions;
    $this->dialIn = $dialIn;
    $this->products = $products;
    $this->clearingVolume = $clearingVolume;
    $this->salesTaxes = $salesTaxes;
    $this->salesDiscount = $salesDiscount;
    $this->costs = $costs;
    $this->DB0 = $DB0;
    $this->cancellations = $cancellations;
    $this->cancellationClearVolume = $cancellationClearVolume;
    $this->fixedFees = $fixedFees;
    $this->date = $date;
  }
}