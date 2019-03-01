<?php declare(strict_types = 1);

namespace Netmosfera\OpalTests;

use Netmosfera\Opal\Component;
use Netmosfera\Opal\Identifier;
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
        $supportedPackage = new Package(new Identifier("A"), new Identifier("B"));
        $manager->enablePreprocessorForPackage($supportedPackage, 42);
        $filteringPreprocessor = $manager->filteringPreprocessor();

        $originalNodes = [new Echo_([new LNumber(123)])];

        $package = new Package(new Identifier("A"), new Identifier("B"));
        $componentName = new Component([new Identifier("C")]);
        $component = new PackageComponent($package, $componentName, ".php");
        $expectNodes = array_merge($originalNodes, [$addNode]);
        $actualNodes = $filteringPreprocessor($component, $originalNodes);
        self::assertSame($expectNodes, $actualNodes);

        $package = new Package(new Identifier("C"), new Identifier("D"));
        $componentName = new Component([new Identifier("C")]);
        $component = new PackageComponent($package, $componentName, ".php");
        $actualNodes = $filteringPreprocessor($component, $originalNodes);
        self::assertSame($originalNodes, $actualNodes);
    }
}
