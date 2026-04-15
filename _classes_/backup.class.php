<?php



 /**
     * Get list of database tables.
     *
     * @return array|bool
     */
    /*public*/ function getTables()
    {
        $sql = 'SHOW TABLES';
        $query = $this->query($sql);

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Backup database tables or just a table.
     *
     * @param  string  $tables
     */
    /*public*/ function backup($backup_dir, $backup_tables = '*')
    {
        $tables = array();
        $data = "\n-- DATABASE BACKUP --\n\n";
        $data .= "--\n-- Date: " . date('d/m/Y H:i:s', time()) . "\n";
        $data .= "\n\n-- --------------------------------------------------------\n\n";
        if ($backup_tables == '*') {
            $tables = $this->getTables();
        } else {
            $tables = is_array($backup_tables) ? $backup_tables : explode(',', $backup_tables);
        }
        foreach ($tables as $table) {
            $sth = $this->prepare('SELECT count(*) FROM ' . $table);
            $sth->execute();
            $num_fields = $sth->fetch(PDO::FETCH_NUM);
            $num_fields = $num_fields[0];
            $result = $this->prepare('SELECT * FROM ' . $table);
            $result->execute();
            $data .= "--\n-- CREATE TABLE `" . $table . "`\n--";
            $data .= "\n\nDROP TABLE IF EXISTS `" . $table . '`;';
            $result2 = $this->prepare('SHOW CREATE TABLE ' . $table);
            $result2->execute();
            $row2 = $result2->fetch(PDO::FETCH_NUM);
            $row2[1] = preg_replace("/AUTO_INCREMENT=[\w]*./", '', $row2[1]);
            $row2[1] = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2[1]);
            $data .= "\n\n" . $row2[1] . ";\n\n";
            $data .= "-- --------------------------------------------------------\n\n";
            $data .= "--\n-- INSERT INTO table `" . $table . "`\n--\n\n";
            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $data .= 'INSERT INTO ' . $table . ' VALUES(' . implode(',', array_map(array(
                        $this,
                        'escape',
                    ), $row)) . ");\n";
                }
            }
        }
        $data .= "\n-- --------------------------------------------------------\n\n\n";
        $filename = $backup_dir . '/db-backup' . ((is_array($backup_tables)) ? '-' . (implode(',', $tables)) : '') . '-' . date('dmY', time()) . '-' . time() . '.sql';
        $f = fopen($filename, 'w+');
        fwrite($f, pack('CCC', 0xef, 0xbb, 0xbf));
        fwrite($f, $data);
        fclose($f);
    }

    /**
     * Escape variable.
     *
     * @param  string  $value
     */
    /*protected */function escape($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        if ((string)intval($value) === $value) {
            return (int)$value;
        }

        return $this->quote($value);
    }










 /**
     * Get SQL Query method.
     *
     * @param $query
     *
     * @return mixed|string
     *
     * @see    https://github.com/marcocesarato/PHP-Light-SQL-Parser-Class
     */
    /*public static*/ function parseMethod($query)
    {
        if (!empty(self::$parserMethod[$query])) {
            return self::$parserMethod[$query];
        }
        $methods = array(
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'RENAME',
            'SHOW',
            'SET',
            'DROP',
            'CREATE INDEX',
            'CREATE TABLE',
            'EXPLAIN',
            'DESCRIBE',
            'TRUNCATE',
            'ALTER',
        );
        $queries = self::parseQueries($query);
        foreach ($queries as $query) {
            foreach ($methods as $method) {
                $_method = str_replace(' ', '[\s]+', $method);
                if (preg_match('#^[\s]*' . $_method . '[\s]+#i', $query)) {
                    self::$parserMethod[$query] = $method;

                    return $method;
                }
            }
        }

        return '';
    }


    /**
     * Get SQL Query Tables.
     *
     * @param $_query
     *
     * @return array|mixed
     *
     * @see    https://github.com/marcocesarato/PHP-Light-SQL-Parser-Class
     */
    /*public static */function parseTables($_query)
    {
        $connectors = "OR|AND|ON|LIMIT|WHERE|JOIN|GROUP|ORDER|OPTION|LEFT|INNER|RIGHT|OUTER|SET|HAVING|VALUES|SELECT|\(|\)";
        if (!empty(self::$parserTables[$_query])) {
            return self::$parserTables[$_query];
        }
        $results = array();
        $queries = self::parseQueries($_query);
        foreach ($queries as $query) {
            $patterns = array(
                '#[\s]+FROM[\s]+(([\s]*(?!' . $connectors . ')[\w]+([\s]+(AS[\s]+)?(?!' . $connectors . ')[\w]+)?[\s]*[,]?)+)#i',
                '#[\s]*INSERT[\s]+INTO[\s]+([\w]+)#i',
                '#[\s]*UPDATE[\s]+([\w]+)#i',
                '#[\s]+[\s]+JOIN[\s]+([\w]+)#i',
                '#[\s]+TABLE[\s]+([\w]+)#i',
            );
            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $query, $matches, PREG_SET_ORDER);
                foreach ($matches as $val) {
                    $tables = explode(',', $val[1]);
                    foreach ($tables as $table) {
                        $table = trim(preg_replace('#[\s]+(AS[\s]+)[\w\.]+#i', '', $table));
                        $results[] = $table;
                    }
                }
            }
        }
        $tables = array_unique($results);

        self::$parserTables[$_query] = $tables;

        return $tables;
    }

