<?php

declare(strict_types=1);

namespace Shopie\Benchmark;

require_once __DIR__ . '/../../vendor/autoload.php';

use Shopie\DiContainer\Contracts\ResettableInterface;
use Shopie\DiContainer\ServiceCollection;

/**
 * 1. Mock Resettable Service
 */
class TenantProvider implements ResettableInterface
{
    public array $data = [];
    public function reset(): void { $this->data = []; }
}

/**
 * 2. Setup Benchmark Parameters
 */
$container = new ServiceCollection();
$totalServices = 1000;
$resettableCount = 100; // 10% of services are resettable
$iterations = 10000;   // Simulate 10k requests

echo "--- Shopie DI Reset Benchmark ---\n";
echo "Registering $totalServices services ($resettableCount resettable)...\n";

for ($i = 0; $i < $totalServices; $i++) {
    $id = "service_$i";
    if ($i < $resettableCount) {
        $container->setObject($id, new TenantProvider());
    } else {
        $container->setObject($id, new \stdClass());
    }
}

// Ensure GC is clean before starting
gc_collect_cycles();
$initialMemory = memory_get_usage(true);
$startTime = microtime(true);

/**
 * 3. Run Benchmark Loop
 */
for ($j = 0; $j < $iterations; $j++) {
    // Simulate a service being swapped mid-worker (Testing your cleanup logic)
    if ($j % 100 === 0) {
        $container->setObject("service_0", new TenantProvider());
    }
    
    $container->resetAll();
}

$endTime = microtime(true);
$finalMemory = memory_get_usage(true);
gc_collect_cycles(); // Collect anything eligible for destruction
$afterGcMemory = memory_get_usage(true);

/**
 * 4. Report Results
 */
$totalTime = $endTime - $startTime;
$avgLatencyMs = ($totalTime / $iterations) * 1000;

// Throughput Formula: Total Iterations / Total Time in Seconds
$requestsPerSecond = $iterations / $totalTime;

$finalMemory = memory_get_usage(true);
gc_collect_cycles();
$afterGcMemory = memory_get_usage(true);
$memoryLeak = $afterGcMemory - $initialMemory;

echo "---------------------------------\n";
echo "Total Time:      " . number_format($totalTime, 4) . "s\n";
echo "Avg Latency:     " . number_format($avgLatencyMs, 6) . "ms per request\n";
echo "THROUGHPUT:      " . number_format($requestsPerSecond, 0) . " req/sec\n";
echo "Peak Memory:     " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
echo "Memory Delta:    " . ($memoryLeak === 0 ? "0 bytes (PERFECT)" : $memoryLeak . " bytes") . "\n";
echo "---------------------------------\n";

if ($memoryLeak > 0) {
    echo "WARNING: Memory leak detected! Check setObject() cleanup.\n";
} else {
    echo "SUCCESS: DI container is worker-safe and leak-proof.\n";
}