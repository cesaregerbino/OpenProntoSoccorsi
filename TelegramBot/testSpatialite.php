<html>
  <head>
    <title>Testing SpatiaLite on PHP</title>
  </head>
  <body>
    <h1>testing SpatiaLite on PHP</h1>

<?php
# connecting some SQLite DB
# we'll actually use an IN-MEMORY DB
# so to avoid any further complexity;
# an IN-MEMORY DB simply is a temp-DB
$db = new SQLite3(':memory:');

# loading SpatiaLite as an extension
$db->loadExtension('mod_spatialite.so');

# enabling Spatial Metadata
# using v.2.4.0 this automatically initializes SPATIAL_REF_SYS
# and GEOMETRY_COLUMNS
$db->exec("SELECT InitSpatialMetadata()");

# reporting some version info
$rs = $db->query('SELECT sqlite_version()');
while ($row = $rs->fetchArray())
{
  print "<h3>SQLite version: $row[0]</h3>";
}
$rs = $db->query('SELECT spatialite_version()');
while ($row = $rs->fetchArray())
{
  print "<h3>SpatiaLite version: $row[0]</h3>";
}

# creating a POINT table
$sql = "CREATE TABLE test_pt (";
$sql .= "id INTEGER NOT NULL PRIMARY KEY,";
$sql .= "name TEXT NOT NULL)";
$db->exec($sql);
# creating a POINT Geometry column
$sql = "SELECT AddGeometryColumn('test_pt', ";
$sql .= "'geom', 4326, 'POINT', 'XY')";
$db->exec($sql);

# creating a LINESTRING table
$sql = "CREATE TABLE test_ln (";
$sql .= "id INTEGER NOT NULL PRIMARY KEY,";
$sql .= "name TEXT NOT NULL)";
$db->exec($sql);
# creating a LINESTRING Geometry column
$sql = "SELECT AddGeometryColumn('test_ln', ";
$sql .= "'geom', 4326, 'LINESTRING', 'XY')";
$db->exec($sql);

# creating a POLYGON table
$sql = "CREATE TABLE test_pg (";
$sql .= "id INTEGER NOT NULL PRIMARY KEY,";
$sql .= "name TEXT NOT NULL)";
$db->exec($sql);
# creating a POLYGON Geometry column
$sql = "SELECT AddGeometryColumn('test_pg', ";
$sql .= "'geom', 4326, 'POLYGON', 'XY')";
$db->exec($sql);

# inserting some POINTs
# please note well: SQLite is ACID and Transactional
# so (to get best performance) the whole insert cycle
# will be handled as a single TRANSACTION
$db->exec("BEGIN");
for ($i = 0; $i < 10000; $i++)
{
  # for POINTs we'll use full text sql statements
  $sql = "INSERT INTO test_pt (id, name, geom) VALUES (";
  $sql .= $i + 1;
  $sql .= ", 'test POINT #";
  $sql .= $i + 1;
  $sql .= "', GeomFromText('POINT(";
  $sql .= $i / 1000.0;
  $sql .= " ";
  $sql .= $i / 1000.0;
  $sql .= ")', 4326))";
  $db->exec($sql);
}
$db->exec("COMMIT");

# checking POINTs
$sql = "SELECT DISTINCT Count(*), ST_GeometryType(geom), ";
$sql .= "ST_Srid(geom) FROM test_pt";
$rs = $db->query($sql);
while ($row = $rs->fetchArray())
{
  # read the result set
  $msg = "Inserted ";
  $msg .= $row[0];
  $msg .= " entities of type ";
  $msg .= $row[1];
  $msg .= " SRID=";
  $msg .= $row[2];
  print "<h3>$msg</h3>";
}

