<?php

class DataBase
{
    public $db;

    function __construct()
    {
        $config = require(dirname(__FILE__) . '/../config/config.php');

        $this->db = new mysqli($config['servername'], $config['username'], $config['password'], $config['database']);

        if ($this->db->errno) {
            echo 'Ошибка в подключении к базе данных (' . $this->db->errno. '): ' . $this->db->error;
            exit();
        }
    }

    /**
     * Метод выборки данных из базы данных
     * @param string $sql
     * @return array|null
     */
    public function dbSelectQuery(string $sql): array|null
    {
        $query = $this->db->query($sql);

        if (!$query) {
            echo "Ошибка: " . $this->db->error;
        }

        while ($row = $query->fetch_assoc())
        {
            $result[] = $row;
        }

        return $result ?? [];
    }

    /**
     * Метод для выполнения вставки данных в базу данных
     * @param string $sql
     * @return string|int
     */
    public function dbQuery(string $sql): string|int
    {
        $query = $this->db->query($sql);

        if (!$query) {
            echo "Ошибка: " . $this->db->error;
        } else {
            return mysqli_insert_id($this->db);
        }
    }

    /**
     * Метод получения следующего id после последнего записанного из указанной таблицы
     * @param string $table
     * @return int
     */
    public function lastIdQuery(string $table): int
    {
        $sql = "SELECT id FROM `$table` ORDER BY id DESC LIMIT 1";
        $query = $this->db->query($sql);

        $result = $query->fetch_assoc();

        return empty($result) ? 1 : $result[0]+1;
    }

    /**
     * Метод получения последнего id указанного каннала из таблицы messages
     * @param string $channel
     * @return int
     */
    public function lastMessageIdQuery(string $channel): int
    {
        $sql = "SELECT message_id FROM messages WHERE channel = '$channel' ORDER BY id DESC LIMIT 1";
        $query = $this->db->query($sql);

        $result = $query->fetch_assoc();

        return empty($result) ? 0 : $result['message_id'];
    }

    function __destruct()
    {
        $this->db->close();
    }
}
