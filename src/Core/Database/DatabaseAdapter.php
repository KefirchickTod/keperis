<?php


namespace src\Core\Database;

use PDO;
use src\Core\Database\Interfaces\DatabaseAdapterInterface AS DatabaseInterfaces;
use src\Http\Environment;

/**
 * Class Database
 * @package src\Core\Database
 * !!!! Using not supported PDO attribute for Oracle !!!
 */
class DatabaseAdapter implements DatabaseInterfaces
{


    /**
     * @var PDO
     */
    private $connection;


    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    

    /**
     * @param Environment $environment
     * Return static with qyickle setting object
     * @return static
     */
    public static function createDateBaseConnection(Environment $environment, $options = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"])
    {
        $attributes = [
            PDO::ATTR_ERRMODE      => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
            PDO::ATTR_CASE         => PDO::CASE_LOWER,
        ];
        if(!$environment->hasMany(['DB_USER', 'DB_HOST', 'DB_PASS', 'DB_NAME'])){
            throw new \PDOException("Undefined db setting");
        }
        
        if(!$environment->has('dsn')){
            $environment->set('dsn','mysql:host=' . $environment->get('DB_HOST') . ';dbname=' . $environment->get('DB_NAME'));
        }

        try {
            $pdo =  new PDO(
                $environment->get('dsn'),
                $environment->get('DB_USER'),
                $environment->get('DB_PASS'),
                $options
            );
            foreach ($attributes as $attribute => $value){

                $pdo->setAttribute($attribute, $value);
            }
            $pdo->exec("SET time_zone = '" . date('P') . "'");
            $pdo->exec('SET names utf8');
            $connection = new static($pdo);
        }catch (\PDOException $exception){;
            if(boolval($environment->get('APP_DEBUG', false)) === true){
                echo $exception->getMessage();
            }
            error_log($exception->getMessage());
            die(0);
        }
        return  $connection;

    }



    /**
     * @inheritDoc
     */
    public function getPdo(): \PDO
    {
        return $this->connection;
    }

    public function select(string $query, $style = PDO::FETCH_ASSOC ){
        try {
            $STH = $this->connection->prepare($query);
            $STH->execute();
            return $STH->fetchAll($style);
        } catch (\PDOException $exception){
            if((bool)env("APP_DEBUG", false)  === true){
                echo $exception->getMessage() ." <br > ".$STH->queryString;
            }
            error_log($exception->getMessage());
            die();
        }
    }

    public function line(string $query)
    {
        try {
            return $this->connection->query($query);
        } catch (\PDOException $exception){
            if((bool)env("APP_DEBUG", false)  === true){
                echo $exception->getMessage();
            }
            error_log($exception->getMessage());
            die();
        }
    }
}