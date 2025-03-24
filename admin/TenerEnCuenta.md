Interes Simple Diario es una tasa de 0.5% diario
    1.000.000 GS
    * 0.1% diario
    -------------
        1.000 GS ---> diario a modo de intereses por no pagar a fecha
                        Tasa mensual: 3%
                        Tasa Trimensual: 9%
                        Tasa Semestral 18%
                        Tasa Anual: 36%         

----------------------------------------------------------------------------------------------------------------------------------------------------------

Interés Compuesto
Ejemplo de Codigo

<?php
function calcularDeuda($capitalInicial, $tasaDiaria, $dias) {
    // Convertimos la tasa porcentual a decimal
    $tasaDecimal = $tasaDiaria / 100;
    
    // Fórmula del interés compuesto: A = C * (1 + r)^t
    $montoFinal = $capitalInicial * pow((1 + $tasaDecimal), $dias);
    
    // Interés acumulado
    $interesGenerado = $montoFinal - $capitalInicial;

    return [
        "monto_total" => round($montoFinal, 2),
        "interes_acumulado" => round($interesGenerado, 2)
    ];
}

// Configuración inicial
$capitalInicial = 1000000; // 1.000.000 Gs
$tasaDiaria = 0.05; // 0.05% diario

// Definir los periodos a calcular
$periodos = [
    "Mensual (30 días)" => 30,
    "Bimestral (60 días)" => 60,
    "Trimestral (90 días)" => 90,
    "Cuatrimestral (120 días)" => 120,
    "Quimestral (150 días)" => 150,
    "Semestral (180 días)" => 180,
    "Anual (360 días)" => 360
];

// Calcular y mostrar los resultados
echo "<table border='1' cellspacing='0' cellpadding='5'>";
echo "<tr><th>Periodo</th><th>Monto Total (Gs)</th><th>Interés Acumulado (Gs)</th></tr>";

foreach ($periodos as $nombre => $dias) {
    $resultado = calcularDeuda($capitalInicial, $tasaDiaria, $dias);
    echo "<tr>
            <td>$nombre</td>
            <td>{$resultado['monto_total']}</td>
            <td>{$resultado['interes_acumulado']}</td>
          </tr>";
}

echo "</table>";
?>

---------------------------------------------------------------------------------------------------------------------------------------------------------

Interés Moratorio con Penalización Escalonada

<?php
/**
 * Calcula el monto final de una deuda con interés compuesto diario y penalizaciones escalonadas.
 *
 * @param float $capitalInicial Monto original de la deuda.
 * @param float $tasaDiaria     Tasa de interés diaria en porcentaje (ej.: 0.05 para 0.05%).
 * @param int   $dias           Número de días de atraso.
 *
 * @return array Arreglo con el monto con interés, penalizaciones y monto final.
 */
function calcularDeudaMoratoria($capitalInicial, $tasaDiaria, $dias) {
    // Convertir la tasa de porcentaje a decimal (ej.: 0.05% = 0.0005)
    $tasaDecimal = $tasaDiaria / 100;
    
    // Calcular el monto acumulado con interés compuesto diario: A = C * (1 + r)^t
    $montoConInteres = $capitalInicial * pow((1 + $tasaDecimal), $dias);
    
    // Inicializar la penalización
    $penalizacion = 0;
    
    // Aplicar penalizaciones escalonadas según días de atraso
    if ($dias >= 5) {
        $penalizacion += 10000; // Penalización al superar 5 días
    }
    if ($dias >= 15) {
        $penalizacion += 20000; // Penalización adicional al superar 15 días
    }
    if ($dias >= 30) {
        $penalizacion += 50000; // Penalización adicional al superar 30 días
    }
    
    // Monto final: monto con interés más las penalizaciones
    $montoFinal = $montoConInteres + $penalizacion;
    
    // Interés generado es la diferencia entre el monto con interés y el capital inicial
    $interesGenerado = $montoConInteres - $capitalInicial;
    
    return [
        "monto_con_interes" => round($montoConInteres, 2),
        "penalizacion"      => round($penalizacion, 2),
        "monto_final"       => round($montoFinal, 2),
        "interes_generado"  => round($interesGenerado, 2)
    ];
}

// --- Ejemplo de uso ---
$capitalInicial = 1000000; // 1.000.000 Gs
$tasaDiaria     = 0.05;     // 0.05% diario
// Ejemplo: calcular la deuda para 18 días de atraso
$dias           = 18;

$resultado = calcularDeudaMoratoria($capitalInicial, $tasaDiaria, $dias);

// Mostrar resultados en una tabla HTML
echo "<table border='1' cellspacing='0' cellpadding='5'>";
echo "<tr><th>Concepto</th><th>Monto (Gs)</th></tr>";
echo "<tr><td>Capital Inicial</td><td>" . number_format($capitalInicial, 2, ",", ".") . "</td></tr>";
echo "<tr><td>Días de atraso</td><td>" . $dias . " días</td></tr>";
echo "<tr><td>Monto con Interés Compuesto</td><td>" . number_format($resultado['monto_con_interes'], 2, ",", ".") . "</td></tr>";
echo "<tr><td>Interés Generado</td><td>" . number_format($resultado['interes_generado'], 2, ",", ".") . "</td></tr>";
echo "<tr><td>Penalizaciones</td><td>" . number_format($resultado['penalizacion'], 2, ",", ".") . "</td></tr>";
echo "<tr><td><strong>Monto Final a Pagar</strong></td><td><strong>" . number_format($resultado['monto_final'], 2, ",", ".") . "</strong></td></tr>";
echo "</table>";
?>




