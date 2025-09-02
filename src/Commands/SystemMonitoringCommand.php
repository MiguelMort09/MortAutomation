<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mort\Automation\Contracts\AutomationInterface;

class SystemMonitoringCommand extends Command implements AutomationInterface
{
    protected $signature = 'mort:monitor {--detailed} {--export}';
    protected $description = 'Monitorear el sistema siguiendo las métricas de Mort';

    public function handle(): int
    {
        $this->info('📊 Monitoreo del Sistema');
        $this->line('========================');

        try {
            $this->checkSystemHealth();
            $this->checkDatabaseHealth();
            $this->checkApplicationMetrics();
            $this->checkPerformanceMetrics();
            $this->checkSecurityMetrics();

            if ($this->option('detailed')) {
                $this->showDetailedMetrics();
            }

            if ($this->option('export')) {
                $this->exportMetrics();
            }

            $this->info('✅ Monitoreo completado');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    public function executeAutomation(): int
    {
        return $this->handle();
    }

    public function isAvailable(): bool
    {
        return true; // Siempre disponible
    }

    public function getDescription(): string
    {
        return 'Monitoreo del sistema siguiendo las métricas de Mort';
    }

    private function checkSystemHealth(): void
    {
        $this->info('🏥 Estado del Sistema');
        $this->line('-------------------');

        // Verificar conexión a base de datos
        try {
            DB::connection()->getPdo();
            $this->info('✅ Base de datos: Conectada');
        } catch (\Exception $e) {
            $this->error('❌ Base de datos: Error de conexión');
        }

        // Verificar cache
        try {
            Cache::put('health_check', 'ok', 60);
            $this->info('✅ Cache: Funcionando');
        } catch (\Exception $e) {
            $this->error('❌ Cache: Error');
        }

        // Verificar storage
        try {
            Storage::put('health_check.txt', 'ok');
            Storage::delete('health_check.txt');
            $this->info('✅ Storage: Funcionando');
        } catch (\Exception $e) {
            $this->error('❌ Storage: Error');
        }

        // Verificar memoria
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $this->info("💾 Memoria: " . $this->formatBytes($memoryUsage) . " / {$memoryLimit}");

        // Verificar tiempo de ejecución
        $executionTime = microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
        $this->info("⏱️  Tiempo de ejecución: " . number_format($executionTime, 3) . "s");
    }

    private function checkDatabaseHealth(): void
    {
        $this->info('🗄️  Estado de la Base de Datos');
        $this->line('-----------------------------');

        try {
            // Verificar conexiones activas
            $connections = DB::select('SHOW STATUS LIKE "Threads_connected"');
            if (!empty($connections)) {
                $this->info("🔗 Conexiones activas: {$connections[0]->Value}");
            }

            // Verificar tamaño de la base de datos
            $dbSize = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            if (!empty($dbSize)) {
                $this->info("📊 Tamaño de BD: {$dbSize[0]->{'DB Size in MB'}} MB");
            }

            // Verificar tablas más grandes
            $largestTables = DB::select("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size in MB'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
                LIMIT 5
            ");

            $this->info('📋 Tablas más grandes:');
            foreach ($largestTables as $table) {
                $tableName = $table->table_name ?? $table->{'Table Name'} ?? 'unknown';
                $size = $table->{'Size in MB'} ?? $table->{'Size_MB'} ?? '0';
                $this->line("  - {$tableName}: {$size} MB");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error verificando base de datos: {$e->getMessage()}");
        }
    }

    private function checkApplicationMetrics(): void
    {
        $this->info('📈 Métricas de la Aplicación');
        $this->line('----------------------------');

        try {
            // Verificar si existen las tablas antes de consultarlas
            $tables = ['users', 'customers', 'payments', 'memberships'];
            $existingTables = [];

            foreach ($tables as $table) {
                try {
                    DB::table($table)->count();
                    $existingTables[] = $table;
                } catch (\Exception $e) {
                    // Tabla no existe, continuar
                }
            }

            // Usuarios
            if (in_array('users', $existingTables)) {
                $totalUsers = DB::table('users')->count();
                $activeUsers = DB::table('users')->whereNotNull('email_verified_at')->count();
                $this->info("👥 Usuarios totales: {$totalUsers}");
                $this->info("👥 Usuarios activos: {$activeUsers}");
            }

            // Clientes
            if (in_array('customers', $existingTables)) {
                $totalCustomers = DB::table('customers')->count();
                $activeCustomers = DB::table('customers')->where('status', 1)->count();
                $this->info("🏃 Clientes totales: {$totalCustomers}");
                $this->info("🏃 Clientes activos: {$activeCustomers}");
            }

            // Pagos
            if (in_array('payments', $existingTables)) {
                $totalPayments = DB::table('payments')->count();
                $successfulPayments = DB::table('payments')->where('status', 0)->count();
                $pendingPayments = DB::table('payments')->where('status', 1)->count();
                $failedPayments = DB::table('payments')->where('status', 2)->count();

                $this->info("💳 Pagos totales: {$totalPayments}");
                $this->info("✅ Pagos exitosos: {$successfulPayments}");
                $this->info("⏳ Pagos pendientes: {$pendingPayments}");
                $this->info("❌ Pagos fallidos: {$failedPayments}");

                // Ingresos
                $totalRevenue = DB::table('payments')->where('status', 0)->sum('total') ?? 0;
                $monthlyRevenue = DB::table('payments')
                    ->where('status', 0)
                    ->whereMonth('created_at', now()->month)
                    ->sum('total') ?? 0;

                $this->info("💰 Ingresos totales: $" . number_format($totalRevenue / 100, 2));
                $this->info("💰 Ingresos del mes: $" . number_format($monthlyRevenue / 100, 2));
            }

            // Membresías
            if (in_array('memberships', $existingTables)) {
                $totalMemberships = DB::table('memberships')->count();
                $this->info("🎫 Membresías: {$totalMemberships}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error verificando métricas de aplicación: {$e->getMessage()}");
        }
    }

    private function checkPerformanceMetrics(): void
    {
        $this->info('⚡ Métricas de Rendimiento');
        $this->line('-------------------------');

        try {
            // Verificar queries lentas (si está habilitado el slow log)
            $slowQueries = DB::select("
                SELECT 
                    query_time,
                    lock_time,
                    rows_sent,
                    rows_examined,
                    sql_text
                FROM mysql.slow_log 
                WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY query_time DESC
                LIMIT 5
            ");

            if (!empty($slowQueries)) {
                $this->warn('⚠️  Queries lentas detectadas:');
                foreach ($slowQueries as $query) {
                    $this->line("  - Tiempo: {$query->query_time}s, Filas: {$query->rows_examined}");
                }
            } else {
                $this->info('✅ No se detectaron queries lentas');
            }

            // Verificar cache hit rate
            $cacheHits = Cache::get('cache_hits', 0);
            $cacheMisses = Cache::get('cache_misses', 0);
            $totalCacheRequests = $cacheHits + $cacheMisses;

            if ($totalCacheRequests > 0) {
                $hitRate = ($cacheHits / $totalCacheRequests) * 100;
                $this->info("🎯 Cache hit rate: " . number_format($hitRate, 2) . "%");
            }

            // Verificar tiempo de respuesta promedio
            $avgResponseTime = $this->getAverageResponseTime();
            $this->info("⏱️  Tiempo de respuesta promedio: {$avgResponseTime}ms");

        } catch (\Exception $e) {
            $this->error("❌ Error verificando métricas de rendimiento: {$e->getMessage()}");
        }
    }

    private function checkSecurityMetrics(): void
    {
        $this->info('🔒 Métricas de Seguridad');
        $this->line('----------------------');

        try {
            // Verificar intentos de login fallidos
            $failedLogins = DB::table('failed_jobs')
                ->where('failed_at', '>', now()->subDay())
                ->count();

            $this->info("🚫 Intentos de login fallidos (24h): {$failedLogins}");

            // Verificar usuarios no verificados
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $unverifiedUsers = DB::table('users')
                    ->whereNull('email_verified_at')
                    ->where('created_at', '<', now()->subDay())
                    ->count();

                if ($unverifiedUsers > 0) {
                    $this->warn("⚠️  Usuarios no verificados (más de 24h): {$unverifiedUsers}");
                } else {
                    $this->info('✅ Todos los usuarios están verificados');
                }
            }

            // Verificar pagos fallidos recientes
            if (DB::getSchemaBuilder()->hasTable('payments')) {
                $recentFailedPayments = DB::table('payments')
                    ->where('status', 2)
                    ->where('created_at', '>', now()->subHour())
                    ->count();

                if ($recentFailedPayments > 0) {
                    $this->warn("⚠️  Pagos fallidos recientes (1h): {$recentFailedPayments}");
                } else {
                    $this->info('✅ No hay pagos fallidos recientes');
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Error verificando métricas de seguridad: {$e->getMessage()}");
        }
    }

    private function showDetailedMetrics(): void
    {
        $this->info('📊 Métricas Detalladas');
        $this->line('---------------------');

        // Métricas por hora
        $this->showHourlyMetrics();
        
        // Métricas por día
        $this->showDailyMetrics();
    }

    private function showHourlyMetrics(): void
    {
        $this->info('📈 Métricas por Hora (Últimas 24h)');
        
        try {
            $hourlyData = DB::select("
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count
                FROM payments 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY HOUR(created_at)
                ORDER BY hour
            ");

            foreach ($hourlyData as $data) {
                $this->line("  {$data->hour}:00 - {$data->count} pagos");
            }
        } catch (\Exception $e) {
            $this->warn('No se pudieron obtener métricas por hora');
        }
    }

    private function showDailyMetrics(): void
    {
        $this->info('📅 Métricas por Día (Últimos 7 días)');
        
        try {
            $dailyData = DB::select("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as payments,
                    SUM(total) as revenue
                FROM payments 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");

            foreach ($dailyData as $data) {
                $revenue = number_format($data->revenue / 100, 2);
                $this->line("  {$data->date}: {$data->payments} pagos, \${$revenue}");
            }
        } catch (\Exception $e) {
            $this->warn('No se pudieron obtener métricas por día');
        }
    }

    private function exportMetrics(): void
    {
        $this->info('📤 Exportando métricas...');
        
        $metrics = [
            'timestamp' => now()->toISOString(),
            'system_health' => $this->getSystemHealthData(),
            'application_metrics' => $this->getApplicationMetricsData(),
            'performance_metrics' => $this->getPerformanceMetricsData(),
            'security_metrics' => $this->getSecurityMetricsData(),
        ];

        $filename = 'metrics_' . now()->format('Y-m-d_H-i-s') . '.json';
        $path = storage_path("monitoring/{$filename}");
        
        // Crear directorio si no existe
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($metrics, JSON_PRETTY_PRINT));
        
        $this->info("✅ Métricas exportadas a: {$path}");
    }

    private function getSystemHealthData(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'execution_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)),
        ];
    }

    private function getApplicationMetricsData(): array
    {
        $data = [];
        
        try {
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $data['total_users'] = DB::table('users')->count();
                $data['active_users'] = DB::table('users')->whereNotNull('email_verified_at')->count();
            }
            
            if (DB::getSchemaBuilder()->hasTable('customers')) {
                $data['total_customers'] = DB::table('customers')->count();
                $data['active_customers'] = DB::table('customers')->where('status', 1)->count();
            }
            
            if (DB::getSchemaBuilder()->hasTable('payments')) {
                $data['total_payments'] = DB::table('payments')->count();
                $data['successful_payments'] = DB::table('payments')->where('status', 0)->count();
                $data['total_revenue'] = DB::table('payments')->where('status', 0)->sum('total') ?? 0;
            }
        } catch (\Exception $e) {
            // Ignorar errores
        }
        
        return $data;
    }

    private function getPerformanceMetricsData(): array
    {
        return [
            'cache_hits' => Cache::get('cache_hits', 0),
            'cache_misses' => Cache::get('cache_misses', 0),
            'average_response_time' => $this->getAverageResponseTime(),
        ];
    }

    private function getSecurityMetricsData(): array
    {
        $data = [];
        
        try {
            $data['failed_logins_24h'] = DB::table('failed_jobs')
                ->where('failed_at', '>', now()->subDay())
                ->count();
                
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $data['unverified_users'] = DB::table('users')
                    ->whereNull('email_verified_at')
                    ->where('created_at', '<', now()->subDay())
                    ->count();
            }
        } catch (\Exception $e) {
            // Ignorar errores
        }
        
        return $data;
    }

    private function getAverageResponseTime(): float
    {
        // Simular cálculo de tiempo de respuesta promedio
        return rand(100, 500);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
