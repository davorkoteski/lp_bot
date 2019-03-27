<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 3/19/2019
 * Time: 10:14 PM
 */

namespace App\Services;


class MainService
{
    private $pdo;
    public function __construct(DatabaseConnection $connection)
    {
        $this->pdo = $connection;
    }
    public function selectUsers($app_id)
    {
        $statement = $this->pdo->getConnection()->prepare('SELECT * FROM users WHERE app_id = :app_id');
        $statement->bindParam(':app_id', $app_id);
        $statement->execute();
        $user = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $user[0];
    }
    public function insertUsers($data)
    {
        $statement = $this->pdo->getConnection()->prepare('INSERT INTO users (name, app_id, created_at) VALUES (:name, :app_id, :created_at)');
        $statement->bindParam(':name', $data['name']);
        $statement->bindParam(':app_id', $data['app_id']);
        $statement->bindParam(':created_at', $data['created_at']);
        $statement->execute();
        return $this->pdo->getConnection()->lastInsertId();
    }
    public function selectPlates($number)
    {
        $statement = $this->pdo->getConnection()->prepare('SELECT * FROM plates WHERE number = :number');
        $statement->bindParam(':number', $number);
        $statement->execute();
        $plate = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $plate[0];
    }
    public function insertPlates($data)
    {
        $statement = $this->pdo->getConnection()->prepare('INSERT INTO plates (number, user_id, created_at) VALUES (:number, :user_id, :created_at)');
        $statement->bindParam(':number', $data['number']);
        $statement->bindParam(':user_id', $data['user_id']);
        $statement->bindParam(':created_at', $data['created_at']);
        $statement->execute();
    }
    public function insertMessages($data)
    {
        $statement = $this->pdo->getConnection()->prepare('INSERT INTO messages (name, user_id, plate_id, created_at) VALUES (:name, :user_id, :plate_id, :created_at)');
        $statement->bindParam(':name', $data['name']);
        $statement->bindParam(':user_id', $data['user_id']);
        $statement->bindParam(':plate_id', $data['plate_id']);
        $statement->bindParam(':created_at', $data['created_at']);
        $statement->execute();
    }
    public function selectUsersByPlate($number)
    {
        $statement = $this->pdo->getConnection()->prepare('SELECT * FROM plates AS p INNER JOIN users AS u ON p.user_id = u.id WHERE p.number = :number');
        $statement->bindParam(':number', $number);
        $statement->execute();
        $userByPlate = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $userByPlate[0];
    }
    public function insertLog($request, $exception)
    {
        $statement = $this->pdo->getConnection()->prepare('INSERT INTO log (request, exception) VALUES (:request, :exception)');
        $statement->bindParam(':request', $request);
        $statement->bindParam(':exception', $exception);
        $statement->execute();
    }
}