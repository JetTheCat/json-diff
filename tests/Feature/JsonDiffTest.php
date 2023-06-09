<?php

declare(strict_types=1);

namespace Jet\Tests\Feature;

use Illuminate\Support\Arr;
use Jet\JsonDiff\JsonDiff;
use Jet\JsonDiff\KeyAdded;
use Jet\JsonDiff\KeyRemoved;
use Jet\JsonDiff\ValueAdded;
use Jet\JsonDiff\ValueChange;
use Jet\JsonDiff\ValueRemoved;
use Jet\Tests\TestCase;

class JsonDiffTest extends TestCase
{
    public function test_basic_value_change_between_jsons(): void
    {
        $original = [
            'name' => 'Charles',
            'age' => 23,
            'birth_date' => '16/07/1980',
            'sports' => 'basketball',
        ];

        $new = [
            'name' => 'James',
            'age' => 50,
            'birth_date' => '16/06/1960',
            'sports' => 'soccer',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $jsonDiff->getValuesChanged()->each(function (ValueChange $valueChange) use ($new): void {
            $this->assertSame($new[$valueChange->getPath()], $valueChange->getNewValue());
        });

        $this->assertSame(4, $jsonDiff->getValuesChanged()->count());

        $this->assertEmpty($jsonDiff->getKeysAdded());
        $this->assertEmpty($jsonDiff->getKeysRemoved());
        $this->assertEmpty($jsonDiff->getValuesAdded());
        $this->assertEmpty($jsonDiff->getValuesRemoved());
    }

    public function test_new_elements_added_between_jsons(): void
    {
        $original = [
            'name' => 'Charles',
            'age' => 23,
            'birth_date' => '16/07/1980',
            'sports' => 'basketball',
        ];

        $new = [
            'name' => 'Charles',
            'age' => 23,
            'birth_date' => '16/07/1980',
            'sports' => 'basketball',
            'gender' => 'male',
            'nationality' => 'Singapore',
        ];

        $addedItems = [
            'gender',
            'nationality',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $this->assertSame(2, $jsonDiff->getValuesAdded()->count());
        $this->assertSame(2, $jsonDiff->getKeysAdded()->count());

        $keysAdded = $jsonDiff->getKeysAdded();
        $valuesAdded = $jsonDiff->getValuesAdded();

        // Check that the new keys are added
        $keysAdded->each(function (KeyAdded $keyAdded) use ($addedItems): void {
            $this->assertContains($keyAdded->getPath(), $addedItems);
        });

        // Check that new values are added
        $valuesAdded->each(function (ValueAdded $valueAdded) use ($new): void {
            $this->assertSame(Arr::get($new, $valueAdded->getPath()), $valueAdded->getValue());
        });

        $this->assertEmpty($jsonDiff->getKeysRemoved());
        $this->assertEmpty($jsonDiff->getValuesRemoved());
        $this->assertEmpty($jsonDiff->getValuesChanged());
    }

    public function test_new_array_elements_added_between_jsons(): void
    {
        $original = [
            [
                'name' => 'Charles',
                'age' => 23,
                'birth_date' => '16/07/1985',
                'nationality' => 'Singapore',
            ],
        ];

        $new = [
            [
                'name' => 'James',
                'age' => 31,
                'birth_date' => '16/06/1972',
                'nationality' => 'Korea',
            ],
            [
                'name' => 'Charles',
                'age' => 23,
                'birth_date' => '16/07/1985',
                'nationality' => 'Singapore',
            ],
            [
                'name' => 'Joanne',
                'age' => 25,
                'birth_date' => '16/06/1982',
                'nationality' => 'Sweden',
            ],
        ];

        $addedItems = [
            '0',
            '2',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $this->assertSame(2, $jsonDiff->getValuesAdded()->count());
        $this->assertSame(2, $jsonDiff->getKeysAdded()->count());

        $keysAdded = $jsonDiff->getKeysAdded();
        $valuesAdded = $jsonDiff->getValuesAdded();

        // Check that the new keys are added
        $keysAdded->each(function (KeyAdded $keyAdded) use ($addedItems): void {
            $this->assertContains($keyAdded->getPath(), $addedItems);
        });

        // Check that new values are added
        $valuesAdded->each(function (ValueAdded $valueAdded) use ($new): void {
            $this->assertSame(Arr::get($new, $valueAdded->getPath()), $valueAdded->getValue());
        });

        $this->assertEmpty($jsonDiff->getKeysRemoved());
        $this->assertEmpty($jsonDiff->getValuesChanged());
        $this->assertEmpty($jsonDiff->getValuesRemoved());
    }

    public function test_elements_removed_between_jsons(): void
    {
        $original = [
            'flight_reference' => 'MH10783',
            'booking_reference' => '123-MHS-INS',
            'airline' => 'Test Airline',
            'flight_date' => '12/12/2024',
            'destination' => 'Japan',
            'flight_time' => '7h30m',
        ];

        $new = [
            'flight_reference' => 'MH10783',
            'booking_reference' => '123-MHS-INS',
            'airline' => 'Test Airline',
            'destination' => 'Japan',
        ];

        $removedItems = [
            'flight_date',
            'flight_time',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $keysRemoved = $jsonDiff->getKeysRemoved();
        $valuesRemoved = $jsonDiff->getValuesRemoved();

        // Check that the correct keys are removed
        $keysRemoved->each(function (KeyRemoved $keyRemoved) use ($removedItems): void {
            $this->assertContains($keyRemoved->getPath(), $removedItems);
        });

        // Check that the correct values are removed
        $valuesRemoved->each(function (ValueRemoved $valueRemoved) use ($original): void {
            $this->assertSame(Arr::get($original, $valueRemoved->getPath()), $valueRemoved->getValue());
        });

        $this->assertEmpty($jsonDiff->getKeysAdded());
        $this->assertEmpty($jsonDiff->getValuesAdded());
        $this->assertEmpty($jsonDiff->getValuesChanged());
    }

    public function test_array_elements_removed_between_jsons(): void
    {
        $original = [
            [
                'flight_reference' => 'AP10622',
                'booking_reference' => '345-AST-INS',
                'airline' => 'Alpaca Airline',
                'flight_date' => '12/12/2024',
                'destination' => 'Fiji',
                'flight_time' => '5h30m',
            ],
            [
                'flight_reference' => 'MH10783',
                'booking_reference' => '123-MHS-INS',
                'airline' => 'Koala Airline',
                'flight_date' => '12/12/2024',
                'destination' => 'Japan',
                'flight_time' => '7h30m',
            ],
            [
                'flight_reference' => 'JT12222',
                'booking_reference' => '222-JTS-INS',
                'airline' => 'Jet Airline',
                'flight_date' => '12/12/2024',
                'destination' => 'France',
                'flight_time' => '10h45m',
            ],
        ];

        $new = [
            [
                'flight_reference' => 'MH10783',
                'booking_reference' => '123-MHS-INS',
                'airline' => 'Koala Airline',
                'flight_date' => '12/12/2024',
                'destination' => 'Japan',
                'flight_time' => '7h30m',
            ],
        ];

        $removedItems = [
            '0',
            '2',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $keysRemoved = $jsonDiff->getKeysRemoved();
        $valuesRemoved = $jsonDiff->getValuesRemoved();

        // Check that the correct number keys are removed
        $this->assertSame(2, $jsonDiff->getKeysRemoved()->count());

        // Check that the correct keys are removed
        $keysRemoved->each(function (KeyRemoved $keyRemoved) use ($removedItems): void {
            $this->assertContains($keyRemoved->getPath(), $removedItems);
        });

        // Check that the correct values are removed
        $valuesRemoved->each(function (ValueRemoved $valueRemoved) use ($original): void {
            $this->assertSame(Arr::get($original, $valueRemoved->getPath()), $valueRemoved->getValue());
        });

        $this->assertEmpty($jsonDiff->getKeysAdded());
        $this->assertEmpty($jsonDiff->getValuesAdded());
        $this->assertEmpty($jsonDiff->getValuesChanged());
    }

    public function test_modify_nested_element_between_jsons(): void
    {
        $original = [
            'id' => '3fe21e46fd78',
            'company' => 'Alpha Airline',
            'points' => 20000,
            'duration' => 862,
            'segment' => [
                0 => [
                    'duration' => 635,
                    'departureTime' => '2023-05-04 00:53:35',
                    'arrivalTime' => '2023-05-04 11:28:53',
                    'origin' => 'Sydney',
                    'destination' => 'Taiwan',
                    'connectionDuration' => 125,
                ],
                1 => [
                    'duration' => 180,
                    'departureTime' => '2023-05-04 13:33:53',
                    'arrivalTime' => '2023-05-04 16:33:53',
                    'origin' => 'Taiwan',
                    'destination' => 'Korea',
                ],
            ],
        ];

        $new = [
            'id' => '3fe21e46fd78',
            'company' => 'Beta Airline',
            'points' => 50000,
            'duration' => 862,
            'segment' => [
                0 => [
                    'duration' => 635,
                    'departureTime' => '2023-05-04 00:53:35',
                    'arrivalTime' => '2023-05-04 11:28:53',
                    'origin' => 'Sydney',
                    'destination' => 'Japan',
                    'connectionDuration' => 125,
                ],
                1 => [
                    'duration' => 180,
                    'departureTime' => '2023-05-04 13:33:53',
                    'arrivalTime' => '2023-05-04 16:33:53',
                    'origin' => 'Japan',
                    'destination' => 'Korea',
                ],
            ],
        ];

        $modifiedItems = [
            'company',
            'points',
            'segment.0.destination',
            'segment.1.origin',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $valuesChanged = $jsonDiff->getValuesChanged();

        $valuesChanged->each(function (ValueChange $valueChange) use ($original, $new, $modifiedItems): void {
            $this->assertContains($valueChange->getPath(), $modifiedItems);
            $this->assertSame(Arr::get($original, $valueChange->getPath()), $valueChange->getOldValue());
            $this->assertSame(Arr::get($new, $valueChange->getPath()), $valueChange->getNewValue());
        });

        $this->assertEmpty($jsonDiff->getKeysAdded());
        $this->assertEmpty($jsonDiff->getKeysRemoved());
        $this->assertEmpty($jsonDiff->getValuesAdded());
        $this->assertEmpty($jsonDiff->getValuesRemoved());
    }

    public function test_add_new_nested_element_between_jsons(): void
    {
        $original = [
            'id' => '3fe21e46fd78',
            'company' => 'Alpha Airline',
            'points' => 20000,
            'duration' => 862,
            'segment' => [
                0 => [
                    'duration' => 635,
                    'departureTime' => '2023-05-04 00:53:35',
                    'arrivalTime' => '2023-05-04 11:28:53',
                    'origin' => 'Sydney',
                    'destination' => 'Taiwan',
                    'connectionDuration' => 125,
                ],
                1 => [
                    'duration' => 180,
                    'departureTime' => '2023-05-04 13:33:53',
                    'arrivalTime' => '2023-05-04 16:33:53',
                    'origin' => 'Taiwan',
                    'destination' => 'Korea',
                ],
            ],
        ];

        $new = [
            'id' => '3fe21e46fd78',
            'company' => 'Alpha Airline',
            'points' => 20000,
            'duration' => 862,
            'segment' => [
                0 => [
                    'booking_reference' => 'AH15243',
                    'boarding_information' => [
                        'terminal' => 2,
                        'gate' => '15',
                    ],
                    'duration' => 635,
                    'departureTime' => '2023-05-04 00:53:35',
                    'arrivalTime' => '2023-05-04 11:28:53',
                    'origin' => 'Sydney',
                    'destination' => 'Taiwan',
                    'connectionDuration' => 125,
                ],
                1 => [
                    'booking_reference' => 'AH35728',
                    'boarding_information' => [
                        'terminal' => 1,
                        'gate' => '1',
                    ],
                    'duration' => 180,
                    'departureTime' => '2023-05-04 13:33:53',
                    'arrivalTime' => '2023-05-04 16:33:53',
                    'origin' => 'Taiwan',
                    'destination' => 'Korea',
                ],
            ],
        ];

        $addedItems = [
            'segment.0.booking_reference',
            'segment.0.boarding_information',
            'segment.1.booking_reference',
            'segment.1.boarding_information',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $valuesAdded = $jsonDiff->getValuesAdded();
        $keysAdded = $jsonDiff->getKeysAdded();

        $keysAdded->each(function (KeyAdded $keyAdded) use ($addedItems): void {
            $this->assertContains($keyAdded->getPath(), $addedItems);
        });

        $valuesAdded->each(function (ValueAdded $valueAdded) use ($new): void {
            $this->assertSame(Arr::get($new, $valueAdded->getPath()), $valueAdded->getValue());
        });

        $this->assertEmpty($jsonDiff->getKeysRemoved());
        $this->assertEmpty($jsonDiff->getValuesChanged());
        $this->assertEmpty($jsonDiff->getValuesRemoved());
    }

    public function test_remove_nested_element_between_jsons(): void
    {
        $original = [
            [
                'id' => '3fe21e46fd78',
                'company' => 'Alpha Airline',
                'points' => 20000,
                'duration' => 862,
                'segment' => [
                    0 => [
                        'booking_reference' => 'AH15243',
                        'boarding_information' => [
                            'terminal' => 2,
                            'gate' => '15',
                        ],
                        'duration' => 635,
                        'departureTime' => '2023-05-04 00:53:35',
                        'arrivalTime' => '2023-05-04 11:28:53',
                        'origin' => 'Sydney',
                        'destination' => 'Taiwan',
                        'connectionDuration' => 125,
                    ],
                    1 => [
                        'booking_reference' => 'AH35728',
                        'boarding_information' => [
                            'terminal' => 1,
                            'gate' => '1',
                        ],
                        'duration' => 180,
                        'departureTime' => '2023-05-04 13:33:53',
                        'arrivalTime' => '2023-05-04 16:33:53',
                        'origin' => 'Taiwan',
                        'destination' => 'Korea',
                    ],
                ],
            ],
            [
                'id' => '4fe21e477f78',
                'company' => 'Beta Airline',
                'points' => 10000,
                'duration' => 300,
                'segment' => [
                    0 => [
                        'booking_reference' => 'BH61121',
                        'boarding_information' => [
                            'terminal' => 3,
                            'gate' => '7',
                        ],
                        'duration' => 300,
                        'departureTime' => '2023-05-04 00:53:35',
                        'arrivalTime' => '2023-05-04 05:53:35',
                        'origin' => 'Singapore',
                        'destination' => 'Thailand',
                    ],
                ],
            ],
        ];

        $new = [
            [
                'id' => '4fe21e477f78',
                'company' => 'Beta Airline',
                'points' => 10000,
                'duration' => 300,
                'segment' => [
                    0 => [
                        'booking_reference' => 'BH61121',
                        'duration' => 300,
                        'departureTime' => '2023-05-04 00:53:35',
                        'arrivalTime' => '2023-05-04 05:53:35',
                        'origin' => 'Singapore',
                        'destination' => 'Thailand',
                    ],
                ],
            ],
        ];

        $removedItems = [
            '0',
            '1.segment.0.boarding_information',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $keysRemoved = $jsonDiff->getKeysRemoved();
        $valuesRemoved = $jsonDiff->getValuesRemoved();

        // Check that the correct keys are removed
        $keysRemoved->each(function (KeyRemoved $keyRemoved) use ($removedItems): void {
            $this->assertContains($keyRemoved->getPath(), $removedItems);
        });

        // Check that correct values are removed
        $valuesRemoved->each(function (ValueRemoved $valueRemoved) use ($original): void {
            $this->assertSame(Arr::get($original, $valueRemoved->getPath()), $valueRemoved->getValue());
        });

        $this->assertEmpty($jsonDiff->getKeysAdded());
        $this->assertEmpty($jsonDiff->getValuesChanged());
        $this->assertEmpty($jsonDiff->getValuesAdded());
    }

    public function test_complex_operations_between_two_jsons(): void
    {
        $original = [
            [
                'id' => '3fe21e46fd78',
                'company' => 'Alpha Airline',
                'points' => 20000,
                'duration' => 862,
                'segment' => [
                    0 => [
                        'booking_reference' => 'AH15243',
                        'boarding_information' => [
                            'terminal' => 2,
                            'gate' => '15',
                        ],
                        'duration' => 635,
                        'departureTime' => '2023-05-04 00:53:35',
                        'arrivalTime' => '2023-05-04 11:28:53',
                        'origin' => 'Sydney',
                        'destination' => 'Taiwan',
                    ],
                    1 => [
                        'booking_reference' => 'AH35728',
                        'boarding_information' => [
                            'terminal' => 1,
                            'gate' => '1',
                        ],
                        'duration' => 180,
                        'departureTime' => '2023-05-04 13:33:53',
                        'arrivalTime' => '2023-05-04 16:33:53',
                        'origin' => 'Taiwan',
                        'destination' => 'Korea',
                    ],
                ],
            ],
            [
                'id' => '4fe21e477f78',
                'company' => 'Beta Airline',
                'points' => 10000,
                'duration' => 300,
                'segment' => [
                    0 => [
                        'booking_reference' => 'BH61121',
                        'boarding_information' => [
                            'terminal' => 3,
                            'gate' => '7',
                        ],
                        'duration' => 300,
                        'departureTime' => '2023-05-04 00:53:35',
                        'arrivalTime' => '2023-05-04 05:53:35',
                        'origin' => 'Singapore',
                        'destination' => 'Thailand',
                    ],
                ],
            ],
        ];

        $new = [
            [
                'id' => '66t21d46fd78',
                'company' => 'Alpha Airline',
                'points' => 20000,
                'duration' => 300,
                'segment' => [
                    0 => [
                        'booking_reference' => 'AH11143',
                        'boarding_information' => [
                            'terminal' => 5,
                            'gate' => '7',
                        ],
                        'duration' => 300,
                        'departureTime' => '2023-07-04 11:00:00',
                        'arrivalTime' => '2023-07-04 14:00:00',
                        'origin' => 'Sydney',
                        'destination' => 'Jakarta',
                    ],
                ],
            ],
            [
                'id' => '5de21e211f78',
                'company' => 'Jet Airline',
                'points' => 15000,
                'duration' => 480,
                'segment' => [
                    0 => [
                        'booking_reference' => 'JA21121',
                        'boarding_information' => [
                            'terminal' => 1,
                            'gate' => '1',
                        ],
                        'duration' => 480,
                        'departureTime' => '2023-07-04 08:00:00',
                        'arrivalTime' => '2023-07-04 16:00:00',
                        'origin' => 'Sydney',
                        'destination' => 'Kuala Lumpur',
                    ],
                ],
            ],
            [
                'id' => '4fe21e477f78',
                'company' => 'Beta Airline',
                'points' => 10000,
                'duration' => 300,
                'segment' => [
                    0 => [
                        'duration' => 300,
                        'departureTime' => '2023-05-04 00:53:35',
                        'arrivalTime' => '2023-05-04 05:53:35',
                        'origin' => 'Singapore',
                        'destination' => 'Thailand',
                    ],
                ],
            ],
        ];

        $addedItems = [
            '1',
        ];

        $changedItems = [
            '0.id',
            '0.duration',
            '0.segment.0.booking_reference',
            '0.segment.0.boarding_information.terminal',
            '0.segment.0.boarding_information.gate',
            '0.segment.0.duration',
            '0.segment.0.departureTime',
            '0.segment.0.arrivalTime',
            '0.segment.0.destination',
        ];

        $removedItems = [
            '0.segment.1',
            '1.segment.0.booking_reference',
            '1.segment.0.boarding_information',
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $keysAdded = $jsonDiff->getKeysAdded();
        $valuesAdded = $jsonDiff->getValuesAdded();

        $keysRemoved = $jsonDiff->getKeysRemoved();
        $valuesRemoved = $jsonDiff->getValuesRemoved();

        $valuesChanged = $jsonDiff->getValuesChanged();

        // Check that the correct keys and values are added
        $keysAdded->each(function (KeyAdded $keyAdded) use ($addedItems): void {
            $this->assertContains($keyAdded->getPath(), $addedItems);
        });

        $valuesAdded->each(function (ValueAdded $valueAdded) use ($new): void {
            $this->assertSame(Arr::get($new, $valueAdded->getPath()), $valueAdded->getValue());
        });

        // Check that the correct keys and values are removed
        $keysRemoved->each(function (KeyRemoved $keyRemoved) use ($removedItems): void {
            $this->assertContains($keyRemoved->getPath(), $removedItems);
        });

        $valuesRemoved->each(function (ValueRemoved $valueRemoved) use ($original): void {
            $this->assertSame(Arr::get($original, $valueRemoved->getPath()), $valueRemoved->getValue());
        });

        // Check that the values changed are correct
        $valuesChanged->each(function (ValueChange $valueChange) use ($changedItems, $new, $original): void {
            $this->assertContains($valueChange->getPath(), $changedItems);
            $this->assertSame(Arr::get($original, $valueChange->getPath()), $valueChange->getOldValue());
            $this->assertSame(Arr::get($new, $valueChange->getPath()), $valueChange->getNewValue());
        });
    }

    public function test_json_diff_with_scalar_types(): void
    {
        $dataSets = [
            'string' => [
                'original' => 'Original String',
                'new' => 'New String',
                'changes' => 1,
            ],
            'number' => [
                'original' => 1,
                'new' => 2,
                'changes' => 1,
            ],
            'boolean' => [
                'original' => true,
                'new' => false,
                'changes' => 1,
            ],
            'null' => [
                'original' => null,
                'new' => null,
                'changes' => 0,
            ],
        ];

        // Compare same data types
        foreach ($dataSets as $dataSet) {
            $jsonDiff = new JsonDiff($dataSet['original'], $dataSet['new']);
            $this->assertCount($dataSet['changes'], $jsonDiff->getValuesChanged());
            $this->assertSame($dataSet['changes'], $jsonDiff->getNumberOfChanges());
            $this->assertCount(0, $jsonDiff->getKeysAdded());
            $this->assertCount(0, $jsonDiff->getKeysRemoved());
            $this->assertCount(0, $jsonDiff->getValuesAdded());
            $this->assertCount(0, $jsonDiff->getValuesRemoved());

            if (! $dataSet['changes']) {
                continue;
            }

            /** @var ValueChange $valueChange */
            $valueChange = $jsonDiff->getValuesChanged()->first();
            $this->assertSame(
                $dataSet['original'],
                $valueChange->getOldValue()
            );
            $this->assertSame(
                $dataSet['new'],
                $valueChange->getNewValue()
            );
            $this->assertSame(
                '',
                $valueChange->getPath()
            );
        }

        // Compare different data types
        foreach ($dataSets as $dataType => $dataSet) {
            foreach ($dataSets as $subDataType => $subDataSet) {
                if ($dataType === $subDataType) {
                    continue;
                }

                $jsonDiff = new JsonDiff($dataSet['original'], $subDataSet['original']);
                $this->assertCount(1, $jsonDiff->getValuesChanged());
                $this->assertSame(1, $jsonDiff->getNumberOfChanges());
                $this->assertCount(0, $jsonDiff->getKeysAdded());
                $this->assertCount(0, $jsonDiff->getKeysRemoved());
                $this->assertCount(0, $jsonDiff->getValuesAdded());
                $this->assertCount(0, $jsonDiff->getValuesRemoved());

                /** @var ValueChange $valueChange */
                $valueChange = $jsonDiff->getValuesChanged()->first();
                $this->assertSame(
                    $dataSet['original'],
                    $valueChange->getOldValue()
                );
                $this->assertSame(
                    $subDataSet['original'],
                    $valueChange->getNewValue()
                );
                $this->assertSame(
                    '',
                    $valueChange->getPath()
                );
            }
        }
    }

    public function test_recursiveness_with_nested_arrays(): void
    {
        $original = [
            'name' => 'John',
            'company' => [
                'name' => 'Company X',
            ],
        ];

        $new = [
            'name' => 'John',
            'company' => [
                'name' => 'Company Y',
            ],
        ];

        $jsonDiff = new JsonDiff($original, $new);

        $this->assertSame(1, $jsonDiff->getNumberOfChanges());
        $this->assertCount(1, $jsonDiff->getValuesChanged());
        /** @var ValueChange $valueChange */
        $valueChange = $jsonDiff->getValuesChanged()->first();
        $this->assertSame(
            'Company X',
            $valueChange->getOldValue()
        );
        $this->assertSame(
            'Company Y',
            $valueChange->getNewValue()
        );
    }

    public function test_basic_array_list(): void
    {
        $originalArray = [0, 1, 2];
        $newArray = [0, 1, 3];
        $jsonDiff = new JsonDiff($originalArray, $newArray);
        $this->assertCount(
            1,
            $jsonDiff->getValuesChanged()
        );
        $this->assertSame(
            1,
            $jsonDiff->getNumberOfChanges()
        );

        /** @var ValueChange $valueChanged */
        $valueChanged = $jsonDiff->getValuesChanged()->first();
        $this->assertSame(
            '2',
            $valueChanged->getPath()
        );
        $this->assertSame(
            2,
            $valueChanged->getOldValue()
        );
        $this->assertSame(
            3,
            $valueChanged->getNewValue()
        );
    }

    public function test_diff_between_scalar_type_and_array(): void
    {
        $original = 'a string';
        $new = [0, 1, 2];

        $jsonDiff = new JsonDiff($original, $new);

        $this->assertSame(
            1,
            $jsonDiff->getNumberOfChanges()
        );

        /** @var ValueChange $valueChange */
        $valueChange = $jsonDiff->getValuesChanged()->first();
        $this->assertSame(
            $original,
            $valueChange->getOldValue()
        );
        $this->assertSame(
            $new,
            $valueChange->getNewValue()
        );
        $this->assertSame(
            '',
            $valueChange->getPath()
        );

        // Reverse the diff
        $original = [0, 1, 2];
        $new = 'a string';

        $jsonDiff = new JsonDiff($original, $new);

        $this->assertSame(
            1,
            $jsonDiff->getNumberOfChanges()
        );

        /** @var ValueChange $valueChange */
        $valueChange = $jsonDiff->getValuesChanged()->first();
        $this->assertSame(
            $original,
            $valueChange->getOldValue()
        );
        $this->assertSame(
            $new,
            $valueChange->getNewValue()
        );
        $this->assertSame(
            '',
            $valueChange->getPath()
        );
    }

    public function test_path_generation(): void
    {
        $dataSets = [
            [
                'original' => 'a string',
                'new' => 'another string',
                'expectedPath' => '',
            ],
            [
                'original' => [0],
                'new' => [1],
                'expectedPath' => '0',
            ],
            [
                'original' => [
                    'name' => 'Bill Gates',
                    'sports' => [
                        'soccer',
                        'rugby',
                    ],
                ],
                'new' => [
                    'name' => 'Bill Gates',
                    'sports' => [
                        'soccer',
                        'tennis',
                    ],
                ],
                'expectedPath' => 'sports.1',
            ],
            [
                'original' => [
                    [
                        'name' => 'Bill Gates',
                        'children' => [
                            [
                                'name' => 'Alice Gates',
                                'sports' => [
                                    'soccer',
                                    'rugby',
                                ],
                            ],
                        ],
                    ],
                ],
                'new' => [
                    [
                        'name' => 'Bill Gates',
                        'children' => [
                            [
                                'name' => 'Alice Gates',
                                'sports' => [
                                    'soccer',
                                    'tennis',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedPath' => '0.children.0.sports.1',
            ],
        ];

        foreach ($dataSets as $dataSet) {
            $jsonDiff = new JsonDiff($dataSet['original'], $dataSet['new']);
            $this->assertCount(1, $jsonDiff->getValuesChanged());
            /** @var ValueChange $valueChange */
            $valueChange = $jsonDiff->getValuesChanged()->first();
            $this->assertSame($dataSet['expectedPath'], $valueChange->getPath());
        }
    }
}
