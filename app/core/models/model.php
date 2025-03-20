<?php

abstract class model
{
    protected $db;
    protected $table;
    protected $columns;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function get_all()
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table);
        $query->execute();
        return $query->fetchAll();
    }

    public function get_by_id($id)
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public function insert($data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->columns));
        unset($filteredData['id']); // Ensure ID is never included

        // Build columns and placeholders dynamically
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));

        // Construct the SQL query
        $sql = 'INSERT INTO ' . $this->table . ' (' . $columns . ') VALUES (' . $placeholders . ')';
        $query = $this->db->prepare($sql);

        // Debugging: Log SQL and data before execution
        error_log("SQL Query: " . $sql);
        error_log("Data: " . print_r($filteredData, true));

        // Execute the query
        $query->execute($filteredData);
        return $this->db->lastInsertId();
    }


    public function update($data)
    {
        if (!isset($data['id'])) {
            throw new InvalidArgumentException("Missing 'id' for update.");
        }

        $filteredData = array_intersect_key($data, array_flip($this->columns));
        unset($filteredData['id']);

        // Ensure we only update provided fields
        $set = implode(', ', array_map(function ($col) {
            return "$col = :$col";
        }, array_keys($filteredData)));

        // Construct SQL query
        $sql = 'UPDATE ' . $this->table . ' SET ' . $set . ' WHERE id = :id';
        $query = $this->db->prepare($sql);

        // Add 'id' back for binding
        $filteredData['id'] = $data['id'];

        // Debugging: Log SQL and data
        error_log("SQL Query: " . $sql);
        error_log("Data: " . print_r($filteredData, true));

        // Execute query
        return $query->execute($filteredData);
    }


    public function delete($id)
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException("Invalid 'id' provided for deletion.");
        }

        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');

        // Debugging: Log SQL query
        error_log("SQL Query: DELETE FROM " . $this->table . " WHERE id = $id");

        return $query->execute(['id' => $id]);
    }

}

?>