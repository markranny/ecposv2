<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class CsvImportValidator
{
    protected $errors = [];
    protected $warnings = [];

    public function validateCsvData($data, $rowNumber)
    {
        $errors = [];

        // Required fields validation
        if (empty($data[0])) {
            $errors[] = "Row {$rowNumber}: Item ID is required";
        }

        if (empty($data[1])) {
            $errors[] = "Row {$rowNumber}: Description is required";
        }

        // Numeric validation
        if (!empty($data[2]) && !is_numeric($data[2])) {
            $errors[] = "Row {$rowNumber}: Cost price must be a number";
        }

        if (!empty($data[3]) && !is_numeric($data[3])) {
            $errors[] = "Row {$rowNumber}: Sales price must be a number";
        }

        // Barcode validation (should be 13 digits if provided)
        if (!empty($data[8]) && (!is_numeric($data[8]) || strlen($data[8]) !== 13)) {
            $errors[] = "Row {$rowNumber}: Barcode must be 13 digits";
        }

        // Price validation (cost should not be higher than sales price)
        if (!empty($data[2]) && !empty($data[3])) {
            $costPrice = (float) $data[2];
            $salesPrice = (float) $data[3];
            
            if ($costPrice > $salesPrice) {
                $this->warnings[] = "Row {$rowNumber}: Cost price is higher than sales price";
            }
        }

        return $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function addWarning($warning)
    {
        $this->warnings[] = $warning;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function hasWarnings()
    {
        return !empty($this->warnings);
    }

    public function reset()
    {
        $this->errors = [];
        $this->warnings = [];
    }
}