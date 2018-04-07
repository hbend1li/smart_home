<?php
  session_start();

  // Prepart connexion à la base de données
  // FireWorks('host|user|pwd|bdd');
  $FW = new FireWorks('localhost|root|genesis|iot');

  class FireWorks{

    private static $databases;
    private $connection;

    public function __construct($connDetails){
      if(!is_object(self::$databases[$connDetails])){
        list($host, $user, $pass, $dbname) = explode('|', $connDetails);
        $dsn = "mysql:host=$host;dbname=$dbname";
        self::$databases[$connDetails] = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
      }
      $this->connection = self::$databases[$connDetails];
    }

    // RUN SQL =========================================================
    public function fetch($sql,$all = null){
      $args = func_get_args();
      array_shift($args);
      $statement = $this->connection->prepare($sql);
      $statement->execute($args);

      $result                 = array();
      $result["errorInfo"]    = $statement->errorInfo();
      $result['lastInsertId'] = $this->connection->lastInsertId();
      if ($all)
        $result['data']         = $statement->fetchAll(PDO::FETCH_OBJ);
      else
        $result['data']         = $statement->fetch(PDO::FETCH_OBJ);

      return $result;
    }

    // SQL Injection ===================================================
    public function _inj(&$val, $default = null){
      $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
      $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
      return isset($val) ? str_replace($search, $replace, $val) : $default;
    }

  }