<?php
declare(strict_types=1);
namespace App\Helpers;

use App\Exceptions\ValidationException;

final class Validator
{
    private array $errors = [];

    public function required(array $data, array $fields): self
    {
        foreach ($fields as $f) {
            if (empty(trim((string)($data[$f] ?? '')))) {
                $this->errors[$f] = "Le champ {$f} est requis";
            }
        }
        return $this;
    }

    public function email(array $data, string $field): self
    {
        if (!empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Adresse email invalide';
        }
        return $this;
    }

    public function minLength(array $data, string $field, int $min): self
    {
        if (!empty($data[$field]) && strlen((string)$data[$field]) < $min) {
            $this->errors[$field] = "Minimum {$min} caractères";
        }
        return $this;
    }

    public function maxLength(array $data, string $field, int $max): self
    {
        if (!empty($data[$field]) && strlen((string)$data[$field]) > $max) {
            $this->errors[$field] = "Maximum {$max} caractères";
        }
        return $this;
    }

    public function numeric(array $data, string $field, float $min = 0): self
    {
        if (isset($data[$field]) && $data[$field] !== '') {
            if (!is_numeric($data[$field])) {
                $this->errors[$field] = 'Doit être un nombre';
            } elseif ((float)$data[$field] < $min) {
                $this->errors[$field] = "Doit être supérieur ou égal à {$min}";
            }
        }
        return $this;
    }

    public function dateInFuture(array $data, string $field): self
    {
        if (!empty($data[$field])) {
            try {
                $d = new \DateTimeImmutable($data[$field]);
                if ($d <= new \DateTimeImmutable()) {
                    $this->errors[$field] = 'La date doit être dans le futur';
                }
            } catch (\Throwable) {
                $this->errors[$field] = 'Date invalide';
            }
        }
        return $this;
    }

    public function between(array $data, string $field, int $min, int $max): self
    {
        if (isset($data[$field])) {
            $v = (int)$data[$field];
            if ($v < $min || $v > $max) {
                $this->errors[$field] = "Doit être entre {$min} et {$max}";
            }
        }
        return $this;
    }

    public function passwordStrength(array $data, string $field): self
    {
        $p = $data[$field] ?? '';
        if (strlen($p) < 8) {
            $this->errors[$field] = 'Mot de passe : minimum 8 caractères';
        } elseif (!preg_match('/[A-Z]/', $p) || !preg_match('/[0-9]/', $p)) {
            $this->errors[$field] = 'Mot de passe : au moins une majuscule et un chiffre';
        }
        return $this;
    }

    public function confirmed(array $data, string $field, string $confirmField): self
    {
        if (($data[$field] ?? '') !== ($data[$confirmField] ?? '')) {
            $this->errors[$confirmField] = 'Les mots de passe ne correspondent pas';
        }
        return $this;
    }

    public function phone(array $data, string $field): self
    {
        if (!empty($data[$field])) {
            $phone = preg_replace('/\s/', '', (string)$data[$field]);
            if (!preg_match('/^(77|78|70|76|33)\d{7}$/', $phone)) {
                $this->errors[$field] = 'Numéro de téléphone invalide';
            }
        }
        return $this;
    }

    public function notSame(array $data, string $f1, string $f2, string $msg): self
    {
        if (!empty($data[$f1]) && !empty($data[$f2]) && $data[$f1] === $data[$f2]) {
            $this->errors[$f2] = $msg;
        }
        return $this;
    }

    public function hasErrors(): bool { return !empty($this->errors); }
    public function getErrors(): array { return $this->errors; }

    public function throwIfErrors(): void
    {
        if ($this->hasErrors()) throw new ValidationException($this->errors);
    }

    public static function make(): self { return new self(); }
}
