<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

enum ProductionType: string
{
    case BIOMASS = 'BIOMASS';
    case FOSSIL_BROWN_COAL_LIGNITE = 'FOSSIL_BROWN_COAL_LIGNITE';
    case FOSSIL_COAL_DERIVED_GAS = 'FOSSIL_COAL_DERIVED_GAS';
    case FOSSIL_GAS = 'FOSSIL_GAS';
    case FOSSIL_HARD_COAL = 'FOSSIL_HARD_COAL';
    case FOSSIL_OIL = 'FOSSIL_OIL';
    case FOSSIL_OIL_SHALE = 'FOSSIL_OIL_SHALE';
    case FOSSIL_PEAT = 'FOSSIL_PEAT';
    case GEOTHERMAL = 'GEOTHERMAL';
    case HYDRO_PUMPED_STORAGE = 'HYDRO_PUMPED_STORAGE';
    case HYDRO_RUN_OF_RIVER_AND_POUNDAGE = 'HYDRO_RUN_OF_RIVER_AND_POUNDAGE';
    case HYDRO_WATER_RESERVOIR = 'HYDRO_WATER_RESERVOIR';
    case MARINE = 'MARINE';
    case NUCLEAR = 'NUCLEAR';
    case OTHER_RENEWABLE = 'OTHER_RENEWABLE';
    case SOLAR = 'SOLAR';
    case WASTE = 'WASTE';
    case WIND_OFFSHORE = 'WIND_OFFSHORE';
    case WIND_ONSHORE = 'WIND_ONSHORE';
    case OTHER = 'OTHER';

    public function getIconIdentifier(): string
    {
        return match ($this) {
            self::BIOMASS => 'hugeicons/biomass-energy',
            self::FOSSIL_BROWN_COAL_LIGNITE,
            self::FOSSIL_COAL_DERIVED_GAS,
            self::FOSSIL_GAS,
            self::FOSSIL_HARD_COAL,
            self::FOSSIL_OIL,
            self::FOSSIL_OIL_SHALE,
            self::FOSSIL_PEAT => 'ph/fire-fill',
            self::HYDRO_PUMPED_STORAGE => 'mdi/lake',
            self::HYDRO_RUN_OF_RIVER_AND_POUNDAGE => 'mdi/watermill',
            self::HYDRO_WATER_RESERVOIR => 'maki/dam',
            self::NUCLEAR => 'ph/nuclear-plant-fill',
            self::WIND_ONSHORE, self::WIND_OFFSHORE => 'game-icons/wind-turbine',
            self::SOLAR => 'ph/solar-panel-fill',
            default => 'mdi/electricity',
        };
    }
}
