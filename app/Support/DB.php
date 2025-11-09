<?php
namespace App\Support;

use PDO;

class DB
{
    public PDO $pdo;
    public function __construct(array $cfg)
    {
        if ($cfg['driver'] === 'mysql') {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',$cfg['host'],$cfg['port'],$cfg['name']);
        } else {
            throw new \RuntimeException('Only MySQL supported in this skeleton.');
        }
        $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    public function query(string $sql, array $params=[]){
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public function lastInsertId(){ return (int)$this->pdo->lastInsertId(); }
}
