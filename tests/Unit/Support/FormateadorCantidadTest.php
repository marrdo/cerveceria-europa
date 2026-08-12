<?php

namespace Tests\Unit\Support;

use App\Support\Formato\FormateadorCantidad;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FormateadorCantidadTest extends TestCase
{
    /**
     * @return iterable<string, array{float|int|string|null, int, string}>
     */
    public static function cantidades(): iterable
    {
        yield 'entero sin relleno' => [2.000, 3, '2'];
        yield 'fraccion conserva precision util' => [1.250, 3, '1,25'];
        yield 'mil usa separador de miles' => [1000, 3, '1.000'];
        yield 'fraccion pequena' => [0.125, 3, '0,125'];
        yield 'unidad indivisible' => [14.8, 0, '15'];
        yield 'nulo' => [null, 3, '0'];
    }

    #[DataProvider('cantidades')]
    public function test_formatea_cantidades_sin_decimales_innecesarios(
        float|int|string|null $cantidad,
        int $maximoDecimales,
        string $esperado,
    ): void {
        $this->assertSame($esperado, FormateadorCantidad::formatear($cantidad, $maximoDecimales));
    }
}
