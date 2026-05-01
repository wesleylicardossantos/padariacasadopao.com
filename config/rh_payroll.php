<?php

return [
    'inss' => [
        'teto' => 8475.55,
        'faixas' => [
            ['ate' => 1621.00, 'aliquota' => 0.075],
            ['ate' => 2902.84, 'aliquota' => 0.09],
            ['ate' => 4354.27, 'aliquota' => 0.12],
            ['ate' => 8475.55, 'aliquota' => 0.14],
        ],
        'fonte' => 'Portaria Interministerial MPS/MF nº 13/2026',
    ],
    'irrf' => [
        'desconto_simplificado_mensal' => 607.20,
        'deducao_dependente' => 189.59,
        'faixas' => [
            ['ate' => 2428.80, 'aliquota' => 0.00, 'deducao' => 0.00],
            ['ate' => 2826.65, 'aliquota' => 0.075, 'deducao' => 182.16],
            ['ate' => 3751.05, 'aliquota' => 0.15, 'deducao' => 394.16],
            ['ate' => 4664.68, 'aliquota' => 0.225, 'deducao' => 675.49],
            ['ate' => null, 'aliquota' => 0.275, 'deducao' => 908.73],
        ],
        'reducao_mensal_2026' => [
            'ativa' => true,
            'faixa_isenta_ate' => 5000.00,
            'faixa_reducao_ate' => 7350.00,
            'formula_linear' => [
                'constante' => 978.62,
                'coeficiente' => 0.133145,
            ],
        ],
        'fonte' => 'Receita Federal - Tributação 2026',
    ],
    'financeiro' => [
        'integrar_automaticamente' => true,
    ],
];
