<?php

class Authentication
{
    private static $instance = null;
    private static $identity = null;
    private $conn = null;

    private function __construct()
    {
        if (isset($_SESSION['identity'])) {
            self::$identity = $_SESSION['identity'];
        }
        $this->conn = Connection::getConnection();
    }


    public static function getInstance()
    {

        if (self::$instance == null) {
            
            self::$instance = new Authentication();
        }
        return self::$instance;
    }

    public function selectId($username)
    {

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = :username");

    }

    public function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, email, password, name, lastname, role FROM users WHERE email = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $result = $stmt->fetch();

        if (password_verify($password, $result['password'])) {

            $_SESSION['identity'] = array('id' => $result['id'], 'username' => $result['email'], 'name' => $result['name'], 'lastname' => $result['lastname'], 'role' => $result['role']);
            
            
            self::$identity = $_SESSION['identity'];
            return true;
        } else {
            return false;
        }
    }

    public function selectUser($id)
    {
        $stmt = $this->conn->prepare("SELECT id, email, password, name, lastname, role FROM users WHERE id = :username");
        $stmt->bindParam(":username", $id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return array('id' => $result['id'], 'username' => $result['email'], 'name' => $result['name'], 'lastname' => $result['lastname'], 'role' => $result['role']);
    }


    public function updateUser($username, $password, $lastname, $id)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("update users set name=:username, password=:password, lastname= :lastname where id = :id");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":lastname", $lastname);
        $stmt->bindParam(":id", $id);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    public function register($username, $password, $name, $surname)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (email, password, name, lastname) VALUES (:username, :password, :name, :surname)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname", $surname);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
        return true;
    }

    public function registerUziv($username)
    {

        $stmt = $this->conn->prepare("INSERT INTO users (username, password, name, surname) VALUES (:username, :password, :name, :surname)");
        $stmt->bindParam(":username", $username);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    public function selectAllUzivatele(){
        $stmt = $this->conn->prepare("SELECT id, email, password, name, lastname, role FROM users");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $users = array();

        foreach($results as $result){
            array_push($users, array('id' => $result['id'], 'username' => $result['email'], 'name' => $result['name'], 'lastname' => $result['lastname'], 'role' => $result['role']));
        }

        return $users;
    }

    public function logout()
    {
        unset($_SESSION['identity']);
        self::$identity = null;
    }


    public function hasIdentity()
    {
        return self::$identity ? true : false;
    }


    public function getIdentity()
    {
        return self::$identity ? self::$identity : null;
    }


    public function isAdmin()
    {
        return self::$identity['role'] === "1" ? true : false;
    }
}
