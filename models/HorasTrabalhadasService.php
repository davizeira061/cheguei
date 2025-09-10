<?php

class HorasTrabalhadasService {

    const JORNADA_DIARIA_SEGUNDOS = 8 * 3600; // 8 horas

    /**
     * Processa os registros de ponto para calcular as horas trabalhadas.
     *
     * @param array $pontos Registros de ponto ordenados por data_hora ASC.
     * @return array Um array com o resumo por dia e o total do período.
     */
    public function calcularHorasTrabalhadas(array $pontos): array {
        $dias = $this->agruparPontosPorDia($pontos);
        $resumoDiario = [];
        $totalSegundosPeriodo = 0;
        $bancoHorasSaldoPeriodo = 0;

        foreach ($dias as $data => $registrosDoDia) {
            $resultadoDia = $this->calcularHorasDia($registrosDoDia);

            $bancoHorasSaldoDia = 0;
            if ($resultadoDia['status'] === 'completo') {
                $bancoHorasSaldoDia = $resultadoDia['total_segundos_trabalhados'] - self::JORNADA_DIARIA_SEGUNDOS;
                $totalSegundosPeriodo += $resultadoDia['total_segundos_trabalhados'];
                $bancoHorasSaldoPeriodo += $bancoHorasSaldoDia;
            }

            $resumoDiario[$data] = [
                'data' => $data,
                'registros' => $registrosDoDia,
                'total_segundos_trabalhados' => $resultadoDia['total_segundos_trabalhados'],
                'status' => $resultadoDia['status'],
                'banco_horas_saldo' => $bancoHorasSaldoDia,
            ];
        }

        return [
            'resumo_diario' => $resumoDiario,
            'total_segundos_periodo' => $totalSegundosPeriodo,
            'banco_horas_saldo_periodo' => $bancoHorasSaldoPeriodo,
        ];
    }

    /**
     * Agrupa os registros de ponto por dia.
     */
    private function agruparPontosPorDia(array $pontos): array {
        $dias = [];
        foreach ($pontos as $ponto) {
            $data = date('Y-m-d', strtotime($ponto['data_hora']));
            $dias[$data][] = $ponto;
        }
        return $dias;
    }

    /**
     * Calcula o total de horas trabalhadas em um único dia usando uma lógica de "pilha".
     */
    private function calcularHorasDia(array $registros): array {
        $totalSegundos = 0;
        $ultimoTimestampEntrada = null;
        $status = 'incompleto'; // Padrão

        // Validações iniciais
        if (empty($registros)) {
            return ['total_segundos_trabalhados' => 0, 'status' => 'vazio'];
        }

        foreach ($registros as $registro) {
            $tipo = $registro['tipo'];
            $timestampAtual = strtotime($registro['data_hora']);

            if ($tipo === 'entrada' || $tipo === 'retorno_almoco') {
                // Se já houver um timestamp de entrada, a sequência está errada.
                if ($ultimoTimestampEntrada !== null) {
                    return ['total_segundos_trabalhados' => 0, 'status' => 'incompleto'];
                }
                $ultimoTimestampEntrada = $timestampAtual;
            }
            elseif ($tipo === 'saida_almoco' || $tipo === 'saida') {
                // Se não houver uma entrada correspondente, a sequência está errada.
                if ($ultimoTimestampEntrada === null) {
                    return ['total_segundos_trabalhados' => 0, 'status' => 'incompleto'];
                }

                // Validação de par lógico
                $ultimoTipo = $this->getTipoFromTimestamp($registros, $ultimoTimestampEntrada);
                $parValido = ($ultimoTipo === 'entrada' && ($tipo === 'saida_almoco' || $tipo === 'saida')) ||
                             ($ultimoTipo === 'retorno_almoco' && $tipo === 'saida');

                if (!$parValido) {
                    return ['total_segundos_trabalhados' => 0, 'status' => 'incompleto'];
                }

                $totalSegundos += ($timestampAtual - $ultimoTimestampEntrada);
                $ultimoTimestampEntrada = null; // Zera para o próximo par
            }
        }

        // Se ao final do dia sobrou um registro de entrada sem par, está incompleto.
        if ($ultimoTimestampEntrada !== null) {
            return ['total_segundos_trabalhados' => $totalSegundos, 'status' => 'incompleto'];
        }

        // Para ser completo, precisa ter pelo menos uma entrada e uma saída no dia.
        $tiposDoDia = array_column($registros, 'tipo');
        if (in_array('entrada', $tiposDoDia) && in_array('saida', $tiposDoDia)) {
            $status = 'completo';
        }

        return [
            'total_segundos_trabalhados' => $totalSegundos,
            'status' => $status,
        ];
    }

    /**
     * Helper para encontrar o tipo de registro a partir de um timestamp.
     */
    private function getTipoFromTimestamp(array $registros, int $timestamp): ?string {
        foreach ($registros as $registro) {
            if (strtotime($registro['data_hora']) === $timestamp) {
                return $registro['tipo'];
            }
        }
        return null;
    }
}
