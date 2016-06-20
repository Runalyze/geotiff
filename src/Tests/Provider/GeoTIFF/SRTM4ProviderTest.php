<?php

/*
 * This file is part of the Runalyze DEM Reader.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Runalyze\DEM\Tests\Provider\GeoTIFF;

use Runalyze\DEM\Interpolation\BilinearInterpolation;
use Runalyze\DEM\Provider\GeoTIFF\SRTM4Provider;

class SRTM4ProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    const PATH_TO_FILES = __DIR__.'/../../testfiles/';

    /**
     * @var \Runalyze\DEM\Provider\GeoTIFF\SRTM4Provider
     */
    protected $Provider;

    public function setUp()
    {
        $this->Provider = new SRTM4Provider(self::PATH_TO_FILES);
        $this->Provider->setInterpolation(new BilinearInterpolation());
    }

    /**
     * @param string $filename
     */
    protected function checkFile($filename)
    {
        if (!file_exists(self::PATH_TO_FILES.$filename)) {
            $this->markTestSkipped('Required testfile "'.$filename.'" is not available.');
        }
    }

    public function testThatLocationOutOfBoundsIsRecognized()
    {
        $this->assertFalse($this->Provider->hasDataFor([
            [90.0, 0.1],
        ]));
    }

    public function testThatAvailableFileIsRecognized()
    {
        $this->checkFile('srtm_38_03.tif');

        $this->assertTrue($this->Provider->hasDataFor([
            [49.4, 7.7],
            [49.5, 7.6],
        ]));
    }

    public function testSingleElevationInGermany()
    {
        $this->checkFile('srtm_38_03.tif');

        $this->assertEquals(238, $this->Provider->getElevation(49.444722, 7.768889));
    }

    public function testThatUnknownElevationInSydneyIsGuessedBySurroundingValues()
    {
        $this->checkFile('srtm_67_19.tif');

        $this->assertEquals(3, $this->Provider->getElevation(-33.8705667, 151.1486337));
    }

    public function testMultipleElevationsInSydney()
    {
        $this->checkFile('srtm_67_19.tif');

        $this->assertEquals(
            [3, 3, 2],
            $this->Provider->getElevations(
                [-33.8706555, -33.8705667, -33.8704860],
                [151.1486918, 151.1486337, 151.1485585]
            )
        );
    }

    public function testLocationsInLondonLondon()
    {
        $this->checkFile('srtm_36_02.tif');

        $this->assertEquals(
            [18, 18, 21],
            $this->Provider->getElevations(
                [51.5073509, 51.5074509, 51.5075509],
                [-0.1277583, -0.1278583, -0.1279583]
            )
        );
    }

    public function testLocationsInWindhoek()
    {
        $this->checkFile('srtm_40_17.tif');

        $this->assertEquals(
            [1666, 1669, 1671],
            $this->Provider->getElevations(
                [-22.5700, -22.5705, -22.5710],
                [17.0836,  17.0841,  17.0846]
            )
        );
    }

    public function testNewYork()
    {
        $this->checkFile('srtm_22_04.tif');

        $this->assertEquals(
            [22, 25, 41],
            $this->Provider->getElevations(
                [40.7127,  40.7132,  40.7137],
                [-74.0059, -74.0064, -74.0069]
            )
        );
    }
}