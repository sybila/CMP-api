<?php


namespace App\Helpers;


use Doctrine\DBAL\Logging\SQLLogger;
use PHPSQLParser\PHPSQLParser;

class CMPSQLLogger implements SQLLogger
{
    /**
     * Executed SQL queries.
     *
     * @var array<int, array<string, mixed>>
     */
    public $queries = [];

    /**
     * If Debug Stack is enabled (log queries) or not.
     *
     * @var bool
     */
    public $enabled = true;


    /** @var int */
    public $currentQuery = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        if (! $this->enabled) {
            return;
        }
        //START TRANSACTION and COMMIT should trigger with em->flush, however, it might be good to check it in future
        $parser = new PHPSQLParser();
        if (str_starts_with($sql, 'UPDATE')) {
            $parsed = $parser->parse($sql);
            $table = current($parsed['UPDATE'])['table'];
            $i = 0;
            foreach ($parsed['SET'] as $expr) {
                parse_str($expr['base_expr'], $res);
                $data[substr(array_key_first($res),0,-1)] = $params[$i];
                $i++;
            }
            $this->queries[++$this->currentQuery] = [
                'table' => $table,
                'data' => json_encode($data),
                dump(json_encode($data))
            ];
        }
        elseif (str_starts_with($sql, 'DELETE')) {
            $parsed = $parser->parse($this->replaceSqlWildcards($sql, $params));
            $table = current($parsed['FROM'])['table'];
            $this->queries[++$this->currentQuery] = [
                'table' => $table,
                'data' => null
            ];
        }
        elseif (str_starts_with($sql, 'INSERT')) {
            $parsed = $parser->parse($this->replaceSqlWildcards($sql, $params));
            $table = $parsed['INSERT'][1]['table'];
            $this->queries[++$this->currentQuery] = [
                'table' => $table,
                'data' => null
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if (! $this->enabled) {
            return;
        }
    }

    protected function replaceSqlWildcards(string $sql, array $params): string
    {
        $haystack = $sql;
        $needle = '?';
        $pos = 0;
        foreach ($params as $param) {
            $pos = strpos($haystack, $needle, $pos);
            if ($pos !== false) {
                $haystack = substr_replace($haystack, $param, $pos, strlen($needle));
            }
        }
        return $haystack;
    }

}