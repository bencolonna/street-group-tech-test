<?php

namespace Tests\Unit\Services;

use App\Services\FormatNamesService;
use Exception;
use Tests\TestCase;

class FormatNamesServiceTest extends TestCase
{
    public function test_process_single_name_with_full_first_name(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mrs Jane Jones']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mrs',
                'first_name' => 'Jane',
                'last_name' => 'Jones',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_single_name_with_initial_without_dot(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr J Smith']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => null,
                'last_name' => 'Smith',
                'initial' => 'J',
            ],
        ]), $result);
    }

    public function test_process_single_name_with_initial_dot(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mrs J. Jones']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mrs',
                'first_name' => null,
                'last_name' => 'Jones',
                'initial' => 'J',
            ],
        ]), $result);
    }

    public function test_process_two_names_with_and_separator(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr John Smith and Mrs Jane Jones']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'initial' => null,
            ],
            [
                'title' => 'Mrs',
                'first_name' => 'Jane',
                'last_name' => 'Jones',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_three_names_with_and_separator(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr John Smith and Mrs Jane Jones and Dr Sam Williams']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'initial' => null,
            ],
            [
                'title' => 'Mrs',
                'first_name' => 'Jane',
                'last_name' => 'Jones',
                'initial' => null,
            ],
            [
                'title' => 'Dr',
                'first_name' => 'Sam',
                'last_name' => 'Williams',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_two_names_with_ampersand_separator(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr John Smith & Mrs Jane Jones']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'initial' => null,
            ],
            [
                'title' => 'Mrs',
                'first_name' => 'Jane',
                'last_name' => 'Jones',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_three_names_with_ampersand_separator(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr John Smith & Mrs Jane Jones & Dr Sam Williams']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'initial' => null,
            ],
            [
                'title' => 'Mrs',
                'first_name' => 'Jane',
                'last_name' => 'Jones',
                'initial' => null,
            ],
            [
                'title' => 'Dr',
                'first_name' => 'Sam',
                'last_name' => 'Williams',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_multiple_names_with_no_first_name(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr and Mrs Smith']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => null,
                'last_name' => 'Smith',
                'initial' => null,
            ],
            [
                'title' => 'Mrs',
                'first_name' => null,
                'last_name' => 'Smith',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_multiple_names_with_first_name(): void
    {
        $service = new FormatNamesService();
        $result = $service->formatNames(['Mr and Mrs John Smith']);

        $this->assertEquals(json_encode([
            [
                'title' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'initial' => null,
            ],
            [
                'title' => 'Mrs',
                'first_name' => null,
                'last_name' => 'Smith',
                'initial' => null,
            ],
        ]), $result);
    }

    public function test_process_without_input_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No input data to process');

        $service = new FormatNamesService();
        $service->formatNames([]);
    }

    public function test_too_few_parts_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Name has an unsupported number of parts: Mrs');

        $service = new FormatNamesService();
        $service->formatNames(['Mrs']);
    }

    public function test_too_many_parts_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Name has an unsupported number of parts: Mr John A Smith');

        $service = new FormatNamesService();
        $service->formatNames(['Mr John A Smith']);
    }
}
