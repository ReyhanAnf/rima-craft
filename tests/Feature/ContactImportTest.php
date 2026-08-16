<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ContactImportTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = $this->createUserWithPermission('view-contacts');
    }

    public function test_can_import_contacts_via_json_array_with_phone_normalization(): void
    {
        $payload = [
            'default_type' => 'customer',
            'duplicate_strategy' => 'skip',
            'contacts' => [
                [
                    'name' => 'Budi Android User',
                    'phone' => '+62 812-3456-7890',
                    'email' => 'budi@gmail.com',
                    'address' => 'Jakarta Selatan',
                    'type' => 'customer',
                ],
                [
                    'name' => 'Siti Reseller Tasik',
                    'phone' => '0878-1122-3344',
                    'email' => 'siti@reseller.id',
                    'address' => 'Tasikmalaya',
                    'type' => 'reseller',
                ]
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('contacts.import'), $payload);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'name' => 'Budi Android User',
            'phone' => '081234567890',
            'type' => 'customer',
        ]);

        $this->assertDatabaseHas('contacts', [
            'name' => 'Siti Reseller Tasik',
            'phone' => '087811223344',
            'type' => 'reseller',
        ]);
    }

    public function test_duplicate_contacts_are_skipped_when_strategy_is_skip(): void
    {
        Contact::create([
            'name' => 'Ahmad Pelanggan Lama',
            'phone' => '081234567890',
            'type' => 'customer',
        ]);

        $payload = [
            'default_type' => 'customer',
            'duplicate_strategy' => 'skip',
            'contacts' => [
                [
                    'name' => 'Ahmad Pelanggan Baru',
                    'phone' => '+62 812-3456-7890', // duplicate phone
                    'email' => 'ahmad_new@gmail.com',
                    'type' => 'customer',
                ],
                [
                    'name' => 'Doni Baru',
                    'phone' => '085511223344',
                    'type' => 'customer',
                ]
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('contacts.import'), $payload);

        $response->assertRedirect(route('contacts.index'));

        // Doni should be created, Ahmad duplicate should not create a second row
        $this->assertDatabaseCount('contacts', 2);
        $this->assertDatabaseHas('contacts', [
            'name' => 'Doni Baru',
            'phone' => '085511223344',
        ]);
    }

    public function test_can_import_vcard_vcf_file(): void
    {
        $vcfContent = <<<VCF
BEGIN:VCARD
VERSION:3.0
FN:Pak Hendra Pengrajin
TEL;TYPE=CELL:+62 899-1234-5678
EMAIL:hendra@craft.id
ADR:;;Jl. Kerajinan No. 12;Cirebon;Jawa Barat;;Indonesia
END:VCARD
VCF;

        $file = UploadedFile::fake()->createWithContent('contacts.vcf', $vcfContent);

        $response = $this->actingAs($this->adminUser)
            ->post(route('contacts.import'), [
                'file' => $file,
                'default_type' => 'crafter',
                'duplicate_strategy' => 'skip',
            ]);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'name' => 'Pak Hendra Pengrajin',
            'phone' => '089912345678',
            'email' => 'hendra@craft.id',
            'type' => 'crafter',
        ]);
    }
}
