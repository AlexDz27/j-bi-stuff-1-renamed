<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Statistik - load big "stats" CSV files</title>
</head>
<body>
<h1>Wartung der Statistik</h1>
</br></br>
<h2>Wartung Integrator file als csv</h2>
</br></br>

<p>Load <code>salesStats.csv</code> file:</p>
<form action="/big-stats-csv-files/upload-sales-stats.php" method="post" enctype="multipart/form-data">
  <input type="file" name="csv" value="filename" />
  <input type="submit" name="submit" value="Save" /></form>

<p>Load <code>stats.csv</code> file:</p>
<form action="/big-stats-csv-files/upload-stats.php" method="post" enctype="multipart/form-data">
  <input type="file" name="csv" value="filename" />
  <input type="submit" name="submit" value="Save" /></form>
</body>
</html>
