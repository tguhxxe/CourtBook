<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class DesignTokenTest extends TestCase
{
    public function test_documented_palette_matches_canonical_css(): void
    {
        $root = dirname(__DIR__, 2);
        $design = file_get_contents($root.'/DESIGN.md');
        $css = file_get_contents($root.'/resources/css/app.css');
        $tokens = Yaml::parse(explode('---', $design)[1]);
        $this->assertCount(9, $tokens['colors']);
        foreach ($tokens['colors'] as $name => $value) {
            $this->assertStringContainsString('--color-'.$name.':'.$value, $css);
        }
    }
}
