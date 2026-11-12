<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductImageCatalog
{
    /**
     * @var array<int, string>
     */
    private const UPLOADED_IMAGE_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];

    /**
     * @var array<string, string>
     */
    private const OFFICIAL_IMAGES = [
        'mikrotik groovea 52 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1208_lg.webp',
        'mikrotik groove 52 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1483_lg.webp',
        'mikrotik omnitik 5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1243_lg.webp',
        'mikrotik mantbox ax 19s' => 'https://cdn.mikrotik.com/web-assets/rb_images/2309_lg.webp',
        'mikrotik mantbox ax 15s' => 'https://cdn.mikrotik.com/web-assets/rb_images/2309_lg.webp',
        'mikrotik qrt 5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1044_lg.webp',
        'mikrotik sxt hg5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1043_lg.webp',
        'mikrotik lhg hp5' => 'https://cdn.mikrotik.com/web-assets/rb_images/1382_lg.webp',
        'mikrotik lhg 5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1387_lg.webp',
        'mikrotik lhg xl hp5' => 'https://cdn.mikrotik.com/web-assets/rb_images/1381_lg.webp',
        'mikrotik lhg xl 5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1432_lg.webp',
        'mikrotik omnitik upa-5hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/785_lg.webp',
        'mikrotik basebox 2' => 'https://cdn.mikrotik.com/web-assets/rb_images/662_lg.webp',
        'mikrotik basebox 5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/779_lg.webp',
        'mikrotik netmetal ac2' => 'https://cdn.mikrotik.com/web-assets/rb_images/1902_lg.webp',
        'mikrotik netmetal ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2347_lg.webp',
        'mikrotik netbox 5 with mantbox' => 'https://cdn.mikrotik.com/web-assets/rb_images/933_lg.webp',
        'mikrotik netmetal ax with mant30' => 'https://cdn.mikrotik.com/web-assets/rb_images/2347_lg.webp',
        'mikrotik mantbox 19s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1146_lg.webp',
        'mikrotik mantbox 15s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1141_lg.webp',
        'mikrotik lhg xl 52 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1833_lg.webp',
        'mikrotik lhg 60g' => 'https://cdn.mikrotik.com/web-assets/rb_images/1814_lg.webp',
        'mikrotik cubeg-5ac60ay' => 'https://cdn.mikrotik.com/web-assets/rb_images/2017_lg.webp',
        'mikrotik cube 60pro ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/2138_lg.webp',
        'mikrotik wireless wire nray' => 'https://cdn.mikrotik.com/web-assets/rb_images/1960_lg.webp',
        'mikrotik wireless wire dish' => 'https://cdn.mikrotik.com/web-assets/rb_images/1507_lg.webp',
        'mikrotik wireless wire cube pro' => 'https://cdn.mikrotik.com/web-assets/rb_images/2142_lg.webp',
        'mikrotik wireless wire cube' => 'https://cdn.mikrotik.com/web-assets/rb_images/2010_lg.webp',
        'mikrotik wireless wire' => 'https://cdn.mikrotik.com/web-assets/rb_images/1355_lg.webp',
        'mikrotik basebox 6' => 'https://cdn.mikrotik.com/web-assets/rb_images/1616_lg.webp',
        'mikrotik basebox 5' => 'https://cdn.mikrotik.com/web-assets/rb_images/779_lg.webp',
        'mikrotik netmetal 5' => 'https://cdn.mikrotik.com/web-assets/rb_images/1118_lg.webp',
        'mikrotik metal 52 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1190_lg.webp',
        'mikrotik disc lite5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/1495_lg.webp',
        'mikrotik disc lite5' => 'https://cdn.mikrotik.com/web-assets/rb_images/1254_lg.webp',
        'mikrotik qrt 5' => 'https://cdn.mikrotik.com/web-assets/rb_images/919_lg.webp',
        'mikrotik sxt 5 ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/931_lg.webp',
        'mikrotik sxt sa5 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/1441_lg.webp',
        'mikrotik sxtsq lite5' => 'https://cdn.mikrotik.com/web-assets/rb_images/1308_lg.webp',
        'mikrotik sxtsq 5 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2448_lg.webp',
        'mikrotik lhg 5 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2460_lg.webp',
        'mikrotik lhg xl 5 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2463_lg.webp',
        'mikrotik netbox 5 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2305_lg.webp',
        'mikrotik intercell 10 b38+b39' => 'https://cdn.mikrotik.com/web-assets/rb_images/1926_lg.webp',
        'mikrotik mant lte 5o with r11e-lte' => 'https://cdn.mikrotik.com/web-assets/rb_images/1529_lg.webp',
        'mikrotik mant lte 5o' => 'https://cdn.mikrotik.com/web-assets/rb_images/1529_lg.webp',
        'mikrotik r11e-lr8g' => 'https://cdn.mikrotik.com/web-assets/rb_images/2476_lg.webp',
        'mikrotik r11e-4g' => 'https://cdn.mikrotik.com/web-assets/rb_images/1670_lg.webp',
        'mikrotik r11e-lte' => 'https://cdn.mikrotik.com/web-assets/rb_images/1425_lg.webp',
        'mikrotik r11e-lte6' => 'https://cdn.mikrotik.com/web-assets/rb_images/1882_lg.webp',
        'mikrotik r11el-ec200a-eu' => 'https://cdn.mikrotik.com/web-assets/rb_images/2302_lg.webp',
        'mikrotik r11el-fg621-ea' => 'https://cdn.mikrotik.com/web-assets/rb_images/2294_lg.webp',
        'mikrotik r11e-lte-us' => 'https://cdn.mikrotik.com/web-assets/rb_images/1435_lg.webp',
        'mikrotik sxtsq embedded lte4 global' => 'https://cdn.mikrotik.com/web-assets/rb_images/2666_lg.webp',
        'mikrotik sxtsq embedded lte4' => 'https://cdn.mikrotik.com/web-assets/rb_images/2661_lg.webp',
        'mikrotik knot embedded lte4 global' => 'https://cdn.mikrotik.com/web-assets/rb_images/2606_lg.webp',
        'mikrotik knot embedded lte4' => 'https://cdn.mikrotik.com/web-assets/rb_images/2510_lg.webp',
        'mikrotik wap lte kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1316_lg.webp',
        'mikrotik wap ac lte kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1794_lg.webp',
        'mikrotik wap ax lte7 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/2598_lg.webp',
        'mikrotik ltap lr8 lte' => 'https://cdn.mikrotik.com/web-assets/rb_images/2022_lg.webp',
        'mikrotik ltap mini' => 'https://cdn.mikrotik.com/web-assets/rb_images/1510_lg.webp',
        'mikrotik ltap mini lte kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1513_lg.webp',
        'mikrotik ltap mini lte kit (2024)' => 'https://cdn.mikrotik.com/web-assets/rb_images/2360_lg.webp',
        'mikrotik ltap lte kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1804_lg.webp',
        'mikrotik ltap lte6 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1891_lg.webp',
        'mikrotik ltap lte7 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/2557_lg.webp',
        'mikrotik lhg lte kit us' => 'https://cdn.mikrotik.com/web-assets/rb_images/1666_lg.webp',
        'mikrotik ldf lte kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1942_lg.webp',
        'mikrotik ldf lte6 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1942_lg.webp',
        'mikrotik sxt lte kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1543_lg.webp',
        'mikrotik sxt lte7 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/2560_lg.webp',
        'mikrotik lhgg lte6 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1996_lg.webp',
        'mikrotik sxt lte6 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1884_lg.webp',
        'mikrotik lhg lte6 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/1886_lg.webp',
        'mikrotik lhgg lte18 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/2165_lg.webp',
        'mikrotik lhg lte18 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/2165_lg.webp',
        'mikrotik atl lte18 kit' => 'https://cdn.mikrotik.com/web-assets/rb_images/2214_lg.webp',
        'mikrotik atl lte18' => 'https://cdn.mikrotik.com/web-assets/rb_images/2214_lg.webp',
        'mikrotik hap ax lite lte6' => 'https://cdn.mikrotik.com/web-assets/rb_images/2318_lg.webp',
        'mikrotik chateau lte6 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2260_lg.webp',
        'mikrotik chateau lte6-us' => 'https://cdn.mikrotik.com/web-assets/rb_images/2187_lg.webp',
        'mikrotik chateau lte12' => 'https://cdn.mikrotik.com/web-assets/rb_images/1914_lg.webp',
        'mikrotik chateau lte18 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2205_lg.webp',
        'mikrotik chateau lte7 ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2558_lg.webp',
        'mikrotik chateau lte7' => 'https://cdn.mikrotik.com/web-assets/rb_images/2562_lg.webp',
        'mikrotik atl 5g r16' => 'https://cdn.mikrotik.com/web-assets/rb_images/2456_lg.webp',
        'mikrotik chateau 5g ax' => 'https://cdn.mikrotik.com/web-assets/rb_images/2208_lg.webp',
        'mikrotik chateau 5g' => 'https://cdn.mikrotik.com/web-assets/rb_images/2049_lg.webp',
        'mikrotik chateau 5g r16' => 'https://cdn.mikrotik.com/web-assets/rb_images/2303_lg.webp',
        'mikrotik cubesa 60pro ac' => 'https://cdn.mikrotik.com/web-assets/rb_images/2144_lg.webp',
        'mikrotik wap 60g ap' => 'https://cdn.mikrotik.com/web-assets/rb_images/1474_lg.webp',
        'mikrotik wap 60g' => 'https://cdn.mikrotik.com/web-assets/rb_images/1473_lg.webp',
        'mikrotik rb760igs (hex s)' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'mikrotik rb760igs hex s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'mikrotik hex s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'hex s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'mikrotik rb4011igs+rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/1633_lg.webp',
        'rb4011igs+rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/1633_lg.webp',
        'mikrotik rb951ui-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/902_lg.webp',
        'rb951ui-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/902_lg.webp',
        'mikrotik l009uigs-rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/2267_lg.webp',
        'l009uigs-rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/2267_lg.webp',
        'mikrotik l009uigs-2haxd-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2263_lg.webp',
        'l009uigs-2haxd-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2263_lg.webp',
        'mikrotik rb5009ug+s+in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2065_lg.webp',
        'rb5009ug+s+in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2065_lg.webp',
        'mikrotik rb5009upr+s+in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2190_lg.webp',
        'mikrotik gper' => 'https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp',
        'gper' => 'https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp',
        'mikrotik rbpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/848_lg.webp',
        'rbpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/848_lg.webp',
        'mikrotik rbgpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/1967_lg.webp',
        'rbgpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/1967_lg.webp',
        'mikrotik rbgpoe-con-hp' => 'https://cdn.mikrotik.com/web-assets/rb_images/1181_lg.webp',
        'rbgpoe-con-hp' => 'https://cdn.mikrotik.com/web-assets/rb_images/1181_lg.webp',
        'gesp+poe-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2062_lg.webp',
        'mikrotik gpen11' => 'https://cdn.mikrotik.com/web-assets/rb_images/1744_lg.webp',
        'gpen11' => 'https://cdn.mikrotik.com/web-assets/rb_images/1744_lg.webp',
        'mikrotik gpen21' => 'https://cdn.mikrotik.com/web-assets/rb_images/1946_lg.webp',
        'gpen21' => 'https://cdn.mikrotik.com/web-assets/rb_images/1946_lg.webp',
        'mikrotik gpoe-usb' => 'https://cdn.mikrotik.com/web-assets/rb_images/2676_lg.webp',
        'gpoe-usb' => 'https://cdn.mikrotik.com/web-assets/rb_images/2676_lg.webp',
        'mikrotik ftc21-ups' => 'https://cdn.mikrotik.com/web-assets/rb_images/2597_lg.webp',
        'ftc21-ups' => 'https://cdn.mikrotik.com/web-assets/rb_images/2597_lg.webp',
        'mikrotik r11e-5hacd' => 'https://cdn.mikrotik.com/web-assets/rb_images/981_lg.webp',
        'r11e-5hacd' => 'https://cdn.mikrotik.com/web-assets/rb_images/981_lg.webp',
        'mikrotik r11e-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/667_lg.webp',
        'r11e-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/667_lg.webp',
        'mikrotik r11e-2hpnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/666_lg.webp',
        'r11e-2hpnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/666_lg.webp',
        'mikrotik r11e-5hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/815_lg.webp',
        'r11e-5hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/815_lg.webp',
    ];

    /**
     * @var array<string, string>
     */
    private const OFFICIAL_IMAGE_SLUGS = [
        'mikrotik-rb760igs-hex-s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'rb760igs-hex-s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'mikrotik-hex-s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'hex-s' => 'https://cdn.mikrotik.com/web-assets/rb_images/1539_lg.webp',
        'mikrotik-rb4011igs-rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/1633_lg.webp',
        'rb4011igs-rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/1633_lg.webp',
        'mikrotik-rb951ui-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/902_lg.webp',
        'rb951ui-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/902_lg.webp',
        'mikrotik-l009uigs-rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/2267_lg.webp',
        'l009uigs-rm' => 'https://cdn.mikrotik.com/web-assets/rb_images/2267_lg.webp',
        'mikrotik-l009uigs-2haxd-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2263_lg.webp',
        'l009uigs-2haxd-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2263_lg.webp',
        'mikrotik-rb5009ug-s-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2065_lg.webp',
        'rb5009ug-s-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2065_lg.webp',
        'mikrotik-rb5009upr-s-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2190_lg.webp',
        'rb5009upr-s-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2190_lg.webp',
        'mikrotik-gper' => 'https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp',
        'gper' => 'https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp',
        'mikrotik-rbpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/848_lg.webp',
        'rbpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/848_lg.webp',
        'mikrotik-rbgpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/1967_lg.webp',
        'rbgpoe' => 'https://cdn.mikrotik.com/web-assets/rb_images/1967_lg.webp',
        'mikrotik-rbgpoe-con-hp' => 'https://cdn.mikrotik.com/web-assets/rb_images/1181_lg.webp',
        'rbgpoe-con-hp' => 'https://cdn.mikrotik.com/web-assets/rb_images/1181_lg.webp',
        'gesppoe-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2062_lg.webp',
        'gesp-poe-in' => 'https://cdn.mikrotik.com/web-assets/rb_images/2062_lg.webp',
        'mikrotik-gpen11' => 'https://cdn.mikrotik.com/web-assets/rb_images/1744_lg.webp',
        'gpen11' => 'https://cdn.mikrotik.com/web-assets/rb_images/1744_lg.webp',
        'mikrotik-gpen21' => 'https://cdn.mikrotik.com/web-assets/rb_images/1946_lg.webp',
        'gpen21' => 'https://cdn.mikrotik.com/web-assets/rb_images/1946_lg.webp',
        'mikrotik-gpoe-usb' => 'https://cdn.mikrotik.com/web-assets/rb_images/2676_lg.webp',
        'gpoe-usb' => 'https://cdn.mikrotik.com/web-assets/rb_images/2676_lg.webp',
        'mikrotik-ftc21-ups' => 'https://cdn.mikrotik.com/web-assets/rb_images/2597_lg.webp',
        'ftc21-ups' => 'https://cdn.mikrotik.com/web-assets/rb_images/2597_lg.webp',
        'mikrotik-r11e-5hacd' => 'https://cdn.mikrotik.com/web-assets/rb_images/981_lg.webp',
        'r11e-5hacd' => 'https://cdn.mikrotik.com/web-assets/rb_images/981_lg.webp',
        'mikrotik-r11e-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/667_lg.webp',
        'r11e-2hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/667_lg.webp',
        'mikrotik-r11e-2hpnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/666_lg.webp',
        'r11e-2hpnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/666_lg.webp',
        'mikrotik-r11e-5hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/815_lg.webp',
        'r11e-5hnd' => 'https://cdn.mikrotik.com/web-assets/rb_images/815_lg.webp',
    ];

    /**
     * @var array<int, string>
     */
    private const TRUSTED_OFFICIAL_IMAGE_HOSTS = [
        'assets.ecomm.ui.com',
        'cdn.ecomm.ui.com',
        'images.svc.ui.com',
    ];

    /**
     * @var array<int, string>
     */
    private const TRUSTED_MANUFACTURER_HOSTS = [
        'help.ui.com',
        'store.ui.com',
        'techspecs.ui.com',
        'ui.com',
        'www.ui.com',
    ];

    public static function officialUrlFor(?string $productName): ?string
    {
        $key = self::normalizeName($productName);
        if ($key === '') {
            return null;
        }

        return self::OFFICIAL_IMAGES[$key]
            ?? self::OFFICIAL_IMAGES['mikrotik ' . $key]
            ?? self::OFFICIAL_IMAGE_SLUGS[Str::slug($key)]
            ?? self::OFFICIAL_IMAGE_SLUGS[Str::slug('mikrotik ' . $key)]
            ?? null;
    }

    /**
     * Official gallery images for a product, preferring trusted Ubiquiti media
     * and falling back to the legacy built-in image map.
     *
     * @return array<int, string>
     */
    public static function officialUrls(Product $product): array
    {
        if (is_array($product->official_gallery_images) && $product->official_gallery_images !== []) {
            $urls = array_values(array_unique(array_filter(
                array_map('strval', $product->official_gallery_images),
                fn (string $url): bool => self::isTrustedOfficialImageUrl($url)
            )));

            if ($urls !== []) {
                return $urls;
            }
        }

        foreach (['official_image_url', 'manufacturer_image_url'] as $field) {
            if (($single = trim((string) ($product->{$field} ?? ''))) && self::isTrustedOfficialImageUrl($single)) {
                return [$single];
            }
        }

        if ($static = self::officialUrlFor($product->name)) {
            return [$static];
        }

        return [];
    }

    public static function officialVideoUrlFor(Product $product): ?string
    {
        $videoUrl = trim((string) $product->official_video_url);

        return $videoUrl !== '' ? $videoUrl : null;
    }

    public static function placeholderUrl(): string
    {
        return self::publicPathUrl('assets/product-placeholder.svg');
    }

    public static function uploadedUrlFor(?string $productName, ?string $productSlug = null): ?string
    {
        foreach (self::uploadedImageBasenames($productName, $productSlug) as $basename) {
            foreach (self::UPLOADED_IMAGE_EXTENSIONS as $extension) {
                $relativePath = 'uploads/products/' . $basename . '.' . $extension;

                if (is_file(public_path($relativePath))) {
                    return self::publicPathUrl($relativePath);
                }
            }
        }

        return null;
    }

    public static function publicPathUrl(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        return rtrim(request()->getBaseUrl(), '/') . '/' . ltrim($path, '/');
    }

    public static function isTrustedOfficialImageUrl(?string $url): bool
    {
        $parts = self::httpsUrlParts($url);
        if ($parts === null) {
            return false;
        }

        return in_array($parts['host'], self::TRUSTED_OFFICIAL_IMAGE_HOSTS, true);
    }

    public static function isTrustedManufacturerUrl(?string $url): bool
    {
        $parts = self::httpsUrlParts($url);
        if ($parts === null) {
            return false;
        }

        if (in_array($parts['host'], self::TRUSTED_MANUFACTURER_HOSTS, true)) {
            return true;
        }

        return str_ends_with($parts['host'], '.store.ui.com');
    }

    /**
     * @return array{scheme: string, host: string}|null
     */
    private static function httpsUrlParts(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https' || $host === '') {
            return null;
        }

        return [
            'scheme' => $scheme,
            'host' => $host,
        ];
    }

    private static function normalizeName(?string $productName): string
    {
        $name = Str::lower(trim(strip_tags((string) $productName)));

        return preg_replace('/\s+/u', ' ', $name) ?? '';
    }

    /**
     * @return array<int, string>
     */
    private static function uploadedImageBasenames(?string $productName, ?string $productSlug): array
    {
        $candidates = [];

        foreach ([$productSlug, $productName] as $value) {
            $slug = Str::slug((string) $value);
            if ($slug !== '') {
                $candidates[] = $slug;
            }
        }

        $normalizedName = self::normalizeName($productName);
        if (Str::startsWith($normalizedName, 'mikrotik ')) {
            $withoutBrand = trim(Str::after($normalizedName, 'mikrotik '));
            $slug = Str::slug($withoutBrand);

            if ($slug !== '') {
                $candidates[] = $slug;
            }
        }

        return array_values(array_unique($candidates));
    }
}
