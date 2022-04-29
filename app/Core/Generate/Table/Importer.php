<?php


namespace App\Core\Generate\Table;



use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;

class Importer
{

    private MySqlConnection $db;
    private string $connection;
    private bool $error = true;
    private string $msg = '';

    private array $tableList = [];

    private array $option = [];

    public function __construct(string $connection = '', array $option = [])
    {
        $this->connection = $connection;
        $this->db = DB::connection($connection);
    }

    private function findTables(): array
    {
        $listObj = $this->db->select("show tables;");
        $list = [];
        for ($i = 0; $i < sizeof($listObj); $i++) {
            foreach ($listObj[$i] as $key => $item) {
                $list[] = $item;
            }
        }
        return $list;
    }

    public function importTableList(): array
    {
        $list = $this->findTables();
        foreach ($list as $item) {
            $this->tableList[$item] = $this->importTable($item);
        }
        return $this->tableList;
    }

    public function importTable(string $tableName): Table
    {

        $tableObject = toArray($this->db->select("show create table $tableName;"));
        $createTableSql = toArray($tableObject)[0]["Create Table"];


        $resolve = new Resolve($createTableSql);
        $table = $resolve->getTable();
        return $table;
    }


    public function handle()
    {

    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @return mixed
     */
    public function getTableList()
    {
        return $this->tableList;
    }
}