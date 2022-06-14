<?php

    class DB {
        protected static $instance = null;

        protected function __construct() {}
        protected function __clone() {}

        public static function instance() {
            global $config;
            if (self::$instance === null) {
                $dsn = "sqlsrv:Server={$config['db']['host']};Database={$config['db']['name']}";
                try {
                    self::$instance = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::$instance->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);
                } catch (Exception $e) {
                    throw new PDOException("{$e->getMessage()} (on line {$e->getLine()})", (int)$e->getCode());
                }
            }
            return self::$instance;
        }

        public static function __callStatic($method, $args) {
            return call_user_func_array(array(self::instance(), $method), $args);
        }

        public static function run($sql, $args = []) {
            if (!$args)
                return self::instance()->query($sql);
            $stmt = self::instance()->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        }

        public static function prepare($sql) {
            return self::instance()->prepare($sql);
        }

        public static function close() {
            self::$instance = null;
        }
    }