# inserting some LINESTRINGs
# this time we'll use a Prepared Statement
$sql = "INSERT INTO test_ln (id, name, geom) ";
$sql .= "VALUES (?, ?, GeomFromText(?, 4326))";
$stmt = $db->prepare($sql);
$db->exec("BEGIN");
for ($i = 0; $i < 10000; $i++)
{
  # setting up values / binding
  $name = "test LINESTRING #";
  $name .= $i + 1;
  $geom = "LINESTRING(";
  if (($i%2) == 1)
  {
    # odd row: five points
    $geom .= "-180.0 -90.0, ";
    $geom .= -10.0 - ($i / 1000.0);
    $geom .= " ";
    $geom .= -10.0 - ($i / 1000.0);
    $geom .= ", ";
    $geom .= -10.0 - ($i / 1000.0);
    $geom .= " ";
    $geom .= 10.0 + ($i / 1000.0);
    $geom .= ", ";
    $geom .= 10.0 + ($i / 1000.0);
    $geom .= " ";
    $geom .= 10.0 + ($i / 1000.0);
    $geom .= ", 180.0 90.0";
  }
  else
  {
    # even row: two points
    $geom .= -10.0 - ($i / 1000.0);
    $geom .= " ";
    $geom .= -10.0 - ($i / 1000.0);
    $geom .= ", ";
    $geom .= 10.0 + ($i / 1000.0);
    $geom .= " ";
    $geom .= 10.0 + ($i / 1000.0);
  }
  $geom .= ")";

  $stmt->reset();
  $stmt->clear();
  $stmt->bindValue(1, $i+1, SQLITE3_INTEGER);
  $stmt->bindValue(2, $name, SQLITE3_TEXT);
  $stmt->bindValue(3, $geom, SQLITE3_TEXT);
  $stmt->execute();
}
$db->exec("COMMIT");

# checking LINESTRINGs
$sql = "SELECT DISTINCT Count(*), ST_GeometryType(geom), ";
$sql .= "ST_Srid(geom) FROM test_ln";
$rs = $db->query($sql);
while ($row = $rs->fetchArray())
{
  # read the result set
  $msg = "Inserted ";
  $msg .= $row[0];
  $msg .= " entities of type ";
  $msg .= $row[1];
  $msg .= " SRID=";
  $msg .= $row[2];
  print "<h3>$msg</h3>";
}

# insering some POLYGONs
# this time too we'll use a Prepared Statement
$sql = "INSERT INTO test_pg (id, name, geom) ";
$sql .= "VALUES (?, ?, GeomFromText(?, 4326))";
$stmt = $db->prepare($sql);
$db->exec("BEGIN");
for ($i = 0; $i < 10000; $i++)
{
  # setting up values / binding
  $name = "test POLYGON #";
  $name .= $i + 1;
  $geom = "POLYGON((";
  $geom .= -10.0 - ($i / 1000.0);
  $geom .= " ";
  $geom .= -10.0 - ($i / 1000.0);
  $geom .= ", ";
  $geom .= 10.0 + ($i / 1000.0);
  $geom .= " ";
  $geom .= -10.0 - ($i / 1000.0);
  $geom .= ", ";
  $geom .= 10.0 + ($i / 1000.0);
  $geom .= " ";
  $geom .= 10.0 + ($i / 1000.0);
  $geom .= ", ";
  $geom .= -10.0 - ($i / 1000.0);
  $geom .= " ";
  $geom .= 10.0 + ($i / 1000.0);
  $geom .= ", ";
  $geom .= -10.0 - ($i / 1000.0);
  $geom .= " ";
  $geom .= -10.0 - ($i / 1000.0);
  $geom .= "))";

  $stmt->reset();
  $stmt->clear();
  $stmt->bindValue(1, $i+1, SQLITE3_INTEGER);
  $stmt->bindValue(2, $name, SQLITE3_TEXT);
  $stmt->bindValue(3, $geom, SQLITE3_TEXT);
  $stmt->execute();
}
$db->exec("COMMIT");

# checking POLYGONs
$sql = "SELECT DISTINCT Count(*), ST_GeometryType(geom), ";
$sql .= "ST_Srid(geom) FROM test_pg";
$rs = $db->query($sql);
while ($row = $rs->fetchArray())
{
  # read the result set
  $msg = "Inserted ";
  $msg .= $row[0];
  $msg .= " entities of type ";
  $msg .= $row[1];
  $msg .= " SRID=";
  $msg .= $row[2];
  print "<h3>$msg</h3>";
}

# closing the DB connection
$db->close();
?>

  </body>
</html>
