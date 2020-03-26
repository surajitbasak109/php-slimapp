<?php

/**
 * src/classes/db.php
 *
 * @package default
 */


/**
 * classes/db.php
 *
 * @package default
 */
class DB
{

  /**
   *
   * @return unknown
   */
  public static function connect()
  {

    // Database configs
    $dbdriver = "mysql";
    $dbhost = "localhost";
    $dbname = "slimapp";
    $username = "root";
    $password = "password";

    $options = [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
    $pdo = new PDO("{$dbdriver}:host={$dbhost};dbname={$dbname};", $username, $password, $options);
    return $pdo;
  }


  /**
   *
   * @param string  $table
   * @param string  $cols  (optional)
   * @param string  $opts  (optional)
   * @return unknown
   */
  public static function select(string $table, string $cols = "*", string $opts = "")
  {
    $stmt = self::connect()->prepare("SELECT {$cols} FROM {$table} {$opts}");
    $stmt->execute();
    return $stmt;
  }


  /**
   *
   * @param string  $table
   * @param string  $cols     (optional)
   * @param array   $where    (optional)
   * @param string  $optional (optional)
   * @return unknown
   */
  public static function select_where(string $table, string $cols = "*", array $where = [], string $optional = '')
  {

    $whereCommand = "";

    foreach ($where as $key => $value) {
      $whereCommand .= $key . " = " . "? AND ";
    }

    $whereCommand = rtrim($whereCommand, 'AND ');

    $sql = "SELECT {$cols} FROM $table WHERE {$whereCommand} {$optional}";
    $stmt = self::connect()->prepare($sql);
    $stmt->execute(array_values($where));

    return $stmt;
  }


  /**
   *
   * @param string  $table
   * @param array   $cols
   * @return unknown
   */
  public static function insert(string $table, array $cols)
  {
    $cols_string = implode(", ", array_keys($cols));
    $value_string = implode(", ", array_fill(0, count(array_keys($cols)), '?'));
    $sql = "INSERT INTO $table ({$cols_string}) VALUES ({$value_string});";

    $stmt = self::connect()->prepare($sql);
    try {
      $stmt->execute(array_values($cols));
    } catch (Exception $e) {
      return $e;
    }
  }


  /**
   *
   * @param string  $table
   * @param array   $data  (optional)
   * @return unknown
   */
  public static function batch_insert(string $table, array $data = [])
  {
    //Will contain SQL snippets.
    $rowsSQL = array();

    //Will contain the values that we need to bind.
    $toBind = array();

    //Get a list of column names to use in the SQL statement.
    $columnNames = array_keys($data[0]);

    //Loop through our $data array.
    foreach ($data as $arrayIndex => $row) {
      $params = array();
      foreach ($row as $columnName => $columnValue) {
        $param = ":" . $columnName . $arrayIndex;
        $params[] = $param;
        $toBind[$param] = $columnValue;
      }
      $rowsSQL[] = "(" . implode(", ", $params) . ")";
    }

    //Construct our SQL statement
    $sql = "INSERT INTO `$table` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

    //Prepare our PDO statement.
    $stmt = self::connect()->prepare($sql);

    //Bind our values.
    foreach ($toBind as $param => $val) {
      $stmt->bindValue($param, $val);
    }

    //Execute our statement (i.e. insert the data).
    return $stmt->execute();
  }


  /**
   *
   * @param string  $table
   * @param array   $where (optional)
   * @param array   $data  (optional)
   * @return unknown
   */
  public static function update(string $table, array $where = [], array $data = [])
  {
    $fieldname = implode(',', array_keys($where));

    $sqlCommand = "";
    foreach ($data as $key => $value) {
      $sqlCommand .= $key . '=' . '?, ';
    }

    $sqlCommand = rtrim($sqlCommand, ', ');

    $sql = "UPDATE {$table} SET {$sqlCommand} WHERE {$fieldname} = ?";

    $stmt = self::connect()->prepare($sql);
    $params = array_merge(array_values($data), array_values($where));
    return $stmt->execute($params);
  }


  /**
   *
   * @param string  $table
   * @param array   $where (optional)
   * @return unknown
   */
  public static function delete(string $table, array $where = [])
  {
    $fieldname = implode(',', array_keys($where));
    $sql = "DELETE FROM {$table} WHERE {$fieldname} = ?";
    $stmt = self::connect()->prepare($sql);
    return $stmt->execute(array_values($where));
  }


  /**
   * batch_update
   *
   *
   * @param string  $table
   * @param array   $data    (optional)
   * @param array   $colname (optional)
   * @return void
   */
  public function batch_update(string $table, array $data = [], array $colname = [])
  {
    if ($this->delete($table, $colname)) {
      //Will contain SQL snippets.
      $rowsSQL = array();

      //Will contain the values that we need to bind.
      $toBind = array();

      //Get a list of column names to use in the SQL statement.
      $columnNames = array_keys($data[0]);

      //Loop through our $data array.
      foreach ($data as $arrayIndex => $row) {
        $params = array();
        foreach ($row as $columnName => $columnValue) {
          $param = ":" . $columnName . $arrayIndex;
          $params[] = $param;
          $toBind[$param] = $columnValue;
        }
        $rowsSQL[] = "(" . implode(", ", $params) . ")";
      }

      //Construct our SQL statement
      $sql = "INSERT INTO `$table` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);


      //Prepare our PDO statement.
      $pdoStatement = self::connect()->prepare($sql);

      //Bind our values.
      foreach ($toBind as $param => $val) {
        $pdoStatement->bindValue($param, $val);
      }

      //Execute our statement (i.e. insert the data).
      return $pdoStatement->execute();
    }
    return false;
  }
}
