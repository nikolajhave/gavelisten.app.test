<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validerings-sprogstrenge (Validation Language Lines)
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute skal accepteres.',
    'accepted_if' => ':attribute skal accepteres, når :other er :value.',
    'active_url' => ':attribute skal være en gyldig URL.',
    'after' => ':attribute skal være en dato efter :date.',
    'after_or_equal' => ':attribute skal være en dato efter eller lig med :date.',
    'alpha' => ':attribute må kun indeholde bogstaver.',
    'alpha_dash' => ':attribute må kun indeholde bogstaver, tal, bindestreger og understregninger.',
    'alpha_num' => ':attribute må kun indeholde bogstaver og tal.',
    'array' => ':attribute skal være et array.',
    'ascii' => ':attribute må kun indeholde enkeltbyte alfanumeriske tegn og symboler.',
    'before' => ':attribute skal være en dato før :date.',
    'before_or_equal' => ':attribute skal være en dato før eller lig med :date.',
    'between' => [
        'array' => ':attribute skal have mellem :min og :max elementer.',
        'file' => ':attribute skal være mellem :min og :max kilobytes.',
        'numeric' => ':attribute skal være mellem :min og :max.',
        'string' => ':attribute skal være mellem :min og :max tegn.',
    ],
    'boolean' => ':attribute skal være sandt eller falsk.',
    'can' => ':attribute indeholder en uautoriseret værdi.',
    'confirmed' => ':attribute bekræftelsen matcher ikke.',
    'contains' => ':attribute mangler en påkrævet værdi.',
    'current_password' => 'Adgangskoden er forkert.',
    'date' => ':attribute skal være en gyldig dato.',
    'date_equals' => ':attribute skal være en dato lig med :date.',
    'date_format' => ':attribute matcher ikke formatet :format.',
    'decimal' => ':attribute skal have :decimal decimaler.',
    'declined' => ':attribute skal afvises.',
    'declined_if' => ':attribute skal afvises, når :other er :value.',
    'different' => ':attribute og :other skal være forskellige.',
    'digits' => ':attribute skal have :digits cifre.',
    'digits_between' => ':attribute skal have mellem :min og :max cifre.',
    'dimensions' => ':attribute har ugyldige billeddimensioner.',
    'distinct' => ':attribute har en duplikatværdi.',
    'doesnt_end_with' => ':attribute må ikke slutte med en af følgende: :values.',
    'doesnt_start_with' => ':attribute må ikke starte med en af følgende: :values.',
    'email' => ':attribute skal være en gyldig e-mailadresse.',
    'ends_with' => ':attribute skal slutte med en af følgende: :values.',
    'enum' => 'Den valgte :attribute er ugyldig.',
    'exists' => 'Den valgte :attribute er ugyldig.',
    'extensions' => ':attribute skal have en af følgende udvidelser: :values.',
    'file' => ':attribute skal være en fil.',
    'filled' => ':attribute skal have en værdi.',
    'gt' => [
        'array' => ':attribute skal have mere end :value elementer.',
        'file' => ':attribute skal være større end :value kilobytes.',
        'numeric' => ':attribute skal være større end :value.',
        'string' => ':attribute skal have mere end :value tegn.',
    ],
    'gte' => [
        'array' => ':attribute skal have :value elementer eller mere.',
        'file' => ':attribute skal være større end eller lig med :value kilobytes.',
        'numeric' => ':attribute skal være større end eller lig med :value.',
        'string' => ':attribute skal have :value tegn eller mere.',
    ],
    'hex_color' => ':attribute skal være en gyldig hexadecimal farve.',
    'image' => ':attribute skal være et billede.',
    'in' => 'Den valgte :attribute er ugyldig.',
    'in_array' => ':attribute skal findes i :other.',
    'integer' => ':attribute skal være et heltal.',
    'ip' => ':attribute skal være en gyldig IP-adresse.',
    'ipv4' => ':attribute skal være en gyldig IPv4-adresse.',
    'ipv6' => ':attribute skal være en gyldig IPv6-adresse.',
    'json' => ':attribute skal være en gyldig JSON-streng.',
    'list' => ':attribute skal være en liste.',
    'lowercase' => ':attribute skal være med små bogstaver.',
    'lt' => [
        'array' => ':attribute skal have mindre end :value elementer.',
        'file' => ':attribute skal være mindre end :value kilobytes.',
        'numeric' => ':attribute skal være mindre end :value.',
        'string' => ':attribute skal have mindre end :value tegn.',
    ],
    'lte' => [
        'array' => ':attribute må ikke have mere end :value elementer.',
        'file' => ':attribute skal være mindre end eller lig med :value kilobytes.',
        'numeric' => ':attribute skal være mindre end eller lig med :value.',
        'string' => ':attribute må højst have :value tegn.',
    ],
    'mac_address' => ':attribute skal være en gyldig MAC-adresse.',
    'max' => [
        'array' => ':attribute må ikke have mere end :max elementer.',
        'file' => ':attribute må ikke være større end :max kilobytes.',
        'numeric' => ':attribute må ikke være større end :max.',
        'string' => ':attribute må højst have :max tegn.',
    ],
    'max_digits' => ':attribute må højst have :max cifre.',
    'mimes' => ':attribute skal være en fil af typen: :values.',
    'mimetypes' => ':attribute skal være en fil af typen: :values.',
    'min' => [
        'array' => ':attribute skal mindst have :min elementer.',
        'file' => ':attribute skal mindst være :min kilobytes.',
        'numeric' => ':attribute skal mindst være :min.',
        'string' => ':attribute skal mindst have :min tegn.',
    ],
    'min_digits' => ':attribute skal mindst have :min cifre.',
    'missing' => ':attribute skal mangle.',
    'missing_if' => ':attribute skal mangle, når :other er :value.',
    'missing_unless' => ':attribute skal mangle, medmindre :other er :value.',
    'missing_with' => ':attribute skal mangle, når :values er til stede.',
    'missing_with_all' => ':attribute skal mangle, når alle :values er til stede.',
    'multiple_of' => ':attribute skal være et multiplum af :value.',
    'not_in' => 'Den valgte :attribute er ugyldig.',
    'not_regex' => ':attribute formatet er ugyldigt.',
    'numeric' => ':attribute skal være et tal.',
    'password' => [
        'letters' => ':attribute skal indeholde mindst ét bogstav.',
        'mixed' => ':attribute skal indeholde mindst ét stort og ét lille bogstav.',
        'numbers' => ':attribute skal indeholde mindst ét tal.',
        'symbols' => ':attribute skal indeholde mindst ét symbol.',
        'uncompromised' => 'Den angivne :attribute er dukket op i et datalæk. Vælg venligst en anden :attribute.',
    ],
    'present' => ':attribute skal være til stede.',
    'present_if' => ':attribute skal være til stede, når :other er :value.',
    'present_unless' => ':attribute skal være til stede, medmindre :other er :value.',
    'present_with' => ':attribute skal være til stede, når :values er til stede.',
    'present_with_all' => ':attribute skal være til stede, når :values er til stede.',
    'prohibited' => ':attribute er forbudt.',
    'prohibited_if' => ':attribute er forbudt, når :other er :value.',
    'prohibited_if_accepted' => ':attribute er forbudt, når :other er accepteret.',
    'prohibited_if_declined' => ':attribute er forbudt, når :other er afvist.',
    'prohibited_unless' => ':attribute er forbudt, medmindre :other er i :values.',
    'prohibits' => ':attribute forhindrer :other i at være til stede.',
    'regex' => ':attribute formatet er ugyldigt.',
    'required' => ':attribute feltet er påkrævet.',
    'required_array_keys' => ':attribute skal indeholde poster for: :values.',
    'required_if' => ':attribute feltet er påkrævet, når :other er :value.',
    'required_if_accepted' => ':attribute feltet er påkrævet, når :other er accepteret.',
    'required_if_declined' => ':attribute feltet er påkrævet, når :other er afvist.',
    'required_unless' => ':attribute feltet er påkrævet, medmindre :other er i :values.',
    'required_with' => ':attribute feltet er påkrævet, når :values er til stede.',
    'required_with_all' => ':attribute feltet er påkrævet, når :values er til stede.',
    'required_without' => ':attribute feltet er påkrævet, når :values ikke er til stede.',
    'required_without_all' => ':attribute feltet er påkrævet, når ingen af :values er til stede.',
    'same' => ':attribute og :other skal være ens.',
    'size' => [
        'array' => ':attribute skal indeholde :size elementer.',
        'file' => ':attribute skal være :size kilobytes.',
        'numeric' => ':attribute skal være :size.',
        'string' => ':attribute skal være :size tegn.',
    ],
    'starts_with' => ':attribute skal starte med en af følgende: :values.',
    'string' => ':attribute skal være en streng.',
    'timezone' => ':attribute skal være en gyldig tidszone.',
    'unique' => ':attribute er allerede taget.',
    'uploaded' => ':attribute fejlede under upload.',
    'uppercase' => ':attribute skal være med store bogstaver.',
    'url' => ':attribute skal være en gyldig URL.',
    'ulid' => ':attribute skal være en gyldig ULID.',
    'uuid' => ':attribute skal være en gyldig UUID.',

    /*
    |--------------------------------------------------------------------------
    | Brugerdefinerede validerings-sprogstrenge (Custom Validation Language Lines)
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Brugerdefinerede attributter (Custom Validation Attributes)
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'phone' => 'telefonnummer',
        'code' => 'bekræftelseskode',
        'title' => 'titel',
        'description' => 'beskrivelse',
        'price' => 'pris',
        'url' => 'link',
        'link' => 'link',
    ],

];
