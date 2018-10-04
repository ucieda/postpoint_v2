<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute kabul edilmeli.',
    'active_url'           => ':attribute geçerli bir URL değil.',
    'after'                => ':attribute sonra bir tarih olmalı :date.',
    'after_or_equal'       => ':attribute tarihten sonra veya :date eşit bir tarih olmalıdır. ',
    'alpha'                => ':attribute sadece harf içerebilir.',
    'alpha_dash'           => ':attribute sadece harf, sayı ve tire içerebilir.',
    'alpha_num'            => ':attribute yalnızca harf ve rakam içerebilir.',
    'array'                => ':attribute bir dizi olmalı.',
    'before'               => ':attribute Önce tarih olmalı :date.',
    'before_or_equal'      => ':attribute Şu :date. tarihden önceki veya ona eşit tarih olmalıdır',
    'between'              => [
        'numeric' => ':attribute :min ve :max tarih aralığında olmalıdır.',
        'file'    => ':attribute :min ve :max kilobayt aralığında olmalıdır.',
        'string'  => ':attribute :min ve :max karakter aralığında olmalıdır.',
        'array'   => ':attribute dizi :min and :max aralığında olmalıdır.',
    ],
    'boolean'              => ':attribute doğru ve ya yanlış olmalıdır.',
    'confirmed'            => ':attribute onay uyuşmuyor.',
    'date'                 => ':attribute geçerli bir tarih değil.',
    'date_format'          => ':attribute :format formatla eşleşmiyor.',
    'different'            => ':attribute ve :other farklı olmalıdır.',
    'digits'               => ':attribute :digits rakamlar olmalıdır.',
    'digits_between'       => ':attribute :min and :max rakamlar olmalıdır.',
    'dimensions'           => ':attribute geçersiz resim boyutları var.',
    'distinct'             => ':attribute alanın benzer değeri var.',
    'email'                => ':attribute geçerli bir e-posta adresi olmalı.',
    'exists'               => 'seçilen :attribute geçersiz.',
    'file'                 => ':attribute bir dosya olmalı.',
    'filled'               => ':attribute alanın bir değeri olmalı.',
    'image'                => ':attribute bir görüntü olmalı.',
    'in'                   => 'seçilen :attribute geçersiz.',
    'in_array'             => ':attribute alanı :other da mevcut değil.',
    'integer'              => ':attribute bir tamsayı olmalıdır.',
    'ip'                   => ':attribute geçerli bir IP adresi olmalı.',
    'ipv4'                 => ':attribute geçerli bir IPv4 adresi olmalı.',
    'ipv6'                 => ':attribute geçerli bir IPv6 adresi olmalı.',
    'json'                 => ':attribute geçerli bir JSON dizgisi olmalı.',
    'max'                  => [
        'numeric' => ':attribute en fazla :max olabilir .',
        'file'    => ':attribute :max kilobaytdan fazla olamaz.',
        'string'  => ':attribute karakterler :max fazla olamaz.',
        'array'   => ':attribute diziler :max fazla olamaz.',
    ],
    'mimes'                => ':attribute bir dosya türü olmalıdır: :values.',
    'mimetypes'            => ':attribute dosyanın bu türü olmalıdır: :values.',
    'min'                  => [
        'numeric' => ':attribute en az :min olmalıdır.',
        'file'    => ':attribute en az :min kilobayt olmalıdır.',
        'string'  => ':attribute en az :min karakter olmalıdır.',
        'array'   => ':attribute öğeler en az :min olmalıdır.',
    ],
    'not_in'               => 'seçilen :attribute geçersiz.',
    'numeric'              => ':attribute bir sayı olmalıdır.',
    'present'              => ':attribute alan mevcut olmalıdır.',
    'regex'                => ':attribute geçersiz biçim.',
    'required'             => ':attribute alanı gerekli.',
    'required_if'          => ':attribute alanı eğer varsa :other :value.',
    'required_unless'      => ':attribute aksi belirtilmediği sürece alan gereklidir :other :values - da.',
    'required_with'        => ':attribute alanı gereklidir :values eğer mevcutsa.',
    'required_with_all'    => ':attribute gerekli olduğunda :values mecvut.',
    'required_without'     => ':attribute alanı gereklidir :values mevcut değilse.',
    'required_without_all' => ':attribute :values hiç biri yoksa alan gereklidir.',
    'same'                 => ':attribute ve :other eşleşmelidir.',
    'size'                 => [
        'numeric' => ':attribute olmalıdır :size.',
        'file'    => ':attribute :size kilobayt olmalıdır.',
        'string'  => ':attribute :size karakter olmalıdır.',
        'array'   => ':attribute :size öğeden ibaret olmalıdır.',
    ],
    'string'               => ':attribute string türü olmalıdır.',
    'timezone'             => ':attribute geçerli bir bölge olmalıdır.',
    'unique'               => ':attribute zaten kabul edildi.',
    'uploaded'             => ':attribute yüklenemedi.',
    'url'                  => ':attribute geçersiz bir biçim.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
