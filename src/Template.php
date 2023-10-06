<?php

namespace i350\Fields;


use Modules\Locale\Entities\City;
use Modules\Locale\Entities\State;

class Template
{
    public static function id():IField {
        return new BigIntegerField(
            name: 'id',
            unsigned: true,
            primary_key: true,
            increment: true,
        );
    }
    public static function uuid($primaryKey = true):IField {
        return new UuidField(
            name: 'uuid',
            primary_key: $primaryKey,
        );
    }
    public static function binaryUuid($primaryKey = true, $as='uuid'):IField {
        return new BinaryField(
            name: $as,
            primary_key: $primaryKey,
            default: \DB::raw('(uuid_to_bin(uuid()))')
        );
    }
    public static function displayOrder():IField {
        return new IntegerField(
            name: 'display_order',
            unsigned: true,
            default: 0,
            fillable: true,
        );
    }
    public static function isActive():IField {
        return new BooleanField(
            name: 'is_active',
            default: true,
            fillable: true,
        );
    }
    public static function rememberToken():IField {
        return new StringField(
            name: 'remember_token',
            nullable: true,
            required: false,
            fillable: true,
        );
    }

    /**
     * @return array[IField]
     */
    public static function timestamps($includeDeletedAt=false):array {
        $fields = [
            new TimestampField(
                name: 'created_at',
                nullable: true,
                default: TimestampField::USE_CURRENT,
            ),
            new TimestampField(
                name: 'updated_at',
                nullable: true,
                default: TimestampField::USE_CURRENT_ON_UPDATE,
            ),
        ];
        if ($includeDeletedAt) {
            $fields[] = new TimestampField(
                name: 'deleted_at',
                nullable: true,
                default: null,
            );
        }

        return $fields;
    }


    public static function locationFields(
        bool $country=true, bool $state=true, bool $city=true,
        ?String $countryOnDelete=null, ?String $stateOnDelete=null, ?string $cityOnDelete=null
    ):array {
        $fields = [];
        if ($country) {
            $fields[] = new StringField(
                name: 'country_code',
                max_length: 2,
                nullable: $countryOnDelete=='set null',
                fillable: true,
                foreign_key: (array)ForeignKey::define('code', 'countries', $countryOnDelete)
            );
        }
        if ($state) {
            $fields[] = new ForeignKeyField(
                name: 'state_id',
                model: State::class,
                on_delete: $stateOnDelete
            );
        }
        if ($city) {
            $fields[] = new ForeignKeyField(
                name: 'city_id',
                model: City::class,
                on_delete: $cityOnDelete
            );
        }
        return $fields;
    }
}
