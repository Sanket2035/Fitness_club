<?php

/**
 * Database Class
 * 
 * Handles all database operations using PDO with a Singleton pattern.
 * Provides secure database access with prepared statements and
 * transaction support.
 * 
 * @package FitnessClub
 * @version 1.0
 */

require_once 'config.php';

class Database
{
    /** @var Database|null Singleton instance */
    private static $instance = null;

    /** @var PDO Database connection */
    private $conn;

    /**
     * Private constructor to prevent direct instantiation
     * Establishes database connection using PDO
     * 
     * @throws Exception If connection fails
     */
    private function __construct()
    {
        try {
            // Create DSN string for MySQL connection
            $dsn = "mysql:host=" . DB_HOST .
                ";dbname=" . DB_NAME .
                ";charset=" . DB_CHARSET;

            // PDO connection options for better security and performance
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database Single instance of database connection
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get raw PDO connection
     * 
     * @return PDO Active database connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Execute a query with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return PDOStatement Executed statement
     * @throws Exception If query fails
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }

    /**
     * Fetch a single row from the database
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array|false Single row of data or false if no results
     * @throws Exception If fetch fails
     */
    public function fetchOne($sql, $params = [])
    {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("FetchOne Error: " . $e->getMessage());
            throw new Exception("Failed to fetch data");
        }
    }

    /**
     * Fetch all matching rows from the database
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array Array of matching rows
     * @throws Exception If fetch fails
     */
    public function fetchAll($sql, $params = [])
    {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("FetchAll Error: " . $e->getMessage());
            throw new Exception("Failed to fetch data");
        }
    }

    /**
     * Insert a record into the database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @return int Last insert ID
     * @throws Exception If insert fails
     */
    public function insert($table, $data)
    {
        try {
            $fields = array_keys($data);
            $values = array_fill(0, count($fields), '?');

            $sql = "INSERT INTO " . $table .
                " (" . implode(", ", $fields) . ") " .
                "VALUES (" . implode(", ", $values) . ")";

            $this->query($sql, array_values($data));
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            error_log("Insert Error: " . $e->getMessage());
            throw new Exception("Failed to insert data");
        }
    }

    /**
     * Update records in the database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @param string $where WHERE clause of the query
     * @param array $whereParams Parameters for WHERE clause
     * @return bool True on success
     * @throws Exception If update fails
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        try {
            $fields = array_keys($data);
            $set = array_map(function ($field) {
                return "$field = ?";
            }, $fields);

            $sql = "UPDATE " . $table .
                " SET " . implode(", ", $set) .
                " WHERE " . $where;

            $params = array_merge(array_values($data), $whereParams);
            $this->query($sql, $params);

            return true;
        } catch (Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            throw new Exception("Failed to update data");
        }
    }

    /**
     * Delete records from the database
     * 
     * @param string $table Table name
     * @param string $where WHERE clause of the query
     * @param array $params Parameters for WHERE clause
     * @return bool True on success
     * @throws Exception If delete fails
     */
    public function delete($table, $where, $params = [])
    {
        try {
            $sql = "DELETE FROM " . $table . " WHERE " . $where;
            $this->query($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("Delete Error: " . $e->getMessage());
            throw new Exception("Failed to delete data");
        }
    }

    public function execute($query, $params = [])
    {
        try {
            $stmt = $this->query($query, $params);
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            throw new Exception("Failed to execute query");
        }
    }

    /**
     * Begin a database transaction
     * 
     * @return bool True on success
     */
    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit a database transaction
     * 
     * @return bool True on success
     */
    public function commit()
    {
        return $this->conn->commit();
    }

    /**
     * Rollback a database transaction
     * 
     * @return bool True on success
     */
    public function rollback()
    {
        return $this->conn->rollBack();
    }

    /**
     * Prevent cloning of the instance (Singleton pattern)
     */
    private function __clone() {}

    /**
     * Prevent unserialize of the instance (Singleton pattern)
     */
    function __wakeup() {}
}
