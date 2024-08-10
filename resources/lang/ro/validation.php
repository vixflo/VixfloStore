<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linii de limbaj pentru validare
    |--------------------------------------------------------------------------
    |
    | Liniile de limbaj următoare conțin mesajele de eroare implicite utilizate
    | de clasa validatorului. Unele dintre aceste reguli au mai multe versiuni,
    | cum ar fi regulile de dimensiune. Simțiți-vă liber să ajustați fiecare
    | dintre aceste mesaje aici.
    |
    */

    'accepted'             => ':attribute trebuie să fie acceptat.',
    'active_url'           => ':attribute nu este un URL valid.',
    'after'                => ':attribute trebuie să fie o dată după :date.',
    'after_or_equal'       => ':attribute trebuie să fie o dată după sau egală cu :date.',
    'alpha'                => ':attribute poate conține doar litere.',
    'alpha_dash'           => ':attribute poate conține doar litere, numere, cratime și underscore.',
    'alpha_num'            => ':attribute poate conține doar litere și numere.',
    'array'                => ':attribute trebuie să fie un array.',
    'before'               => ':attribute trebuie să fie o dată înainte de :date.',
    'before_or_equal'      => ':attribute trebuie să fie o dată înainte sau egală cu :date.',
    'between'              => [
        'numeric' => ':attribute trebuie să fie între :min și :max.',
        'file'    => ':attribute trebuie să fie între :min și :max kilobytes.',
        'string'  => ':attribute trebuie să fie între :min și :max caractere.',
        'array'   => ':attribute trebuie să aibă între :min și :max elemente.',
    ],
    'boolean'              => 'Câmpul :attribute trebuie să fie adevărat sau fals.',
    'confirmed'            => 'Confirmarea :attribute nu se potrivește.',
    'date'                 => ':attribute nu este o dată validă.',
    'date_format'          => ':attribute nu se potrivește cu formatul :format.',
    'different'            => ':attribute și :other trebuie să fie diferite.',
    'digits'               => ':attribute trebuie să aibă :digits cifre.',
    'digits_between'       => ':attribute trebuie să fie între :min și :max cifre.',
    'dimensions'           => ':attribute are dimensiuni de imagine nevalide.',
    'distinct'             => 'Câmpul :attribute are o valoare duplicată.',
    'email'                => ':attribute trebuie să fie o adresă de email validă.',
    'exists'               => ':attribute selectat este invalid.',
    'file'                 => ':attribute trebuie să fie un fișier.',
    'filled'               => 'Câmpul :attribute trebuie să aibă o valoare.',
    'gt'                   => [
        'numeric' => ':attribute trebuie să fie mai mare decât :value.',
        'file'    => ':attribute trebuie să fie mai mare decât :value kilobytes.',
        'string'  => ':attribute trebuie să fie mai mare decât :value caractere.',
        'array'   => ':attribute trebuie să aibă mai mult de :value elemente.',
    ],
    'gte'                  => [
        'numeric' => ':attribute trebuie să fie mai mare sau egal cu :value.',
        'file'    => ':attribute trebuie să fie mai mare sau egal cu :value kilobytes.',
        'string'  => ':attribute trebuie să fie mai mare sau egal cu :value caractere.',
        'array'   => ':attribute trebuie să aibă :value elemente sau mai multe.',
    ],
    'image'                => ':attribute trebuie să fie o imagine.',
    'in'                   => ':attribute selectat este invalid.',
    'in_array'             => 'Câmpul :attribute nu există în :other.',
    'integer'              => ':attribute trebuie să fie un întreg.',
    'ip'                   => ':attribute trebuie să fie o adresă IP validă.',
    'ipv4'                 => ':attribute trebuie să fie o adresă IPv4 validă.',
    'ipv6'                 => ':attribute trebuie să fie o adresă IPv6 validă.',
    'json'                 => ':attribute trebuie să fie un șir JSON valid.',
    'lt'                   => [
        'numeric' => ':attribute trebuie să fie mai mic decât :value.',
        'file'    => ':attribute trebuie să fie mai mic decât :value kilobytes.',
        'string'  => ':attribute trebuie să fie mai mic decât :value caractere.',
        'array'   => ':attribute trebuie să aibă mai puțin de :value elemente.',
    ],
    'lte'                  => [
        'numeric' => ':attribute trebuie să fie mai mic sau egal cu :value.',
        'file'    => ':attribute trebuie să fie mai mic sau egal cu :value kilobytes.',
        'string'  => ':attribute trebuie să fie mai mic sau egal cu :value caractere.',
        'array'   => ':attribute nu trebuie să aibă mai mult de :value elemente.',
    ],
    'max'                  => [
        'numeric' => ':attribute nu poate fi mai mare de :max.',
        'file'    => ':attribute nu poate fi mai mare de :max kilobytes.',
        'string'  => ':attribute nu poate fi mai mare de :max caractere.',
        'array'   => ':attribute nu poate avea mai mult de :max elemente.',
    ],
    'mimes'                => ':attribute trebuie să fie un fișier de tipul: :values.',
    'mimetypes'            => ':attribute trebuie să fie un fișier de tipul: :values.',
    'min'                  => [
        'numeric' => ':attribute trebuie să fie cel puțin :min.',
        'file'    => ':attribute trebuie să fie cel puțin :min kilobytes.',
        'string'  => ':attribute trebuie să aibă cel puțin :min caractere.',
        'array'   => ':attribute trebuie să aibă cel puțin :min elemente.',
    ],
    'not_in'               => ':attribute selectat este invalid.',
    'not_regex'            => 'Formatul :attribute este invalid.',
    'numeric'              => ':attribute trebuie să fie un număr.',
    'present'              => 'Câmpul :attribute trebuie să fie prezent.',
    'regex'                => 'Formatul :attribute este invalid.',
    'required'             => 'Câmpul :attribute este obligatoriu.',
    'required_if'          => 'Câmpul :attribute este obligatoriu când :other este :value.',
    'required_unless'      => 'Câmpul :attribute este obligatoriu decât dacă :other este în :values.',
    'required_with'        => 'Câmpul :attribute este obligatoriu când :values este prezent.',
    'required_with_all'    => 'Câmpul :attribute este obligatoriu când :values este prezent.',
    'required_without'     => 'Câmpul :attribute este obligatoriu când :values nu este prezent.',
    'required_without_all' => 'Câmpul :attribute este obligatoriu când niciunul dintre :values nu este prezent.',
    'same'                 => ':attribute și :other trebuie să se potrivească.',
    'size'                 => [
        'numeric' => ':attribute trebuie să fie :size.',
        'file'    => ':attribute trebuie să fie :size kilobytes.',
        'string'  => ':attribute trebuie să aibă :size caractere.',
        'array'   => ':attribute trebuie să conțină :size elemente.',
    ],
    'string'               => ':attribute trebuie să fie un șir.',
    'timezone'             => ':attribute trebuie să fie o zonă validă.',
    'unique'               => ':attribute a fost deja luat.',
    'uploaded'             => ':attribute nu a reușit să se încarce.',
    'url'                  => 'Formatul :attribute este invalid.',

    /*
    |--------------------------------------------------------------------------
    | Linii personalizate de validare
    |--------------------------------------------------------------------------
    |
    | Aici puteți specifica mesaje de validare personalizate pentru atribute folosind
    | convenția "attribute.rule" pentru a numi liniile. Acest lucru face ca
    | specificarea unei linii de limbaj personalizate pentru o regulă de atribut să fie rapidă.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mesaj-personalizat',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atribute de validare personalizate
    |--------------------------------------------------------------------------
    |
    | Liniile de limbaj următoare sunt utilizate pentru a schimba substituenții
    | atributelor cu ceva mai prietenos pentru cititor, cum ar fi Adresa de E-Mail în loc de "email".
    | Acest lucru ne ajută pur și simplu să facem mesajele un pic mai clare.
    |
    */

    'attributes' => [],

];
