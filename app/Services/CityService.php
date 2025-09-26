<?php

namespace App\Services;

class CityService
{
    public function getCities($data)
    {
        return getCities($data);
    }

    public function createCity($data)
    {
        return addCity($data);
    }

    public function updateCity($id, $name)
    {
        return changeCityName($id, $name);
    }

    public function removeCity($id)
    {
        return deleteCity($id);
    }
}
