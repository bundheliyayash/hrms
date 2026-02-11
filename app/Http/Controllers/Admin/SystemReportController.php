<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemReportController extends Controller
{
    /**
     * Display system/technical report with database schema information.
     * Only accessible by admin users.
     */
    public function index()
    {
        // Get all tables in the database
        $tables = $this->getAllTables();
        
        $schemaInfo = [];
        
        foreach ($tables as $tableName) {
            $columns = Schema::getColumnListing($tableName);
            $columnDetails = [];
            
            foreach ($columns as $column) {
                $columnDetails[] = [
                    'name' => $column,
                    'type' => Schema::getColumnType($tableName, $column),
                ];
            }
            
            $schemaInfo[] = [
                'table' => $tableName,
                'columns' => $columnDetails,
                'column_count' => count($columns),
            ];
        }
        
        return view('admin.reports.system', [
            'schemaInfo' => $schemaInfo,
            'tableCount' => count($tables),
        ]);
    }
    
    /**
     * Get all table names from the database.
     */
    private function getAllTables(): array
    {
        $tables = [];
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            $results = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . DB::connection()->getDatabaseName();
            foreach ($results as $row) {
                $tables[] = $row->$key;
            }
        } elseif ($driver === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($results as $row) {
                $tables[] = $row->name;
            }
        } elseif ($driver === 'pgsql') {
            $results = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            foreach ($results as $row) {
                $tables[] = $row->tablename;
            }
        }
        
        sort($tables);
        return $tables;
    }
}
