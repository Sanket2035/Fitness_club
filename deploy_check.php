<?php
/**
 * Deployment Configuration Checker
 * This script checks if the server meets all requirements for the Fitness Club website
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

class DeploymentChecker {
    private $results = [];
    private $isValid = true;

    /**
     * Run all deployment checks
     */
    public function runChecks() {
        // Check PHP version
        $this->checkPHPVersion();
        
        // Check required PHP extensions
        $this->checkPHPExtensions();
        
        // Check directory permissions
        $this->checkDirectoryPermissions();
        
        // Check database connection
        $this->checkDatabase();
        
        // Check configuration file
        $this->checkConfigFile();
        
        // Check upload limits
        $this->checkPHPLimits();
        
        // Display results
        $this->displayResults();
    }

    /**
     * Check PHP version
     */
    private function checkPHPVersion() {
        $required = '7.4.0';
        $current = phpversion();
        $status = version_compare($current, $required, '>=');
        
        $this->addResult(
            'PHP Version',
            "Required: $required, Current: $current",
            $status
        );
    }

    /**
     * Check required PHP extensions
     */
    private function checkPHPExtensions() {
        $required_extensions = [
            'pdo',
            'pdo_mysql',
            'gd',
            'mbstring',
            'xml',
            'curl'
        ];

        foreach ($required_extensions as $ext) {
            $status = extension_loaded($ext);
            $this->addResult(
                "PHP Extension: $ext",
                $status ? "Installed" : "Missing",
                $status
            );
        }
    }

    /**
     * Check directory permissions
     */
    private function checkDirectoryPermissions() {
        $directories = [
            'uploads' => '775',
            'temp' => '775',
            'includes' => '755',
            'admin' => '755'
        ];

        foreach ($directories as $dir => $required_perms) {
            if (!file_exists($dir)) {
                $this->addResult(
                    "Directory: $dir",
                    "Directory does not exist",
                    false
                );
                continue;
            }

            $perms = substr(sprintf('%o', fileperms($dir)), -3);
            $status = $perms >= $required_perms;
            
            $this->addResult(
                "Directory Permissions: $dir",
                "Required: $required_perms, Current: $perms",
                $status
            );
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabase() {
        if (!file_exists('includes/config.php')) {
            $this->addResult(
                "Database Configuration",
                "Config file missing",
                false
            );
            return;
        }

        require_once 'includes/config.php';
        
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->addResult(
                "Database Connection",
                "Successfully connected to database",
                true
            );
        } catch (PDOException $e) {
            $this->addResult(
                "Database Connection",
                "Failed: " . $e->getMessage(),
                false
            );
        }
    }

    /**
     * Check configuration file
     */
    private function checkConfigFile() {
        $required_constants = [
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'DB_PASS',
            'SITE_URL'
        ];

        foreach ($required_constants as $constant) {
            $status = defined($constant);
            $this->addResult(
                "Config Constant: $constant",
                $status ? "Defined" : "Missing",
                $status
            );
        }
    }

    /**
     * Check PHP limits
     */
    private function checkPHPLimits() {
        $limits = [
            'memory_limit' => '128M',
            'upload_max_filesize' => '10M',
            'post_max_size' => '10M',
            'max_execution_time' => '30'
        ];

        foreach ($limits as $limit => $required) {
            $current = ini_get($limit);
            $status = $this->compareSize($current, $required);
            
            $this->addResult(
                "PHP Limit: $limit",
                "Required: $required, Current: $current",
                $status
            );
        }
    }

    /**
     * Compare PHP size values (e.g., 128M, 10M)
     */
    private function compareSize($current, $required) {
        $current_bytes = $this->convertToBytes($current);
        $required_bytes = $this->convertToBytes($required);
        return $current_bytes >= $required_bytes;
    }

    /**
     * Convert PHP size values to bytes
     */
    private function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;
        
        switch($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Add a check result
     */
    private function addResult($check, $message, $status) {
        $this->results[] = [
            'check' => $check,
            'message' => $message,
            'status' => $status
        ];
        
        if (!$status) {
            $this->isValid = false;
        }
    }

    /**
     * Display check results
     */
    private function displayResults() {
        echo "<html><head><title>Deployment Check Results</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .result { margin: 10px 0; padding: 10px; border-radius: 4px; }
            .success { background: #d4edda; color: #155724; }
            .error { background: #f8d7da; color: #721c24; }
            .summary { font-size: 1.2em; margin: 20px 0; padding: 10px; }
        </style></head><body>";
        
        echo "<h1>Deployment Check Results</h1>";
        
        foreach ($this->results as $result) {
            $class = $result['status'] ? 'success' : 'error';
            echo "<div class='result $class'>";
            echo "<strong>{$result['check']}:</strong> {$result['message']}";
            echo "</div>";
        }
        
        $summaryClass = $this->isValid ? 'success' : 'error';
        $summaryText = $this->isValid ? 
            "All checks passed! The system is ready for deployment." : 
            "Some checks failed. Please fix the issues before deploying.";
        
        echo "<div class='summary $summaryClass'>$summaryText</div>";
        echo "</body></html>";
    }
}

// Run the deployment checker
$checker = new DeploymentChecker();
$checker->runChecks();