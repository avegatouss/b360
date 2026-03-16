<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Support\Str;
use Modules\Eshop360\Models\Brand;
use Modules\Eshop360\Models\Category;
use Modules\Eshop360\Models\Product;

/**
 * Seeds pharmaceutical demo data: categories, brands, and 100 products.
 * Designed for West African pharmaceutical wholesale (SAPHIR/CODIFARM context).
 *
 * Called by the Demo module via HookRegistry::addDemoProvider().
 */
final class DemoCatalogPharmaSeeder
{
    public function run(int $instanceId): void
    {
        $categories = $this->seedCategories($instanceId);
        $brands = $this->seedBrands($instanceId);
        $this->seedProducts($instanceId, $categories, $brands);
    }

    public function reset(int $instanceId): void
    {
        Product::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('sku', 'like', 'PHARMA-%')->forceDelete();
        Brand::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('slug', 'like', 'pharma-%')->delete();
        Category::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('slug', 'like', 'pharma-%')->delete();
    }

    private function seedCategories(int $instanceId): array
    {
        $tree = [
            'Analgesiques & Antipyretiques' => [
                'Paracetamol', 'Ibuprofene', 'Aspirine', 'Tramadol',
            ],
            'Antibiotiques' => [
                'Penicillines', 'Cephalosporines', 'Macrolides', 'Fluoroquinolones', 'Tetracyclines',
            ],
            'Antipaludeens' => [
                'ACT (Combinaisons)', 'Quinine', 'Chloroquine', 'Artemether',
            ],
            'Anti-inflammatoires' => [
                'AINS', 'Corticoides',
            ],
            'Vitamines & Supplements' => [
                'Vitamines', 'Mineraux', 'Complements alimentaires',
            ],
            'Antibacteriens & Antiseptiques' => [
                'Antiseptiques cutanes', 'Desinfectants',
            ],
            'Antitussifs & Bronchodilatateurs' => [
                'Sirops', 'Inhalateurs',
            ],
            'Gastro-enterologie' => [
                'Antiacides', 'Antidiarrheiques', 'Laxatifs', 'Antiemetiques',
            ],
            'Dermatologie' => [
                'Cremes', 'Pommades', 'Lotions',
            ],
            'Materiel medical & Consommables' => [
                'Seringues & Aiguilles', 'Pansements', 'Gants', 'Tests rapides',
            ],
        ];

        $result = [];

        foreach ($tree as $parentName => $children) {
            $parentSlug = 'pharma-' . Str::slug($parentName);

            $parent = Category::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'slug' => $parentSlug],
                [
                    'name' => $parentName,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );

            $result[$parentName] = $parent;

            foreach ($children as $i => $childName) {
                $childSlug = 'pharma-' . Str::slug($childName);

                $child = Category::withoutGlobalScopes()->updateOrCreate(
                    ['instance_id' => $instanceId, 'slug' => $childSlug],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'is_active' => true,
                        'sort_order' => $i,
                    ],
                );

                $result[$childName] = $child;
            }
        }

        return $result;
    }

    private function seedBrands(int $instanceId): array
    {
        $brandsData = [
            'Sanofi', 'Novartis', 'Roche', 'Pfizer', 'GSK',
            'Bayer', 'AstraZeneca', 'Johnson & Johnson', 'Merck', 'Abbott',
            'Cipla', 'Dr. Reddy\'s', 'Sun Pharma', 'Aurobindo', 'Hikma',
            'Denk Pharma', 'Pharmivoire', 'CIPHARM', 'Maphar', 'Galenica',
        ];

        $result = [];

        foreach ($brandsData as $brandName) {
            $slug = 'pharma-' . Str::slug($brandName);

            $brand = Brand::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'slug' => $slug],
                [
                    'name' => $brandName,
                    'is_active' => true,
                ],
            );

            $result[$brandName] = $brand;
        }

        return $result;
    }

    private function seedProducts(int $instanceId, array $categories, array $brands): void
    {
        $products = $this->getPharmaceuticalProducts();
        $brandNames = array_keys($brands);

        foreach ($products as $i => $p) {
            $sku = sprintf('PHARMA-%04d', $i + 1);
            $catModel = $categories[$p['category']] ?? null;
            $brandModel = $brands[$brandNames[array_rand($brandNames)]] ?? null;

            // Pricing SAPHIR logic
            $factoryPrice = $p['factory_price'];
            $provisionalPrice = $factoryPrice * 1.35;
            $pght = round($provisionalPrice * 1.13, 2);
            $salePrice = round($pght * 1.10, 2);
            $codifarmPrice = round($pght * 1.20, 2);

            Product::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'sku' => $sku],
                [
                    'category_id' => $catModel?->id,
                    'brand_id' => $brandModel?->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']) . '-' . $sku,
                    'description' => $p['description'] ?? null,
                    'unit' => $p['unit'],
                    'price' => $salePrice,
                    'cost_price' => $provisionalPrice,
                    'purchase_price_factory' => $factoryPrice,
                    'purchase_price_provisional' => $provisionalPrice,
                    'pght' => $pght,
                    'cost_price_real' => $factoryPrice * 1.40,
                    'sale_price_codifarm' => $codifarmPrice,
                    'tax_rate' => 0,
                    'min_quantity' => 1,
                    'alert_quantity' => $p['alert'] ?? 20,
                    'stock_alert_quantity' => $p['alert'] ?? 20,
                    'expiry_alert_days' => 60,
                    'barcode_type' => 'code128',
                    'is_active' => true,
                ],
            );
        }
    }

    private function getPharmaceuticalProducts(): array
    {
        return [
            // ======= Analgesiques & Antipyretiques (15) =======
            ['name' => 'Paracetamol 500mg Cp B/100', 'category' => 'Paracetamol', 'unit' => 'boite', 'factory_price' => 850, 'alert' => 50, 'description' => 'Comprime 500mg, boite de 100'],
            ['name' => 'Paracetamol 1g Cp B/8', 'category' => 'Paracetamol', 'unit' => 'boite', 'factory_price' => 450, 'alert' => 30],
            ['name' => 'Doliprane 1000mg Cp B/8', 'category' => 'Paracetamol', 'unit' => 'boite', 'factory_price' => 1200, 'alert' => 25],
            ['name' => 'Efferalgan 500mg Cp Eff B/16', 'category' => 'Paracetamol', 'unit' => 'boite', 'factory_price' => 1800, 'alert' => 20],
            ['name' => 'Dafalgan Sirop Enfant 150ml', 'category' => 'Paracetamol', 'unit' => 'flacon', 'factory_price' => 2200, 'alert' => 15],
            ['name' => 'Ibuprofene 400mg Cp B/30', 'category' => 'Ibuprofene', 'unit' => 'boite', 'factory_price' => 1100, 'alert' => 30],
            ['name' => 'Ibuprofene 200mg Cp B/20', 'category' => 'Ibuprofene', 'unit' => 'boite', 'factory_price' => 750, 'alert' => 40],
            ['name' => 'Nurofen 400mg Cp B/12', 'category' => 'Ibuprofene', 'unit' => 'boite', 'factory_price' => 2500, 'alert' => 15],
            ['name' => 'Aspirine 500mg Cp B/20', 'category' => 'Aspirine', 'unit' => 'boite', 'factory_price' => 650, 'alert' => 30],
            ['name' => 'Aspegic 1000mg Sach B/20', 'category' => 'Aspirine', 'unit' => 'boite', 'factory_price' => 2800, 'alert' => 15],
            ['name' => 'Tramadol 50mg Gel B/30', 'category' => 'Tramadol', 'unit' => 'boite', 'factory_price' => 3500, 'alert' => 10],
            ['name' => 'Tramadol 100mg Cp LP B/30', 'category' => 'Tramadol', 'unit' => 'boite', 'factory_price' => 5200, 'alert' => 10],
            ['name' => 'Diclofenac 50mg Cp B/30', 'category' => 'Ibuprofene', 'unit' => 'boite', 'factory_price' => 900, 'alert' => 25],
            ['name' => 'Ketoprofene 100mg Cp B/20', 'category' => 'Ibuprofene', 'unit' => 'boite', 'factory_price' => 1400, 'alert' => 20],
            ['name' => 'Paracetamol Codeine 500/30mg B/16', 'category' => 'Paracetamol', 'unit' => 'boite', 'factory_price' => 2100, 'alert' => 15],

            // ======= Antibiotiques (20) =======
            ['name' => 'Amoxicilline 500mg Gel B/12', 'category' => 'Penicillines', 'unit' => 'boite', 'factory_price' => 1200, 'alert' => 40],
            ['name' => 'Amoxicilline 1g Cp B/14', 'category' => 'Penicillines', 'unit' => 'boite', 'factory_price' => 2200, 'alert' => 30],
            ['name' => 'Amoxicilline Sirop 250mg/5ml 60ml', 'category' => 'Penicillines', 'unit' => 'flacon', 'factory_price' => 1500, 'alert' => 25],
            ['name' => 'Augmentin 1g/125mg Cp B/12', 'category' => 'Penicillines', 'unit' => 'boite', 'factory_price' => 4500, 'alert' => 20],
            ['name' => 'Augmentin Sirop Enfant 100ml', 'category' => 'Penicillines', 'unit' => 'flacon', 'factory_price' => 3800, 'alert' => 15],
            ['name' => 'Cefixime 200mg Cp B/8', 'category' => 'Cephalosporines', 'unit' => 'boite', 'factory_price' => 3200, 'alert' => 15],
            ['name' => 'Ceftriaxone 1g Inj', 'category' => 'Cephalosporines', 'unit' => 'ampoule', 'factory_price' => 1800, 'alert' => 30],
            ['name' => 'Cefalexine 500mg Gel B/16', 'category' => 'Cephalosporines', 'unit' => 'boite', 'factory_price' => 2800, 'alert' => 20],
            ['name' => 'Azithromycine 500mg Cp B/3', 'category' => 'Macrolides', 'unit' => 'boite', 'factory_price' => 2500, 'alert' => 20],
            ['name' => 'Erythromycine 500mg Cp B/16', 'category' => 'Macrolides', 'unit' => 'boite', 'factory_price' => 2200, 'alert' => 15],
            ['name' => 'Ciprofloxacine 500mg Cp B/10', 'category' => 'Fluoroquinolones', 'unit' => 'boite', 'factory_price' => 1500, 'alert' => 25],
            ['name' => 'Levofloxacine 500mg Cp B/7', 'category' => 'Fluoroquinolones', 'unit' => 'boite', 'factory_price' => 3800, 'alert' => 15],
            ['name' => 'Ofloxacine 200mg Cp B/10', 'category' => 'Fluoroquinolones', 'unit' => 'boite', 'factory_price' => 1800, 'alert' => 20],
            ['name' => 'Doxycycline 100mg Cp B/10', 'category' => 'Tetracyclines', 'unit' => 'boite', 'factory_price' => 800, 'alert' => 30],
            ['name' => 'Metronidazole 500mg Cp B/20', 'category' => 'Fluoroquinolones', 'unit' => 'boite', 'factory_price' => 700, 'alert' => 40],
            ['name' => 'Cotrimoxazole 480mg Cp B/20', 'category' => 'Penicillines', 'unit' => 'boite', 'factory_price' => 600, 'alert' => 35],
            ['name' => 'Gentamicine 80mg/2ml Inj B/10', 'category' => 'Penicillines', 'unit' => 'boite', 'factory_price' => 2500, 'alert' => 15],
            ['name' => 'Cloxacilline 500mg Gel B/12', 'category' => 'Penicillines', 'unit' => 'boite', 'factory_price' => 2000, 'alert' => 20],
            ['name' => 'Clarithromycine 500mg Cp B/14', 'category' => 'Macrolides', 'unit' => 'boite', 'factory_price' => 4200, 'alert' => 10],
            ['name' => 'Amoxicilline Acide Clav Sirop 100ml', 'category' => 'Penicillines', 'unit' => 'flacon', 'factory_price' => 3200, 'alert' => 15],

            // ======= Antipaludeens (10) =======
            ['name' => 'Coartem 20/120mg Cp B/24 Adulte', 'category' => 'ACT (Combinaisons)', 'unit' => 'boite', 'factory_price' => 3500, 'alert' => 30],
            ['name' => 'Coartem 20/120mg Cp B/12 Enfant', 'category' => 'ACT (Combinaisons)', 'unit' => 'boite', 'factory_price' => 2200, 'alert' => 30],
            ['name' => 'Artesunate-Amodiaquine Cp B/6 Adulte', 'category' => 'ACT (Combinaisons)', 'unit' => 'boite', 'factory_price' => 1800, 'alert' => 40],
            ['name' => 'Artesunate 60mg Inj', 'category' => 'Artemether', 'unit' => 'ampoule', 'factory_price' => 2500, 'alert' => 20],
            ['name' => 'Artemether 80mg/ml Inj 1ml', 'category' => 'Artemether', 'unit' => 'ampoule', 'factory_price' => 1500, 'alert' => 25],
            ['name' => 'Quinine 300mg Cp B/30', 'category' => 'Quinine', 'unit' => 'boite', 'factory_price' => 2800, 'alert' => 15],
            ['name' => 'Quinine 600mg/2ml Inj B/10', 'category' => 'Quinine', 'unit' => 'boite', 'factory_price' => 4500, 'alert' => 10],
            ['name' => 'SP Fansidar 500/25mg Cp B/3', 'category' => 'ACT (Combinaisons)', 'unit' => 'boite', 'factory_price' => 500, 'alert' => 50],
            ['name' => 'Dihydroartemisinine-Piperaquine B/9', 'category' => 'ACT (Combinaisons)', 'unit' => 'boite', 'factory_price' => 4200, 'alert' => 15],
            ['name' => 'Chloroquine 100mg Cp B/30', 'category' => 'Chloroquine', 'unit' => 'boite', 'factory_price' => 600, 'alert' => 25],

            // ======= Vitamines & Supplements (10) =======
            ['name' => 'Vitamine C 500mg Cp Eff B/10', 'category' => 'Vitamines', 'unit' => 'tube', 'factory_price' => 1200, 'alert' => 30],
            ['name' => 'Vitamine C 1000mg Cp Eff B/10', 'category' => 'Vitamines', 'unit' => 'tube', 'factory_price' => 1800, 'alert' => 25],
            ['name' => 'Fer Acide Folique Cp B/30', 'category' => 'Mineraux', 'unit' => 'boite', 'factory_price' => 900, 'alert' => 40],
            ['name' => 'Zinc 20mg Cp B/10', 'category' => 'Mineraux', 'unit' => 'boite', 'factory_price' => 500, 'alert' => 50],
            ['name' => 'Calcium Vitamine D3 Cp B/60', 'category' => 'Mineraux', 'unit' => 'boite', 'factory_price' => 3500, 'alert' => 15],
            ['name' => 'Multivitamines Cp B/30', 'category' => 'Complements alimentaires', 'unit' => 'boite', 'factory_price' => 2200, 'alert' => 20],
            ['name' => 'Omega 3 Gel B/60', 'category' => 'Complements alimentaires', 'unit' => 'boite', 'factory_price' => 4500, 'alert' => 10],
            ['name' => 'Vitamine B Complex Cp B/30', 'category' => 'Vitamines', 'unit' => 'boite', 'factory_price' => 1500, 'alert' => 25],
            ['name' => 'Vitamine D3 1000UI Cp B/30', 'category' => 'Vitamines', 'unit' => 'boite', 'factory_price' => 2800, 'alert' => 15],
            ['name' => 'Spiruline 500mg Gel B/60', 'category' => 'Complements alimentaires', 'unit' => 'boite', 'factory_price' => 3800, 'alert' => 10],

            // ======= Anti-inflammatoires (5) =======
            ['name' => 'Prednisolone 5mg Cp B/30', 'category' => 'Corticoides', 'unit' => 'boite', 'factory_price' => 1200, 'alert' => 20],
            ['name' => 'Dexamethasone 4mg/ml Inj B/10', 'category' => 'Corticoides', 'unit' => 'boite', 'factory_price' => 3500, 'alert' => 15],
            ['name' => 'Betamethasone Creme 0.1% 30g', 'category' => 'Corticoides', 'unit' => 'tube', 'factory_price' => 1800, 'alert' => 20],
            ['name' => 'Piroxicam 20mg Gel B/10', 'category' => 'AINS', 'unit' => 'boite', 'factory_price' => 800, 'alert' => 25],
            ['name' => 'Meloxicam 15mg Cp B/10', 'category' => 'AINS', 'unit' => 'boite', 'factory_price' => 1500, 'alert' => 20],

            // ======= Gastro-enterologie (10) =======
            ['name' => 'Omeprazole 20mg Gel B/14', 'category' => 'Antiacides', 'unit' => 'boite', 'factory_price' => 1200, 'alert' => 30],
            ['name' => 'Lansoprazole 30mg Gel B/14', 'category' => 'Antiacides', 'unit' => 'boite', 'factory_price' => 2200, 'alert' => 20],
            ['name' => 'Maalox Susp 250ml', 'category' => 'Antiacides', 'unit' => 'flacon', 'factory_price' => 2500, 'alert' => 15],
            ['name' => 'Loperamide 2mg Gel B/20', 'category' => 'Antidiarrheiques', 'unit' => 'boite', 'factory_price' => 800, 'alert' => 30],
            ['name' => 'SRO Sachets B/20', 'category' => 'Antidiarrheiques', 'unit' => 'boite', 'factory_price' => 350, 'alert' => 60],
            ['name' => 'Metoclopramide 10mg Cp B/30', 'category' => 'Antiemetiques', 'unit' => 'boite', 'factory_price' => 700, 'alert' => 25],
            ['name' => 'Ondansetron 4mg Cp B/10', 'category' => 'Antiemetiques', 'unit' => 'boite', 'factory_price' => 3200, 'alert' => 10],
            ['name' => 'Bisacodyl 5mg Cp B/30', 'category' => 'Laxatifs', 'unit' => 'boite', 'factory_price' => 600, 'alert' => 25],
            ['name' => 'Lactulose Sirop 200ml', 'category' => 'Laxatifs', 'unit' => 'flacon', 'factory_price' => 2800, 'alert' => 10],
            ['name' => 'Ranitidine 150mg Cp B/20', 'category' => 'Antiacides', 'unit' => 'boite', 'factory_price' => 900, 'alert' => 25],

            // ======= Antitussifs & Respiratoire (5) =======
            ['name' => 'Carbocisteine Sirop Adulte 200ml', 'category' => 'Sirops', 'unit' => 'flacon', 'factory_price' => 2200, 'alert' => 15],
            ['name' => 'Dextromethorphane Sirop 150ml', 'category' => 'Sirops', 'unit' => 'flacon', 'factory_price' => 1800, 'alert' => 15],
            ['name' => 'Salbutamol 100mcg Aerosol', 'category' => 'Inhalateurs', 'unit' => 'unite', 'factory_price' => 3500, 'alert' => 10],
            ['name' => 'Ambroxol Sirop Enfant 100ml', 'category' => 'Sirops', 'unit' => 'flacon', 'factory_price' => 1500, 'alert' => 20],
            ['name' => 'Fluticasone Spray Nasal 120 doses', 'category' => 'Inhalateurs', 'unit' => 'unite', 'factory_price' => 5500, 'alert' => 8],

            // ======= Antiseptiques (5) =======
            ['name' => 'Betadine Dermique 10% 125ml', 'category' => 'Antiseptiques cutanes', 'unit' => 'flacon', 'factory_price' => 2500, 'alert' => 20],
            ['name' => 'Chlorhexidine 0.5% 250ml', 'category' => 'Antiseptiques cutanes', 'unit' => 'flacon', 'factory_price' => 1200, 'alert' => 25],
            ['name' => 'Alcool 70° 250ml', 'category' => 'Desinfectants', 'unit' => 'flacon', 'factory_price' => 800, 'alert' => 30],
            ['name' => 'Eau Oxygenee 10V 250ml', 'category' => 'Desinfectants', 'unit' => 'flacon', 'factory_price' => 600, 'alert' => 30],
            ['name' => 'Hexomedine 0.1% 45ml', 'category' => 'Antiseptiques cutanes', 'unit' => 'flacon', 'factory_price' => 3200, 'alert' => 10],

            // ======= Dermatologie (5) =======
            ['name' => 'Econazole Creme 1% 30g', 'category' => 'Cremes', 'unit' => 'tube', 'factory_price' => 1800, 'alert' => 15],
            ['name' => 'Terbinafine Creme 1% 15g', 'category' => 'Cremes', 'unit' => 'tube', 'factory_price' => 2200, 'alert' => 12],
            ['name' => 'Acide Fusidique Pommade 2% 15g', 'category' => 'Pommades', 'unit' => 'tube', 'factory_price' => 2800, 'alert' => 10],
            ['name' => 'Hydrocortisone Creme 1% 15g', 'category' => 'Cremes', 'unit' => 'tube', 'factory_price' => 1500, 'alert' => 15],
            ['name' => 'Calamine Lotion 200ml', 'category' => 'Lotions', 'unit' => 'flacon', 'factory_price' => 1200, 'alert' => 15],

            // ======= Materiel medical (15) =======
            ['name' => 'Seringue 5ml Luer B/100', 'category' => 'Seringues & Aiguilles', 'unit' => 'boite', 'factory_price' => 4500, 'alert' => 20],
            ['name' => 'Seringue 10ml Luer B/100', 'category' => 'Seringues & Aiguilles', 'unit' => 'boite', 'factory_price' => 5200, 'alert' => 15],
            ['name' => 'Seringue Insuline 1ml B/100', 'category' => 'Seringues & Aiguilles', 'unit' => 'boite', 'factory_price' => 4800, 'alert' => 15],
            ['name' => 'Aiguille 21G B/100', 'category' => 'Seringues & Aiguilles', 'unit' => 'boite', 'factory_price' => 2200, 'alert' => 20],
            ['name' => 'Sparadrap 5m x 2.5cm', 'category' => 'Pansements', 'unit' => 'rouleau', 'factory_price' => 800, 'alert' => 30],
            ['name' => 'Compresse Sterile 10x10cm B/100', 'category' => 'Pansements', 'unit' => 'boite', 'factory_price' => 3500, 'alert' => 20],
            ['name' => 'Bande de Gaze 5m x 10cm', 'category' => 'Pansements', 'unit' => 'unite', 'factory_price' => 350, 'alert' => 50],
            ['name' => 'Coton Hydrophile 500g', 'category' => 'Pansements', 'unit' => 'paquet', 'factory_price' => 2500, 'alert' => 15],
            ['name' => 'Gants Latex Examen M B/100', 'category' => 'Gants', 'unit' => 'boite', 'factory_price' => 3200, 'alert' => 20],
            ['name' => 'Gants Latex Examen L B/100', 'category' => 'Gants', 'unit' => 'boite', 'factory_price' => 3200, 'alert' => 20],
            ['name' => 'Gants Nitrile M B/100', 'category' => 'Gants', 'unit' => 'boite', 'factory_price' => 4500, 'alert' => 15],
            ['name' => 'TDR Paludisme B/25', 'category' => 'Tests rapides', 'unit' => 'boite', 'factory_price' => 8500, 'alert' => 10],
            ['name' => 'Test Grossesse B/25', 'category' => 'Tests rapides', 'unit' => 'boite', 'factory_price' => 5500, 'alert' => 10],
            ['name' => 'Bandelettes Urinaires B/50', 'category' => 'Tests rapides', 'unit' => 'boite', 'factory_price' => 7500, 'alert' => 8],
            ['name' => 'Thermometre Digital', 'category' => 'Tests rapides', 'unit' => 'unite', 'factory_price' => 1500, 'alert' => 15],
        ];
    }
}
