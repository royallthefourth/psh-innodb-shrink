<?php

namespace Shrinker;

use PHPUnit\Framework\TestCase;
use RoyallTheFourth\SmoothPdo\DataObject;

class TableTest extends TestCase
{
    public function testShouldShrink() {
        $tbl = new Table('test', 'main', 10, 100);
        $this->assertFalse($tbl->ShouldShrink(0.2));
        $this->assertTrue($tbl->ShouldShrink(0.05));
    }

    public function testShouldZero() {
        $tbl = new Table('test', 'main', 0, 100);
        $this->assertFalse($tbl->ShouldShrink(0));
    }

    public function testShrink() {
        $spdo = $this->createMock(DataObject::class);
        $spdo->expects($this->once())
            ->method('prepare')
            ->withAnyParameters();
        $tbl = new Table('test', 'main', 0, 0);

        try {
            $tbl->Shrink($spdo);
        } catch (\Exception $e) {
            $this->fail($e->getTraceAsString());
        }
    }

    public function testShrinkExcept() {
        $spdo = $this->createMock(DataObject::class);
        $spdo->expects($this->once())
            ->method('prepare')
            ->withAnyParameters()
            ->willThrowException(new \Exception('bogus exception'));
        $tbl = new Table('test', 'main', 0, 0);

        $this->expectException('Exception');
        $tbl->Shrink($spdo);
    }
}
