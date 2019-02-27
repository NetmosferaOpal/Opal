<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Package;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PerPackagePreprocessorManager;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Echo_;
use PHPUnit\Framework\TestCase;
use function array_merge;

class PerPackagePreprocessorManagerTest extends TestCase
{
    public function test(){
        /** @var PerPackagePreprocessorManager $manager */

        $addNode = new Echo_([new LNumber(456)]);
        $preprocessor = function(
            PackageComponent $component, Array $nodes
        ) use($addNode, &$manager): array{
            self::assertSame(42, $manager->dataOfPackage($component->package));
            $nodes[] = $addNode;
            return $nodes;
        };

        $manager = new PerPackagePreprocessorManager($preprocessor);
        $manager->enablePreprocessorForPackage(new Package("A", "B"), 42);
        $filteringPreprocessor = $manager->filteringPreprocessor();

        $nodes = [new Echo_([new LNumber(123)])];

        $package = new Package("A", "B");
        $component = new PackageComponent($package, ["C"], ".php");
        $actualNodes = $filteringPreprocessor($component, $nodes);
        $expectNodes = array_merge($nodes, [$addNode]);
        self::assertSame($expectNodes, $actualNodes);

        $nodes = [new Echo_([new LNumber(123)])];
        $package = new Package("XXX", "XXX");
        $component = new PackageComponent($package, ["C"], ".php");
        $actualNodes = $filteringPreprocessor($component, $nodes);
        self::assertSame($nodes, $actualNodes);
    }
}
