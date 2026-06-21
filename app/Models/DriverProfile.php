<?php
declare(strict_types=1);
namespace App\Models;

use App\Exceptions\ValidationException;

class DriverProfile extends AbstractEntity
{
    private int    $userId        = 0;
    private string $licenseNumber = '';
    private string $vehicleBrand  = '';
    private string $vehicleModel  = '';
    private string $vehicleColor  = '';
    private string $plateNumber   = '';
    private int    $defaultSeats  = 3;
    private ?string $licenseDoc   = null;

    public function validate(): void
    {
        $errors = [];
        if (empty($this->licenseNumber)) $errors['license_number'] = 'Numéro de permis requis';
        if (empty($this->vehicleBrand))  $errors['vehicle_brand']  = 'Marque du véhicule requise';
        if (empty($this->vehicleModel))  $errors['vehicle_model']  = 'Modèle requis';
        if (empty($this->plateNumber))   $errors['plate_number']   = 'Immatriculation requise';
        if ($this->defaultSeats < 1 || $this->defaultSeats > 8) $errors['default_seats'] = 'Places entre 1 et 8';
        if (!empty($errors)) throw new ValidationException($errors);
    }

    public function getUserId(): int         { return $this->userId; }
    public function getLicenseNumber(): string { return $this->licenseNumber; }
    public function getVehicleBrand(): string  { return $this->vehicleBrand; }
    public function getVehicleModel(): string  { return $this->vehicleModel; }
    public function getVehicleColor(): string  { return $this->vehicleColor; }
    public function getPlateNumber(): string   { return $this->plateNumber; }
    public function getDefaultSeats(): int     { return $this->defaultSeats; }
    public function getLicenseDoc(): ?string   { return $this->licenseDoc; }

    public function setUserId(int $v): void          { $this->userId = $v; }
    public function setLicenseNumber(string $v): void { $this->licenseNumber = trim($v); }
    public function setVehicleBrand(string $v): void  { $this->vehicleBrand = trim($v); }
    public function setVehicleModel(string $v): void  { $this->vehicleModel = trim($v); }
    public function setVehicleColor(string $v): void  { $this->vehicleColor = trim($v); }
    public function setPlateNumber(string $v): void   { $this->plateNumber = strtoupper(trim($v)); }
    public function setDefaultSeats(int $v): void     { $this->defaultSeats = max(1, min(8, $v)); }
    public function setLicenseDoc(?string $v): void   { $this->licenseDoc = $v; }
}
