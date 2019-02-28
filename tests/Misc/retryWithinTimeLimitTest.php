<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests\Misc;

use PHPUnit\Framework\TestCase;
use function array_sum;
use function Netmosfera\Opal\Misc\retryWithinTimeLimit;

class retryWithinTimeLimitTest extends TestCase
{
    public function test_timeout(){
        $elapsed = [];
        $time = microtime(TRUE);

        $result = retryWithinTimeLimit(function() use(&$time, &$elapsed){
            $elapsed[] = microtime(TRUE) - $time;
            $time = microtime(TRUE);
            return FALSE;
        }, 2.0, 0.5);

        self::assertGreaterThanOrEqual(1.5, array_sum($elapsed));
        self::assertLessThanOrEqual(1.6, array_sum($elapsed));

        self::assertFalse($result);
    }

    public function test_success(){
        $elapsed = [];
        $time = microtime(TRUE);

        $result = retryWithinTimeLimit(function() use(&$time, &$elapsed){
            $elapsed[] = microtime(TRUE) - $time;
            $time = microtime(TRUE);
            return count($elapsed) > 3;
        }, 5.0, 0.5);

        self::assertGreaterThanOrEqual(1.5, array_sum($elapsed));
        self::assertLessThanOrEqual(1.6, array_sum($elapsed));

        self::assertTrue($result);
    }
}
