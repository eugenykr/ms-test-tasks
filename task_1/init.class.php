<?php

namespace Ms\Task\One;

/**
 * Init class
 *
 * @package Ms\Task\One
 */
final class init
{
    /**
     * Table name
     *
     * @var string
     */
    private $tableName = 'test';

    /**
     * DB connection object
     *
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        try {
            $this->db = new PDO("mysql:dbname=test;host=localhost", "root", "test");
        }
        catch (\PDOException $ex)
        {
            echo  $ex->getMessage();
        }

        $this->create();
        $this->fill();
    }

    /**
     * Create test table
     *
     * @return void
     */
    private function create()
    {
        $query =
        "CREATE TABLE IF NOT EXISTS".$this->tableName." (
          id int PRIMARY KEY AUTO_INCREMENT,
          script_name varchar(25),
          start_time int unsigned,
          end_time int unsigned,
          result ENUM('normal', 'illegal', 'failed', 'success')
        );";

        $this->db->exec($query);
    }

    /**
     * Filling the test table with random data
     *
     * @return void
     */
    private function fill()
    {
        $insertValuesArray = array();
        $resultsArray = array(
            'normal',
            'illegal',
            'failed',
            'success'
        );

        for ($i = 0; $i < 15; $i++) {
            $scriptName = substr(
                str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCD"),
                0,25
            );

            $currentTime = new Datetime("now");
            $startTime = $currentTime->format('U');
            $currentTime->modify('+1 year');
            $endTime = $currentTime->format('U');

            $resultsArrayKey = rand(0, 3);
            $result = $resultsArray[$resultsArrayKey];

            $insertValuesArray[] = "('$scriptName', $startTime, $endTime, '$result')";
        }

        $query =
            "INSERT INTO '".$this->tableName."' ('script_name', 'start_time', 'end_time', 'result') 
            VALUES ".implode(",", $insertValuesArray);

        $this->db->exec($query);
    }

    /**
     * Select data by criterion: result among the values 'normal' and 'success'
     *
     * @return array
     */
    public function get()
    {
        $query =
        "SELECT * FROM ".$this->tableName." WHERE result IN ('normal', 'success')";

        $sth = $this->db->prepare($query);
        $sth->execute();

        return $sth->fetchAll();
    }
}
