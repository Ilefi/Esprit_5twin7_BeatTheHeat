<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Category;
use App\Models\Certification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Gestion 1 — Produits & Certifications: admin CRUD, image upload and the product page.
 */
class ProductCertificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_creates_a_product_with_image_and_certifications(): void
    {
        $certifications = Certification::whereIn('slug', ['bio', 'local'])->pluck('id')->all();

        $response = $this->actingAs($this->admin)->post('/admin/produits', $this->productData([
            'certifications' => $certifications,
            'image' => $this->png(800, 600),
        ]));

        $product = Product::where('name', 'Figues séchées de Djebba')->firstOrFail();
        $response->assertRedirect(route('admin.products.show', $product->id))->assertSessionHas('success');

        $this->assertSame('figues-sechees-de-djebba', $product->slug);
        $this->assertEqualsCanonicalizing($certifications, $product->certifications->pluck('id')->all());
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_product_validation_rejects_bad_images_and_unknown_references(): void
    {
        $this->actingAs($this->admin)->from('/admin/produits/create')->post('/admin/produits', $this->productData([
            'producer_id' => Actor::where('type', '!=', 'producer')->value('id'),
            'certifications' => [999],
            'price' => '12.345',
            'image' => $this->png(100, 100),
        ]))
            ->assertRedirect('/admin/produits/create')
            ->assertSessionHasErrors(['producer_id', 'certifications.0', 'price', 'image']);

        $this->actingAs($this->admin)->post('/admin/produits', $this->productData([
            'image' => UploadedFile::fake()->create('notice.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors('image');

        $this->actingAs($this->admin)->post('/admin/produits', $this->productData([
            'image' => UploadedFile::fake()->createWithContent('lourde.png', $this->pngBytes(800, 600))->size(5000),
        ]))->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', ['name' => 'Figues séchées de Djebba']);
    }

    public function test_admin_updates_a_product_and_replaces_its_image(): void
    {
        $product = Product::factory()->create(['image' => UploadedFile::fake()->createWithContent('a.png', $this->pngBytes(800, 600))->store('products', 'public')]);
        $oldImage = $product->image;
        $bio = Certification::where('slug', 'bio')->value('id');

        // Without a new file, the current image is kept.
        $this->actingAs($this->admin)->put("/admin/produits/{$product->id}", $this->productData(['name' => 'Nom modifié', 'certifications' => [$bio]]))
            ->assertRedirect(route('admin.products.show', $product->id));

        $product->refresh();
        $this->assertSame('Nom modifié', $product->name);
        $this->assertSame($oldImage, $product->image);
        $this->assertSame([$bio], $product->certifications->pluck('id')->all());

        // A new file replaces (and deletes) the previous one; unchecked certifications are detached.
        $this->actingAs($this->admin)->put("/admin/produits/{$product->id}", $this->productData(['image' => $this->png(1200, 900)]));

        $product->refresh();
        $this->assertNotSame($oldImage, $product->image);
        $this->assertCount(0, $product->certifications);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_deletes_a_product_unless_it_is_reported(): void
    {
        $product = Product::factory()->create(['image' => UploadedFile::fake()->createWithContent('a.png', $this->pngBytes(800, 600))->store('products', 'public')]);

        $this->actingAs($this->admin)->delete("/admin/produits/{$product->id}")->assertRedirect('/admin/produits');
        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing($product->image);

        $reported = Product::where('slug', 'huile-olive-sfax')->firstOrFail();
        $this->actingAs($this->admin)->delete("/admin/produits/{$reported->id}")->assertSessionHas('error');
        $this->assertModelExists($reported);
    }

    public function test_admin_creates_and_updates_a_certification(): void
    {
        $data = [
            'name' => 'Pêche durable',
            'short_name' => 'Pêche durable',
            'type' => 'reasoned',
            'issuer' => 'Organisme de test',
            'expires_at' => now()->addYear()->toDateString(),
            'description' => 'Encadre les quotas, les engins de pêche et la saisonnalité des captures.',
            'criteria' => "Quotas respectés\r\n\r\n  Engins sélectifs  \n",
            'guarantees' => 'Stocks préservés',
            'limits' => '',
        ];

        $this->actingAs($this->admin)->post('/admin/certifications', $data)->assertRedirect('/admin/certifications');

        $certification = Certification::where('slug', 'peche-durable')->firstOrFail();
        $this->assertSame(['Quotas respectés', 'Engins sélectifs'], $certification->criteria);
        $this->assertSame([], $certification->limits);
        $this->assertFalse($certification->is_expired);

        $this->actingAs($this->admin)->from('/admin/certifications/create')
            ->post('/admin/certifications', ['expires_at' => now()->subDay()->toDateString()] + $data)
            ->assertSessionHasErrors(['name', 'short_name', 'expires_at']);

        // An existing certification may be recorded as expired.
        $this->actingAs($this->admin)->put("/admin/certifications/{$certification->id}", ['expires_at' => now()->subDay()->toDateString()] + $data)
            ->assertSessionHasNoErrors();
        $this->assertTrue($certification->refresh()->is_expired);
    }

    public function test_admin_deletes_a_certification_unless_it_is_reported(): void
    {
        $certification = Certification::factory()->create();
        $product = Product::factory()->create();
        $product->certifications()->attach($certification);

        $this->actingAs($this->admin)->delete("/admin/certifications/{$certification->id}")->assertRedirect('/admin/certifications');
        $this->assertModelMissing($certification);
        $this->assertCount(0, $product->certifications()->get());

        $reported = Certification::where('slug', 'agriculture-raisonnee')->firstOrFail();
        $this->actingAs($this->admin)->delete("/admin/certifications/{$reported->id}")->assertSessionHas('error');
        $this->assertModelExists($reported);
    }

    public function test_admin_manages_categories(): void
    {
        $this->actingAs($this->admin)->post('/admin/categories', ['name' => 'Poissons', 'icon' => 'fa-fish'])->assertSessionHasNoErrors();
        $category = Category::where('slug', 'poissons')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/categories', ['name' => 'Poissons', 'icon' => 'fa-fish'])->assertSessionHasErrors('name', null, 'createCategory');

        $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}")->assertSessionHas('success');
        $this->assertModelMissing($category);

        $used = Category::where('slug', 'fruits')->firstOrFail();
        $this->actingAs($this->admin)->delete("/admin/categories/{$used->id}")->assertSessionHas('error');
        $this->assertModelExists($used);
    }

    public function test_product_page_shows_certifications_with_their_validity(): void
    {
        // The seeded "Agriculture raisonnée" label has expired; "Produit local" never expires.
        $this->get('/produits/oranges-maltaises-nabeul')
            ->assertOk()
            ->assertSee('Produit local')
            ->assertSee('Agriculture raisonnée')
            ->assertSee('Certificat valide')
            ->assertSee('Certificat expiré');
    }

    private function productData(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Figues séchées de Djebba',
            'category_id' => Category::where('slug', 'fruits-secs')->value('id'),
            'producer_id' => Actor::where('type', 'producer')->value('id'),
            'region' => 'Béja',
            'format' => 'Sachet kraft 250 g',
            'price' => '14.50',
            'status' => 'draft',
            'description' => 'Figues séchées au soleil sur les pentes du Djebel Goraa.',
        ];
    }

    private function png(int $width, int $height): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('produit.png', $this->pngBytes($width, $height));
    }

    /** A valid black PNG built without the GD extension. */
    private function pngBytes(int $width, int $height): string
    {
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $pixels = str_repeat("\0".str_repeat("\0\0\0", $width), $height);

        return "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($pixels))
            .$chunk('IEND', '');
    }
}
