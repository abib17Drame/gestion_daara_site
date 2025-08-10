<?php
/**
 * Supabase connection and utility functions
 * Version sans dépendances externes, utilisant cURL natif de PHP
 */
class Supabase {
    private $url;
    private $key;
    private static $instance = null;
    
    private function __construct() {
        $this->url = getenv('SUPABASE_URL') ?: 'https://jmvdiiecfkwrwyrychoc.supabase.co';
        $this->key = getenv('SUPABASE_API_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImptdmRpaWVjZmt3cnd5cnljaG9jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDQ1MDE0MjEsImV4cCI6MjA2MDA3NzQyMX0.khQtvQisPluf0maUh7Krf2CmENOhf0QAeXdWklVMzYo';
        
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Supabase();
        }
        return self::$instance;
    }
    
    /**
     * Exécute une requête cURL vers l'API Supabase
     */
    private function executeRequest($method, $endpoint, $data = null) {
        $url = $this->url . $endpoint;
        error_log("Supabase request URL: $url");
        
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->key,
                'Authorization: Bearer ' . $this->key,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ],
        ];
        
        if ($data !== null && ($method === 'POST' || $method === 'PATCH')) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            error_log("Supabase request data: " . json_encode($data));
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            error_log("Supabase cURL Error: $error");
            throw new Exception("cURL Error: $error");
        }
        
        if ($statusCode >= 400) {
            error_log("Supabase API Error: Status $statusCode, Response: $response");
            throw new Exception("API Error: Status $statusCode");
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Supabase JSON Decode Error: " . json_last_error_msg());
            error_log("Raw response: $response");
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }
        
        return $decodedResponse;
    }
    
    /**
     * Execute a SELECT query on a table
     * 
     * @param string $table The table name
     * @param array $params Query parameters
     * @return array The query result
     */
    public function select($table, $params = []) {
        try {
            $queryString = '';
            
            // Add filters
            if (isset($params['filter'])) {
                foreach ($params['filter'] as $column => $value) {
                    // Encode the value to handle spaces and special characters
                    $encodedValue = urlencode($value);
                    $queryString .= "&$column=eq.$encodedValue";
                }
            }
            
            // Add order by
            if (isset($params['order'])) {
                $queryString .= "&order=" . $params['order'];
            }
            
            // Add select columns
            if (isset($params['select'])) {
                $queryString .= "&select=" . $params['select'];
            }
            
            $endpoint = "/rest/v1/$table?$queryString";
            error_log("Supabase SELECT query: $endpoint");
            
            $result = $this->executeRequest('GET', $endpoint);
            if ($result === null) {
                error_log("Supabase SELECT returned null for query: $endpoint");
                return [];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Supabase SELECT error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Execute a specialized SELECT query with more complex conditions
     */
    public function customSelect($table, $conditions) {
        try {
            return $this->executeRequest('GET', "/rest/v1/$table?" . $conditions) ?: [];
        } catch (Exception $e) {
            error_log("Supabase customSelect error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Insert a record into a table
     * 
     * @param string $table The table name
     * @param array $data The data to insert
     * @return array|null The inserted row or null on failure
     */
    public function insert($table, $data) {
        try {
            return $this->executeRequest('POST', "/rest/v1/$table", $data);
        } catch (Exception $e) {
            error_log("Supabase INSERT error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update a record in a table
     * 
     * @param string $table The table name
     * @param array $data The data to update
     * @param array $conditions The conditions for the update
     * @return array|null The updated rows or null on failure
     */
    public function update($table, $data, $conditions) {
        try {
            $queryString = '';
            foreach ($conditions as $column => $value) {
                // Encoder la valeur pour gérer les espaces et les caractères spéciaux
                $encodedValue = urlencode($value);
                $queryString .= "$column=eq.$encodedValue&";
            }
            
            // Supprimer le dernier &
            $queryString = rtrim($queryString, '&');
            
            error_log("Supabase UPDATE query: $queryString");
            error_log("Supabase UPDATE data: " . json_encode($data));
            
            return $this->executeRequest('PATCH', "/rest/v1/$table?$queryString", $data);
        } catch (Exception $e) {
            error_log("Supabase UPDATE error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Delete a record from a table
     * 
     * @param string $table The table name
     * @param array $conditions The conditions for deletion
     * @return bool Success status
     */
    public function delete($table, $conditions) {
        try {
            $queryString = '';
            foreach ($conditions as $column => $value) {
                $queryString .= "$column=eq.$value&";
            }
            
            $result = $this->executeRequest('DELETE', "/rest/v1/$table?$queryString");
            return $result !== null;
        } catch (Exception $e) {
            error_log("Supabase DELETE error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count records in a table
     * 
     * @param string $table The table name
     * @param array $conditions Query conditions
     * @return int The count of records
     */
    public function count($table, $conditions = []) {
        try {
            $queryString = 'select=count';
            if (!empty($conditions)) {
                foreach ($conditions as $column => $value) {
                    $queryString .= "&$column=eq.$value";
                }
            }
            
            $result = $this->executeRequest('GET', "/rest/v1/$table?$queryString");
            
            if (is_array($result) && count($result) > 0 && isset($result[0]['count'])) {
                return (int)$result[0]['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Supabase COUNT error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get distinct values from a column
     * 
     * @param string $table The table name
     * @param string $column The column to get distinct values from
     * @return array The distinct values
     */
    public function distinct($table, $column) {
        try {
            $result = $this->executeRequest('GET', "/rest/v1/$table?select=$column");
            
            $distinctValues = [];
            if (is_array($result)) {
                foreach ($result as $row) {
                    if (isset($row[$column]) && !in_array($row[$column], $distinctValues)) {
                        $distinctValues[] = $row[$column];
                    }
                }
            }
            
            return $distinctValues;
        } catch (Exception $e) {
            error_log("Supabase DISTINCT error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute RPC (Remote Procedure Call)
     * 
     * @param string $function Function name
     * @param array $params Function parameters
     * @return mixed The function result
     */
    public function rpc($function, $params = []) {
        try {
            return $this->executeRequest('POST', "/rest/v1/rpc/$function", $params);
        } catch (Exception $e) {
            error_log("Supabase RPC error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Sign in user with email and password
     */
    public function signIn($email, $password) {
        try {
            return $this->executeRequest('POST', "/auth/v1/token?grant_type=password", [
                'email' => $email,
                'password' => $password
            ]);
        } catch (Exception $e) {
            error_log("Supabase auth error: " . $e->getMessage());
            return null;
        }
    }
}
?>
